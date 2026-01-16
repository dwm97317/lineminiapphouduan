<?php
namespace app\store\controller;

use app\common\model\Logistics as LogisticsModel;

/**
 * 线路设置
 * Class Delivery
 * @package app\store\controller\setting
 */
class Logistics extends Controller
{


    /**
     * 删除模板
     * @param $delivery_id
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delete($id)
    {
        $model = (new LogisticsModel())->details($id);
        if (!$model->remove()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
}
