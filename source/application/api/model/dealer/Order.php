<?php

namespace app\api\model\dealer;

use app\common\model\dealer\Order as OrderModel;
use app\common\model\Line;
use app\common\service\Order as OrderService;
use app\common\enum\OrderType as OrderTypeEnum;

/**
 * 分销商订单模型
 * Class Apply
 * @package app\api\model\dealer
 */
class Order extends OrderModel
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
    public function getList($user_id, $is_settled = -1)
    {
        $is_settled > 0 && $this->where('is_settled', '=',$is_settled);
        // Optimize: Select only necessary user fields
        $data = $this->with(['user' => function($query){
            $query->field('user_id,nickName,avatarUrl');
        }, 'inpack'])
            ->field('id,order_id,user_id,order_price,create_time,is_settled,first_money,second_money,third_money,first_user_id,second_user_id,third_user_id,order_no,goods_snapshot')
            ->where('first_user_id|second_user_id|third_user_id', '=', $user_id)
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
            
        if ($data->isEmpty()) {
            return $data;
        }

        $line = new Line();
        foreach ($data as $k => $value) {
            $data[$k]['line_name'] = $line->where(['id'=>$value['inpack']['line_id']])->value('name');
            
            // Fix 1: Consolidation Order No (Use inpack order_sn)
            $sn = isset($value['inpack']['order_sn']) ? $value['inpack']['order_sn'] : '';
            if (empty($sn) && isset($value['inpack']['order_no'])) {
                 $sn = $value['inpack']['order_no'];
            }
            if (empty($sn)) {
                $sn = $value['order_no'];
            }
            
            $data[$k]['order_no'] = $sn;
            $data[$k]['order_sn'] = $sn;
            
            // Fix 2: Commission Money Mismatch
            $commission = 0;
            if ($value['first_user_id']==$user_id){
                $commission = $value['first_money'];
            } elseif ($value['second_user_id']==$user_id){
                $commission = $value['second_money'];
            } elseif ($value['third_user_id']==$user_id){
                $commission = $value['third_money'];
            }
            $data[$k]['commission_money'] = $commission;
            $data[$k]['money'] = $commission;
            
            // Optimization: Unset heavy/unused fields
            // We need goods_snapshot IF we fallback to it for image, but frontend prioritized User Avatar.
            // Still, checking frontend code: `item.goods && item.goods[0]...`
            // If we want to keep `goods` working as fallback, we keep goods_snapshot OR parse it then unset.
            // Current Model `getGoodsSnapshotAttr` parses it automatically.
            // Let's Parse and Simplify `goods`.
            $goods = $value['goods_snapshot']; // This is getter, returns array
            if (!empty($goods) && is_array($goods) && isset($goods[0])) {
                // Only keep first goods image
                $data[$k]['goods'] = [[
                    'image' => ['file_path' => $goods[0]['file_path'] ?? ''],
                    'goods_name' => $goods[0]['goods_name'] ?? ''
                ]];
            } else {
                 $data[$k]['goods'] = [];
            }
            
            unset($data[$k]['inpack']); // Remove huge inpack object
            unset($data[$k]['goods_snapshot']); // Remove original snapshot
            // Fields like first_money, etc., keep for debugging or further logic if needed, or unset.
        }
        return $data;
    }
    
    public function inpack(){
        return $this->belongsTo('app\api\model\Inpack','order_id');
    }

    /**
     * 创建分销商订单记录
     * @param $order
     * @param int $order_type 订单类型 (10商城订单 20拼团订单)
     * @return bool|false|int
     * @throws \think\exception\DbException
     */
    public static function createOrder(&$order, $order_type = OrderTypeEnum::MASTER)
    {
        // 分销订单模型
        $model = new self;
        // 分销商基本设置
        $setting = Setting::getItem('basic');
        // 是否开启分销功能
        if (!$setting['is_open']) {
            return false;
        }
        // 获取当前买家的所有上级分销商用户id
        $dealerUser = $model->getDealerUserId($order['user_id'], $setting['level'], $setting['self_buy']);
        // 非分销订单
        if (!$dealerUser['first_user_id']) {
            return false;
        }
        // 计算订单分销佣金
        $capital = $model->getCapitalByOrder($order);
        // 保存分销订单记录
        return $model->save([
            'user_id' => $order['user_id'],
            'order_id' => $order['order_id'],
            'order_type' => $order_type,
            // 'order_no' => $order['order_no'],  // 废弃
            'order_price' => $capital['orderPrice'],
            'first_money' => max($capital['first_money'], 0),
            'second_money' => max($capital['second_money'], 0),
            'third_money' => max($capital['third_money'], 0),
            'first_user_id' => $dealerUser['first_user_id'],
            'second_user_id' => $dealerUser['second_user_id'],
            'third_user_id' => $dealerUser['third_user_id'],
            'is_settled' => 0,
            'goods_snapshot' => json_encode(array_map(function($goods) {
                return [
                    'goods_id' => $goods['goods_id'],
                    'goods_name' => $goods['goods_name'],
                    'goods_num' => $goods['total_num'],
                    'file_path' => isset($goods['image']['file_path']) ? $goods['image']['file_path'] : '',
                ];
            }, is_object($order['goods']) ? $order['goods']->toArray() : $order['goods']), JSON_UNESCAPED_UNICODE),
            'wxapp_id' => $model::$wxapp_id
        ]);
    }

    /**
     * 获取当前买家的所有上级分销商用户id
     * @param $user_id
     * @param $level
     * @param $self_buy
     * @return mixed
     * @throws \think\exception\DbException
     */
    private function getDealerUserId($user_id, $level, $self_buy)
    {
        $dealerUser = [
            'first_user_id' => $level >= 1 ? Referee::getRefereeUserId($user_id, 1, true) : 0,
            'second_user_id' => $level >= 2 ? Referee::getRefereeUserId($user_id, 2, true) : 0,
            'third_user_id' => $level == 3 ? Referee::getRefereeUserId($user_id, 3, true) : 0
        ];
        // 分销商自购
        if ($self_buy && User::isDealerUser($user_id)) {
            return [
                'first_user_id' => $user_id,
                'second_user_id' => $dealerUser['first_user_id'],
                'third_user_id' => $dealerUser['second_user_id'],
            ];
        }
        return $dealerUser;
    }
 

}
