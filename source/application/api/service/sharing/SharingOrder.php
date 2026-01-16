<?php
namespace app\api\service\sharing;
use app\api\service\Basics;
use app\api\model\sharing\SharingOrder as ShareOrderModel;
use app\api\model\sharing\SharingOrderItem;
use app\api\model\sharing\SharingOrderAddress;
use app\api\model\sharing\Setting as SharingSetting;
use app\api\model\Package;

class SharingOrder extends Basics {
    
    // 检查拼团订单是否可打包
    public function checkIsInPack($list){
 
        foreach ($list as $key => $value) {
            // code...
            $list[$key]['applypack'] = false;
        }   
        return $list;
    }
    
    // 获取已团包裹重量    
    public function getPackageWeight($list){
        $OrderItem = (new SharingOrderItem());
        foreach ($list as $key => $val){
            $hasWeight = 0;
            $item = $OrderItem->where(['order_id'=>$val['order_id']])->select();
            if (!$item->isEmpty()){
                $hasWeight = $this->getHasWeight($item);
            }
            $list[$key]['has_weight'] = $hasWeight;
            $hasWeight = $hasWeight>$val['predict_weight']?$val['predict_weight']:$hasWeight;
            $list[$key]['remain_weight'] = round($val['predict_weight'] - $hasWeight,2);
        }
        return $list;
    } 
    
    // 获取已团包裹重量    
    public function getHasWeight($item){
         $Package = (new Package());
         $packIds = array_column($item->toArray(),'package_id');
         $packlist = $Package->where('id','in',$packIds)->select();
         $allHeight = 0;
         foreach ($packlist as $val){
             $volumWeight = ($val['length']*$val['width']*$val['height'])/6000;
             // 体积重 和 重量 取重者
             $weight = $volumWeight>$val['weight']?$volumWeight:$val['weight'];
             $allHeight += $weight;
         }
         return $allHeight;
        //  $setting = (new Setting());
         
    }
    
    public function getMainAddressInfo($list){
         $OrderItem = (new SharingOrderAddress());
        foreach ($list as $key => $val){
            $address = '';
            $item = $OrderItem->where(['order_id'=>$val['order_id'],'is_head'=>1])->find();
            if ($item){
                $address = $item['country'].$item['province'].$item['city'].$item['region'];
            }
            $list[$key]['address'] = $address;
        }
        return $list;
    }
    
    // 检查是否能够发布
    public function checkPubiler($user,$post){
        $setting = SharingSetting::getItem('sharp');
        $order_id = $post['share_id']??$post['order_id'];
        if ($setting['is_own_join'] == 0){
            $sharingData = (new ShareOrderModel())->find($order_id);
            if ($sharingData['member_id']==$user){
                return false;
            }
        }
        return true;
    }
}