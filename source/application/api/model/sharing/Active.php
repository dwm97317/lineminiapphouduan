<?php
namespace app\api\model\sharing;

use app\common\model\sharing\Active as ActiveModel;
use app\api\service\sharing\LogisticsPrice;
use app\common\model\sharing\ActiveUsers;
use app\api\model\sharing\DepositLog;

/**
 * 拼团活动模型
 */
class Active extends ActiveModel
{
    /**
     * 发起集运拼团
     */
    public function createLogistics($userId, $data)
    {
        $this->startTrans();
        try {
            // 参数验证
            if ((!isset($data['storage_id']) || $data['storage_id'] === '') || empty($data['target_region_id']) || empty($data['line_id'])) {
                $this->error = '缺少必要参数';
                return false;
            }
            
            // 计算单价
            $pricePerKg = LogisticsPrice::calculatePricePerKg($data['line_id'], $data['weight'], $data['wxapp_id']);
            
            // 截止时间(默认72小时)
            $endTime = time() + (72 * 3600);
            
            // 计算预付运费 (保证金)
            $freightAmount = $data['weight'] * $pricePerKg;
            
            // 扣除余额 (模拟支付)
            $user = \app\common\model\User::detail($userId);
            if ($user['balance'] < $freightAmount) {
                throw new \Exception('余额不足，需预付运费: ' . $freightAmount);
            }
            $user->setDec('balance', $freightAmount);
            
            // 记录保证金日志
            DepositLog::create([
                'user_id' => $userId,
                'amount' => $freightAmount,
                'type' => 'freeze',
                'status' => 'frozen',
                'wxapp_id' => $data['wxapp_id'],
                'create_time' => time() // Model automatic?
            ]);

            // 保存活动
            $this->save([
                'active_type' => 20,
                'creator_id' => $userId,
                'storage_id' => $data['storage_id'],
                'target_region_id' => $data['target_region_id'],
                'line_id' => $data['line_id'],
                'people' => 100, // 假设上限100人
                'actual_people' => 1,
                'status' => 10,
                'current_weight' => $data['weight'],
                'current_volume' => $data['volume'] ?? 0,
                'price_per_kg' => $pricePerKg,
                'end_time' => $endTime,
                'wxapp_id' => $data['wxapp_id']
            ]);
            
            // 保存成员
            ActiveUsers::add([
                'active_id' => $this['active_id'],
                'user_id' => $userId,
                'is_creator' => 1,
                'package_weight' => $data['weight'],
                'package_volume' => $data['volume'] ?? 0,
                'target_address_id' => $data['address_id'] ?? 0,
                'freight_amount' => $freightAmount,
                'wxapp_id' => $data['wxapp_id']
            ]);
            
            // 更新保证金日志的active_id
            DepositLog::where('user_id', $userId)->where('active_id', 0)->update(['active_id' => $this['active_id']]);

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 获取集运拼团列表
     */
    public function getLogisticsList($params)
    {
        // 筛选条件
        $this->where('active_type', '=', 20); // 集运拼团
        
        if (isset($params['storage_id']) && $params['storage_id'] > 0) {
            $this->where('storage_id', '=', $params['storage_id']);
        }
        
        if (isset($params['target_region_id']) && $params['target_region_id'] > 0) {
            $this->where('target_region_id', '=', $params['target_region_id']);
        }
        
        // 只显示拼团中的
        $this->where('status', '=', 10);
        
        // 排序
        $sort = $params['sort'] ?? 'latest';
        switch ($sort) {
            case 'deadline':
                $this->order(['end_time' => 'asc']);
                break;
            case 'price':
                $this->order(['price_per_kg' => 'asc']);
                break;
            case 'weight':
                $this->order(['current_weight' => 'desc']);
                break;
            default:
                $this->order(['create_time' => 'desc']);
        }
        
        // 关联查询
        return $this->with(['creator', 'storage', 'targetRegion', 'line'])
            ->paginate(10, false, [
                'query' => \request()->request()
            ]);
    }

    public function creator() {
        return $this->belongsTo('app\common\model\User', 'creator_id');
    }

    public function storage()
    {
        return $this->belongsTo('app\api\model\store\Shop', 'storage_id');
    }

    public function targetRegion() {
        return $this->belongsTo('app\common\model\Region', 'target_region_id');
    }

    public function line() {
        return $this->belongsTo('app\common\model\Line', 'line_id');
    }
    
    /**
     * 加入集运拼团
     */
    public function joinLogistics($userId, $data)
    {
        // 验证当前拼单是否允许加入
        if (!$this->checkAllowJoin()) {
            return false;
        }
        
        // 检查是否已加入
        if (ActiveUsers::where('active_id', $this['active_id'])->where('user_id', $userId)->count()) {
            $this->error = '您已参与该拼团';
            return false;
        }

        $this->startTrans();
        try {
            // 计算新总重量
            $newWeight = $this['current_weight'] + $data['weight'];
            
            // 重新计算单价
             $pricePerKg = LogisticsPrice::calculatePricePerKg($this['line_id'], $newWeight, $this['wxapp_id']);

            // 计算预付运费
            $freightAmount = $data['weight'] * $pricePerKg;

            // 扣除余额
            $user = \app\common\model\User::detail($userId);
            if ($user['balance'] < $freightAmount) {
                throw new \Exception('余额不足，需预付运费: ' . $freightAmount);
            }
            $user->setDec('balance', $freightAmount);

            // 记录保证金
            DepositLog::create([
                'user_id' => $userId,
                'active_id' => $this['active_id'],
                'amount' => $freightAmount,
                'type' => 'freeze',
                'status' => 'frozen',
                'wxapp_id' => $this['wxapp_id']
            ]);

            // 保存成员
            ActiveUsers::add([
                'active_id' => $this['active_id'],
                'user_id' => $userId,
                'is_creator' => 0,
                'package_weight' => $data['weight'],
                'package_volume' => $data['volume'] ?? 0,
                'target_address_id' => $data['address_id'] ?? 0,
                'freight_amount' => $freightAmount, // 实际扣除金额
                'wxapp_id' => $this['wxapp_id']
            ]);

            // 更新拼团信息
            $this->save([
                'actual_people' => $this['actual_people'] + 1,
                'current_weight' => $newWeight,
                'current_volume' => $this['current_volume'] + ($data['volume'] ?? 0),
                'price_per_kg' => $pricePerKg
            ]);

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 退出拼团
     */
    public function quitLogistics($userId)
    {
        // 验证状态
        if (!in_array($this['status']['value'], [0, 10])) {
            $this->error = '当前状态禁止退出';
            return false;
        }

        // 查找成员记录
        $member = ActiveUsers::where('active_id', $this['active_id'])
            ->where('user_id', $userId)
            ->find();
            
        if (!$member) {
            $this->error = '您未参与该拼团';
            return false;
        }
        
        if ($member['is_creator']) {
            $this->error = '团长不能退出，请选择关闭拼团';
            return false;
        }
        
        $this->startTrans();
        try {
            // 删除成员
            $member->delete();
            
            // 重新计算
            $newWeight = max(0, $this['current_weight'] - $member['package_weight']);
            $newVolume = max(0, $this['current_volume'] - $member['package_volume']);
            $pricePerKg = LogisticsPrice::calculatePricePerKg($this['line_id'], $newWeight, $this['wxapp_id']);
            
            // 更新拼团
            $this->save([
                'actual_people' => max(1, $this['actual_people'] - 1),
                'current_weight' => $newWeight,
                'current_volume' => $newVolume,
                'price_per_kg' => $pricePerKg
            ]);
            
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 集运拼团详情扩展
     */
    public static function detailWithLogistics($active_id)
    {
        return self::detail($active_id, ['storage', 'targetRegion', 'line']);
    }
}
