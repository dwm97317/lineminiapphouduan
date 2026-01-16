<?php

namespace app\task\behavior;

use think\Cache;
use app\task\model\shop\Inpack as InpackModel;

/**
 * 加盟商订单行为管理
 * Class DealerOrder
 * @package app\task\behavior
 */
class Detained
{
    /* @var DealerOrderModel $model */
    private $model;
    private $wxappId;
    /**
     * 执行函数
     * @param $model
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function run($model)
    {
        if (!$model instanceof InpackModel) {
            return new InpackModel and false;
        }
        $this->model = new InpackModel;
        $this->wxappId = $model::$wxapp_id;
        if (!Cache::has("__task_space__Detained__{$this->wxappId}")) {
            $this->model->startTrans();
            try {
                // 检查滞留件
                $this->detained();
                $this->model->commit();
            } catch (\Exception $e) {
                $this->model->rollback();
            }
            Cache::set("__task_space__Detained__{$this->wxappId}",86400);
        }
        return true;
    }

    /**
     * 发放加盟订单佣金
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function detained()
    {
        // 获取未结算佣金的订单列表
        $list = $this->model->getdetainedList($this->wxappId);
      
        if ($list->isEmpty()) return false;
        // 整理id集
        // $grantIds = [];
    // dump($list->toArray()[0]);
        foreach ($list->toArray() as $key => $item) {
                
            // 滞留件的订单
            if ($item['status'] == 7) {
                InpackModel::detained($item, $item['inpack_type']);
            }
        }
        // 记录日志
        $this->dologs('detained', ['Ids' => $list]);
        return true;
    }

    /**
     * 记录日志
     * @param $method
     * @param array $params
     * @return bool|int
     */
    private function dologs($method, $params = [])
    {
        $value = 'behavior Detained --' . $method;
        foreach ($params as $key => $val) {
            $value .= ' --' . $key . ' ' . (is_array($val) ? json_encode($val) : $val);
        }
        return log_write($value);
    }

}