<?php

namespace app\api\model\store;

use app\common\model\store\Shop as ShopModel;

/**
 * 商家门店模型
 * Class Shop
 * @package app\store\model\store
 */
class Shop extends ShopModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'is_delete',
        'wxapp_id',
        'create_time',
        'update_time'
    ];
    
    
    /**
     * 资金冻结
     * @param $money
     * @return false|int
     */
    public function freezeMoney($money)
    {
        return $this->save([
            'income' => $this['income'] - $money,
            'freeze_income' => $this['freeze_income'] + $money,
        ]);
    }

    /**
     * 获取门店列表
     * @param null $is_check
     * @param string $longitude
     * @param string $latitude
     * @param bool $limit
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($is_check = null, $longitude = '', $latitude = '', $limit = false)
    {
        // 是否支持自提核销
        $is_check && $this->where('is_check', '=', $is_check);
        // 获取数量
        $limit != false && $this->limit($limit);
        // 获取门店列表数据
        $data = $this->where('is_delete', '=', '0')
            ->where('status', '=', '1')
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->select();
        // 根据距离排序
        if (!empty($longitude) && !empty($latitude)) {
            return $this->sortByDistance($data, $longitude, $latitude);
        }
        return $data;
    }

    /**
     * 根据距离排序
     * @param string $longitude
     * @param string $latitude
     * @param \think\Collection|false|\PDOStatement|string $data
     * @return array
     * @throws
     */
    private function sortByDistance(&$data, $longitude, $latitude)
    {
        // 根据距离排序
        $list = $data->isEmpty() ? [] : $data->toArray();
        $sortArr = [];
        foreach ($list as &$shop) {
            // 计算距离
            $distance = self::getDistance($longitude, $latitude, $shop['longitude'], $shop['latitude']);
            // 排序列
            $sortArr[] = $distance;
            $shop['distance'] = $distance;
            if ($distance >= 1000) {
                $distance = bcdiv($distance, 1000, 2);
                $shop['distance_unit'] = $distance . 'km';
            } else
                $shop['distance_unit'] = $distance . 'm';
        }
        // 根据距离排序
        array_multisort($sortArr, SORT_ASC, $list);
        return $list;
    }

    /**
     * 获取两个坐标点的距离
     * @param $ulon
     * @param $ulat
     * @param $slon
     * @param $slat
     * @return float
     */
    private static function getDistance($ulon, $ulat, $slon, $slat)
    {
        // 地球半径
        $R = 6378137;
        // 将角度转为狐度
        $radLat1 = deg2rad($ulat);
        $radLat2 = deg2rad($slat);
        $radLng1 = deg2rad($ulon);
        $radLng2 = deg2rad($slon);
        // 结果
        $s = acos(cos($radLat1) * cos($radLat2) * cos($radLng1 - $radLng2) + sin($radLat1) * sin($radLat2)) * $R;
        // 精度
        $s = round($s * 10000) / 10000;
        return round($s);
    }

    /**
     * 根据门店id集获取门店列表
     * @param $shopIds
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getListByIds($shopIds)
    {
        // 筛选条件
        $filter = ['shop_id' => ['in', $shopIds]];
        if (!empty($shopIds)) {
            $this->orderRaw('field(shop_id, ' . implode(',', $shopIds) . ')');
        }
        // 获取商品列表数据
        return $this->with(['logo'])
            ->where('is_delete', '=', '0')
            ->where('status', '=', '1')
            ->where($filter)
            ->select();
    }

    public function getDetails($id){
       return $this->field('shop_id,shop_name,province_id,city_id,region_id,address,phone,post,linkman,type,send_bonus,pick_bonus,summary,shop_hours,logo_image_id,shop_alias_name')->find($id);
    }
    
    /**
     * 编辑记录
     * @param $data
     * @return bool|false|int
     * @throws \think\exception\DbException
     */
    public function edit($data)
    {
        return $this->allowField(true)->save($data) !== false;
    }
     
    /**
     * 根据id 获取字段
     */
    public function getValueById($id,$field){
      return $this->where(['shop_id'=>$id])->value($field);
    }
}