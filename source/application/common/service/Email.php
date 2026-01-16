<?php

namespace app\common\service;
use app\common\model\Setting as SettingModel;
/**
 * 消息通知服务
 * Class Message
 * @package app\common\service
 */
class Email extends Basics
{

 /**
     * 发送邮件
     * @param $user 用户昵称，用户邮箱
     * @param $user 用户昵称，用户邮箱
     * @return mixed
     */
    public function sendemail($user,$data,$type){
            //type==1 物流变更  type==2 验证码
            if(!isset($user['email']) || empty($user['email'])){
            //   $this->error('用户邮箱为空'); 
               return false;
            }
            if(!isset($user['nickName'])){
            //   $this->error('昵称为空'); 
               return false;
            }
            //获取设置信息
            $setting = SettingModel::getItem('email');
            if($setting['is_enable']==0){
                $this->error('邮箱功能已关闭'); 
                return false;
            }
               
            
            //物流变更
            if($type ==1){
                $resmsg = str_ireplace('${code}',$data['code'],$setting['template']['status']['value']);
                $resmsg = str_ireplace('${message}',$data['logistics_describe'],$resmsg);
                 //收件人的邮箱
                $toemail=$user['email'];
                //收件人的名称
                $name= $user['nickName'];
                //物流通知名称
                $subject= $setting['template']['status']['theme'];
                // $code = mt_rand(10000, 99999);
                // session("code",$code);
                // $content='你得验证码为'.$code;
                $content = "【".$setting['setting']['replyName']."】".$resmsg;
            }
            ///验证码
            if($type ==2){
                 //收件人的邮箱
                 $toemail=$user['email'];
                 //收件人的名称
                 $name= $user['nickName'];
                 $subject = "【".$setting['setting']['replyName']."】".'邮箱验证';
                 $content = "【".$setting['setting']['replyName']."】".$data['code'];
            }
           
            send_mail($toemail,$name,$subject,$content,$attachment=null,$setting['setting']);
            return true;
    }  
    
    public function sendEmailCaptcha($mail,$code,$type){
            //type==1 物流变更  type==2 验证码
            if(!isset($mail) || empty($mail)){
            //   $this->error('用户邮箱为空'); 
               return false;
            }
            //获取设置信息
            $setting = SettingModel::getItem('email');
            if($setting['is_enable']==0){
                $this->error('邮箱功能已关闭'); 
                return false;
            }

            ///验证码
            if($type ==2){
                 //收件人的邮箱
                 $toemail=$mail;
                 //收件人的名称
                 $name= $setting['setting']['replyName'];
                 $subject = "【".$setting['setting']['replyName']."】".'邮箱验证';
                 $content = "【".$setting['setting']['replyName']."】".$code;
            }
           
            send_mail($toemail,$name,$subject,$content,$attachment=null,$setting['setting']);
            return true;
    } 

}