<?php
 
date_default_timezone_set('Asia/Shanghai');

if (!defined('PATH_ROOT')) {
    define('PATH_ROOT', dirname(__FILE__));
}

if (!defined('DS')) {
    define('DS', '/');
}

if (!defined('PATH_CONF')) {
    define('PATH_CONF', PATH_ROOT.DS.'conf');    
}

if (!defined('PATH_LIB')) {
    define('PATH_LIB', PATH_ROOT.DS.'lib');
}

if (!defined('PATH_LOG')) {
    define('PATH_LOG', PATH_ROOT.DS.'logs');
}

if (!defined('PATH_FONT')) {
    define('PATH_FONT', PATH_ROOT.DS.'font');
}

if ( ! defined('ALLOW_SMS')) {
    define('ALLOW_SMS', FALSE);
}

require_once(PATH_CONF.DS.'config.php');
require_once(PATH_LIB.DS.'common.php');

function sp_autoload ($class)
{
    static $lookUpPath = array();
    
    if (trigger_event('on_autoload', $class)) {
        return;    
    }
    
    if (empty($lookUpPath)) {
        $lookUpPath = array(PATH_LIB);
        if (defined('APP_PATH_LIB')) {
            $lookUpPath = array_merge($lookUpPath, explode(';', APP_PATH_LIB));
        }
    }
    
    $paths = explode('_', $class);
    $filename = array_pop($paths);
    $segments = array();
    $segments[] = implode(DS, $paths);
    $segments[] = $filename.'.php';
    
    array_unshift($segments, '');
    foreach ($lookUpPath as $libPath) {
        $segments[0] = $libPath;
        $path = implode(DS, array_filter($segments));
        if (file_exists($path)) {
            require($path);
            trigger_event('on_load_class_succ', array($class));
            return;
        }
    }
}

function sp_load_helper($name)
{
    if ( ! is_array($name)) {
        $name = array($name);
    }
    
    foreach ($name as $filename) {
        require_once(PATH_LIB . DS . 'helper' . DS . $filename . '.php');
    }
}

spl_autoload_register('sp_autoload');

if (!defined('LOG_SCRIBE_CATEGORY')) {
    define('LOG_SCRIBE_CATEGORY', 'DEFAULT');
}
