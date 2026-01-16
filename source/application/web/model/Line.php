<?php
namespace app\web\model;

use app\common\model\Line as LineModel;

/**
 * 线路模型
 * Class Express
 * @package app\web\model
 */
class Line extends LineModel
{
    
     public function getListAll(){
      return $this
        ->order('created_time','desc')
        ->select();
    }
 
    public function getList($where){
        return $this->where($where)->order('sort DESC')->paginate(15);
    }

    public function getLine($where){
      return $this->where($where)->field('id,name')->order('sort DESC')->paginate(15);
  }
    
    // 推荐路线
    public function goodsLine(){
        return $this->where(['status'=>1,'is_recommend'=>1])->field('id,name,tariff,goods_limit,image_id,limitationofdelivery')->select();
    }
    
     public function country(){
        return $this->belongsTo('Country','country_id');
    }
    
    public function upload(){
        return $this->belongsTo('UploadFile','image_id');
    }

}