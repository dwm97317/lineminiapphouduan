<?php
declare (strict_types=1);

namespace app\api\controller;

use app\api\service\passport\Login as LoginService;

/**
 * 用户认证模块
 * Class Passport
 * @package app\api\controller
 */
class Passport extends Controller
{
    public function getCode(){
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx483d93f2c89fb198&redirect_uri=http%3A%2F%2Fzhuanyun.sllowly.cn/index.php?s=/api/passport/loginwx&wxapp_id=10001&response_type=code&scope=snsapi_userinfo&state=10001#wechat_redirect";
        
        return $url;
    }
    
    public function register(){
        $LoginService = new LoginService;
        $data = $this->postData();
        $data['wxapp_id'] = $this->wxapp_id;
        if (!$LoginService->registerMobile($data)) {
            return $this->renderError($LoginService->getError());
        }
        return $this->renderSuccess([],'注册成功，请前往登录');
    }
    
    /**
     * 找回密码
     * Class Passport
     * @package app\api\controller
     */
    public function findpassword(){
        $LoginService = new LoginService;
        if (!$LoginService->findpassword($this->postData())) {
            return $this->renderError($LoginService->getError());
        }
        return $this->renderSuccess([],'重置成功，请前往登录');
    }

    
    /**
     * 登录接口 (需提交手机号、短信验证码、第三方用户信息)
     * @return array|\think\response\Json
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function login()
    {
        // 执行登录
        $LoginService = new LoginService;
        if (!$LoginService->login($this->postData())) {
            return $this->renderError($LoginService->getError());
        }
        // 用户信息
        $userInfo = $LoginService->getUserInfo();
        return $this->renderSuccess([
            'userId' => (int)$userInfo['user_id'],
            'token' => $LoginService->getToken((int)$userInfo['user_id'])
        ], '登录成功');
    }

    /**
     * 微信小程序快捷登录 (需提交wx.login接口返回的code、微信用户公开信息)
     * 业务流程：判断openid是否存在 -> 存在:  更新用户登录信息 -> 返回userId和token
     *                          -> 不存在: 返回false, 跳转到注册页面
     * @return array|\think\response\Json
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function loginMpWx()
    {
        // 微信小程序一键登录
        $LoginService = new LoginService;
        if (!$LoginService->loginMpWx($this->postForm())) {
            return $this->renderError($LoginService->getError());
        }
        // 获取登录成功后的用户信息
        $userInfo = $LoginService->getUserInfo();
        return $this->renderSuccess([
            'userId' => (int)$userInfo['user_id'],
            'token' => $LoginService->getToken((int)$userInfo['user_id'])
        ], '登录成功');
    }
    
    /**
     * zalo小程序快捷登录 (需提交accessToken接口返回的code、微信用户公开信息)
     * 业务流程：判断openid是否存在 -> 存在:  更新用户登录信息 -> 返回userId和token
     *                          -> 不存在: 返回false, 跳转到注册页面
     * @return array|\think\response\Json
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function loginbyzalo(){
        // 微信小程序一键登录
        $LoginService = new LoginService;

        if (!$LoginService->loginMpZalo($this->postForm())) {
            return $this->renderError($LoginService->getError());
        }
        // 获取登录成功后的用户信息
        $userInfo = $LoginService->getUserInfo();
        return $this->renderSuccess([
            'userId' => (int)$userInfo['user_id'],
            'nickname' => (string) $userInfo['nickName'],
            'avatarUrl' => (string) $userInfo['avatarUrl'],
            'token' => $LoginService->getToken((int)$userInfo['user_id'])
        ], '登录成功');  
    }
    
    /**
     * LINE小程序快捷登录 (需提交id_token)
     * 业务流程：验证ID Token -> 判断openid是否存在 -> 存在: 更新用户登录信息 -> 返回userId和token
     *                                          -> 不存在: 创建新用户或要求绑定手机
     * @return array|\think\response\Json
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function loginMpLine(){
        $LoginService = new LoginService;

        if (!$LoginService->loginMpLine($this->postForm())) {
            return $this->renderError($LoginService->getError());
        }
        // 获取登录成功后的用户信息
        $userInfo = $LoginService->getUserInfo();
        return $this->renderSuccess([
            'userId' => (int)$userInfo['user_id'],
            'nickname' => (string) $userInfo['nickName'],
            'avatarUrl' => (string) $userInfo['avatarUrl'],
            'token' => $LoginService->getToken((int)$userInfo['user_id'])
        ], '登录成功');  
    }

    
    public function loginClerk()
    {
        // 微信小程序一键登录
        $LoginService = new LoginService;
        $data = $this->request->param();
        // dump($data);die;
        if (!$LoginService->loginMpWxMobileClerk($data)) {
            return $this->renderError($LoginService->getError());
        }
        // 获取登录成功后的用户信息
        $userInfo = $LoginService->getUserInfo();
        return $this->renderSuccess([
            'wxapp_id'=>$userInfo['wxapp_id'],
            'userId' => (int)$userInfo['user_id'],
            'token' => $LoginService->getToken((int)$userInfo['user_id'])
        ], '登录成功');
    }
    
    /**
     * 微信公众号快捷登录 (需提交wx.login接口返回的code、微信用户公开信息)
     * 业务流程：判断openid是否存在 -> 存在:  更新用户登录信息 -> 返回userId和token
     *                          -> 不存在: 返回false, 跳转到注册页面
     * @return array|\think\response\Json
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function loginWxOfficial()
    {
        // 微信小程序一键登录
        $LoginService = new LoginService;
   
        if (!$LoginService->loginWxOfficial($this->postForm())) {
            return $this->renderError($LoginService->getError());
        }
        // 获取登录成功后的用户信息
        $userInfo = $LoginService->getUserInfo();
        return $this->renderSuccess([
            'userId' => (int)$userInfo['user_id'],
            'token' => $LoginService->getToken((int)$userInfo['user_id'])
        ], '微信授权登录成功');
    }

    /**
     * 快捷登录: 微信小程序授权手机号登录
     * @return array|\think\response\Json
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function loginMpWxMobile()
    {
        // 微信小程序一键登录
        $LoginService = new LoginService;
        if (!$LoginService->loginMpWxMobile($this->request->param())) {
            return $this->renderError($LoginService->getError());
        }
        // 获取登录成功后的用户信息
        $userInfo = $LoginService->getUserInfo();
        return $this->renderSuccess([
            'userId' => (int)$userInfo['user_id'],
            'token' => $LoginService->getToken((int)$userInfo['user_id'])
        ], '登录成功');
    }
}