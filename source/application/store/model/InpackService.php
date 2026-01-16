<?php
namespace app\store\model;

use app\common\model\InpackService as InpackServiceModel;

/**
 * 集运单服务项目模型
 * Class GoodsImage
 * @package app\store\model
 */
class InpackService extends InpackServiceModel
{
    
     /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo('PackageService','service_id','id');
    }
    
    
    //新增服务项目
    public function add($data){
        $dat['inpack_id'] = $data['inpack']['id'];
        $dat['service_id'] = $data['inpack']['service_id'];
        $dat['service_sum']= $data['inpack']['service_sum'];
        $dat['wxapp_id'] = self::$wxapp_id;
        $dat['create_time'] = time();
        $result = $this->where('inpack_id',$dat['inpack_id'])->where('service_id',$dat['service_id'])->find();
  
        if($result){
            $this->error = "存在该服务项目，请勿重复添加";
            return false;
        }
        if($this->save($dat)){
            return true;
        }
        return false;
    }
    
     /**
     * 处理包装服务
     * @var array
     */
    public function doservice($inpack,$pack_ids){
        $packArr = explode(',',$pack_ids);
        $this->where('inpack_id',$inpack)->delete();
        foreach ($packArr as $key => $value){
            $pack[$key]['inpack_id'] = $inpack;
            $pack[$key]['service_id'] = $value;
            $pack[$key]['wxapp_id'] = self::$wxapp_id;
            $pack[$key]['create_time'] = time();
        }
        $res = $this->saveAll($pack);
        if(!$res){
            return false;
        }
        return true;
    }
    
    
    //删除
    public function deletes($id){
        return $this->find($id)->delete();
    }
    
}
