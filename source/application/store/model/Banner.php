<?php
namespace app\store\model;
use think\Model;
use app\common\model\Banner as BannerModel;
/**
 * 线路模型
 * Class Delivery
 * @package app\common\model
 */
class Banner extends BannerModel
{
    /**
     * 添加新记录
     * @param $data
     * @return bool|int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function add($data)
    {
        // 表单验证
        if (!$this->onValidate($data)) return false;
        $data['wxapp_id'] = self::$wxapp_id;
        $data['created_time'] = time();
        if ($this->allowField(true)->save($data)) {
            return true;
        }
        return false;
    }

    public function getList($query){
        return $this->setListQueryWhere($query)
        ->alias('a')
        ->with('image')
        ->paginate(10,false,[
            'query'=>\request()->request()
        ]);
    }

    public function setListQueryWhere($query){
        return $this;
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
        $data['wxapp_id'] = self::$wxapp_id;
        // 保存数据
        if ($this->allowField(true)->save($data)) {
            return true;
        }
        return false;
    }

    /**
     * 表单验证
     * @param $data
     * @return bool
     */
    private function onValidate($data)
    {
        if (!isset($data['title'])) {
            $this->error = '请输入轮播图名称';
            return false;
        }
        return true;
    }

    /**
     * 删除记录
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        return $this->delete();
    }


}
