<?php

namespace app\common\model\user;

use app\common\model\BaseModel;

/**
 * 用户唛头模型
 * Class user_mark
 * @package app\common\model\user
 */
class UserMark extends BaseModel
{
    protected $name = 'user_mark';
    protected $updateTime = false;

    /**
     * 关联会员记录表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\User");
    }

    /**
     * 新增记录
     * @param $data
     */
    public static function add($data)
    {
        $static = new static;
        return $static->save(array_merge(['wxapp_id' => $static::$wxapp_id], $data));
    }


}