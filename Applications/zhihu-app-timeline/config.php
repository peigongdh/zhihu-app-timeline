<?php
/**
 * Created by PhpStorm.
 * User: zhangp
 * Date: 2017/10/23
 * Time: 15:17
 */

return [

    'mysql' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'db_username' => 'zhangpei',
        'db_password' => 'zhangpei0638',
        'db_name' => 'zhihu',
    ],

    'redis' => [
        'host' => '127.0.0.1',
        'port' => '6379',
    ],

    'ssl' => [
        'local_cert' => '/etc/nginx/cert/uhihz.peigongdh.com/214302588940425.pem',
        'local_pk' => '/etc/nginx/cert/uhihz.peigongdh.com/214302588940425.key'
    ]

];
