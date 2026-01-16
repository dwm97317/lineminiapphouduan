<?php
namespace app\common\model;

/**
 * 订单收货地址模型
 * Class OrderAddress
 * @package app\common\model
 */
class Country extends BaseModel
{
    protected $name = 'countries';
    protected $updateTime = false;
    
    public function getListAllCountry(){
        return $this->select();
    }
    
}
