<?php
namespace app\web\model\user;

use app\common\model\user\BalanceLog as BalanceLogModel;

/**
 * 用户余额变动明细模型
 * Class BalanceLog
 * @package app\web\model\user
 */
class BalanceLog extends BalanceLogModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
    ];

    /**
     * 获取账单明细列表
     * @param $userId
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($userId,$where=[])
    {
        // 获取列表数据
        return $this->where('user_id', '=', $userId)
            ->where($where)
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

}