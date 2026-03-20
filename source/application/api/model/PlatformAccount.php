<?php

namespace app\api\model;

use app\common\model\BaseModel;

/**
 * 平台账户绑定模型 (FB/IG Bot Customer ID)
 * 支持多账户绑定：一个 Customer ID 可以绑定多个用户账户
 * Class PlatformAccount
 * @package app\api\model
 */
class PlatformAccount extends BaseModel
{
    protected $name = 'platform_account';

    /**
     * 最大绑定数量限制
     */
    const MAX_BINDINGS_PER_CUSTOMER = 10;

    /**
     * 通过 Customer ID 获取所有绑定记录
     * @param string $customerId
     * @param int $wxappId
     * @return array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getByCustomerId($customerId, $wxappId = null)
    {
        $query = new static;
        if ($wxappId) {
            $query = $query->where('wxapp_id', $wxappId);
        }
        return $query->where('customer_id', $customerId)
                     ->where('status', 1)
                     ->order('binding_time', 'DESC')
                     ->select();
    }

    /**
     * 通过 User ID 获取绑定记录
     * @param int $userId
     * @param int $wxappId
     * @return array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getByUserId($userId, $wxappId = null)
    {
        $query = new static;
        if ($wxappId) {
            $query = $query->where('wxapp_id', $wxappId);
        }
        return $query->where('user_id', $userId)
                     ->where('status', 1)
                     ->order('binding_time', 'DESC')
                     ->select();
    }

    /**
     * 检查 Customer ID 是否已被特定用户绑定
     * @param string $customerId
     * @param int $userId
     * @param int $wxappId
     * @return bool
     */
    public static function isCustomerBoundByUser($customerId, $userId, $wxappId = null)
    {
        $query = new static;
        if ($wxappId) {
            $query = $query->where('wxapp_id', $wxappId);
        }
        return $query->where([
            'customer_id' => $customerId,
            'user_id' => $userId,
            'status' => 1
        ])->count() > 0;
    }

    /**
     * 检查用户是否已绑定某个平台的账户
     * @param int $userId
     * @param string $platformType
     * @param int $wxappId
     * @return bool
     */
    public static function isUserBound($userId, $platformType = 'FACEBOOK', $wxappId = null)
    {
        $query = new static;
        if ($wxappId) {
            $query = $query->where('wxapp_id', $wxappId);
        }
        return $query->where([
            'user_id' => $userId, 
            'platform_type' => $platformType,
            'status' => 1
        ])->count() > 0;
    }

    /**
     * 检查 Customer ID 的绑定数量是否已达上限
     * @param string $customerId
     * @param int $wxappId
     * @return bool
     */
    public static function isBindingLimitReached($customerId, $wxappId = null)
    {
        $query = new static;
        if ($wxappId) {
            $query = $query->where('wxapp_id', $wxappId);
        }
        $count = $query->where([
            'customer_id' => $customerId,
            'status' => 1
        ])->count();
        
        return $count >= self::MAX_BINDINGS_PER_CUSTOMER;
    }

    /**
     * 获取 Customer ID 的绑定数量
     * @param string $customerId
     * @param int $wxappId
     * @return int
     */
    public static function getBindingCount($customerId, $wxappId = null)
    {
        $query = new static;
        if ($wxappId) {
            $query = $query->where('wxapp_id', $wxappId);
        }
        return $query->where([
            'customer_id' => $customerId,
            'status' => 1
        ])->count();
    }

    /**
     * 创建新的绑定关系
     * @param array $data
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function createBinding($data)
    {
        $model = new static;
        
        // 设置默认值
        $bindingData = [
            'user_id' => $data['user_id'],
            'platform_type' => $data['platform_type'] ?? 'FACEBOOK',
            'customer_id' => $data['customer_id'],
            'customer_name' => $data['customer_name'] ?? null,
            'is_anonymized' => $data['is_anonymized'] ?? 1,
            'status' => 1,
            'wxapp_id' => $data['wxapp_id'] ?? self::$wxapp_id,
            'binding_time' => date('Y-m-d H:i:s'),
        ];

        return $model->save($bindingData);
    }

    /**
     * 更新验证时间
     * @param int $id
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function updateVerifyTime($id)
    {
        $model = new static;
        return $model->where('id', $id)->update([
            'last_verify_time' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 匿名化显示客户名称
     * @param string $name
     * @return string
     */
    public static function anonymizeName($name)
    {
        if (empty($name)) {
            return '***';
        }
        
        $length = mb_strlen($name, 'UTF-8');
        if ($length <= 2) {
            return mb_substr($name, 0, 1, 'UTF-8') . '***';
        }
        
        // 显示第一个字符，后面用 *** 代替
        return mb_substr($name, 0, 1, 'UTF-8') . '***';
    }

    /**
     * 格式化绑定信息（用于 API 返回）
     * @param array $binding
     * @return array
     */
    public static function formatBindingInfo($binding)
    {
        return [
            'id' => $binding['id'],
            'platform_type' => $binding['platform_type'],
            'platform_name' => ucfirst(strtolower($binding['platform_type'])),
            'customer_id' => $binding['customer_id'],
            'customer_name_anonymized' => $binding['is_anonymized'] 
                ? self::anonymizeName($binding['customer_name'])
                : $binding['customer_name'],
            'binding_time' => $binding['binding_time'],
            'last_verify_time' => $binding['last_verify_time'],
            'status' => $binding['status'],
        ];
    }
}
