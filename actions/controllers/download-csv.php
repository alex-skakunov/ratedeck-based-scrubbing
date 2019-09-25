<?php
if (empty($_GET)) return;

header('Cache-Control: no-cache');
header('Cache-Control: private');

$userId = $_SESSION['user']['id'];
$id = (int)$_GET['id'];

if (empty($id)) {
  header("HTTP/1.0 204 No Content");
  exit;
}

$queueItem = query('SELECT `user_id` FROM `queue` WHERE `id`=' . $id)->fetch();
if (empty($queueItem)) {
  header("HTTP/1.0 204 No Content");
  exit;
}

if ($queueItem['user_id'] != $userId) {
  header("HTTP/1.0 204 No Content");
  exit;
}

$fileSuffix = '';
if (!empty($_GET['blacklist'])) {
  $blacklist = strtolower($_GET['blacklist']);
  if (in_array($blacklist, $blacklistsList)) {
    $fileSuffix = '_' . $blacklist;
  }
}
$filename = $id . $fileSuffix . '.csv';
$fullpath = TEMP_DIR . $filename;

if (!file_exists($fullpath)) {
  header("HTTP/1.0 204 No Content");
  exit;
}

header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename="'.$filename.'";');
readfile($fullpath);