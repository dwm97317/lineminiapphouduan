<?php
declare (strict_types=1);

namespace app\web\controller;

use app\web\service\passport\Login as LoginService;
use think\Session;
/**
 * 用户认证模块
 * Class Passport
 * @package app\web\controller
 */
class Passport extends Controller
{
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
        if (!$this->request->isAjax()) {
            $this->view->engine->layout(false);
            return $this->fetch('login');
        }
        // 执行登录
        $LoginService = new LoginService;
        $postData = array_merge($this->postData(),['wxapp_id'=>$this->wxapp_id]);
        
        if (!$LoginService->loginForm($postData)) {
            return $this->renderError($LoginService->getError());
        }
        // 用户信息
        $userInfo = $LoginService->getUserInfo();
        return $this->renderSuccess([
            'userId' => (int)$userInfo['user_id'],
            'token' => $LoginService->getToken((int)$userInfo['user_id']),
        ], '登录成功');
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
        if (!$LoginService->loginMpWxMobile($this->postForm())) {
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
     * 普通登录: 用户通过 账号 密码 进行登录
     */
    public function loginUser(){
        $LoginService = new LoginService;
        if (!$LoginService->LoginForm($this->postData())) {
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
     * 普通注册：用户 注册
     */
    public function register(){
        if (!$this->request->isAjax()) {
            $this->view->engine->layout(false);
            return $this->fetch('register');
        }
        $postData = array_merge($this->postData(),['wxapp_id'=>$this->wxapp_id]);
        $LoginService = new LoginService;
        if (!$LoginService->registerForm($postData)) {
          return $this->renderError($LoginService->getError());
        }
        // 获取登录成功后的用户信息
        $userInfo = $LoginService->getUserInfo();
        return $this->renderSuccess([
            'userId' => (int)$userInfo['user_id'],
            // 'token' => $LoginService->getToken((int)$userInfo['user_id'])
        ], '注册成功');
    }
    
    /**
     * 退出登录
     */
    public function logout()
    {
        Session::clear('yoshop_user');
        $this->redirect(urlCreate('/web/passport/login'));
    }

}