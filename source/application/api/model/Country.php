<?php
namespace app\api\model;

use app\common\model\Country as CountryModel;

/**
 * 物流公司模型
 * Class Express
 * @package app\api\model
 */
class Country extends CountryModel
{
    // 查询快递列表
    public function queryCountry($where){
        if ($where){
            $this -> whereLike('title','%'.$where."%");
        }
        return $this->order('id ASC')->select();
    }
    
    public function queryHotCountry($where){
        if ($where){
            $this -> whereLike('title','%'.$where."%");
        }
        return 
        $this->where('is_hot',1)
        ->order('sort DESC')
        ->select();
    }
    
    // 查询快递列表
    public function queryTopCountry(){
        return $this->where('is_top',1)->find();
    }
    
    
    // 根据ID 查找字段
    public function getValueById($id,$field){
        return $this->where(['id'=>$id])->value($field);
    }
}