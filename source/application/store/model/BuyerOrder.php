<?php
namespace app\store\model;
use app\common\model\BuyerOrder as BuyerOrderModel;
use app\common\model\Express;
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
     * 获取数据列表
     */
    public function getList($query){
         // 检索查询条件
        !empty($query) && $this->setWhere($query);
        // 获取数据列表 
        return $this->with('member')
            ->order('updated_time DESC')
            ->paginate(10, false, [
                'query' => \request()->request()
            ]);
    }
    
    public function setWhere($query){
        !empty($query['status']) && $this->where('status','in',$query['status']);
        !empty($query['express_num']) && $this->where('order_sn','=',$query['express_num']);
        !empty($query['batch']) && $this->where('batch','=',$query['batch']);
        !empty($query['start_time']) && $this->where('created_time', '>', $query['start_time']);
        !empty($query['end_time']) && $this->where('created_time', '<', $query['end_time']." 23:59:59");
        if(!empty($query['search'])){
           if (!is_numeric($query['search'])){ 
              $id = Db::table('yoshop_user')->where('nickName', 'like',$param['search'])->value('user_id');
           }else{
              $id = $query['search'];
           }
           if (!empty($id)){
               $this->where('member_id','=',$id);
           }else{
               $this->where('member_id','=',0);
           }
        }
        return $this;
    }
    
    // 更新代购单信息
    public function updateData($data){
        foreach ($data as $k => $value) {
            $updata[$k] = $value;
        }
        if ($data['status']==3){
            if ($data['express_id']){
                if (empty($data['express_num'])){
                    $this->error = '请填写物流信息';
                    return false;
                }
                $updata['express_name'] = (new Express())->where(['express_id'=>$data['express_id']])->value('express_name');
            }
        }
        if ($data['status'] == 5){
             $order = $this->find($data['id']);
             if ($order['status']>=5){
                  $this->error = '订单已同步到集运单';
                  return false;
             }
             // 同步到集运订单
             $inpack = (new Inpack())->anyicData($order);
        }
        if ($data['status']==-1){
            $updata['rufund_step'] = 2;
            $updata['real_payment'] = 0;
        }
        $updata['updated_time'] = getTime();
        unset($updata['id']);
        unset($updata['type']);
        
        return $this->where(['b_order_id'=>$data['id']])->update($updata);
    }
    
    
    public function Member(){
      return $this->belongsTo('app\api\model\User','member_id')->field('user_id,nickName,avatarUrl');
    }
   
}
