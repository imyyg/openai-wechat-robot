# openai-wechat-robot
WeChat chatbot developed based on OpenAI's GPT-3.5-Turbo，Developed natively using PHP without using any framework

基于openai的GPT-3.5-Turbo模型开发的机器人，使用php原生开发。

使用前在common文件夹下创建Common.php文件，文件内容如下：
```
<?php
namespace common;
class Config {
    // openai api key
    const API_KEY = '';

    // openai api 聊天接口url
    const CHAT_URL = 'https://api.openai.com/v1/chat/completions';

    // 要使用哪个微信机器人的wxid
    const USE_WECHAT = '';

    // 机器人回复的url
    const REPLY_URL = 'http://host.docker.internal:7777/DaenWxHook/httpapi/?wxid=' . self::USE_WECHAT;

    const REPLY_GROUP_SWITCH = 1;   // 0-开启，1-关闭
    const REPLY_PERSON_SWITCH = 1;   // 0-开启，1-关闭

    const REPLY_PERSON_WXID = ['', ''];

    const SPECIFIC_REPLY_TEXT = [
        321 => '收到，请稍等...',
        987 => '超出对话限制，将开启新聊天',
        999 => '系统繁忙，请稍后再试',
        7 => '连接失败，抱歉',
        28 => '网络连接超时，抱歉',
        886 => '好的，我已准备好开始新的聊天',
    ];

    const IS_SUPPORT_CONTEXT = 1;   // 0-不支持，1-支持

    const MAX_TOKENS = 4000; // GPT-3.5-turbo 最大支持 4096 tokens

    const CONTEXT_CACHE_TIME = 3600; // 上下文缓存时间，单位：秒

    const START_NEW_CHAT = '开启新聊天';
}
```
