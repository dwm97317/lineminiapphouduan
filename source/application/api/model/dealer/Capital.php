<?php

namespace app\api\model\dealer;

use app\common\model\dealer\Capital as CapitalModel;

/**
 * 分销商资金明细模型
 * Class Apply
 * @package app\api\model\dealer
 */
class Capital extends CapitalModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'update_time',
    ];
    
    /**
     * 获取分销商订单列表
     * @param $user_id
     * @param int $is_settled
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($user_id, $type = -1)
    {
        $type > 0 && $this->where('follow_type', '=',$type);
        $data = $this
            ->where('user_id', '=', $user_id)
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
        if ($data->isEmpty()) {
            return $data;
        }
        return $data;
    }
    

}