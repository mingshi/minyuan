<?php
/**
 * @FileName    :   config_development.php
 * @QQ          :   224156865
 * @date        :   2014/02/27 15:15:12
 * @link
 * @Auth        :   Mingshi <fivemingshi@gmail.com>
 */

Config::add(array(
    'db_physical' => array(
        'minyuan_server' => array(
            'write' => array(
                'host' => '127.0.0.1',
                'port' => 3306
            ),
            'read' => array(
                'host' => '127.0.0.1',
                'port' => 3306
            ),
            'db_user' => 'root',
            'db_pwd' => ''
        )
    ),
    'db_singles' => array(
        'ad_core' => array(
            'map' => 'minyuan_server',
            'db_name' => 'minyuan'
        ),
    ),
));

