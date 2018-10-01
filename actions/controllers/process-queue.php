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

$fQuickCSV->import();


if( !empty($fQuickCSV->error) )
{
    echo $fQuickCSV->error;
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
        INNER JOIN `ratedeck` ON SUBSTR(scrub.number, 1, 6) = ratedeck.NPANXX
        WHERE ratedeck.Rate <= %f
          %s
          AND scrub.`number` NOT IN (SELECT `number` FROM `blacklist_'.$item['blacklist_type'].'`)
        INTO OUTFILE "%s"';
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

if (!empty($areacodes)) {
  $statesList = '"' . implode('", "', $areacodes) . '"';
  $additionalCriteriaClause .= sprintf('AND SUBSTR(scrub.`number`, 1, 3) IN (SELECT `code` FROM `areacode` WHERE REPLACE(LCASE(`region`), " ", "_") IN (%s))', $statesList);
}

$filename = $item['id'] . '.csv';
$fullname = TEMP_DIR . $filename;
$sql = sprintf($sqlTemplate, $max_price, $additionalCriteriaClause, $fullname);
$res = $db->query($sql);
new dBug($sql);
$finalRowsCount = query('SELECT FOUND_ROWS()')->fetchColumn();


query('UPDATE `queue` SET `status`="success", final_rows_count=:final_rows_count, updated_at=NOW() WHERE id=:id', array(
    ':id' => $item['id'],
    ':final_rows_count' => $finalRowsCount
));
