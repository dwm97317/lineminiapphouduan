<?php

namespace app\task\behavior;

use think\Cache;
use app\task\model\Order as OrderModel;
use app\task\model\shop\Inpack as InpackModel;

/**
 * 加盟商订单行为管理
 * Class DealerOrder
 * @package app\task\behavior
 */
class Inpack
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
        $this->model = $model;
        $this->wxappId = $model::$wxapp_id;
        if (!Cache::has("__task_space__ShopOrder__{$this->wxappId}")) {
            $this->model->startTrans();
            try {
                // 发放加盟订单佣金
                $this->grantMoney();
                $this->model->commit();
            } catch (\Exception $e) {
                $this->model->rollback();
            }
            Cache::set("__task_space__ShopOrder__{$this->wxappId}", time(), 3600);
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
    private function grantMoney()
    {
        // 获取未结算佣金的订单列表
        $list = $this->model->getUnSettledList($this->wxappId);
       
        if ($list->isEmpty()) return false;
        // 整理id集
   
        $grantIds = [];
        // 发放加盟订单佣金
        foreach ($list->toArray() as $item) {
            // 已完成的订单
            if ($item['is_pay'] == 1 && $item['status'] ==8) {
                $grantIds[] = $item['id'];
               
                InpackModel::grantMoney($item, $item['inpack_type']);
            }
        }

        // 记录日志
        $this->dologs('grantMoney', ['Ids' => $list]);
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
        $value = 'behavior ShopOrder --' . $method;
        foreach ($params as $key => $val) {
            $value .= ' --' . $key . ' ' . (is_array($val) ? json_encode($val) : $val);
        }
        return log_write($value);
    }

}