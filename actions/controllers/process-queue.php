<?php

$id = query('SELECT id FROM `queue` WHERE status="processing"')->fetchColumn(PDO::FETCH_ASSOC);
if (!empty($id)) {
    new dBug('Another process is in progress');
    return;
}

$item = query('SELECT * FROM `queue` WHERE status="queued" ORDER BY id LIMIT 1')->fetch(PDO::FETCH_ASSOC);
if (empty($item)) {
    new dBug('Nothing queued');
    return;
}

query('UPDATE `queue` SET STATUS="processing", error_message=NULL, updated_at=NOW() WHERE id=' . $item['id']);

$filename = TEMP_DIR . $item['temp_filename'];

// convert xls file to csv
$pathParts = pathinfo($filename);
if (in_array($pathParts['extension'], ['xls', 'xlsx'])) {
  if (!file_exists($pathParts['filename'] . '.csv')) {
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filename);
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($filename);
    $filename = TEMP_DIR . $pathParts['filename'] . '.csv';
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
    $writer->save($filename);
  }
}

$fQuickCSV = new Quick_CSV_import;

$fQuickCSV->table_name = 'scrub';
$fQuickCSV->file_name = $filename;
$fQuickCSV->use_csv_header = false;
$fQuickCSV->make_temporary = !true;
$fQuickCSV->table_exists = true;
$fQuickCSV->truncate_table = true;
$fQuickCSV->field_enclose_char = '"';
$fQuickCSV->fields_list = array('@area', '@number');
$fQuickCSV->parameters = array(
    // this implements support of both formats:
    // 1. 1234567890 (just a number)
    // 2. 123,4567890 (area code and the number)
    'number' => 'CONCAT(@area, IFNULL(@number, ""))'
);

try {
  $fQuickCSV->import();
}
catch(Exception $e) {
  echo $e->getMessage();
  query('UPDATE `queue` SET status="error", error_message=:error_message, updated_at=NOW() WHERE id=:id', array(
    ':id' => $item['id'],
    ':error_message' => 'File processing error: ' . $e->getMessage()
  ));
  return;
}

if( !empty($fQuickCSV->error) )
{
    query('UPDATE `queue` SET status="error", error_message=:error_message, updated_at=NOW() WHERE id=:id', array(
        ':id' => $item['id'],
        ':error_message' => $fQuickCSV->error
    ));
    return;
}

query('UPDATE `queue` SET rows_count=:rows_count, updated_at=NOW() WHERE id=:id', array(
    ':id' => $item['id'],
    ':rows_count' => $fQuickCSV->rows_count
));

if (!$fQuickCSV->rows_count) {
    return;
}

$max_price = (float)$item['max_price'];
$wireless = !empty($item['include_wireless_type']) ? 1 : 0;
$landline = !empty($item['include_landline_type']) ? 1 : 0;

switch($item['download_order']) {
  case 'asc':
      $downloadOrder = 'ORDER BY `number` ASC';
      break;
  case 'desc':
      $downloadOrder = 'ORDER BY `number` DESC';
      break;
  case 'random':
      $downloadOrder = 'ORDER BY RAND()';
      break;
  default:
      $downloadOrder = '';
}

$sqlTemplate = 'SELECT number
        FROM scrub
        INNER JOIN `ratedeck` ON SUBSTR(scrub.`number`, 1, 6) = ratedeck.`NPANXX`
        WHERE ratedeck.`Rate` <= %f
          %s
        ' . $downloadOrder . '
        INTO OUTFILE "%s"';
$typeCriteria = array();
if (!empty($wireless)) {
  $typeCriteria[] = 'ratedeck.Wireless = "x"';
}
if (!empty($landline)) {
  $typeCriteria[] = 'ratedeck.Landline = "x"';
}

$additionalCriteriaClause = !empty($typeCriteria)
  ? ' AND (' . implode(' OR ', $typeCriteria) . ')'
  : '';

$blacklistsClause = '';
foreach($blacklistsList as $token) {
    if (!empty($item['include_' . $token . '_dnc'])) {
        $blacklistsClause .= chr(10) . ' AND scrub.`number` NOT IN (SELECT `number` FROM `' . get_blacklist_tablename($token, $item['user_id']) . '`)';
    }
}

if (!empty($item['specific_states_list'])) {
  $statesArray = explode(',', $item['specific_states_list']);
  $statesListAsString = '"' . implode('", "', $statesArray) . '"';
  $additionalCriteriaClause .= chr(10) . sprintf(' AND SUBSTR(scrub.`number`, 1, 3) IN (SELECT `code` FROM `areacode` WHERE REPLACE(LCASE(`region`), " ", "_") IN (%s))', $statesListAsString);
}

$filename = $item['id'] . '.csv';
$fullname = TEMP_DIR . $filename;
@unlink($fullname);
$sql = sprintf($sqlTemplate, $max_price, $additionalCriteriaClause . $blacklistsClause, $fullname);
$db->query($sql);
new dBug(nl2br($sql));

$finalRowsCount = query('SELECT FOUND_ROWS()')->fetchColumn();
new dBug(nl2br($finalRowsCount));

// now the file
query('UPDATE `queue` SET `status`="success", final_rows_count=:final_rows_count, updated_at=NOW() WHERE id=:id', array(
    ':id' => $item['id'],
    ':final_rows_count' => $finalRowsCount
));


try {
  // export items matched the blacklists
  if (!empty($item['is_blacklisted_report_required'])) {
    $blacklistsReport = array();

    foreach ($blacklistsList as $blacklistName) {
        if (empty($item['include_' . $blacklistName . '_dnc'])) {
            new dBug(array('error' => 'Skipping'));
            continue;
        }
        $fullname = TEMP_DIR . $item['id'] . "_$blacklistName.csv";
        @unlink($fullname);
        $sqlTemplate = 'SELECT number
            FROM scrub
            INNER JOIN `ratedeck` ON SUBSTR(scrub.`number`, 1, 6) = ratedeck.`NPANXX`
            INNER JOIN `' . get_blacklist_tablename($blacklistName, $item['user_id']) . '` USING(`number`)
            WHERE ratedeck.`Rate` <= %f
              %s
            INTO OUTFILE "%s"';
        $sql = sprintf($sqlTemplate, $max_price, $additionalCriteriaClause, $fullname);
        $db->query($sql);
        new dBug(nl2br($sql));

        $rowsCount = (int)query('SELECT FOUND_ROWS()')->fetchColumn();
        query('UPDATE `queue` SET blacklist_'.$blacklistName.'_rows_count=:rows_count, updated_at=NOW() WHERE id=:id', array(
            ':id' => $item['id'],
            ':rows_count' => $rowsCount
        ));
        if (0 === $rowsCount) {
          @unlink($fullname);
        }
      }
  }
}
catch(Exception $e) {
  $errorMessage = $e->getMessage();
  error_log('Exception: ' . $e->getMessage());
}

