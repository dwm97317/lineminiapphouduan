<?php
namespace app\web\model;
use app\common\model\Logistics as LogisticsModel;

/**
 * 线路模型
 * Class Express
 * @package app\web\model
 */
class Logistics extends LogisticsModel
{ 
    
     public function getList($sn){
        return $this->setQuery($sn)->order('created_time DESC')->select();
     }
     
     public function setQuery($sn){
         return $this->where('express_num',$sn);
     }
 
}