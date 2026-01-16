<?php
namespace app\web\model;

use app\common\model\WxappHelp as WxappHelpModel;

/**
 * 小程序帮助中心
 * Class WxappHelp
 * @package app\web\model
 */
class WxappHelp extends WxappHelpModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'create_time',
        'update_time',
    ];
}
