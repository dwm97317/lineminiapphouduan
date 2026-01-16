<?php
namespace app\common\model;
/**
 * 批次轨迹模型
 * Class BatchTemplate
 * @package app\common\model\BatchTemplate
 */
class BatchTemplate extends BaseModel
{
    protected $name = 'batch_template';

    /**
     * 批次详情
     * @param $forwarder
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function detail($batch_id)
    {
        return static::get($batch_id);
    }

}