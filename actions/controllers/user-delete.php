<?php

check_admin_access();

if (empty($_POST)) {
  return;
}

query('DELETE FROM user WHERE `id` = ' . (int)$_POST['id']);
header('HTTP/1.0 204 No Content');