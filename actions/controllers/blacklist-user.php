<?php

$userId = $_SESSION['user']['id'];

if (!empty($_GET['truncate'])) {
    query('TRUNCATE TABLE blacklist_user_' . $userId);
    header('Location: ?page=blacklist&table_erased=your');
    exit;
}

$rows_count = 0;

if (empty($_POST)) {
  return;
}

$errorCode = $_FILES['file_source']['error'];
if( 0 == $_FILES['file_source']['size'] )
{
  $errorCode = -1; //empty file
}

if(!is_uploaded_file($_FILES['file_source']['tmp_name']) || UPLOAD_ERR_OK !== $errorCode ) {
    return $message = $error = "Something went wrong while the upload";
}

$temp_file = $_FILES['file_source']['tmp_name'];
$our_file  = tempnam(TEMP_DIR, 'blacklist');
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

$fQuickCSV = new Quick_CSV_import;
$fQuickCSV->table_name = 'blacklist_user_' . $userId;
$fQuickCSV->file_name = $our_file;
$fQuickCSV->use_csv_header = false;
$fQuickCSV->make_temporary = false;
$fQuickCSV->table_exists = true;
$fQuickCSV->truncate_table = !true;
$fQuickCSV->fields_list = array('@area', '@number');
$fQuickCSV->parameters = array(
    // this implements support of both formats:
    // 1. 1234567890 (just a number)
    // 2. 123,4567890 (area code and the number)
    'number' => 'CONCAT(@area, IFNULL(@number, ""))'
);


try {
    $fQuickCSV->import();
    unlink($our_file);

    if (!empty($fQuickCSV->error)) {
      throw new Exception($fQuickCSV->error);
    }
    $message = 'The DNC list was uploaded successfully';
}
catch(Exception $e) {
    return $message = $errorMessage = $e->getMessage();
}

$userRecord = query(
  'SELECT `email`, `name`
   FROM `user`
   WHERE `id` = ' . $userId)->fetch();

if (empty($userRecord)) {
  return $message = 'Could not find the current user in the database';
}

sendEmail('Blacklist upload is done: ' . $_FILES['file_source']['name'],
    (empty($errorMessage)
        ?  'The DNC list was uploaded successfully.'
        : 'There was a problem with the upload: ' . $errorMessage
    ) . chr(10) .
    ' The file size: '
    . number_format($_FILES['file_source']['size']) . ' bytes',
    $userRecord['email'],
    $userRecord['name']
);
