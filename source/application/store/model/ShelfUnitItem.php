<?php
namespace app\store\model;
use think\Model;
use app\common\model\ShelfUnitItem as ShelfUnitItemModel;
/**
 * 货架货位模型
 * Class Delivery
 * @package app\common\model
 */
class ShelfUnitItem extends ShelfUnitItemModel
{
    
    public function item($id,$where=[]){
        $shelfU = $this->where(['shelf_unit_id'=>$id])->where($where)->with('Package')->select();
        $shelfUnit = (new ShelfUnit());
        foreach ($shelfU as $k => $v){
            $shelf = $shelfUnit->with('shelf')->where(['shelf_unit_id'=>$v['shelf_unit_id']])->find();
            if (!$shelf){
                $shelfU[$k]['shelf'] = '货架数据错误';
            }
            if ($shelf['shelf']){
                 $shelfU[$k]['shelf'] = $shelf['shelf']['shelf_name'].$shelf['shelf']['shelf_no'].$shelf['shelf_unit_floor'].'层'.$shelf['shelf_unit_no']."号";
            }else{
                $shelfU[$k]['shelf'] = '未知货架'.$shelf['shelf']['shelf_unit_floor'].'层'.$shelf['shelf_unit_no']."号";
            }
        }
        return $shelfU;
    }
     
    public function post($data){
        // dump($data);die;
        $data1['shelf_unit_id'] = $data['shelf_unit_id'];
        $data1['pack_id'] = $data['pack_id'];
        $data1['created_time'] = $data['created_time'];
        $data1['user_id'] = $data['user_id'];
        $data1['express_num'] = $data['express_num'];
        $data1['wxapp_id'] = self::$wxapp_id;
        return $this->insert($data1);
    }
    
    public function postplus($data){
        // dump($data);die;
        $data1['shelf_unit_id'] = $data['shelf_unit'];
        $data1['pack_id'] = $data['pack_id'];
        $data1['created_time'] = $data['created_time'];
        $data1['user_id'] = $data['user_id'];
        $data1['express_num'] = $data['express_num'];
        $data1['wxapp_id'] = self::$wxapp_id;
        return $this->insert($data1);
    }
    
    //  
    public function getItemWithPackage($where){ 
        
        return $this->where($where)->select();
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
       
    
    public function shelfDown($ids){
        $idarr = [];
        if (is_string($ids)){
            $idarr = [$ids];
        }else{
            $idarr = $ids;
        }
        $shelfUnitItem = $this->whereIn('pack_id',$idarr)->select();
        if ($shelfUnitItem->isEmpty()){
            (new Package())->whereIn('id',$idarr)->update(['status'=>7,'updated_time'=>getTime()]);
            return true;
        }
        (new Package())->whereIn('id',$idarr)->update(['status'=>7,'updated_time'=>getTime()]);
        return $this->whereIn('pack_id',$idarr)->delete();
    }
    
    public function Package(){
        return $this->belongsTo('Package','pack_id');  
    } 
}
