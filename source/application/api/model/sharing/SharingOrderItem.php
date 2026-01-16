<?php
namespace app\api\model\sharing;
use app\common\model\sharing\SharingOrderItem as SharingOrderItemModel;
use app\api\model\sharing\SharingOrderAddress;
use app\api\model\UserAddress;
use app\common\model\Country;
use app\api\model\Package;

class SharingOrderItem extends SharingOrderItemModel {
    
    public function addItem($post,$res){
        $status = 2;
        $insert['order_id'] = $post['share_id'];
        $insert['package_id'] = $res;
        $insert['status'] = $status;
        $insert['user_id'] = $post['user_id'];
        $insert['wxapp_id'] = self::$wxapp_id;
         // 开启事务
        $this->startTrans();
        try {
            $this->save($insert);
            $this->addAddress($this['order_item_id'],$post);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }
    
    public function addAddress($id,$form){
        $countrylist = (new Country())->getListAllCountry();
        $detail = (new UserAddress())->find($form['address_id']);
        $address_insert = [
            'order_id' => $id,
            'is_head' => 0,
            'province' => $detail['province'],
            'city' => $detail['city'],
            'region' => $detail['region'],
            'address' => $detail['detail'].$detail['door'],
            'country' => $countrylist[$form['country_id']]['title'],
            'wxapp_id' => self::$wxapp_id
        ];
        return (new SharingOrderAddress())->save($address_insert);
    }
    
    // 获取参与最多的拼团Id
    public function getHotOrderId(){
        $sql = 'SELECT order_id,count(*) as count FROM yoshop_sharing_tr_order_item GROUP BY order_id order by count DESC limit 0,10';
        $data = $this->query($sql);
        return array_column($data,'order_id');
    }
    
    //获取拼团列表
    public function getList($query){
        !empty($query) && $this->setWhere($query);
        return $this
            ->with(['order','package'=> function($quer) use ($query) {
                $quer->where('member_id',$query['member_id']);
            },'package.Member','package.address'])
            ->where('status','<>',8)
            ->paginate(15,false, [
                'query' => \request()->request()
            ]);
    }
    
    public function setWhere($query){
              
        if (isset($query['member_id']) && $query['member_id']){
            // $this->where(['member_id'=>$query['member_id']]);
        }
        if (isset($query['status']) && $query['status']){
            $this->where('status','in',$query['status']);
        }
        return $this;
    }
    
    public function removeByPack(){
          // 开启事务
        $this->startTrans();
        try {
            $model = $this->where(['package_id'=>$id])->find();
            $model->delete();
            $SharingOrderAddress = (new SharingOrderAddress())->where(['order_id'=>$model['order_item_id'],'is_head'=>0])->delete();
            $this->commit();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
    
    // 获取审核列表
    public function getVeriftListWithPack($orderIds,$query){
        return $this->with(['package','order','package.Member'])->where('order_id','in',$orderIds)->where('status','in',$query['status'])->paginate(15,false, [
                'query' => \request()->request()
        ]);
    }
    
    public function cancle(){
         // 开启事务
        $this->startTrans();
        try {
            (new Package())->remove($this->package_id);
            $this->save(['status'=>8]);
            $this->commit();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
    
    // 审核修改状态
    public function modifyUpdata($param){
        $update['status'] = $param['status']==1?1:9;
        if ($param['status']==2){
            $update['reject_reason'] = $param['reject'];
        }
        return $this->save($update);
    }
    
    public function order(){
        return $this->belongsTo('SharingOrder','order_id');
    }
    
    public function user(){
        return $this->belongsTo('app\api\model\User','user_id');
    }
    
    public function package(){
        return $this->belongsTo('app\api\model\Inpack','package_id');
    }
}