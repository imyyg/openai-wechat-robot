<?php

require_once 'OpenAi.php';
require_once './common/Config.php';
require_once './common/HttpRequest.php';
require_once './common/Log.php';
require_once './common/MyRedis.php';
require_once 'SupportContext.php';

class Main
{
    /**
     * @var OpenAi
     */
    private $openAi;
    private $supportContext;
    public function __construct()
    {
        $this->openAi = new OpenAi();
        $this->supportContext = new SupportContext();
    }

    public function reply($data)
    {
        $data = json_decode($data, true);

        if (!$data) {
            return $this->response();
        }

        $res = $this->supportContext->startNewChat($data);

        if ($res === 1) {
            return $this->response();
        }

        if ($data['event'] == 10008 && \common\Config::REPLY_GROUP_SWITCH) {
            return $this->replyGroup($data);
        } elseif ($data['event'] == 10009 && \common\Config::REPLY_PERSON_SWITCH) {
            if (in_array($data['data']['data']['fromWxid'], \common\Config::REPLY_PERSON_WXID)) {
                return $this->replyPerson($data);
            }
        }

        return $this->response();
    }

    public function replyGroup($data)
    {

        if (!is_array($data)) {
            $data = json_decode($data, true);

            if (!$data) {
                return $this->response();
            }
        }

        if ($data['event'] != 10008) {
            return $this->response();
        }

        $data = $data['data'];

        if (substr($data['timestamp'], 0, 10) < time() - 60) {
            return $this->response();
        }

        $data = $data['data'];

        if ($data['fromType'] != 2 || $data['msgType'] != 1) {
            return $this->response();
        }

        if (!$data['atWxidList'] || !in_array(\common\Config::USE_WECHAT, $data['atWxidList'])) {
            return $this->response();
        }

//        $this->sendMsg($data['fromWxid'], $data['finalFromWxid'], \common\Config::SPECIFIC_REPLY_TEXT[321]);

        $msg = preg_replace('/^@\S+\s+/', '', $data['msg']);

        $this->log($msg, 'msg');

        $msgFormat = $this->supportContext->handle($data['fromWxid'] . ':' . $data['finalFromWxid'], $msg);

        $aiAnswer = $this->openAi->askGpt($msgFormat);
        
        if (!is_array($aiAnswer)) {
            $this->log($aiAnswer, 'askGpt');

            $this->sendMsg($data['fromWxid'], $data['finalFromWxid'], \common\Config::SPECIFIC_REPLY_TEXT[$aiAnswer] ?: \common\Config::SPECIFIC_REPLY_TEXT[999]);

            return $this->response();
        }

        $this->sendMsg($data['fromWxid'], $data['finalFromWxid'], $aiAnswer['choices'][0]['message']['content']);

        $isExceedLimit = $this->supportContext->judgeTokens($data['fromWxid'] . ':' . $data['finalFromWxid'], $aiAnswer);

        if ($isExceedLimit) {
            $this->sendMsg($data['fromWxid'], $data['finalFromWxid'], \common\Config::SPECIFIC_REPLY_TEXT[987]);
        } else {
            $this->supportContext->handle($data['fromWxid'] . ':' . $data['finalFromWxid'], $msg, $aiAnswer['choices'][0]['message']['content']);
        }


        return $this->response();
    }

    public function sendMsg($roomid, $receiveWxid, $msg)
    {
        if (!in_array($msg, array_values(\common\Config::SPECIFIC_REPLY_TEXT))) {
            $msg = "\n" . $msg;
        }

        $data = [
            'type' => 'Q0001',
            'data' => [
                'wxid' => $roomid,
                'msg' => "[@,wxid=$receiveWxid,nick=,isAuto=true] $msg"
            ]
        ];

        $response = \common\HttpRequest::post(\common\Config::REPLY_URL, $data);

        if (!is_array($response)) {
            $this->log($response, 'sendMsg');
        }

        return $response;
    }

    public function replyPerson($data)
    {
        if (!is_array($data)) {
            $data = json_decode($data, true);

            if (!$data) {
                return $this->response();
            }
        }

        if ($data['event'] != 10009) {
            return $this->response();
        }

        $data = $data['data'];

        if (substr($data['timestamp'], 0, 10) < time() - 60) {
            return $this->response();
        }

        $data = $data['data'];

        if ($data['fromType'] != 1 || $data['msgType'] != 1) {
            return $this->response();
        }

//        $this->sendMsgToPerson($data['fromWxid'], \common\Config::SPECIFIC_REPLY_TEXT[321]);

        $this->log($data['msg'], 'msg');

        $msgFormat = $this->supportContext->handle($data['fromWxid'], $data['msg']);

        $aiAnswer = $this->openAi->askGpt($msgFormat);

        if (!is_array($aiAnswer)) {
            $this->log($aiAnswer, 'askGpt');

            $this->sendMsgToPerson($data['fromWxid'], \common\Config::SPECIFIC_REPLY_TEXT[$aiAnswer] ?: \common\Config::SPECIFIC_REPLY_TEXT[999]);

            return $this->response();
        }

        $this->sendMsgToPerson($data['fromWxid'], $aiAnswer['choices'][0]['message']['content']);

        $isExceedLimit = $this->supportContext->judgeTokens($data['fromWxid'], $aiAnswer);

        if ($isExceedLimit) {
            $this->sendMsgToPerson($data['fromWxid'], \common\Config::SPECIFIC_REPLY_TEXT[987]);
        } else {
            $this->supportContext->handle($data['fromWxid'], $data['msg'], $aiAnswer['choices'][0]['message']['content']);
        }

        return $this->response();
    }

    public function sendMsgToPerson($receiveWxid, $msg)
    {
        $this->log(func_get_args(), 'sendMsg');

        $data = [
            'type' => 'Q0001',
            'data' => [
                'wxid' => $receiveWxid,
                'msg' => $msg
            ]
        ];

        $response = \common\HttpRequest::post(\common\Config::REPLY_URL, $data);

        if (!is_array($response)) {
            $this->log($response, 'sendMsgError');
        }

        return $response;
    }

    public function log($msg, $tag = '')
    {
        \common\Log::save($msg, $tag);
    }

    private function response() {
        return json_encode([
            'code' => 200,
            'msg' => 'ok'
        ]);
    }
}