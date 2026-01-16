<?php
namespace app\common\model;
use app\common\service\Message as MessageService;
use app\common\enum\OrderType as OrderTypeEnum;
use app\common\library\helper;
use app\common\model\store\shop\Capital;
use app\common\model\store\shop\Setting;
use app\common\model\Setting as SettingModel;
use app\common\model\store\Shop;
use app\common\service\Message;
use app\common\model\InpackService as InpackServiceModel;
use think\Db;
use think\Hook;
/**
 * 打包模型
 * Class OrderAddress
 * @package app\common\model
 */
class Inpack extends BaseModel
{
    protected $name = 'inpack';
    protected $createTime = null;
    protected $updateTime = null;
    /**
     * 订单模型初始化
     */
    public static function init()
    {
        parent::init();
        // 监听加盟商订单行为管理
        $static = new static;
        Hook::listen('Inpack', $static);
        Hook::listen('Detained', $static);
        
    }
    
     /**
     * 滞留件并发送消息
     * @param array|\think\Model $order 订单详情
     * @param int $orderType 订单类型
     * @return bool|false|int
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public static function detained($order,$orderType =0){
        // 订单是否已完成
        if ($order['status'] != 7) {
            return false;
        }
        // 获取滞留件设置
        $settleDays = SettingModel::getItem('store')['retention_day'];
        // 判断该订单是否满足结算时间 (订单完成时间 + 佣金结算时间) ≤ 当前时间
        $deadlineTime = strtotime($order['shoprk_time']) + ((int)$settleDays * 86400);
 
        if ($settleDays > 0 && $deadlineTime > time()) {
            return false;
        }
        //加盟订单详情
        $model = self::getDetailByOrderId($order['id'], $orderType);
        if (!$model) {
            return false;
        }
        //发送滞留件消息通知给用户
        $data['order_sn'] = $model['order_sn'];
        $data['order'] = $model;
        $data['order']['total_free'] = $model['free'];
        $data['order']['userName'] = "用户";
        $data['order_type'] = 10;
        $data['order']['remark'] ="您的订单已滞留多日，请尽快前来取件" ;
        Message::send('order.payment',$data);
        return true;
    }
    
       /**
     * 发放加盟订单佣金
     * @param array|\think\Model $order 订单详情
     * @param int $orderType 订单类型
     * @return bool|false|int
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public static function grantMoney($order, $orderType =0)
    {
        // 订单是否已完成
        if ($order['status'] != 8) {
            return false;
        }
           
        // 加盟结算天数
        $settleDays = Setting::getItem('settlement', $order['wxapp_id'])['settle_days'];
   
        // 判断该订单是否满足结算时间 (订单完成时间 + 佣金结算时间) ≤ 当前时间
        $deadlineTime = strtotime($order['receipt_time']) + ((int)$settleDays * 86400);
 
        if ($settleDays > 0 && $deadlineTime > time()) {
            return false;
        }
         
        //加盟订单详情
        $model = self::getDetailByOrderId($order['id'], $orderType);
        if (!$model || $model['is_settled'] == 1) {
            return false;
        }
        // 重新计算加盟佣金
        $capital = Capital::countMoney($model);
        //计算服务项目的佣金
        $service = InpackServiceModel::countMoney($model);
        // dump($service);die; 
        //todo
        // 发放寄件的
        $model['storage_id'] > 0 && Shop::grantMoney($model['storage_id'],$capital['send'] + $service,10,$model['order_sn']);
        $model['shop_id'] > 0 && Shop::grantMoney($model['shop_id'], $capital['pick'],20,$model['order_sn']);
    
        return $model->save([
            'is_settled' => 1,
            'settle_time' => getTime()
        ]);
    }
 
    
    /**
     * 订单详情
     * @param $orderId
     * @param $orderType
     * @return Order|null
     * @throws \think\exception\DbException
     */
    public static function getDetailByOrderId($orderId, $orderType)
    {
        return static::detail(['id' => $orderId, 'inpack_type' => $orderType]);
    }

    // 修改已拣货状态
    public function CheckISShelf($map){
       $map['status'] = 3;    
       $data = $this->where($map)->select();
       foreach ($data as $v){
           $packIds = explode(',',$v['pack_ids']);
           $packItem = (new Package())->whereIn('id',$packIds)->field('id,status')->select();
           $packItemStatus = array_unique(array_column($packItem->toArray(),'status'));
           $statusArr = [];
           foreach ($packItemStatus as $va){
               if ($va==7){
                   $statusArr[] = $va;
               }
           }
           if (count($statusArr)==count($packItemStatus)){
              $res = $this->where(['id'=>$v['id']])->update(['status'=>4,'pick_time'=>getTime()]); 
             
           }
       }
    } 
    
    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\User",'member_id');
    }


    // 修改已打包状态
    public function CheckISPack($map){
       $map['status'] = 4; 
       $data = $this->where($map)->select(); 
       foreach ($data as $v){
           $packIds = explode(',',$v['pack_ids']);
           $packItem = (new Package())->whereIn('id',$packIds)->field('id,status')->select();
           $packItemStatus = array_unique(array_column($packItem->toArray(),'status'));
           $statusArr = [];
           foreach ($packItemStatus as $va){
               if ($va==8){
                   $statusArr[] = $va;
               }
           }
           if (count($statusArr)==count($packItemStatus)){
              $this->where(['id'=>$v['id']])->update(['status'=>5,'unpack_time'=>getTime()]); 
           }
       }
    } 
    
    /**
     * 确认入库发送消息通知
     * @param $orderList
     * @return bool
     */
    public function sendEnterMessage($orderList,$type)
    {
        // 发送消息通知
        foreach ($orderList as $item) {
            MessageService::send('order.'.$type, [
                'order' => $item,
                'order_type' => OrderTypeEnum::MASTER,
            ]);
        }
        return true;
    }
    
     /**
     * 批量获取订单列表
     * @param $orderIds
     * @param array $with 关联查询
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getListByIds($orderIds, $with = [])
    {   
  
        $data = $this->getListByInArray('id', $orderIds,$with);
     
        return $data;
    }
    
    /**
     * 批量获取订单列表
     * @param string $field
     * @param array $data
     * @param array $with
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getListByInArray($field, $data, $with = [])
    {
        return $this->with($with)
            ->where($field, 'in', $data)
            ->where('is_delete', '=', 0)
            ->select();
    }
    
    /**
     * 订单详情
     * @param array|int $where
     * @param array $with
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($orderId)
    {
        return self::with(['user'])
        ->where('id',$orderId)
        ->find();
    }
    
    
       
     /**
     * 关联包裹图片表
     * @return \think\model\relation\HasMany
     */
    public function inpackimage()
    {
        return $this->hasMany('InpackImage')->order(['id' => 'asc']);
    }
    
    /**
     * 关联货架表
     * @return \think\model\relation\HasMany
     */
    public function shelfunititem()
    {
        return $this->hasOne('ShelfUnitItem','pack_id','order_sn');
    }
    
     /**
     * 关联优惠券
     * @return \think\model\relation\HasMany
     */
    public function usercoupon()
    {
        return $this->belongsTo('UserCoupon','user_coupon_id','user_coupon_id');
    }
    
           
     /**
     * 关联服务项目表
     * @return \think\model\relation\HasMany
     */
    public function inpackservice()
    {
        return $this->hasMany('InpackService');
    }
     /**
     * 关联包裹图片表
     * @return \think\model\relation\HasMany
     */
    public function linedata()
    {
        return $this->hasOne('Line', 'id', 'line_id')
            ->field(['name','id']);
    }
    
    /**
     * 显示支付方式
     * @param $value
     * @return mixed
     */
    public function getPayTypeAttr($value)
    {
        $type = [0=>'立即发货',1=>'货到付款',2=>'2月结'];
        return [
            'text'=> $type[$value],
            'value'=>$value
        ];
    }
    
    /**
     * 显示支付方式
     * @param $value
     * @return mixed
     */
    public function getIsPayTypeAttr($value)
    {
        //0 后台操作 1 微信 2 余额 3 汉特  4omipay  5现金支付
        $type = [0=>'后台操作',1=>'微信支付',2=>'余额支付',3=>'汉特支付',4=>'OMIPAY',5=>'现金支付'];
        return [
            'text'=> $type[$value],
            'value'=>$value
        ];
    }

}
