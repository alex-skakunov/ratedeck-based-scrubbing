<?php

if (!empty($_GET['truncate'])) {
    $token = strtolower(trim($_GET['truncate']));
    switch($token) {
        case 'lawsuits':
            $tableNameToTruncate = 'blacklist_lawsuits';
            break;
        case 'master':
            $tableNameToTruncate = 'blacklist_master';
            break;
        default:
            $tableNameToTruncate = null;
            break;
    }
    
    if (!empty($tableNameToTruncate)) {
        query('TRUNCATE TABLE ' . $tableNameToTruncate);
    }
    header('Location: ?page=blacklist&table_erased=' . $token);
    exit;
}

if (!empty($_GET['table_erased'])) {
    $message = ucfirst($_GET['table_erased']) . ' DNC list was erased';
}

// $blacklistCount = (int)query('SELECT COUNT(*) FROM `blacklist`')->fetchColumn(); // takes more than a minute on 250 mln table
$rows_count = 0;

if (empty($_POST)) {
  return;
}

$blacklistType = strtolower(trim($_POST['blacklist_type']));
if (!in_array($blacklistType, array('lawsuits', 'master'))) {
    return $message = 'Please choose a DNC list to upload to';
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
$fQuickCSV->table_name = 'blacklist_' . $blacklistType;
$fQuickCSV->file_name = $our_file;
$fQuickCSV->use_csv_header = false;
$fQuickCSV->make_temporary = false;
$fQuickCSV->table_exists = true;
$fQuickCSV->truncate_table = true;
$fQuickCSV->fields_list = array('@area', '@number');
$fQuickCSV->parameters = array(
    'number' => 'CONCAT(@area, @number)'
);


try {
    $fQuickCSV->import();
    unlink($our_file);

    if (!empty($fQuickCSV->error)) {
      throw new Exception($fQuickCSV->error);
    }
    $message = 'The ' . ucfirst($blacklistType) . ' DNC list was uploaded successfully.';
}
catch(Exception $e) {
    $message = $errorMessage = $e->getMessage();
}

sendEmail('Blacklist upload is done: ' . $_FILES['file_source']['name'],
    (empty($errorMessage)
        ?  'The ' . ucfirst($blacklistType) . ' DNC list was uploaded successfully.'
        : 'There was a problem with the upload: ' . $errorMessage
    ) .
    '<br/><br/> The file size: '
    . number_format($_FILES['file_source']['size']) . ' bytes'
);
