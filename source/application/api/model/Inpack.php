<?php
namespace app\api\model;

use app\common\model\Inpack as InpackModel;

/**
 * 线路模型
 * Class Express
 * @package app\api\model
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
     
    /**
     * 获取集运单
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
    public function getGList($query=[],$order=''){
        // 检索查询条件
         return $this->setWherePack($query)
         ->alias('in')
        ->with(['line','address','storage','Member'])
        ->field('in.*,u.user_id,u.user_code,u.nickName,u.avatarUrl')
        ->join('user u','u.user_id = in.member_id','left')
        ->order(['in.'.$order => 'desc'])
        ->where('in.is_delete',0)
        ->paginate(10, false, [
            'query' => \request()->request()
        ]);
    }
    
    public function getList($query=[]){
        // 检索查询条件
        !empty($query) && $this->setWhere($query);

        // 获取数据列表 
             return $this
            ->with(['line','address','storage'])
            ->order(['created_time' => 'desc'])
            
            ->where('is_delete',0)
            ->paginate(10, false, [
                'query' => \request()->request()
            ]);
    }
    
    /**
     * 待取件单
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
    public function getWList($query = [])
    {
        return $this->setWherePack($query)
            ->alias('in')
            ->field('in.*,u.user_id,u.user_code,u.nickName,u.avatarUrl')
            ->with(['Member','storage','address','shelfunititem.shelfunit.shelf'])
            ->where('in.is_delete',0)
            ->join('user u','u.user_id = in.member_id','left')
            ->order('in.shoprk_time DESC')
            ->paginate(10,false,[
                'query'=>\request()->request()
            ]);
    }
    
    /**
     * 待取件单
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
    public function getWYList($query = [])
    {
        return $this->setWherePack($query)
            ->alias('in')
            ->field('in.*,u.user_id,u.user_code,u.nickName,u.avatarUrl')
            ->with(['Member','storage','address','shelfunititem.shelfunit.shelf'])
            ->join('user u','u.user_id = in.member_id','left')
            ->where('in.is_delete',0)
            ->order('in.receipt_time DESC')
            ->paginate(10,false,[
                'query'=>\request()->request()
            ]);
    }
    
    public function Member(){
      return $this->belongsTo('app\api\model\User','member_id')->field('user_id,nickName,avatarUrl,user_code');
    }
    
    // 设置查询条件
    public function setQuery($where){
        foreach ($where as $v){
            $this->where($v[0],$v[1],$v[2]);
        } 
        return $this; 
    }
    
    // 设置 条件
    public function setWherePack($query){
        // dump($query);die;
        isset($query['keyword']) && $this->where('in.order_sn|in.member_id|in.t_order_sn|u.user_code|u.nickName','like','%'.$query['keyword'].'%');
        isset($query['storage_id']) && $this->where('in.storage_id','=',$query['storage_id']);
        isset($query['status']) && $this->where('in.status','in',$query['status']);
        // dump(isset($query['status']) && $this->where('in.status','in',$query['status']));die;
        return $this;
    }
    
    // 设置 条件
    public function setWhere($query){
        isset($query['status']) && $this->where('status','in',$query['status']);
        isset($query['member_id']) && $this->where('member_id','=',$query['member_id']);
        isset($query['inpack_type']) && $this->where('inpack_type','=',$query['inpack_type']);
        isset($query['shop_id']) && $this->where('shop_id','=',$query['shop_id']);
        isset($query['is_settled']) && $this->where('is_settled','=',$query['is_settled']);
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
    
    /**
     * 更新集运单的所属仓库,更新状态为7 已到货
     * @param int $id $shop_id
     * @return bool
     * @throws \think\exception\DbException
     */
    public function UpdateShop($id,$shop_id,$takecode){
        return $this
            ->where('id',$id)
            ->update([
                'shop_id'=> $shop_id,
                'status'=> 7,
                'take_code'=>$takecode,
                'shoprk_time' =>getTime(),
                'updated_time' =>getTime()
            ]);
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
    
    //支付回调里边用的
    public function getInpackSn($sn){
        return $this->where(['pay_order'=>$sn])->find();
    }
    
    public function getInpackGuoji($sn){
        return $this->where(['order_sn'=>$sn])->find();
    }
    
    public function getDetails($id,$field){
        return $this->with(['line','storage','inpackimage.file','inpackservice.service'])->field($field)->find($id);
    }
    
    public function line(){
        return $this->belongsTo('line','line_id');
    }
    
    public function service(){
        return $this->hasMany('app\api\model\InpackService','inpack_id','id');
    }
    
    public function address(){
        return $this->belongsTo('app\api\model\UserAddress','address_id');
    }
    
    public function storage(){
        return $this->belongsTo('app\store\model\store\Shop','storage_id');
    }
     
}