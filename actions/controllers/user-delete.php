<?php

check_admin_access();

if (empty($_POST)) {
  return;
}

$userId = (int)$_POST['id'];
$currentUserId = $_SESSION['user']['id'];

erase_user_queue($currentUserId);
if ($currentUserId == $userId) {
  return $errorMessage = 'You cannot kill yourself.';
}

query('DELETE FROM `user` WHERE `id` = ' . $userId);
query("DROP TABLE IF EXISTS `blacklist_user_$userId`");

header('HTTP/1.0 204 No Content');