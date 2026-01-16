<?php
namespace app\store\model;
use think\Model;
use think\Db;
use app\api\model\Logistics;
use app\api\model\Package;
use app\store\model\Package as PackageModel;
use app\api\model\User;
use app\api\model\user\BalanceLog;
use app\common\enum\user\balanceLog\Scene as SceneEnum;
use app\common\model\Inpack as InpackModel;
use app\common\service\Email;
use app\store\service\BarCodeService;
use app\api\service\trackApi\TrackApi;
use app\common\model\User as UserModel;
use app\common\model\Setting as SettingModel;
use app\store\model\InpackImage;
use app\store\model\UserAddress;
use app\store\model\Ditch as DitchModel;
use app\store\model\Express;
use app\common\service\Message;
/**
 * 打包模型
 * Class Delivery
 * @package app\common\model
 */
class Inpack extends InpackModel
{
    
    public  $status = [
        'all' => [-1,1,2,3,4,5,6,7,8,9],
        'cancel' => [-1],
        'verify' => [1], // 查验
        'pay' => [-1,2,3,4,5,6,7,8,9], // 未支付
        'payed' => [2,3,4,5], // 已支付
        'sending' => [6],
        'sended' => [7],
        'complete' => [8]
     ];
     
     /**
     * 订单列表
     * @param string $dataType
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($dataType, $query = [])
    {
        // 检索查询条件
        !empty($query) && $this->setWhere($query);
        !isset($query['limitnum']) && $query['limitnum'] = 10;
        // 获取数据列表 - 使用 Db 查询避免触发 UserAddress 模型的访问器
        $res= \think\Db::name('inpack')
            ->alias('pa')
            ->field('pa.*,ba.batch_id,ba.batch_name,ba.batch_no,u.nickName,add.address_id,add.name,add.phone,add.country,add.province,add.city,add.region,add.detail,add.identitycard,add.clearancecode,add.street,add.door,add.code,add.email')
            ->join('user u','u.user_id = pa.member_id','left')
            ->join('user_address add','add.address_id = pa.address_id','left')
            ->join('batch ba','ba.batch_id = pa.batch_id','left')
            ->where('pa.status','in',$this->status[$dataType])
            ->where('pa.is_delete',0)
            ->order('pa.created_time', 'desc')
            ->paginate($query['limitnum'], false, [
                'query' => \request()->request()
            ]);
            
        // 手动加载关联数据（line, storage, user, shop, usercoupon）
        $res->each(function(&$item) {
            // 初始化 num 字段（控制器会重新计算，但先初始化避免错误）
            $item['num'] = !empty($item['pack_ids']) ? count(explode(',', $item['pack_ids'])) : 0;
            $item['down_shelf'] = 0;
            $item['inpack'] = 0;
            
            // 初始化关联字段，避免视图中出现 "Undefined index" 错误
            $item['line'] = ['name' => ''];
            $item['storage'] = ['shop_name' => ''];
            $item['shop'] = ['shop_name' => ''];
            $item['usercoupon'] = ['name' => ''];
            $item['user'] = ['nickName' => $item['nickName'] ?? '', 'user_code' => '', 'service' => []];
            
            // 构建 address 数组 - 使用已经 join 的字段
            $item['address'] = [
                'name' => $item['name'] ?? '',
                'phone' => $item['phone'] ?? '',
                'country' => $item['country'] ?? '',
                'province' => $item['province'] ?? '',
                'city' => $item['city'] ?? '',
                'region' => $item['region'] ?? '',
                'detail' => $item['detail'] ?? '',
                'identitycard' => $item['identitycard'] ?? '',
                'clearancecode' => $item['clearancecode'] ?? '',
                'street' => $item['street'] ?? '',
                'door' => $item['door'] ?? '',
                'code' => $item['code'] ?? '',
                'email' => $item['email'] ?? ''
            ];
            
            // 加载 user 关联（包括 service）
            if (!empty($item['member_id'])) {
                try {
                    $user = \think\Db::name('user')->where('user_id', $item['member_id'])->find();
                    if ($user) {
                        $item['user'] = $user;
                        // 加载 service 关联
                        if (!empty($user['service_id'])) {
                            $service = \think\Db::name('store_clerk')->where('clerk_id', $user['service_id'])->find();
                            if ($service) $item['user']['service'] = $service;
                        } else {
                            $item['user']['service'] = [];
                        }
                    }
                } catch (\Exception $e) {}
            }
            
            // 加载 line 关联
            if (!empty($item['line_id'])) {
                try {
                    $line = \think\Db::name('line')->where('id', $item['line_id'])->find();
                    if ($line) $item['line'] = $line;
                } catch (\Exception $e) {}
            }
            // 加载 storage 关联
            if (!empty($item['storage_id'])) {
                try {
                    $storage = \think\Db::name('shop')->where('shop_id', $item['storage_id'])->find();
                    if ($storage) $item['storage'] = $storage;
                } catch (\Exception $e) {}
            }
            // 加载 shop 关联
            if (!empty($item['shop_id'])) {
                try {
                    $shop = \think\Db::name('shop')->where('shop_id', $item['shop_id'])->find();
                    if ($shop) $item['shop'] = $shop;
                } catch (\Exception $e) {}
            }
            // 加载 usercoupon 关联
            if (!empty($item['coupon_id'])) {
                try {
                    $coupon = \think\Db::name('user_coupon')->where('user_coupon_id', $item['coupon_id'])->find();
                    if ($coupon) $item['usercoupon'] = $coupon;
                } catch (\Exception $e) {}
            }
            return $item;
        });
        
        return $res;
    }
    
    
    /**
     * 未支付订单列表
     * @param string $dataType
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getNoPayList($dataType, $query = [])
    {
        // 检索查询条件
        !empty($query) && $this->setWhere($query);
        !isset($query['limitnum']) && $query['limitnum'] = 10;
        // 获取数据列表 - 使用 Db 查询避免触发 UserAddress 模型的访问器
        $res= \think\Db::name('inpack')
            ->alias('pa')
            ->field('pa.*,u.nickName,add.name as address_name,add.phone as address_phone,add.country,add.province,add.city,add.region,add.detail')
            ->join('user u','u.user_id = pa.member_id','left')
            ->join('user_address add','add.address_id = pa.address_id','left')
            ->where('pa.status','in',$this->status[$dataType])
            ->where('pa.is_delete',0)
            ->where('pa.is_pay',2)
            ->order('pa.created_time', 'desc')
            ->paginate($query['limitnum'], false, [
                'query' => \request()->request()
            ]);
            
        // 手动加载关联数据
        $items = $res->items();
        foreach ($items as &$item) {
            $item['line'] = ['name' => ''];
            $item['storage'] = ['shop_name' => ''];
            $item['user'] = ['nickName' => $item['nickName'] ?? ''];
            $item['address'] = [
                'name' => $item['address_name'] ?? '',
                'phone' => $item['address_phone'] ?? '',
                'country' => $item['country'] ?? '',
                'province' => $item['province'] ?? '',
                'city' => $item['city'] ?? '',
                'region' => $item['region'] ?? '',
                'detail' => $item['detail'] ?? ''
            ];
            
            if (!empty($item['line_id'])) {
                try {
                    $line = \think\Db::name('line')->where('id', $item['line_id'])->find();
                    if ($line) $item['line'] = $line;
                } catch (\Exception $e) {}
            }
            if (!empty($item['storage_id'])) {
                try {
                    $storage = \think\Db::name('shop')->where('shop_id', $item['storage_id'])->find();
                    if ($storage) $item['storage'] = $storage;
                } catch (\Exception $e) {}
            }
        }
        $res->items($items);
        
        return $res;
    }
    
    /**
     * 快速打包订单列表
     * @param string $dataType
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getQuicklypack($dataType, $query = [])
    {
        // 检索查询条件
        !empty($query) && $this->setWhere($query);
        !isset($query['limitnum']) && $query['limitnum'] = 10;
        // 获取数据列表 - 使用 Db 查询避免触发 UserAddress 模型的访问器
        $res= \think\Db::name('inpack')
            ->alias('pa')
            ->field('pa.*,u.nickName')
            ->join('user u','u.user_id = pa.member_id','left')
            ->join('user_address add','add.address_id = pa.address_id','left')
            ->where('pa.status','in',$this->status[$dataType])
            ->where('pa.is_delete',0)
            ->where('pa.address_id',null)
            ->order('pa.created_time', 'desc')
            ->paginate($query['limitnum'], false, [
                'query' => \request()->request()
            ]);
            
        // 手动加载关联数据
        $items = $res->items();
        foreach ($items as &$item) {
            $item['line'] = ['name' => ''];
            $item['storage'] = ['shop_name' => ''];
            $item['user'] = ['nickName' => $item['nickName'] ?? ''];
            
            if (!empty($item['line_id'])) {
                try {
                    $line = \think\Db::name('line')->where('id', $item['line_id'])->find();
                    if ($line) $item['line'] = $line;
                } catch (\Exception $e) {}
            }
            if (!empty($item['storage_id'])) {
                try {
                    $storage = \think\Db::name('shop')->where('shop_id', $item['storage_id'])->find();
                    if ($storage) $item['storage'] = $storage;
                } catch (\Exception $e) {}
            }
        }
        $res->items($items);
        
        return $res;
    }
    
    
    
    // 代购单 同步
    public function anyicData($v){
        $data['source'] = 4;
        $data['express_num'] = $v['express_num']; 
        $data['express_id'] = $v['express_id'];
        $data['real_payment'] = $v['free'];
        $data['price'] = $v['price'];
        $data['member_id'] = $v['member_id'];
        $data['storage_id'] = $v['storage_id'];
        $data['created_time'] = getTime();
        $data['updated_time'] = getTime();
        $data['status'] = 2;
        $data['is_pay'] = 1;
        $data['pay_time'] =  getTime();
        $data['entering_warehouse_time'] = getTime();
        $data['order_sn'] = createSn();
        $data['country_id'] = 12;
        $data['wxapp_id'] = self::$wxapp_id;
        $packageRes = (new Package())->insert($data);  
        if (!$packageRes){
            return false;
        }
        return true;
    }
    
    
     /***
     * 保存集运单编辑内容
     * 2022年11月5日 冯
     * @param $data []
     */
    public function edit($data){
      
        //修改保存集运图片
        $imgres = true;
        if(isset($data['images'])){
           $inpackImg = new  InpackImage;
           $inpackImg->where('inpack_id',$data['id'])->delete();
           $img = [
                'inpack_id' => $data['id'],
                'wxapp_id' =>  self::$wxapp_id,
            ];
           foreach ($data['images'] as $val){
               $img['image_id'] = $val;
               $imgres = $inpackImg->insert($img);
           }
           unset($data['images']);
        }
  
        $pack = $this->where('id',$data['id'])->find();
        // if(isset($data['is_pay']) && $data['is_pay'] == 1){
        //       $data['pay_time'] = getTime();
        //       $data['is_pay_type'] = 0;
        //       $data['is_pay'] = 1;
        //       if($pack['status']==2){
        //           $data['status'] = 3;
        //       }else{
        //           $data['status'] = $pack['status'];
        //       }
        // }
        
        
        $userData = (new UserModel)->where('user_id',$pack['member_id'])->find();
        $pack['userName']=$userData['nickName'];
        //判断是否更新状态到已查验
        if(isset($data['verify']) && ($data['verify'] ==1)){
            $data['status'] = 2;
            //发送订阅消息以及模板消息,包裹查验完成，等待支付
            $noticesetting = SettingModel::getItem('notice');
            //根据设置内容，判断是否需要发送通知；
            
         
            
            $pack['remark']= $noticesetting['check']['describe'];
            $pack['total_free'] = $pack['free']+$pack['other_free']+$pack['pack_free'];
            //获取模板消息设置，根据设置选择调用的函数

            //发送邮件通知
             $email = SettingModel::getItem('email');
            if($email['is_enable']==1 && (isset($pack['member_id']) || !empty($pack['member_id']))){
                $EmailUser = UserModel::detail($pack['member_id']);
                $EmailData['code'] = $data['id'];
                $EmailData['logistics_describe']=$noticesetting['check']['describe'];
                (new Email())->sendemail($EmailUser,$EmailData,$type=1);
            }
        }
        
        
        unset($data['verify']);
        // dump($pack);die;
        $rers =  $this->where('id',$data['id'])->update($data);
        $tplmsgsetting = SettingModel::getItem('tplMsg');
            if($tplmsgsetting['is_oldtps']==1){
                  //发送旧版本订阅消息以及模板消息
                //   $sub = $this->sendEnterMessage([$post]);
                  $res =$this->sendEnterMessage([$pack],'payment');
            }else{
                  //发送新版本订阅消息以及模板消息
                  Message::send('package.dabaosuccess',$pack);
                  Message::send('package.payorder',$pack);
            }
       if($rers || $imgres){return true;}
       return false;
    }
    
    
    /***
     * 修改集运单的状态 
     * @param $data []
     */
    public function modify($data){
        // 调试：记录接收到的数据
        $log_file = __DIR__ . '/../../../../../debug_transfer_log.txt';
        file_put_contents($log_file, 
            "=== Inpack::modify() 接收数据 " . date('Y-m-d H:i:s') . " ===\n" .
            print_r($data, true) . "\n",
            FILE_APPEND
        );
                
        $field = ['line_id','length','width','height','weight','verify','free','pack_free','cale_weight','volume','other_free','remark','t_number','t_name','t_order_sn','tt_number','transfer'];
        $update = [];
        //物流模板设置
        $noticesetting = SettingModel::getItem('notice');
        $tplmsgsetting = SettingModel::getItem('tplMsg');
        foreach ($field as $v){
            if (isset($data[$v]))
               $update[$v] = $data[$v];
        }
        
        // 调试：记录过滤后的数据
        file_put_contents($log_file, 
            "过滤后的 \$update 数组:\n" . print_r($update, true) . "\n",
            FILE_APPEND
        );
      
        $update['updated_time'] = getTime();
   
        if (isset($data['pay_method'])){ 
            $pack = $this->where(['id'=>$data['id']])->field('id,order_sn,weight,free,other_free,pack_free,member_id,created_time,wxapp_id,pack_ids')->find();
            if ($data['pay_method']==1){
            
                $update['status'] = 5;
                $update['pay_time'] = getTime();
                $update['is_pay'] = 1;
                // 立即扣款 
                $allPrice = $data['free'] + $data['pack_free'] + $data['other_free'];
                $user = (new User())->find($pack['member_id']);
                Db::startTrans();
                if ($user['balance']<$allPrice){
                    return $this->renderError('余额不足,请充值');
                }
                $memberUp = (new User())->where(['user_id'=>$user['user_id']])->update([
                  'balance'=>$user['balance']-$allPrice,
                  'pay_money' => $user['pay_money']+ $allPrice,
                ]);
                 if (!$memberUp){
                     Db::rollback();
                     return $this->renderError('支付失败,请重试');
                 }
                  // 新增余额变动记录
                 BalanceLog::add(SceneEnum::CONSUME, [
                  'user_id' => $user['user_id'],
                  'money' => $allPrice,
                  'remark' => '包裹单号'.$pack['order_sn'].'的运费支付',
                  'sence_type' => 2,
                  'wxapp_id' => (new PackageModel())->getWxappId(),
              ], [$user['nickName']]);
            }else{
                $update['status'] = 5;
            }
        }
        
        if (!isset($data['type'])){
             // 更新查验物流信息
                $pack = $this->where(['id'=>$data['id']])->find();
                $userData = (new UserModel)->where('user_id',$pack['member_id'])->find();
                if (isset($data['verify']) && $data['verify']==1){
                 
                $update['status'] = 2;
                if ($pack['source']==2){
                    $update['status'] = 2;
                }
                unset($update['verify']);
               
                $pack['total_free'] = $pack['free']+$pack['other_free']+$pack['pack_free'];
               
                $packids = explode(',',$pack['pack_ids']);
                //判断是否需要添加物流信息
                if($noticesetting['enter']['is_enable']==1){
                   foreach ($packids as $v){
                    Logistics::add($v,$noticesetting['enter']['describe']);
                    } 
                }
                
                //发送订阅消息以及模板消息
                $pack['userName']=$userData['nickName'];
                $pack['remark']= $noticesetting['enter']['describe'];
                
                // 发送LINE查验通知
                try {
                    $lineNotification = new \app\common\service\message\line\Inwarehouse();
                    $lineNotification->send($pack);
                } catch (\Exception $e) {
                    log_write([
                        'describe' => 'LINE查验通知发送失败',
                        'inpack_id' => $pack['id'],
                        'error' => $e->getMessage(),
                        'time' => date('Y-m-d H:i:s')
                    ]);
                }
                
                //发送邮件通知
                $emailsetting = SettingModel::getItem('email');
                if($emailsetting['is_enable']==1 && (isset($pack['member_id']) || !empty($pack['member_id']))){
                    $EmailUser = UserModel::detail($pack['member_id']);
                    $EmailData['code'] = $data['id'];
                    $EmailData['logistics_describe']=$noticesetting['enter']['describe'];
                    (new Email())->sendemail($EmailUser,$EmailData,$type=1);
                }  
                
            }
            if (isset($data['pay_status'])){
                if (isset($update['status']) && $update['status']!=6){
                    $update['status'] = $data['pay_status'];
                }
                if($data['pay_status']){
                    $update['status'] = 3;
                    $update['pay_time'] = getTime();
                }

                // 更新查验物流信息
                $pack = $this->where(['id'=>$data['id']])->field('id,order_sn,pack_ids')->find();
                $packids = explode(',',$pack['pack_ids']);
                (new Package())->where('id','in',$packids)->update(['status'=>6]);
                unset($update['pay_status']);
            }
        }else{
            $update['status'] = '6';
             // 更新查验物流信息
            $pack = $this->where(['id'=>$data['id']])->find();
            $useraddress = UserAddress::detail($pack['address_id']);
            // dump(substr($useraddress['phone'],-4));die;
            $userData = (new UserModel)->where('user_id',$pack['member_id'])->find();
            $packids = explode(',',$pack['pack_ids']);
            // foreach ($packids as $v){
            //     Logistics::add($v,'包裹已经为您发货,正在发往所在目的地');
            // }
            // $track =  getFileData('assets/track.json');
            // $trackByKey = array_column($track,null,'key');  
            // $pack['t_name'] = !empty($update['t_name1'])?$trackByKey[$update['t_name1']]['_name']:$update['t_name'];
            // if ($update['t_name']=='' && $update['t_number']){
            //     $update['t_name'] = $pack['t_name'];
            //     $update['t_number'] = $update['t_number'];
            // }
            
 
          
            $pack['t_order_sn'] = $data['t_order_sn'];
            
            //   dump($update);die;
             //判断是否需要添加物流信息
            if($noticesetting['dosend']['is_enable']==1){
                if(!empty($pack['t_order_sn'])){
                   $noticesetting['dosend']['describe'] = '包裹转单操作，新单号为'.$pack['t_order_sn']; 
                }
                if(strpos($noticesetting['dosend']['describe'],'code')){
                     $des = str_ireplace('{code}', $pack['t_order_sn'], $noticesetting['dosend']['describe']);
                     Logistics::addInpackLog($pack['order_sn'],$des,$pack['t_order_sn']);
                }else{
                     Logistics::addInpackLog($pack['order_sn'],$noticesetting['dosend']['describe'],$pack['t_order_sn']);
                }   
            }
            ////注册发货单到17track,当是选择可以查询的物流时，自有物流不可查询
            if($data['transfer']==1 && $noticesetting['is_track_fahuo']['is_enable']==1){
                $express = (new Express())->where('express_code',$data['tt_number'])->find();
                // dump($express);die;
                $trackd = (new TrackApi())
                ->register([
                    'track_sn'=>$pack['t_order_sn'],
                    't_number'=>$data['tt_number'],
                    'phone' => substr($useraddress['phone'],-4),
                    'wxapp_id' =>$userData['wxapp_id']
                ]);
                $update['t_name'] = $express['express_name'];
                $update['t_number'] = $data['tt_number'];
                $update['transfer'] = $data['transfer'];
            }
            
            if($data['transfer']==0){
                $ditchdetail = DitchModel::detail($data['t_number']);
                $update['t_name'] = $ditchdetail['ditch_name'];
                $update['t_number'] = $ditchdetail['ditch_id'];
                $update['transfer'] = $data['transfer'];
            }
            

            //发送订阅消息以及模板消息（仅在非转单模式下发送）
            if($data['type'] != 'change'){
                $pack['userName']=$userData['nickName'];
                $pack['remark']='包裹已经发货';
                $pack['total_free'] = $pack['free'] + $pack['pack_free'] + $pack['other_free'] ;
                
                // 发送LINE发货通知
                try {
                    $lineNotification = new \app\common\service\message\line\Sendpack();
                    $lineNotification->send($pack);
                } catch (\Exception $e) {
                    log_write([
                        'describe' => 'LINE发货通知发送失败',
                        'inpack_id' => $pack['id'],
                        'error' => $e->getMessage(),
                        'time' => date('Y-m-d H:i:s')
                    ]);
                }
            }
                
            //发送邮件通知
            $emailsetting = SettingModel::getItem('email');
            if($emailsetting['is_enable']==1 && (isset($pack['member_id']) || !empty($pack['member_id']))){
                $EmailUser = UserModel::detail($pack['member_id']);
                $EmailData['code'] = $data['id'];
                $EmailData['logistics_describe']=$noticesetting['dosend']['describe'];
                (new Email())->sendemail($EmailUser,$EmailData,$type=1);
            }
        }
        unset($update['verify']);
        
        if($data['type']=='change'){
            // 调试：记录进入转单模式
            $log_file = __DIR__ . '/../../../../../debug_transfer_log.txt';
            file_put_contents($log_file, 
                "=== 进入转单模式 ===\n" .
                "data['type'] = " . $data['type'] . "\n" .
                "data['transfer'] = " . ($data['transfer'] ?? 'undefined') . "\n" .
                "data['tt_number'] = " . ($data['tt_number'] ?? 'undefined') . "\n" .
                "data['t_number'] = " . ($data['t_number'] ?? 'undefined') . "\n" .
                "data['t_order_sn'] = " . ($data['t_order_sn'] ?? 'undefined') . "\n",
                FILE_APPEND
            );
            
            // 转单模式：需要查询承运商名称
            $carrier_name = '';
            $carrier_number = '';
            
            if($data['transfer']==1){
                // 外部承运商
                file_put_contents($log_file, "查询外部承运商: " . $data['tt_number'] . "\n", FILE_APPEND);
                $express = (new Express())->where('express_code',$data['tt_number'])->find();
                if($express){
                    $carrier_name = $express['express_name'];
                    $carrier_number = $data['tt_number'];
                    file_put_contents($log_file, "找到承运商: $carrier_name ($carrier_number)\n", FILE_APPEND);
                } else {
                    file_put_contents($log_file, "未找到承运商!\n", FILE_APPEND);
                }
            }else{
                // 自有物流
                file_put_contents($log_file, "查询自有物流: " . $data['t_number'] . "\n", FILE_APPEND);
                $ditchdetail = DitchModel::detail($data['t_number']);
                if($ditchdetail){
                    $carrier_name = $ditchdetail['ditch_name'];
                    $carrier_number = $ditchdetail['ditch_id'];
                    file_put_contents($log_file, "找到自有物流: $carrier_name ($carrier_number)\n", FILE_APPEND);
                } else {
                    file_put_contents($log_file, "未找到自有物流!\n", FILE_APPEND);
                }
            }
            
            $upd['t2_number'] = $carrier_number;
            $upd['t2_name'] = $carrier_name;
            $upd['t2_order_sn'] = $data['t_order_sn'];
            $upd['updated_time'] = $update['updated_time'];
            $upd['status'] = $update['status'];
            
            // 调试：记录最终更新数据
            file_put_contents($log_file, 
                "最终更新数组 \$upd:\n" . print_r($upd, true) . "\n",
                FILE_APPEND
            );
            
            $update = $upd;
            
            // 发送LINE转单通知（在数据库更新之前，使用即将更新的数据）
            try {
                // 获取订单基本信息
                $pack = $this->where(['id'=>$data['id']])->find();
                $userData = (new UserModel)->where('user_id',$pack['member_id'])->find();
                
                // 添加转单信息
                $pack['t2_name'] = $carrier_name;
                $pack['t2_number'] = $carrier_number;
                $pack['t2_order_sn'] = $data['t_order_sn'];
                $pack['userName'] = $userData['nickName'];
                $pack['remark'] = '包裹已转单发货';
                $pack['total_free'] = $pack['free'] + $pack['pack_free'] + $pack['other_free'];
                
                $lineNotification = new \app\common\service\message\line\Sendpack();
                $lineNotification->send($pack);
                
                file_put_contents($log_file, "LINE转单通知已发送\n", FILE_APPEND);
            } catch (\Exception $e) {
                file_put_contents($log_file, "LINE转单通知发送失败: " . $e->getMessage() . "\n", FILE_APPEND);
                log_write([
                    'describe' => 'LINE转单通知发送失败',
                    'inpack_id' => $data['id'],
                    'error' => $e->getMessage(),
                    'time' => date('Y-m-d H:i:s')
                ]);
            }
            // dump($update);die;
            ////注册发货单到17track,当是选择可以查询的物流时，自有物流不可查询
            if($data['transfer']==1 && $noticesetting['is_track_zhuandan']['is_enable']==1){
                $trackd = (new TrackApi())
                ->register([
                    'track_sn'=>$upd['t2_order_sn'],
                    't_number'=>$upd['t2_number'],
                    'phone' => substr($useraddress['phone'],-4),
                    'wxapp_id' =>$userData['wxapp_id']
                ]);
            }
        }
        
        // 调试：记录最终执行的更新
        file_put_contents($log_file, 
            "=== 执行数据库更新 ===\n" .
            "inpack_id = " . $data['id'] . "\n" .
            "更新数据:\n" . print_r($update, true) . "\n" .
            "==================\n\n",
            FILE_APPEND
        );
        
        $resss = $this->where(['id'=>$data['id']])->update($update);
        Db::commit();
        return  $resss;
    }
    
    // 添加包裹
    public function appendData($data){
        //$pack : 要插入的快递信息
        //$inpack : 被插入的集运单
            
        if (!$pack = (new Package())->where(['express_num'=>$data['express_num']])->find()){
            $this->error = '该包裹不在库中';
            return false;
        }
        // 判断包裹是否已经在其他集运包裹中；如果不在，则可以添加进来；
        if(in_array($pack['status'],[5,6,7,8,9,10,11])){
            $this->error = '该包裹已在集运单中';
            return false;
        }
        $inpack = (new Inpack())->find($data['id']);
        //判断需要插入的快递单号是否被领取，如果领取memberid不存在，则设置为集运单的memberid；并修改状态为已认领；
        if(!$pack['member_id']){
           (new Package())->where(['id'=>$pack['id']])->update(['member_id'=>$inpack['member_id'],'is_take' => 2]);
        }
        $packages = explode(',',$inpack['pack_ids']);
        $packages[] = $pack['id'];
        $inpackData['pack_ids'] = implode(',',$packages);
        $res = $this->where(['id'=>$data['id']])->update($inpackData);
        (new Package())->where(['id'=>$pack['id']])->update(['status'=>5,'inpack_id'=>$data['id']]);
        if ($res){
            return true;
        }
        $this->error = '包裹添加失败';
        return false;
    }
      
    
    //获取集运单的相关信息->with(['line','storage','inpackimage.file'])
    public static function details($id){
        $detail = self::get($id, ['inpackimage.file']);
        
        // 手动加载 address 数据，避免触发访问器
        if ($detail && $detail['address_id']) {
            $address = \think\Db::name('user_address')
                ->where('address_id', $detail['address_id'])
                ->find();
            
            if ($address) {
                // 直接设置到 data 属性中，这样可以访问但不会保存到数据库
                $detail->data['country'] = $address['country'] ?? '';
                $detail->data['province'] = $address['province'] ?? '';
                $detail->data['city'] = $address['city'] ?? '';
                $detail->data['region'] = $address['region'] ?? '';
                $detail->data['detail'] = $address['detail'] ?? '';
                $detail->data['name'] = $address['name'] ?? '';
                $detail->data['phone'] = $address['phone'] ?? '';
            }
        }
        
        return $detail;
    }

    public function setWhere($query){
        // dump($query);die;
        !empty($query['status']) && $this->where('pa.status','in',$query['status']);
        !empty($query['order_sn']) && $this->where('pa.order_sn|pa.t_order_sn','like','%'.$query['order_sn'].'%');
        !empty($query['extract_shop_id']) && $this->where('pa.storage_id','=',$query['extract_shop_id']);
        !empty($query['line_id']) && $this->where('pa.line_id','=',$query['line_id']);
        !empty($query['service_id']) && $this->where('u.service_id','=',$query['service_id']);
        !empty($query['user_code']) && $this->where('u.user_code','=',$query['user_code']);
        !empty($query['user_id']) && $this->where('pa.member_id','=',$query['user_id']);
        !empty($query['batch_no']) && $this->where('ba.batch_name|ba.batch_no','=',$query['batch_no']);
        !empty($query['batch_id']) && $this->where('pa.batch_id','=',$query['batch_id']);
        !empty($query['start_time']) && $this->where('created_time', '>', $query['start_time']);
        !empty($query['end_time']) && $this->where('created_time', '<', $query['end_time']." 23:59:59");
        !empty($query['search']) && $this->where('pa.member_id|u.nickName|u.user_code','like','%'.$query['search'].'%');
        if(!empty($query['tr_number'])){
            $express_num = str_replace("\r\n","\n",trim($query['tr_number']));
            $express_num = explode("\n",$express_num);
            $express_num = implode(',',$express_num);
            $where['t_order_sn'] = array('in', $express_num);
            $this->where($where);
        }
        return $this;
    }
    
    // 获取面单相关数据
    public function getExpressData($id){
        $data = $this->with(['address','storage'])->find($id);
        if ($data['member_id']){
            $data['member'] = (new UserModel())->find($data['member_id']);
        }
        return $data;
    }
    
    // 批量获取面单相关数据
    public function getExpressBatchData($ids){
        $data = $this->with(['address','storage'])->whereIn('id',$ids)->select();
        $BarCodeService = new BarCodeService();
        foreach ($data as $k => $val){
             if ($val['member_id']){
                $data[$k]['member'] = (new UserModel())->find($val['member_id']);
             }
             $code = $val['t_order_sn']??0;
             if (!file_exists('barcode/'.$code.'.png')){
                 $codeRes = $BarCodeService->Generator($code);
             }
             $codeUrl = 'barcode/'.$code.'.png';
             $data[$k]['codeurl'] = $codeUrl;
        }
        return $data;
    }
    
    public function remove(){
         return $this->save(['status'=>-1]);
    }
    
    public function removedelete(){
         return $this->save(['is_delete'=>1]);
    }
   
    public function line(){
        return $this->belongsTo('line','line_id');
    }
    
    public function address(){
        return $this->belongsTo('app\api\model\UserAddress','address_id');
    }
    
    public function storage(){
        return $this->belongsTo('app\store\model\store\Shop','storage_id');
    }
    
    public function shop(){
        return $this->belongsTo('app\store\model\store\Shop','shop_id');
    }
    
    public function batch(){
        return $this->belongsTo('app\store\model\Batch');
    }
    /**
     * 获取订单总量
     * @param null $day
     * @return string
     * @throws \think\Exception
     */
    public function getPayOrderTotal($startDate = null, $endDate = null)
    {
        $filter = [
            'is_delete' => 0,
            'is_pay' => 1
        ];    
        if (!is_null($startDate) && !is_null($endDate)) {
              $filter['pay_time'] = ["between time",[$startDate, date('Y-m-d',strtotime($endDate)+86400)]];   
        }
        return $this
            ->where('status', 'in', [3,4,5,6,7,8])
            ->where($filter)
            ->count('id');
    }
    
    // 统计成交额
    public function getPackageTotalPrice($startDate = null, $endDate = null){
 
        if (!is_null($startDate) && !is_null($endDate)) {
            $map['pay_time'] = ["between time",[$startDate, date('Y-m-d',strtotime($endDate)+86400)]];
        }
        return $this->where('is_pay', '=', 1)
            ->where($map)
            ->where('status', '>', 2)
            ->where('is_delete', '=', 0)
            ->sum('real_payment');
    }
    
    /**
     * 获取已付款订单总数 (可指定某天)
     * @param null $startDate
     * @param null $endDate
     * @return int|string
     * @throws \think\Exception
     */
    public function getPayPackageTotal($startDate = null, $endDate = null)
    {
        $filter = [
            'is_pay' => 1,
            'status' => ['<>','-1'],
        ];
        if (!is_null($startDate) && !is_null($endDate)) {
            $filter['pay_time'] = ["between time",[$startDate, date('Y-m-d',strtotime($endDate)+86400)]];
        }
        return $this->where($filter)
            ->where('is_delete', '=', 0)
            ->count();
    }
    
        
    // 下单用户数统计
    public function getPayPackageUserTotal($day){
         $filter = [
            'is_delete' => 0,
        ];
        if (!is_null($day) && !is_null($day)) {
            $filter['pay_time'] = ["between time",[$day, date('Y-m-d',strtotime($day)+86400)]];
        }
        $userIds = $this->distinct(true)
             ->where('status', '>', 2)
            ->where($filter)
            ->column('member_id');
        return count($userIds);
    }
    
        /**
     * 获取某天的总销售额
     * @param null $startDate
     * @param null $endDate
     * @return float|int
     */
    public function getOrderTotalPrice($startDate = null, $endDate = null)
    {
        $filter = [
            'is_delete' => 0,
            'is_pay' => 1
        ];
        if (!is_null($startDate) && !is_null($endDate)) {
            $filter['pay_time'] = ["between time",[$startDate, date('Y-m-d',strtotime($endDate)+86400)]];        
        }
        return $this->where($filter)->sum('real_payment');
    }
}
