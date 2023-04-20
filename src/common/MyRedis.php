<?php

namespace common;

require_once 'Log.php';

class MyRedis
{
    private static $instance = null;

    private $redis;

    /**
     * @throws \RedisException
     */
    private function __construct()
    {
        $this->redis = new \Redis();
        try {
            $this->redis->connect('172.18.0.1');
        } catch (\RedisException $e) {
            Log::save($e->getMessage(), 'redis');
            throw $e;
        }
    }

    public static function getInstance(): ?MyRedis
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // 常用的redis操作

    /**
     * @throws \RedisException
     */
    public function get($key)
    {
        return $this->redis->get($key);
    }

    /**
     * @throws \RedisException
     */
    public function set($key, $value, $expire = 0)
    {
        if ($expire > 0) {
            return $this->redis->setex($key, $expire, $value);
        } else {
            return $this->redis->set($key, $value);
        }
    }

    /**
     * @throws \RedisException
     */
    public function del($key)
    {
        return $this->redis->del($key);
    }

    /**
     * @throws \RedisException
     */
    public function hGet($key, $field)
    {
        return $this->redis->hGet($key, $field);
    }

    /**
     * @throws \RedisException
     */
    public function hSet($key, $field, $value)
    {
        return $this->redis->hSet($key, $field, $value);
    }

    /**
     * @throws \RedisException
     */
    public function hDel($key, $field)
    {
        return $this->redis->hDel($key, $field);
    }

    /**
     * @throws \RedisException
     */
    public function hGetAll($key)
    {
        return $this->redis->hGetAll($key);
    }

    /**
     * @throws \RedisException
     */
    public function hExists($key, $field)
    {
        return $this->redis->hExists($key, $field);
    }

    /**
     * @throws \RedisException
     */
    public function hIncrBy($key, $field, $value)
    {
        return $this->redis->hIncrBy($key, $field, $value);
    }

    /**
     * @throws \RedisException
     */
    public function hLen($key)
    {
        return $this->redis->hLen($key);
    }

    /**
     * @throws \RedisException
     */
    public function hKeys($key)
    {
        return $this->redis->hKeys($key);
    }

    /**
     * @throws \RedisException
     */
    public function hVals($key)
    {
        return $this->redis->hVals($key);
    }

    /**
     * @throws \RedisException
     */
    public function hMGet($key, $fields)
    {
        return $this->redis->hMGet($key, $fields);
    }

}