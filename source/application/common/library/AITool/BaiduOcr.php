<?php

namespace app\common\library\AITool;

use think\Cache;
use app\common\exception\BaseException;

/**
 * 百度文字识别
 * Class wechat
 * @package app\library
 */
class BaiduOcr extends BaiduBase
{
    /**
     * 百度文字识别-标准文字识别
     * Class wechat
     * @package app\library
     */
    public function generalBasic($img){
       $token = $this->getAccessToken();
       $url = "https://aip.baidubce.com/rest/2.0/ocr/v1/general_basic?access_token={$token}";
            //   dump($img);die;
       $header = ["Content-Type:application/x-www-form-urlencoded"];
       $bodys = array(
            'url' => $img,
            'detect_direction'=>"true",
            'paragraph'=>"true"
       );
       $res = $this->requestPost($url, $bodys);
       
       $data = json_decode($res,true)['words_result'];

       $words = '';
       foreach ($data as $key =>$val){
          $words = $words.$val['words'];
       }
       $user_id = $this->getBetweenAB($words,$this->keyword1,$this->keyword2);
       return ['user_id'=>$user_id,'words'=>$words];
    }
    
    
    /**
     * PHP截取两个指定字符(串)之间的所有字符。拿到任何地方都可以使用
     */
    public function getBetweenAB($str, $begin, $end)
    {
        if ($begin == '') return '';
        $beginPos = mb_strpos($str, $begin);
        if ($beginPos === false) return '';       // 起始字符不存在，直接返回空。合理
        $start = $beginPos + mb_strlen($begin);       // 1.1、开始截取下标
        if ($end == '') $endPos = mb_strlen($str);// 结束字符不存在，默认截取到字符串末尾。合理
        else $endPos = mb_strpos($str, $end, $start); // 1.2、从开始下标之后查找
        if ($endPos === false) $endPos = mb_strlen($str);
        $length = $endPos - $start;                   // 2、截取字符的长度
        return mb_substr($str, $start, $length);
    }
    /**
    * 发起http post请求(REST API), 并获取REST请求的结果
    * @param string $url
    * @param string $param
    * @return - http response body if succeeds, else false.
    */
    public function requestPost($url = '', $param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }
    
        $postUrl = $url;
        $curlPost = $param;
        // dump($curlPost);die;
        // 初始化curl
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $postUrl);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // 要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        // post提交方式
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        // 运行curl
        $data = curl_exec($curl);
        curl_close($curl);
    
        return $data;
    }

}