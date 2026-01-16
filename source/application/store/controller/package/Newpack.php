<?php
namespace app\store\controller\package;

use app\store\controller\Controller;
use app\store\model\Package;
use app\store\model\PackageItem;
use app\store\model\Category;
use app\store\model\Express;
use app\store\model\Line;
use app\store\model\PackageService;
use app\store\model\Countries;
use app\store\model\Shelf;
use app\store\model\ShelfUnit;
use app\store\model\ShelfUnitItem;
use app\store\model\store\Shop as ShopModel;
use app\store\model\User;
use app\store\model\Inpack;
use app\store\model\UserAddress;
use app\store\model\Comment;
use app\api\model\Logistics;
use app\store\model\PackageImage;
use app\common\model\Setting as SettingModel;
use think\Db;
use app\common\service\Email;
use app\common\model\setting;
use app\store\model\InpackService as InpackServiceModel;
/**
 * 包裹控制器
 * Class Package
 * 2023年5月10日 新增newpack板块用于对旧版程序的优化吗，规范格式
 * @package app\store\controller
 */
class Newpack extends Controller
{
    
    /*
     * 查询包裹，用于后台录入包裹前先查询包裹信息
     * 2023年5月10日
     */
    public function findexpress(){
        $param = $this->request->param();
        $Package = new Package;
        $detail = $Package->getNumber($param['number']);
        if(!empty($detail)){
            return $this->renderSuccess('操作成功','',$detail);
        }
        return $this->renderError('没有雷同的单号');
    }
    
    
    /**
     * 后台手动后台录入
     * 后台【包裹管理】【后台录入】
     * @param $id
     * @return list
     * @throws \think\Exception
     */
    public function addpackage(){
        $Package = new Package;
        $Countries = new Countries;
        $CategoryModel = new Category;
        $Shelf = new Shelf;
        $ShelfUnit = new ShelfUnit;
        //所有国家列表
        $countryList = $Countries->getListAll();
        //系统设置参数
        $set = Setting::detail('store')['values'];
        $adminsetting = Setting::detail('adminstyle')['values'];  //电脑端设置
        $printsetting = Setting::detail('printer')['values'];  //打印机设置
        $line = (new Line())->getListAll([]);
        $category = $CategoryModel->getAll()['tree'];
        if(!empty($category)){
            foreach ($category as $key){
                if(!isset($key['child'])){
                     return $this->renderError('分类设置有误,请设置两级菜单，不可单独一级菜单'); 
                }
             }
        }
        $shopList = ShopModel::getAllList();
        if($this->store['user']['shop_id']>0){
            $shopList = (new ShopModel())->where('shop_id',$this->store['user']['shop_id'])->select();
        }
        // dump($this->store);die;
        $expressList = Express::getAll();
        $shelf = [];
        if(count($shopList)>0){
            //货架数据
            $shelf = $Shelf->where('ware_no',$shopList[0]['shop_id'])->select();
        }
        if(isset($shelf) && count($shelf)>0){
            if(isset($detail['shelfunititem'])){
                $shelfid = $detail['shelfunititem']['shelfunit']['shelf']['id'];
                $shelfitem = $ShelfUnit->where('shelf_id',$shelfid)->select();
            }
        }
        //获取当天的包裹列表
        $map['start_time'] = date("Y-m-d",time());
        $map['end_time'] = date("Y-m-d",time() + 86400);
        $map['status'] = [2,3,4,5];
        $list = $Package->getList($map);
        if (!$this->request->isAjax()){
            return $this->fetch('package/index/newadd', compact('data','list','shopList','shelf','shelfitem','category','expressList','countryList','set','line','adminsetting','printsetting'));
        }
    }
    
    /**
     * 后台手动后台录入
     * 后台【包裹管理】【后台录入】
     * @param $id
     * @return list
     * @throws \think\Exception
     */
    public function savepackage(){
        $param = $this->request->param();
        // dump($param);die;
        $param = $param['data'];
        
        $Package = new Package;
        $noticesetting = SettingModel::getItem('notice');
        $User = new User;
        if(!empty($param['user_code'])){
            $userData = $User->where('user_code',$param['user_code'])->find();
            $param['member_id'] = $userData['user_id'];
        }
        if(!empty($param['member_id'])){
            $userData = $User->where('user_id',$param['member_id'])->find();
            $param['member_id'] = $userData['user_id'];
        }
        $set = Setting::detail('store')['values'];
        $detail = $Package->getNumber($param['express_num']);
        $num = 0;
        if(!empty($detail)){
             $j = 0;
             $num = array_sum($param['num']);
        //   dump(!empty($param['member_id']));die;
             $detail->save(['is_delete'=>1]);
             foreach ($param['num'] as $key => $val){
                 for ($i = 0; $i < $val; $i++) {
                     $data['express_num'] = $num==1?$param['express_num']:$param['express_num'].'-'.($j+1);
                     $data['width'] = !empty($param['width'])?$param['width'][$key]:$detail['width'];
                     $data['height'] = !empty($param['height'])?$param['height'][$key]:$detail['height'];
                     $data['length'] = !empty($param['length'])?$param['length'][$key]:$detail['length'];
                     $data['weight'] = !empty($param['weight'])?$param['weight'][$key]:$detail['weight'];
                     $data['member_id'] = !empty($param['member_id'])?$param['member_id']:$detail['member_id'];
                     $data['storage_id'] = !empty($param['storage_id'])?$param['storage_id']:$detail['storage_id'];
                     $data['country_id'] =  !empty($param['country_id'])?$param['country_id']:$detail['country_id'];
                     $data['express_id'] = !empty($param['express_id'])?$param['express_id']:$detail['express_id'];
                     $data['usermark'] = !empty($param['mark'])?$param['mark']:$detail['usermark'];
                     $data['status'] = 2;  //已入库
                     $data['is_take'] = (!empty($param['member_id']) || !empty($detail['member_id']))?2:1;
                     $data['source'] = 2; //电脑后台录入
                     $data['pack_attr'] = !empty($param['pack_attr'])?$param['pack_attr']:$detail['pack_attr']; //电脑后台录入
                     $data['entering_warehouse_time'] = getTime();
                     $data['created_time'] = getTime();
                     $data['updated_time'] = getTime();
                     $data['wxapp_id'] = $this->getWxappId();
                     $data['num'] = array_sum($param['num']);
                     $data['remark'] =!empty($param['remark'])?($detail['remark'].$param['remark']):$detail['remark'];
                     $package_id = $Package->insertGetId($data);
                     $arraypack[$j] = $package_id;
                     $j +=1;
                     //处理货架工作
                    if(!empty($param['shelf_unit_id'])){
                        $shelf = [
                            'shelf_unit_id'=> $param['shelf_unit_id'],
                            'express_num' => $param['express_num'],
                            'user_id' => $param['member_id'],
                        ];
                        $Package->saveshelf($shelf,$package_id);
                    }
                    //处理包裹图片
                    if(!empty($param['package_image_id']) && count($param['package_image_id'])>0){
                        $images = $param['package_image_id'];
                        $Package->savaImage($images,$package_id);
                    }
                    
                    $class = [
                        'class_ids'=>$param['class_ids'],
                        'width'=> $param['width'][$key], 
                        'height'=> $param['height'][$key],   
                        'length'=> $param['length'][$key],   
                        'weight'=> $param['weight'][$key],   
                        'product_num'=> $param['product_num'][$key],   
                        'one_price'=> $param['one_price'][$key],   
                        'goods_name'=> $param['goods_name'][$key],   
                        'volumeweight'=> $param['volume'][$key],
                        'volume'=>$data['width']*$data['height']*$data['length']/1000000,
                    ];
                    $Package->doClassIdstwo($class,$data['express_num'],$package_id);
                    //发送订阅消息以及模板消息
                    //  if(isset($param['member_id']) || !empty($detail['member_id'])){
                    //       $EmailUser = User::detail($data['member_id']);
                    //       $shopData =  (new ShopModel())->where('shop_id',$data['storage_id'])->find();
                          
                    //       $data['member_name'] = $EmailUser['nickName'];
                    //       $data['shop_name'] = !empty($shopData)?$shopData['shop_name']:'未知仓库';
                    //       $data['id'] = $detail['id'];
                    //       $sub = (new package())->sendEnterMessage([$data]);
                    //       if($EmailUser['email']){
                    //           $EmailData['code'] = $detail['express_num'];
                    //           $EmailData['logistics_describe']=$noticesetting['enter']['describe'];
                    //          (new Email())->sendemail($EmailUser,$EmailData,$type=1);
                    //       }
                    //   }
                 }
                
             }
  
             
        }else{
            //处理包裹的保存
            //固定参数
            $j = 0;
            $num = array_sum($param['num']);
            foreach ($param['num'] as $key => $val){
                 if($param['width'][$key]==''){
                     $param['width'][$key]=0;
                 }
                 
                 if($param['height'][$key]==''){
                     $param['height'][$key]=0;
                 }
                 
                 if($param['length'][$key]==''){
                     $param['length'][$key]=0;
                 }
                 
                 if($param['weight'][$key]==''){
                     $param['weight'][$key]=0;
                 }
                 
                 if($param['product_num'][$key]==''){
                     $param['product_num'][$key]=0;
                 }
                 
                 
                 for ($i = 0; $i < $val; $i++) {
                     $data['order_sn'] = createSn();
                     $data['express_num'] = $num==1?$param['express_num']:$param['express_num'].'-'.($j+1);
                     $data['member_id'] = $param['member_id'];
                     $data['storage_id'] = isset($param['storage_id'])?$param['storage_id']:'';
                     $data['country_id'] = isset($param['country_id'])?$param['country_id']:'';
                     $data['express_id'] = isset($param['express_id'])?$param['express_id']:'';
                     $data['usermark'] = isset($param['mark'])?$param['mark']:'';
                     $data['status'] = 2;  //已入库
                     $data['is_take'] = $param['member_id']?2:1;  //已入库
                     $data['source'] = 2; //电脑后台录入
                     $data['pack_attr'] = isset($param['pack_attr'])?$param['pack_attr']:''; //电脑后台录入
                     $data['entering_warehouse_time'] = getTime();
                     $data['created_time'] = getTime();
                     $data['updated_time'] = getTime();
                     $data['wxapp_id'] = $this->getWxappId();
                    //需要计算的参数
                     $data['width'] = $param['width'][$key];
                     $data['height'] = $param['height'][$key];
                     $data['length'] = $param['length'][$key];
                     $data['weight'] = $param['weight'][$key];
                     $data['num'] = $num;
                     $data['volumeweight'] = $param['volume'][$key];
                     $data['volume'] = $param['width'][$key]*$param['height'][$key]*$param['length'][$key]/1000000;
                     $data['remark'] = isset($param['remark'])?$param['remark']:'';
                    if($set['moren']['send_mode']==20 && $set['moren']['is_zhiyou_pack']==1){
                        $data['status'] = 8;  //已入库
                    }
                    
                    $package_id = $Package->insertGetId($data);
                    $arraypack[$j] = $package_id;
                    $j +=1;
                     //处理货架工作
                    if(!empty($param['shelf_unit_id'])){
                        $shelf = [
                            'shelf_unit_id'=> $param['shelf_unit_id'],
                            'express_num' => $param['express_num'],
                            'user_id' => $param['member_id'],
                        ];
                        $Package->saveshelf($shelf,$package_id);
                    }
                    //处理包裹图片
                    if(!empty($param['package_image_id']) && count($param['package_image_id'])>0){
                        $images = $param['package_image_id'];
                        $Package->savaImage($images,$package_id);
                    }
                    
                    $class = [
                        'class_ids'=>$param['class_ids'],
                        'width'=> $param['width'][$key], 
                        'height'=> $param['height'][$key],   
                        'length'=> $param['length'][$key],   
                        'weight'=> $param['weight'][$key],   
                        'product_num'=> $param['product_num'][$key],   
                        'one_price'=> $param['one_price'][$key],   
                        'goods_name'=> $param['goods_name'][$key],   
                        'volumeweight'=> $param['volume'][$key],
                        'volume'=>$data['width']*$data['height']*$data['length']/1000000,
                    ];
                    $Package->doClassIdstwo($class,$data['express_num'],$package_id);
                 }
            }
        }

        
        //如果是直邮模式，包裹状态变更为
         if($set['moren']['send_mode']==20 &&  $set['moren']['is_zhiyou_pack']==1){

            //处理货架工作
            if(!empty($param['shelf_unit_id'])){
                $shelf = [
                    'shelf_unit_id'=> $param['shelf_unit_id'],
                    'express_num' => $param['express_num'],
                    'user_id' => $param['member_id'],
                ];
                $Package->saveshelf($shelf,$package_id);
            }
            //处理包裹图片
            if(!empty($param['package_image_id']) && count($param['package_image_id'])>0){
                $images = $param['package_image_id'];
                $Package->savaImage($images,$package_id);
            }
            //如果存在member_id,就获取用户默认地址
            $userda = $User::detail($param['member_id']);
            // dump($param['member_id']);die;
            if(empty($userda) || count($userda['address'])==0){
                 return $this->renderError('用户收货地址未填写，请先添加收货地址');
            }
            // dump($userda);die;
            $Inpack = new Inpack;
            $zinpackOrder = [
              'order_sn' => $param['express_num'],
              'remark' => isset($param['remark'])?$param['remark']:'',
              'pack_ids' => implode(',',$arraypack),
              'line_id' => isset($param['line_id'])?$param['line_id']:'',
              'inpack_type'=>2,
              'storage_id' => isset($param['storage_id'])?$param['storage_id']:'',
              'shop_id' => isset($param['shop_id'])?$param['shop_id']:'',
              'address_id' => isset($userda['address'])?$userda['address'][0]['address_id']:'',
              'free' => 0,
              'weight' => !empty($param['weight'])?$param['weight']:0,
              'width' =>  !empty($param['width'])?$param['width']:0,
              'length' => !empty($param['length'])?$param['length']:0,
              'height'=> !empty($param['height'])?$param['height']:0,
              'cale_weight' => 0,
              'volume' => 0, //体积重
              'pack_free' =>  0,
              'other_free' => 0,
              'member_id' => $param['member_id'],
              'country' => isset($address['country'])?$address['country']:'',
              'pay_type'=> $userda['paytype'],
              'created_time' => getTime(),
              'updated_time' => getTime(),
              'status' => $set['moren']['pack_in_status'],
              'source' => 4, // 直邮订单
              'wxapp_id' => (new Package())->getWxappId(),
            ];
                // dump($zinpackOrder);die;
            $inpackres = $Inpack->where(['order_sn'=>$param['express_num'],'is_delete'=>0])->find();
         
            if(!empty($inpackres)){
                $inpackres->save($zinpackOrder);
            }else{
                $reus = $Inpack->save($zinpackOrder);
                // dump($reus);die;
            }
         }
       
        return $this->renderSuccess('操作成功');  
   
    }
    
}