<?php


namespace app\store\model;

use think\Model;
use app\common\model\PackageItem as PackageItemModel;

class PackageItem extends PackageItemModel
{
    protected $createTime = null;
    protected $updateTime = null;
    
   // 批量保存
    public function saveAllData($data,$id){
        $wxapp_id = self::$wxapp_id;
        foreach ($data as $k => $v){
            $data[$k]['order_id'] = $id;
            $data[$k]['wxapp_id'] = $wxapp_id;
        }
        return $this->insertAll($data); 
    }
    
    // 批量保存
    public function saveAllDataTWO($data,$id){
        $wxapp_id = self::$wxapp_id;
        $data['order_id'] = $id;
        $data['wxapp_id'] = $wxapp_id;
        return $this->save($data); 
    }
    
    public function getList($where){
        $model = $this->where('order_id',$where['id']);
        if (isset($where['search'])){
            $model = $model -> where('class_name','like','%'.$where['search']."%");
        }
        return $model->paginate(15);
    }
    
    public  function details($id){
        return $this->find($id);
    }
    
    public function deletes($id){
        return $this->find($id)->delete();
    }
    
    public function images(){
        return $this->hasMany('PackageImage','package_id','order_id');
    }

}