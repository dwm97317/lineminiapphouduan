<?php

namespace app\store\model\store;

use app\common\model\store\Shop as ShopModel;
use Lvht\GeoHash;

/**
 * 商家门店模型
 * Class Shop
 * @package app\store\model\store
 */
class Shop extends ShopModel
{
    /**
     * 获取列表数据
     * @param array $param
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($param = [])
    {
        // 查询列表数据
        
        return $this->setListQueryWhere($param)
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }
    
        /**
     * 提现打款成功：累积提现佣金
     * @param $user_id
     * @param $money
     * @return false|int
     * @throws \think\exception\DbException
     */
    public static function totalMoney($shop_id, $money)
    {
        $model = self::detail($shop_id);
        return $model->save([
            'freeze_income' => $model['freeze_income'] - $money,
            'total_money' => $model['total_money'] + $money,
        ]);
    }

    /**
     * 提现驳回：解冻分销商资金
     * @param $user_id
     * @param $money
     * @return false|int
     * @throws \think\exception\DbException
     */
    public static function backFreezeMoney($shop_id, $money)
    {
        $model = self::detail($shop_id);
        return $model->save([
            'income' => $model['income'] + $money,
            'freeze_income' => $model['freeze_income'] - $money,
        ]);
    }
    
    


    /**
     * 获取所有门店列表
     * @param array $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getAllList($param = [])
    {   
        // dump($param);die;
        $list = (new static)
        ->setListQueryWhere($param)
        ->with('shelf.shelfunit')
        ->where('status',1)
        ->select();
        // dump((new static)->getLastsql());die;
        return $list;
    }

    //获取门店名和id
    public static function getListName($param = [])
    {
        return (new static)->setListQueryWhere($param)->field('shop_id,shop_name')->select();
    }
    /**
     * 设置列表查询条件
     * @param array $param
     * @return $this
     */
    private function setListQueryWhere($param = [])
    {
        // 查询参数
        $param = array_merge(['is_check' => '', 'search' => '', 'status' => null,], $param);
        is_numeric($param['is_check']) && $param['is_check'] > -1 && $this->where('is_check', '=', (int)$param['is_check']);
        !empty($param['search']) && $this->where('shop_name|linkman|phone', 'like', "%{$param['search']}%");
        !empty($param['shop_id']) && $this->where('shop_id', '=', $param['shop_id']);
        !empty($param['wxapp_id']) && $this->where('wxapp_id', '=', $param['wxapp_id']);
        !empty($param['storage_id']) && $this->where('shop_id', '=', $param['storage_id']);
        is_numeric($param['status']) && $this->where('status', '=', (int)$param['status']);
        return $this->where('is_delete', '=', '0')->order(['sort' => 'asc', 'create_time' => 'desc']);
    }
    

    

    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data)
    {
        if (!$this->validateForm($data)) {
            $data['logo_image_id'] = 1;

            return $this->allowField(true)->save($this->createData($data));
        }
        return $this->allowField(true)->save($this->createData($data));
    }

    /**
     * 编辑记录
     * @param $data
     * @return false|int
     */
    public function edit($data)
    {
        if (!$this->validateForm($data)) {
            return false;
        }
        return $this->allowField(true)->save($this->createData($data)) !== false;
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }

    /**
     * 创建数据
     * @param array $data
     * @return array
     */
    private function createData($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        // 格式化坐标信息
        $coordinate = explode(',', $data['coordinate']);
        $data['latitude'] = $coordinate[0];
        $data['longitude'] = $coordinate[1];
        // 生成geohash
        $Geohash = new Geohash;
        $data['geohash'] = $Geohash->encode($data['longitude'], $data['latitude']);
        return $data;
    }

    /**
     * 表单验证
     * @param $data
     * @return bool
     */
    private function validateForm($data)
    {
        if (!isset($data['logo_image_id']) || empty($data['logo_image_id'])) {

            return false;
        }
        return true;
    }

}