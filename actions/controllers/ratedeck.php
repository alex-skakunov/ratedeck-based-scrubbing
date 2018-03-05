<?php

if( !empty($_POST) ) //form was submitted
{
  $errorCode = $_FILES['file_source']['error'];
  if( 0 == $_FILES['file_source']['size'] )
  {
    $errorCode = -1; //empty file
  }
  
  if( is_uploaded_file($_FILES['file_source']['tmp_name']) && UPLOAD_ERR_OK == $errorCode )  //file was uploaded successfully
  {

    $temp_file = $_FILES['file_source']['tmp_name'];
    $our_file  = TEMP_DIR . basename($temp_file);
    if ( !move_uploaded_file( $temp_file, $our_file ) ) //copy to our folder
    {
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
      
      $lineSeparator = detect_line_ending($our_file);
      if( '\n' != $lineSeparator ) {
        $fQuickCSV->line_separate_char = $lineSeparator;
      }
      
      $fQuickCSV->table_name = DB_TABLE;
      $fQuickCSV->file_name = $our_file;
      $fQuickCSV->use_csv_header = true;
      $fQuickCSV->make_temporary = false;
      $fQuickCSV->table_exists = true;
      $fQuickCSV->truncate_table = true;
      $fQuickCSV->field_separate_char = ',';
      $fQuickCSV->encoding = 'utf8';
      $fQuickCSV->field_enclose_char = '"';
      $fQuickCSV->field_escape_char = '\\';
      
      $fQuickCSV->import();
      unlink($our_file);
      if( !empty($fQuickCSV->error) )
      {
        $error = $fQuickCSV->error;
      }
      elseif( 0 == $fQuickCSV->rows_count )
      {
        $error = 'Imported rows count is 0.';
      }

      $rows_count = $fQuickCSV->rows_count;

  }
  else //no, file was not uploaded, so let's rise an error
  {
    $error = coalesce( $uploadErrors[$errorCode], 'General upload error. Check <a href="http://php.net/manual/en/features.file-upload.php">file uploads settings</a> of your php.ini' );
    $_SESSION['data'] = array(); //erase previosly saved options
  }
  
}
else //form wasn't submited, it's a first request
{
  $_POST["use_csv_header"] = 1;
  $_SESSION['data'] = array(); //erase previosly saved options
}