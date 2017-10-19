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
use Workerman\Lib\Timer;

class Events
{

    public static $db = null;

    public static $redis = null;

    public static $onlineUsers = [];

    public static $TIMELINE_KEY = 'TIMELINE';

    public static $TIMER_SECONDS = 1;

    public static $MESSAGE_TYPE_INIT = 'init';

    public static $MESSAGE_TYPE_ACTION = 'action';

    public static function onWorkerStart($worker)
    {
        self::$db = new Workerman\MySQL\Connection('192.168.3.5', '3306', 'root', 'zhangpei', 'zhihu');
        self::$redis = new Predis\Client('tcp://192.168.3.5:6379');
        Timer::add(self::$TIMER_SECONDS, array(self::class, 'consume_actions'), true);
    }

    public static function onConnect($client_id)
    {
        echo "connection from $client_id\n";
        Gateway::sendToClient($client_id, json_encode(array(
            'type' => self::$MESSAGE_TYPE_INIT,
            'client_id' => $client_id
        )));
    }

    public static function onMessage($client_id, $message)
    {

    }

    public static function onClose($client_id)
    {

    }

    public static function consume_actions()
    {
        $actionsJson = self::$redis->lpop(self::$TIMELINE_KEY);
        if ($actionsJson) {
            $action = json_decode($actionsJson, true);
            $followerIds = self::get_user_followers($action['user_id']);
            foreach ($followerIds as $followerId) {
                self::pull_action($followerId['follower_id'], $action['action_id']);
            }
        }
    }

    public static function pull_action($userId, $actionId)
    {
        $insertId = self::$db->insert('timelines')->cols([
            'user_id' => $userId,
            'action_id' => $actionId,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ])->query();

        $isUserOnline = Gateway::isUidOnline($userId);
        if ($isUserOnline) {
            Gateway::sendToUid($userId, json_encode([
                'type' => self::$MESSAGE_TYPE_ACTION,
                'data' => [
                    'user_id' => $userId,
                    'action_id' => $actionId,
                ]
            ]));
        }
        return $insertId;
    }

    public static function get_user_followers($userId)
    {
        $result = self::$db->select('follower_id')->from('followers')->where("followed_id = $userId")->query();
        return $result;
    }
}
