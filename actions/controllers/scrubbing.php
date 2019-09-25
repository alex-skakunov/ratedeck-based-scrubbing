<?php

$userId = $_SESSION['user']['id'];

if (!empty($_GET['erase_queue'])) {
  erase_user_queue($userId);
  header('Location: ?page=scrubbing');
  exit;
}

$userRecord = query(
  'SELECT `max_price`
   FROM `user`
   WHERE `id` = ' . $userId)->fetch();

$wireless = 1;
$landline = 1;
$areacodes_all = empty($_POST['areacode']);

$recordset = query('SELECT * FROM `queue` WHERE `user_id` = '.$userId.' ORDER BY `id` DESC')->fetchAll(PDO::FETCH_ASSOC);
$theLastQueuedItem = query('SELECT * FROM `queue` WHERE `user_id` = '.$userId.' AND `filename` <> "" AND `filename` IS NOT NULL ORDER BY `id` DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);

$_areacodeList = query('SELECT DISTINCT `region` FROM `areacode` ORDER BY `region` ASC')->fetchAll(PDO::FETCH_ASSOC);
// need to rely on states names and not on the code, since there are multiple codes for some states
$areacodeList = array();
foreach($_areacodeList as $areacode) {
  $token = str_replace(' ', '_', strtolower($areacode['region']));
  $areacodeList[$token] = $areacode['region'];
}

$templates = query('SELECT * FROM `template` WHERE `user_id` = '.$userId)->fetchAll(PDO::FETCH_ASSOC);

if ('admin' == $_SESSION['user']['level']) {
  include_once 'scrubbing-admin.php';
}
else {
  include_once 'scrubbing-user.php';
}