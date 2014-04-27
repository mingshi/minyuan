<?php
function error_report($errno, $msg, $smsMsg = '')
{
    log_message('error_report:'.$msg, LOG_ERR);

    //发送短信
    if (empty($smsMsg)) {
        $smsMsg = $msg;
    }

    alert_sms($smsMsg);
}

define('APP_PATH_LOG', dirname(__FILE__) . '/application/logs');
define('APP_PATH_CONF', dirname(__FILE__) . '/application/third_party/conf');
#define('APP_LOG_SCRIBE_CATEGORY', 'default');

require_once(dirname(__FILE__) . '/../php_core/bootstrap.php');

//读写主库
Db_Model::setForceReadOnMater();

sp_load_helper(array('array', 'http', 'parameter', 'string', 'date'));

Session::$config = array(
    'cookieName' => substr(ENV, 0, 1) . '_session',
    'domain' => 'ggxt.net',
);

if (ENV == 'DEVELOPMENT') {
    error_reporting(E_ALL);
    ini_set('display_errors', 'on');
    ini_set('track_errors', 1);
} else {
    error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
}
