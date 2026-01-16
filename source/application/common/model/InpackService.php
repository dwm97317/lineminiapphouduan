<?php

namespace app\common\model;
use app\common\model\store\shop\Capital;
use app\common\model\store\shop\ShopBonus;
use app\common\model\store\Shop;
/**
 * 包裹图片模型
 * Class GoodsImage
 * @package app\common\model
 */
class InpackService extends BaseModel
{
    protected $name = 'inpack_service';
    protected $updateTime = false;
    
    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo('PackageService','service_id','id');
    }
    
    /**
     * 根据集运单，计算费用
     * @param $id
     * @return Apply|static
     * @throws \think\exception\DbException
     */
    public static function countMoney($order){
       //检验一下这个集运单是否结算过,结算过则不在执行结算程序
       $capstorage = Capital::detail($order['id'],$order['storage_id']);
         
       if(!empty($capstorage)){
           return false;
       }

       //根据仓库的id，查找分成比例，分成模式，并计算对应的分成金额；
       $ShopBonus = new ShopBonus();
       $service = 0;
       //寄件分成
            $static = new static();
            //得到寄件的分成比例 寄件分成+服务项目分成
            $shop = Shop::detail($order['storage_id']);
            $servicelist = $static->with('service')->where('inpack_id',$order['id'])->select();
            // dump($servicelist->toArray());die;
            $service= 0;
            foreach ($servicelist as $key =>$value){
                //获取到服务项目，服务项目里边0 固定金额算服务费，1 是按运费的比例算费用
                $stprageBonus = $ShopBonus->where(['shop_id' => $order['storage_id'],'line_id' => $value['service_id']])->find();
                if(empty($stprageBonus)){
                    //当没有设置服务项目的分成比例时，就默认不分
                    if($value['service']['type']==0){
                        $service = ($value['service_sum'] * $value['service']['price'] * $shop['service_bonus'])/100 + $service; //对的
                    }
                    
                    if($value['service']['type']==1){
                        // dump($service);
 
                        $service = ($value['service_sum'] * $order['free'] * $value['service']['percentage'] * $shop['service_bonus'])/10000 + $service; //对的
                        
                    }
                    
                }else{
            //   dump($service);
                    //当服务项目按运费比例时
                        //  dump($order['free']);die;
                    if($value['service']['type']==1){
                        // 服务项目数量 * 服务项目金额 * 订单运费/100
                        $servicefff = ($value['service_sum'] * $value['service']['percentage'] * $order['free']) /100 + $service;
                        //0 固定金额  1按比例分成
                        if($stprageBonus['bonus_type']==0){
                            $service = $stprageBonus['proportion'] * $value['service_sum'] + $service;
                        }
                        if($stprageBonus['bonus_type']==1){
                            
                            $service = $stprageBonus['proportion'] * $servicefff*$value['service_sum']/100 + $service;
                        }
                      
                    }
                    //当服务项目按固定金额时
                    if($value['service']['type']==0){
                        //服务费需要按规则分给仓库
                        //0 固定金额  1按比例分成
                        if($stprageBonus['bonus_type']==0){
                            $service = $stprageBonus['proportion']* $value['service_sum'] + $service; //对的
                        }
                        if($stprageBonus['bonus_type']==1){
                            $service = ($stprageBonus['proportion'] * $value['service']['price'] * $value['service_sum']) /100 + $service;  //对的
                        }
                    }
                }
            }
            return $service;
    }
    
    
}
