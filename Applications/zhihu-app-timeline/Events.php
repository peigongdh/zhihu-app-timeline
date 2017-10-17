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

    public static $TIMELINE_KEY = 'TIMELINE';

    public static function onWorkerStart($worker)
    {
        echo $worker->id . " start \n";
        self::$db = new Workerman\MySQL\Connection('192.168.3.5', '3306', 'root', 'zhangpei', 'zhihu');
        self::$redis = new Predis\Client('tcp://192.168.3.5:6379');
        // Timer::add(3, array(self::class, 'consume_actions'));
    }

    public static function onConnect($client_id)
    {
        echo $client_id . " connect \n";
        Gateway::sendToClient($client_id, json_encode(array(
            'type' => 'init',
            'client_id' => $client_id
        )));
    }

    public static function onMessage($client_id, $message)
    {
        echo $client_id . " on message \n";
    }

    public static function onClose($client_id)
    {

    }

    public static function consume_actions()
    {
        while (true) {
            echo "loop \n";
            $actionsJson = self::$redis->lpop(self::$TIMELINE_KEY);
            if ($actionsJson) {
                $action = json_decode($actionsJson, true);
                $followerIds = self::get_user_followers($action['user_id']);
                foreach ($followerIds as $followerId) {
                    self::pull_action($followerId['follower_id'], $action['action_id']);
                }
            }
            sleep(1);
        }

    }

    public static function pull_action($userId, $actionId)
    {
        $insertId = self::$db->insert('timelines')->cols([
            'user_id' => $userId,
            'action_id' => $actionId,
        ])->query();
        return $insertId;
    }

    public static function get_user_followers($userId)
    {
        $result = self::$db->select('follower_id')->from('followers')->where("followed_id = $userId")->query();
        return $result;
    }
}
