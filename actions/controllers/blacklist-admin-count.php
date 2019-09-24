<?php

if (empty($_GET)) return;

check_admin_access();

$name = strtolower(trim($_GET['name']));

if (!in_array($name, $blacklistsList)) return;

$result = query('SHOW TABLE STATUS WHERE Name = "blacklist_' . $name . '"')->fetch();
$count = $result['Rows'];
header('Content-Type: application/json');
exit(json_encode(array('count' => number_format($count))));