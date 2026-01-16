<?php

namespace app\common\library\AITool;

use think\Cache;
use app\common\exception\BaseException;

/**
 * 百度基类
 * Class baidu
 * @package app\library
 */
class BaiduBase
{
    protected $apikey;
    protected $apisecret;
    protected $keyword1;
    protected $keyword2;
    protected $error;

    /**
     * 构造函数
     * WxBase constructor.
     * @param $appId
     * @param $appSecret
     */
    public function __construct($setting)
    {
        $this->setConfig($setting['apikey'], $setting['apisecret'],$setting['keyword1'],$setting['keyword2']);
    }

    protected function setConfig($apikey = null, $apisecret = null,$keyword1,$keyword2)
    {
        !empty($apikey) && $this->apikey = $apikey;
        !empty($apisecret) && $this->apisecret = $apisecret;
        !empty($keyword1) && $this->keyword1 = $keyword1;
        !empty($keyword2) && $this->keyword2 = $keyword2;
    }

    /**
     * 写入日志记录
     * @param $values
     * @return bool|int
     */
    protected function doLogs($values)
    {
        return log_write($values);
    }

    /**
     * 获取access_token
     * @return mixed
     * @throws BaseException
     */
    protected function getAccessToken()
    {
        $cacheKey = $this->apikey . '@access_token';
        if (!Cache::get($cacheKey)) {
            // 请求API获取 access_token
            $url = "https://aip.baidubce.com/oauth/2.0/token?grant_type=client_credentials&client_id={$this->apikey}&client_secret={$this->apisecret}";
            $result = $this->get($url);
            $response = $this->jsonDecode($result);
            if (array_key_exists('errcode', $response)) {
                throw new BaseException(['msg' => "access_token获取失败，错误信息：{$result}"]);
            }
            // 记录日志
            $this->doLogs([
                'describe' => '获取百度access_token',
                'url' => $url,
                'appId' => $this->apikey,
                'result' => $result
            ]);
            // 写入缓存
            Cache::set($cacheKey, $response['access_token'], 86400*30);
        }
        return Cache::get($cacheKey);
    }
    
  

    /**
     * 模拟GET请求 HTTPS的页面
     * @param string $url 请求地址
     * @return string $result
     */
    protected function get($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    /**
     * 模拟POST请求
     * @param $url
     * @param array $data
     * @param bool $useCert
     * @param array $sslCert
     * @return mixed
     */
    protected function post($url, $data = [], $useCert = false, $sslCert = [])
    {
        $header = [
            'Content-type: application/json;'
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        if ($useCert == true) {
            // 设置证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLCERT, $sslCert['certPem']);
            curl_setopt($curl, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLKEY, $sslCert['keyPem']);
        }
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    /**
     * 模拟POST请求 [第二种方式, 用于兼容微信api]
     * @param $url
     * @param array $data
     * @return mixed
     */
    protected function post2($url, $data = [])
    {
        $header = [
            'Content-Type: application/x-www-form-urlencoded'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 数组转json
     * @param $data
     * @return string
     */
    protected function jsonEncode($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * json转数组
     * @param $json
     * @return mixed
     */
    protected function jsonDecode($json)
    {
        return json_decode($json, true);
    }

    /**
     * 返回错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

}
