<?php
namespace app\store\controller\apps\sharing;
use app\store\controller\Controller;
use app\store\model\sharing\SharingOrder;
use app\store\model\sharing\SharingOrderItem;
use app\store\model\store\Shop as ShopModel;
use app\store\model\sharing\SharingOrderAddress;
use app\common\model\Setting;
use app\store\model\Line;
use app\store\model\UserAddress;
use app\store\model\Inpack;
use app\common\model\User;
use app\common\service\Message;
use app\api\model\Logistics;
use app\store\model\Countries;
/**
 * 拼单管理控制器
 * Class Active
 * @package app\store\controller\apps\sharing
 */
class Order extends Controller
{
    // 首页
    public function index(){
       $SharingOrder = (new SharingOrder());
       $shopList = ShopModel::getAllList();
       $SharingOrderItem = new SharingOrderItem();
       $param = $this->request->param();
       $lists = $SharingOrder->getList($param);
       foreach ($lists as $key => $item){
          $lists[$key]['count'] = $SharingOrderItem->where('order_id',$item['order_id'])->where('status','<',9)->count();
       }
       $list = $lists;
        // dump($list);die;
       $set = Setting::detail('store')['values']['address_setting'];
       $setcode = Setting::detail('store')['values']['usercode_mode'];
       return $this->fetch('index',compact('list','shopList','set','setcode'));
    }
    
    
    //查看集运单里边的集运单列表
    public function inpacklist(){
        $SharingOrderItem = new SharingOrderItem();
        $Inpack = new Inpack();
        $shopList = ShopModel::getAllList();
        $lists = $SharingOrderItem->where("order_id",input('order_id'))->where('status','<',9)->select();
        $list = [];
        $set = Setting::detail('store')['values']['address_setting'];
        foreach ($lists as $key =>$value){
        //   $list[$key]['pin_status']  = $value['status'];
           $list[$key] = $Inpack::details($value['package_id']);
           $list[$key]['pin_status'] =$value['status'];
        }
        // dump($list);die;
        return $this->fetch('inpacklist',compact('list','set','shopList'));
    }
    
    
    //从拼团中移出订单
    public function yichu(){
        $SharingOrderItem = new SharingOrderItem();
        $Inpack = new Inpack();
        $res = $SharingOrderItem->where('package_id',input('id'))->delete();
        if($res){
             $result = $Inpack->where('id',input('id'))->update(['inpack_type' => 0]);
             if(!$result){
                 return $this->renderError('移出失败');
             }
        }
        return $this->renderSuccess('移出成功');
    }
    
    //对拼团订单发货
    public function delivery($id){
        $SharingOrder = (new SharingOrder());
        $SharingOrderItem = new SharingOrderItem();
        $Inpack = new Inpack();
           //更新拼团订单的inpack_id 以及 所有的用户集运单中的t_order_sn
        $detail = $SharingOrder->where('order_id',$id)->find();
        $track = getFileData('assets/track.json');
        $track = array_slice($track,0,998);
        if (!$this->request->isAjax()){
            return $this->fetch('delivery', compact(
                'detail','track'
            ));
        }
        $data = input();
       
        $res = $SharingOrder->where('order_id',$id)->update(['inpack_id'=> $data['delivery']['t_order_sn'],'status'=>6]);
        //TODO发货记录log and message send 
        if(!$res){
             return $this->renderError('发货失败');
        }
        $pack = $SharingOrderItem->where('order_id',$id)->select();
        foreach ($pack as $val){
            $Inpack->where('id',$val['package_id'])->update(['t_order_sn'=>$data['delivery']['t_order_sn'],'status'=>6]);
        }
        //更新物流信息
        $this->AddPinLog($datas = ['selectIds' => $id,'logistics_describe' => '拼团订单已发货,国际单号：'.$data['delivery']['t_order_sn']]);
        
        return  $this->renderSuccess('发货成功','javascript:history.back(1)');
    }
    
    //更新拼团订单的物流信息
    public function AddPinLog($data){
        $SharingOrderItem = new SharingOrderItem();
        $res = $SharingOrderItem->where('order_id',$data['selectIds'])->select();
        foreach ($res as $key =>$val){
            $sendOrder = (new Inpack())->details($val['package_id']);
            //发送用户以及用户信息
            $userId = $sendOrder['member_id'];
            $data['code'] = $val;
            $user = User::detail($userId);
            
            //发送订阅消息，模板消息
            $data['order_sn'] = isset($sendOrder['t_order_sn'])?$sendOrder['t_order_sn']:$sendOrder['order_sn'];
            $data['order'] = $sendOrder;
            $data['order']['total_free'] = $sendOrder['free'];
            $data['order']['userName'] = $user['nickName'];
            $data['order_type'] = 10;
            $data['order']['remark'] =$data['logistics_describe'] ;
            Message::send('order.payment',$data);
            
            //邮件通知
            if($user['email']){
                $this->sendemail($user,$data,$type=1);
            }
             $res = Logistics::addLog($sendOrder['order_sn'],$data['logistics_describe'],date("Y-m-d H:i:s",time()));
             if (!$res){
                return $this->renderError('物流更新失败');
             }
        }
        return $this->renderSuccess('物流更新成功');
    }
    
    
    //更新拼团订单的物流信息
    public function alllogistics(){
        $data = input();
        if(!$data['logistics_describe']){
             return $this->renderError('请输入订单物流信息');
        }
        $SharingOrderItem = new SharingOrderItem();
        $res = $SharingOrderItem->where('order_id',$data['selectIds'])->select();
        foreach ($res as $key =>$val){
            $sendOrder = (new Inpack())->details($val['package_id']);
            //发送用户以及用户信息
            $userId = $sendOrder['member_id'];
            $data['code'] = $val;
            $user = User::detail($userId);
            
            //发送订阅消息，模板消息
            $data['order_sn'] = isset($sendOrder['t_order_sn'])?$sendOrder['t_order_sn']:$sendOrder['order_sn'];
            $data['order'] = $sendOrder;
            $data['order']['total_free'] = $sendOrder['free'];
            $data['order']['userName'] = $user['nickName'];
            $data['order_type'] = 10;
            $data['order']['remark'] =$data['logistics_describe'] ;
            Message::send('order.payment',$data);
            
            //邮件通知
            if($user['email']){
                $this->sendemail($user,$data,$type=1);
            }
             $res = Logistics::addLog($sendOrder['order_sn'],$data['logistics_describe'],$data['created_time']);
             if (!$res){
                return $this->renderError('物流更新失败');
             }
        }
        return $this->renderSuccess('物流更新成功');
    }
    
    // 修改用户地址
    public function updateAddress(){
        $selectIds = $this->postData();
        $SharingOrder = (new SharingOrder()); 
        if(!$selectIds['id'] || !$selectIds['address_id']){
            return $this->renderError('修改失败');
            
        }
        $result = $SharingOrder->where('order_id',$selectIds['id'])->update(['address_id'=>$selectIds['address_id']]);
        return $this->renderSuccess('修改成功');
    }
    
    //拼团新增
    public function add(){
        $model = new SharingOrder;
        if (!$this->request->isAjax()) {
            $shopList = ShopModel::getListName();
            $line = (new Line())->getListAll();
            $countryList = (new Countries())->getListAll();
            return $this->fetch('add',compact('line','shopList','useraddress','countryList'));
        }
        // 新增记录
        if ($model->add($this->postData('data'))) {
            return $this->renderSuccess('添加成功', url('store/apps.sharing.order/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }
    
    
    
    // 拼团编辑
    public function edit(){
        $model = new SharingOrder;
        if (!$this->request->isAjax()) {
            $id= input('order_id');
            $detail = $model::detail($id);
            $shopList = ShopModel::getListName();
            $line = (new Line())->getListAll();
            $countryList = (new Countries())->getListAll();
            return $this->fetch('edit',compact('detail','shopList','line','countryList'));
        }
                      
        // 新增记录
        if ($model->edit($this->postData('data'))) {
            return $this->renderSuccess('添加成功', url('store/apps.sharing.order/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }
    

    
    //拼团审核
    public function verify(){

        $param = $this->request->param();
        $SharingOrderItem = new SharingOrderItem();
        $Inpack = new Inpack();
        $data = [
            'status'=> $param['verify']['status'],
            'reject_reason' =>$param['verify']['reason']
        ];
 
        if($data['status']==9){
            $result = $Inpack->where('id',$param['verify']['id'])->update(['inpack_type' => 0]);
            $res= $SharingOrderItem->where('package_id',$param['verify']['id'])->delete();
           if($res){
                 return $this->renderSuccess('操作成功');
            }
        }
        $resf= $SharingOrderItem->where('package_id',$param['verify']['id'])->update($data);
        if($resf){
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($SharingOrderItem->getError()??'操作失败');
    }
      
}