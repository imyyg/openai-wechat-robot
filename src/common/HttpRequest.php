<?php
namespace common;
// curl http 请求封装类
class HttpRequest {

    /**
     * get请求
     *
     * @param $url
     * @param $headers
     * @param $timeout
     * @return bool|mixed|string
     */
    public static function get($url, $headers = array(), $timeout = 10)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode == 200) {
            return json_decode($output, true);
        } else {
            return $output;
        }
    }

    /**
     * post请求
     *
     * @param $url
     * @param $data
     * @param $headers
     * @param $timeout
     * @return bool|mixed|string
     */
    public static function post($url, $data, $headers = array(), $needProxy = false, $timeout = 100) {

        if (is_array($data)) {
            $data = json_encode($data);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($needProxy) {
            curl_setopt($ch, CURLOPT_PROXY, "http://host.docker.internal");
            curl_setopt($ch, CURLOPT_PROXYPORT, "7890");
        }

        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        curl_close($ch);
        if ($httpCode == 200) {
            return json_decode($output, true);
        } else {
            return $output . $errno;
        }
    }

}
