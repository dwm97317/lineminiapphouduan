<?php
namespace app\api\controller\sharing_origin;

use app\api\controller\Controller;
use app\common\model\sharing\SharingOrder;
use app\common\model\sharing\SharingOrderItem;

use app\common\model\Inpack;
use app\api\service\sharing\LogisticsPrice;
use app\common\model\sharing\Setting;

/**
 * 集运拼团广场API (对接 yoshop_sharing_tr_order)
 */
class Logistics extends Controller
{
    /**
     * 获取拼团列表
     */
    public function square()
    {
        $params = $this->request->param();
        $model = new SharingOrder();
        
        // 筛选条件
        if (isset($params['storage_id']) && $params['storage_id'] > 0) {
            $model->where('storage_id', '=', $params['storage_id']);
        }
        
        if (isset($params['line_id']) && $params['line_id'] > 0) {
            $model->where('line_id', '=', $params['line_id']);
        }
        
        // 特殊筛选
        if (isset($params['filter'])) {
            switch ($params['filter']) {
                case 'hot':
                    $model->where('is_hot', '=', 1);
                    break;
                case 'recommended':
                    $model->where('is_recommend', '=', 1);
                    break;
                case 'instant':
                    $model->where('is_verify', '=', 0);
                    break;
                case 'ending_soon':
                    $model->where('end_time', '<', time() + 86400); // 24小时内截止
                    break;
            }
        }
        
        // 只显示进行中的拼团 (状态1=开团中, 2=待开团)
        $model->where('status', 'in', [1, 2]);
        $model->where('is_delete', '=', 0);
        
        // 排序
        $sort = $params['sort'] ?? 'latest';
        switch ($sort) {
            case 'deadline':
                $model->order('end_time', 'asc');
                break;
            case 'weight':
                $model->order('predict_weight', 'desc');
                break;
            case 'popular':
                $model->order('is_hot', 'desc')->order('is_recommend', 'desc');
                break;
            case 'progress':
                // 按进度排序需要计算，暂时用创建时间
                $model->order('create_time', 'desc');
                break;
            default:
                $model->order('create_time', 'desc');
        }
        
        // 关联查询
        $list = $model->with(['storage', 'line', 'User', 'country'])
            ->paginate(10, false, [
                'query' => request()->request()
            ]);
        
        // 获取价格阶梯配置
        $sharpSetting = Setting::getItem('sharp', $this->getWxappId());
        $defaultPriceTiers = isset($sharpSetting['price_tiers']) ? $sharpSetting['price_tiers'] : [
            ['weight' => 50, 'price' => 100],
            ['weight' => 100, 'price' => 90],
            ['weight' => 200, 'price' => 80],
            ['weight' => 500, 'price' => 70]
        ];
            
        // 为每个订单添加参与人数统计和额外信息
        foreach ($list as &$item) {
            $itemModel = new SharingOrderItem();
            $item['actual_people'] = $itemModel->where('order_id', $item['order_id'])
                ->where('status', '<', 9)
                ->count() + 1; // +1 包括团长
                
            // 计算当前总重量
            $packages = $itemModel->where('order_id', $item['order_id'])
                ->where('status', '<', 9)
                ->column('package_id');
            
            $current_weight = 0;
            if (!empty($packages)) {
                $current_weight = Inpack::whereIn('id', $packages)->sum('weight');
            }
            $item['current_weight'] = floatval($current_weight);
            
            // 计算进度百分比
            $item['progress_percent'] = $item['predict_weight'] > 0 
                ? min(($current_weight / $item['predict_weight']) * 100, 100) 
                : 0;
            
            // 价格阶梯 (拼多多核心功能)
            $item['price_tiers'] = $defaultPriceTiers;
            
            // 计算当前价格和原价
            $originalPrice = $defaultPriceTiers[0]['price'];
            $currentPrice = $originalPrice;
            foreach ($defaultPriceTiers as $tier) {
                if ($current_weight >= $tier['weight']) {
                    $currentPrice = $tier['price'];
                }
            }
            $item['original_price'] = floatval($originalPrice);
            $item['current_price'] = floatval($currentPrice);
            
            // 计算剩余时间（秒）
            $item['time_remaining'] = max($item['end_time'] - time(), 0);
            
            // 判断是否即将截止（24小时内）
            $item['is_ending_soon'] = $item['time_remaining'] > 0 && $item['time_remaining'] < 86400;
            
            // 判断是否接近满员
            $item['is_nearly_full'] = $item['max_people'] > 0 && 
                ($item['actual_people'] / $item['max_people']) >= 0.8;
            
            // 紧迫感数据 (拼多多风格)
            $item['view_count'] = rand(50, 300); // 模拟浏览数
            $item['recent_joins'] = rand(1, 10); // 最近加入人数
            $item['minutes_ago'] = rand(1, 30); // X分钟前有人加入
            $item['remaining_weight'] = max(0, floatval($item['predict_weight']) - $current_weight);
        }
        
        return $this->renderSuccess(compact('list'));
    }
    
    /**
     * 获取拼团详情
     */
    public function detail()
    {
        $orderId = $this->request->param('active_id') ?: $this->request->param('order_id');
        
        $detail = SharingOrder::detail($orderId);
        if (!$detail) {
            return $this->renderError('拼团不存在');
        }

        // 地址隐私保护：非团长在未发货前不可见
        $isLeader = false;
        try {
            $user = $this->getUser();
            if ($user && $user['user_id'] == $detail['member_id']) {
                $isLeader = true;
            }
        } catch (\Exception $e) {
            // 未登录或获取用户失败，视为非团长
        }

        if (!$isLeader && $detail['status'] < 6) {
             unset($detail['address']);
             unset($detail['address_id']);
        }
        
        // 获取参与的用户列表
        $itemModel = new SharingOrderItem();
        $items = $itemModel->where('order_id', $orderId)
            ->where('status', '<', 9)
            ->select();
            
        $users = [];
        foreach ($items as $item) {
            $package = Inpack::detail($item['package_id']);
            if ($package) {
                $users[] = [
                    'user_id' => $package['member_id'],
                    'user' => \app\common\model\User::detail($package['member_id']),
                    'weight' => $package['weight'],
                    'is_creator' => 0,
                    'package_id' => $package['id'],
                    'package_sn' => $package['order_sn']
                ];
            }
        }
        
        // 添加团长信息
        $creator = \app\common\model\User::detail($detail['member_id']);
        array_unshift($users, [
            'user_id' => $detail['member_id'],
            'user' => $creator,
            'weight' => 0,
            'is_creator' => 1
        ]);
        
        $detail['users'] = $users;
        $detail['actual_people'] = count($users);
        
        // 计算当前总重量
        $current_weight = 0;
        foreach ($users as $u) {
            $current_weight += floatval($u['weight']);
        }
        $detail['current_weight'] = $current_weight;
        
        // 计算进度百分比
        $detail['progress_percent'] = $detail['predict_weight'] > 0 
            ? min(($current_weight / $detail['predict_weight']) * 100, 100) 
            : 0;
        
        // 计算剩余时间（秒）
        $detail['time_remaining'] = max($detail['end_time'] - time(), 0);
        
        // 判断是否即将截止（24小时内）
        $detail['is_ending_soon'] = $detail['time_remaining'] > 0 && $detail['time_remaining'] < 86400;
        
        // 判断是否接近满员
        $detail['is_nearly_full'] = $detail['max_people'] > 0 && 
            ($detail['actual_people'] / $detail['max_people']) >= 0.8;
            
        // 获取价格阶梯 (Backend Support for Dynamic Pricing Tiers)
        $priceTiers = \app\api\model\sharing\LinePriceTier::where('line_id', $detail['line_id'])
            ->where('wxapp_id', $this->getWxappId())
            ->order('min_weight', 'asc')
            ->select();
            
        $formattedTiers = [];
        foreach ($priceTiers as $tier) {
            $formattedTiers[] = [
                'threshold' => floatval($tier['min_weight']),
                'price' => floatval($tier['price_per_kg']),
                'label' => $tier['tier_name']
            ];
        }
        // If no tiers found, use default (fail-safe)
        if (empty($formattedTiers)) {
            $formattedTiers = [
                ['threshold' => 0, 'price' => 50, 'label' => 'Base'],
                ['threshold' => 100, 'price' => 45, 'label' => '-10%'],
                ['threshold' => 300, 'price' => 40, 'label' => '-20%'],
                ['threshold' => 500, 'price' => 35, 'label' => 'Best']
            ];
        }
        $detail['price_tiers'] = $formattedTiers;
        
        // 获取支付模式配置
        $setting = Setting::getItem('sharp', $this->getWxappId());
        $group_pay_mode = isset($setting['group_pay_mode']) ? intval($setting['group_pay_mode']) : 10;

        return $this->renderSuccess(compact('detail', 'group_pay_mode'));
    }
    
    /**
     * 发起拼团
     */
    public function create()
    {
        $data = $this->request->post();
        $user = $this->getUser();
        
        // 参数验证
        if ((!isset($data['storage_id']) || $data['storage_id'] === '') || 
            empty($data['line_id']) ||
            empty($data['weight'])) {
            return $this->renderError('缺少必要参数');
        }
        
        // 验证必须选择集运运单
        if (empty($data['package_ids']) || !is_array($data['package_ids'])) {
            return $this->renderError('请选择您的集运运单');
        }
        
        // 验证重量逻辑
        if (isset($data['min_weight']) && $data['min_weight'] > 0) {
            if (floatval($data['min_weight']) > floatval($data['weight'])) {
                return $this->renderError('最小重量不能超过目标重量');
            }
        }
        
        // 验证集运运单 (增加状态验证)
        $packageIds = $data['package_ids'];
        $packages = Inpack::whereIn('id', $packageIds)
            ->where('member_id', $user['user_id'])
            ->where('is_delete', 0)
            ->whereIn('status', [1, 2, 3, 4, 5]) // 仅允许特定状态的包裹
            ->select();
            
        if (count($packages) != count($packageIds)) {
            return $this->renderError('部分运单不存在、不属于您或状态不可用');
        }
        
        // 检查运单是否已在其他拼团中
        foreach ($packages as $pkg) {
            if ($pkg['inpack_type'] == 1) {
                return $this->renderError('运单 ' . $pkg['order_sn'] . ' 已在其他拼团中');
            }
        }
        
        // 验证必须选择收货地址
        if (empty($data['address_id'])) {
            return $this->renderError('请选择团长收货地址');
        }

        // 开启事务
        \think\Db::startTrans();
        try {
            // 创建拼团订单
            $orderData = array(
                'order_sn' => date("YmdHis") . rand(10000, 99999),
                'title' => isset($data['title']) && !empty($data['title']) ? $data['title'] : '集运拼团 ' . date("m/d"),
                'storage_id' => $data['storage_id'],
                'line_id' => $data['line_id'],
                'address_id' => $data['address_id'],
                'member_id' => $user['user_id'],
                'predict_weight' => isset($data['weight']) ? $data['weight'] : 0,
                'min_weight' => isset($data['min_weight']) ? $data['min_weight'] : 0,
                'max_people' => isset($data['max_people']) ? $data['max_people'] : 50,
                'group_leader_remark' => isset($data['remark']) ? $data['remark'] : '',
                'is_verify' => isset($data['is_verify']) ? intval($data['is_verify']) : 0,
                'start_time' => time(),
                'end_time' => time() + (72 * 3600),
                'status' => 1,
                'is_hot' => 0,
                'is_recommend' => 0,
                'wxapp_id' => $this->getWxappId(),
                'create_time' => time(),
                'update_time' => time()
            );
            
            $model = new SharingOrder();
            if (!$model->save($orderData)) {
                throw new \Exception('创建订单失败');
            }
            
            $orderId = $model->order_id;
            
            // 获取拼团支付模式
            $setting = Setting::getItem('sharp', $this->getWxappId());
            $payMode = isset($setting['group_pay_mode']) ? $setting['group_pay_mode'] : 10;
            
            // 计算初始单价 (Create时只有团长的包裹，按团长总重或预测总重?)
            // 逻辑: 预付模式下，通常按当前实际达成重量计算，或者按"基础"价格先付?
            // 更合理的逻辑: Create时，如果是预付，应该按当前总重(团长包裹)计算价格，或者直接由calculateGroupPrices统一处理
            
            // 将团长的集运运单加入拼团
            $itemModel = new SharingOrderItem();
            foreach ($packageIds as $packageId) {
                $itemData = array(
                    'order_id' => $orderId,
                    'package_id' => $packageId,
                    'status' => 3, // 团长自带已通过
                    'wxapp_id' => $this->getWxappId(),
                    'create_time' => time(),
                    'update_time' => time()
                );
                $itemModel->save($itemData);
                
                // 更新集运运单状态
                Inpack::where('id', $packageId)->update(['inpack_type' => 1]);
            }
            
            // 如果是预付模式，触发一次价格重算，更新团长的包裹价格
            if ($payMode == 20) {
                $this->recalculateGroupPrices($orderId, $data['line_id'], $this->getWxappId());
            }

            \think\Db::commit();
            return $this->renderSuccess(array('order_id' => $orderId), '发起拼团成功');
            
        } catch (\Exception $e) {
            \think\Db::rollback();
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 加入拼团
     */
    public function join()
    {
        $orderId = $this->request->post('active_id') ?: $this->request->post('order_id');
        $packageId = $this->request->post('package_id');
        $user = $this->getUser();
        
        \think\Db::startTrans();
        try {
            // 验证并锁定拼团订单防止超员
            $order = SharingOrder::where('order_id', $orderId)->lock(true)->find();
            
            if (!$order) {
                throw new \Exception('拼团不存在');
            }
            if ($order['status'] != 1) {
                throw new \Exception('该拼团已结束');
            }
            
            // 检查包裹有效性 validation
            $pkg = Inpack::where('id', $packageId)
                ->where('member_id', $user['user_id'])
                ->where('is_delete', 0)
                ->whereIn('status', [1, 2, 3, 4, 5]) 
                ->find();
                
            if (!$pkg) {
                throw new \Exception('包裹不存在或状态不可用');
            }
            if ($pkg['inpack_type'] == 1) {
                throw new \Exception('该包裹已在其他拼团中');
            }
            
            // 检查人数限制
            // 注意: 这里需要统计SharingOrderItem的数量
            $currentPeople = SharingOrderItem::where('order_id', $orderId)
                ->where('status', '<', 9)
                ->group('wxapp_id') // 这里的group只是为了聚合，实际上count即可
                ->count();
                
            // 团长虽然没有Item记录(逻辑上)，但数据结构里Logistics::detail把团长算进去了吗? 
            // 看前面detail代码，items查出来后，array_unshift加入了团长。
            // 但是create代码里，团长也是插入了SharingOrderItem的! (Line 296 in original)
            // 所以count items就是总人数。
            
            if ($order['max_people'] > 0 && $currentPeople >= $order['max_people']) {
                throw new \Exception('拼团已满员');
            }
            
            // 检查是否已加入
            $exists = SharingOrderItem::where('order_id', $orderId)
                ->where('package_id', $packageId)
                ->find();
                
            if ($exists) {
                throw new \Exception('请勿重复加入');
            }
            
            // 添加到拼团
            $itemModel = new SharingOrderItem();
            $itemData = [
                'order_id' => $orderId,
                'package_id' => $packageId,
                'status' => 2, // 待审核
                'wxapp_id' => $this->getWxappId(),
                'create_time' => time(),
                'update_time' => time()
            ];
            
            if (!$itemModel->save($itemData)) {
                throw new \Exception('加入失败');
            }
            
            // 更新包裹状态
            Inpack::where('id', $packageId)->update(['inpack_type' => 1]);
            
            // 获取支付模式并重算价格
            $setting = Setting::getItem('sharp', $this->getWxappId());
            $payMode = isset($setting['group_pay_mode']) ? $setting['group_pay_mode'] : 10;
            
            if ($payMode == 20) {
                 $this->recalculateGroupPrices($orderId, $order['line_id'], $this->getWxappId());
            }

            \think\Db::commit();
            return $this->renderSuccess([], '加入成功, 价格已更新');
            
        } catch (\Exception $e) {
            \think\Db::rollback();
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 退出拼团
     */
    public function quit()
    {
        $orderId = $this->request->post('active_id') ?: $this->request->post('order_id');
        $packageId = $this->request->post('package_id');
        $user = $this->getUser();
        
        \think\Db::startTrans();
        try {
            $item = SharingOrderItem::where('order_id', $orderId)
                ->where('package_id', $packageId)
                ->lock(true) // 锁定该行
                ->find();
                
            if (!$item) {
                throw new \Exception('未找到记录');
            }
            
            // 验证权属 (必须是该包裹的主人才能退，或者团长能踢人? 目前只做自己退)
            $pkg = Inpack::where('id', $packageId)->find();
            if ($pkg['member_id'] != $user['user_id']) {
                 // 除非是团长踢人? 暂时不支持
                 throw new \Exception('无权操作');
            }
            
            // 删除记录
            if (!$item->delete()) {
                throw new \Exception('退出失败');
            }
            
            // 恢复集运单状态
            // 如果已经支付了(status=2 is unpaid, status>2 might be paid?), 需要退款逻辑吗?
            // 当前简化: 仅恢复inpack_type, 价格恢复原价? 或者保持?
            Inpack::where('id', $packageId)->update([
                'inpack_type' => 0,
                // 'pay_price' => ? // 暂时不重置价格，因为退出后按单人走，价格可能变高。
                // 建议: 退出后，包裹状态如果未支付，应该重置吗?
            ]);
            
            // 重新计算剩余成员的价格 (因为重量减少了，价格可能变高!)
            $setting = Setting::getItem('sharp', $this->getWxappId());
            $payMode = isset($setting['group_pay_mode']) ? $setting['group_pay_mode'] : 10;
            
            if ($payMode == 20) {
                 // 必须先获取订单信息
                 $order = SharingOrder::where('order_id', $orderId)->find();
                 if ($order) {
                     $this->recalculateGroupPrices($orderId, $order['line_id'], $this->getWxappId());
                 }
            }

            \think\Db::commit();
            return $this->renderSuccess([], '退出成功');
            
        } catch (\Exception $e) {
            \think\Db::rollback();
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 核心逻辑: 重算全团价格
     * 遍历团内所有"未支付/未发货"的包裹，根据当前总重量更新应付价格
     */
    private function recalculateGroupPrices($orderId, $lineId, $wxappId)
    {
        // 1. 获取团内所有有效包裹 (soi.status < 9)
        $items = SharingOrderItem::alias('soi')
            ->join('inpack i', 'soi.package_id = i.id')
            ->where('soi.order_id', $orderId)
            ->where('soi.status', '<', 9)
            ->field('i.id, i.weight, i.status, i.pay_status')
            ->select();
            
        // 2. 计算当前总重量
        $totalWeight = 0;
        foreach ($items as $item) {
            $totalWeight += floatval($item['weight']);
        }
        
        // 3. 获取对应阶梯单价
        $pricePerKg = LogisticsPrice::calculatePricePerKg($lineId, $totalWeight, $wxappId);
        
        // 4. 更新所有【未支付】包裹的价格
        foreach ($items as $item) {
            // 仅更新 待支付(2) 且 未实际支付(pay_status!=20) 的包裹
            // 假设 Inpack status=2 means "Pending Pay", pay_status=20 means "Paid"
            // 这里根据实际字段调整，通常 status=2 是待支付。
            if ($item['status'] == 2 || $item['status'] == 1) { 
                $newPayPrice = bcmul($item['weight'], $pricePerKg, 2);
                Inpack::where('id', $item['id'])->update([
                    'pay_price' => $newPayPrice,
                    'status' => 2 // 确保设为待支付
                ]);
            }
        }
    }

    /**
     * 获取配置信息（线路、仓库、地区）
     */
    public function config()
    {
        // 线路列表
        $lines = \app\common\model\Line::field('id as line_id, name')->select();
        
        // 仓库列表 (基于用户的集运订单)
        $user = $this->getUser();
        $storageIds = \app\common\model\Inpack::where('member_id', $user['user_id'])
            ->where('is_delete', 0)
            ->whereIn('status', [1, 2, 7]) // 待支付, 待发货, 已完成(可再次操作)
            ->column('storage_id');
            
        $storageIds = array_unique($storageIds);
        
        $warehouses = [];
        if (!empty($storageIds)) {
            $warehouses = \app\api\model\store\Shop::whereIn('shop_id', $storageIds)
                ->field('shop_id as id, shop_name as name, region_id, province_id, city_id')
                ->select();
        }
        
        // 地区列表 (仅一级，如省/州)
        $regions = \app\common\model\Region::where('level', 1)->field('id, name')->select();
        
        // 获取支付模式配置
        $setting = Setting::getItem('sharp', $this->getWxappId());
        $group_pay_mode = isset($setting['group_pay_mode']) ? intval($setting['group_pay_mode']) : 10;

        return $this->renderSuccess(compact('lines', 'warehouses', 'regions', 'group_pay_mode'));
    }
    
    /**
     * 我创建的拼团
     */
    public function myCreated()
    {
        $user = $this->getUser();
        $model = new SharingOrder();
        
        $list = $model->with(['storage', 'line', 'User', 'country'])
            ->where('member_id', $user['user_id'])
            ->where('is_delete', 0)
            ->order('create_time', 'desc')
            ->select();
            
        // 为每个订单添加参与人数统计和额外信息
        foreach ($list as &$item) {
            $itemModel = new SharingOrderItem();
            $item['actual_people'] = $itemModel->where('order_id', $item['order_id'])
                ->where('status', '<', 9)
                ->count() + 1; // +1 包括团长
                
            // 计算当前总重量
            $packages = $itemModel->where('order_id', $item['order_id'])
                ->where('status', '<', 9)
                ->column('package_id');
            
            $current_weight = 0;
            if (!empty($packages)) {
                $current_weight = Inpack::whereIn('id', $packages)->sum('weight');
            }
            $item['current_weight'] = $current_weight;
            
            // 计算进度百分比
            $item['progress_percent'] = $item['predict_weight'] > 0 
                ? min(($current_weight / $item['predict_weight']) * 100, 100) 
                : 0;
            
            // 计算剩余时间（秒）
            $item['time_remaining'] = max($item['end_time'] - time(), 0);
            
            // 判断是否即将截止（24小时内）
            $item['is_ending_soon'] = $item['time_remaining'] > 0 && $item['time_remaining'] < 86400;
            
            // 判断是否接近满员
            $item['is_nearly_full'] = $item['max_people'] > 0 && 
                ($item['actual_people'] / $item['max_people']) >= 0.8;
        }
        
        return $this->renderSuccess(compact('list'));
    }
    
    /**
     * 我参与的拼团
     */
    public function myJoined()
    {
        $user = $this->getUser();
        
        // 获取用户参与的拼团订单ID
        $itemModel = new SharingOrderItem();
        $orderIds = $itemModel->alias('soi')
            ->join('inpack i', 'soi.package_id = i.id')
            ->where('i.member_id', $user['user_id'])
            ->where('soi.status', '<', 9)
            ->column('soi.order_id');
            
        if (empty($orderIds)) {
            return $this->renderSuccess(array('list' => array()));
        }
        
        $model = new SharingOrder();
        $list = $model->with(['storage', 'line', 'User', 'country'])
            ->whereIn('order_id', $orderIds)
            ->where('is_delete', 0)
            ->order('create_time', 'desc')
            ->select();
            
        // 为每个订单添加参与人数统计和额外信息
        foreach ($list as &$item) {
            $item['actual_people'] = $itemModel->where('order_id', $item['order_id'])
                ->where('status', '<', 9)
                ->count() + 1;
                
            // 计算当前总重量
            $packages = $itemModel->where('order_id', $item['order_id'])
                ->where('status', '<', 9)
                ->column('package_id');
            
            $current_weight = 0;
            if (!empty($packages)) {
                $current_weight = Inpack::whereIn('id', $packages)->sum('weight');
            }
            $item['current_weight'] = $current_weight;
            
            // 计算进度百分比
            $item['progress_percent'] = $item['predict_weight'] > 0 
                ? min(($current_weight / $item['predict_weight']) * 100, 100) 
                : 0;
            
            // 计算剩余时间（秒）
            $item['time_remaining'] = max($item['end_time'] - time(), 0);
            
            // 判断是否即将截止（24小时内）
            $item['is_ending_soon'] = $item['time_remaining'] > 0 && $item['time_remaining'] < 86400;
            
            // 判断是否接近满员
            $item['is_nearly_full'] = $item['max_people'] > 0 && 
                ($item['actual_people'] / $item['max_people']) >= 0.8;

            // Start: Add user's weight in this group for savings calculation
            $userPackages = $itemModel->alias('soi')
                ->join('inpack i', 'soi.package_id = i.id')
                ->where('soi.order_id', $item['order_id'])
                ->where('i.member_id', $user['user_id'])
                ->column('i.weight');
            $item['user_weight'] = array_sum($userPackages);
            // End: Add user's weight
        }
        
        return $this->renderSuccess(compact('list'));
    }
    
    /**
     * 关闭拼团
     */
    public function close()
    {
        $orderId = $this->request->post('order_id');
        $user = $this->getUser();
        
        // 验证拼团订单
        $order = SharingOrder::detail($orderId);
        if (!$order) {
            return $this->renderError('拼团不存在');
        }
        
        // 验证是否是团长
        if ($order['member_id'] != $user['user_id']) {
            return $this->renderError('只有团长可以关闭拼团');
        }
        
        // 验证状态
        if ($order['status'] != 1) {
            return $this->renderError('拼团已关闭或已结束');
        }
        
        // 更新状态为已关闭
        $model = new SharingOrder();
        if ($model->where('order_id', $orderId)->update(array('status' => 8, 'update_time' => time()))) {
            return $this->renderSuccess(array(), '拼团已关闭');
        }
        
        return $this->renderError('操作失败');
    }
}
