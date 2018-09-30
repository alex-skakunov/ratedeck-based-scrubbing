<?php
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
ignore_user_abort(true);

ini_set("session.use_trans_sid", 1);
ini_set("arg_separator.output", "&amp;");
ini_set("display_errors", 1);
ini_set("track_errors", 1);
ini_set("error_log", 'error.log');
ini_set("memory_limit", "4G");
ini_set("post_max_size", "4G");
ini_set("upload_max_filesize", "4G");

//database settings
define("DB_HOST"    , 'localhost');
define("DB_LOGIN"   , 'root');
define("DB_PASSWORD", '');
define("DB_NAME"    , 'ratedeck');
define('DB_TABLE'   , 'ratedeck');

define("CURRENT_DIR"  , getcwd() . DIRECTORY_SEPARATOR );   //stand-alone classes
define("CLASSES_DIR"  , CURRENT_DIR . 'classes' .  DIRECTORY_SEPARATOR);   //stand-alone classes
define("ACTIONS_DIR"  , CURRENT_DIR . 'actions' .  DIRECTORY_SEPARATOR);   //controllers processing sumbitted data and preparing output
define("TEMP_DIR",  CURRENT_DIR . 'temp' . DIRECTORY_SEPARATOR . 'csv' . DIRECTORY_SEPARATOR); //all uploaded files will be copied here so that they won't be deleted between requests
//define('TEMP_DIR', '/var/lib/mysql-files/');