<?php
namespace app\api\controller\sharp;
use app\api\controller\Controller;
use app\api\model\sharing\SharingOrder;
use app\api\model\sharing\SharingOrderItem;
use app\api\model\sharing\SharingOrderAddress;
use app\api\model\Line;
use app\api\model\PackageItem;
use app\common\model\Country;
use app\api\model\User as UserModel;
use app\api\service\sharing\SharingOrder as SharingOrderService;
use app\store\model\Inpack;
use app\api\model\sharing\Setting;
/**
 * 拼团控制器
 * Class Article
 * @package app\api\controller
 */
class Order extends Controller
{
   
     // 创建拼团订单
     public function create(){
        $form = $this->request->param();
        // 当前用户信息
        $userInfo = $this->getUser();
        $model = (new SharingOrder());
        $form['member_id'] = $userInfo['user_id'];
        if ($model->created($form)){
              return $this->renderSuccess('拼团已成功发起');
        }
        return $this->renderError($model->getError()??'操作失败');
     }
     
     // 拼团订单管理列表
     public function managelist(){
        $query = $this->request->param();
        // 当前用户信息
        $userInfo = $this->getUser();
        $query['member_id'] = $userInfo['user_id'];
        $model = (new SharingOrder());
        $query['status'] = $this->mapStatus($query['status']);
        $list = $model->getList($query);

        $shareService = (new SharingOrderService());
        // 判断是否可打包
        $list = $shareService->checkIsInPack($list);
        return $this->renderSuccess(compact('list'));
     }
     
     // 解散拼团活动
     public function dissolution(){
        $id = $this->request->param('id');
        $userInfo = $this->getUser();
        $model = (new SharingOrder())->detail($id);
        if ($model->dissolution($userInfo)){
            return $this->renderSuccess('拼团成功取消');
        }
        return $this->renderError($model->getError()??'操作失败');
     }
     
     public function list(){
        $query = $this->request->param();
        // 当前用户信息
        $userInfo = $this->getUser();
        $query['member_id'] = $userInfo['user_id'];
        
        if ($query['type']=='sharing'){
            $query['status'] = 1;
        }
        if ($query['type']=='received'){
            $query['status'] = 6;
        }
        if ($query['type']=='no_pay'){
            $query['status'] = 4;
        }
        if ($query['type']=='no_send'){
            $query['status'] = 5;
        }
        if ($query['type']=='complete'){
            $query['status'] = 7;
        }
        $model = (new SharingOrderItem());
        $list = $model->getList($query);
        foreach ($list as $key => $v){
            $address = (new SharingOrderAddress())->where(['order_id'=>$v['order_item_id'],'is_head'=>0])->find();
            $list[$key]['address'] = $address['country'].$address['province'].$address['city'].$address['region'];
        }
        return $this->renderSuccess(compact('list'));
     }
     
     // 获取热门城市
     public function getHotCountry(){
        $model = (new SharingOrder());
        $list = $model->getHotCountryIds();
        
        $data = (new Country())->where('id','in',$list)->select();
        return $this->renderSuccess(compact('data'));
     }
     
     // 拼团广场数据
     public function center(){
        $query = $this->request->param();
        if ($query['type']=='recommend'){
            $query['is_recommend'] = 1;
        }
        // if ($query['type']=='hot'){
        //     $order_ids = (new SharingOrderItem())->getHotOrderId();
        //     $query['order_ids'] = $order_ids;
        // }
        // 当前用户信息
        $userInfo = $this->getUser();
        $query['user_id'] = $userInfo['user_id'];
        $model = (new SharingOrder());
        $list = $model->getList($query);
        $shareService = (new SharingOrderService());
         // 获取已拼团包裹重量
         $list = $shareService->getPackageWeight($list);
         $list = $shareService->getMainAddressInfo($list);
        return $this->renderSuccess(compact('list'));
     }
     
     // 取消该拼团单
     public function cancle(){
        $id = $this->request->param('id');
        $userInfo = $this->getUser();
        $model = (new SharingOrderItem())->detail($id);
        if ($model->cancle($userInfo)){
            return $this->renderSuccess('拼团成功取消');
        }
        return $this->renderError($model->getError()??'操作失败');
     }
     
     // 拼单详情
     public function detail(){
         $id = $this->request->param('id');
         $item_id = $this->request->param('item_id');
         $userInfo = $this->getUser();
         $model = (new SharingOrder())->detail($id);
         $item = (new SharingOrderItem())->find($item_id);
         $is_publiser = $userInfo['user_id']==$model['member_id']?true:false;
         $address_where['order_id'] = $item['order_item_id'];
         $is_publiser == true?$address_where['is_head'] = 1:$address_where['is_head'] = 0;
        //  dump($model);die;
         if ($model['address_id']){
             $model['address'] = (new SharingOrderAddress())->where($address_where)->find();
         }
         if ($model['line_id']){
             $model['line'] = (new Line())->find($model['line_id']);
         }
         return $this->renderSuccess(compact('model'));
     }
     
     // 拼单详情
     public function detail_join(){
         $id = $this->request->param('id');
         $userInfo = $this->getUser();
         $model = (new SharingOrder())->detail($id);
         $inpack = new Inpack();
         $userModel = new UserModel();
         $model['leader'] =$userModel->find($model['member_id']);

         $orderItem = (new SharingOrderItem())->where(['order_id'=> $id])->select();
         $userList = [];
         $packageItem = [];
         if($orderItem){
             foreach($orderItem as $key => $val){
                $packageItem[$key] = $inpack->where('id',$val['package_id'])->find();
             }
             
             foreach($packageItem as $key => $val){
                $userList[$key] = $userModel->where('user_id',$val['member_id'])->find();
              }
             
         }
         if ($model['address_id']){
             $address_where['order_id'] = $model['order_id'];
             $address_where['is_head'] = 1;
             $model['address'] = (new SharingOrderAddress())->where($address_where)->find();
         }
         $SharingService = (new SharingOrderService());
         $model['setting'] =  htmlspecialchars_decode(Setting::getItem('sharp')['describe']);
        //  dump($setting);die;
        //  $allWeight = $SharingService->getHasWeight($orderItem);
        //  $allWeight = $allWeight>$model['predict_weight']?$model['predict_weight']:$allWeight;
        //  $model['allweight'] = $allWeight;
         $count = (new SharingOrderItem())->where(['order_id'=> $id])->count();
         $model['percent'] = (round($count/$model['max_people'],2))*100;
         $model['join_user_list'] = $userList;
         if ($model['line_id']){
             $model['line'] = (new Line())->find($model['line_id']);
         }
         return $this->renderSuccess(compact('model'));
     }
     
     // 申请加入拼团
     public function applytopintuan(){
         $param = $this->request->param();
         $SharingOrderItem = new SharingOrderItem();
         $inpack = new Inpack();
         $setting = Setting::getItem('sharp');
         if($setting['is_open'] == 0 ){
             return $this->renderError('暂时无法加入拼团');  
         }
         $shareoder = $SharingOrderItem->where('package_id',$param['id'])->where('status','<',9)->find();
         if(!empty($shareoder)){
             return $this->renderError('请勿重复加入该拼团');  
         }
         $data = [
           'order_id' => $param['pin_id'],
           'package_id' => $param['id'],
           'create_time' => time(),
           'update_time' => time(),
           'status' => $setting['is_shenhe']==1?2:1,
           'wxapp_id' => $param['wxapp_id'],
         ];
         if($SharingOrderItem->insert($data)){
              $inpack->where('id',$param['id'])->update(['inpack_type' => 1 ]);
              return $this->renderSuccess('加入拼团成功');
         }
         return $this->renderError('暂时无法加入拼团');  
     }
     
     // 变更地址
     public function addressUpdate(){
        $id = $this->request->param('id');
        $userInfo = $this->getUser();
        $address = $this->request->param('address_id');
        $order = (new SharingOrder())->find($id);
        $order_item = (new SharingOrderItem())->where(['order_id'=>$order['order_id'],'user_id'=>$userInfo['user_id']])->find();
        $addressInfo = (new SharingOrderAddress())->where(['order_id'=>$order_item['order_item_id']])->find();
        if(!$order->updateAddress($addressInfo,$address)){
            return $this->renderError($order->getError()??'操作失败');
        };
        return $this->renderSuccess('地址更新成功');
     }
     
     // 审核列表
     public function verifylist(){
        $query = $this->request->param();
        $ShareOrderItem = (new SharingOrderItem());
        $where['status'] = [1,2,3,4,5,6];
        if (isset($query['type']) && $query['type']=='verify'){
            $where['status'] = [2];
        }
        if (isset($query['type']) && $query['type']=='reject'){
            $where['status'] = [9];
        }
        if (isset($query['type']) && $query['type']=='ok'){
            $where['status'] = [1,3,4,5,6,7];
        }
        $shareOrder = (new SharingOrder());
        $userInfo = $this->getUser();
        // 获取我发布的拼团ID
  
        $orderIds = $shareOrder->getMyOrderIds($userInfo['user_id'],$where);
            //  dump($orderIds);die;
        $orderIdsArr = array_column($orderIds->toArray(),'order_id');
        $list = $ShareOrderItem->getVeriftListWithPack($orderIdsArr,$where);
                        
        foreach ($list as $key => $v){
            $packageItem = (new PackageItem())->where(['order_id'=>$v['package']['id']])->select();
            $packageName = array_column($packageItem->toArray(),'class_name');
            $list[$key]['name'] = implode('-',$packageName);
        }
    
        return $this->renderSuccess(compact('list'));
     }
     
     // 修改审核状态
     public function verifyupdate(){
        $id = $this->request->param('id');
        $param = $this->request->param();
        $model = (new SharingOrderItem());
        $detail = $model->detail($id);
        // dump($detail);die;
        if ($detail->modifyUpdata($param)){
            return $this->renderSuccess('审核成功'); 
        }
        return $this->renderError($order->getError()??'操作失败');
     }
     
     // 映射查询状态
     public function mapStatus($value){
         $query_status = [
            0 => [1,2,3,4,5,6,7,8],
            1 => [1,2,3,4,5,6],
            2 => [8],
            3 => [7]
         ];
         return $query_status[$value]??0; 
     }
    
}
