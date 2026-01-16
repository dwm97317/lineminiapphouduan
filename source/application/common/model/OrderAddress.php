<?php

namespace app\common\model;

/**
 * 订单收货地址模型
 * Class OrderAddress
 * @package app\common\model
 */
class OrderAddress extends BaseModel
{
    protected $name = 'order_address';
    protected $updateTime = false;

    /**
     * 追加字段
     * @var array
     */
    protected $append = ['region'];

    /**
     * 地区名称
     * @param $value
     * @param $data
     * @return array
     */
    public function getRegionAttr($value, $data)
    {
        return [
            'province' => Region::getNameById($data['province_id']),
            'city' => Region::getNameById($data['city_id']),
            'region' => $data['region_id'] == 0 ? '' : Region::getNameById($data['region_id']),
            'sub_district' => $data['sub_district'] ?? '',
            'postal_code' => $data['postal_code'] ?? '',
        ];
    }

    /**
     * 获取完整地址
     * @return string
     */
    public function getFullAddress()
    {
        // 泰国地址格式: 详细地址, 区/街道, 县, 省, 邮编
        if (isset($this['country']) && (stripos($this['country'], 'Thailand') !== false || stripos($this['country'], '泰国') !== false)) {
            $address = $this['detail'];
            if (!empty($this['region']['sub_district'])) $address .= ', ' . $this['region']['sub_district'];
            if (!empty($this['region']['region'])) $address .= ', ' . $this['region']['region'];
            if (!empty($this['region']['city'])) $address .= ', ' . $this['region']['city'];
            if (!empty($this['region']['province'])) $address .= ', ' . $this['region']['province'];
            if (!empty($this['region']['postal_code'])) $address .= ' ' . $this['region']['postal_code'];
            return $address;
        }
        // 默认中国格式: 省市区详细地址
        return $this['region']['province'] . $this['region']['city'] . $this['region']['region'] . $this['detail'];
    }


}
