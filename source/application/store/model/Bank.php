<?php
namespace app\store\model;

use app\common\model\Bank as BankModel;

class Bank extends BankModel
{

   public function getList($query=[]){
        return $this->setListQueryWhere($query)
        ->alias('a')
        ->paginate(10,false,[
            'query'=>\request()->request()
        ]);
    }

    public function setListQueryWhere($query){
        return $this;
    }

    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        $data['created_time'] = getTime();
        return $this->allowField(true)->save($data);
    }
    
    public function getPickAll($query=[]){
      return $this->setListQueryWhere($query)
        ->where('status',1)
        ->select();
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        return $this->allowField(true)->save($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     */
    public function editAccount($data)
    {
        if (!$data['password']){
             $this->error = '请选择账号密码';
             return false;    
        }
        $data['password'] = yoshop_hash($data['password']);
        return $this->allowField(true)->save($data);
    }


    /**
     * 删除记录
     * @return bool|int
     */
    public function remove()
    {
        return $this->delete();
    }

}