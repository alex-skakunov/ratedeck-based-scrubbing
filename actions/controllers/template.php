<?php

if (empty($_REQUEST)) {
    return;
}
$id = (int)$_REQUEST['id'];
$userId = $_SESSION['user']['id'];

switch ($_REQUEST['method']) {
    case 'create':
        $fieldsList = (array)$_REQUEST['fields'];
        query('INSERT INTO `template` (`user_id`, `title`, `settings`) VALUES(:user_id, :title, :settings)', array(
          ':user_id' => $userId,
          ':title' => trim($_REQUEST['title']),
          ':settings' => json_encode($fieldsList)
        ));
        header('Content-Type: application/json');
        echo '{"id": ' . $db->lastInsertId() . '}';
        break;

    case 'update':
        $updateList = (array)$_REQUEST['fields'];
        $oldSettingsJson = query('SELECT settings FROM template WHERE `user_id` = :user_id AND id= :id', array(
          ':user_id' => $userId,
          ':id' => $id
        ))->fetchColumn();
        $oldSettings = json_decode($oldSettingsJson, 1);
        $newSettings = array_merge($oldSettings, $updateList);
        query('UPDATE template SET settings = :settings WHERE `user_id` = :user_id AND id = :id', array(
          ':user_id' => $userId,
          ':id' => $id,
          ':settings' => json_encode($newSettings)
        ));
        break;

    case 'delete':
        query('DELETE FROM `template` WHERE `user_id` = :user_id AND `id` = :id', array(
          ':user_id' => $userId,
          ':id' => $id
        ));
        break;
}
exit;