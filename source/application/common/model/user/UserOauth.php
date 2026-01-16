<?php

namespace app\common\model\user;

use app\common\model\BaseModel;

/**
 * 用户唛头模型
 * Class user_mark
 * @package app\common\model\user
 */
class UserOauth extends BaseModel
{
    protected $name = 'user_oauth';
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