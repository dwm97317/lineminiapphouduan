<?php

namespace app\api\model;

use app\common\model\Wxapp as WxappModel;

/**
 * LINE小程序模型
 * Class LineApp
 * @package app\api\model
 */
class LineApp extends WxappModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'app_name',
        'app_id',
        'app_secret',
        'mchid',
        'apikey',
        'create_time',
        'update_time'
    ];

}
