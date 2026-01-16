<?php

namespace app\common\library\wechat;

use think\Cache;
/**
 * 微信小程序用户管理类
 * Class WxUser
 * @package app\common\library\wechat
 */
class WxUser extends WxBase
{
    /**
     * 获取session_key
     * @param $code
     * @return array|mixed
     */
    public function sessionKey($code)
    {
        /**
         * code 换取 session_key
         * ​这是一个 HTTPS 接口，开发者服务器使用登录凭证 code 获取 session_key 和 openid。
         * 其中 session_key 是对用户数据进行加密签名的密钥。为了自身应用安全，session_key 不应该在网络上传输。
         */
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        $result = json_decode(curl($url, [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'grant_type' => 'authorization_code',
            'js_code' => $code
        ]), true);
        if (isset($result['errcode'])) {
            $this->error = $result['errmsg'];
            return false;
        }
        return $result;
    }
    
    /**
     * 通过code换取网页授权access_token
     * @param $code
     * @return array|mixed
     */
    public function sessionWxKey($code,$app_wxappid,$app_wxsecret)
    {
        /**
         * code 换取 session_key
         * ​这是一个 HTTPS 接口，开发者服务器使用登录凭证 code 获取 session_key 和 openid。
         * 其中 session_key 是对用户数据进行加密签名的密钥。为了自身应用安全，session_key 不应该在网络上传输。
         */
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$app_wxappid.'&secret='.$app_wxsecret.'&code='.$code.'&grant_type=authorization_code';
 
        $result = json_decode(curlPost($url, [
            'appid' => $app_wxappid,
            'secret' => $app_wxsecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
        ]), true);
        if (isset($result['errcode'])) {
            $this->error = $result['errmsg'];
            return false;
        }
        $cacheKey = $app_wxappid . '@access_token';
        // Cache::set($cacheKey, $result['access_token'], 6000);    // 7000
        // dump($result);die;
        return $result;
    }
    
    
    /**
     * 拉取用户信息(需scope为 snsapi_userinfo)
     * @param $code
     * @return array|mixed
     */
    public function sessionGetUserInfo($access_token,$openid)
    {
        /**
         * code 换取 session_key
         * ​这是一个 HTTPS 接口，开发者服务器使用登录凭证 code 获取 session_key 和 openid。
         * 其中 session_key 是对用户数据进行加密签名的密钥。为了自身应用安全，session_key 不应该在网络上传输。
         */
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
 
        $result = json_decode(curlPost($url, [
            'access_token' => $access_token,
            'openid' => $openid,
            'lang' => 'zh_CN',
        ]), true);
        if (isset($result['errcode'])) {
            $this->error = $result['errmsg'];
            return false;
        }
        return $result;
    }

}