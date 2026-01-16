<?php
namespace app\store\model;
use think\Model;
use app\common\model\PackageService as PackageServiceModel;

/**
 * packageService模型
 * Class PackageService
 * @package app\common\model
 */
class PackageService extends PackageServiceModel
{
    public function getList($query){
        return $this->setListQueryWhere($query)
            ->alias('a')
            ->paginate(10,false,[
                'query'=>\request()->request()
            ]);
    }
    
    public function getListAll(){
        return $this->select();
    }

    public function setListQueryWhere($query){
        return $this;
    }

    public function add($data){
        // 表单验证
        if (!$this->onValidate($data)) return false;
        // 保存数据
        $data['wxapp_id'] = self::$wxapp_id;
        if ($this->allowField(true)->save($data)) {
            return true;
        }
        return false;
    }

    public function edit($data)
    {
        // 表单验证
        if (!$this->onValiEdit($data)) return false;
        // 保存数据
        if ($this->allowField(true)->save($data)) {
        }
        return true;
    }


    public function details($id){
        return $this->find($id);
    }
    public function deletes($id){
        return $this->find($id)->delete();
    }


    public function onValidate($data){
        if (!isset($data['name']) || empty($data['name'])) {
            $this->error = '请输入服务名称';
            return false;
        }
        if (!isset($data['type'])) {
            $this->error = '请选择项目类型';
            return false;
        }
        if (!isset($data['price'])){
            $this->error = '请输入服务价格';
            return false;
        }else{
            if($data['price']<0){
                $this->error = '价格不能小于0';
                return false;
            }
        }
        if (isset($data['percentage']) && (($data['percentage'] < 0) || ($data['percentage'] > 100) )) {
            $this->error = '项目收费百分比不正确';
            return false;
        }
        $res = $this->where(['name'=>$data['name']])->find();
        if ($res){
            $this->error = '该服务项目已存在';
            return false;
        }
        return true;
    }
    
    public function onValiEdit($data){
        if (!isset($data['name']) || empty($data['name'])) {
            $this->error = '请输入项目名称';
            return false;
        }
        if (!isset($data['type'])) {
            $this->error = '请选择项目类型';
            return false;
        }
        if (isset($data['price']) && ($data['price'] <0)) {
            $this->error = '金额不能小于0';
            return false;
        }
        if (isset($data['percentage']) && (($data['percentage'] < 0) || ($data['percentage'] > 100) )) {
            $this->error = '项目收费百分比不正确';
            return false;
        }
        return true;
    }
}
