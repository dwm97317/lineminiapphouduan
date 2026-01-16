<?php
namespace app\store\model;
use think\Model;
use app\common\model\Shelf as ShelfModel;


/**
 * 线路模型
 * Class Delivery
 * @package app\common\model
 */
class Shelf extends ShelfModel
{
    public function getList($query){
        return $this->setListQueryWhere($query)
        ->alias('a')
        ->with('storage')
        ->paginate(10,false,[
            'query'=>\request()->request()
        ]);
    }
    
     public function getAllList($query){
        return $this->setListQueryWhere($query)
        ->alias('a')
        ->with('storage')
        ->select();
    }

    public function setListQueryWhere($query){
        !empty($query['ware_no'])  && $this->where('ware_no','=',$query['ware_no']);
        return $this;
    }
    
    public function add($data){
       // 表单验证
      if (!$this->onValidate($data)) return false;
       // 保存数据
       $data['wxapp_id'] = self::$wxapp_id;
       $data['created_time'] = time();

       $res = $this->allowField(true)->insertGetId($data);
       if ($res) {
           if ($data['shelf_column'] && $data['shelf_row']){
              $shelf_data = (new ShelfUnit())->getShelfUnitData($data,$res);
              $result = (new ShelfUnit())->insertAll($shelf_data);
              if(!$result){return false;}
           }
           return true;
       }else{
           return false;
       }
       return true;
    }

    
    public function details($id){
      return $this->find($id);
   }

    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function edit($data)
    {
        // 表单验证
        if (!$this->onValidate($data)) return false;
        // 保存数据
        if ($this->allowField(true)->save($data)) {
            return true;
        }
        return false;
    }
    
    public function onValidate($data){
      if (!isset($data['shelf_name']) || empty($data['shelf_name'])) {
         $this->error = '请输入货架名称';
         return false;
      }
      if (!isset($data['shelf_no']) || empty($data['shelf_no'])) {
        $this->error = '请输入货架编号';
        return false;
      }
      $model = $this->where(['shelf_no'=>$data['shelf_no']]);
      if (input('id')){
          $model->where('id','neq',input('id'));   
      } 
      $res = $model->find();
      if ($res){
          $this->error = '该货架编号已存在';
          return false;
      }
      return true;
    }
    
    public function setDelete($id){
        (new ShelfUnit())->where(['shelf_id'=>$id])->delete();
        return $this->delete();
    }
    
    public function storage(){
        return $this->belongsTo('app\store\model\store\\Shop','ware_no')->field('shop_name,shop_id,province_id,city_id,region_id');
    }
}
