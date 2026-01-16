<?php

namespace app\store\model\store\shop;

use app\common\model\store\shop\Capital as CapitalModel;
use app\common\service\Message as MessageService;
use app\store\model\store\Shop as ShopModel;
use app\store\model\store\shop\Clerk;
use app\store\model\User;
/**
 * 仓库申请加盟模型
 * Class Capital
 * @package app\store\model\store\shop
 */
class Capital extends CapitalModel
{
    /**
     * 获取列表数据
     * @param string $search 仓库名称/手机号
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($data = '')
    {
    
        // 查询列表数据
        $this->alias('cap')
            ->with('shop.logo')
            ->field(['cap.*'])
            ->join('store_shop shop', 'shop.shop_id = cap.shop_id')
            ->order('cap.create_time','DESC');
            // 检索查询条件
        // dump($data);die;
        !empty($data) && !empty($data['search']) && $this->where('shop.shop_name|shop.linkman|shop.phone', 'like', "%{$data['search']}%");
        !empty($data) && !empty($data['shop_id']) && $data['shop_id'] !=-1 && $this->where('cap.shop_id','=',$data['shop_id']);
        return $this->paginate(15, false, [
            'query' => \request()->request()
        ]);
    }
    
    
    /**
     * 关联仓库
     * @return \think\model\relation\HasOne
     */
    public function shop()
    {
        return $this->belongsTo('app\store\model\store\Shop', 'shop_id');
    }
    
    
}