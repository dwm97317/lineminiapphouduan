<?php

namespace app\api\model;

use app\common\model\BaseModel;

/**
 * 平台账户绑定模型 (FB/IG Bot Customer ID)
 * Class PlatformAccount
 * @package app\api\model
 */
class PlatformAccount extends BaseModel
{
    protected $name = 'platform_account';

    /**
     * 通过 Customer ID 获取绑定记录
     * @param string $customerId
     * @param int $wxappId
     * @return array|null
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
        return $query->where('customer_id', $customerId)->find();
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
        return $query->where('user_id', $userId)->select();
    }

    /**
     * 检查 Customer ID 是否已被绑定
     * @param string $customerId
     * @param int $wxappId
     * @return bool
     */
    public static function isCustomerBound($customerId, $wxappId = null)
    {
        $query = new static;
        if ($wxappId) {
            $query = $query->where('wxapp_id', $wxappId);
        }
        return $query->where('customer_id', $customerId)->count() > 0;
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
        return $query->where(['user_id' => $userId, 'platform_type' => $platformType])->count() > 0;
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
}
