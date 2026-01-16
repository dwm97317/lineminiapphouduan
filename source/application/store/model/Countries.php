<?php
namespace app\store\model;
use app\common\model\Country;
use think\Db;

class Countries extends Country
{
    public function getList($name){
            return $this
            ->where(function($query) use ($name) {
               $query->where('title','like','%'.$name.'%')
               ->whereor('code','like','%'.$name.'%');
            })
            ->paginate(300,false, [
                'query' => \request()->request()
            ]);
    }
    
    public function getListAll(){
           return $this
            ->order(["is_top desc","is_hot desc","sort"=>"desc"])
            ->paginate(300,false, [
                'query' => \request()->request()
            ]);
    }
    
    public function getListAllCountry(){
        return $this->select();
    }
    
     public function details($id){
        return $this->find($id);
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
    
    public function copy(){
        $data = Db::name('countries')->where('wxapp_id','=',10001)->select();
        $country=[];
        foreach ($data as $key =>$value) {
            $country[$key] = $value;
            unset($country[$key]['id']);
            $country[$key]['wxapp_id'] =self::$wxapp_id;
        }
        if ($this->insertAll($country)) {
            return true;
        }
        return false;
    }
    
    
    // 批量新增
    public function addAll(){
        
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
    
    public function deletes($id){
        return $this->find($id)->delete();
    }


    public function onValidate($data){
        if (!isset($data['title']) || empty($data['title'])) {
            $this->error = '请输入国家代码';
            return false;
        }
        if (!isset($data['code']) || empty($data['code'])) {
            $this->error = '请输入国家代码';
            return false;
        }
        if (self::where(['title'=>$data['title']])->find()){
            $this->error = '该国家已在数据库中';
            return false;
        }
        return true;
    }
    
    public function onValiEdit($data){
        if (!isset($data['title']) || empty($data['title'])) {
            $this->error = '请输入打包类型';
            return false;
        }
        if (!isset($data['code']) || empty($data['code'])) {
            $this->error = '请输入打包价格';
            return false;
        }
        return true;
    }
}