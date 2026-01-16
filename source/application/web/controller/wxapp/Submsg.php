<?php
namespace app\web\controller\wxapp;

use app\web\controller\Controller;
use app\web\model\Setting as SettingModel;

/**
 * 微信小程序订阅消息
 * Class Submsg
 * @package app\web\controller\wxapp
 */
class Submsg extends Controller
{
    /**
     * 获取订阅消息配置
     * @return array
     */
    public function setting()
    {
        $setting = SettingModel::getSubmsg();
        return $this->renderSuccess(compact('setting'));
    }

}