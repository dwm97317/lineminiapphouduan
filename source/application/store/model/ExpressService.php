<?php

namespace app\store\model;

use app\common\model\ExpressService as ExpressServiceModel;

/**
 * 快递标签配置模型
 * Class ExpressService
 * @package app\store\model
 */
class ExpressService extends ExpressServiceModel
{
    /**
     * 获取列表
     * @param $params
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($params)
    {
        return $this->where('is_delete', '=', 0)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 添加新记录
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        if (empty($data['service_name'])) {
            $this->error = '请输入标签名称';
            return false;
        }
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->save($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool
     */
    public function edit($data)
    {
        if (empty($data['service_name'])) {
            $this->error = '请输入标签名称';
            return false;
        }
        return $this->save($data) !== false;
    }

    /**
     * 软删除
     * @return bool
     */
    public function remove()
    {
        return $this->save(['is_delete' => 1]);
    }
}
