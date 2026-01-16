<?php

namespace app\store\model;

use app\common\model\BatchTemplate as BatchTemplateModel;
use think\Session;
/**
 * 商家门店模型
 * Class Shop
 * @package app\store\model\store
 */
class BatchTemplate extends BatchTemplateModel
{
    /**
     * 获取列表数据
     * @param array $param
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($param = [])
    {
        // 查询列表数据
        return $this->setListQueryWhere($param)
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }
    
    /**
     * 获取列表数据
     * @param array $param
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getAllList($param = [])
    {
        // 查询列表数据
        return $this->setListQueryWhere($param)
            ->select();
    }
    
    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data)
    { 
        if (!$this->validateForm($data)) {
            return false;
        }
        return $this->allowField(true)->save($this->createData($data));
    }
    
    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function edit($data)
    { 
        if (!$this->validateForm($data)) {
            return false;
        }
        // dump($data);die;
        return $this->allowField(true)->save($this->createData($data));
    }
    
    /**
     * 创建数据
     * @param array $data
     * @return array
     */
    private function createData($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        $data['create_time'] = time();
        return $data;
    }
    
    /**
     * 表单验证
     * @param $data
     * @return bool
     */
    private function validateForm($data)
    {
        if (!isset($data['template_name']) || empty($data['template_name'])) {
            $this->error = "模板名称不能为空";
            return false;
        }
        return true;
    }

    /**
     * 设置列表查询条件
     * @param array $param
     * @return $this
     */
    private function setListQueryWhere($param = [])
    {
        // 查询参数
        !empty($param['template_id']) && $this->where('template_id', '=', $param['template_id']);
        return $this->order(['create_time' => 'desc']);
    }
    
    public function setDelete(){
         return $this->save(['is_delete' => 1]);
    }
    
}