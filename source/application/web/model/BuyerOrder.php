<?php
namespace app\web\model;
use app\web\model\User;
use app\common\model\BuyerOrder as BuyerOrderModel;
use app\web\model\user\BalanceLog;
use app\common\enum\user\balanceLog\Scene as SceneEnum;
use think\Db;
/**
 * 订单收货地址模型
 * Class OrderAddress
 * @package app\common\model
 */
class BuyerOrder extends BuyerOrderModel
{
    protected $name = 'buyer_order';
    protected $updateTime = false;
    /** 
     * 支持解析链接
     */
    private static $support = [
       'detail.tmall.com', 
       'item.taobao.com',
       'item.jd.com/',
       'detail.1688.com/'
    ];
    
    // 平台url对应映射
    private static $url_map = [
        'tmall' => '天猫',
        'taobao' => '淘宝',
        'jd' => '京东',
        '1688' => '阿里巴巴1688'
    ];
    
    /**
     * 新增预报单
     */
    public function add($data){
       $this->Validater($data); 
       $url_pase = $this->check($data['url']);
       $post = [
         'url' => $data['url'],
         'order_sn' => 'DG'.createSn(),
         'spec' => $data['spec'],
         'price' => $data['price']??"0",
         'num' => $data['num'],
         'free' => $data['free'],
         'member_id' => $data['member_id'],
         'remark' => $data['remark'],
         'status' => 1,
         'address_id' => $data['address_id'],
         'storage_id' => $data['storage_id'],
         'is_close' => 0,
         'service_free' => $data['service_free'],
         'created_time' => getTime(),
         'updated_time' => getTime(),
         'reason' => '',
         'batch' => $data['batch'],
         'palform' => $url_pase['web']??'未知平台',
         'wxapp_id' => self::$wxapp_id,
       ];
       if (!$id = $this->insertGetId($post)){
           return false; 
       }
      
       $post['b_order_id'] = $id;
       $this->toPay($post);
       return $id;
    }
    
    public function checkData($data,$member_id){
        $amount = 0;
        foreach ($data as $v){
             $amount+= $v['price']*$v['num']+$v['free'];
        }
        $user = (new User())->find($member_id);
        if ($user['balance']<$amount){
            $this->error = '余额不足，请充值';
            return false;
        }
        return true;
    }
    
    public function toPay($data){
        $amount = $data['price']*$data['num']+$data['free'];
        $user = (new User())->find($data['member_id']);
        Db::startTrans();
        $update['real_payment'] = $amount;
        $update['is_pay'] = 1;
        $update['pay_time'] = getTime();
        try {
             $this->where('b_order_id',$data['b_order_id'])->update($update);
             $memberUp = (new User())->where(['user_id'=>$user['user_id']])->update([
               'balance'=>$user['balance']-$amount,
               'pay_money' => $user['pay_money']+ $amount,
             ]);
             if (!$memberUp){
                 Db::rollback();
                 return false;
             }
              // 新增余额变动记录
             BalanceLog::add(SceneEnum::CONSUME, [
              'user_id' => $user['user_id'],
              'money' => $amount,
              'remark' => '代购订单'.$data['order_sn'].'的支付',
              'sence_type' => 2,
          ], [$user['nickName']]);
         }catch(\Exception $e){
             dump($e); die;
             return false;
         }
         Db::commit();
         return true;
    }
    
    // 检查是否支持解析 
    public function check($url){
        $urls = parse_url($url);
        if (!$url){
            $this->error = '请输入正确的购买链接'; 
            return false; 
        }
        if (!isset($urls['host'])){
             $this->error = '请输入正确的购买链接'; 
            return false; 
        }
        $host = $urls['host'];
        $hostarr = explode('.',$host);
        if (!isset(self::$url_map[$hostarr[1]])){
            return false;
        }
        return [
            'web' => self::$url_map[$hostarr[1]]
        ];
    }
    
    /**
     * 获取数据列表
     */
    public function getList($query){
         // 检索查询条件
        !empty($query) && $this->setWhere($query);
        // 获取数据列表
        return $this
            ->order(['created_time' => 'desc'])
            ->paginate(10, false, [
                'query' => \request()->request()
            ]);
    }
    
    public function setWhere($query){
        isset($query['status']) && $this->where('status','in',$query['status']);
        isset($query['member_id']) && $this->where('member_id','=',$query['member_id']);
        return $this;
    }
    
    public function Validater($data){
       
        if (!isset($data['url']) || empty($data['url'])){
            $this->error = '请输入购买链接'; 
            return false;
        }
        if (!isset($data['spec']) || empty($data['spec'])){
            $this->error = '请输入您要购买的规格'; 
            return false;
        }
        return true;
        
    }
}
