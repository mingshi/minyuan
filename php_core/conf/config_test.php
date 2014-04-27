<?php
Config::add(array(
    'db_physical' => array( //physical master-slave shard configuration
        0 => array(
            'write' => array(
                'host' => 'localhost',
                'port' => 3306 
            ),
            'read' => array(
                'host' => 'localhost',
                'port' => 3306 
            ),
            'db_user' => 'root',
            'db_pwd' => '' 
        ),
    ),
    'db_cluster' => array(),
    'db_singles' => array(
        'ad_report' => array(
            'map' => 0,
            'db_name' => 'ad_report' 
        ),
        'ad_core' => array(
            'map' => 0,
            'db_name' => 'ad_core' 
        ),
        'ad_rt' => array(
            'map' => 0,
            'db_name' => 'ad_rt' 
        ),
        'ad_cpa' => array(
            'map' => 0,
            'db_name' => 'ad_cpa',
        ),
        'ad_box' => array(
            'map' => 0,
            'db_name' => 'ad_box',
        ),
    ),
    'cache_physical' => array(),
    'cache_cluster' => array(
        'default' => array() 
    ),
    'redis_physical' => array(
        0 => array(
            'host' => '127.0.0.1',
            'port' => '6379' 
        ) 
    ),
    'redis_single' => array(
        #0.0, 第一个0指映射到的redis_physical的配置，第二个0指选择redis的0号数据库
        #默认redis分16个数据库，即0-15
        'default' => '0.0', 
    ),
    /*
    'Scribe' => array(
        'host' => '',
        'port' => '1463' 
    ),
    */
));
