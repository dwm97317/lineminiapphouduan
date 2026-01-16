<?php

namespace app\store\controller;

use app\store\model\Wxapp as WxappModel;
use app\store\model\Setting as SettingModel;
use app\store\model\store\User;
use think\Cache;

/**
 * 小程序管理
 * Class Wxapp
 * @package app\store\controller
 */
class Wxapp extends Controller
{
    /**
     * 小程序设置
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function setting()
    {
        // 当前小程序信息
        $model = WxappModel::detail();
        if (!$this->request->isAjax()) {
            return $this->fetch('setting', compact('model'));
        }
        // 更新小程序设置
        if ($model->edit($this->postData('wxapp'))) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    // web端设置    
    public function web(){
         // 当前小程序信息
        $model = WxappModel::detail();
        if (!$this->request->isAjax()) {
            return $this->fetch('web', compact('model'));
        }
        $data = $this->postData('h5');
        $model->url_code = $data['code'];
        if ($model->save()) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
    public function h5(){
         // 当前小程序信息
        $model = WxappModel::detail();
        if (!$this->request->isAjax()) {
            return $this->fetch('h5', compact('model'));
        }
        $data = $this->postData('wxapp');
        if ($model->save($data)) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
    //多语言设置
    public function lang(){
        $SettingModel = new SettingModel;
        $lang = $SettingModel::getItem("lang");
        // dump($lang);die;
        if (!$this->request->isAjax()) {
            return $this->fetch('lang', compact('lang'));
        }
        $data = $this->postData('lang');
        if ($SettingModel->edit("lang",$data)) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($SettingModel->getError() ?: '更新失败');
        // dump($data);die;
    }

    
    public function code(){
        // 当前小程序信息
        $data = $this->request->param();
        $User = new User();
        if(empty($data['password'])){
            return $this->renderError('请输入密码');
        }
        $username = $this->store['user']['user_name'];
        $result = $User->useGlobalScope(false)->with(['wxapp'])->where([
            'user_name' => $username,
            'password' => yoshop_hash($data['password']),
            'is_delete' => 0
        ])->find();
            
        if (empty($result)) {
            return $this->renderError('更新失败,重置URL密码不正确');
        }
        $model = WxappModel::detail();
        $key = generate_password(22);
        $key = Cache::set($model['wxapp_id'].'_en_key',$key);
        $code = encrypt($model['wxapp_id']);
        Cache::set($code,$model['wxapp_id']);
        $model->url_code = $code;
        if ($model->save()) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}
