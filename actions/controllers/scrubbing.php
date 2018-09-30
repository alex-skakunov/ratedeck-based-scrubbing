<?php

if (!empty($_GET['erase_queue'])) {
    $filesToDelete = query('SELECT id, temp_filename FROM `queue`')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($filesToDelete as $file) {
      @unlink(TEMP_DIR . $file['temp_filename']);
      @unlink(TEMP_DIR . $file['id'] . '.csv');
    }
    query('TRUNCATE TABLE `queue`');
    header('Location: ?page=scrubbing');
    exit;
}

$wireless = 1;
$landline = 1;
$areacodes_all = empty($_POST['areacode']);


$recordset = query('SELECT * FROM `queue` ORDER BY `id` DESC')->fetchAll(PDO::FETCH_ASSOC);

$theLastQueuedItem = query('SELECT * FROM `queue` ORDER BY `id` DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);


$_areacodeList = query('SELECT DISTINCT `region` FROM `areacode` ORDER BY `region` ASC')->fetchAll(PDO::FETCH_ASSOC);
// need to rely on states names and not on the code, since there are multiple codes for some states
$areacodeList = array();
foreach($_areacodeList as $areacode) {
  $token = str_replace(' ', '_', strtolower($areacode['region']));
  $areacodeList[$token] = $areacode['region'];
}


if(empty($_POST)) {
  return;
}

new dBug($_POST);
new dBug($_FILES);

$areacodes = array();

if (!empty($_POST['areacode'])) {
  foreach ($_POST['areacode'] as $code) {
    $areacodes[$code] = $code;
  }
}


$errorCode = $_FILES['file_source']['error'];
if( 0 == $_FILES['file_source']['size'] )
{
  $errorCode = -1; //empty file
}

if( is_uploaded_file($_FILES['file_source']['tmp_name']) && UPLOAD_ERR_OK == $errorCode ) {
    $temp_file = $_FILES['file_source']['tmp_name'];
    $our_file  = tempnam(TEMP_DIR, 'scrub');
    if ( !move_uploaded_file( $temp_file, $our_file ) ) {
      $error = 'Could not copy [' . $temp_file .'] to [' . $our_file . ']';
      return;
    }

    $zip = new ZipArchive;
    if ($zip->open($our_file) === TRUE) {
        $csvFilename = $zip->getNameIndex(0);
        $zip->extractTo(TEMP_DIR, array($csvFilename));
        $zip->close();
        unlink($our_file); //remove zip
        $our_file = TEMP_DIR . $csvFilename;
    }
}

if (!empty($_FILES['file_source']['name'])) {
    $originalFilename = $_FILES['file_source']['name'];
} elseif (!empty($theLastQueuedItem)) {
    $originalFilename = $theLastQueuedItem['filename'];
} else {
  throw new Exception("No file was uploaded");
}

if (!empty($our_file)) {
    $temporaryFilename = pathinfo($our_file, PATHINFO_BASENAME);
} elseif (!empty($theLastQueuedItem)) {
    $temporaryFilename = $theLastQueuedItem['temp_filename'];
}

$rows_count = !empty($theLastQueuedItem) ? $theLastQueuedItem['rows_count'] : null;

query('INSERT INTO `queue`(`filename`, `temp_filename`, `max_price`, `include_wireless_type`, `include_landline_type`, `specific_states_list`, `rows_count`, `created_at`) VALUES (
    :original_filename,
    :temp_filename,
    :max_price,
    :include_wireless_type,
    :include_landline_type,
    :specific_states_list,
    :rows_count,
    NOW()
)', array(
    ':original_filename' => $originalFilename,
    ':temp_filename' => $temporaryFilename,
    ':max_price' => (float)$_POST['max_price'],
    ':include_wireless_type' => (int)$_POST['wireless'],
    ':include_landline_type' => (int)$_POST['landline'],
    ':specific_states_list' => !empty($areacodes) 
      ? implode(',', $areacodes)
      : null,
    ':rows_count' => $rows_count
));


$stmt = query('SELECT * FROM `queue` ORDER BY `id` DESC LIMIT 100');
$recordset = $stmt->fetchAll();