<?php
namespace app\common\model\sharing;
use app\common\model\BaseModel;

/**
 * 拼团订单项模型
 */
class SharingOrderItem extends BaseModel
{
    protected $name = 'sharing_tr_order_item';
    
    /**
     * 关联集运单
     */
    public function package()
    {
        return $this->belongsTo('app\common\model\Inpack', 'package_id');
    }
    
    /**
     * 关联拼团订单
     */
    public function order()
    {
        return $this->belongsTo('app\common\model\sharing\SharingOrder', 'order_id');
    }
}