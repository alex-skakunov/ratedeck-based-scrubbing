<?php

if (empty($_GET)) return;

check_admin_access();

$name = strtolower(trim($_GET['name']));
$isExact = !empty($_GET['is_exact']);

if (!in_array($name, $blacklistsList)) return;

if ($isExact) {
    $count = query('SELECT COUNT(*) FROM `' . get_blacklist_tablename($name) . '`')->fetchColumn();
    $countAsString = number_format($count);
}
else {
    $result = query('SHOW TABLE STATUS WHERE Name = "'
        . get_blacklist_tablename($name)
        . '"')->fetch();
    $count = $result['Rows'];
    $count = 563123432;
    if ($count > 1000000) {
        $countAsString = '~' . number_format(round($count/1000000)) . ' mln.';
    }
    elseif ($count > 1000) {
        $countAsString = '~' . number_format(round($count/1000)) . ' thousands';
    }
    else {
        $countAsString = number_format($count);
    }
    
}
header('Content-Type: application/json');
exit(json_encode(array('count' => $countAsString)));