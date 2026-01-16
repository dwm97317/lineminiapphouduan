<?php

namespace app\store\model\store\shop;

use app\common\model\store\shop\ShopBonus as ShopBonusModel;

/**
 * 仓库分成规则模型
 * Class ShopBonus
 * @package app\store\model\store\shop
 */
class ShopBonus extends ShopBonusModel
{
    /**
     * 获取列表数据
     * @param string $search
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($search = '')
    {
        // 检索查询条件
        // dump($search);die;
        !empty($search['extract_shop_id']) && $this->where('shop_id', '=',$search['extract_shop_id']);
        // !empty($search['search']) && $this->where('shop_name', '=',$search['search']);
        // 查询列表数据
        return $this
            ->with(['shop','line'])
            ->where('source',10)
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }
    
    /**
     * 获取列表数据
     * @param string $search
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getServiceList($search = '')
    {
        // 检索查询条件
        // dump($search);die;
        !empty($search['extract_shop_id']) && $this->where('shop_id', '=',$search['extract_shop_id']);
        // !empty($search['search']) && $this->where('shop_name', '=',$search['search']);
        // 查询列表数据
        return $this
            ->with(['shop','service'])
            ->where('source',20)
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }
    
    /**
     * 编辑记录
     * @param $data
     * @return false|int
     */
    public function edit($data)
    {
        if (!$this->validateForm($data)) {
            return false;
        }
        return $this->allowField(true)->save($this->createData($data)) !== false;
    }
    
    /**
     * 创建数据
     * @param array $data
     * @return array
     */
    private function createData($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        return $data;
    }
    
    /**
     * 表单验证
     * @param $data
     * @return bool
     */
    private function validateForm($data)
    {
        if (!isset($data['proportion']) || empty($data['proportion'])) {
            return false;
        }
        return true;
    }
    
    /**
     * 硬删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->delete();
    }
    
    
    /**
     * 新增分成
     * @param Array 
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function add($data){
      $data['wxapp_id'] = self::$wxapp_id;
      $data['create_time'] = time();
      return $this->allowField(true)->save($data);
    }

    
}