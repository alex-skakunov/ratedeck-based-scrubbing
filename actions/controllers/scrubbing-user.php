<?php

if(empty($_POST)) {
  return;
}


$areacodes = array();

if (!empty($_POST['areacode'])) {
  foreach ($_POST['areacode'] as $code) {
    $areacodes[$code] = $code;
  }
}

if ('admin' == $_SESSION['user']['level']) {
  $maxPrice = !empty($_POST['max_price']) ? (float)$_POST['max_price'] : null;
}

$maxPrice = !empty($userRecord) ? $userRecord['max_price'] : 1;


$isTheOnlyFile = sizeof($_FILES['file_source']['name']) == 1;

foreach ($_FILES['file_source']['name'] as $index => $filename) {
    $errorCode = $_FILES['file_source']['error'][$index];
    if (!$isTheOnlyFile && 0 == $_FILES['file_source']['size'][$index]) {
        $errorCode = -1; //empty file
    }

    if (!$isTheOnlyFile && UPLOAD_ERR_OK != $errorCode) {
        $errorMessage = $uploadErrors[$errorCode];
        query('INSERT INTO `queue`(`user_id`, `filename`, `temp_filename`, `max_price`, `include_wireless_type`, `include_landline_type`, `specific_states_list`, `status`, `error_message`, `created_at`) VALUES (
                :user_id,
                :original_filename,
                :temp_filename,
                :max_price,
                :include_wireless_type,
                :include_landline_type,
                :specific_states_list,
                "error",
                :error_message,
                NOW()
        )', array(
            ':user_id' => $userId,
            ':original_filename' => $filename,
            ':temp_filename' => $_FILES['file_source']['tmp_name'][$index],
            ':max_price' => (float)$_POST['max_price'],
            ':include_wireless_type' => (int)$_POST['wireless'],
            ':include_landline_type' => (int)$_POST['landline'],
            ':specific_states_list' => !empty($areacodes) 
              ? implode(',', $areacodes)
              : null,
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

        $zip = new ZipArchive;
        if ($zip->open($our_file) === TRUE) {
            $csvFilename = $zip->getNameIndex(0);
            $zip->extractTo(TEMP_DIR, array($csvFilename));
            $zip->close();
            rename(TEMP_DIR . $csvFilename, $our_file);
        }
    }

    if (!empty($_FILES['file_source']['name'][$index])) {
        $originalFilename = $_FILES['file_source']['name'][$index];
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


    query('INSERT INTO `queue`(`user_id`, `filename`, `temp_filename`, `max_price`, `include_wireless_type`, `include_landline_type`,
        `specific_states_list`, `include_lawsuits_dnc`, `include_master_dnc`, `include_own_dnc`, `is_blacklisted_report_required`,
        `rows_count`, `created_at`) VALUES (
            :user_id,
            :original_filename,
            :temp_filename,
            :max_price,
            :include_wireless_type,
            :include_landline_type,
            :specific_states_list,
            :include_lawsuits_dnc,
            :include_master_dnc,
            :include_own_dnc,
            :is_blacklisted_report_required,
            :rows_count,
            NOW()
    )', array(
        ':user_id' => $userId,
        ':original_filename' => $originalFilename,
        ':temp_filename' => $temporaryFilename,
        ':max_price' => (float)$_POST['max_price'],
        ':include_wireless_type' => (int)$_POST['wireless'],
        ':include_landline_type' => (int)$_POST['landline'],
        ':specific_states_list' => !empty($areacodes) 
          ? implode(',', $areacodes)
          : null,
        ':include_lawsuits_dnc' => !empty($_POST['include_lawsuits_dnc']) ? 1 : 0,
        ':include_master_dnc' => !empty($_POST['include_master_dnc']) ? 1 : 0,
        ':include_own_dnc' => !empty($_POST['include_own_dnc']) ? 1 : 0,
        ':is_blacklisted_report_required' => !empty($_POST['is_blacklisted_report_required']) ? 1 : 0,
        ':rows_count' => $rows_count
    ));
}

header('Location: ?page=scrubbing');
