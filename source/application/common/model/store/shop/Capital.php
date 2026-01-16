<?php

namespace app\common\model\store\shop;

use app\common\model\BaseModel;
use app\common\enum\inpack\CapitalType;
use app\common\model\store\shop\ShopBonus;
use app\common\model\store\Shop;
/**
 * 仓库申请加盟模型
 * Class Clerk
 * @package app\common\model\store
 */
class Capital extends BaseModel
{
    protected $name = 'store_shop_capital';
    
    
    /**
     * 余额变动场景
     * @param $value
     * @return array
     */
    public function getFlowTypeAttr($value)
    {
        return ['text' => CapitalType::data()[$value]['name'], 'value' => $value];
    }
    
    public static function detail($id,$shop_id){
        return self::where('inpack_id',$id)->where('shop_id',$shop_id)->find();
    }
    
    /**
     * 加盟资金明细
     * @param $data
     */
    public static function add($data)
    {
        $model = new static;
        $model->save(array_merge([
            'wxapp_id' => $model::$wxapp_id
        ], $data));
    }
    
    /**
     * 根据集运单，计算并生成结算单
     * @param $id
     * @return Apply|static
     * @throws \think\exception\DbException
     */
    public static function countMoney($order){

       //检验一下这个集运单是否结算过,结果过则不在执行结算程序
       $capstorage = self::detail($order['id'],$order['storage_id']);
       if(!empty($capstorage)){
           return false;
       }
       $capshop = [] ;
       if(!empty($order['shop_id'])){
         $capshop = self::detail($order['id'],$order['shop_id']);
       }
       //根据仓库的id，查找分成比例，分成模式，并计算对应的分成金额；
       $ShopBonus = new ShopBonus();
       $sendbous = [];
       //寄件分成
       if(empty($capstorage)){
            //得到寄件的分成比例 寄件分成+服务项目分成
           $stprageBonus = $ShopBonus->where(['shop_id' => $order['storage_id'],'line_id'=>$order['line_id']])->find();
           if(!empty($stprageBonus)){
               if($stprageBonus['sr_type'] == 0){
                   $sendbous['send'] = $stprageBonus['proportion'];
               }
               if($stprageBonus['sr_type'] == 1){
                   $sendbous['send'] = $order['free']*$stprageBonus['proportion']/100;
               }
           }else{
              $stprageBonus = Shop::detail($order['storage_id']);
              $sendbous['send'] =  $order['free']* $stprageBonus['send_bonus']/100;
           }
       }
       //派件分成
       if(empty($capshop)){
           //得到寄件的分成比例
           $stprageBonus = $ShopBonus->where(['shop_id' => $order['shop_id'],'line_id'=>$order['line_id']])->find();
           if(!empty($stprageBonus)){
               if($stprageBonus['sr_type'] == 0){
                   $sendbous['pick'] = $stprageBonus['proportion'];
               }
               if($stprageBonus['sr_type'] == 1){
                   $sendbous['pick'] = $order['free']*$stprageBonus['proportion']/100;
               }
           }else{
              $stprageBonus = Shop::detail($order['storage_id']);
              $sendbous['pick']  =  $order['free']* $stprageBonus['pick_bonus']/100;
           }
       }
        return $sendbous;
    }
}
    