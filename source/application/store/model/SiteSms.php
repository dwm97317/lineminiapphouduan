<?php
namespace app\store\model;
use think\Model;
use app\common\model\SiteSms as SiteSmsModel;
/**
 * 线路模型
 * Class Delivery
 * @package app\common\model
 */
class SiteSms extends SiteSmsModel
{
   
    
    public function add($data){
       // 表单验证
       if (!$this->onValidate($data)) return false;
       // 保存数据
       $data['wxapp_id'] = self::$wxapp_id;
       $data['created_time'] = getTime();
       $data['updated_time'] = getTime();
       $res = $this->allowField(true)->insertGetId($data);
       
       if ($res){
           return true;
       }
       return false;
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
        }
        return false;
    }
    
    public function onValidate($data){
      if (!isset($data['user_id']) || empty($data['user_id'])) {
         $this->error = '请选择用户';
         return false;
      }
      if (!isset($data['content']) || empty($data['content'])) {
        $this->error = '请输入内容';
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
