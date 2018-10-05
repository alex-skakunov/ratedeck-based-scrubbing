<?php

if (empty($_GET)) return;

$name = strtolower(trim($_GET['name']));

if (!in_array($name, $blacklistsList)) return;

$count = query('SELECT COUNT(*) FROM blacklist_' . $name)->fetchColumn();

header('Content-Type: application/json');
exit(json_encode(array('count' => number_format($count))));