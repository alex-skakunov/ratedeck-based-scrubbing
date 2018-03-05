<?php

$wireless = 1;
$landline = 1;
$areacodes_all = empty($_POST['areacode']);

if(empty($_POST)) {
  return;
}

$areacodes = array();

if (!empty($_POST['areacode'])) {
  foreach ($_POST['areacode'] as $code) {
    $areacodes[$code] = $code;
  }
}

$rows_count = array();

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
    
    $fQuickCSV->table_name = 'scrub';
    $fQuickCSV->file_name = $our_file;
    $fQuickCSV->use_csv_header = false;
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
}

//$db->query('UPDATE scrub SET NPANXX=SUBSTR(number, 1, 6)');

$db->query('SELECT COUNT(*) FROM scrub');
$rows_count['Original records in the table'] = $db->getField();

$max_price = (float)$_POST['max_price'];
$wireless = !empty($_POST['wireless']) ? 1 : 0;
$landline = !empty($_POST['landline']) ? 1 : 0;

$sqlTemplate = 'SELECT COUNT(*)
        FROM scrub
        INNER JOIN `ratedeck` ON SUBSTR(scrub.number, 1, 6) = ratedeck.NPANXX
        WHERE ratedeck.Rate <= %f
          %s
          AND scrub.`number` NOT IN (SELECT `number` FROM `blacklist`)';
$typeCriteria = array();
if (!empty($wireless)) {
  $typeCriteria[] = 'ratedeck.Wireless = "x"';
}
if (!empty($landline)) {
  $typeCriteria[] = 'ratedeck.Landline = "x"';
}

$additionalCriteriaClause = !empty($typeCriteria)
  ? 'AND (' . implode(' OR ', $typeCriteria) . ')'
  : '';

if (empty($areacodes_all)) {
  $statesList = '"' . implode('", "', $areacodes) . '"';
  $additionalCriteriaClause .= sprintf('AND SUBSTR(scrub.`number`, 1, 3) IN (SELECT `code` FROM `areacode` WHERE REPLACE(LCASE(`region`), " ", "_") IN (%s))', $statesList);
}
$sql = sprintf($sqlTemplate, $max_price, $additionalCriteriaClause);
$db->query($sql);
$rows_count['After applying price and wireless/landline filters'] = $db->getField();
//echo $sql;