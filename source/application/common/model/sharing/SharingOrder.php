<?php
namespace app\common\model\sharing;
use app\common\model\BaseModel;
/**
 * 拼团订单服务
 * */
class SharingOrder extends BaseModel{
    protected $name = 'sharing_tr_order';
    
    /**
     * 有效期-开始时间
     * @param $value
     * @return mixed
     */
    public function getCreateTimeAttr($value)
    {
        return ['text' => date('Y-m-d H:i:s', $value), 'value' => $value];
    }
    
    public function getStatusAttr($value){
        $map = [
           1 => '开团中',
           2 => '待开团',
           3 => '待打包',
           4 => '待付款',
           5 => '待发货',
           6 => '已结束',
           8 => '已取消'
        ];
        return ['text' => $map[$value] , 'value'=>$value];
    }

    public static function detail($id)
    {
        return self::get($id,['country','storage','address','line']);
    }
    
    public function country(){
        return $this->belongsTo('app\common\model\Country','country_id');
    }
    
    public function storage(){
        return $this->belongsTo('app\common\model\store\Shop','storage_id');
    }
        public function User(){
        return $this->belongsTo('app\common\model\User','member_id');
    }
    public function line(){
        return $this->belongsTo('app\common\model\Line','line_id');
    }
    public function address(){
        return $this->belongsTo('app\common\model\UserAddress','address_id');
    }
} 