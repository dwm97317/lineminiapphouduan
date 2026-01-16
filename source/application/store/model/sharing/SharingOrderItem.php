<?php
namespace app\store\model\sharing;
use app\common\model\sharing\SharingOrderItem as SharingOrderItemModel;
use app\store\model\Inpack;
class SharingOrderItem extends SharingOrderItemModel {
     
      // 根据订单ID获取拼团列表
      public function getItemByOrderId($id){
         $item = $this->with(['package'])->where(['order_id'=>$id])->paginate(15);
         return $item;
      }
      
      public function package(){
        return $this->belongsTo('app\api\model\Package','package_id');
      }
      
      //循环保存集运单
      public function insertInpack($idsArray,$pintuan_id){
        
        $Inpack = new Inpack();
        //先检查有没有重复加入拼团的
        foreach ($idsArray as $key => $value){
           $data[$key]['package_id'] = $value;
           $data[$key]['order_id'] = $pintuan_id;
           $data[$key]['status'] = 1;
           $data[$key]['wxapp_id'] = self::$wxapp_id;
           $data[$key]['create_time'] = time();
           $data[$key]['update_time'] = time();
           $result = $this->where('package_id',$value)->find();
           if($result){
               $this->error = '此包裹已加入拼团,请勿重复添加';
               return false;
           }
        }
        if($this->insertAll($data)){
            //检查完成后就开始
            foreach ($idsArray as $key => $value){
               $Inpack->where('id',$value)->update(['inpack_type'=>1]);
            }
            return true;
        }
        return false;
      }
      
}  

?>