<?php
namespace app\web\model;
use app\common\model\ShelfUnitItem as ShelfUnitItemModel;
use think\Db;
use traits\model\SoftDelete;

/**
 * 货位数据管理
 * Class Order
 * @package app\store\model
 */
class ShelfUnitItem extends ShelfUnitItemModel
{

    protected $createTime = null;
    protected $updateTime = null;

    public function post($data){
        $data1['shelf_unit_id'] = $data['shelf_unit'];
        $data1['pack_id'] = $data['pack_id'];
        $data1['created_time'] = getTime();
        $data1['user_id'] = $data['user_id'];
        $data1['express_num'] = $data['express_num'];
        $data1['wxapp_id'] = self::$wxapp_id;
        return $this->save($data1);
    }
    
    // 根据包裹ID 查询货架货位数据
    public function getShelfUnitByPackId($id){
        $shelfItem = $this->where(['pack_id'=>$id])->find();
        if (!$shelfItem){
            return '';
        }
        $shelf = (new ShelfUnit())->with('shelf')->where(['shelf_unit_id'=>$shelfItem['shelf_unit_id']])->find();
        if (!$shelf){
            return '';
        }
        if ($shelf['shelf']){
            return $shelf['shelf']['shelf_name'].$shelf['shelf']['shelf_no'].$shelf['shelf_unit_floor'].'层'.$shelf['shelf_unit_no']."号";
        }else{
            return '未知货架'.$shelf['shelf']['shelf_unit_floor'].'层'.$shelf['shelf_unit_no']."号";
        }
    }
    
    
}
