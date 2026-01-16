<?php
namespace app\common\model;
/**
 * 批次轨迹模型
 * Class BatchTemplate
 * @package app\common\model\BatchTemplate
 */
class BatchTemplateItem extends BaseModel
{
    protected $name = 'batch_template_item';
    
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