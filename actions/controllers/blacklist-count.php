<?php

if (empty($_GET)) return;

$userId = $_SESSION['user']['id'];

$result = query('SHOW TABLE STATUS WHERE Name = "blacklist_user_' . $userId . '"')->fetch();
$count = $result['Rows'];
header('Content-Type: application/json');
exit(json_encode(array('count' => number_format($count))));