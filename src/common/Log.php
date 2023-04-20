<?php

namespace common;

class Log
{
    public static function save($info, $tag = '')
    {
        if (!is_string($info)) {
            $info = json_encode($info);
        }

        $str = '--------------' . $tag . '-----------------' . date('Y-m-d H:i:s') . '-------------------------------' . PHP_EOL . $info . PHP_EOL;

        file_put_contents(dirname(__DIR__) . '/log.txt', $str, FILE_APPEND);
    }
}