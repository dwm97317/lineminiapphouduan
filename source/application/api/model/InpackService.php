<?php

namespace app\api\model;

use app\common\model\InpackService as InpackServiceModel;

/**
 * 集运单服务项目模型
 * Class GoodsImage
 * @package app\api\model
 */
class InpackService extends InpackServiceModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'create_time',
        'update_time',
    ];
    

    
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
}
