<?php
namespace app\common\model;

/**
 * 用户收货地址模型
 * Class UserAddress
 * @package app\common\model
 */
class UserAddress extends BaseModel
{
    protected $name = 'user_address';

    /**
     * 追加字段
     * @var array
     */
    protected $append = ['region', 'full_address'];

    /**
     * 地区名称
     * @param $value
     * @param $data
     * @return array
     */
    public function getRegionAttr($value, $data)
    {
        return [
            'country' => $data['country'] ?? '',
            'province' => $data['province'] ?? '',
            'city' => $data['city'] ?? '',
            'region' => $data['region'] ?? '',
            'sub_district' => $data['sub_district'] ?? '',
            'postal_code' => $data['postal_code'] ?? '',
        ];
    }

    /**
     * 获取完整地址
     * @return string
     */
    public function getFullAddressAttr($value, $data)
    {
        // 泰国地址格式: 详细地址, 区/街道, 县, 省, 邮编
        if (isset($data['country']) && (stripos($data['country'], 'Thailand') !== false || stripos($data['country'], '泰国') !== false)) {
            $address = $data['detail'];
            if (!empty($data['sub_district'])) $address .= ', ' . $data['sub_district'];
            if (!empty($data['region'])) $address .= ', ' . $data['region'];
            if (!empty($data['city'])) $address .= ', ' . $data['city'];
            if (!empty($data['province'])) $address .= ', ' . $data['province'];
            if (!empty($data['postal_code'])) $address .= ' ' . $data['postal_code'];
            return $address;
        }
        // 默认中国格式: 省市区详细地址
        return $data['province'] . $data['city'] . $data['region'] . $data['detail'];
    }


}
