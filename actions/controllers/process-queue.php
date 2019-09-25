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

$fQuickCSV = new Quick_CSV_import;

$fQuickCSV->table_name = 'scrub';
$fQuickCSV->file_name = TEMP_DIR . $item['temp_filename'];
$fQuickCSV->use_csv_header = false;
$fQuickCSV->make_temporary = !true;
$fQuickCSV->table_exists = true;
$fQuickCSV->truncate_table = true;

try {
  $fQuickCSV->import();
}
catch(Exception $e) {
  query('UPDATE `queue` SET status="error", error_message=:error_message, updated_at=NOW() WHERE id=:id', array(
    ':id' => $item['id'],
    ':error_message' => 'File processing error'
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

$sqlTemplate = 'SELECT number
        FROM scrub
        INNER JOIN `ratedeck` ON SUBSTR(scrub.`number`, 1, 6) = ratedeck.`NPANXX`
        WHERE ratedeck.`Rate` <= %f
          %s
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
        $blacklistsClause .= chr(10) . ' AND scrub.`number` NOT IN (SELECT `number` FROM `' . get_blacklist_tablename($token) . '`)';
    }
}

if (!empty($item['specific_states_list'])) {
  $statesArray = explode(',', $item['specific_states_list']);
  $statesListAsString = '"' . implode('", "', $statesArray) . '"';
  $additionalCriteriaClause .= chr(10) . sprintf(' AND SUBSTR(scrub.`number`, 1, 3) IN (SELECT `code` FROM `areacode` WHERE REPLACE(LCASE(`region`), " ", "_") IN (%s))', $statesListAsString);
}

$filename = $item['id'] . '.csv';
$fullname = TEMP_DIR . $filename;
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
        $sqlTemplate = 'SELECT number
            FROM scrub
            INNER JOIN `ratedeck` ON SUBSTR(scrub.`number`, 1, 6) = ratedeck.`NPANXX`
            INNER JOIN `' . get_blacklist_tablename($blacklistName) . '` USING(`number`)
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

