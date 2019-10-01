<?php

if (empty($_GET)) return;

$userId = $_SESSION['user']['id'];

$count = query('SELECT COUNT(*) FROM `blacklist_user_' . $userId . '`')->fetchColumn();
header('Content-Type: application/json');
exit(json_encode(array('count' => number_format($count))));