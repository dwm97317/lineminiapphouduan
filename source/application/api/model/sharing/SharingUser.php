<?php
namespace app\api\model\sharing;
use app\common\model\sharing\SharingUser as SharingUserModel;
use app\common\model\User as UserModel;

class SharingUser extends SharingUserModel {
   
   /**
    * 申请
    * */
   public function apply($data){
 
       if ($this->isExist($data['user_id'])){
           $this->error = '您已经申请过了，无需重新申请';
           return false;
       }
       $data['wxapp_id'] = self::$wxapp_id;
       return $this->save($data);
   }
   
   public function reapply($data){
       $model = $this->where(['user_id'=>$data['user_id']])->find();
       if (!$model){
           $this->error = '申请信息不存在';
           return false;
       }
       return $model->save($data);
   }
   
   // 是否存在
   public function isExist($data){
      return $this->where('user_id',$data['user_id'])->find(); 
   }
   
   // 验证是否待审核
   public function getVerify($userid){
      $data['user_id'] = $userid;
      return $this->where($data)->whereIn('status',['2','3'])->field('status')->find(); 
   }
}