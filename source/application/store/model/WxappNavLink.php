<?php

namespace app\store\model;

use app\common\model\wxapp\Navlink;

/**
 * 小程序导航
 * Class WxappNav
 * @package app\store\model
 */
class WxappNavLink extends Navlink
{
    public function getList($query=[]){
        return $this->setListQueryWhere($query)
        ->alias('a')
        ->with('image')
        ->order('sort','asc')
        ->paginate(10,false,[
            'query'=>\request()->request()
        ]);
    }
    
    public function setListQueryWhere($query){
        return $this;
    }
    
    /**
     * 新增记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->allowField(true)->save($data);
    }

    /**
     * 更新记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 删除记录
     * @return int
     */
    public function remove() {
        return $this->delete();
    }

}
