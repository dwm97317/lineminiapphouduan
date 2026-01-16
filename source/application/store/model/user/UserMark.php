<?php

namespace app\store\model\user;

use app\common\model\user\UserMark as UserMarkModel;

/**
 * 唛头细模型
 * Class BalanceLog
 * @package app\store\model\user
 */
class UserMark extends UserMarkModel
{
    /**
     * 获取余额变动明细列表
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($query = [])
    {
        // 设置查询条件
        !empty($query) && $this->setQueryWhere($query);
        // 获取列表数据
        return $this->with(['user'])
            ->alias('log')
            ->field('log.*')
            ->join('user', 'user.user_id = log.user_id')
            ->order(['log.create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 设置查询条件
     * @param $query
     */
    private function setQueryWhere($query)
    {
        // 用户ID
        // $query['user_id'] > 0 && $this->where('log.user_id', '=', $query['user_id']);
        
        isset($query['member_id']) && ($query['member_id'] > 0 || empty($query['member_id'])) && $this->where('log.user_id', '=', $query['member_id']);
        // 用户昵称
        !empty($query['search']) && $this->where('user.nickName|user.user_id|log.mark', 'like', "%{$query['search']}%");
    }

}