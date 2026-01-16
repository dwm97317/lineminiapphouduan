<?php
namespace app\web\controller;
use app\web\model\Express;
use app\web\model\store\Shop;
use app\web\model\Country;
use app\web\model\Category;
use app\web\model\Inpack;
use app\web\model\Line;
use app\web\model\Logistics;
use app\web\model\Package as PackageModel;
use app\web\model\PackageItem as PackageItemModel;
use app\web\model\User;
use app\web\model\user\BalanceLog;
use app\common\enum\user\balanceLog\Scene as SceneEnum;
use app\web\model\UserAddress;
use app\web\model\store\Shop as ShopModel;
use app\web\model\store\shop\Clerk;
use app\web\model\Setting as SettingModel;
use app\common\library\EmsService\Ems;
use app\common\library\Pinyin; 
use think\Db;
use app\common\service\Message;
use app\store\model\Countries;

/**
 * web
 * Class Index
 * @package app\web\controller
 */
class Package extends Controller
{
    
    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
    }
    
    public function addAddress(){
        $Countries = (new Countries());
        $countryList =$Countries->getListAll();
        $setting = SettingModel::getItem('store');
        // dump($setting);die;
         if(!$this->request->isAjax()){
            return $this->fetch('package/addAddress',compact('countryList','setting')); 
         }
         $data = $this->request->param();
         $countryData = $Countries->details($data['address']['country_id']);
         $data['address']['country'] = $countryData['title'];
         $model = new UserAddress();
         $user = $this->user['user'];
         if($model->add($user,$data['address'])){
            return $this->renderSuccess('添加成功');
         }
        return $this->renderError($model->getError()??'添加失败');
      
    }
    
    public function mypackage(){
        $package = (new PackageModel());
        $field = 'id,usermark,country_id,order_sn,member_id,storage_id,express_num,status,created_time,pack_free,free,weight,express_name,length,height,width,remark,express_id';
        $param = $this->request->param();
        $where = [];    
        isset($param['number']) && $where['express_num'] = $param['number'];
        $where['member_id']= $this->user['user']['user_id'];
        if(isset($param['type'])){
            switch ($param['type']) {
                case 1:
                    $where['member_id']= $this->user['user']['user_id'];
                    break;
                case 2:
                    $where['status'] = 1;
                    $where['member_id']= $this->user['user']['user_id'];
                    break;
                case 3:
                    $where['member_id']= $this->user['user']['user_id'];
                    $package->where('status','in',[2,3,4,7]);
                    break;
                case 4:
                   $where['status'] = 8;
                   $where['member_id']= $this->user['user']['user_id'];
                    break;
                case 5:
                   $where['status'] = 9;
                   $where['member_id']= $this->user['user']['user_id'];
                    break;
                case 6:
                   $where['status'] = 10;
                   $where['member_id']= $this->user['user']['user_id'];
                    break;
                case 7:
                  $where['status'] = 11;
                  $where['member_id']= $this->user['user']['user_id'];
                    break;
                default:
                    // code...
                    break;
            }
        }
        $line = (new Line())->getListAll([]);
        $address =(new UserAddress())->getList($this->user['user']['user_id'],'');
        
        $list = $package->query($where,$field);
        // dump($list->toArray());die;
        foreach ($list as $key=>$value){
            $packageitem = (new PackageItemModel())->whereIn('order_id',$value['id'])->find();
            $value['class_name'] = $packageitem['class_name'];
            $value['all_price'] = $packageitem['all_price'];
            $value['all_weight'] = $packageitem['all_weight'];
        }
        $count['count1'] = $package->where(['member_id' => $this->user['user']['user_id'],'is_delete'=>0])->count();
        $count['count2'] = $package->where(['member_id' => $this->user['user']['user_id'],'is_delete'=>0,'status'=>1])->count();
        $count['count3'] = $package->where(['member_id' => $this->user['user']['user_id'],'is_delete'=>0])->where('status','in',[2,3,4,7])->count();
        $count['count4'] = $package->where(['member_id' => $this->user['user']['user_id'],'is_delete'=>0,'status'=>8])->count();
        $count['count5'] = $package->where(['member_id' => $this->user['user']['user_id'],'is_delete'=>0,'status'=>9])->count();
        $count['count6'] = $package->where(['member_id' => $this->user['user']['user_id'],'is_delete'=>0,'status'=>10])->count();
        $count['count7'] = $package->where(['member_id' => $this->user['user']['user_id'],'is_delete'=>0,'status'=>-1])->count();
        // $list = $this->getPackageItemList($data);
        
        if(!$this->request->isAjax()){
           return $this->fetch('package/mypackage',compact('list','count','line','address'));
        }
        
        // $list = $package->query($where,$field);
        // dump($list->render());die;
        return $this->renderSuccess(compact('list'),'添加成功');
    }
    
    // 包裹数据
     public function getPackageItemList($data){
        $orderItem = [];
        foreach($data as $k => $v){
            $orderItem[] = $v['id'];
        }
        $orderIdItem = [];
        $orderItemList = (new PackageItemModel())->whereIn('order_id',$orderItem)->select();
        // dump($orderItemList);die;
        if ($orderItemList->isEmpty()){
            return $data;
        }
        foreach ($orderItemList as $v){
            $orderIdItem[$v['order_id']][] = $v->toArray();
        }
        foreach($data as $k =>$v){
            if (isset($orderIdItem[$v['id']]))
                $data[$k]['class_name'] = implode(',',array_column($orderIdItem[$v['id']],'class_name'));
        }
        return $data;
     }
    
    public function packreport(){
        $package = (new PackageModel());
        $shopList = (new Shop)->getQueryList();
        $country = (new Country)->getListAllCountry();
        $express = Express::getAll();
        $category = Category::getAllSubCategory();
        // dump($category->toArray());die;
        if(!$this->request->isAjax()){
            return $this->fetch('package/packreport',compact('shopList','country','express','category'));
        }
        $param = $this->request->param()['yubao'];

        $data['user_id'] = $this->user['user']['user_id'];
        $data['wxapp_id'] = $this->wxapp_id;
        foreach ($param['express_num'] as $key =>$value){
            if(empty($param['express_num'][$key])){
                return $this->renderError('单号不能为空');     
            }
            $packdata = $package->where('express_num',$param['express_num'][$key])->find();
            if(!empty($packdata)){
                return $this->renderError('单号不可重复预报');     
            }
            $data['express_num'] = $param['express_num'][$key];
            $data['express_id'] = $param['express_id'][$key];
            $data['is_take'] = isset($data['member_id'])?2:1;
            $data['remark'] = isset($param['remark'][$key])?$param['remark'][$key]:'';
            $id = $package->created($data);
            
            if($id){
                $packItemModel = new PackageItemModel();
                $classItem = [];
                $packItemModel->where('order_id',$id)->delete();
                $classItems = $this->parseClass($param['class_id'][$key]);
                
                $classItem['goods_name'] = $param['goods_name'][$key];
                $classItem['express_name'] = '';
                $classItem['class_name'] = isset($classItems[0])?$classItems[0]['name']:'';
                $classItem['class_id'] = isset($classItems[0]['category_id'])?$classItems[0]['category_id']:0;
                $classItem['express_num'] = $param['express_num'][$key];
                $classItem['product_num'] = isset($param['product_num'][$key])?$param['product_num'][$key]:1;
                $classItem['one_price'] = isset($param['one_price'][$key])?$param['one_price'][$key]:1;
                $classItem['order_id'] = $id;
                $packItemRes = $packItemModel->save($classItem);
            }
        }
         return $this->renderSuccess(Url('package/packreport'),'添加成功');
    }

    
    public function jaddAddress(){
        $Countries = (new Countries());
        $countryList =$Countries->getListAll();
        $setting = SettingModel::getItem('store');
         if(!$this->request->isAjax()){
            return $this->fetch('package/jaddAddress',compact('countryList','setting')); 
         }
         $data = $this->request->param();
         $countryData = $Countries->details($data['address']['country_id']);
         $data['address']['country'] = $countryData['title'];
         $model = new UserAddress();
         $user = $this->user['user'];
         if($model->add($user,$data['address'])){
            return $this->renderSuccess('添加成功');
         }
        return $this->renderError($model->getError()??'添加失败');
      
    }
    
    //新建订单
    public function gotocreateorder(){
       $shopList = ShopModel::getAllList(); 
       $expresslist = Express::getAll();
       $line = (new Line())->getListAll([]);
       $countryList = (new Countries())->getListAll();
       $user = $this->user;
       $model = new UserAddress();
       $address = $model->getList($user['user']['user_id']);
       $jaddress = $model->getjList($user['user']['user_id']);
    //   dump($address);die;
       return $this->fetch('package/gotocreateorder',compact('expresslist','shopList','line','countryList','address','jaddress'));
        
    }
    
    //包裹列表
    public function index(){
        $field = 'id,country_id,order_sn,member_id,storage_id,express_num,status,created_time,pack_free,free,weight,express_name,length,height,width,remark,express_id';
        if(!empty(input('express_num'))){
            $where = [
                'member_id' => $this->user['user']['user_id'],
                'express_num' => input('express_num'),
                'status'=>1
            ];
        }else{
            $where = [
              'member_id' => $this->user['user']['user_id'],
              'status'=>1
            ]; 
        }         
        $data = (new PackageModel())->query($where,$field);
            //  dump($data->toArray());die;
        $list = $this->getPackItemList($data);
   
        return $this->fetch('package/index',compact('list'));
    }
    
        
    //待认领
    public function waitrl(){
        $PackageModel = new PackageModel;
        $field = 'id,country_id,order_sn,member_id,storage_id,express_num,status,created_time,pack_free,free,weight,express_name,length,height,width,remark,express_id,entering_warehouse_time';
        if(!empty(input('express_num'))){
            $where = [
                'express_num' => input('express_num'),
                'is_take'=>1    
            ];
        }else{
            $where = [
             'is_take'=>1    
            ]; 
        }     
        $list = $PackageModel->query($where,$field);
        foreach($list as $k => $v){
              $list[$k]['express_num'] = func_substr_replace($v['express_num'],'*',4,5);
         }
        return $this->fetch('package/waitrl',compact('list'));
    }
    
    //认领包裹
    public function renling(){
       $param = $this->request->param();
      
       $user = $this->user;
       $PackageModel = new PackageModel;
       if(!isset($param['id'])){
            return $this->renderError('数据有误');
       }
       if(!isset($param['express_num'])){
            return $this->renderError('包裹单号不能为空');
       }
       $detail = $PackageModel->getDetails($param['id'],'*');
       if(!empty($detail) && ($detail['express_num']==$param['express_num'])){
            $detail->save([
                'is_take'=>2,
                'member_id'=>$user['user']['user_name']
            ]);
            return $this->renderSuccess('认领成功');
       }
       return $this->renderError('认领失败');
    }
    
    
    //包裹列表
    public function search(){
        $field = 'id,country_id,order_sn,member_id,storage_id,express_num,status,created_time,pack_free,free,weight,express_name,length,height,width,remark,express_id';
        if(!empty(input('express_num'))){
            $where = [
                'number' => input('express_num') 
            ];
        }        
        $data = (new PackageModel())->query($where,$field);
        // dump($data);die;
        $list = $this->getPackItemList($data);
        return $this->renderSuccess($list);
    }
    
    //国际单号列表
    public function searchOrder(){
        if(!empty(input('express_num'))){
            $where = [
                'member_id' => $this->user['user']['user_id'],
                't_order_sn' => input('express_num'),
                'status' => [6,7,8],
            ];
        }        
        $list = (new Inpack())->getList($where);
        return $this->renderSuccess($list);
    }
    
    public function pricedetail($id){
        $LineModel = new Line();
        $data = $LineModel->where('id',$id)->find();
        $result = [];
        $data['free_rule'] = json_decode($data['free_rule'],true);
        if($data['free_mode']==4 || $data['free_mode']==3){
            foreach ($data['free_rule'] as $key=> $val){
                !isset($val['weight_unit']) && $val['weight_unit'] = 1;
                $result[] = $val;
            }
            $data['free_rule']  = $result;
        }
        
        $free_mode = [1=>'阶梯计费',2=>'首/续重模式',3=>'范围区间计费',4=>'重量区间计费'];
       
        // dump($result);die;
        $country = [];
        $category = [];
        $country = (new Countries())->where('status','=',1)->select();
        $category = (new Category())->where('parent_id','<>',0)->select();
        $countryId = array_column($country->toArray(),null,'id');
        $categoryId = array_column($category->toArray(),null,'category_id');

        $country_text = []; //城市
        $category_text = [];//分类
        if ($data['countrys']){
            $modelCountryIds = explode(',',$data['countrys']);
            foreach ($modelCountryIds as $value) {
                $cres = (new Countries())->where('id',$value)->where('status',1)->find();
                if(!empty($cres)){
                    $country_text[] = $countryId[$value];
                }
                
            }
        }

        if ($data['categorys']){
            $modelCategoryIds = explode(',',$data['categorys']);
            foreach ($modelCategoryIds as $value) {
                $category_text[] = $categoryId[$value];
            }
        }
          
        $data['country'] = $country_text;
        $data['category'] = $category_text;
        $data['free_mode'] = $free_mode[$data['free_mode']];
        // dump($data->toArray());die;
        return $this->fetch('order/pricedetail',compact('data'));
    }
    
    //已经入库的包裹
    public function inpackage(){
        $field = 'id,country_id,order_sn,member_id,storage_id,express_num,status,created_time,pack_free,free,weight,express_name,length,height,width,remark,express_id';
        if(!empty(input('express_num'))){
            $where = [
                'member_id' => $this->user['user']['user_id'],
                'express_num' => input('express_num'),
                'status'=>2
            ];
        }else{
            $where = [
              'member_id' => $this->user['user']['user_id'],
              'status'=>2
            ]; 
        }      
        $data = (new PackageModel())->query($where,$field);
        $address =(new UserAddress())->getList($this->user['user']['user_id'],'');
        $list = $this->getPackItemList($data);
        $line = (new Line())->getListAll([]);
        // dump($list->toArray());die;
        return $this->fetch('package/inpackage',compact('list','line','address'));
    }
    
    //未发货
    public function orderno(){
        $where = [
          'is_delete' => 0,
          'member_id' => $this->user['user']['user_id'],
          'status' => [1,2,3,4,5],
        ];
        $list = (new Inpack())->getList($where);
        // dump($data);die;
        return $this->fetch('order/orderno',compact('list'));
    }
    
    //已发货
    public function orderyes(){
      
        $where = [
          'is_delete' => 0,
          'member_id' => $this->user['user']['user_id'],
          'status' =>6,
        ];
        $list = (new Inpack())->getList($where);
        return $this->fetch('order/orderyes',compact('list'));
    }
    
    //已到货订单
    public function sended(){
        $where = [
          'is_delete' => 0,
          'member_id' => $this->user['user']['user_id'],
          'status' => 7,
        ];
        $list = (new Inpack())->getList($where);
        // dump($data);die;
        return $this->fetch('order/sended',compact('list'));
    }
    
    //已完成
    public function complete(){
        $where = [
          'is_delete' => 0,
          'member_id' => $this->user['user']['user_id'],
          'status' => 8,
        ];
        $list = (new Inpack())->getList($where);
        // dump($data);die;
        return $this->fetch('order/complete',compact('list'));
    }
    
    
    
    
    
    //草稿订单
    public function draft(){
        $where = [
          'is_delete' => 0,
          'member_id' => $this->user['user']['user_id'],
          'status' => 10,
        ];
        $list = (new Inpack())->getList($where);
        // dump($data);die;
        return $this->fetch('order/draft',compact('list'));
    }
    
    //所有订单
    public function allorder(){
        $where = [
          'is_delete' => 0,
          'member_id' => $this->user['user']['user_id'],
        //   'status' => [1,2,3,4,5],
        ];
        $list = (new Inpack())->getList($where);
        // dump($data);die;
        return $this->fetch('order/allorder',compact('list'));
    }
    
    //包裹物流轨迹查询
    public function trajectory(){
        if(!$this->request->isAjax()){
            return $this->fetch('order/trajectory');
        }
        $express =input('express_name');
        $sn = (new Inpack())->getInpackTroder($express);
                // dump($sn);die;
        if ($sn){
            $pack = (new PackageModel())->where('id','in',explode(',',$sn['pack_ids']))->find();
            $list = (new Logistics())->getList($pack['express_num']);
        }else{
            $list = (new Logistics())->getList($express); 
        }
        return $this->renderSuccess($list);
    }
    
    //价格查询
    public function price(){
        $countryList = (new Countries())->getListAll();
        if(!$this->request->isAjax()){
          return $this->fetch('order/price',compact('countryList'));  
        }
        $data = $this->request->param();
        $free = getsearchfree($data);
        // dump($free);die;
        // 1阶梯计费 2首续重 3 区间计费 4 重量区间计费)
        $mode = [ 1=>'阶梯计费',2=>'首续重模式',3=>'范围区间计费',4=>'重量区间计费'];
        foreach ($free as $key=>$val){
            // dump($val['free_mode']);die;
            $free[$key]['free_mode'] = $mode[$val['free_mode']] ;
        }
       return $this->renderSuccess($free);
    }
    
    public function yubao(){
        $package = (new PackageModel());
        $shopList = (new Shop)->getQueryList();
        $country = (new Country)->getListAllCountry();
        $express = Express::getAll();
        $category = Category::getCacheTree();
        if(!$this->request->isAjax()){
            return $this->fetch('package/yubao',compact('shopList','country','express','category'));
        }
        $data = $this->request->post()['yubao'];
        $data['user_id'] = $this->user['user']['user_id'];
        $data['wxapp_id'] = $this->wxapp_id;
        $id = $package->created($data);
        if($id){
            if (isset($data['categoryIds']) && !empty($data['categoryIds'])){
                $packItemModel = new PackageItemModel();
                $classItem = $this->parseClass($data['categoryIds']);
                $packItemModel->where('order_id',$id)->delete();
                foreach ($classItem as $k => $val){
                        $classItem[$k]['class_id'] = $val['category_id'];
                        $classItem[$k]['express_name'] = '';
                        $classItem[$k]['class_name'] = $val['name'];
                        $classItem[$k]['express_num'] = $data['express_num'];
                        unset($classItem[$k]['category_id']); 
                        unset($classItem[$k]['name']);        
                }
                $packItemRes = $packItemModel->saveAllData($classItem,$id);
            }
            return $this->renderSuccess('添加成功',Url('package/index'));
        }
        return $this->renderError($package->getError()??'预报失败');
    }
    
    //获取用户地址详情
    public function getuseraddress(){
        $user = $this->user;
        $data = $this->request->param();
        // dump($data);die;
        $addr = UserAddress::detail($user['user']['user_id'],$data['address_id']);
        if(empty($addr)){
            return $this->renderError('添加失败');
        }
        return $this->renderSuccess($addr,'添加成功');
    }
    //保存草稿
    public function createcgorder(){
        $data = $this->request->param();
      $UserAddress = new UserAddress;
      $packageModel = (new PackageModel());
      //获取用户信息
      $user = $this->user;
      //创建包裹
      if(empty($data['order_no'])){
          $pack = [
            'order_sn'=>createSn(),
            'express_num' => createPCsn(),
            'member_id' => $user['user']['user_id'],
            'created_time' => date("Y-m-d H:i:s",time()),
            'updated_time' => date("Y-m-d H:i:s",time()),
            'wxapp_id' =>$this->wxapp_id,
            'remark' => $data['remark'],
            'express_id' =>'',
            'storage_id'=>'',
            'source' => 5,
            'is_take' => 2,
            'country_id'=>$data['address']['country_id'],
            'price' => 0,
          ];
         $ids = $packageModel->insertGetId($pack);
      }
      //包裹包裹里边的信息
      
      //保存地址信息,判断是否存在address_id
      if(empty($data['address']['address_id']) && !empty($data['address']['country_id'])){
           $addres = $data['address'];
           $addres['addressty'] = 0;
           $UserAddress->add($user['user'],$addres);
      }
      
      if(empty($data['jaddress']['address_id']) && !empty($data['jaddress']['country_id'])){
          $jaddress = $data['jaddress'];
          $jaddress['addressty'] = 1;
          $addres = $UserAddress->add($user['user'],$jaddress);
      }
      //创建订单
     
      $result = [
          'order_sn' => createSn(),
          'remark' =>$data['remark'],
          'pack_ids' => json_encode($ids), //零时创建
          'pack_services_id' => '',
          'storage_id' => '',
          'address_id' => $data['address']['address_id'],
          'free' => 0,
          'weight' =>0,
          'cale_weight' =>0,
          'volume' => 0,
          'pack_free' => 0,
          'status' => 10,
          'member_id' => $user['user']['user_id'],
          'country' => $data['address']['country'],
          'created_time' => getTime(),
          'updated_time' => getTime(),
          'status' => 1,
          'line_id' => $data['line_id'],
          'operate_id' => 0,
          'wxapp_id' => $this->wxapp_id,
        ];
  
        $inpack = (new Inpack())->insertGetId($result); 
        return $this->renderSuccess('添加成功',Url('package/index'));
    }
    
    //保存新建的订单
    public function createorder(){
      $data = $this->request->param();
      $UserAddress = new UserAddress;
      $packageModel = (new PackageModel());
      //获取用户信息
      $user = $this->user;
      //创建包裹
      $pack = [
        'order_sn'=>createSn(),
        'express_num' => empty($data['order_no'])?createPCsn():$data['order_no'],
        'member_id' => $user['user']['user_id'],
        'created_time' => date("Y-m-d H:i:s",time()),
        'updated_time' => date("Y-m-d H:i:s",time()),
        'wxapp_id' =>$this->wxapp_id,
        'remark' => $data['remark'],
        'express_id' =>'',
        'storage_id'=>'',
        'source' => 5,
        'is_take' => 2,
        'country_id'=>$data['address']['country_id'],
        'price' => 0,
      ];
      $ids = $packageModel->insertGetId($pack);
      $packItemModel = new PackageItemModel();
      //包裹包裹里边的信息
    //   dump($data['goods']);die;
      if(!empty($ids)){
          $classItem = [];
          foreach ($data['goods']['cnname'] as $k => $val){
                $classItem[$k]['express_num'] = empty($data['order_no'])?createPCsn():$data['order_no'];
                $classItem[$k]['order_id'] = $ids;
                $classItem[$k]['class_name'] = $data['goods']['cnname'][$k];
                $classItem[$k]['class_name_en'] = $data['goods']['enname'][$k];
                $classItem[$k]['distribution'] = $data['goods']['peihuo'][$k];
                $classItem[$k]['unit_weight'] = $data['goods']['oneweight'][$k];
                $classItem[$k]['product_num'] = $data['goods']['num'][$k];
                $classItem[$k]['all_price'] = $data['goods']['price'][$k];
                $classItem[$k]['customs_code'] = $data['goods']['haiguannum'][$k];
            }
            // dump($classItem);die;
            $packItemRes = $packItemModel->saveAllData($classItem,$ids);
      }
      
      
      
      //保存地址信息,判断是否存在address_id
      if(empty($data['address']['address_id']) && !empty($data['address']['country_id'])){
           $addres = $data['address'];
           $addres['addressty'] = 0;
           $UserAddress->add($user['user'],$addres);
      }
      
      if(empty($data['jaddress']['address_id']) && !empty($data['jaddress']['country_id'])){
          $jaddress = $data['jaddress'];
          $jaddress['addressty'] = 1;
          $addres = $UserAddress->add($user['user'],$jaddress);
      }
      //创建订单
     
      $result = [
          'order_sn' => createSn(),
          'remark' =>$data['remark'],
          'pack_ids' => json_encode($ids), //零时创建
          'pack_services_id' => '',
          'storage_id' => '',
          'address_id' => $data['address']['address_id'],
          'free' => 0,
          'weight' =>0,
          'cale_weight' =>0,
          'volume' => 0,
          'pack_free' => 0,
          'member_id' => $user['user']['user_id'],
          'country' => $data['address']['country'],
          'created_time' => getTime(),
          'updated_time' => getTime(),
          'status' => 1,
          'line_id' => $data['line_id'],
          'operate_id' => 0,
          'wxapp_id' => $this->wxapp_id,
        ];
  
        $inpack = (new Inpack())->insertGetId($result); 
        return $this->renderSuccess('添加成功',Url('package/index'));
    }
    
    
    public function edit($id){
        $packageModel = (new PackageModel());
        $shopList = (new Shop)->getQueryList();
        $country = (new Country)->getListAllCountry();
        $express = Express::getAll();
        $category = Category::getCacheTree();
        $detail = $packageModel->getDetails($id,'*');

        if (!$this->request->isAjax()) {
            return $this->fetch('edit',compact('detail','shopList','country','express','category'));
        }
        $data = $this->request->post()['package'];
    
        if($packageModel->edit($data,$id)){
            if (isset($data['categoryIds']) && !empty($data['categoryIds'])){
            $packItemModel = new PackageItemModel();
            $classItem = $this->parseClass($data['categoryIds']);
            $packItemModel->where('order_id',$id)->delete();
            foreach ($classItem as $k => $val){
                    $classItem[$k]['class_id'] = $val['category_id'];
                    $classItem[$k]['express_name'] = '';
                    $classItem[$k]['class_name'] = $val['name'];
                    $classItem[$k]['express_num'] = $data['express_num'];
                    unset($classItem[$k]['category_id']); 
                    unset($classItem[$k]['name']);        
            }
            $packItemRes = $packItemModel->saveAllData($classItem,$id);
        }
            return $this->renderSuccess('添加成功',Url('package/index'));
        }
        return $this->renderError($package->getError()??'预报失败');
      
    }
    
     // 提交打包处理
     public function postPack(){
        $ids = $this->postData('package')['selectIds'];
        
        $line_id = $this->postData('package')['line_id'];
        $remark = $this->postData('package')['remark'];
        $address_id = $this->postData('package')['address_id'];
        $waitreceivedmoney = $this->postData('package')['waitreceivedmoney'];
        if (!$ids){
            return $this->renderError('请选择要打包的包裹');
        }
        $idsArr = $ids;
        $pack = (new PackageModel())->whereIn('id',$idsArr)->select();
       
        if (!$pack || count($pack) !== count($idsArr)){
            return $this->renderError('打包包裹数据错误');
        }
        if (!$address_id){
            return $this->renderError('请先选择地址');
        }
        $address = (new UserAddress())->find($address_id);
        if (!$address){
            return $this->renderError('地址信息错误');
        }
        $line = (new Line())->find($line_id);
        if (!$line){
            return $this->renderError('线路不存在,请重新选择');
        }
        $free_rule = json_decode($line['free_rule'],true);
        $price = 0; // 总运费
        $allWeigth = 0;
        $caleWeigth = 0;
        $volumn = 0;
        // dump(implode($ids));die;
        // 创建包裹订单
        $inpackOrder = [
          'order_sn' => createSn(),
          'remark' =>$remark,
          'pack_ids' => implode($ids),
          'pack_services_id' => isset($pack_ids)?$pack_ids:'',
          'storage_id' => $pack[0]['storage_id'],
          'address_id' => $address_id,
          'free' => $price,
          'waitreceivedmoney'=> $waitreceivedmoney,
          'weight' => $allWeigth,
          'cale_weight' => $caleWeigth,
          'volume' => $volumn,
          'pack_free' => 0,
          'member_id' => $this->user['user']['user_id'],
          'country' => $address['country'],
          'created_time' => getTime(),
          'updated_time' => getTime(),
          'status' => 1,
          'line_id' => $line_id,
          'operate_id' => 0,
          'wxapp_id' => $this->wxapp_id,
        ];
       
        $inpack = (new Inpack())->insertGetId($inpackOrder); 
        if (!$inpack){
           return $this->renderError('打包包裹提交失败');
        }
        $res = (new PackageModel())->whereIn('id',$idsArr)->update(['inpack_id'=>$inpack,'status'=>5,'line_id'=>$line_id,'pack_service'=>'','address_id'=>$address_id,'updated_time'=>getTime()]);
        
        $inpackdate = (new Inpack())->where('id',$inpack)->find();
        //更新包裹的物流信息
        //物流模板设置
        $packnum =[];
        $noticesetting = SettingModel::getItem('notice');
        if($noticesetting['packageit']['is_enable']==1){
            foreach ($idsArr as $key => $val){
                $packnum[$key] = (new PackageModel())->where('id',$val)->value('express_num');
                Logistics::addLogPack($val,$inpackdate['order_sn'],$noticesetting['packageit']['describe']);
            }
            //修改包裹的记录
            foreach ($packnum as $ky => $vl){
                Logistics::updateOrderSn($vl,$inpackdate['order_sn']);
            }
        }
 
        //通知仓管员去看看
         $this->user = $this->user['user'];
         $clerk = (new Clerk())->where('shop_id',$pack[0]['storage_id'])->where('mes_status',0)->select();
         if(!empty($clerk)){
         $data=[
            'nickName' => ($this->user)['user_name'],
            'userCode' => ($this->user)['user_code'],
            'countpack' =>count($idsArr),
            'packtime' => getTime(),
            'packid' => $inpack,
            'wxapp_id' => $this->wxapp_id,
            'remark' =>$remark,
          ];
          foreach ($clerk as $key => $val){
                  $data['clerkid'] = $val['user_id'];
                  Message::send('order.packageit',$data);   
          }
         }
        
        if (!$res){
            return $this->renderError('打包包裹提交失败');
        }
        return $this->renderSuccess('打包包裹提交成功');
     }
    
    
    
    
    
    
    
    // 包裹预报
    public function report(){
         $post = $this->request->post();
         $package = (new PackageModel());
         $post['user_id'] = $this->user['user_id'];
         if ($data = $package->created($post)){
             // 中邮EMS 发送订单数据 获取物流单号
             $emsService = (new Ems());
             $emsService->getBarService($data);   
             return $this->renderSuccess('预报包裹成功');
         } else{
           return $this->renderError($package->getError()??'预报失败');
         }
     }
     
     public function subtempate(){
         $values = SettingModel::getItem('submsg');
         $templateid = [];
         foreach ($values['order'] as $v){
             if($v['template_id'])
                $templateid[] = $v['template_id'];
         }
         $values['template_ids'] = $templateid;
         return $this->renderSuccess($values);
     }
     
     // 分类列表
     public function category(){
        $data = (new Category())->getCategoryAll();
        foreach ($data as $k => $v){
             $data[$k]['is_show'] = false;
        }
        $data = makeTree($data,'category_id');
        return $this->renderSuccess($data);
     }
     
     // 待支付
     public function nopay(){
        $field = 'id,country_id,order_sn,member_id,storage_id,express_num,status,created_time,pack_free,free';
        $kw = input('keyword');
        $where = [
          'is_delete' => 0,
          'member_id' => $this->user['user_id'],
          'status' =>5,
        ];
        if ($kw){
            $where['express_num'] = $kw;
        }
        $data = (new PackageModel())->query($where,$field);
        $data = $this->getPackItemList($data);
        foreach ( $data as $k => $v){ 
                 $data[$k]['total_free'] = $v['free'] + $v['pack_free'];
        }
        return $this->renderSuccess($data); 
     }

     // 包裹列表
     public function packageList(){
         $query = [];
         $status = $this->request->param('type');
         $order_sn = $this->request->param('order_sn');
         $line_id = $this->request->param('line_id');
         $country = $this->request->param('country');
         $statusMap = [
           'verify' => [1],     
           'nopay' => [2],
           'no_send' => [3,4,5],
           'send' => [6,7],
           'complete' => [8]
         ];
         if ($status)
            $query['status'] = $statusMap[$status];
         $query['member_id'] = $this->user['user_id'];
         !empty($order_sn)?$query['order_sn'] = $order_sn:'';
         !empty($line_id)?$query['line_id'] = $line_id:'';
         !empty($country)?$query['country'] = $country:'';
         $list = (new Inpack())->getList($query);
         foreach ($list as &$value) {
            $value['num'] = count(explode(',',$value['pack_ids']));
            $value['total_free'] = $value['free'] + $value['pack_free'] + $value['other_free'];
         }
         return $this->renderSuccess($list);
     }
     
     // 包裹列表 - 取消包裹
     public function cancle(){
         $data = $this->postData();
         $id = $data['id'];
         $info = (new PackageModel())->field('id,status')->find($id);
    
         if (in_array($info['status'],[3,4,5,6,7,8])){
              return $this->renderError('该包裹已发货,无法为您拦截取消');
         }
         $res = (new PackageModel())->where(['id'=>$info['id']])->update(['status'=>'-1']);
         if (!$res){
              return $this->renderError('取消失败');
         }
         return $this->renderSuccess("取消成功");
     }

      // 包裹列表 - 批量取消包裹
      public function batchcancle(){
            $ids = $this->postData('ids');
            $packlist = (new PackageModel())->field('id,status')->where('id','in',$ids)->select();
            if (!$this->checkPackIsCancle($packlist)){
                return $this->renderError('该包裹已发货,无法为您拦截取消');
            }
            $res = (new PackageModel())->whereIn('id',$ids)->update(['status'=>'-1']);
            $inpacklist = (new Inpack())->where('pack_ids','in',$ids)->select();
            foreach($inpacklist as $v){
               if ($v['is_pay']==1 && $v['real_payment']){
                  // 退款流程
                  $remark =  '集运订单'.$v['order_sn'].'的支付退款';
                  (new User())->banlanceUpdate('add',$v['member_id'],$v['real_payment'],$remark);
               }
            }
            // 取消集运单
            $inpack = (new Inpack())->where('pack_ids','in',$ids)->update(['status'=>'-1']);
            if (!$res){
                 return $this->renderError('取消失败');
            }
            return $this->renderSuccess("取消成功");
      }
     
     // 包裹列表 - 取消包裹
     public function canclePack(){
         $id = $this->postData('id');
         $info = (new Inpack())->field('id,status,is_pay')->find($id[0]);
         if (!in_array($info['status'],[1,2,3,4])){
              return $this->renderError('该包裹已发货,无法为您拦截取消');
         }
         // 判断该订单是否已支付 且 实际付款金额>0
         if ($info['is_pay']==1 && $info['real_payment']){
             // 退款流程
            $remark =  '集运订单'.$info['order_sn'].'的支付退款';
            (new User())->banlanceUpdate('add',$info['member_id'],$info['real_payment'],$remark);
         }
         $res = (new Inpack())->where(['id'=>$info['id']])->update(['status'=>'-1']);
         if (!$res){
              return $this->renderError('取消失败');
         }
         return $this->renderSuccess("取消成功");
     }

    // 批量取消 集运单 
    public function batchCanclePack(){
      $ids = $this->postData('ids');
      $inpacklist = (new Inpack())->field('id,status,is_pay')->where('id','in',$ids)->select();
      if (!$this->checkInPackCancle($inpacklist)){
         return $this->renderError('该包裹已发货,无法为您拦截取消');
      }
      // 判断该订单是否已支付 且 实际付款金额>0
      foreach($inpacklist as $v){
        if ($v['is_pay']==1 && $v['real_payment']){
           // 退款流程
           $remark =  '集运订单'.$v['order_sn'].'的支付退款';
           (new User())->banlanceUpdate('add',$v['member_id'],$v['real_payment'],$remark);
        }
     }
      $res = (new Inpack())->whereIn('id',$ids)->update(['status'=>'-1']);
      if (!$res){
           return $this->renderError('取消失败');
      }
      return $this->renderSuccess("取消成功");
  }

     // 检查包裹状态 是否可以取消
     public function checkPackIsCancle($packlist){
        foreach ($packlist as $v){
            if (!in_array($v['status'],[1,2,3,4,5,6,7,8])){
                return false;
            }
        }
        return true;
     }

     // 检查集运单状态 是否可以取消
     public function checkInPackCancle($packlist){
        foreach ($packlist as $v){
            if (!in_array($v['status'],[1,2,3,4])){
                return false;
            }
        }
        return true;
     }

     // 待认领
     public function packageForTaker(){
         $field = 'id,country_id,order_sn,storage_id,created_time,express_num,entering_warehouse_time';
         $kw = input('keyword');
         $where = [
           'is_delete' => 0,
           'is_take' =>1,
         ];
         if ($kw){
             $where['express_num'] = $kw;
         }
         $data = (new PackageModel())->where($where)->field($field)->paginate(15);
         foreach($data as $k => $v){

              $data[$k]['express_num'] = func_substr_replace($v['express_num'],'*',4,6);
         }
         return $this->renderSuccess($data);
     }
     
     // 包裹认领
     public function getTakePackage(){
        $post = $this->postData();
        if (!$post['express_sn']){
          return $this->renderError('认领信息错误');
        }
        $classIds = $post['class_ids'];
        if  (!$classIds && !is_string($classIds)){
          return $this->renderError('认领信息错误');
        }
        $classIdsArr = explode(',',$classIds);
        if (count($classIdsArr)<=0){
          return $this->renderError('认领信息错误');
        }
        $package = (new PackageModel())->where(['express_num'=>$post['express_sn'],'is_take'=>1])->find();
        if (!$package){
          return $this->renderError('认领信息错误');
        }    
        (new PackageModel())->where(['id'=>$package['id']])->update(['member_id'=>$this->user['user_id'],'is_take'=>2]);
        if (isset($class_ids)){
            $packItemModel = new PackageItemModel();
            $classItem = $this->parseClass($class_ids);
            foreach ($classItem as $k => $val){
                    $classItem[$k]['class_id'] = $val['category_id'];
                    $classItem[$k]['express_name'] = '';
                    $classItem[$k]['class_name'] = $val['name'];
                    $classItem[$k]['express_num'] = $post['express_sn'];
                    unset($classItem[$k]['category_id']); 
                    unset($classItem[$k]['name']);        
            }
            $packItemRes = $packItemModel->saveAllData($classItem,$package['id']);
        }
        return $this->renderSuccess('认领成功');
     }

     // 包裹数据
     public function getPackItemList($data){
        $orderItem = [];
        foreach($data as $k => $v){
            $orderItem[] = $v['id'];
        }
        $orderIdItem = [];
        $orderItemList = (new PackageItemModel())->whereIn('order_id',$orderItem)->select();
        // dump($orderItemList);die;
        if ($orderItemList->isEmpty()){
            return $data;
        }
        foreach ($orderItemList as $v){
            $orderIdItem[$v['order_id']][] = $v->toArray();
        }
        foreach($data as $k =>$v){
            if (isset($orderIdItem[$v['id']]))
                $data[$k]['class_name'] = implode(',',array_column($orderIdItem[$v['id']],'class_name'));
        }
        return $data;
     }
     
     // 待查验
     public function verify(){
        $field = 'id,country_id,order_sn,storage_id,status,express_num,created_time';
        $status = \request()->get('status');
        $keyword = \request()->get('keyword');
        $where[] = ['is_delete','=',0];
        $where[] = ['member_id','=',$this->user['user_id']];
        $where[] = ['status','in',[2,3]];
        if ($keyword){
            $where[] = ['express_num','like','%'.$keyword.'%'];
        }
        $inpack = (new Inpack())->where(['status'=>$status])->select();
        $ids = [];
        foreach ($inpack as $v){
            $_ids = explode(',',$v['pack_ids']);
            array_merge($ids,$_ids);
        }
        $where[] = ['id','in',$ids]; 
        $data = (new PackageModel())->Dbquery($where,$field);
        $data = $this->getPackItemList($data);
        return $this->renderSuccess($data); 
     }

     // 未入库
     public function outside(){
        if(!\request()->get('token')){
            return $this->renderError('请先登录');
        }
        $orderSn = $this->request->param('order_sn');
        $country = $this->request->param('country');
        $line = $this->request->param('line');
        $field = 'id,country_id,order_sn,storage_id,remark,line_name,status,pack_attr,goods_attr,created_time';
        $where = [];
        $where[] = ['is_delete','=',0];
        $where[] = ['status','=',\request()->get('status')];
        $where[] = ['member_id','=',$this->user['user_id']];
        !empty($orderSn)?$where[] = ['order_sn','like','%'.$orderSn."%"]:'';
        !empty($country)?$where[] = ['country_id','=',$orderSn]:'';
        !empty($line)?$where[] = ['line_id','=',$line]:'';
      
        $data = (new PackageModel())->Dbquery($where,$field);
        // dump((new PackageModel())->getLastsql());die;
        foreach ($data as $k => $items){
             $allWeight = 0;
             $allNum = 0;
             $data[$k]['goods_attr'] = $items['goods_attr']?implode(',',json_decode($items['goods_attr'],true)):'';
            foreach ($items['item'] as $itemsGoods){
                 $allWeight += $itemsGoods['unit_weight'];
                 $allNum += $itemsGoods['product_num'];
            }
            $data[$k]['allWeight'] = $allWeight;
             $data[$k]['allnum'] = $allNum;
        }
       
        $data = $this->getPackItemList($data);
       
        return $this->renderSuccess($data);
     }
     
     // 包裹更新
     public function packageUpdate(){
          $post = $this->postData();
          if (!$post['country_id']){
              return $this->renderError('请选择国家');
          }
          $country = (new Country())->getValueById($post['country_id'],'title');
          if (!$country){
              return $this->renderError('国家信息错误');
          }
          if (!$post['storage_id']){
              return $this->renderError('请选择仓库');
          }
          $storage = (new Shop())->getValueById($post['storage_id'],'shop_name');
          if (!$storage){
              return $this->renderError('仓库信息错误');
          }
          if (!$post['express_id']){
              return $this->renderError('请选择快递');
          }
          $express = (new Express())->getValueById($post['express_id'],'express_name');
          if (!$express){
              return $this->renderError('快递信息错误');
          }
          if (!$post['id']){
             return $this->renderError('包裹参数错误');
          }
          $class_ids = $post['class_ids'];
          $classItem = [];
          if ($class_ids){
              $classItem = $this->parseClass($class_ids);
              foreach ($classItem as $k => $val){
                    $classItem[$k]['class_id'] = $val['category_id'];
                    $classItem[$k]['express_name'] = $express;
                    $classItem[$k]['class_name'] = $val['name'];
                    $classItem[$k]['express_num'] = $post['express_sn'];
                    unset($classItem[$k]['category_id']); 
                    unset($classItem[$k]['name']);        
              }
          }
          $packModel = new PackageModel();
          $packItemModel = new PackageItemModel();
          // 开启事务
          Db::startTrans();
          $post['express_name'] = $express;
          $post['express_num'] = $post['express_sn'];
          unset($post['express_sn']);
          unset($post['class_ids']);
          unset($post['token']);
          $res = $packModel->saveData($post);
          if (!$res){
              return $this->renderError('申请修改失败');
          }
          if ($classItem){
              // 删除之前的数据
              $map = [
                 'order_id' => $post['id'],
              ];
              $packItemModel -> where($map) -> delete();
              $packItemRes = $packItemModel->saveAllData($classItem,$post['id']);
              if (!$packItemRes){
                Db::rollback();
                return $this->renderError('申请修改失败');
              }
          }
          Db::commit();
          return $this->renderSuccess('申请修改成功');
     }

     // 包装服务
     public function postservice(){
        $data = Db::name('package_services')->where(['wxapp_id'=>(new PackageModel())->getWxappId()])->select()->toArray();
        foreach($data as $k => $val){
           $data[$k]['is_show'] = false;
        }
        return $this->renderSuccess($data);
     }

    public function inpack(){
        $ids = $this->postData('selectIds')[0];
        $line_id = $this->postData('inpack')['line_id'];
        $address_id = $this->postData('inpack')['address_id'];
        $pack_ids = $this->postData('inpack')['id'];
        $remark = $this->postData('remark')[0];
        //物流模板设置
        $noticesetting = setting::getItem('notice');
        if (!$ids){
            return $this->renderError('请选择要打包的包裹');
        }
        
        $idsArr = explode(',',$ids);
        $pack = (new Package())->whereIn('id',$idsArr)->select();
        
        if (!$pack || count($pack) !== count($idsArr)){
            return $this->renderError('打包包裹数据错误');
        }
        $status = array_unique(array_column($pack->toArray(),'status'));
        //dump($status);die;
        if (count($status)==1 && in_array($status[0], [1,7,8,9,10,11])){
            return $this->renderError('请选择可以打包的包裹');             
        }
        $pack_member = array_unique(array_column($pack->toArray(),'member_id'));
       
        if (count($pack_member)!=1){
             return $this->renderError('请选择同一用户包裹进行打包');
        }

        
        if($address_id=='-1'){
            $address = (new UserAddress())->where(['user_id'=>$pack_member[0]])->find();
            if(!$address){
                return $this->renderError('该用户没有默认地址<br><a target="_blank" href="index.php?s=/store/user/address">[前往地址设置]</a>');
            }
            $address_id = $address['address_id']; 
        }else{
            $address = (new UserAddress())->where(['address_id'=>$address_id])->find();
        }
        
        
        $line = (new Line())->find($line_id);
        if (!$line){
            return $this->renderError('线路不存在,请重新选择');
        }
        //计算路线费用；
        
        $priceRes = $this->computeLinePrice($pack,$line,$pack_ids);
      
        if(isset($priceRes['code'])){
             return $this->renderError('线路价格无法预估,请更换线路'); 
        }
      
        // 创建包裹订单
        $inpackOrder = [
          'order_sn' => createSn(),
          'remark' =>$remark,
          'pack_ids' => $ids,
          'pack_services_id' => $pack_ids,
          'storage_id' => $pack[0]['storage_id'],
          'address_id' => $address_id,
          'free' => $priceRes['free'],
          'weight' => $priceRes['allWeigth'],
          'cale_weight' =>$priceRes['caleWeigth'],
          'volume' => $priceRes['volumn'],
          'pack_free' => $priceRes['pack_free'],
          'other_free' =>$priceRes['other_free'],
          'member_id' => $pack_member[0],
          'country' => $address['country'],
          'source' => 1,
          'created_time' => getTime(),
          'updated_time' => getTime(),
          'status' => 1,
          'source' => 1,
          'operate_id' => 0,
          'wxapp_id' => (new Package())->getWxappId(),
          'line_id' => $line_id,
        ];
        $inpack = (new Inpack())->insertGetId($inpackOrder); 
        $inpackdate = (new Inpack())->where('id',$inpack)->find();
        $res = (new Package())->whereIn('id',$idsArr)->update(['status'=>5,'line_id'=>$line_id,'pack_service'=>$pack_ids,'address_id'=>$address_id,'updated_time'=>getTime()]);
        //更新包裹的物流信息
        foreach ($idsArr as $key => $val){
            $packnum[$key] = (new Package())->where('id',$val)->value('express_num');
        }
        //修改包裹的记录
        foreach ($packnum as $ky => $vl){
            Logistics::updateOrderSn($vl,$inpackdate['order_sn']);
        }
         if($noticesetting['packageit']['is_enable']==1){
             Logistics::addInpackLog($inpackdate['order_sn'],$noticesetting['packageit']['describe']);
         }
        
        if (!$res){
            return $this->renderError('打包包裹提交失败');
        }
        return $this->renderSuccess('打包包裹提交成功');
    }

     // 批量支付
     public function batchdopay(){
         $ids = $this->postData('ids');
          $pack = (new Inpack())->field('id,status,pack_ids,free,is_pay,order_sn,pack_free')->where('id','in',$ids)->select();
          if (!$pack){
            return $this->renderError('集运订单不存在');
          }
          foreach($pack as $v){
            if ($v['status'] != 2 || $v['is_pay'] != 2) {
               return $this->renderError('集运订单状态不正确');
            }
          }
          Db::startTrans();
          foreach($pack as $item){
            $user = $this->user;
            $amount = $item['free'] + $item['pack_free'];
            if ($user['balance']<$amount){
              return $this->renderError('余额不足,请充值');
            }
            $update['real_payment'] = $amount;
            $update['is_pay'] = 1;
            $update['status'] = 3;
            $update['pay_time'] = getTime();
            try {
                (new Inpack())->where('id',$item['id'])->update($update);
                $update['status'] = 6;
                $up = (new PackageModel())->where('id','in',explode(',',$item['pack_ids']))->update($update);
                if (!$up){
                  Db::rollback();
                  return $this->renderError('支付失败,请重试');
                }
                $memberUp = (new User())->where(['user_id'=>$user['user_id']])->update([
                  'balance'=>$user['balance']-$amount,
                  'pay_money' => $user['pay_money']+ $amount,
                ]);
                if (!$memberUp){
                    Db::rollback();
                    return $this->renderError('支付失败,请重试');
                }
                // 新增余额变动记录
                BalanceLog::add(SceneEnum::CONSUME, [
                'user_id' => $user['user_id'],
                'money' => $amount,
                'remark' => '包裹单号'.$item['order_sn'].'的运费支付',
                'sence_type' => 2,
            ], [$user['nickName']]);
            }catch(\Exception $e){
                dump($e); die;
                return $this->renderError('支付失败,请重试');
            }
            
          }
          Db::commit();
          return $this->renderSuccess('支付成功');
     }

     // 支付
     public function doPay(){
         $id = $this->postData('id')[0];
         $pack = (new Inpack())->field('id,status,pack_ids,free,order_sn,pack_free')->find($id);
         if ($pack['status'] != 2 && $pack['is_pay'] != 2) {
            return $this->renderError('包裹状态不正确');
         }
         $user = $this->user;
         $amount = $pack['free'] + $pack['pack_free'];
         if ($user['balance']<$amount){
            return $this->renderError('余额不足,请充值');
         }
         Db::startTrans();
         $update['real_payment'] = $amount;
         $update['is_pay'] = 1;
         $update['status'] = 3;
         $update['pay_time'] = getTime();
         try {
             (new Inpack())->where('id',$pack['id'])->update($update);
             $update['status'] = 6;
             $up = (new PackageModel())->where('id','in',explode(',',$pack['pack_ids']))->update($update);
             if (!$up){
                Db::rollback();
                return $this->renderError('支付失败,请重试');
             }
             $memberUp = (new User())->where(['user_id'=>$user['user_id']])->update([
               'balance'=>$user['balance']-$amount,
               'pay_money' => $user['pay_money']+ $amount,
             ]);
             if (!$memberUp){
                 Db::rollback();
                 return $this->renderError('支付失败,请重试');
             }
              // 新增余额变动记录
             BalanceLog::add(SceneEnum::CONSUME, [
              'user_id' => $user['user_id'],
              'money' => $amount,
              'remark' => '包裹单号'.$pack['order_sn'].'的运费支付',
              'sence_type' => 2,
          ], [$user['nickName']]);
         }catch(\Exception $e){
             dump($e); die;
             return $this->renderError('支付失败,请重试');
         }
         Db::commit();
         return $this->renderSuccess('支付成功');
     }

     // 包裹信息
     public function details(){
        $field_group = [
           'edit' => [
              'id,order_sn,storage_id,country_id,express_name,express_num,express_id,free,pack_free,price,address_id,status,line_id,remark'
           ],
        ];
        $id = \request()->post('id');
        $method = $this->postData('method');
        $data = (new PackageModel())->getDetails($id,$field_group[$method[0]]);
        $packItem = (new PackageItemModel())->where(['order_id'=>$data['id']])->field('class_name,id,class_id,order_id')->select();
        $data['free_total'] = $data['free']+$data['pack_free'];
        $data['shop'] = '';
        if ($packItem){
            $data['shop'] = implode(',',array_column($packItem->toArray(),'class_name'));
            $data['shop_ids'] = implode(',',array_column($packItem->toArray(),'class_id'));
        }
        if ($data['address_id']){
            $data['address'] = (new UserAddress())->find($data['address_id']);
        }
        if ($data['line_id']){
            $data['line'] = (new Line())->field('id,name,limitationofdelivery,image_id')->find($data['line_id']);
            $data['line'] = $this->withImageById($data['line'],'image_id');
        }
        return $this->renderSuccess($data);
     }
     
     // 包裹信息
     public function details_pack(){
        $field_group = [
           'edit' => [
              'id,order_sn,pack_ids,storage_id,free,pack_free,other_free,address_id,weight,cale_weight,volume,length,width,height,status,line_id,remark,country,t_order_sn'
           ],
        ];
        $id = \request()->post('id');
        $method = $this->postData('method');
        $data = (new Inpack())->getDetails($id,$field_group[$method[0]]);
        $package = (new PackageModel())->where('id','in',explode(',',$data['pack_ids']))->field('id,express_num,price,express_name')->select();
        $packItem = (new PackageItemModel())->where('order_id','in',explode(',',$data['pack_ids']))->field('class_name,id,class_id,order_id')->select();
        $packItemGroup = [];
        foreach ($packItem as $val){
             $packItemGroup[$val['order_id']][] = $val['class_name'];
        }

        foreach ($package as $k => $v){
             
             $package[$k]['class_name'] = '';
             if (isset($packItemGroup[$v['id']])){
                 $package[$k]['class_name'] = implode(',',$packItemGroup[$v['id']]);
             }
        }
        $data['free_total'] = $data['free']+$data['pack_free'];
        $data['shop'] = '';
        if ($data['address_id']){
            $data['address'] = (new UserAddress())->find($data['address_id']);
        }
        if ($data['line_id']){
            $data['line'] = (new Line())->field('id,name,limitationofdelivery,image_id')->find($data['line_id']);
            $data['line'] = $this->withImageById($data['line'],'image_id');
        }
        $data['free_total'] = $data['free'] + $data['pack_free'] + $data['other_free'];
        $data['item'] = $package;
        if (isset($data['line']['image'])){
            $data['image'] = $data['line']['image'];
        }
        return $this->renderSuccess($data);
     }
     
     // 更换地址
     public function addressUpdate(){
        $address_id = $this->postData('address_id')[0];
        $id = $this->postData('id')[0];
       
        $pack = (new Inpack())->field('id,status')->find($id);
        if ($pack['status'] >= 6){
            return $this->renderError('包裹已发货,无法更改地址');
        }
        $address = (new UserAddress())->find($address_id);
        if (!$address){
            return $this->renderError('地址信息错误,请确认地址是否正确');
        }
        $up = (new Inpack())->where(['id'=>$id])->update([
           'address_id' => $address_id,
           'updated_time' => getTime(),
        ]);
        if (!$up){
          return $this->renderError('更新地址失败');
        }
        return $this->renderSuccess("更新地址成功");
     }
     
     // 轨迹列表
     public function logicist(){
        $express = $this->postData('code')[0];
        $sn = (new Inpack())->getInpackSn($express);
        if ($sn){
            $pack = (new PackageModel())->where('id','in',explode(',',$sn['pack_ids']))->find();
            $logic = (new Logistics())->getList($pack['express_num']);
        }else{
            $logic = (new Logistics())->getList($express); 
        }
        return $this->renderSuccess($logic);
     }
     
     // 包裹统计
     public function packTotal(){
         $model =  (new Inpack());
         $return = [
           'no_pay' => $model->where('status',2)->where('member_id',$this->user['user_id'])->count(),
           'verify' => $model->whereIn('status',[1])->where('member_id',$this->user['user_id'])->count(),
           'no_send' => $model->whereIn('status',[3,4,5])->where('member_id',$this->user['user_id'])->count(),
           'send' => $model->whereIn('status',[6,7])->where('member_id',$this->user['user_id'])->count(),
           'complete' => $model->whereIn('status',[8])->where('member_id',$this->user['user_id'])->count(),
         ];
         return $this->renderSuccess($return);
     }
     
     // 包裹统计
     public function total(){
         $model =  (new PackageModel());
         $return = [
           'outside' => $model->where('status',1)->where('member_id',$this->user['user_id'])->count(),
           'enter' => $model->whereIn('status',2)->where('member_id',$this->user['user_id'])->count()
         ];
         return $this->renderSuccess($return);
     }

     // 签收
     public function signedin(){
         $id = $this->postData('id')[0];
         $pack = (new Inpack())->field('id,pack_ids,status')->find($id);
         if (!$pack){
             return $this->renderError('包裹数据错误');
         }
         if ($pack['status']!=6){
             return $this->renderError('包裹状态错误');
         }
         (new Inpack())->where(['id'=>$id])->update(['status'=>7]);
         $pack_ids = explode(',',$pack['pack_ids']);
         $up = (new PackageModel())->where('id','in',$pack_ids)->update(['status'=>10]);
         foreach($pack_ids as $v){
            Logistics::add($v,'包裹已经本人签收,如有问题,请知悉客服');
         }
         if (!$up){
          return $this->renderError('签收失败');
        }
        return $this->renderSuccess("签收成功");
     }

     // 格式化
     public function parseClass($class_ids){
         $class_item = [];
         $class_ids = explode(',',$class_ids);
         $class = (new Category())->whereIn('category_id',$class_ids)->field('category_id,name')->select()->toArray(); 

         return $class;
     }
   
     // 国家列表
     public function country(){
        $where = '';
        $k = input('keyword');
        if ($k){
            $where = $k; 
        }    
        $data = (new Country())->queryCountry($where);

        $dataPyin = [];
        
        $AZGROUP = range("A","Z");
         
        foreach ($data as $k => $v){
            $_pyin = Pinyin::getPinyin($v['title']);
            $first = strtoupper(substr($_pyin,0,1));
            if ($first){
                $dataPyin[$first][] = $v;
            }
        }
        $newDataPyin = [];
        foreach ($AZGROUP as $v){
            if (isset($dataPyin[$v]))
                $newDataPyin[$v] = $dataPyin[$v];  
        }
        return $this->renderSuccess($newDataPyin);
     }

     // 快递列表
     public function express(){
        $data = (new Express())->queryExpress();
        return $this->renderSuccess($data);
     }

    // 线路列表
    public function line(){
        $data = (new Line())->getLine([]);
        return $this->renderSuccess($data);
     }

     // 仓库列表
     public function storage(){
        $data = (new Shop())->getList();
        return $this->renderSuccess($data);
     }

     // 订单评论详情
     public function commentOrder(){
         $id = \request()->post('id');
         $data = (new Inpack())->getDetails($id,'id,line_id,storage_id');
         if ($data['line_id']){
            $data['line'] = (new Line())->field('id,name,limitationofdelivery,image_id')->find($data['line_id']);
            $data['line'] = $this->withImageById($data['line'],'image_id');
        }
        return $this->renderSuccess($data);
     }
     
}
