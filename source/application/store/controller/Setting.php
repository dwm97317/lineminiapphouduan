<?php

namespace app\store\controller;

use app\store\model\Printer as PrinterModel;
use app\store\model\Setting as SettingModel;
use app\common\library\sms\Driver as SmsDriver;
use app\store\model\Wxapp as WxappModel;
use app\common\model\UploadFile;
use app\common\model\AiLog;
/**
 * 系统设置
 * Class Setting
 * @package app\store\controller
 */
class Setting extends Controller
{
    /**
     * 商城设置
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function store()
    {
        return $this->updateEvent('store');
    }
    
    /**
     * 批次设置
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function batch()
    {
        return $this->updateEvent('batch');
    }
    
     /**
     * 用户端样式设置
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function userclient()
    {
        return $this->updateUserclient('userclient');
    }
    
     /**
     * 智能AI识别
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function aiidentify()
    {
        return $this->updateUserclient('aiidentify');
    }
    
     /**
     * 仓管端
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function keeper()
    {
        return $this->updateUserclient('keeper');
    }
    
    /**
     * 管理端
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function adminstyle()
    {
        return $this->updateUserclient('adminstyle');
    }
    
    
    /**
     * 智能AI识别的记录日志
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function aiidentifylog(){
        $param = $this->request->param();
        // dump($param);die;
        $AiLog = new AiLog;
        $list = $AiLog->getList($param);
        return $this->fetch("aiidentifylog",compact('list'));
    }

     /**
     * 更新系统设置事件
     * @param $key
     * @param $vars
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    private function updateUserclient($key, $vars = [])
    {
        if (!$this->request->isAjax()) {
            $vars['values'] = SettingModel::getItem($key);
            // dump($key);die;
            $vars['values']['baiduai'] = WxappModel::detail($this->store['wxapp']['wxapp_id'])['baiduai'];
           
            if($key=='userclient' && isset($vars['values']['guide']['first_image'])){
                $vars['values']['guide']['first_file_path'] = UploadFile::detail($vars['values']['guide']['first_image'])['file_path'];
                $vars['values']['guide']['second_file_path'] = UploadFile::detail($vars['values']['guide']['second_image'])['file_path'];
                $vars['values']['guide']['third_file_path'] = UploadFile::detail($vars['values']['guide']['third_image'])['file_path'];
            }
            return $this->fetch($key, $vars);
        }
  
        $model = new SettingModel;
        if ($model->edit($key, $this->postData($key))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 交易设置
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function trade()
    {
        return $this->updateEvent('trade');
    }

    /**
     * 支付设置
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function paytype()
    {

        return $this->updateEvent('paytype');
    }
    
    /**
     * 短信通知
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function sms()
    {
        return $this->updateEvent('sms');
    }
    
    /**
     * 物流模板
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function notice()
    {
        return $this->updateEvent('notice');
    }
    
    /**
     * 邮件通知
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function email()
    {
        return $this->updateEvent('email');
    }

    /**
     * 发送模板消息
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function tplMsg()
    {
        return $this->updateEvent('tplMsg');
    }

    /**
     * 发送短信通知测试
     * @param $AccessKeyId
     * @param $AccessKeySecret
     * @param $sign
     * @param $msg_type
     * @param $template_code
     * @param $accept_phone
     * @return array
     * @throws \think\Exception
     */
    public function smsTest($AccessKeyId, $AccessKeySecret, $sign, $msg_type, $template_code, $accept_phone)
    {
        $SmsDriver = new SmsDriver([
            'default' => 'aliyun',
            'engine' => [
                'aliyun' => [
                    'AccessKeyId' => $AccessKeyId,
                    'AccessKeySecret' => $AccessKeySecret,
                    'sign' => $sign,
                    $msg_type => compact('template_code', 'accept_phone'),
                ],
            ],
        ]);
        $templateParams = [];
        if ($msg_type === 'order_pay') {
            $templateParams = ['order_no' => '2018071200000000'];
        }
        if ($SmsDriver->sendSms($msg_type, $templateParams, true)) {
            return $this->renderSuccess('发送成功');
        }
        return $this->renderError('发送失败 ' . $SmsDriver->getError());
    }

    /**
     * 上传设置
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function storage()
    {
        return $this->updateEvent('storage');
    }
    
    public function service(){
        return $this->updateEvent('service');
    }
    
    
    /**
     * 小票打印设置
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function printer()
    {
        // 获取打印机列表
        return $this->updateEvent('printer');
    }

    /**
     * 更新系统设置事件
     * @param $key
     * @param $vars
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    private function updateEvent($key, $vars = [])
    {
             
        if (!$this->request->isAjax()) {
            $vars['values'] = SettingModel::getItem($key);
            if(isset($vars['values']['cover_id'])){
                $vars['values']['file_path'] = UploadFile::detail($vars['values']['cover_id'])['file_path'];
            }
            return $this->fetch($key, $vars);
        }
        $model = new SettingModel;
        if ($model->edit($key, $this->postData($key))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }


        
    /**
     * 小程序设置
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function stylecenter()
    {
        // 当前小程序信息
        $model = WxappModel::detail();
        if (!$this->request->isAjax()) {
            return $this->fetch('style', compact('model'));
        }
        // 更新小程序设置
        $system_style = $this->postData('wxapp')['system_style'];

        if (!empty($system_style)) {
            $model->where('wxapp_id',$model['wxapp_id'])->update(['system_style' => $system_style]);
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }



}
