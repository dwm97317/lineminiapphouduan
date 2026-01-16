<?php
declare (strict_types=1);

namespace app\api\service\user;

use app\api\model\user\UserOauth as UserOauthModel;
use app\api\model\Wxapp as WxappSettingModel;
use app\common\service\Basics;
use app\common\library\wechat\WxUser;
use app\common\library\wechat\ErrorCode;
use app\common\library\wechat\WXBizDataCrypt;
use cores\exception\BaseException;

/**
 * 服务类: 第三方用户服务类
 * Class Avatar
 * @package app\api\service\user
 */
class Oauth extends Basics
{
    
    /**
     * 拉取用户信息(需scope为 snsapi_userinfo)
     * @param string $code
     * @return array|false
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function sessionGetUserInfo($access_token,$openid)
    {
        $WxUser = new WxUser();
        $result = $WxUser->sessionGetUserInfo($access_token,$openid);
        !$result && false;
        return $result;
    }
    
    /**
     * 微信公众号通过code获取session (openid session_key unionid)
     * @param string $code
     * @return array|false
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function wxgzhCode2Session(string $code)
    {
        // 获取当前小程序信息
        $wxConfig = static::getMpWxConfig();
        $WxUser = new WxUser();
        $result = $WxUser->sessionWxKey($code,$wxConfig['app_wxappid'],$wxConfig['app_wxsecret']);
        !$result && false;
        return $result;
    }
    
    /**
     * 微信小程序通过code获取session (openid session_key unionid)
     * @param string $code
     * @return array|false
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function wxCode2Session(string $code)
    {
        // 获取当前小程序信息
        $wxConfig = static::getMpWxConfig();
        // 微信登录 (获取session_key)
        $WxUser = new WxUser($wxConfig['app_id'], $wxConfig['app_secret']);
        $result = $WxUser->sessionKey($code);
        !$result && false;
        return $result;
    }

    /**
     * 解密微信的加密数据encryptedData
     * @param string $sessionKey
     * @param string $encryptedData
     * @param string $iv
     * @return mixed
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \cores\exception\BaseException
     */
    public static function wxDecryptData(string $sessionKey, string $encryptedData, string $iv)
    {
        // 获取当前小程序信息
        $wxConfig = static::getMpWxConfig();
        // 微信数据解密
        $WXBizDataCrypt = new WXBizDataCrypt($wxConfig['app_id'], $sessionKey);
        $content = null;
        $code = $WXBizDataCrypt->decryptData($encryptedData, $iv, $content);
        if ($code !== ErrorCode::$OK) {
            throwError('微信数据 encryptedData 解密失败');
        }
        return $content;
    }

    /**
     * 获取微信小程序配置项
     * @return array
     * @throws \cores\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private static function getMpWxConfig(): array
    {
        $wxConfig = WxappSettingModel::getWxappCache();
        if (empty($wxConfig['app_id']) || empty($wxConfig['app_secret'])) {
            throwError('请到后台小程序设置填写AppID和AppSecret参数');
        }
        return $wxConfig;
    }

    /**
     * 根据openid获取用户ID
     * @param string $oauthId 第三方用户唯一标识 (openid)
     * @param string $oauthType 第三方登陆类型
     * @return mixed
     */
    public static function getUserIdByOauthId(string $oauthId, string $oauthType)
    {
        return UserOauthModel::getUserIdByOauthId($oauthId, $oauthType);
    }
}