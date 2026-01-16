<?php
namespace app\web\model;

use app\common\model\Wxapp as WxappModel;

/**
 * 微信小程序模型
 * Class Wxapp
 * @package app\web\model
 */
class Wxapp extends WxappModel
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
        'webkey',
        'create_time',
        'update_time'
    ];

}
