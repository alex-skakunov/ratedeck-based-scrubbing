<?php

$userId = $_SESSION['user']['id'];
$isAdmin = 'admin' == $_SESSION['user']['level'];

if (!empty($_GET['erase_queue'])) {
    erase_user_queue($userId);
    header('Location: ?page=scrubbing');
    exit;
}

$wireless = 1;
$landline = 1;
$areacodes_all = empty($_POST['areacode']);

$recordset = query('SELECT * FROM `queue` WHERE `user_id` = '.$userId.' ORDER BY `id` DESC')->fetchAll(PDO::FETCH_ASSOC);
$theLastQueuedItem = query('SELECT * FROM `queue` WHERE `user_id` = '.$userId.' AND `filename` <> "" AND `filename` IS NOT NULL ORDER BY `id` DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);

$_areacodeList = query('SELECT DISTINCT `region` FROM `areacode` ORDER BY `region` ASC')->fetchAll(PDO::FETCH_ASSOC);
// need to rely on states names and not on the code, since there are multiple codes for some states
$areacodeList = array();
foreach($_areacodeList as $areacode) {
    $token = str_replace(' ', '_', strtolower($areacode['region']));
    $areacodeList[$token] = $areacode['region'];
}

$templates = query('SELECT * FROM `template` WHERE `user_id` = '.$userId)->fetchAll(PDO::FETCH_ASSOC);

if(empty($_POST)) {
    return;
}

$areacodes = array();

if (!empty($_POST['areacode'])) {
    foreach ($_POST['areacode'] as $code) {
        $areacodes[$code] = $code;
    }
}

if ($isAdmin) {
    $maxPrice = (float)$_POST['max_price'];
}
else {
    $userRecord = query(
        'SELECT `max_price`
        FROM `user`
        WHERE `id` = ' . $userId)->fetch();
    $maxPrice = !empty($userRecord) ? (float)$userRecord['max_price'] : 1;
}

$isTheOnlyFile = sizeof($_FILES['file_source']['name']) == 1;

foreach ($_FILES['file_source']['name'] as $index => $filename) {
    $errorCode = $_FILES['file_source']['error'][$index];
    if (!$isTheOnlyFile && 0 == $_FILES['file_source']['size'][$index]) {
        $errorCode = -1; //empty file
    }

    if (!$isTheOnlyFile && UPLOAD_ERR_OK != $errorCode) {
        $errorMessage = $uploadErrors[$errorCode];
        query('INSERT INTO `queue`(`user_id`, `filename`, `temp_filename`, `max_price`, `include_wireless_type`, `include_landline_type`, `specific_states_list`, `sort_order`, `status`, `error_message`, `created_at`) VALUES (
                :user_id,
                :original_filename,
                :temp_filename,
                :max_price,
                :include_wireless_type,
                :include_landline_type,
                :specific_states_list,
                :sort_order,
                "error",
                :error_message,
                NOW()
        )', array(
            ':user_id' => $userId,
            ':original_filename' => $filename,
            ':temp_filename' => $_FILES['file_source']['tmp_name'][$index],
            ':max_price' => $maxPrice,
            ':include_wireless_type' => (int)$_POST['wireless'],
            ':include_landline_type' => (int)$_POST['landline'],
            ':specific_states_list' => !empty($areacodes) 
              ? implode(',', $areacodes)
              : null,
            ':sort_order' => (int)$_POST['sort_order'],
            ':error_message' => $errorMessage
        ));
        continue;
    }

    $temp_file = $_FILES['file_source']['tmp_name'][$index];
    if (is_uploaded_file($temp_file)) {
        $our_file  = tempnam(TEMP_DIR, 'scrub');
        if ( !move_uploaded_file( $temp_file, $our_file ) ) {
            $errorMessage = 'Could not copy [' . $temp_file .'] to [' . $our_file . ']';
            return;
        }

        $pathParts = pathinfo($our_file);
        if ('zip' == strtolower($pathParts['extension'])) {
            $zip = new ZipArchive;
            if ($zip->open($our_file) === TRUE) {
                $innerFilename = $zip->getNameIndex(0);
                $zip->extractTo(TEMP_DIR, array($innerFilename));
                $zip->close();
                rename(TEMP_DIR . $innerFilename, $our_file);
            }
        }
    }

    if (!empty($_FILES['file_source']['name'][$index])) {
        $originalFilename = $_FILES['file_source']['name'][$index];
        $pathParts = pathinfo($originalFilename);

        $extension = strtolower($pathParts['extension']);
        if (in_array($extension, ['xls', 'xlsx'])) {
            $baseName = $our_file;
            $excelTmpFile = $baseName . '.' . $extension;
            rename($our_file, $excelTmpFile);
            
            // convert Excel file to csv
            if (!file_exists($pathParts['filename'] . '.csv')) {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($excelTmpFile);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($excelTmpFile);

                $our_file = $baseName . '.csv';
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
                $writer->save($our_file);
                @unlink($excelTmpFile);
            }
        }
    } elseif (!empty($theLastQueuedItem)) {
        $originalFilename = $theLastQueuedItem['filename'];
    } else {
        return $errorMessage = "No file was uploaded";
    }

    if (!empty($our_file)) {
        $temporaryFilename = pathinfo($our_file, PATHINFO_BASENAME);
    } elseif (!empty($theLastQueuedItem)) {
        $temporaryFilename = $theLastQueuedItem['temp_filename'];
    }

    if (!empty($_FILES['file_source']['name'][$index])) {
        $rows_count = null;
    } elseif (!empty($theLastQueuedItem)) {
        $rows_count = $theLastQueuedItem['rows_count'];
    } else {
        $rows_count = null;
    }

    // figure out if it's more than a single column
    // if yes, status = 'action required',
    // else    status = 'queued'
    $firstLine = CSV::get_line(TEMP_DIR . $temporaryFilename);
    $separator = CSV::try_separators($firstLine, ',');
    $fp = fopen(TEMP_DIR . $temporaryFilename, "r");
    $columns = fgetcsv($fp, 1000, $separator);
    $status = count($columns) > 1 ? 'action required' : 'queued';
    $columnsToChooseFrom = count($columns) > 1 ? $columns : null;

    query('INSERT INTO `queue`(`user_id`, `filename`, `temp_filename`, `max_price`, `include_wireless_type`, `include_landline_type`,
        `specific_states_list`, `include_lawsuits_dnc`, `include_master_dnc`, `include_prefix_dnc`, `include_own_dnc`, `is_blacklisted_report_required`,
        `status`, `sort_order`, `rows_count`, `columns_list`, `created_at`) VALUES (
            :user_id,
            :original_filename,
            :temp_filename,
            :max_price,
            :include_wireless_type,
            :include_landline_type,
            :specific_states_list,
            :include_lawsuits_dnc,
            :include_master_dnc,
            :include_prefix_dnc,
            :include_own_dnc,
            :is_blacklisted_report_required,
            :stat,
            :sort_order,
            :rows_count,
            :columns,
            NOW()
    )', array(
        ':user_id' => $userId,
        ':original_filename' => $originalFilename,
        ':temp_filename' => $temporaryFilename,
        ':max_price' => $maxPrice,
        ':include_wireless_type' => (int)$_POST['wireless'],
        ':include_landline_type' => (int)$_POST['landline'],
        ':specific_states_list' => !empty($areacodes) 
          ? implode(',', $areacodes)
          : null,
        ':include_lawsuits_dnc' => !empty($_POST['include_lawsuits_dnc']) ? 1 : 0,
        ':include_master_dnc' => !empty($_POST['include_master_dnc']) ? 1 : 0,
        ':include_prefix_dnc' => !empty($_POST['include_prefix_dnc']) ? 1 : 0,
        ':include_own_dnc' => !empty($_POST['include_own_dnc']) ? 1 : 0,
        ':is_blacklisted_report_required' => !empty($_POST['is_blacklisted_report_required']) ? 1 : 0,
        ':stat' => $status,
        ':sort_order' => (int)$_POST['sort_order'],
        ':rows_count' => $rows_count,
        ':columns'    => !empty($columnsToChooseFrom) ? json_encode($columnsToChooseFrom) : null
    ));
}

header('Location: ?page=scrubbing');