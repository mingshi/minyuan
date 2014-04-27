<?php
Config::add(array(
    'db_physical' => array( //physical master-slave shard configuration
        0 => array(
            'write' => array(
                'host' => 'db.ggxt.net',
                'port' => 3306 
            ),
            'read' => array(
                'host' => 'db.ggxt.net',
                'port' => 3306 
            ),
            'db_user' => 'root',
            'db_pwd' => 'thisisme!' 
        ),
        1 => array(
            'write' => array(
                'host' => '192.168.0.203',
                'port' => 3307 
            ),
            'read' => array(
                'host' => '192.168.0.203',
                'port' => 3307 
            ),
            'db_user' => 'db_52game',
            'db_pwd' => '52game_db#@!' 
        ),
        2 => array(
            'write' => array(
                'host' => 'slave.db.ggxt.net',
                'port' => 3306
            ),
            'read' => array(
                'host' => 'db.ggxt.net',
                'port' => 3306 
            ),
            'db_user' => 'wukong',
            'db_pwd' => 'pwd4wukong#@!' 
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
        'hzeng_backend' => array(
            'map'   =>  0,
            'db_name'   =>  'hzeng_backend',
        ),
        'game_stat' => array(
            'map' => 1,
            'db_name' => 'game_stat',
        ),
        'wukong' => array(
            'map' => 2,
            'db_name' => 'wukong',
        ),
    ),
    'cache_physical' => array( 
        0 => array(
            'host' => '192.168.0.196',
            'port' => '11211'
        ),
    ),
    'cache_cluster' => array(
        'default' => array(0) 
    ),
    'redis_physical' => array(
        0 => array(
            'host' => '127.0.0.1',
            'port' => '6379' 
        ),
        1 => array(
            'host' => '192.168.0.197',
            'port' => '6379' 
        ) 
    ),
    'redis_single' => array(
        #0.0, 第一个0指映射到的redis_physical的配置，第二个0指选择redis的0号数据库
        #默认redis分16个数据库，即0-15
        'default' => '0.0', 
        'stat' => '1.0', 
    ),
    /*
    'Scribe' => array(
        'host' => '',
        'port' => '1463' 
    ),
    */
));
