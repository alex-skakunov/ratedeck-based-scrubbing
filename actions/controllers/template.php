<?php

if (empty($_REQUEST)) {
    return;
}
// print_r($_REQUEST);

$id = (int)$_REQUEST['id'];

switch ($_REQUEST['method']) {
    case 'create':
        $fieldsList = (array)$_REQUEST['fields'];
        query('INSERT INTO template (title, settings) VALUES(:title, :settings)', array(
          ':title' => trim($_REQUEST['title']),
          ':settings' => json_encode($fieldsList)
        ));
        header('Content-Type: application/json');
        echo '{"id": ' . $db->lastInsertId() . '}';
        break;

    case 'update':
        $updateList = (array)$_REQUEST['fields'];
        $oldSettingsJson = query('SELECT settings FROM template WHERE id=' . $id)->fetchColumn();
        $oldSettings = json_decode($oldSettingsJson, 1);
        $newSettings = array_merge($oldSettings, $updateList);
        query('UPDATE template SET settings = :settings WHERE id = :id', array(
          ':id' => $id,
          ':settings' => json_encode($newSettings)
        ));
        break;

    case 'delete':
        query('DELETE FROM template WHERE id = :id', array(
          ':id' => $id
        ));
        break;
}
exit;