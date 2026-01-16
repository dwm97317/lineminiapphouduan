<?php

namespace app\task\model;

use app\common\model\Batch as BatchModel;
use app\common\model\Setting;
use think\Hook;

/**
 * 批次模型
 * Class Express
 * @package app\task\model
 */
class Batch extends BatchModel
{
    public function setting($wxappid){
        $values = Setting::getItem('batch',$wxappid);
        return $values;
    }
    
    /**
     * 获取开始发货的批次
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUnSettledList($wxappid)
    {
   
        return (new BatchModel())
            ->with(['template'])
            ->where('status', '=', 1)
            ->where('is_delete', '=', 0)
            ->where('last_time', '<=',time())
            ->where('wxapp_id',$wxappid)
            ->where('template_id','>',0)
            ->select();
    }
}