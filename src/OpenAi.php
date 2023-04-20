<?php

require_once './common/Config.php';
require_once './common/HttpRequest.php';
class OpenAi
{
    private $headers;

    public function __construct($OPENAI_API_KEY = '')
    {
        if (!$OPENAI_API_KEY) {
            $OPENAI_API_KEY = \common\Config::API_KEY;
        }

        $this->headers = [
            "Content-Type: application/json",
            "Authorization: Bearer $OPENAI_API_KEY",
        ];
    }

    public function askGpt($msg)
    {
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => $msg
//            'temperature' => 0.5
        ];

        return \common\HttpRequest::post(\common\Config::CHAT_URL, $data, $this->headers, true);
    }
    
}