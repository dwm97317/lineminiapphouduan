<?php
namespace app\web\model;

use app\common\model\Express as ExpressModel;

/**
 * 物流公司模型
 * Class Express
 * @package app\web\model
 */
class Express extends ExpressModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'express_code',
        'sort',
        'wxapp_id',
        'create_time',
        'update_time'
    ];
    
    // 查询快递列表
    public function queryExpress(){
        return $this->order('sort DESC')->select();
    }

    // 根据ID 查找字段
    public function getValueById($id,$field){
      return $this->where(['express_id'=>$id])->value($field);
    }
}