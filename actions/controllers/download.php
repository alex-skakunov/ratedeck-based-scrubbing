<?php

$max_price = (float)$_GET['max_price'];
$wireless = !empty($_GET['wireless']) ? 1 : 0;
$landline = !empty($_GET['landline']) ? 1 : 0;
$areacodes = (array)$_GET['areacode'];
$zip = !empty($_GET['zip']);

/* same as query in scrubbing.php, but 'number' instead of 'COUNT(*) */
$sqlTemplate = 'SELECT number
        FROM scrub
        INNER JOIN `ratedeck` ON SUBSTR(scrub.number, 1, 6) = ratedeck.NPANXX
        WHERE ratedeck.Rate <= %f
          %s
          AND scrub.`number` NOT IN (SELECT `number` FROM `blacklist`)
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

$path = TEMP_DIR . '/';
$filename = 'export-' . date('Y-m-d-H-i-s-') . rand(1, 1000) . '.csv';
$fullname = $path . $filename;
$sql = sprintf($sqlTemplate, $max_price, $additionalCriteriaClause, $fullname);
$res = $db->query($sql);

if($zip) {
    $zip = new ZipArchive;
    if(true === ($zip->open($fullname . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE))){
	$zip->addFile($fullname, $filename);
	$zip->close();
	unlink($filename); //remove old CSV
	$filename .= '.zip';
	$fullname .= '.zip';
	header('Content-Type: application/zip');
    }
}
else {
    header('Content-Type: text/csv');
}
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($fullname));

ob_clean();
flush();
readfile($fullname);
unlink($fullname);
exit;
