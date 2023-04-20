<?php

require_once './common/Config.php';
require_once './common/MyRedis.php';
require_once './common/Log.php';
require_once 'Main.php';

class SupportContext
{
    private $redis;
    public function __construct()
    {
        $this->redis = \common\MyRedis::getInstance();
    }

    public function handle($wxid, $ques, $reply = '')
    {
        $isSupportContext = \common\Config::IS_SUPPORT_CONTEXT;

        if (!$isSupportContext) {
            return [
                'role' => 'user',
                'content' => $ques
            ];
        }

        try {
            $context = $this->redis->get($wxid);

            if (!$context) {
                $context = [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $ques
                    ]
                ];
            } else {
                $context = json_decode($context, true);

                if (!$reply) {
                    $context[] = [
                        'role' => 'user',
                        'content' => $ques
                    ];
                } else {
                    $context[] = [
                        'role' => 'assistant',
                        'content' => $reply
                    ];
                }
            }

            $this->redis->set($wxid, json_encode($context), \common\Config::CONTEXT_CACHE_TIME);
        } catch (\Exception $e) {
            \common\Log::save($e->getMessage(), 'redis');
            return [
                'role' => 'user',
                'content' => $ques
            ];
        }

        return $context;
    }

    public function judgeTokens($wxid, $answer): bool
    {
        $totalTokens = $answer['usage']['total_tokens'];

        if ($totalTokens > \common\Config::MAX_TOKENS) {
            try {
                $this->redis->del($wxid);
            } catch (RedisException $e) {
                \common\Log::save($e->getMessage(), 'redis');
            }
            return true;
        }

        return false;
    }

    public function startNewChat($data)
    {
//        try {
//            $limit = $this->redis->get('rate_limit');
//
//            if ($limit) {
//                return 1;
//            }
//
//            $this->redis->set('rate_limit', 1, 1);
//        } catch (RedisException $e) {
//            \common\Log::save($e->getMessage(), 'redis');
//        }

        $msg = $data['data']['data']['msg'];

        // 判断消息内容是不是“开启新聊天”
        if (mb_strpos($msg, \common\Config::START_NEW_CHAT) === false) {
            return true;
        }

        if ($data['event'] == 10008) {
            $key = $data['data']['data']['fromWxid'] . ':' . $data['data']['data']['finalFromWxid'];
        } elseif ($data['event'] == 10009) {
            $key = $data['data']['data']['fromWxid'];
        }

        if (!isset($key)) {
            return true;
        }

        try {
            $this->redis->del($key);

            $main = new Main();

            if ($data['event'] == 10008) {
                $main->sendMsg($data['data']['data']['fromWxid'], $data['data']['data']['finalFromWxid'], \common\Config::SPECIFIC_REPLY_TEXT[886]);
            } elseif ($data['event'] == 10009) {
                $main->sendMsgToPerson($data['data']['data']['fromWxid'], \common\Config::SPECIFIC_REPLY_TEXT[886]);
            }

            return 1;
        } catch (RedisException $e) {
            \common\Log::save($e->getMessage(), 'redis');
        }

        return true;
    }
}