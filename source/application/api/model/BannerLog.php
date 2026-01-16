<?php
namespace app\api\model;
use app\common\model\BannerLog as BannerLogModel;

/**
 * 轮播日志模型
 * Class OrderAddress
 * @package app\common\model
 */
class BannerLog extends BannerLogModel
{
    protected $updateTime = false;
    protected $createTime = false;
    
   public function bannerlog($data){
       $banlogData = [
        "banner_id"=> $data['id'],
        "user_id"=>$data['user_id'],
        "created_time" => time(),
        "wxapp_id"=>$data['wxapp_id']
       ];
       $result = $this->where('banner_id',$data['id'])->where('user_id',$data['user_id'])->find();
       if($result){
          return false; 
       }
      if($this->save($banlogData)){
          return true;
      }
       return false;
   }
}
