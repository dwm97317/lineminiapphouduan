<?php

namespace app\task\behavior;

use think\Cache;
use app\task\model\Batch as BatchModel;

/**
 * 批次物流行为管理
 * Class DealerOrder
 * @package app\task\behavior
 */
class Batch
{
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
        if (!$model instanceof BatchModel) {
            return new BatchModel and false;
        }
        $this->model = $model;
        $this->wxappId = $model::$wxapp_id;
        if (!Cache::has("__task_space__batch__{$this->wxappId}")) {
            $this->model->startTrans();
            try {
                // 查询需要更新轨迹的批次
                $this->needupdate();
                $this->model->commit();
            } catch (\Exception $e) {
                $this->model->rollback();
            }
            Cache::set("__task_space__batch__{$this->wxappId}", time(), 600);
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
    private function needupdate()
    {
        // 获取需要更新轨迹的
        $setting = $this->model->setting($this->wxappId);
        if($setting['is_autolog']==0){
            return false;
        }
        
        $list = $this->model->getUnSettledList($this->wxappId);
        if ($list->isEmpty()) return false;
        $this->model->setBatchLog($list);
        // 记录日志
        $this->dologs('needupdate', ['Ids' => $list]);
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
        $value = 'behavior Batch --' . $method;
        foreach ($params as $key => $val) {
            $value .= ' --' . $key . ' ' . (is_array($val) ? json_encode($val) : $val);
        }
        return log_write($value);
    }

}