<?php

include "config.php"; //load database settings, folders paths and such stuff

require_once 'vendor/autoload.php';

set_include_path( CLASSES_DIR );
require_once "Quick_CSV_import.class.php";
require_once 'CSV.class.php';
require_once "functions.php";
require_once "dBug.php";

if( !is_writable( TEMP_DIR ) )
{
  exit ( "Temporary folder must be writable: <code>".TEMP_DIR."</code>" );
}

if( !is_writable( SESSIONS_DIR ) )
{
  exit ( "Sessions folder must be writable: <code>".SESSIONS_DIR."</code>" );
}

if ( -1 == version_compare( PHP_VERSION, '4.1.0' ) ) {
    exit ('Please, you PHP version greater than 4.1.0 - files uploads will not work properly');
}

register_shutdown_function("fatal_handler");
function fatal_handler() {
    error_log('Caught an error: ' . print_r(error_get_last(), 1));
}

//connect to database
$dsn = sprintf('mysql:host=%s;dbname=%s', DB_HOST, DB_NAME);
$options = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    PDO::MYSQL_ATTR_LOCAL_INFILE => true,
); 
$db = new PDO($dsn, DB_LOGIN, DB_PASSWORD, $options);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

if(empty($db))
{
  exit("Cannot connect to database");
}


$blacklistsList = array(
    'lawsuits',
    'master',
    OWN // this one might be replaced by user id to refer their own blacklist
);
if( !ini_get("file_uploads") ) //check whether administrator must tune PHP
{
  exit ( "PHP directive [file_uploads] must be turned ON" );
}

ini_set('auto_detect_line_endings', 1);

session_save_path(SESSIONS_DIR);
session_start();
setcookie(session_name(),session_id(),time() + SESSION_TTL, "/");

$uploadErrors = array(
    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
    UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.',
    -1 => 'File is empty. Try again.'
);
