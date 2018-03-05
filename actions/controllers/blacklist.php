<?php

$rows_count = 0;

if(empty($_POST)) {
  return;
}

if(!empty($_POST['truncate'])) {
    $db->query('TRUNCATE TABLE blacklist');
    $message  = 'The blacklist table is erased';
    return;
}

$errorCode = $_FILES['file_source']['error'];
if( 0 == $_FILES['file_source']['size'] )
{
  $errorCode = -1; //empty file
}

if( is_uploaded_file($_FILES['file_source']['tmp_name']) && UPLOAD_ERR_OK == $errorCode ) {
    $temp_file = $_FILES['file_source']['tmp_name'];
    $our_file  = TEMP_DIR . basename($temp_file);
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

    $fQuickCSV = new Quick_CSV_import($db);
    
    $fQuickCSV->table_name = 'blacklist';
    $fQuickCSV->file_name = $our_file;
    $fQuickCSV->use_csv_header = false;
    $fQuickCSV->make_temporary = false;
    $fQuickCSV->table_exists = true;
    $fQuickCSV->truncate_table = false;
    $fQuickCSV->field_separate_char = ',';
    $fQuickCSV->encoding = 'utf8';
    $fQuickCSV->field_enclose_char = '"';
    $fQuickCSV->field_escape_char = '\\';
    
    $fQuickCSV->import();
    unlink($our_file);
    $rows_count = $fQuickCSV->rows_count;

    if( !empty($fQuickCSV->error) )
    {
      $error = $fQuickCSV->error;
    }
    elseif(empty($rows_count))
    {
      $error = 'Imported rows count is 0.';
    }
}
