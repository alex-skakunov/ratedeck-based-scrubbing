<?php

include "preload.php"; //this includes all necessary classes and configs

if(empty($_REQUEST['page'])) {
  $_REQUEST['page'] = 'blacklist';
}
$_REQUEST['page'] = strtolower(trim($_REQUEST['page']));

if (empty($_SESSION['authenticated']) 
        && !in_array(
                $_REQUEST['page'],
                array(
                    'login',
                    'user-forgot-password',
                    'user-password-recovery'
                )
            )
  ) {
    header('Location: index.php?page=login');
}

define('CURRENT_ACTION', $_REQUEST['page']);

if (!file_exists(ACTIONS_DIR . 'controllers/' . $_REQUEST['page'] . '.php')) {
    header('Location: index.php');
}

include ACTIONS_DIR . 'controllers/' . $_REQUEST['page'] . '.php';

if (!file_exists(ACTIONS_DIR . 'views/' . $_REQUEST['page'] . '.php')) {
    return;
}

ob_start();
include ACTIONS_DIR . 'views/' . $_REQUEST['page'] . '.php';
$renderedTemplate = ob_get_contents();
ob_end_clean();

ob_start();
include ACTIONS_DIR . 'layout.php';
$layout = ob_get_contents();
ob_end_clean();

echo str_replace('[template]', $renderedTemplate, $layout);