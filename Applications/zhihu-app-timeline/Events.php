<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

use \GatewayWorker\Lib\Gateway;


class Events
{

    public static function onWorkerStart($worker)
    {

    }

    public static function onConnect($client_id)
    {
        // 向当前client_id发送数据 
        Gateway::sendToClient($client_id, "Hello $client_id\n");
        // 向所有人发送
        Gateway::sendToAll("$client_id login\n");
    }

    public static function onMessage($client_id, $message)
    {
        // 向所有人发送 
        // Gateway::sendToAll("$client_id said $message");

        // 发来的消息
        $commend = trim($message);
        if ($commend !== 'get_user_list') {
            Gateway::sendToClient($client_id, "unknown command\n");
            return;
        }
        // 使用数据库实例
        $result = self::$db->select('*')->from('users')->where('id>2')->query();
        // 打印结果

        return Gateway::sendToClient($client_id, json_encode($result));
    }

    public static function onClose($client_id)
    {
        // 向所有人发送
        GateWay::sendToAll("$client_id logout");
    }
}
