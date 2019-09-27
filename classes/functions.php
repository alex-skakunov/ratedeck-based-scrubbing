<?php
use Zend\Mail\Message;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

function sendEmail($subject, $mess, $to=null, $toName=null) {
    if (empty($to)) {
       $to = REPORTS_EMAIL_ADDRESS;
    }

    $html = new MimePart($mess);
    $html->type = "text/html";

    $body = new MimeMessage();
    $body->setParts(array($html));

    $message = new Message();
    $message->addTo($to, $toName);
    $message->addFrom('Service Report <'.SMTP_USERNAME.'>');
    $message->setSubject($subject);
    $message->setBody($body);

    $transport = new SmtpTransport();
    $options   = new SmtpOptions([
        'name'              => 'gmail.com',
        'host'              => 'smtp.gmail.com',
        'port'              => 587,
        // Notice port change for TLS is 587
        'connection_class'  => 'plain',
        'connection_config' => [
            'username' => SMTP_USERNAME,
            'password' => SMTP_PASSWORD,
            'ssl'      => 'tls',
        ],
    ]);
    $transport->setOptions($options);
    return $transport->send($message);
}


//Returns the first non-empty value in the list, or an empty line if there are no non-empty values.
function coalesce()
{ 
  for($i=0; $i < func_num_args(); $i++)
  {
    $arg = func_get_arg($i);
    if(!empty($arg))
      return $arg;
  }
  return "";
}

//go to new location (got from Fusebox4 source)
function Location($URL, $addToken = 1)
{
  $questionORamp = (strstr($URL, "?"))?"&":"?";
  $location = ( $addToken && substr($URL, 0, 7) != "http://" && defined('SID') ) ? $URL.$questionORamp.SID : $URL; //append the sessionID ($SID) by default
  //ob_end_clean(); //clear buffer, end collection of content
  if(headers_sent()) {
    print('<script type="text/javascript" type="text/javascript">( document.location.replace ) ? document.location.replace("'.$location.'") : document.location.href = "'.$location.'";</script>'."\n".'<noscript><meta http-equiv="Refresh" content="0;URL='.$location.'" /></noscript>');
  } else {
    header('Location: '.$location); //forward to another page
    exit; //end the PHP processing
  }
}

//checks that we have all modules we need or exit() will be called
function check_necessary_functions()
{ 
  for($i=0; $i < func_num_args(); $i++)
  {
    $func_name = func_get_arg($i);
    if( !function_exists($func_name) )
    {
      exit ( "Function [" . $func_name . "] is not accessable. Please check that correspondent PHP module is installed at your web-server." );
    }
  }
  return true;
}

//writes data in a file
function write_file($filename, $data)
{
  $fp = fopen($filename, 'w');
  if($fp)
  {
    fwrite($fp, $data);
    fclose($fp);
    return true;
  }
  return false;
}

//writes data in the end of a file
function append_file($filename, $data)
{
  $fp = fopen($filename, 'a');
  if($fp)
  {
    fwrite($fp, $data);
    fclose($fp);
    return true;
  }
  return false;
}

//OS independent deletion of a file
function delete_file($filename)
{
  if(file_exists($filename))
  {
    $os = php_uname();
    if(stristr($os, "indows")!==false)
      return exec("del ".$filename);
    else
      return unlink($filename);
  }
  return true;
}


//returns all fields of [tableName]
function get_table_fields($db, $tableName )
{
  $arrFields = array();
  if( empty($tableName) )
  {
    return false;
  }
  
  $db->query("SHOW TABLES LIKE '".$tableName."'");
  
  if( 0 == $db->getRowsCount())
  {
    return false;
  }
  
  $db->query("SHOW COLUMNS FROM ".$tableName);
  
  
  while( $row = mysql_fetch_array($db->fResult) )
  {
    $arrFields[] = trim( $row[0] );
  }
  
  return $arrFields;
}

function detect_line_ending($file)
{
    $s = file_get_contents($file);
    if( empty($s) ) return null;
    
    if( substr_count( $s,  "\r\n" ) ) return '\r\n'; //Win
    if( substr_count( $s,  "\r" ) )   return '\r';   //Mac
    return '\n'; //Unix
}

function startsWith( $str, $token ) {
    $_token = trim( $token );
    $_str = trim( $str );
    if( empty( $_token ) || empty( $str ) ) return false;
    
    $tokenLen = strlen( $_token );
    // $tokenFromStr = substr( $_str, 0, $tokenLen );
    // return strtolower( $_token ) == strtolower( $tokenFromStr );
    
    return !strncasecmp($_str, $token, $tokenLen );
}

function check_admin_access() {
  if ('admin' != $_SESSION['user']['level']) {
    header('Location: index.php');
    exit;
  }
}

function erase_user_queue($userId) {
  global $blacklistsList;
  $filesToDelete = query(
    'SELECT id, temp_filename FROM `queue` WHERE `user_id`=' . $userId
    )->fetchAll(PDO::FETCH_ASSOC);
  foreach ($filesToDelete as $file) {
      @unlink(TEMP_DIR . $file['temp_filename']);
      @unlink(TEMP_DIR . $file['id'] . '.csv');
      foreach ($blacklistsList as $token) {
          @unlink(TEMP_DIR . $file['id'] . '_' . $token . '.csv');
      }
  }
  query('DELETE FROM `queue` WHERE `user_id`=' . $userId);
}

function get_blacklist_tablename($token, $userId=null) {
    if (OWN == $token) {
        if (empty($userId)) {
          $userId = $_SESSION['user']['id'];
        }

        if (empty($userId)) {
          throw new Exception('Empty user');
        }

        return 'blacklist_user_' . $userId;
    }
    return 'blacklist_' . $token;
}

function query($sql, $replacements=null) {
    global $db;
    $stmt = $db->prepare($sql);
    if (false === $stmt->execute($replacements)) {
      // new dBug($sql);
      error_log(print_r($stmt->errorInfo(), 1));
      throw new Exception($stmt->errorInfo()[2], $stmt->errorInfo()[1]);
    }
    return $stmt;
}