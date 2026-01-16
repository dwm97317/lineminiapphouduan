<?php

namespace app\store\controller\market;

use app\store\controller\Controller;
use app\store\model\wxapp\Formid as FormidModel;
use app\store\service\wxapp\Message as MessageService;
use app\common\model\User;
use app\store\model\SiteSms as SiteSmsModel;

/**
 * 消息推送 (废弃)
 * Class Push
 * @package app\store\controller\market
 */
class Push extends Controller
{
    /**
     * 发送消息
     * @return array|mixed
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function send()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('send');
        }
        // 执行发送
        $MessageService = new MessageService;
        $MessageService->send($this->postData('send'));
        return $this->renderSuccess('', '', [
            'stateSet' => $MessageService->getStateSet()
        ]);
    }
    
    /**
     * 发送消息 [站内信]
     * @return array|mixed
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function sendSms()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('sendsms');
        }
        // 执行发送
        $SiteSmsModel = new SiteSmsModel;
        if (!$SiteSmsModel->add($this->postData('sendsms'))){
           return  $this->renderError($SiteSmsModel->getError()??'操作失败');
        }
        return $this->renderSuccess('操作成功');
    }
    
    
     /**
     * 发送消息 [邮件]
     * @return array|mixed
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function sendUserEmail()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('sendUserEmail');
        }
        // 执行发送
            $data = $this->postData('sendsms');
            $data['code'] = '';
            $data['logistics_describe']= $data['content'];
            $user = User::detail($data['user_id']);
            if($user['email']){
                $this->sendemail($user,$data,$type=3);
            }
       
       
        return $this->renderSuccess('操作成功');
    }


    /**
     * 活跃用户列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function user()
    {
        $list = (new FormidModel)->getUserList();
        return $this->fetch('user', compact('list'));
    }

}