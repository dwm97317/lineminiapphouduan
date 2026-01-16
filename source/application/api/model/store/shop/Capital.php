<?php

namespace app\api\model\store\shop;

use app\common\model\store\shop\Capital as CapitalModel;

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
     * 计算加盟商的佣金总和
     * @param $user_id
     * @param int $is_settled
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function countIncome($shop_id, $type)
    {
        switch ($type) {
            case 'today':
                $today = strtotime(date("Y-m-d"),time());
                $end = $today+60*60*24;
                $this->where('create_time','between',[$today,$end]);
                $data = $this->where('shop_id', '=', $shop_id)->where('flow_type','<>',30)->sum('money');
                break;
                
            case 'week':
                $nowDate = date("Y-m-d");
                $week = date('w',strtotime($nowDate));
                $startTime = strtotime("$nowDate -".($week ? $week - 1 : 6).' days');//本周第一天
                $overTime = $startTime + 86400*7 -1; //本周最后一天
                $this->where('create_time','between',[$startTime,$overTime]);
                $data = $this->where('shop_id', '=', $shop_id)->where('flow_type','<>',30)->sum('money');
                break;
                
            case 'mouth':
                $startTime =mktime(0,0,0,date('m'),1,date('Y'));  
                //本月结束时间时间戳
                $overTime =mktime(23,59,59,date('m'),date('t'),date('Y')); 
                $this->where('create_time','between',[$startTime,$overTime]);
                $data = $this->where('shop_id', '=', $shop_id)->where('flow_type','<>',30)->sum('money');
                break;
            default:
                // code...
                break;
        }
        return $data;
    }
    
    /**
     * 获取分销商订单列表
     * @param $user_id
     * @param int $is_settled
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($shop_id, $type = -1)
    {
        $type > 0 && $this->where('follow_type', '=',$type);
        $data = $this
            ->where('shop_id', '=', $shop_id)
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