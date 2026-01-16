<?php
namespace app\api\model;
use app\common\model\Shelf as ShelfModel;
use think\Db;
use traits\model\SoftDelete;

/**
 * 货位管理
 * Class Order
 * @package app\store\model
 */
class Shelf extends ShelfModel
{

    protected $createTime = null;
    protected $updateTime = null;
    
}
