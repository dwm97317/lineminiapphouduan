<?php
namespace app\common\model\sharing;

use app\common\model\BaseModel;
/**
 * 拼团项目模型 
 */
class SharingOrderItem extends BaseModel{
   protected $name = 'sharing_tr_order_item'; 
   
   public static function detail($printer_id)
   {
        return self::get($printer_id);
   }
   //[1 已加入拼团 2 待团长审核 3 待打包 4 待付款 5 待发货 6 已发货 7 已完成 8 已取消 9 已拒绝]
    public function getStatusAttr($value){
        $map = [
           1 => '已加入',
           2 => '待审核',
           3 => '待打包',
           4 => '待付款',
           5 => '待发货',
           6 => '已发货',
           7 => '已完成', 
           8 => '已取消',
           9 => '已拒绝'
        ];
        return ['text' => $map[$value] , 'value'=>$value];
    }
} 