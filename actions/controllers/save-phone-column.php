<?php
if (empty($_POST)) return;

$userId = $_SESSION['user']['id'];
$id = (int)$_POST['id'];
$index = $_POST['value'];
$downloadScope = $_POST['download_scope'];

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

query('UPDATE `queue` SET `status` = "queued", `selected_column_index`=:index, `download_scope`=:scope WHERE `id`=' . $id, [
    ':index'    => $index,
    ':scope'    => $downloadScope == 'file' ? 'file' : 'column',
]);

header('Content-Type: application/json');
exit(json_encode(array('success' => true)));