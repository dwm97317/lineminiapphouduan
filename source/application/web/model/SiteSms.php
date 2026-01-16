<?php
namespace app\web\model;
use think\Model;
use app\common\model\SiteSms as SiteSmsModel;
/**
 * 线路模型
 * Class Delivery
 * @package app\common\model
 */
class SiteSms extends SiteSmsModel
{
    
    public function getList($query){
        return $this
        ->setListQueryWhere($query)
        ->order('created_time DESC')
        ->paginate(10,false,[
            'query'=>\request()->request()
        ]);
    }

    public function setListQueryWhere($query){
      
        isset($query['member_id']) && $this->where('user_id','=',$query['member_id']);
        isset($query['is_read']) && $this->where('is_read','=',$query['is_read']);
        return $this;
    }

    public function details($id){
        return $this->find($id);
    }

}
