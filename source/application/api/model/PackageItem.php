<?php
namespace app\api\model;
use app\common\model\PackageItem as PackageItemModel;
use think\Db;
use traits\model\SoftDelete;

/**
 * 订单管理
 * Class Order
 * @package app\store\model
 */
class PackageItem extends PackageItemModel
{

    protected $createTime = null;
    protected $updateTime = null;
    
    public $field = [
    ];

    // 批量保存
    public function saveAllData($data,$id){
        $wxapp_id = self::$wxapp_id;
        foreach ($data as $k => $v){
            $data[$k]['order_id'] = $id;
            $data[$k]['wxapp_id'] = $wxapp_id;
        }
        return $this->insertAll($data); 
    }
}
