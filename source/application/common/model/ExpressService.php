<?php

namespace app\common\model;

/**
 * 快递标签配置模型 (Common)
 * Class ExpressService
 * @package app\common\model
 */
class ExpressService extends BaseModel
{
    protected $name = 'express_service';
    protected $pk = 'service_id';

    /**
     * 详情
     * @param $service_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($service_id)
    {
        return static::get($service_id);
    }
}
