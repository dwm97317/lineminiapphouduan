<?php
namespace app\store\controller;
use app\api\model\Logistics;
use app\store\model\Inpack;
use app\store\model\Package;
use app\api\controller\Package as PackageModel;
use app\store\model\PackageItem;
use app\store\model\Line;
use app\store\model\ShelfUnitItem;
use app\store\model\Comment as CommentModel;
use app\store\model\Express as ExpressModel;
use app\store\model\Ditch as DitchModel;
use app\common\model\User;
use app\common\service\Message;
use app\common\model\ExpressService;
use app\common\service\WaybillService;
use app\store\model\User as UserModel;
use app\store\model\store\Shop as ShopModel;
use app\common\model\Setting;
use app\store\model\InpackService;
use app\api\model\Setting as SettingModel;
use app\store\model\user\UserLine;
use app\store\model\UserAddress;
use app\store\model\Batch;
use app\common\model\Setting as SettingModelPlus;
use app\store\model\sharing\SharingOrder;
use app\store\model\sharing\SharingOrderItem;
use app\store\model\PackageService;
use app\common\model\store\shop\Capital;
use app\store\model\store\shop\ShopBonus;
use app\store\model\store\shop\Clerk;
use app\api\model\dealer\Setting as SettingDealerModel;
use app\common\model\dealer\User as DealerUser;
use app\api\model\dealer\Referee as RefereeModel;
use app\common\model\dealer\Order as DealerOrder;
/**
 * 订单管理
 * Class Order
 * @package app\store\controller
 */
class TrOrder extends Controller
{
    /**
     * 待查验订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function verify_list()
    {
        return $this->getList('待查验订单列表', 'verify');
    }

    /**
     * 待支付订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function pay_list()
    {
        
        return $this->getNoPayList('待支付订单列表', 'pay');
    }

    /**
     * 待发货订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function payed_list()
    {
        return $this->getList('待发货订单列表', 'payed');
    }

    /**
     * 转运中订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function sending()
    {
        return $this->getList('已发货订单列表', 'sending');
    }
    
    /**
     * 转运中订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function sended()
    {
        return $this->getList('已到货订单列表', 'sended');
    }
    
    /**
     * 转运中订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function complete()
    {
        return $this->getList('已完成订单列表', 'complete');
    }
    
    /**
     * 转运中订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function quicklypack()
    {
        return $this->getQuicklypack('已完成订单列表', 'all');
    }
    
    /**
     * 订单列表
     * @param string $title
     * @param string $dataType
     * @return mixed
     * @throws \think\exception\DbException
     */
    private function getQuicklypack($title, $dataType)
    {
        // 订单列表
        $model = new Inpack;
        $set = Setting::detail('store')['values'];
        $list = $model->getQuicklypack($dataType, $this->request->param());
        foreach ($list as &$value) {
            $value['num'] = !empty($value['pack_ids'])?count(explode(',',$value['pack_ids'])):0;
            $value['down_shelf'] = 0;
            $value['inpack'] = 0;
           if ($dataType=='payed'){
                $value['down_shelf'] = (new Package())->where('id','in',explode(',',$value['pack_ids']))->where('status',7)->count();
                $value['inpack'] = (new Package())->where('id','in',explode(',',$value['pack_ids']))->where('status',8)->count();
           }
        }

        return $this->fetch('index', compact('list','dataType','set'));
    }
    
    
    public function package($id){
         // 订单详情
        // $detail = Inpack::details($id);
        $Package = new Package();
        $list = $Package->with("packageimage.file")->where('inpack_id',$id)->select();
    //   dump($list->toArray());die;
        foreach ($list as $k => $v){
            $list[$k]['shelf'] = (new ShelfUnitItem())->getShelfUnitByPackId($v['id']);
            $list[$k]['pakitem'] = (new PackageItem())->where('order_id',$v['id'])->select();
        }
        //   dump($list->toarray());die;
        return $this->fetch('package', compact('list','id'));
    }
    
    //修改集运单所属用户id
    public function changeUser(){
        $ids = $this->postData('selectIds')[0];
        $user_id = $this->postData('user_id')[0];
        $Package = new Package();
        $idsArr = explode(',',$ids);
        $array = (new Inpack())->whereIn("id",$idsArr)->where('is_delete',0)->select();
        foreach ($array as $key => $val){
            $Package->where('inpack_id',$val['id'])->update(['member_id'=>$user_id,'updated_time'=>getTime(),'is_take'=>2]);
            $res = $val->save(['member_id'=>$user_id,'updated_time'=>getTime()]);
        }
        if (!$res){
            return $this->renderError('修改提交失败');
        }
        return $this->renderSuccess('修改提交成功');
    }
    
     /**
     * 获取集运路线的数据
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function getlinedata(){
        $param = $this->request->param();
        $model = new Inpack;
        $Line = new Line;
        $line = $model->field('line_id')->where('t_number',$param['ditch_id'])->where('is_delete',0)->group('line_id')->select();
        foreach ($line as $key =>$val){
            $data[$key] = $Line->details($val['line_id']);
            $data[$key]['total_order'] = $model->where('t_number',$param['ditch_id'])->where('line_id',$val['line_id'])->where('is_delete',0)->count();
            $data[$key]['exceed'] = $model->where('t_number',$param['ditch_id'])->where('line_id',$val['line_id'])->where('is_delete',0)->where('is_exceed',1)->count();
            if($data[$key]['total_order']==0){
                $data[$key]['exced_ratio'] = '0%';
            }else{
                $data[$key]['exced_ratio'] = number_format($data[$key]['exceed']/$data[$key]['total_order'],4)*100 .'%';
            }
            $data[$key]['total_free'] = $model->where('t_number',$param['ditch_id'])->where('line_id',$val['line_id'])->where('is_delete',0)->sum('real_payment');
        }
        return $this->renderSuccess('更新成功','',compact('data'));
    }
    
    /**
     * 集运单详情
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function orderdetail($id){
        $packageItem = [];
        $line = (new Line())->getList([]);
        // 订单详情
        $detail = Inpack::details($id);
        // dump($detail->toArray());die;
        if ($detail['status']>=2){
            $detail['total'] = $detail['free']+$detail['pack_free']+$detail['other_free'];
        }
        $packages = explode(",",$detail['pack_ids']);
        
        foreach($packages as $key => $value){
           $package[$key] = (new Package())->where("id",$value)->find();
           $packageItems[$key] = (new PackageItem())->where("order_id",$value)->find();
           if(!empty($packageItems[$key])){
               $packageItem[$key] = $packageItems[$key];
           }
        }
        $packageService = (new PackageService())->getList([]);
        $detail['service'] = (new InpackService())->with('service')->where('inpack_id',$id)->select();
        // dump($PackageItem);die;
        //获取订单日志记录
        if (isset($detail['order_sn'])) {
            $detail['log'] = (new Logistics())->where('order_sn',$detail['order_sn'])->select();
        } else {
            $detail['log'] = [];
        }
        //获取到用户信息
        $detail['user'] = (new UserModel())->where('user_id',$detail['member_id'])->find();
        //获取到仓库信息
        $detail['storage'] = (new ShopModel())->where('shop_id',$detail['storage_id'])->find();
        $set = Setting::detail('store')['values'];
        return $this->fetch('orderdetail', compact(
            'detail','line','package','packageItem','packageService','set'
        ));
    }
    
     /**
     * 后台修改备注信息
     * @return false|int
     * @throws \think\exception\DbException
     */
    public function changeRemark() {
        $param = $this->request->param();
        $model = new Inpack();
        // 直接使用数据库更新，避免保存不存在的字段
        $result = $model->where('id', $param['id'])->update(['remark' => $param['remark']]);
        if($result !== false){
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError('更新失败');
    }

     /**
     * 后台批量修改集运单状态
     * @param $selectIds
     * @param $status
     * @return false|int
     * @throws \think\exception\DbException
     */
    public function upsatatus(){
       $ids = $this->postData('selectIds')[0];
       $status = $this->postData('pack')['status'];
       
       $noticesetting = SettingModelPlus::getItem('notice');
       $idsArr = explode(',',$ids);
       $model = new Inpack();
       //循环处理订单状态
       foreach ($idsArr as $v){
           $order =  $model->where(['id'=>$v])->find($v);
           $userData = (new User())->where('user_id',$order['member_id'])->find();
           $pack_ids = explode(',',$order['pack_ids']);
           $_up = [
             'status' => $status
           ];
           
           $status_map = [
               5 => '8',
               6 => '9',
               7 => '10',
               8 => '11',
           ];
           if($status==5){
               $_up['status'] = 3;
           }
           if($status==7){
               $_up['shoprk_time'] = getTime();
           }
           if($status==8){
               $_up['receipt_time'] = getTime();
           }
           $model->where(['id'=>$v])->update($_up);
           (new Package())->where('id','in',$pack_ids)->update(['status'=>$status_map[$status]]);
           if(strpos($noticesetting['dosend']['describe'],'code')){
                 $dosend = str_ireplace('{code}', $order['t_order_sn'], $noticesetting['dosend']['describe']);
            }else{
                 $dosend = $noticesetting['dosend']['describe'];
            }
           $status_remark = [
               5=> "退回到待发货状态，修改发货单号",
               6 => $dosend,
               7 => $noticesetting['reach']['describe'],
               8 => $noticesetting['take']['describe'],
           ];
           
           
           //处理模板消息
           $data['order_sn'] = $order['order_sn'];
           $data['order'] = $order;
           $data['order']['total_free'] = $order['free'];
           $data['order']['userName'] = $userData['nickName'];
           $data['order_type'] = 10;
           $data['order']['remark'] = $status_remark[$status];
           Logistics::addInpackLogs($order['order_sn'],$status_remark[$status]);
           Message::send('order.payment',$data);
       }    
       return $this->renderSuccess('更新成功');
    }
    
   /**
     * 已完成订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function complete_list()
    {
        return $this->getList('转运中订单列表', 'intransit');
    }

    /**
     * 已取消订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function cancel_list()
    {
        return $this->getList('已取消订单列表', 'cancel');
    }

    /**
     * 全部订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function all_list()
    {
        return $this->getList('全部订单列表', "all");
    }
    
    /**
     * 全部订单列表 (驼峰命名兼容)
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function AllList()
    {
        return $this->all_list();
    }
    
    public function edit($id){
        $line = (new Line())->getListAll([]);
        // 订单详情
        $detail = Inpack::details($id);
        $detail['total'] = $detail['free']+$detail['pack_free']+$detail['other_free'];
        $set = Setting::detail('store')['values'];
        $is_auto_free = 0;
        if($set['is_auto_free']==1){
            $is_auto_free = 1;
        }
        return $this->fetch('detail', compact('detail','line','set','is_auto_free'));
    }
    
    
    /**
     * 点击编辑集运单，修改保存的函数
     * 2022年11月5日 增加图片增删功能
    */
    public function modify_save(){
       $model = (new Inpack());
       if ($model->edit($this->postData('data'))){
             return $this->renderSuccess('操作成功','javascript:history.back(1)');
       } 
       return $this->renderError($model->getError() ?: '操作失败');
    }
    
    /**
     * 点击编辑集运单，修改保存的函数 (驼峰命名兼容)
     * 2022年11月5日 增加图片增删功能
    */
    public function ModifySave(){
       return $this->modify_save();
    }
    
    //获取订单金额和用户余额
    public function balanceAndPrice(){
       $data = $this->request->param(); 
       $model = new Inpack();
       $user =  new UserModel;
       $inpackdata = $model::details($data['id']);
       $userdata = User::detail($data['user_id']);
       $payprice = $inpackdata['free'] + $inpackdata['pack_free'] + $inpackdata['other_free'];
       return  $this->renderSuccess('操作成功','',$result=['price' =>  $payprice ,'balance' =>$userdata['balance']]);
    }
    
    // 临时测试接口：触发分销逻辑
    public function test_trigger() {
        $order_sn = $this->request->param('order_sn');
        if (empty($order_sn)) return $this->renderError('order_sn is required');

        $inpack = Inpack::where('order_sn', $order_sn)->find();
        if (!$inpack) return $this->renderError('Order not found');

        // 设置请求参数，模拟前端提交
        $this->request->withPost([
            'id' => $inpack['id'],
            'user_id' => $inpack['member_id']
        ]);

        // 直接调用 cashforprice 逻辑
        return $this->cashforprice();
    }

     //使用现金支付支付集运单费用
    public function cashforprice(){
        $data = $this->request->param();
        $model = new Inpack();
        $user =  new UserModel;
        $inpackdata = $model::details($data['id']);
        $userdata = User::detail($data['user_id']);
        
        $payprice = $inpackdata['free'] + $inpackdata['pack_free'] + $inpackdata['other_free'];
        if($payprice==0){
            return $this->renderError('订单金额为0，请先设置订单金额');
        }
        //扣除余额，并产生一天用户的消费记录；减少用户余额；
        $res = $user->logUpdate('remove',$data['user_id'],$payprice,date("Y-m-d H:i:s").',集运单'.$inpackdata['order_sn'].'使用现金支付'.$payprice.'（现金支付不改变用户余额）');
        if(!$res){
            return $this->renderError($user->getError() ?: '操作失败');
        }
        //累计消费金额
        $userdata->setIncPayMoney($payprice);
        $this->dealerData(['amount'=>$payprice,'order_id'=>$data['id']],$userdata);
        //修改集运单状态何支付状态
        // dump($rrr);die;
        if($inpackdata['status']==2){
            $inpackdata->where('id',$data['id'])->update(['real_payment'=>$payprice,'status'=>3,'is_pay'=>1,'is_pay_type'=>5,'pay_time'=>date('Y-m-d H:i:s',time())]);
        }else{
            $inpackdata->where('id',$data['id'])->update(['real_payment'=>$payprice,'is_pay'=>1,'is_pay_type'=>5,'pay_time'=>date('Y-m-d H:i:s',time())]);
        }
        
        return $this->renderSuccess('操作成功');
    }
    
    // 处理分销逻辑
     public function dealerData($data,$user){
        
        // 分销商基本设置
        $setting = SettingDealerModel::getItem('basic');
        $User = (new User());
        $dealeruser = new DealerUser();
        // 是否开启分销功能
        if (!$setting['is_open']) {
            return false;
        }
        $commission = SettingDealerModel::getItem('commission');
        // 判断用户 是否有上级
        $ReffeerModel = new RefereeModel;
        $dealerCapital = [];
        $dealerUpUser = $ReffeerModel->where(['user_id'=>$user['user_id']])->find();
        if (!$dealerUpUser){
            return false;
        }

        // --- 一级分销计算 (r1) ---
        // 基数：订单实际金额
        $firstRate = $commission['first_money']; // 默认全局配置
        $firstUserId = $dealerUpUser['dealer_id'];
        
        // 获取一级分销商等级配置
        $firstUser = UserModel::detail($firstUserId);
        // Fix: Allow 0, but fallback if empty string or null. strict check for string length if needed, or just standard isset + !== ''
        if ($firstUser && isset($firstUser['rating']['setting']['first_money']) && $firstUser['rating']['setting']['first_money'] !== '') {
             $firstRate = $firstUser['rating']['setting']['first_money'];
        }
        $firstMoney = $data['amount'] * ($firstRate / 100);

        //给用户分配余额
        $dealeruser->grantMoney($firstUserId,$firstMoney);
        RefereeModel::updateRefereeStats($firstUserId, $user['user_id'], $firstMoney);
        $dealerCapital[] = [
           'user_id' => $firstUserId,
           'flow_type' => 10,
           'money' => $firstMoney,
           'describe' => '分销收益',
           'create_time' => time(),
           'update_time' => time(),
           'wxapp_id' => $user['wxapp_id'],
        ];

        // --- 二级分销计算 (r2) ---
        // 基数：一级分销佣金
        $secondMoney = 0;
        $secondUserId = 0;
        if ($setting['level'] >= 2) {
            // 查询一级分销用户 是否存在上级
            $dealerSencondUser = $ReffeerModel->where(['user_id'=>$dealerUpUser['dealer_id']])->find();
            if ($dealerSencondUser){
                $secondRate = $commission['second_money']; // 默认全局配置
                $secondUserId = $dealerSencondUser['dealer_id'];
                
                // 获取二级分销商等级配置
                $secondUser = UserModel::detail($secondUserId);
                // Fix: Allow 0 override
                if ($secondUser && isset($secondUser['rating']['setting']['second_money']) && $secondUser['rating']['setting']['second_money'] !== '') {
                     $secondRate = $secondUser['rating']['setting']['second_money'];
                }
                
                // 级联计算：通过一级佣金计算二级佣金
                $secondMoney = $firstMoney * ($secondRate/100);
                
                $dealerCapital[] = [
                   'user_id' => $secondUserId,
                   'flow_type' => 10,
                   'money' => $secondMoney,
                   'describe' => '分销收益',
                   'create_time' => time(),
                   'update_time' => time(),
                   'wxapp_id' => $user['wxapp_id'],
                ];
                $dealeruser->grantMoney($secondUserId,$secondMoney);
                RefereeModel::updateRefereeStats($secondUserId, $user['user_id'], $secondMoney);
            }
        }

        // --- 三级分销计算 (r3) ---
        // 基数：二级分销佣金
        $thirdMoney = 0;
        $thirdUserId = 0;
        if ($setting['level'] == 3 && isset($dealerSencondUser) && $dealerSencondUser) {
            // 查询二级分销用户 是否存在上级
            $dealerthirddUser = $ReffeerModel->where(['user_id'=>$dealerSencondUser['dealer_id']])->find();
            if ($dealerthirddUser){
                $thirdRate = $commission['third_money']; // 默认全局配置
                $thirdUserId = $dealerthirddUser['dealer_id'];
                
                // 获取三级分销商等级配置
                $thirdUser = UserModel::detail($thirdUserId);
                // Fix: Allow 0 override
                if ($thirdUser && isset($thirdUser['rating']['setting']['third_money']) && $thirdUser['rating']['setting']['third_money'] !== '') {
                     $thirdRate = $thirdUser['rating']['setting']['third_money'];
                }
                
                // 级联计算：通过二级佣金计算三级佣金
                $thirdMoney = $secondMoney * ($thirdRate/100);
                
                $dealerCapital[] = [
                   'user_id' => $thirdUserId,
                   'flow_type' => 10,
                   'money' => $thirdMoney,
                   'describe' => '分销收益',
                   'create_time' => time(),
                   'update_time' => time(),
                   'wxapp_id' => $user['wxapp_id'],
                ];
                $dealeruser->grantMoney($thirdUserId,$thirdMoney);
                RefereeModel::updateRefereeStats($thirdUserId, $user['user_id'], $thirdMoney);
            }
        }
       
        // 生成分销订单
        $dealerOrder = [
            'user_id' => $user['user_id'],
            'order_id' => $data['order_id'],
            'order_price' => $data['amount'],
            'order_type' => 30,
            'first_user_id' => $firstUserId,
            'second_user_id' => $secondUserId,
            'third_user_id' => $thirdUserId,
            'first_money' => $firstMoney,
            'second_money' => $secondMoney,
            'third_money' => $thirdMoney,
            'is_invalid' => 0,
            'is_settled' => 1,
            'settle_time' => time(),
            'create_time' => time(),
            'update_time' => time(),
            'wxapp_id' => $user['wxapp_id']
        ];
        
        $resCapi = (new Capital())->allowField(true)->saveAll($dealerCapital);
        $resDeal = (new DealerOrder())->allowField(true)->save($dealerOrder);
        if(!$resCapi || !$resDeal){
            return false;
        }
        return true;
     }
    
    //使用余额抵扣集运单费用
    public function payyue(){
        $data = $this->request->param();
        $model = new Inpack();
        $user =  new UserModel;
        $inpackdata = $model::details($data['id']);
        $userdata = User::detail($data['user_id']);
        
        $payprice = $inpackdata['free'] + $inpackdata['pack_free'] + $inpackdata['other_free'];
      
        if(($userdata['balance'] < $payprice) || $payprice==0){
            return $this->renderError('用户余额不足');
        }
        //   dump($userdata['balance'] < $payprice);die;
        //扣除余额，并产生一天用户的消费记录；减少用户余额；
        $res = $user->banlanceUpdate('remove',$data['user_id'],$payprice,date("Y-m-d H:i:s").',集运单'.$inpackdata['order_sn'].'消费余额'.$payprice);
        if(!$res){
            return $this->renderError($user->getError() ?: '操作失败');
        }
        //累计消费金额
        $userdata->setIncPayMoney($payprice);
        //修改集运单状态何支付状态
        $this->dealerData(['amount'=>$payprice,'order_id'=>$data['id']],$userdata);
        if($inpackdata['status']==2){
            $inpackdata->where('id',$data['id'])->update(['real_payment'=>$payprice,'status'=>3,'is_pay'=>1,'is_pay_type'=>0,'pay_time'=>date('Y-m-d H:i:s',time())]);
        }else{
            $inpackdata->where('id',$data['id'])->update(['real_payment'=>$payprice,'is_pay'=>1,'is_pay_type'=>0,'pay_time'=>date('Y-m-d H:i:s',time())]);
        }
        return $this->renderSuccess('操作成功');
    }

    /**
     * [DEBUG] 级联分销算法验证专用接口
     * 自动重置余额，输出详细配置来源
     */
    public function debugCascadeCommission()
    {
        $order_sn = $this->request->param('order_sn', 'TEST_ORDER_001');
        $debugLog = [];
        
        // 0. 强制重置测试账户余额 (避免累加干扰)
        DealerUser::where('user_id', 'in', [20002, 20003, 20004])->update(['money' => 0, 'total_money' => 0]);
        $debugLog['reset'] = '已重置 20002/20003/20004 余额为 0';
        
        $debugLog['wxapp_id_session'] = $this->getWxappId();
        
        // 1. 查找订单
        $inpack = Inpack::where('order_sn', $order_sn)->find();
        if (!$inpack) {
            return json(['code'=>0, 'msg'=>'订单不存在: '.$order_sn]);
        }
        $debugLog['order_id'] = $inpack['id'];
        $debugLog['order_amount'] = $inpack['free'] + $inpack['pack_free'] + $inpack['other_free'];
        
        // 2. 获取用户
        $user = UserModel::detail($inpack['member_id']);
        if (!$user) {
            return json(['code'=>0, 'msg'=>'用户不存在', 'data'=>$debugLog]);
        }
        
        // 3. 获取全局佣金配置 (关键诊断)
        $commission = SettingDealerModel::getItem('commission');
        $debugLog['global_commission'] = $commission;
        
        // 4. 获取一级分销商等级配置 (关键诊断)
        $ReffeerModel = new RefereeModel;
        $dealerUpUser = $ReffeerModel->where(['user_id'=>$user['user_id']])->find();
        if (!$dealerUpUser) {
            return json(['code'=>0, 'msg'=>'无上级分销商', 'data'=>$debugLog]);
        }
        
        $firstDealerUser = UserModel::detail($dealerUpUser['dealer_id']);
        $debugLog['L1_dealer_id'] = $dealerUpUser['dealer_id'];
        $debugLog['L1_rating_id'] = $firstDealerUser['rating_id'] ?? 'null';
        $debugLog['L1_rating_setting'] = $firstDealerUser['rating']['setting'] ?? 'null';
        
        // 计算预期使用的费率
        $expectedFirstRate = $commission['first_money'];
        if ($firstDealerUser && isset($firstDealerUser['rating']['setting']['first_money']) && $firstDealerUser['rating']['setting']['first_money'] !== '') {
            $expectedFirstRate = $firstDealerUser['rating']['setting']['first_money'];
        }
        $debugLog['expected_L1_rate'] = $expectedFirstRate . '%';
        
        // 5. 执行分销计算
        $payprice = $debugLog['order_amount'];
        $result = $this->dealerData([
            'amount' => $payprice,
            'order_id' => $inpack['id']
        ], $user);

        if (!$result) {
            return json(['code'=>0, 'msg'=>'dealerData() 失败', 'data'=>$debugLog]);
        }
        
        // 6. 读取结果
        $data = [
            'debug_log' => $debugLog,
            'order_sn' => $order_sn,
            'order_amount' => $payprice,
            'expected_L1' => $payprice * ($expectedFirstRate / 100),
            'dealers' => [
                'L1 (20002)' => DealerUser::where('user_id', 20002)->field('money')->find(),
                'L2 (20003)' => DealerUser::where('user_id', 20003)->field('money')->find(),
                'L3 (20004)' => DealerUser::where('user_id', 20004)->field('money')->find(),
            ],
        ];
        
        return json(['code'=>1, 'msg'=>'验证完成', 'data'=>$data]);
    }

    /**
     * 物流更新 
     * */
    public function logistics($id){
        $sendOrder = (new Inpack())->details($id);
        if (!$this->request->isAjax()){
            return $this->fetch('send_order_logistics', compact('sendOrder'));
        }
        $order_logic = json_decode($sendOrder['logistics'],true);
        $order_logic[] = $this->postData('sendOrder')['logistics'];
        //发送用户以及用户信息
            $userId = $sendOrder['member_id'];
            $data['code'] = $id;
            $data['logistics_describe']= $this->postData('sendOrder')['logistics'];
            $user = User::detail($userId);
            if($user['email']){
                $this->sendemail($user,$data,$type=1);
            }
            //发送订阅消息，模板消息
            $data['order_sn'] = $sendOrder['order_sn'];
            $data['order'] = $sendOrder;
            $data['order']['total_free'] = $sendOrder['free'];
            $data['order']['userName'] = $user['nickName'];
            $data['order_type'] = 10;
            $data['order']['remark'] =$data['logistics_describe'] ;
            Message::send('order.payment',$data);
             $res = Logistics::addLog($sendOrder['order_sn'],$this->postData('sendOrder')['logistics'],$this->postData('sendOrder')['created_time']);
             if (!$res){
                return $this->renderError('物流更新失败');
            }
            
        return $this->renderSuccess('物流更新成功');
    }
    
    
    /**
     * 批量物流更新 
     * 2022年5月11日
     * */
    public function alllogistics(){
        $data = input();
        if(!$data['logistics_describe']){
             return $this->renderError('请输入订单物流信息');
        }
        
        $selectids = explode(',',$data['selectIds']);
        
        foreach ($selectids as $key =>$val){
            $sendOrder = (new Inpack())->details($val);
            //发送用户以及用户信息
            $userId = $sendOrder['member_id'];
            $data['code'] = $val;
            $user = User::detail($userId);
            
            //发送订阅消息，模板消息
            $data['order_sn'] = $sendOrder['order_sn'];
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
    



    /**
     * 订单详情
     * @param $order_id
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function detail($order_id)
    {
        // 订单详情
        $detail = OrderModel::detail($order_id);
        // 物流公司列表
        $expressList = ExpressModel::getAll();
        // 门店店员列表
        $shopClerkList = (new ShopClerkModel)->getList(true);
        return $this->fetch('detail', compact(
            'detail',
            'expressList',
            'shopClerkList'
        ));
    }
    
    /**
     * 生成电子面单页面
     * @param $id
     * @return mixed
     */
    public function waybill($id)
    {
        // 订单详情
        $detail = Inpack::details($id);
        
        // 获取所有快递服务标签
        $expressServices = ExpressService::getAllGrouped();
        
        return $this->fetch('waybill', compact('detail', 'expressServices'));
    }

    /**
     * 创建顺丰运单 (API对接)
     */
    public function createSfOrder()
    {
        $data = $this->postData('sf');
        if (empty($data['inpack_id']) || empty($data['express_service_id'])) {
            return $this->renderError('参数不完整');
        }

        // 1. 获取订单信息
        $order = Inpack::details($data['inpack_id']);
        if (!$order) {
            return $this->renderError('订单不存在');
        }

        // 2. 获取快递产品配置
        $expressProduct = ExpressService::get($data['express_service_id']);
        if (!$expressProduct) {
             return $this->renderError('所选快递服务无效');
        }

        // 3. 获取增值服务配置
        $vas = [];
        if (!empty($data['vas']) && is_array($data['vas'])) {
            foreach ($data['vas'] as $serviceId => $val) {
                $service = ExpressService::get($serviceId);
                if ($service && !empty($service['service_code'])) {
                     $vas[] = [
                         'code' => $service['service_code'],
                         'value' => $val 
                     ];
                }
            }
        }

        // 4. 获取收件地址
        $address = (new UserAddress())->where('address_id', $order['address_id'])->find();
        if (!$address) {
             return $this->renderError('收件地址不存在');
        }

        // 5. 组装数据
        $orderData = [
            'order_sn' => $order['order_sn'],
            'express_code' => $expressProduct['service_code'], 
            'vas' => $vas,
            'weight' => $data['weight'] ?: $order['weight'],
            'sender' => [
                'company' => '集运中心',
                'name' => '仓管员',
                'mobile' => '13800138000',
                'province' => '广东省',
                'city' => '深圳市',
                'region' => '宝安区',
                'address' => '福永街道XXX号',
            ],
            'receiver' => [
                'name' => $address['name'],
                'phone' => $address['phone'],
                'province' => $address['region']['province'],
                'city' => $address['region']['city'],
                'region' => $address['region']['region'],
                'detail' => $address['detail'],
            ]
        ];

        // 6. 调用 Service
        $sf = new \app\common\library\express\ShunfengExpress($order['wxapp_id']);
        $result = $sf->createOrder($orderData);

        if ($result['success']) {
            // 保存运单号
            $order->save([
                'express_no' => $result['waybill_no'],
                'express_company' => '顺丰速运', 
                'delivery_status' => 20, 
                'delivery_time' => time(),
                'status' => 6 
            ]);
            
            // 记录日志
            Logistics::addLog($order['order_sn'], '顺丰下单成功，运单号：' . $result['waybill_no'], date('Y-m-d H:i:s'));

            return $this->renderSuccess('下单成功', url('store/tr_order/orderdetail', ['id' => $order['id']]));
        } else {
            return $this->renderError($result['message'] . (isset($result['full_response']['apiErrorMsg']) ? ': '.$result['full_response']['apiErrorMsg'] : ''));
        }
    }

    /**
     * 打印中通面单预览
     */
    public function printZhongtong($id)
    {
        $detail = Inpack::details($id);
        if (!$detail) {
            return $this->renderError('订单不存在');
        }
        
        // 模拟生成面单数据
        // 实际开发中这里应该调用 WaybillService 或中通 API 获取面单内容
        // 目前返回一个简单的 HTML 预览
        $html = $this->generateWaybillHtml($detail, 'zhongtong');
        
        return $html;
    }

    /**
     * 打印顺丰面单预览
     */
    public function printShunfeng($id)
    {
        $detail = Inpack::details($id);
        if (!$detail) {
            return $this->renderError('订单不存在');
        }
        
        // 模拟生成面单数据
        // 实际开发中这里应该调用 WaybillService 或顺丰 API 获取面单内容
        $html = $this->generateWaybillHtml($detail, 'shunfeng');
        
        return $html;
    }

    /**
     * 生成面单 HTML (模拟)
     */
    private function generateWaybillHtml($order, $type)
    {
        $typeName = $type == 'zhongtong' ? '中通快递' : '顺丰速运';
        $now = date('Y-m-d H:i:s');
        $address = $order['address'] ? $order['address'] : [];
        $name = isset($address['name']) ? $address['name'] : '';
        $phone = isset($address['phone']) ? $address['phone'] : '';
        $detailAddr = isset($address['detail']) ? $address['detail'] : '';
        $province = isset($address['province']) ? $address['province'] : '';
        $city = isset($address['city']) ? $address['city'] : '';
        $region = isset($address['region']) ? $address['region'] : '';

        $html = <<<HTML
<div style="width: 370px; height: 500px; border: 1px solid #000; padding: 10px; font-family: SimHei; position: relative;">
    <div style="text-align: center; font-size: 24px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 10px;">
        {$typeName}
    </div>
    <div style="margin-top: 10px; font-size: 14px;">
        <div><strong>订单号:</strong> {$order['order_sn']}</div>
        <div><strong>打印时间:</strong> {$now}</div>
    </div>
    <div style="border: 1px solid #000; margin-top: 10px; padding: 10px;">
        <div style="font-size: 16px; font-weight: bold;">收</div>
        <div><strong>姓名:</strong> {$name}</div>
        <div><strong>电话:</strong> {$phone}</div>
        <div><strong>地址:</strong> {$detailAddr}</div>
        <div>{$province} {$city} {$region}</div>
    </div>
    <div style="border: 1px solid #000; margin-top: 10px; padding: 10px;">
        <div style="font-size: 16px; font-weight: bold;">寄</div>
        <div><strong>寄件人:</strong> 集运中心</div>
    </div>
    <div style="margin-top: 20px; text-align: center;">
        <div style="font-size: 30px; font-weight: bold;">123-456-789</div>
        <div>(此处为条码区域)</div>
    </div>
</div>
HTML;
        return $html;
    }

    // 发货物流
    public function delivery($id){
        $detail = Inpack::details($id);
        $ExpressModel = new ExpressModel();
        $DitchModel = new DitchModel();
        $track = $ExpressModel->getTypeList($type = 1);
        $ditchlist = $DitchModel->getAll();
        return $this->fetch('delivery', compact(
            'detail','track','ditchlist'
        ));
    }
    
    // 发货物流
    public function changesn($id){
        $detail = Inpack::details($id);
        $ExpressModel = new ExpressModel();
        $DitchModel = new DitchModel();
        $track = $ExpressModel->getTypeList($type = 1);
        $ditchlist = $DitchModel->getAll();
        return $this->fetch('changesn', compact(
            'detail','track','ditchlist'
        ));
    }
    
    // 打印面单
    public function printOrder($id){
        $detail = Inpack::details($id);
        return $this->fetch('orderPrint', compact(
            'detail'
        ));
    }
    
    // 发货物流
    public function deliverySave(){
       // 调试：记录提交的数据
       $delivery_data = $this->postData('delivery');
       $log_file = __DIR__ . '/../../../../debug_transfer_log.txt';
       file_put_contents($log_file, 
           "=== 转单请求 " . date('Y-m-d H:i:s') . " ===\n" .
           "提交的数据:\n" . print_r($delivery_data, true) . "\n" .
           "POST原始数据:\n" . print_r($_POST, true) . "\n\n",
           FILE_APPEND
       );
       
       $model = (new Inpack());
       if ($model->modify($delivery_data)){
           return $this->renderSuccess('操作成功');
       } 
       return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 修改订单价格
     * @param $order_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function updatePrice($order_id)
    {
        $model = OrderModel::detail($order_id);
        if ($model->updatePrice($this->postData('order'))) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }
    
    
    //问题件删除
    public function orderdelete($id){
        // 直接查询，不使用 details() 避免加载不必要的字段
        $model = Inpack::get($id);
        if (!$model) {
            return json(['code' => 0, 'msg' => '订单不存在']);
        }
        
        $package_ids = $model['pack_ids'];
        $pack = explode(',',$package_ids);
        if(!empty($pack)){
          foreach ($pack as $key => $val){
            (new Package())->where('id',$val)->update(['status' => 2,'inpack_id'=>null]);
          }  
        }
        
        // 直接更新数据库，不使用 save()
        $result = Inpack::where('id', $id)->update(['is_delete' => 1]);
        if ($result !== false) {
            return json(['code' => 1, 'msg' => '删除成功']);
        }
        return json(['code' => 0, 'msg' => '删除失败']);
    }
    
     //集运单删除
    public function delete($id){
        // 直接查询，不使用 details() 避免加载不必要的字段
        $model = Inpack::get($id);
        if (!$model) {
            return json(['code' => 0, 'msg' => '订单不存在']);
        }
        
        //找到集运单所有的包裹单号，循环设置状态为2；
        $package_ids = $model['pack_ids'];
        $pack = explode(',',$package_ids);
        foreach ($pack as $key => $val){
            (new Package())->where('id',$val)->update(['status' => 2]);
        }
        
        // 直接更新数据库，不使用 save()
        $result = Inpack::where('id', $id)->update(['is_delete' => 1]);
        if ($result !== false) {
            return json(['code' => 1, 'msg' => '删除成功']);
        }
        return json(['code' => 0, 'msg' => '删除失败']);
    }

    /**
     * 订单列表
     * @param string $title
     * @param string $dataType
     * @return mixed
     * @throws \think\exception\DbException
     */
    private function getList($title, $dataType)
    {
        // 订单列表
        $model = new Inpack;
        $Line = new Line;
        $Clerk = new Clerk;
        $set = Setting::detail('store')['values'];
        $userclient =  Setting::detail('userclient')['values'];
        $list = $model->getList($dataType, $this->request->param());
        // dump($list->toArray());die;
        $servicelist = $Clerk->where('FIND_IN_SET(:ids,clerk_type)', ['ids' => 7])->select();
        $pintuanlist = (new SharingOrder())->getList([]);
        $batchlist = (new Batch())->getAllwaitList([]);
        $shopList = ShopModel::getAllList();
        $lineList = $Line->getListAll();
        foreach ($list as &$value) {
            $value['num'] = !empty($value['pack_ids'])?count(explode(',',$value['pack_ids'])):0;
            $value['down_shelf'] = 0;
            $value['inpack'] = 0;
           if ($dataType=='payed'){
                $value['down_shelf'] = (new Package())->where('id','in',explode(',',$value['pack_ids']))->where('status',7)->count();
                $value['inpack'] = (new Package())->where('id','in',explode(',',$value['pack_ids']))->where('status',8)->count();
           }
        }

        return $this->fetch('index', compact('list','dataType','set','pintuanlist','shopList','lineList','servicelist','userclient','batchlist'));
    }
    
    
    /**
     * 订单列表
     * @param string $title
     * @param string $dataType
     * @return mixed
     * @throws \think\exception\DbException
     */
    private function getNoPayList($title, $dataType)
    {
        // 订单列表
        $model = new Inpack;
        $set = Setting::detail('store')['values'];
        $list = $model->getNoPayList($dataType, $this->request->param());
        foreach ($list as &$value) {
            $value['num'] = !empty($value['pack_ids'])?count(explode(',',$value['pack_ids'])):0;
            $value['down_shelf'] = 0;
            $value['inpack'] = 0;
           if ($dataType=='payed'){
                $value['down_shelf'] = (new Package())->where('id','in',explode(',',$value['pack_ids']))->where('status',7)->count();
                $value['inpack'] = (new Package())->where('id','in',explode(',',$value['pack_ids']))->where('status',8)->count();
           }
        }

        return $this->fetch('index', compact('list','dataType','set'));
    }
    
    
    /***
     * 从集运单剔除包裹
     * 剔除的包裹恢复到待打包状态
     * 2022年5月8日  重构
     */
    public function delete_package(){
          $model = new Inpack();
          //单个移出集运单
          if(input('value') && input('id')){
              $detail = $model->find(input('value'));
              $package_ids = explode(',',$detail['pack_ids']);
              $key = array_search(input('id'),$package_ids);
              if ($key!==false){
                  unset($package_ids[$key]);
              }
              $update['status'] = 2;
              $update['inpack_id'] = null;
             (new Package())->where('id',input('id'))->update($update);
              $inpackUpdate['pack_ids'] = implode(',',$package_ids);
              $res = $model->where(['id'=>input('value')])->update($inpackUpdate);
          
              if ($res){
                   return $this->renderSuccess('修改成功');
              }
              return $this->renderError($model->getError() ?: '修改失败');
          }
          
          
          //批量移出集运单
          $ids= input("post.selectId/a");  //需要去除的包裹id；
          $item =input("post.selectItem"); // 集运单编号
          $detail = $model->find($item);
          $package_ids = explode(',',$detail['pack_ids']);
          //移除数组
 
          $package_ids = array_diff($package_ids,$ids);
          //循环更新包裹状态为已入库，可打包状态
          $update['status'] = 2;
          $update['inpack_id'] = null;
          foreach($ids as $key => $val){
             (new Package())->where('id',$val)->update($update);    
          } 
          //更新集运单的包裹

          $inpackUpdate['pack_ids'] = implode(',',$package_ids);
          $res = $model->where(['id'=>$item])->update($inpackUpdate);
          
          if ($res){
               return $this->renderSuccess('修改成功');
          }
          return $this->renderError($model->getError() ?: '修改失败');
    }
    
    // 添加快递进入集运单 
    public function add(){
        $order_id = $this->getData('id');
        $Inpack = new Inpack();
        $model = $Inpack::details($order_id);

        if (!$this->request->isAjax()){
            return $this->fetch('appendchild', compact('model'));
        }
    
        if ($Inpack->appendData($this->postData('delivery'))) {
            return $this->renderSuccess('修改成功','javascript:history.back(1)');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }
    
    // 计算价格
    public function caleamount(){
        $data = $this->postData();
        $line_id = $data['line_id'];
        $pakdata = Inpack::details($data['pid']);
        $line = (new Line())->find($line_id);
        if (!$line){
            return $this->renderError('线路不存在,请重新选择');
        }

        $free_rule = json_decode($line['free_rule'],true);
        $price = 0; // 总运费
        $allWeigth = 0;
        $caleWeigth = 0;
        $volumn = 0;
        $setting = SettingModel::getItem('store',$pakdata['wxapp_id']);
        
        if($setting['is_discount']==1){
            $UserLine =  (new UserLine());
            $linedata= $UserLine->where('user_id',$pakdata['member_id'])->where('line_id',$line['id'])->find();
                if($linedata){
                   $value['discount']  = $linedata['discount'];
                }else{
                   $value['discount'] =1;
                }
                //会员等级折扣
                $suer  = User::detail($pakdata['member_id']);
                // dump($suer->toArray());die;
                if(!empty($suer['grade']) && $suer['grade']['status']==1 && $suer['grade']['equity']['discount']>0){
                    $value['discount'] = $suer['grade']['equity']['discount'] * 0.1;
                }
        }else{
            $value['discount'] =1;
        }
          
            
        // 计算体检重
        $weigthV = round(($data['length']*$data['width']*$data['height'])/$line['volumeweight'],2);
        if(!empty($data['length']) && !empty($data['width']) && !empty($data['height']) && $line['volumeweight_type']==20){
            $weigthV = round(($data['weight'] + (($data['length']*$data['width']*$data['height'])/$line['volumeweight'] - $data['weight'])*$line['bubble_weight']/100),2);
        }
        // 取两者中 较重者 
        $oWeigth = $weigthV>$data['weight']?$weigthV:$data['weight'];
        if($line['line_type']==1){
            $oWeigth = round(($data['length']*$data['width']*$data['height'])/$line['volumeweight'],2);
        }
        //关税和增值服务费用
        // $otherfree = $line['service_route'];
        $long = max($data['length'],$data['width'],$data['height']);
        $otherfree = getServiceFree($line['services_require'],$oWeigth,$long);
        // dump($otherfree);die;
        $reprice=0;  
        $lines['predict'] = [
              'weight' => $oWeigth,
              'price' => '包裹重量超限',
              'service'=>0,
           ]; 
        switch ($line['free_mode']) {
            case '1':
               $free_rule = json_decode($line['free_rule'],true);
               $size = sizeof($free_rule);    
               if(($oWeigth>= $free_rule[0]['weight'][0]) && ($oWeigth<= $free_rule[$size-1]['weight'][1])){
   
                  foreach ($free_rule as $k => $v) {
                      if ($oWeigth>$v['weight'][1]){
                            $reprice += ($v['weight'][1] - $v['weight'][0])*$v['weight_price'];
                            continue;
                      }else{
                           $reprice += ($oWeigth - $v['weight'][0])*$v['weight_price'];
                           break;
                      }
                  }
                  $lines['predict'] = [
                    'weight' => $oWeigth,
                    'price' => ($reprice+ $free_rule[0]['weight_price']*$free_rule[0]['weight'][0]+$otherfree)*$value['discount'],
                    'rule' => $free_rule,
                    'service' =>0,
                  ];         
               }else{
                    break;
               }
               break;
            case '2':
                //首重价格+续重价格*（总重-首重）
               $free_rule = json_decode($line['free_rule'],true);
               foreach ($free_rule as $k => $v) {
                          $lines['predict'] = [
                              'weight' => $oWeigth,
                              'price' => ($v['first_price']+ ceil((($oWeigth-$v['first_weight'])/$v['next_weight']))*$v['next_price'] + $otherfree)*$value['discount'],
                              'rule' => $v,
                              'service' =>0,
                          ];   
               }
          
                break;
            case '3':
                $free_rule = json_decode($line['free_rule'],true);
               foreach ($free_rule as $k => $v) {
                   if ($oWeigth >= $v['weight'][0]){
                      if (isset($v['weight'][1]) && $oWeigth<=$v['weight'][1]){
                          $lines['predict'] = [
                              'weight' => $oWeigth,
                              'price' => ($v['weight_price'] + $otherfree)*$value['discount'],
                              'rule' => $v,
                              'service' =>0,
                          ];   
                      }
                   }
                  
               }

               break;
               
            case '4':
                $free_rule = json_decode($line['free_rule'],true);
                // dump($value['free_rule']);
               foreach ($free_rule as $k => $v) {
                   if ($oWeigth >= $v['weight'][0]){
                      if (isset($v['weight'][1]) && $oWeigth<=$v['weight'][1]){
                          !isset($v['weight_unit']) && $v['weight_unit']=1;
                          $lines['predict'] = [
                              'weight' => $oWeigth,
                              'price' => (floatval($v['weight_price']) * ceil(floatval($oWeigth)/floatval($v['weight_unit'])) + floatval($otherfree))*$value['discount'],
                              'rule' => $v,
                              'service' =>0,
                          ]; 
            
                      }
                   }
               }
               
               break;
        }
        $PackageService = new PackageService(); 
        $pricethree = 0;
        $pricetwo = $lines['predict']['price'];
        if(count($pakdata['inpackservice'])>0){
          $servicelist = $pakdata['inpackservice'];
          foreach ($servicelist as $val){
              $servicedetail = $PackageService::detail($val['service_id']);
            //   dump($servicedetail);die;
              if($servicedetail['type']==0){
                  $lines['predict']['service'] = $lines['predict']['service'] + $servicedetail['price'];
                  $pricethree = floatval($pricethree) + floatval($servicedetail['price']);
              }
              if($servicedetail['type']==1){
                  $lines['predict']['service'] = floatval($pricetwo)*floatval($servicedetail['percentage'])/100 + floatval($lines['predict']['service']);
                  $pricethree = floatval($pricetwo)* floatval($servicedetail['percentage'])/100 + floatval($pricethree);
              }
          }
        }
           
        $lines['predict']['price'] = number_format(floatval($lines['predict']['price']),2);
        $settingdata  = SettingModel::getItem('store',$line['wxapp_id']);
        //不需要主动更新费用
        if($settingdata['is_auto_free']==0){
           $lines['predict']['price'] = 0;
        }
        
        //
        
         
        return $this->renderSuccess(['oWeigth'=>$oWeigth,'price'=>str_replace(',','',$lines['predict']['price']),'weightV'=>$weigthV,'packfree'=>$pricethree]);
    }
    
    /**
     * 评价列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function comment()
    {
        $model = new CommentModel;
        $list = $model->getList();
        // dump($list->toArray());die;
        foreach ($list as $k =>$v){
              $list[$k]["score"] = json_decode($v['score'],true);
        }
        return $this->fetch('tr_order/comment', compact('list'));
    }
    
        /**
     * 评价详情
     * @param $comment_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function commentdetail($comment_id)
    {
        // 评价详情

        $model = CommentModel::detail($comment_id);
        $model['score']=json_decode($model['score'],true);
        // dump($model->toArray());die;
        if (!$this->request->isAjax()) {
            return $this->fetch('comment_detail', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('comment'))) {
            return $this->renderSuccess('更新成功', url('tr_order/comment'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
    /**
     * 合并订单
     * @return mixed
     * @throws \think\exception\DbException
     */
     
     public function hedan()
    {
       $model = new Inpack();
       $Package = new Package;
       $ids= input();
       $ids = array_keys($ids);
       $idsArr = explode(',',$ids[0]);
       $arruser = [];
       $packids = null;

       //判断所有包裹是否同一用户
      foreach($idsArr as $key =>$val ){
           $pack = $model->where('id',$val)->find();
           $arruser[] = $pack['member_id'];
           $packids = $packids.",".$pack['pack_ids'];
      }
     
      if(count(array_unique($arruser))>1){
          return $this->renderError('请选择相同用户的集运单');
      }
       //将包裹的packids合并在一个集运单中，并将另外一个集运单状态设置为isdelete；
       //合并包裹思路一：将其他集运单状态改为删除，将快递单id添加到第一个集运单中；
       //合并包裹思路二：新创建新的集运单，之前的集运单全部改为删除状态；此方案可用于创建多用户拼邮；
       $packids = explode(',',$packids,2)[1];
      
        //思路 随意找到集运单的一个基本信息，去除id即可使用基础数据，创建新的order_sn即可
          foreach($idsArr as $key =>$val ){
                  $res = $model->where('id',$val)->update(['is_delete' => 1 ]);
                  if(!$res){
                    return $this->renderError('合并失败');
                  }
            }     
         
          $newpack = $model->find($idsArr[0])->toArray();
          
          $newpack['pack_ids'] = $packids;
          unset($newpack['id']);
        //   $newpack['order_sn'] = createSn();
          $newpack['updated_time'] = getTime();
          $newpack['created_time'] = getTime();
          $newpack['is_delete'] = 0;
          $newpack['is_pay_type'] = $newpack['is_pay_type']['value'];
          $newpack['pay_type'] = $newpack['pay_type']['value'];
          $result = $model->insertGetId($newpack);
          if (!$result){
              return $this->renderSuccess('合并失败');
          }
          $packidss = explode(',',$packids);
          foreach ($packidss as $va){
             $Package->where('id',$va)->update(['inpack_id'=>$result]); 
          }
       //返回成功状态并提示合并成功；
       return $this->renderSuccess('合并成功');
    }

    /**
     * 拆包合包
     * 将拆出的包裹合并成新的集运单
     * @return mixed
     * @throws \think\exception\DbException
     */
     
     public function packageinout()
    {
         $model = new Inpack();
        //批量移出集运单
          $ids= input("post.selectId/a");  //需要移出的包裹id；
          $item =input("post.selectItem"); // 集运单编号
          $detail = $model->find($item);
          $package_ids = explode(',',$detail['pack_ids']);
          //移除数组
          $package_ids = array_diff($package_ids,$ids);
          //更新集运单的包裹
          $inpackUpdate['pack_ids'] = implode(',',$package_ids);
          $res = $model->where(['id'=>$item])->update($inpackUpdate);
          
          if (!$res){
              return $this->renderSuccess('拆包失败');
          }
          //将选中的包裹单号合并为packs_id需要的数据类型
          $newpack = $detail;
          $newpack['pack_ids'] = implode(',',$ids); 
          unset($newpack['id']);
          $newpack['order_sn'] = createSn();
        //   dump($detail->toArray());die;
          $result = $model->insert($newpack->toArray());
        //   dump($model->getLastsql());die;
          if ($result){
              return $this->renderSuccess('拆包合包成功');
          }
          return $this->renderError($model->getError() ?: '拆包合包失败');
    }
    
    
    /**
     * 加入拼团订单
     * @return mixed
     * @throws \think\exception\DbException
     */
     
    public function pintuan()
    {
       $model = new Inpack();
       $pintuan_id= input('pintuan_id');
       $selectIds = input('selectIds');
       $idsArray = explode(',',$selectIds);
       if(empty($pintuan_id)){
           return $this->renderError($model->getError() ?: '请选择拼团订单');
       }
       $SharingOrderItem = new SharingOrderItem();
       $res= $SharingOrderItem->insertInpack($idsArray,$pintuan_id);
        if(!$res){
            return $this->renderError($SharingOrderItem->getError() ?: '添加失败');
        }
        return $this->renderSuccess('添加成功');
    }
    
    
    // 打印面单
    public function expressBill(){
       $id = $this->request->param('id');    
       $inpack = (new Inpack());
       $data = $inpack::details($id);
       if(!$data['t_order_sn']){
           return $this->renderError('转运单号为空');
       }

       $data['setting'] = Setting::getItem('store',$data['wxapp_id']);
       $generatorSVG = new \Picqer\Barcode\BarcodeGeneratorSVG(); #创建SVG类型条形码
       $data['barcode'] = $generatorSVG->getBarcode($data['t_order_sn'], $generatorSVG::TYPE_CODE_128,$widthFactor = 1.5, $totalHeight = 40);
       echo $this->fill($data);
    }
    
     // 打印标签
    public function expressLabel(){
       $id = $this->request->param('id');    
       $inpack = (new Inpack());
       $data = $inpack->getExpressData($id);
       if(!$data['order_sn']){
           return $this->renderError('转运单号为空');
       }
    //   dump($data);die;
       $data['setting'] = Setting::getItem('store',$data['wxapp_id']);
 
       $generatorSVG = new \Picqer\Barcode\BarcodeGeneratorSVG(); #创建SVG类型条形码
       $data['barcode'] = $generatorSVG->getBarcode($data['order_sn'], $generatorSVG::TYPE_CODE_128,$widthFactor =1.5, $totalHeight = 40);
       echo $this->label($data);
    }
    
         // 打印账单
    public function freelistLabel(){
       $id = $this->request->param('id');    
       $inpack = (new Inpack());
       $generatorSVG = new \Picqer\Barcode\BarcodeGeneratorJPG(); #创建SVG类型条形码
       $data = $inpack->getExpressData($id);
       $data['setting'] = Setting::getItem('store',$data['wxapp_id']);
       $data['name'] = '';
       if(!empty($data['member_id'])){
           $member  = UserModel::detail($data['member_id']);
           $data['name'] = $member['nickName'];
           if($data['setting']['usercode_mode']['is_show']!=0){
              $data['member_id'] = $member['user_code'];
           }
       }
       if($data['status']==7){
           $data['receipt_time'] = $data['shoprk_time'];
       }
       if($data['status']<7){
           $data['receipt_time'] = $data['created_time'];
       }
       $data['total_free'] = $data['free']+$data['other_free']+$data['pack_free'];
       $data['barcode'] = base64_encode($generatorSVG->getBarcode($data['order_sn'], $generatorSVG::TYPE_CODE_128,$widthFactor =1.8, $totalHeight = 50));
       echo $this->free($data);
    }
    
   
       public function free($data){
    return  $html = '<style>
	* {
		margin: 0;
		padding: 0
	}

	table {
		margin-top: -1px;
		font: 12px "Microsoft YaHei", Verdana, arial, sans-serif;
		border-collapse: collapse
	}

	table.container {
    	margin-top:10px;
		width: 400px;
		height:600px;
		border: 2px solid #000;
		border-bottom: 0;
		margin:10px;
	}

	table td.center {
		text-align: center
	}

	table td{
		border: 1px solid #000
	}
	.font_xxl {
		font-size: 18px;
		font-weight: bold
	}
	.font_xl {
		font-size: 14px;
		font-weight: bold
	}
	
	.paddingleft{
	    padding-left:10px;
	}

	.font_xxxl {
		font-size: 32px;
		font-weight: bold
	}
	.font_12{font-size: 12px;font-weight: bold;}
</style>
<table class="container">
	<tr>
		<td width="152" height="26" class="pl center font_xxl">
		    '.$data['setting']['name'].'
		</td>
		<td width="240" class="center font_xxl">
		    '.$data['receipt_time'].'
		</td>
	</tr>
	<tr>
		<td width="152" height="36" class="pl center font_xl">
		    签收人Receiver
		</td>
		<td width="240" class="center font_xl">
		    '.$data['name'].'('.$data['member_id'].')'.'
		</td>
	</tr>
	<tr>
		<td width="152" height="36" class="pl center font_xl">
		    客户单号Tracking No
		</td>
		<td width="240" class="center font_xl">
		    '.$data['order_sn'].'
		</td>
	</tr>
	<tr>
		<td class="center" colspan=2 height="120">
		      <img style="width:250px;" src="data:image/png;base64,'. $data['barcode'] .'"/><br>
		      '.$data['order_sn'].'
		</td>
	</tr>
	<tr>
		<td width="152" height="36" class="pl center font_xl">
		    重量Weight
		</td>
		<td width="240" class="center font_xl">
		     '.$data['cale_weight'].'
		</td>
	</tr>
	<tr>
		<td width="152" height="36" class="pl center font_xl">
		    金额Payment
		</td>
		<td width="240" class="center font_xl">
		     '.$data['total_free'].'
		</td>
	</tr>
	<tr>
 		<td colspan=2 class="paddingleft left font_xl" height="36">
 		   '.$data['address']['name']. $data['address']['phone'].'<br>
 		   '.$data['address']['country'].'
 					'.(is_array($data['address']['province'])?'':$data['address']['province']).'
 					'.(is_array($data['address']['city'])?'':$data['address']['city']).'
 					'.(is_array($data['address']['region'])?'':$data['address']['region']).'
 					'.(is_array($data['address']['district'])?'':$data['address']['district']).'
 					'.(is_array($data['address']['street'])?'':$data['address']['street']).'
 					'.(is_array($data['address']['door'])?'':$data['address']['door']).'
 				<strong>'.$data['address']['detail'].'</strong>
 					'.(is_array($data['address']['code'])?'':$data['address']['code']).'
 		</td>
 	</tr>
</table>';
} 
    
    
        // 渲染标签生成网页数据
    public function label($data){
      return  $html = '<style>
	* {
		margin: 0;
		padding: 0
	}

	table {
		margin-top: -1px;
		font: 12px "Microsoft YaHei", Verdana, arial, sans-serif;
		border-collapse: collapse
	}

	table.container {
		width: 375px;
		border: 1px solid #000;
		border-bottom: 0
	}

	table td {
		border-top: 1px solid #000;
		border-bottom: 1px solid #000
	}

	table.nob {
		width: 100%
	}

	table.nob td {
		border: 0
	}

	table td.center {
		text-align: center
	}

	table td.right {
		text-align: right
	}

	table td.pl {
		padding-left: 5px
	}

	table td.br {
		border-right: 1px solid #000
	}

	table.nobt,
	table td.nobt {
		border-top: 0
	}

	table.nobb,
	table td.nobb {
		border-bottom: 0
	}

	.font_s {
		font-size: 10px;
		-webkit-transform: scale(0.84, 0.84);
		*font-size: 10px
	}

	.font_m {
		font-size: 16px
	}

	.font_l {
		font-size: 16px;
		font-weight: bold
	}

	.font_xl {
		font-size: 18px;
		font-weight: bold
	}

	.font_xxl {
		font-size: 28px;
		font-weight: bold
	}

	.font_xxxl {
		font-size: 32px;
		font-weight: bold
	}
	.country{
    	font-size: 37px;
        padding: 0px;
        margin: 0px;
        font-weight: bold;
        width: 100px;
	}
	.barcode{text-align:center;}
	.font_12{font-size: 12px;font-weight: bold;}
</style>
<table class="container">
	<tr>
		<td width="152" height="76" class="pl center font_xxl">
		    <table class="nob">
		        <tr>
		            <td class="font_xxl ">'.$data['setting']['name'].'</td>
		        </tr>
		        <tr>
		            <td>'.$data['setting']['desc'].'</td>
		        </tr>
		    </table>
		</td>
		<td width="240" class="center">
			<table class="nob">
				<tr>
					<td class="barcode">'.$data['barcode'].'</td>
					
				</tr>
				<tr>
					<td class="center font_12">'.$data['order_sn'].'</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td class="font_xxxl pl">
		   目的地： '.$data['country'].'
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td>
			<table class="nob">
				<tr>
					<td class="pl" width="65" height="24">件数：</td>
					<td width="60">1</td>
					<td width="80">重：</td>
					<td>'.$data['cale_weight'].'</td>
				</tr>
				<tr>
					<td class="pl" height="50" valign="top">配货信息：</td>
					<td colspan="3"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';
    }
    
    // 渲染面单生成网页数据
    public function fill($data){
      return  $html = '<style>
	* {
		margin: 0;
		padding: 0
	}

	table {
		margin-top: -1px;
		font: 12px "Microsoft YaHei", Verdana, arial, sans-serif;
		border-collapse: collapse
	}

	table.container {
		width: 375px;
		border: 1px solid #000;
		border-bottom: 0
	}

	table td {
		border-top: 1px solid #000;
		border-bottom: 1px solid #000
	}

	table.nob {
		width: 100%
	}

	table.nob td {
		border: 0
	}

	table td.center {
		text-align: center
	}

	table td.right {
		text-align: right
	}

	table td.pl {
		padding-left: 5px
	}

	table td.br {
		border-right: 1px solid #000
	}

	table.nobt,
	table td.nobt {
		border-top: 0
	}

	table.nobb,
	table td.nobb {
		border-bottom: 0
	}

	.font_s {
		font-size: 10px;
		-webkit-transform: scale(0.84, 0.84);
		*font-size: 10px
	}

	.font_m {
		font-size: 16px
	}

	.font_l {
		font-size: 16px;
		font-weight: bold
	}

	.font_xl {
		font-size: 18px;
		font-weight: bold
	}

	.font_xxl {
		font-size: 28px;
		font-weight: bold
	}

	.font_xxxl {
		font-size: 32px;
		font-weight: bold
	}
	.country{
    	font-size: 37px;
        padding: 0px;
        margin: 0px;
        font-weight: bold;
        width: 100px;
	}
	.barcode{text-align:center;}
</style>
<table class="container">
	<tr>
		<td width="140" height="76" class="pl center font_xxl">'.$data['setting']['name'].'</td>
		<td width="252" class="center">
			<table class="nob">
				<tr>
					<td>'.$data['barcode'].'
					</td>
				</tr>
				<tr>
					<td class="center font_l">'.$data['t_order_sn'].'</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td height="56">
			<table class="nob">
				<tr>
					<td class="pl" height="28">寄件：</td>
					<td>'. $data['storage']['linkman'].'
						'.$data['storage']['phone'].'('.$data['storage']['shop_name'].')</td>
				</tr>
				<tr>
					<td></td>
					<td>'.$data['storage']['address'].'</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container nobb">
	<tr>
		<td height="66" class="nobb">
			<table class="nob">
				<tr>
					<td class="pl" height="28">收件：</td>
					<td><strong>'.$data['address']['name'].  '+'.$data['address']['tel_code'].'  '.$data['address']['phone'].'</strong></td>
				</tr>
				<tr>
					<td height="38" class="country">CN</td>
					<td valign="top"><strong>
					'.$data['address']['country'].'
					'.$data['address']['province'].'
					'.$data['address']['city'].'
 					'.(is_array($data['address']['region'])?'':$data['address']['region']).'
					'.$data['address']['district'].'
					'.$data['address']['street'].'
					'.$data['address']['door'].'
					'.$data['address']['detail'].'</strong>
					'.$data['address']['code'].'
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container nobt">
	<tr>
		<td class="nobt">
			<table class="nob">
				<tr>
					<td class="pl" width="110" height="24">付款方式：</td>
					<td width="60">寄付</td>
					<td width="100">收件人/代签人：</td>
					<td></td>
				</tr>
				<tr>
					<td class="pl" height="24">计费重量（KG）：</td>
					<td>'.$data['cale_weight'].'</td>
					<td>签收时间：</td>
					<td>年&emsp;月&emsp;日</td>
				</tr>
				<tr>
					<td class="pl">运费金额（元）：</td>
					<td>'.$data['real_payment'].'</td>
					<td colspan="2" class="font_s">快件送达收件人地址，经收件人或收件人允许的代收人签字视为送达。</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td>
			<table class="nob">
				<tr>
					<td class="pl" width="65" height="24">件数：</td>
					<td width="60">1</td>
					<td width="80">重：</td>
					<td>'.$data['cale_weight'].'</td>
				</tr>
				<tr>
					<td class="pl" height="50" valign="top">配货信息：</td>
					<td colspan="3"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td class="center" height="65">
			<table class="nob">
				<tr>
					<td class="barcode">'.$data['barcode'].'</td>
				</tr>
				<tr>
					<td class="center font_l">'.$data['t_order_sn'].'</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td width="187" height="65" class="br">
			<table class="nob">
				<tr>
					<td class="pl">寄件：</td>
					<td>'.$data['storage']['linkman'].$data['storage']['phone'].$data['storage']['address'].'</td>
				</tr>
			</table>
		</td>
		<td>
			<table class="nob">
				<tr>
					<td class="pl">收件：</td>
					<td>'.$data['address']['name'].' 
					'.$data['address']['phone'].'</br>
					'.$data['address']['country'].'
					'.$data['address']['detail'].'
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td width="200" height="80">
			<table class="nob">
				<tr>
					<td class="pl">备注：</td>
				</tr>
				<tr>
					<td class="pl font_m font_s">'.$data['remark'].'</td>
				</tr>
			</table>
		</td>
		<td class="center">
			<table class="nob">
				<tr>
					<td class="font_xxxl">'.substr($data['address']['phone'],-4).'</td>
				</tr>
				<tr>
					<td class="">-手机尾号-</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table class="container">
	<tr>
		<td height="30" class="pl">网址：'.$_SERVER["SERVER_NAME"].'</td>
		<td>客服电话：'.$data['setting']['service_phone'].'</td>
	</tr>
</table>
';
    }
   
    // 批量打印面单 [生成pdf]
    public function expressBillbatch(){
        $selectIds = $this->postData("selectIds");
        $inpack = (new Inpack());    
        $data = $inpack->getExpressBatchData($selectIds);
        $html = '';
        foreach($data as $v){
           $html.= $this->fill($v); 
           $html.="</hr>";
        }
        file_put_contents('expressBill.html', charsetToUTF8($html));
    }
    
    
    // 修改用户地址
    public function updateAddress(){
        $selectIds = $this->postData();
        $inpack = (new Inpack()); 
        if(!$selectIds['id'] || !$selectIds['address_id']){
            return $this->renderError('修改失败');
            
        }
        $result = $inpack->where('id',$selectIds['id'])->update(['address_id'=>$selectIds['address_id']]);
        return $this->renderSuccess('修改成功');
    }

    /**包裹导出功能**/
    //导出成excel文档
     public function loaddingOutExcel(){
         //引入excel插件
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        //获取需要导出的数据列表
        $ids= input("post.selectId/a");
        $seach= input("post.seach/a");
        //1 待入库 2 已入库 3 已分拣上架  4 待打包  5 待支付  6 已支付 7 已分拣下架  8 已打包  9 已发货 10 已收货 11 已完成
        $map =[-1=>'问题件',1=>'待入库',2=>'已入库',3=>'已分拣上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成'];
        $status = [1=>'待查验',2=>'待支付',3=>'已支付','4'=>'已拣货','5'=>'已打包','6'=>'已发货','7'=>'已收货','8'=>'已完成','-1'=>'已取消'];
       
        if($ids){
           $data = (new Inpack())->whereIn('id',$ids)->select()->each(function ($item, $key) use($map){
                    $item["user"] = (new UserModel())->where('user_id',$item['member_id'])->field('user_id,nickName,mobile')->find();
                    $item['t_name'] = (new Line())->where('id',$item['line_id'])->value('name');
                    
                    //集运单包裹中的物品分类和价格
                    $packdata = explode(",",$item['pack_ids']);
                    $packClass = [];
                    $packprice = 0;
             
                    foreach($packdata as $key => $vale){
                        $expressnum = (new Package())->where('id',$vale)->find();
                        $packitem = (new PackageItem())->where('express_num',$expressnum['express_num'])->select();
                        $packClass[$key]="";
                        $packprice=0;
                        if(count($packitem)>0){
                            $packClass[$key] = $packitem[0]['class_name'];
                            $packprice += $packitem[0]['all_price'];
                        } 
                    }
                    
                    $item['packClass'] = implode($packClass);
                    $item['packprice'] = $packprice;
                    // dump($packClass);die;
                    //折扣信息
                    
                    $discountData = (new UserLine())->where(['user_id'=>$item["user"]['user_id'],'line_id'=>$item['line_id']])->find();
                    if($discountData){
                        $item['discount'] = $discountData['discount'];
                    }else{
                        $item['discount'] = 1;
                    }
                                   
                    $item['discount_price'] = $item['discount'] * $item['free'];
               
                    $item['status_text'] = $map[$item['status']];
                    $item['address'] =(new UserAddress())->where('address_id',$item['address_id'])->find();
                    return $item;
                }); 
        }else{
            if(!empty($seach['search'])){
                 $where['member_id'] = $seach['search']; //用户id
            }
            if(!empty($seach['status'])){
                 $where['status'] = $seach['status'];    //包裹状态
            }
            if(!empty($seach['start_time']) && !empty($seach['end_time'])){
                 $where['entering_warehouse_time']=['between',[$seach['start_time'],$seach['end_time']]];
            }
            if(!empty($seach['extract_shop_id'])){
                $where['storage_id'] = $seach['extract_shop_id'];  //仓库
            }
            if(!empty($seach['express_num'])){
                 $where['express_num'] = $seach['express_num'];  //快递单号
            }
            $data =(new Inpack())->where($where)->select()->each(function ($item, $key) use($map){
                    
                    $item["user"] = (new UserModel())->where('user_id',$item['member_id'])->field('user_id,nickName,mobile')->find();
                    //判断是否有优惠折扣
                    $discountData = (new UserLine())->where(['user_id'=>$item["user"]['user_id'],'line_id'=>$item['line_id']])->find();
                    if($discountData){
                  
                        $item['discount'] = $discountData['discount'];
                    }else{
                        $item['discount'] = 1;
                    }
                                   
                    $item['discount_price'] = $item['discount'] * $item['free'];
                    $item['status_text'] = $map[$item['status']];
                    $item['phone'] =(new UserAddress())->where('address_id',$item['address_id'])->find();
                    return $item;
                });
        }
        
          $style_Array=array(
            'font'    => array (
               'bold'      => true
              ),
             'alignment' => array (
                      'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
               ),
              'borders' => array (
                   'top'     => array (
                           'style' => \PHPExcel_Style_Border::BORDER_THIN
                       )
                ),
          );
         
        $setting = SettingModel::getItem('store',$data[0]['wxapp_id']);
        $objPHPExcel->getActiveSheet()->getStyle( 'A4:S4')->applyFromArray($style_Array);
        //第一行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('A1',$setting['name'].'── 业务结算清单');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(24);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:S1');
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(36);
        // $objPHPExcel->getActiveSheet()->setRowHeight(25);
        //第二行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('B2','致'.$data[0]['address']['name'].'  '.'导出日期：'.getTime());
        $objPHPExcel->getActiveSheet()->mergeCells('B2:J2');
        
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A4', '序号')
                ->setCellValue('B4', '集运线路')
                ->setCellValue('C4', '平台订单号')
                ->setCellValue('D4', '目的地')
                ->setCellValue('E4', '重量')
                ->setCellValue('F4', '标准价')
                ->setCellValue('G4', '商品品类')
                ->setCellValue('H4', '商品价格')
                ->setCellValue('I4', '用户ID')
                ->setCellValue('J4', '姓名')
                ->setCellValue('K4', '个人通关号码')
                ->setCellValue('L4', '身份证')
                ->setCellValue('M4', '地址')
                ->setCellValue('N4', '邮编')
                ->setCellValue('O4', '承运商')
                ->setCellValue('P4', '发货单号')
                ->setCellValue('Q4', '备注')
                ->setCellValue('R4', '状态')
                ->setCellValue('S4', '业务日期')
                ->setCellValue('T4', '专属客服');
                   
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:T')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A4:T4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:T')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $objPHPExcel->getActiveSheet()->getStyle('A:T')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置行高
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
        //设置颜色

        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(8);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('K')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('L')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('M')->setWidth(25);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('N')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('O')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('P')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('Q')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('R')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('S')->setWidth(22);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('T')->setWidth(22);
        
        for($i=0;$i<count($data);$i++){
            // dump($data->toArray());die;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+5),$i+1);//序号
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+5),$data[$i]['t_name']);//集运路线
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+5),$data[$i]['order_sn']);//平台订单号
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+5),$data[$i]['country']);//目的地
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+5),$data[$i]['weight']);//重量
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+5),$data[$i]['free']);//标准价
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+5),$data[$i]['packClass']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+5),$data[$i]['packprice']);//标准价 ***********
            $objPHPExcel->getActiveSheet()->setCellValue('I'.($i+5),$data[$i]['user']['user_id']);//用户id
            $objPHPExcel->getActiveSheet()->setCellValue('J'.($i+5),$data[$i]['address']['name']);//用户昵称
            $objPHPExcel->getActiveSheet()->setCellValue('K'.($i+5),$data[$i]['address']['clearancecode']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('L'.($i+5),$data[$i]['address']['identitycard']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('M'.($i+5),$data[$i]['address']['detail']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('N'.($i+5),$data[$i]['address']['code']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('O'.($i+5),$data[$i]['t_name'].' ');//内部单号
            $objPHPExcel->getActiveSheet()->setCellValue('p'.($i+5),$data[$i]['t_order_sn'].' ');//内部单号
            $objPHPExcel->getActiveSheet()->setCellValue('Q'.($i+5),$data[$i]['remark']);//备注
            $objPHPExcel->getActiveSheet()->setCellValue('R'.($i+5),$status[$data[$i]['status']]);//转单号码
            $objPHPExcel->getActiveSheet()->setCellValue('S'.($i+5),$data[$i]['created_time']);//业务日期
            $objPHPExcel->getActiveSheet()->setCellValue('T'.($i+5),$data[$i]['user']['user_id']);//专属客服
        }
        //7.设置保存的Excel表格名称
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('业务结算清单');
        //9.设置浏览器窗口下载表格
        $filename = "用户包裹"  . rand(1000000, 9999999) . ".xlsx";
        // $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        $ov = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        $ov->save("excel/" . $filename);
        return $this->renderSuccess("导出成功", [
            "file_name" => "https://".$_SERVER["HTTP_HOST"] . "/excel/" . $filename,
        ]);
     }
     
     
     
     /**导出集运业务结算**/
    //导出成excel文档
     public function exportInpack(){
         //引入excel插件
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        //获取需要导出的数据列表
        $ids= input("post.selectId/a");
        $seach= input("post.seach/a");
        //1 待入库 2 已入库 3 已分拣上架  4 待打包  5 待支付  6 已支付 7 已分拣下架  8 已打包  9 已发货 10 已收货 11 已完成
        $map =[-1=>'问题件',1=>'待入库',2=>'已入库',3=>'已分拣上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成'];
        $status = [1=>'待查验',2=>'待支付',3=>'已支付','4'=>'已拣货','5'=>'已打包','6'=>'已发货','7'=>'已收货','8'=>'已完成','-1'=>'已取消'];
       
        if($ids){
           $data = (new Inpack())->with(['storage','shop'])->whereIn('id',$ids)->select()->each(function ($item, $key) use($map){
                    $item["user"] = (new UserModel())->where('user_id',$item['member_id'])->field('user_id,nickName,mobile')->find();
                    $item['t_name'] = (new Line())->where('id',$item['line_id'])->value('name');
                    $item['shopCapital'] = (new Capital())->where(['inpack_id'=> $item['order_sn'],'shop_id' => $item['shop_id']])->value('money');
                    $item['straogeCapital'] = (new Capital())->where(['inpack_id'=> $item['order_sn'],'shop_id' => $item['storage_id']])->value('money');
                    
                    
                    //集运单包裹中的物品分类和价格
                    $packdata = explode(",",$item['pack_ids']);
                    $packClass = [];
                    $packprice = 0;
             
                    foreach($packdata as $key => $vale){
                        $expressnum = (new Package())->where('id',$vale)->find();
                        $packitem = (new PackageItem())->where('express_num',$expressnum['express_num'])->select();
                        $packClass[$key]="";
                        $packprice=0;
                        if(count($packitem)>0){
                            $packClass[$key] = $packitem[0]['class_name'];
                            $packprice += $packitem[0]['all_price'];
                        } 
                    }
                    
                    $item['packClass'] = implode($packClass);
                    $item['packprice'] = $packprice;
                    // dump($packClass);die;
                    //折扣信息
                    
                    $discountData = (new UserLine())->where(['user_id'=>$item["user"]['user_id'],'line_id'=>$item['line_id']])->find();
                    if($discountData){
                        $item['discount'] = $discountData['discount'];
                    }else{
                        $item['discount'] = 1;
                    }
                                   
                    $item['discount_price'] = $item['discount'] * $item['free'];
               
                    $item['status_text'] = $map[$item['status']];
                    $item['address'] =(new UserAddress())->where('address_id',$item['address_id'])->find();
                    return $item;
                }); 
                
        }else{
            if(!empty($seach['search'])){
                 $where['member_id'] = $seach['search']; //用户id
            }
            if(!empty($seach['status'])){
                 $where['status'] = $seach['status'];    //包裹状态
            }
            if(!empty($seach['start_time']) && !empty($seach['end_time'])){
                 $where['entering_warehouse_time']=['between',[$seach['start_time'],$seach['end_time']]];
            }
            if(!empty($seach['extract_shop_id'])){
                $where['storage_id'] = $seach['extract_shop_id'];  //仓库
            }
            if(!empty($seach['express_num'])){
                 $where['express_num'] = $seach['express_num'];  //快递单号
            }
            $data =(new Inpack())->where($where)->select()->each(function ($item, $key) use($map){
                    
                    $item["user"] = (new UserModel())->where('user_id',$item['member_id'])->field('user_id,nickName,mobile')->find();
                    //判断是否有优惠折扣
                    $discountData = (new UserLine())->where(['user_id'=>$item["user"]['user_id'],'line_id'=>$item['line_id']])->find();
                    if($discountData){
                  
                        $item['discount'] = $discountData['discount'];
                    }else{
                        $item['discount'] = 1;
                    }
                                   
                    $item['discount_price'] = $item['discount'] * $item['free'];
                    $item['status_text'] = $map[$item['status']];
                    $item['phone'] =(new UserAddress())->where('address_id',$item['address_id'])->find();
                    return $item;
                });
        }
        
          $style_Array=array(
            'font'    => array (
               'bold'      => true
              ),
             'alignment' => array (
                      'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
               ),
              'borders' => array (
                   'top'     => array (
                           'style' => \PHPExcel_Style_Border::BORDER_THIN
                       )
                ),
          );
         
        $setting = SettingModel::getItem('store',$data[0]['wxapp_id']);
        $objPHPExcel->getActiveSheet()->getStyle( 'A4:R4')->applyFromArray($style_Array);
        //第一行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('A1',$setting['name'].'── 分成结算');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(24);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:R1');
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(36);
        // $objPHPExcel->getActiveSheet()->setRowHeight(25);
        //第二行的样式
        $objPHPExcel->getActiveSheet()->setCellValue('B2','致'.$data[0]['address']['name'].'  '.'导出日期：'.getTime());
        $objPHPExcel->getActiveSheet()->mergeCells('B2:J2');
        
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A4', '序号')
                ->setCellValue('B4', '集运线路')
                ->setCellValue('C4', '平台订单号')
                ->setCellValue('D4', '目的地')
                ->setCellValue('E4', '重量')
                ->setCellValue('F4', '支付金额')
                ->setCellValue('G4', '寄件仓库')
                ->setCellValue('H4', '寄件分成')
                ->setCellValue('I4', '派件仓库')
                ->setCellValue('J4', '派件分成')
                ->setCellValue('K4', '发货单号')
                ->setCellValue('L4', '派件入库时间')
                ->setCellValue('M4', '派件签收时间')
                ->setCellValue('N4', '分成结算日期');
                   
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:R')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A4:R4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A:R')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $objPHPExcel->getActiveSheet()->getStyle('A:R')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //设置行高
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
        //设置颜色

        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(8);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('K')->setWidth(18);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('L')->setWidth(25);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('M')->setWidth(25);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('N')->setWidth(25);

        
        for($i=0;$i<count($data);$i++){
            // dump($data->toArray());die;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($i+5),$i+1);//序号
            $objPHPExcel->getActiveSheet()->setCellValue('B'.($i+5),$data[$i]['t_name']);//集运路线
            $objPHPExcel->getActiveSheet()->setCellValue('C'.($i+5),$data[$i]['order_sn']);//平台订单号
            $objPHPExcel->getActiveSheet()->setCellValue('D'.($i+5),$data[$i]['country']);//目的地
            $objPHPExcel->getActiveSheet()->setCellValue('E'.($i+5),$data[$i]['weight']);//重量
            $objPHPExcel->getActiveSheet()->setCellValue('F'.($i+5),$data[$i]['real_payment']);//标准价
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($i+5),$data[$i]['storage']['shop_name']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('H'.($i+5),$data[$i]['straogeCapital']);//标准价 ***********
            $objPHPExcel->getActiveSheet()->setCellValue('I'.($i+5),$data[$i]['shop']['shop_name']);//用户id
            $objPHPExcel->getActiveSheet()->setCellValue('J'.($i+5),$data[$i]['shopCapital']);//用户昵称
            $objPHPExcel->getActiveSheet()->setCellValue('K'.($i+5),$data[$i]['t_order_sn']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('L'.($i+5),$data[$i]['shoprk_time']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('M'.($i+5),$data[$i]['receipt_time']);//快递类别  ***********
            $objPHPExcel->getActiveSheet()->setCellValue('N'.($i+5),$data[$i]['settle_time']);//快递类别  ***********
    
        }
        //7.设置保存的Excel表格名称
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('业务结算清单');
        //9.设置浏览器窗口下载表格
        $filename = "用户包裹"  . rand(1000000, 9999999) . ".xlsx";
        // $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        $ov = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        $ov->save("excel/" . $filename);
        return $this->renderSuccess("导出成功", [
            "file_name" => "https://".$_SERVER["HTTP_HOST"] . "/excel/" . $filename,
        ]);
     }

}
