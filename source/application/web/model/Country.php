<?php
namespace app\web\model;

use app\common\model\Country as CountryModel;

/**
 * 物流公司模型
 * Class Express
 * @package app\web\model
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
    
    // 根据ID 查找字段
    public function getValueById($id,$field){
        return $this->where(['id'=>$id])->value($field);
    }
    
    public function getListAll(){
           return $this
           ->order(["is_top desc","is_hot desc","sort"=>"desc"])
           ->paginate(300,false, [
                'query' => \request()->request()
            ]);
    }
}