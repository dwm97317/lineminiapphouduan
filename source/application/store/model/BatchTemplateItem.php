<?php

namespace app\store\model;

use app\common\model\BatchTemplateItem as BatchTemplateItemModel;
/**
 * 商家门店模型
 * Class Shop
 * @package app\store\model\store
 */
class BatchTemplateItem extends BatchTemplateItemModel
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
     * 设置列表查询条件
     * @param array $param
     * @return $this
     */
    private function setListQueryWhere($param = [])
    {
        // 查询参数
        !empty($param['template_id']) && $this->where('template_id', '=', $param['template_id']);
        return $this->order(['step_num' => 'asc']);
    }
    
     /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data,$template_id)
    { 
        if (!$this->validateForm($data)) {
            return false;
        }
        $data['template_id'] = $template_id;
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
     * 表单验证
     * @param $data
     * @return bool
     */
    private function validateForm($data)
    {
        if (!isset($data['title']) || empty($data['title'])) {
            $this->error = "节点名称不能为空";
            return false;
        }
        if (!isset($data['content']) || empty($data['content'])) {
            $this->error = "节点内容不能为空";
            return false;
        }
        if (!isset($data['step_num']) || empty($data['step_num'])) {
            $this->error = "节点步骤不能为空";
            return false;
        }
        if (!isset($data['wait_time']) || empty($data['wait_time'])) {
            $this->error = "节点触发等待时间";
            return false;
        }
        return true;
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
    
}