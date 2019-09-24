<?php

check_admin_access();

if (empty($_POST)) {
  return;
}

$userId = $_SESSION['user']['id'];
query('DELETE FROM user WHERE `id` = ' . (int)$_POST['id']);
query("DROP TABLE IF EXISTS `blacklist_user_$userId`");

header('HTTP/1.0 204 No Content');