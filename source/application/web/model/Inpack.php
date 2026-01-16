<?php
namespace app\web\model;

use app\common\model\Inpack as InpackModel;

/**
 * 线路模型
 * Class Express
 * @package app\web\model
 */
class Inpack extends InpackModel
{
    public  $status = [
        'all' => '',
        'verify' => 1, // 查验
        'pay' => 2, // 待支付
        'payed' => 3, // 已支付
        'downshelf' => 4, // 下架
        'unpack' => 5, // 打包
        'intransit' => 6, // 转运中
     ];
    
     // 检查 包裹 是否处在未发货 阶段 
     public function checkPack($data){
         $packItem = (new Package())->whereIn('id',explode(',',$data['pack_ids']))->select();
         foreach ($packItem as $v){
              if ($v['status'] != 7) {
                  return false;
              }
         }
         return true;
     }
    
    public function getList($query=[]){
        // 检索查询条件
        !empty($query) && $this->setWhere($query);
       
        // 获取数据列表
        return $this
            ->with(['line','address','storage'])
            ->order(['created_time' => 'desc'])
            ->paginate(10, false, [
                'query' => \request()->request()
            ]);
    }
    
    public function Member(){
      return $this->belongsTo('app\web\model\User','member_id')->field('user_id,nickName,avatarUrl');
    }
    
    // 设置 条件
    public function setWhere($query){
        isset($query['status']) && $this->where('status','in',$query['status']);
        isset($query['member_id']) && $this->where('member_id','=',$query['member_id']);
        !empty($query['order_sn']) && $this->where('order_sn','like',"%".$query['order_sn']."%");
        !empty($query['t_order_sn']) && $this->where('t_order_sn','like',"%".$query['t_order_sn']."%");
        !empty($query['country']) && $this->where('country','=',$query['country']);
        !empty($query['line_id']) && $this->where('line_id','=',$query['line_id']);
        return $this;
    }

    // 修改已拣货状态
    public function CheckISShelf($map){
       $data = $this->where($map)->select(); 
       foreach ($data as $v){
           $packIds = explode(',',$v['pack_ids']);
           $packItem = (new Package())->whereIn('id',$packIds)->field('id,status')->select();
           $packItemStatus = array_unique(array_column($packItem->toArray(),'status'));
           $statusArr = [];
           foreach ($packItemStatus as $v){
               if ($v==7){
                   $statusArr[] = $v;
               }
           }
           if (count($statusArr)==count($packItemStatus)){
              $this->where(['id'=>$v['id']])->update(['status'=>4]); 
           }
       }
    } 

    // 修改已打包状态
    public function CheckISPack($map){
       $data = $this->where($map)->select(); 
       foreach ($data as $v){
           $packIds = explode(',',$v['pack_ids']);
           $packItem = (new Package())->whereIn('id',$packIds)->field('id,status')->select();
           $packItemStatus = array_unique(array_column($packItem->toArray(),'status'));
           $statusArr = [];
           foreach ($packItemStatus as $v){
               if ($v==8){
                   $statusArr[] = $v;
               }
           }
           if (count($statusArr)==count($packItemStatus)){
              $this->where(['id'=>$v['id']])->update(['status'=>5]); 
           }
       }
    } 
    
    public function getInpackSn($sn){
        return $this->where(['order_sn'=>$sn])->find();
    }
    
    public function getInpackTroder($sn){
        return $this->where(['t_order_sn'=>$sn])->find();
    }
    
    public function getDetails($id,$field){
        return $this->with(['line','storage'])->field($field)->find($id);
    }
    
    public function line(){
        return $this->belongsTo('line','line_id');
    }
    
    public function address(){
        return $this->belongsTo('app\web\model\UserAddress','address_id');
    }
    
    public function storage(){
        return $this->belongsTo('app\store\model\store\Shop','storage_id');
    }
    
        /**
     * 处理状态
     * @param $value
     * @return mixed
     */
    public function getStatusAttr($value)
    {
        $statusvalue =  [1=>'待查验',2=>'待支付',3=>'已支付','4'=>'已拣货','5'=>'已打包','6'=>'已发货','7'=>'已收货','8'=>'已完成','-1'=>'问题件']; 
        return $statusvalue[$value];
    }
     
}