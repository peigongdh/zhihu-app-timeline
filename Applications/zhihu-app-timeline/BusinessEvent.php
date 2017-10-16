<?php
/**
 * Created by PhpStorm.
 * User: zhangpei-home
 * Date: 2017/10/17
 * Time: 0:07
 */

use Workerman\Lib\Timer;

class BusinessEvent
{
    public static $db = null;

    public static $redis = null;

    public static $TIMELINE_KEY = 'TIMELINE';

    public static function onWorkerStart($businessworker)
    {
        self::$db = new Workerman\MySQL\Connection('192.168.3.5', '3306', 'root', 'zhangpei', 'zhihu');
        self::$redis = new Predis\Client('tcp://192.168.3.5:6379');
        Timer::add(3, array(self::class, 'consume_actions'));
    }

    public static function onMessage($client_id, $message)
    {
        return;
    }

    public static function consume_actions()
    {
        while (true) {
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