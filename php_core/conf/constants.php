<?php
if ( ! defined('STAT_ER_DATABASE')) {
    //数据库错误
    define('STAT_ER_DATABASE', 'stat_error_database');
}

if ( ! defined('STAT_ET_DB_CONNECT')) {
    //数据库连接时间
    define('STAT_ET_DB_CONNECT', 'stat_et_db_connect');
}

if ( ! defined('STAT_ET_DB_QUERY')) {
    //数据库查询耗时
    define('STAT_ET_DB_QUERY', 'stat_et_db_query');
}

if ( ! defined('STAT_ER_MEMCACHE')) {
    //memcache连接错误
    define('STAT_ER_MEMCACHE', 'stat_error_memcache');
}

if ( ! defined('STAT_ET_MEMCACHE_CONNECT')) {
    //Memcache连接时间
    define('STAT_ET_MEMCACHE_CONNECT', 'stat_et_memcache_connect');
}

if ( ! defined('STAT_ER_REDIS')) {
    define('STAT_ER_REDIS', 'stat_er_redis');
}

if ( ! defined('STAT_ET_REDIS')) {
    define('STAT_ET_REDIS', 'stat_et_redis');
}

define('FORM_HASH', '__form_hash');
define('FORM_HASH_NAME', '__form_hash_name');

define('TIME_ZERO', '0000-00-00 00:00:00');
