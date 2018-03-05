<?php

include "preload.inc"; //this includes all necessary classes and configs

if(empty($attributes['page'])) {
  $attributes['page'] = 'ratedeck';
}
define('CURRENT_ACTION', strtolower($attributes['page']));
include ACTIONS_DIR . 'controllers/' . $attributes['page'] . '.php';

ob_start();
include ACTIONS_DIR . 'views/' . $attributes['page'] . '.php';
$renderedTemplate = ob_get_contents();
ob_end_clean();

ob_start();
include ACTIONS_DIR . 'layout.php';
$layout = ob_get_contents();
ob_end_clean();

echo str_replace('[template]', $renderedTemplate, $layout);