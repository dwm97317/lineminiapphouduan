<?php
namespace app\api\controller;
use app\api\model\Express;
use app\api\model\store\Shop;
use app\api\model\Country;
use app\api\model\Category;
use app\api\model\Inpack;
use app\api\model\Line;
use app\api\model\Logistics;
use app\api\model\Package as PackageModel;
use app\api\model\InpackService as InpackServiceModel;
use app\api\model\PackageItem as PackageItemModel;
use app\api\model\User;
use app\api\model\user\BalanceLog;
use app\common\enum\user\balanceLog\Scene as SceneEnum;
use app\api\model\UserAddress;
use app\api\model\Setting as SettingModel;
use app\api\model\dealer\Setting as SettingDealerModel;
use app\api\model\dealer\Referee as RefereeModel;
use app\api\service\sharing\SharingOrder as ShareOrderService;
use app\api\model\sharing\SharingOrderItem;
use app\common\model\dealer\Capital;
use app\common\model\store\shop\Capital as CapitalModel;
use app\common\model\dealer\Order as DealerOrder;
use app\common\library\Pinyin;
use app\common\model\dealer\User as DealerUser;
use app\api\model\Coupon as CouponModel;
use app\api\model\UserCoupon;
use app\common\model\PackageImage;
use think\Db;
use app\common\service\Message;
use app\api\model\store\shop\Clerk;
use app\api\service\Payment as PaymentService;
use app\common\enum\OrderType as OrderTypeEnum;
use app\api\service\trackApi\TrackApi;
use app\common\library\Ditch\config;
use app\common\model\Ditch as DitchModel;
use app\store\model\store\Shop as ShopModel;
use app\common\library\Ditch\BaiShunDa\bsdexp;
use app\common\library\Ditch\Jlfba\jlfba;
use app\common\library\Ditch\kingtrans;
use app\common\library\Ditch\Hualei;
use app\common\library\Ditch\Xzhcms5;
use app\common\library\Ditch\Aolian;
use app\common\library\Ditch\Yidida;
/**
 * 页面控制器
 * Class Index
 * @package app\api\controller
 */
class Package extends Controller
{
     
     /* @var \app\api\model\User $user */
     private $user;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        if (\request()->param('token')){
            // 用户信息
            $this->user = $this->getUser();
        }else{
            $this->user['user_id'] = 0;
        }
    }
    
        
    //批量打包
    public function quickPackageItAll(){
        $param = $this->request->param();
        $PackageModel = new PackageModel;
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        $storesetting = SettingModel::getItem('store');
        $inpackOrder = [
          'order_sn' => createSn(),
          'remark' =>$param['remark'],
          'pack_services_id' => $param['pack_ids'],
          'storage_id' => $clerk['shop_id'],
          'free' => 0,
          'weight' =>$param['weight'],
          'length' =>$param['length'],
          'width' =>$param['width'],
          'height' =>$param['height'],
          'cale_weight' =>0,
          'volume' => 0, //体积重
          'pack_free' => 0,
          'other_free' =>0,
          'created_time' => getTime(),
          'updated_time' => getTime(),
          'status' => 1,
          'source' => 6,
          'operate_id' => 0,
          'wxapp_id' => $param['wxapp_id'],
          'line_id' => $param['line_id'],
        ];
        // 开启事务
        Db::startTrans();
        $inpack = (new Inpack())->insertGetId($inpackOrder);
         //处理包装服务
        (new InpackServiceModel())->doservice($inpack,$param['pack_ids']);
        //查询仓库是否存在此包裹，如果存在，则更新入库时间和入库的状态；
        //如果不存在，则需要入库操作；
        $pack_id = [];
        // dump($clerk);die;
        $express_nums = explode(',',$param['packids']);
        foreach ($express_nums as $key => $val){
            $number = $PackageModel->where('express_num',$val)->find();
            
            if(!empty($number) && $number['status']>4){
                return $this->renderError($val.'已被打包');
            }
            // dump($number->toArray());die;
            if(!empty($number)){
                $number->save(['entering_warehouse_time'=>getTime(),'status'=>8,'inpack_id'=>$inpack,'storage_id'=>$clerk['shop_id'],]);
                $pack_id[$key] = $number['id'];
            }else{
                $id = $PackageModel->insertGetId([
                    'entering_warehouse_time'=>getTime(),
                    'status'=>8,
                    'express_num'=>$val,
                    'storage_id'=>$clerk['shop_id'],
                    'updated_time'=>getTime(),
                    'created_time'=>getTime(),
                    'wxapp_id'=>$param['wxapp_id'],
                    'inpack_id'=>$inpack,
                    'order_sn'=> CreateSn()
                ]);
                $pack_id[$key] = $id;
            }
        }
        (new Inpack())->where('id',$inpack)->update(['pack_ids'=>implode(',',$pack_id)]);
        Db::commit();
        return $this->renderSuccess('提交打包成功');
        //包裹处理完成后，需要把包裹的id加入到集运订单中，创建新的快速打包订单；
        
        //快速打包订单添加好后，需要对订单进行用户归属，路线选择，用户地址选择等操作才能划分到正常订单行列；
    }
    

     // 包裹预报
     public function report(){
         if (!$this->user['user_id']){
            return $this->renderError('请先登录');
         }
         $user = (new User())->find($this->user['user_id']);
         $userclient = SettingModel::detail("userclient")['values'];
        //  dump($userclient);die;
         
         $post = $this->postData();
         
         if($userclient['yubao']['is_country']==1){
            if ($post['country_id']){
              $country = (new Country())->getValueById($post['country_id'],'title');
              if (!$country){
                     return $this->renderError('国家信息错误');
                 }
              } 
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
         if (!$post['express_sn']){
             return $this->renderError('快递单号错误');
         }
         if (preg_match('/[\x7f-\xff]/', $post['express_sn'])){
             return $this->renderError('快递单号不能使用汉字或字符');
         }
         if (!preg_match('/^[^,]+$/', $post['express_sn'])){
             return $this->renderError('单个预报不能添加逗号');
         }
        // dump(preg_match('/^[^,]+$/', $post['express_sn']));die;
         if(!preg_match('/^[^\s]*$/',$post['express_sn'])){
             return $this->renderError('快递单号不能有空格');
         }
         //为京东特别写的代码
         if(!stristr($post['express_sn'],'JD')){
          if(!preg_match('^\w{3,20}$^',$post['express_sn'])){
             return $this->renderError('快递单号不能使用特殊字符');
           } 
         }
         $express = (new Express())->getValueById($post['express_id'],'express_name');
         $express_code = (new Express())->getValueById($post['express_id'],'express_code');
         
         if (!$express){
             return $this->renderError('快递信息错误');
         }
         if (!isset($post['class_ids'])){
             return $this->renderError('请选择物品分类');
         }
         $class_ids = $post['class_ids'];
         $goodslist = isset($post['goodslist'])?$post['goodslist']:[];
         if (isset($post['share_id']) && $post['share_id']){
             $ShareSettingService = (new ShareOrderService());
             if(!$ShareSettingService -> checkPubiler($user['user_id'],$post)){
                  return $this->renderError('您无权限参与该拼团活动，或受到限制');
             }
         } 
         $classItem = [];
         if ($class_ids || $goodslist){
             $classItem = $this->parseClass($class_ids);
             if(empty($classItem)){
                foreach ($goodslist as $k => $val){
                     $classItem[$k]['class_name'] = $val['pinming'];
                     $classItem[$k]['one_price'] = $val['danjia'];
                     $classItem[$k]['all_price'] = (!empty($val['danjia'])?$val['danjia']:0) * (!empty($val['shuliang'])?$val['shuliang']:0);
                     $classItem[$k]['product_num'] = $val['shuliang'];
                     $classItem[$k]['express_num'] = $post['express_sn'];
                     $classItem[$k]['express_name'] = $express;
                }
             }else{
                 foreach ($classItem as $k => $val){
                   $classItem[$k]['class_id'] = $val['category_id'];
                   $classItem[$k]['express_name'] = $express;
                   $classItem[$k]['class_name'] = $val['name'];
                   $classItem[$k]['express_num'] = $post['express_sn'];
                   $classItem[$k]['all_price'] = $post['price'];
                   unset($classItem[$k]['category_id']); 
                   unset($classItem[$k]['name']);        
                }
             }
         }
         $packModel = new PackageModel();
         $packItemModel = new PackageItemModel();
         // todo 判断预报的单号是否存在（待认领或者已认领），如果存在且被认领则提示已认领，如果存在但未被认领则修改存在的记录所属用户，认领状态；
         $packres = $packModel->where('express_num',$post['express_sn'])->where('is_delete',0)->find();
         
         if($packres && ($packres['is_take']==2)){
             return $this->renderError('快递单号已被预报');
         }else{
             $wxapp_id = \request()->get('wxapp_id');
             $express_num = $post['express_sn'];
             $selectData = $packModel->where('express_num','like','%'.$express_num.'%')->where('is_delete',0)->where('status','<',6)->select();
             $skey = 0;
            if(count($selectData)==1){
                $post['express_num'] = $selectData[0]['express_num'];
                $packres =  $selectData[0];
               
                $packModel->where('express_num',$post['express_num'])->update(['express_num'=>$express_num]);
                 //图片id
     
            }elseif(count($selectData)>1){
                //如果查出多个类似的包裹，则选择匹配度最长的；
                for ($i = 1; $i < count($selectData); $i++) {
                    if($selectData[$i]>$selectData[$i-1]){
                        $skey = $i; 
                    }else{
                        $skey = $i+1; 
                    }
                }
                $post['express_num'] = $selectData[$skey]['express_num'];
                $packres =  $selectData[$skey];
                $packModel->where('express_num',$post['express_num'])->update(['express_num'=>$express_num]);
            }
         }
     
   
         // 开启事务
         Db::startTrans();
       
         $post['express_name'] = $express;
         $post['express_num'] = $post['express_sn'];

         $post['member_id'] = $this->user['user_id'];
         $post['member_name'] = $user['nickName'];
 
         if($packres && ($packres['is_take']==1)){
            
           $resup = $packModel->where('id',$packres['id'])->update(
               ['price'=>$post['price'],
               'remark'=>$post['remark'],
               'country_id'=>$post['country_id'],
               'express_name'=>$post['express_name'],
               'express_id'=>$post['express_id'],
               'member_id'=>$user['user_id'],
               'member_name'=>$user['nickName'],
               'storage_id'=>$post['storage_id'],
               'is_take'=>2
               ]);

            if (!$resup){
               return $this->renderError('申请预报失败');
            }
          
            if ($classItem){
                 $packItemRes = $packItemModel->saveAllData($classItem,$packres['id']);
                 if (!$packItemRes){
                    Db::rollback();
                    return $this->renderError('申请预报失败');
                 }
             }
             if(!empty($post['imageIds'])){
                 $this->inImages($packres['id'],$post['imageIds'],$wxapp_id);
             }
             
             Logistics::add($packres['id'],'包裹预报成功');
             Db::commit();
             return $this->renderSuccess('申请预报成功');
         }

         $post['order_sn'] = CreateSn();
         $post['is_take'] = 2;
         if (isset($post['share_id']) && $post['share_id']){
             $post['source'] = 6;
         }
         
         //注册到17track
         $noticesetting = SettingModel::getItem('notice');
         $storesetting = SettingModel::getItem('store');
        //  dump($noticesetting);die;
         if($noticesetting['is_track_yubao']['is_enable']==1){
                $trackd = (new TrackApi())
                ->register([
                    'track_sn'=>$post['express_num'],
                    't_number'=>$express_code,
                    'wxapp_id' =>$this->wxapp_id
                ]);
            }
        
         $res = $packModel->saveData($post);
        //  dump($res);die;
         if (!$res){
             return $this->renderError('申请预报失败');
         }
         
         if (isset($post['share_id']) && $post['is_share'] && $post['share_id']){
                 $post['user_id'] = $user['user_id'];
                 (new SharingOrderItem())->addItem($post,$res);
         }
         if(!empty($post['imageIds'])){
            $this->inImages($res,$post['imageIds'],$wxapp_id);
         }
         if ($classItem){
             $packItemRes = $packItemModel->saveAllData($classItem,$res);
             if (!$packItemRes){
                Db::rollback();
                return $this->renderError('申请预报失败');
             }
         }
         Logistics::add($res,'包裹预报成功');
         Db::commit();
         return $this->renderSuccess('申请预报成功');
     }

     // 批量预报
     public function reportBatch(){
        if (!$this->user['user_id']){
            return $this->renderError('请先登录');
         }
         $user = (new User())->find($this->user['user_id']);
         $post = $this->postData();
         if ($post['country_id']){
             $country = (new Country())->getValueById($post['country_id'],'title');
             if (!$country){
                 return $this->renderError('国家信息错误');
             }
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
         if (!$post['express_sn']){
             return $this->renderError('快递单号错误');
         }
         if (preg_match('/[\x7f-\xff]/', $post['express_sn'])){
             return $this->renderError('快递单号不能使用汉字或字符');
         }
         if(!preg_match('/^[^\s]*$/',$post['express_sn'])){
             return $this->renderError('快递单号不能有空格');
         }
         if(!preg_match('^\w{3,20}$^',$post['express_sn'])){
             return $this->renderError('快递单号不能使用特殊字符');
         }
         $express = (new Express())->getValueById($post['express_id'],'express_name');
         if (!$express){
             return $this->renderError('快递信息错误');
         }
         
         if (!isset($post['class_ids'])){
             return $this->renderError('请选择物品分类');
         }
         
         $class_ids = $post['class_ids'];
         
         $packModel = new PackageModel();
         $packItemModel = new PackageItemModel();
         
         //将多个快递单号分拆为一个数组中英文逗号都可以进行转换
         $post['express_sn'] =trim($post['express_sn']);
         $post['express_sn'] =preg_replace("/\s|　/","",$post['express_sn']);
         $post['express_sn'] =str_replace('，',',',$post['express_sn']);
         $post['express_sn'] =str_replace('+',',',$post['express_sn']);
        
         $expno = explode(',',$post['express_sn']);
         $classItem = [];
              
         // 开启事务
         Db::startTrans();
         $post['express_name'] = $express;
         $post['member_id'] = $this->user['user_id'];
         $post['member_name'] = $user['nickName'];
        //  dump($expno);
         //循环对每个包裹进行预判是否已经入库
         foreach ($expno as $key => $v){
        //   dump($v);
            if(empty($v)  || $v==" " || $v==""){
               return $this->renderError('请不要加多余的逗号'); 
            }
            
            $post['express_num'] =  $v;
            $packres = $packModel->where('express_num',$v)->where('is_delete',0)->find();
            //当快递单号为一个数字加空格形式时候，则会提示报错
            if($packres && ($packres['is_take']==2)){
                return $this->renderError('快递单号'.$v.'已被预报');
            }
              
            if($packres && ($packres['is_take']==1)){
                $resup = $packModel->where('id',$packres['id'])->update(
                   ['price'=>$post['price'],
                   'remark'=>$post['remark'],
                   'country_id'=>$post['country_id'],
                   'express_name'=>$post['express_name'],
                   'express_id'=>$post['express_id'],
                   'member_id'=>$user['user_id'],
                   'member_name'=>$user['nickName'],
                   'storage_id'=>$post['storage_id'],
                   'is_take'=>2
                   ]);
    
                if (!$resup){return $this->renderError('申请预报失败');}
                //存包裹信息
        
                if ($class_ids){
                    $classItem = $this->parseClass($class_ids);
                 
                     foreach ($classItem as $k => $val){
                           $classItem[$k]['class_id'] = $val['category_id'];
                           $classItem[$k]['express_name'] = $express;
                           $classItem[$k]['class_name'] = $val['name'];
                           $classItem[$k]['express_num'] = $v;
                           unset($classItem[$k]['category_id']); 
                           unset($classItem[$k]['name']);     
                     }
                     
                     if ($classItem){
                         $packItemRes = $packItemModel->saveAllData($classItem,$packres['id']);
                         if (!$packItemRes){
                            Db::rollback();
                            return $this->renderError('申请预报失败');
                         }
                     }
                 }
              
                 Logistics::add($packres['id'],'包裹预报成功');
                //  Db::commit();
             }
             
             if(!$packres){
                 $post['order_sn'] = CreateSn();
                 $post['is_take'] = 2;
                 $res = $packModel->saveData($post);
                 if (!$res){
                     return $this->renderError('申请预报失败');
                 }
  
                 if ($class_ids){
                        $classItem = $this->parseClass($class_ids);
                     
                         foreach ($classItem as $k => $val){
                        
                               $classItem[$k]['class_id'] = $val['category_id'];
                               $classItem[$k]['express_name'] = $express;
                               $classItem[$k]['class_name'] = $val['name'];
                               $classItem[$k]['express_num'] = $v;
                               unset($classItem[$k]['category_id']); 
                               unset($classItem[$k]['name']);     
                         }
                        
                         if ($classItem){
                             $packItemRes = $packItemModel->saveAllData($classItem,$res);
                             if (!$packItemRes){
                                Db::rollback();
                                return $this->renderError('申请预报失败');
                             }
                         }
                    }        
                    Logistics::add($res,'包裹预报成功');
             }
         }
         Db::commit();
         return $this->renderSuccess('申请预报成功');
     }
     
     /***
      * 用户预约包裹上门取件
      * 时间：2022年06月29日
      */
      public function appreport(){
         if (!$this->user['user_id']){
            return $this->renderError('请先登录');
         }
         $user = (new User())->find($this->user['user_id']);
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
         //生成预约单号
         $express = createYysn();
         $class_ids = $post['class_ids'];
         $classItem = [];
         if ($class_ids){
             $classItem = $this->parseClass($class_ids);
             
             foreach ($classItem as $k => $val){
                   $classItem[$k]['class_id'] = $val['category_id'];
                   $classItem[$k]['express_name'] = '预约取件';
                   $classItem[$k]['class_name'] = $val['name'];
                   $classItem[$k]['express_num'] = $express;
                   unset($classItem[$k]['category_id']); 
                   unset($classItem[$k]['name']);        
             }
         }
         
         $packModel = new PackageModel();
         $packItemModel = new PackageItemModel();
         // todo 判断预报的单号是否存在（待认领或者已认领），如果存在且被认领则提示已认领，如果存在但未被认领则修改存在的记录所属用户，认领状态；
         $packres = $packModel->where('express_num',$express)->where('is_delete',0)->find();

   
         // 开启事务
         Db::startTrans();
       
         $post['express_name'] = '预约取件';
         $post['express_num'] = $express;
         $post['source'] = 7;
         $post['member_id'] = $this->user['user_id'];
         $post['member_name'] = $user['nickName'];
            

         $post['order_sn'] = CreateSn();
         $post['is_take'] = 2;
        //  dump($post);die;
         $res = $packModel->saveData($post);
        
         if (!$res){
             return $this->renderError('预约失败');
         }
         //图片id
         $this->inImages($res,$post['imageIds'],$this->wxapp_id);

         if ($classItem){
             $packItemRes = $packItemModel->saveAllData($classItem,$res);
             if (!$packItemRes){
                Db::rollback();
                return $this->renderError('预约失败');
             }
         }         
         Logistics::add($res,'预约成功');
         Db::commit();
         return $this->renderSuccess('预约成功');
     }
     
     public function inImages($id,$imageIds,$wxapp_id){
        $PackageImage =  new PackageImage();
        if(isset($imageIds) && count($imageIds)>0){
                foreach ($imageIds as $key =>$val){
                    //校验图片是否又重复的
                     $result = (new $PackageImage)->where('package_id',$id)->where('image_id',$val)->find();
                     if(!isset($result)){
                         $update['package_id'] = $id;
                         $update['image_id'] = $val;
                         $update['wxapp_id'] =$wxapp_id;
                         $update['create_time'] = strtotime(getTime());
                         $resthen= (new PackageImage())->save($update);
                         if(!$resthen){
                              return false;
                         }
                     }
                }
            }    
        return true;
    }
     
     
     public function subtempate(){
         $values = SettingModel::getItem('submsg');
        //  dump($values);die;
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
     
     // 分类列表
     public function hotcategory(){
        $data = (new Category())->gethotCategoryAll();
        foreach ($data as $k => $v){
             $data[$k]['is_show'] = false;
        }
        // $data = makeTree($data,'category_id');
        return $this->renderSuccess($data);
     }
     
     // 未打包列表
     public function unpack(){
        $this->user = $this->getUser(); 
        $field = 'id,country_id,order_sn,express_num,weight,volume,storage_id,created_time,remark,source';
       
        $where[] = ['is_delete','=',0];
        $where[] = ['is_take','=',2];
        $where[] = ['member_id','=',$this->user['user_id']];
        $where[] = ['status','in',[2,3,4,7]];
        $data = (new PackageModel())->Dbquery300($where,$field);
        $data = $this->getPackItemList($data);
        // foreach($data as $k => $val){
        //     $data[$k]['is_show'] = false;
        // }
        return $this->renderSuccess($data);
     }
     
     // 提交打包处理
     public function postPack(){
        $ids = $this->postData('packids')[0];
        $line_id = $this->postData('line_id')[0];
        $pack_ids = $this->postData('pack_ids')[0];
        $address_id = $this->postData('address_id')[0];
        $waitreceivedmoney = $this->postData('waitreceivedmoney')[0];
        $remark = $this->postData('remark')[0];
        if (!$ids){
            return $this->renderError('请选择要打包的包裹');
        }
        $idsArr = explode(',',$ids);
        $pack = (new PackageModel())->whereIn('id',$idsArr)->select();
        if (!$pack || count($pack) !== count($idsArr)){
            return $this->renderError('打包包裹数据错误');
        }
        $pack_storage = array_unique(array_column($pack->toArray(),'storage_id'));
        if (count($pack_storage)!=1){
             return $this->renderError('请选择同一仓库的包裹进行打包');
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
        $storesetting = SettingModel::getItem('store');
        // 创建包裹订单
        $inpackOrder = [
          'order_sn' => $storesetting['createSn']==10?createSn():createSnByUserIdCid($this->user['user_id'],$address['country_id']),
          'remark' =>$remark,
          'pack_ids' => $ids,
          'waitreceivedmoney'=>$waitreceivedmoney,
          'pack_services_id' => $pack_ids, ///此值可作废
          'storage_id' => $pack[0]['storage_id'],
          'address_id' => $address_id,
          'free' => $price,
          'weight' => $allWeigth,
          'cale_weight' => $caleWeigth,
          'volume' => $volumn,
          'pack_free' => 0,
          'member_id' => $this->user['user_id'],
          'unpack_time' => getTime(),
          'created_time' => getTime(),
          'updated_time' => getTime(),
          'status' => 1,
          'line_id' => $line_id,
          'operate_id' => 0,
          'wxapp_id' => \request()->get('wxapp_id'),
        ];
        
        $user_id =$this->user['user_id'];
        if($storesetting['usercode_mode']['is_show']==1){
           $member =  (new User())->where('user_id',$this->user['user_id'])->find();
           $user_id = $member['user_code'];
        }
        $createSnfistword = $storesetting['createSnfistword'];
        $xuhao = ((new Inpack())->where(['member_id'=>$this->user['user_id'],'is_delete'=>0])->count()) + 1;
        $shopname = ShopModel::detail($pack[0]['storage_id']);     
        $orderno = createNewOrderSn($storesetting['orderno']['default'],$xuhao,$createSnfistword,$user_id,$shopname['shop_alias_name'],$address['country_id'],$address['country_id']);
        $inpackOrder['order_sn'] = $orderno;
        
        $inpack = (new Inpack())->insertGetId($inpackOrder); 
        if (!$inpack){
           return $this->renderError('打包包裹提交失败');
        }
        
        //处理包装服务
        if(!empty($pack_ids)){
             (new InpackServiceModel())->doservice($inpack,$pack_ids);
        }
       
        
        $res = (new PackageModel())->whereIn('id',$idsArr)->update(
            [
                'status'=>5,
                'line_id'=>$line_id,
                'pack_service'=>$pack_ids,
                'address_id'=>$address_id,
                'updated_time'=>getTime(),
                'inpack_id'=>$inpack
            ]);
        
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
            
            //发送模板消息通知
        }
         //计算费用
         if($storesetting['is_auto_free']==1){
             getpackfree($inpackdate['id']); 
         }
        
         
         $this->user = $this->getUser();
         $clerk = (new Clerk())->where('shop_id',$pack[0]['storage_id'])->where('mes_status',0)->where('is_delete',0)->select();
          
         if(!empty($clerk)){
         $data=[
            'id'=>$inpackdate['order_sn'],
            'nickName' => ($this->user)['nickName'],
            'userCode' => ($this->user)['user_code'],
            'countpack' =>count($idsArr),
            'packtime' => getTime(),
            'packid' => $inpack,
            'shopname'=>$shopname['shop_name'],
            'wxapp_id' => \request()->get('wxapp_id'),
            'remark' =>$remark,
          ];
          $tplmsgsetting = SettingModel::getItem('tplMsg');
        //   dump($tplmsgsetting);die;
          try {
              if($tplmsgsetting['is_oldtps']==1){
                  //循环通知员工打包消息 
                  foreach ($clerk as $key => $val){
                      $data['clerkid'] = $val['user_id'];
                      Message::send('order.packageit',$data);   
                  }
              }else{
                  foreach ($clerk as $key => $val){
                      $data['member_id'] = $val['user_id'];
                      Message::send('package.outapply',$data);
                  }
                  
              }
          } catch (\Exception $e) {
              // 消息发送失败不影响打包流程，只记录日志
              // 可以在这里记录日志：\think\Log::error('消息发送失败: ' . $e->getMessage());
          }
         }
        
        if (!$res){
            return $this->renderError('打包包裹提交失败');
        }
        return $this->renderSuccess('打包包裹提交成功');
     }
     

     // 仓管员快速录单
     public function fastPack(){
        $line_id = $this->postData('line_id')[0];
        $pack_ids = $this->postData('pack_ids')[0];
        $length = $this->postData('length')[0];
        $width = $this->postData('width')[0];
        $height = $this->postData('height')[0];
        $payType = $this->postData('payType')[0];
        $weight = $this->postData('weight')[0];
        $address_id = $this->postData('address_id')[0];
        $remark = $this->postData('remark')[0];
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
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $storesetting = SettingModel::getItem('store');
        $free_rule = json_decode($line['free_rule'],true);
        $price = 0; // 总运费
        $allWeigth = 0;
        $caleWeigth = 0;
        $volumn = 0;
        //先生成包裹单
        $PackageModel = new PackageModel();
        $packOrder = [
            'order_sn' =>createSn(),
            'member_id' => $address['user_id'],
            'express_num' => createJysn(),
            'status' => 4,
            'storage_id' => $clerk['shop_id'],
            'remark' =>$remark,
            'line_id' => $line_id,
            'address_id' => $address_id,
            'country_id'=>$address['country_id'],
            'weight' => $weight,
            'width' =>$width,
            'height' => $height,
            'length' =>$length,
            'is_take' =>2,
            'entering_warehouse_time'=>getTime(),
            'created_time' => getTime(),  
            'updated_time' => getTime(),
            'wxapp_id' => \request()->get('wxapp_id'),
        ];
        $ids = $PackageModel->insertGetId($packOrder);
        
        // 创建包裹订单
        $inpackOrder = [
          'order_sn' => $storesetting['createSn']==10?createSn():createSnByUserIdCid($address['user_id'],$address['country_id']),
          'remark' =>$remark,
          'pay_type' => $payType,
          'pack_ids' => $ids,
          'pack_services_id' => $pack_ids, ///此值可作废
          'storage_id' => $clerk['shop_id'],
          'address_id' => $address_id,
          'free' => $price,
          'weight' => $weight,
          'width' =>$width,
          'height' => $height,
          'length' =>$length,
          'cale_weight' => $caleWeigth,
          'volume' => $volumn,
          'pack_free' => 0,
          'member_id' => $address['user_id'],
          'country' => $address['country'],
          'unpack_time' => getTime(),  //提交打包时间
          'created_time' => getTime(),  
          'updated_time' => getTime(),
          'status' => 1,
          'line_id' => $line_id,
          'operate_id' => 0,
          'wxapp_id' => \request()->get('wxapp_id'),
        ];
        $inpack = (new Inpack())->insertGetId($inpackOrder); 
        if (!$inpack){
           return $this->renderError('打包包裹提交失败');
        }
        
        //处理包装服务
        $res = (new InpackServiceModel())->doservice($inpack,$pack_ids);
        
        $inpackdate = (new Inpack())->where('id',$inpack)->find();
        //更新包裹的物流信息
        //物流模板设置
        $noticesetting = SettingModel::getItem('notice');
        if($noticesetting['packageit']['is_enable']==1){
            $packnum= (new PackageModel())->where('id',$ids)->value('express_num');
            Logistics::addLogPack($ids,$inpackdate['order_sn'],$noticesetting['packageit']['describe']);
            //修改包裹的记录
            Logistics::updateOrderSn($ids,$inpackdate['order_sn']);
        }
        
         $userData = User::detail($address['user_id']);
         
         $clerkData = (new Clerk())->where(['shop_id'=>$clerk['shop_id'],'is_delete'=>0,'mes_status' => 0])->find();
       
         if(!empty($clerkData)){
             $data=[
                'nickName' => $userData['nickName'],
                'userCode' => $userData['user_code'],
                'countpack' => 1,
                'packtime' => getTime(),
                'packid' => $inpack,
                'wxapp_id' => \request()->get('wxapp_id'),
                'remark' =>$remark,
              ];
             
              foreach($clerkData as $key => $val){
                  $data['clerkid'] = $val['user_id'];
                  Message::send('order.packageit',$data); 
              }
         }
        
        if (!$res){
            return $this->renderError('打包包裹提交失败');
        }
        return $this->renderSuccess('打包包裹提交成功');
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
         $this->user = $this->getUser(); 
         $query = [];
         $status = $this->request->param('type');
         $statusMap = [
           'all' =>[1,2,3,4,5,6,7,8],
           'verify' => [1],     
           'nopay' => [2],
           'no_send' => [3,4,5],
           'send' => [6,7],
           'complete' => [8]
         ];
         if ($status)
         $query['status'] = $statusMap[$status];
         if($status == 'nopay'){
             $query['is_pay'] = 0;
         }
         $query['member_id'] = $this->user['user_id']; 
         $list = (new Inpack())->getList($query);
         foreach ($list as &$value) {
            $value['num'] = count(explode(',',$value['pack_ids']));
            $value['total_free'] = round($value['free'] + $value['pack_free'] + $value['other_free'],2);
         }
         return $this->renderSuccess($list);
     }
     
      // 可以参与拼团的包裹列表
     public function pintuanpackageList(){
         $this->user = $this->getUser(); 
         $query = [];
         $query['status'] = [1,2,3,4,5];
         $query['is_pay'] = 0;
         $query['inpack_type'] = 0;
         $query['member_id'] = $this->user['user_id']; 
         $list = (new Inpack())->getList($query);
         foreach ($list as &$value) {
            $value['num'] = count(explode(',',$value['pack_ids']));
            $value['total_free'] = $value['free'] + $value['pack_free'] + $value['other_free'];
         }
         return $this->renderSuccess($list);
     }
     
     // 包裹列表 - 取消包裹
     public function cancle(){
         $id = $this->postData('id');
         $info = (new PackageModel())->field('id,status,source')->find($id[0]);
         
         if (!in_array($info['status'],[1,2,3,4,5,6,7,8])){
              return $this->renderError('该包裹已发货,无法为您拦截取消');
         }
         // 判断是否为拼团订单
         if ($info['source']==6){
             $SharingOrderItem = (new SharingOrderItem());
             $SharingOrderItem->removeByPack($info['id']);
         }
         $res = (new PackageModel())->where(['id'=>$info['id']])->update(['status'=>'-1']);
         if (!$res){
              return $this->renderError('取消失败');
         }
         return $this->renderSuccess("取消成功");
     }
     
     // 包裹列表 - 取消包裹
     public function canclePack(){
         $id = $this->postData('id');
         $info = (new Inpack())->field('id,status,is_pay,pack_ids,real_payment,order_sn,member_id,wxapp_id')->find($id[0]);
         if (!in_array($info['status'],[1,2,3,4])){
              return $this->renderError('该包裹已发货,无法为您拦截取消');
         }
          
         // 判断该订单是否已支付 且 实际付款金额>0
         if ($info['is_pay']==1 && $info['real_payment']>0){
             // 退款流程
            $remark =  '集运订单'.$info['order_sn'].'的支付退款';
            (new User())->banlanceUpdate('add',$info['member_id'],$info['real_payment'],$remark);
         }
         
         $padata= explode(',',$info['pack_ids']);
         if($info['pack_ids']){
             foreach($padata as $key=>$val){
                 (new PackageModel())->where('id',$val)->update(['status'=>2,'inpack_id'=>null]);
             }
         }
        
         $res = (new Inpack())->where(['id'=>$info['id']])->update(['status'=>'-1']);
         if (!$res){
              return $this->renderError('取消失败');
         }
         return $this->renderSuccess("取消成功");
     }

     // 待认领
     public function packageForTaker(){
         $this->user = $this->getUser(); 
         $kw = input('keyword');
         $where = [
           'is_delete' => 0,
           'is_take' =>1,
         ];
         if ($kw){
             $where['express_num'] = $kw;
         }
         $data = (new PackageModel())->with('packageimage.file')->where($where)->paginate(15);
         foreach($data as $k => $v){
              $data[$k]['express_num'] = func_substr_replace($v['express_num'],'*',4,6);
         }
         return $this->renderSuccess($data);
     }
     
     // 包裹认领
     public function getTakePackage(){
        $post = $this->postData();
        if (!$post['express_sn']){
          return $this->renderError('请输入快递单号');
        }
        $classIds = $post['class_ids'];
        if  (!$classIds && !is_string($classIds)){
          return $this->renderError('认领信息错误');
        }
        $classIdsArr = explode(',',$classIds);
        if (count($classIdsArr)<=0){
          return $this->renderError('认领信息错误');
        }
        $package = (new PackageModel())->where(['express_num'=>$post['express_sn']])->where('is_delete',0)->find();
       
        if (!$package){
          return $this->renderError('认领信息错误');
        }
        if($package['is_take'] ==2 && $package['member_id'] >0){
            return $this->renderError('包裹已被认领');
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
        
        // 获取包裹商品分类
        $orderIdItem = [];
        $orderItemList = (new PackageItemModel())->whereIn('order_id',$orderItem)->field('order_id,id,class_name')->select();
        if (!$orderItemList->isEmpty()){
            foreach ($orderItemList as $v){
                $orderIdItem[$v['order_id']][] = $v->toArray();
            }
        }
        
        // 获取包裹图片 - 返回所有图片作为数组
        $packageImages = [];
        try {
            $PackageImageModel = new \app\common\model\PackageImage();
            $imageRelations = $PackageImageModel->with(['file'])->whereIn('package_id', $orderItem)->select();
            
            if (!$imageRelations->isEmpty()) {
                foreach ($imageRelations as $relation) {
                    $packageId = $relation['package_id'];
                    if (!isset($packageImages[$packageId])) {
                        $packageImages[$packageId] = [];
                    }
                    // 添加图片URL到数组
                    if (isset($relation['file']) && isset($relation['file']['file_path'])) {
                        $packageImages[$packageId][] = $relation['file']['file_path'];
                    }
                }
            }
        } catch (\Exception $e) {
            // 图片加载失败不影响主流程
        }
        
        // 获取国家信息
        $countries = [];
        try {
            $countryIds = array_filter(array_unique(array_column($data, 'country_id')));
            if (!empty($countryIds)) {
                $countryList = (new \app\common\model\Country())->whereIn('id', $countryIds)->select();
                foreach ($countryList as $country) {
                    $countries[$country['id']] = $country->toArray();
                }
            }
        } catch (\Exception $e) {
            // 国家信息加载失败不影响主流程
        }
        
        // 组装数据
        foreach($data as $k => $v){
            if (isset($orderIdItem[$v['id']])) {
                $data[$k]['class_name'] = implode(',',array_column($orderIdItem[$v['id']],'class_name'));
            }
            // 返回图片数组
            if (isset($packageImages[$v['id']]) && !empty($packageImages[$v['id']])) {
                $data[$k]['images'] = $packageImages[$v['id']];
            }
            if (isset($countries[$v['country_id']])) {
                $data[$k]['country'] = $countries[$v['country_id']];
            }
            // 唛头字段重命名为mark以保持一致性
            if (isset($v['usermark']) && !empty($v['usermark'])) {
                $data[$k]['mark'] = $v['usermark'];
            }
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
        $this->user = $this->getUser(); 
        if(!\request()->get('token')){
            return $this->renderError('请先登录');
        }
        
        // 获取分页参数
        $page = \request()->get('page', 1);
        $pageSize = \request()->get('page_size', 20);
        $keyword = \request()->get('keyword', '');
        $status = \request()->get('status');
        
        $field = 'id,inpack_id,country_id,order_sn,storage_id,express_num,created_time,source,status,usermark,weight,length,width,height,volume';
        
        // 构建基础查询条件
        $where = [
          'is_delete' => 0,
          'member_id' => $this->user['user_id']
        ];
        
        // 构建查询
        $query = (new PackageModel())->where($where);
        
        // 处理状态筛选 - 根据原有逻辑
        if($status == 2){
            // 状态2表示已入库，包含状态2,3,4
            $query->whereIn('status',[2,3,4]);
        } elseif($status == 56){
            // 状态56表示已出库（待支付+已支付）
            $query->whereIn('status',[5,6]);
        } else {
            // 其他状态直接查询
            $query->where('status', $status);
        }
        
        // 添加关键词搜索 - 支持多字段模糊搜索
        if(!empty($keyword)){
            $query->where(function($q) use ($keyword) {
                $q->whereOr('express_num', 'like', '%' . $keyword . '%')
                  ->whereOr('order_sn', 'like', '%' . $keyword . '%')
                  ->whereOr('usermark', 'like', '%' . $keyword . '%');
            });
        }
        
        // 使用分页查询，包含关联数据（不使用嵌套关联，避免bind问题）
        // 注意：packageimage 关联可能受全局作用域影响，需要单独处理
        $data = $query->with(['country','storage','inpack'])
                      ->field($field)
                      ->order('created_time DESC')
                      ->paginate($pageSize, false, ['page' => $page]);
        
        // 优化后的数据处理 - 直接使用关联数据
        $list = $data->toArray();
        
        // 批量获取所有包裹的物品分类（避免N+1查询）
        $packageIds = [];
        if(isset($list['data']) && is_array($list['data'])){
            $packageIds = array_column($list['data'], 'id');
        }
        
        // 一次性查询所有包裹的图片（使用原生查询避免全局作用域问题）
        $imageFiles = [];
        $packageImageMap = [];
        if(!empty($packageIds)){
            // 直接查询 package_image 表，不受全局作用域限制
            $packageImages = \think\Db::name('package_image')
                ->whereIn('package_id', $packageIds)
                ->field('package_id,image_id')
                ->select();
            
            // 收集所有图片ID
            $allImageIds = [];
            foreach($packageImages as $img){
                $allImageIds[] = $img['image_id'];
                if(!isset($packageImageMap[$img['package_id']])){
                    $packageImageMap[$img['package_id']] = [];
                }
                $packageImageMap[$img['package_id']][] = $img['image_id'];
            }
            
            // 批量查询文件信息
            if(!empty($allImageIds)){
                $files = \think\Db::name('upload_file')
                    ->whereIn('file_id', array_unique($allImageIds))
                    ->field('file_id,file_name,file_url,storage')
                    ->select();
                
                foreach($files as $file){
                    // 手动构建完整路径
                    if($file['storage'] === 'local'){
                        $filePath = request()->domain() . '/uploads/' . $file['file_name'];
                    } else {
                        $filePath = $file['file_url'] . '/' . $file['file_name'];
                    }
                    $imageFiles[$file['file_id']] = $filePath;
                }
            }
        }
        
        // 一次性查询所有包裹的物品分类
        $packageItems = [];
        if(!empty($packageIds)){
            $items = (new PackageItemModel())
                ->whereIn('order_id', $packageIds)
                ->field('order_id,class_name')
                ->select();
            
            if (!$items->isEmpty()) {
                foreach ($items as $item) {
                    $orderId = $item['order_id'];
                    if (!isset($packageItems[$orderId])) {
                        $packageItems[$orderId] = [];
                    }
                    $packageItems[$orderId][] = $item['class_name'];
                }
            }
        }
        
        // 处理每个包裹的数据
        if(isset($list['data']) && is_array($list['data'])){
            foreach($list['data'] as $k => $v){
                // 处理图片数组 - 使用批量查询的文件信息
                $images = [];
                if (isset($packageImageMap[$v['id']])) {
                    foreach ($packageImageMap[$v['id']] as $imageId) {
                        if (isset($imageFiles[$imageId])) {
                            $images[] = $imageFiles[$imageId];
                        }
                    }
                }
                $list['data'][$k]['images'] = $images;
                
                // 唛头字段重命名为mark
                if (isset($v['usermark']) && !empty($v['usermark'])) {
                    $list['data'][$k]['mark'] = $v['usermark'];
                }
                
                // 使用批量查询的结果设置物品分类
                if (isset($packageItems[$v['id']])) {
                    $list['data'][$k]['class_name'] = implode(',', $packageItems[$v['id']]);
                }
            }
        }
        
        return $this->renderSuccess($list);
     }
     
     
     // 搜索未入库已入库的包裹数量
     public function searchlist(){
        $this->user = $this->getUser(); 
        if(!\request()->get('token')){
            return $this->renderError('请先登录');
        }
        $field = 'id,country_id,order_sn,storage_id,express_num,created_time,source,status';
        $where = [
          'is_delete' => 0,
          'status' =>\request()->get('status'),
          'member_id' => $this->user['user_id']
        ];
        $keyword = \request()->get('keyword');
        $data = (new PackageModel())->querysearch($where,$field,$keyword);
        $data = $this->getPackItemList($data);
        return $this->renderSuccess($data);
     }
     
     
     
     //统计各个状态的包裹的数量
     public function countpack(){
        $this->user = $this->getUser(); 
        $PackageModel = new PackageModel();
        if(!\request()->get('token')){
            return $this->renderError('请先登录');
        }
        $where = [
          'is_delete' => 0,
          'member_id' => $this->user['user_id']
        ];
        
        // 已出库数量（待支付+已支付）
        $outboundcount = $PackageModel->where($where)->whereIn('status', [5, 6])->count();
        
        $data = [
            'nocount' => $PackageModel->querycount($where,$status=1),
            'yescount' => $PackageModel->querycount($where,$status=2),
            'outboundcount' => $outboundcount, // 已出库（待支付+已支付）
            'yessend' => $PackageModel->querycount($where,$status=9), // 已发货
            'completedcount' => $PackageModel->querycount($where,$status=10), // 已完成（已收货）
            'procount' => $PackageModel->querycount($where,$status=-1),
        ];
        return $this->renderSuccess($data);
     }
     
     
     /**
      * 恢复问题件
      * 更新于2022年5月7日
      * by feng
      */
     public function rechangepackage(){
        if(!\request()->post('token')){
            return $this->renderError('请先登录');
        }
        //找到包裹最新的状态，在日志记录中找到最新一条的状态值
        $packageStatus = (new Logistics())->where('express_num',\request()->post('express_num'))->order('id desc')->limit(1)->find();
        //恢复状态为最新的状态
        $res = (new PackageModel())->where('id',\request()->post('id'))->update(['status' => $packageStatus['status']]);
      
        if(!$res){
            return $this->renderError('恢复问题件失败');
        }
               
        $field = 'id,country_id,order_sn,storage_id,express_num,created_time,status';
        $where = [
          'is_delete' => 0,
          'status' => -1,
          'member_id' => $this->user['user_id']
        ];
        $data = (new PackageModel())->query($where,$field);
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
          $classItem = [];
          if (isset($post['class_ids'])){
              $class_ids = $post['class_ids'];
              
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
          }
          $packModel = new PackageModel();
          $packItemModel = new PackageItemModel();
        //   $result = $packModel->where('express_num',$post['express_sn'])->find();
        //   if(!$result){
        //       return $this->renderError('包裹不已存在');
        //   }
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
     
    /***
     * 集运单提交支付接口
     * 可用的支付接口
     */ 
    public function payType(){
        $setting = SettingModel::detail("notice")['values'];
        dump();die;
    }
    

    /***
     * 集运单提交支付接口
     * 去支付
     */
     public function doPay(){
         $id = $this->postData('id')[0]; //集运单id
         $couponId = $this->postData('coupon_id')[0]; //优惠券id
         $paytype = $this->postData('paytype')[0];  //支付类型
         if($paytype==20){
             $paytype = 1;
         }
         if($paytype==10){
             $paytype = 2;
         }
         $client = $this->postData('client')[0];  //支付所在客户端 client:"MP-WEIXIN"
         $pack = (new Inpack())->field('id,status,pack_ids,free,order_sn,pack_free,other_free,remark,storage_id,is_pay,pay_order')->find($id);
         //生成支付订单号
         $payorderSn = createOrderSn();
         (new Inpack())->where('id',$pack['id'])->update(['pay_order'=>$payorderSn]);
         if ($pack['status'] != 2 && $pack['is_pay'] != 2) {
            return $this->renderError('包裹状态不正确');
         }
         $user = $this->user;
         $amount = $pack['free'] + $pack['pack_free'] + $pack['other_free'];
         if($couponId){
             $amount = $this->UseConponPrice($couponId,$amount);
         }
       
     
         if($pack['status']!=2){
            $update['status'] =  $pack['status'];
         }else{
             $update['status'] =3;
         }
         
         Db::startTrans();
         $update['real_payment'] = $amount;
         $update['is_pay'] = 1;
         $update['pay_time'] = getTime();
         $coupon['user_coupon_id'] = $couponId;
         $coupon['user_coupon_money'] = $pack['free'] + $pack['pack_free'] + $pack['other_free'] - $amount; //计算优惠了多少费用；
         try {
             (new Inpack())->where('id',$pack['id'])->update($update);
             (new Inpack())->where('id',$pack['id'])->update($coupon);
             //更新优惠券的状态为is_use
             (new UserCoupon())->where('user_coupon_id',$couponId)->update(['is_use'=>1]);
             
             $update['status'] = 6;
            //  dump($pack['pack_ids']);die;
             $up = (new PackageModel())->where('id','in',explode(',',$pack['pack_ids']))->update($update);
            // dump((new PackageModel())->getLastsql());die;
             if (!$up){
                Db::rollback();
                return $this->renderError('支付失败,请重试');
             }
             if($paytype==1){
               
                    // 构建微信支付
                    $payment = PaymentService::wechat(
                        $user,
                        $pack['id'],
                        $payorderSn,
                        $amount,
                        OrderTypeEnum::TRAN
                    );
                    // 支付状态提醒
                    $message = ['success' => '支付成功', 'error' => '订单未支付'];
                    return $this->renderSuccess(compact('payment', 'message'), $message);
             }elseif($paytype==2){
                    if ($user['balance']<$amount){
                        return $this->renderError('余额不足,请充值');
                     }
                    //减少余额
                     $memberUp = (new User())->where(['user_id'=>$user['user_id']])->update([
                       'balance'=>$user['balance']-$amount,
                       'pay_money' => $user['pay_money']+ $amount,
                     ]);
                           
                     if (!$memberUp){
                         Db::rollback();
                         return $this->renderError('支付失败,请重试');
                     }
                     //记录支付类型
                      $payres = (new Inpack())->where('id',$pack['id'])->update(['is_pay_type' => 2]);
                      if(!$payres){
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
                  
                    // $message = ['success' => '支付成功', 'error' => '支付失败'];
                    // return $this->renderSuccess(compact('message'), $message);
                  
             }elseif($paytype==3){
                 // 构建微信支付
                    $payment = PaymentService::Hantepay(
                        $user,
                        $pack['id'],
                        $payorderSn,
                        $amount,
                        OrderTypeEnum::TRAN
                    );
                    // 支付状态提醒
                    $message = ['success' => '支付成功', 'error' => '订单未支付'];
                    return $this->renderSuccess(compact('payment', 'message'), $message);
             }
             
         //处理通知信息
         $clerk = (new Clerk())->where('shop_id',$pack['storage_id'])->where('mes_status',0)->where('is_delete',0)->select();
       
         if(!empty($clerk)){
         $data=[
            'amount' =>$amount,
            'paytime' => getTime(),
            'packid' => $id,
            'wxapp_id' => \request()->get('wxapp_id'),
            'remark' => $pack['remark'],
          ];
           
          foreach ($clerk as $key => $val){
                 $data['clerkid'] = $val['user_id'];
                 $reeee = Message::send('order.paymessage',$data);  
          }
         }
          // 处理分销逻辑的源头
          $this->dealerData(['amount'=>$amount,'order_id'=>$id],$user);
          
         }catch(\Exception $e){
              dump($e);die;
             return $this->renderError('支付失败,请重试');
         }
         Db::commit();
         return $this->renderSuccess('支付成功');
     }
     
     /***
     * 集运单提交支付接口
     * 去支付
     */
     public function newdoPay(){
         $id = $this->postData('id')[0]; //集运单id
         $couponId = $this->postData('coupon_id')[0]; //优惠券id
         $paytype = $this->postData('paytype')[0];  //支付类型
         $client = $this->postData('client')[0];  //支付所在客户端 client:"MP-WEIXIN"
         $pack = (new Inpack())->field('id,status,pack_ids,free,order_sn,pack_free,other_free,remark,storage_id,is_pay,pay_order')->find($id);
         //生成支付订单号
         $payorderSn = createOrderSn();
         (new Inpack())->where('id',$pack['id'])->update(['pay_order'=>$payorderSn]);
         if ($pack['status'] != 2 && $pack['is_pay'] != 2) {
            return $this->renderError('包裹状态不正确');
         }
         $user = $this->user;
         $amount = $pack['free'] + $pack['pack_free'] + $pack['other_free'];
         if($couponId){
             $amount = $this->UseConponPrice($couponId,$amount);
         }
         if($pack['status']!=2){
            $update['status'] =  $pack['status'];
         }else{
            $update['status'] =3;
         }
         
         Db::startTrans();
         $update['real_payment'] = $amount;
         $update['is_pay'] = 1;
         $update['pay_time'] = getTime();
         $coupon['user_coupon_id'] = $couponId;
         $coupon['user_coupon_money'] = $pack['free'] + $pack['pack_free'] + $pack['other_free'] - $amount; //计算优惠了多少费用；
         try {
             (new Inpack())->where('id',$pack['id'])->update($update);
             (new Inpack())->where('id',$pack['id'])->update($coupon);
             //更新优惠券的状态为is_use
             (new UserCoupon())->where('user_coupon_id',$couponId)->update(['is_use'=>1]);
             
             $update['status'] = 6;
            //  dump($pack['pack_ids']);die;
             $up = (new PackageModel())->where('id','in',explode(',',$pack['pack_ids']))->update($update);
            // dump((new PackageModel())->getLastsql());die;
             if (!$up){
                Db::rollback();
                return $this->renderError('支付失败,请重试');
             }
             
             switch ($paytype) {
                case 10:
                    // 构建余额支付
                    if ($user['balance']<$amount){
                        return $this->renderError('余额不足,请充值');
                    }
                    //减少余额
                    $memberUp = (new User())->where(['user_id'=>$user['user_id']])->update([
                       'balance'=>$user['balance']-$amount,
                       'pay_money' => $user['pay_money']+ $amount,
                    ]);
                           
                    if (!$memberUp){
                        Db::rollback();
                        return $this->renderError('支付失败,请重试');
                    }
                    //记录支付类型
                    $payres = (new Inpack())->where('id',$pack['id'])->update(['is_pay_type' => 2]);
                    if(!$payres){
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
                  
                     break;
                case 20:
                    // 构建微信支付
                     
                    $payment = PaymentService::wechat(
                        $user,
                        $pack['id'],
                        $payorderSn,
                        $amount,
                        OrderTypeEnum::TRAN
                    );
                    // 支付状态提醒
                    $message = ['success' => '支付成功', 'error' => '订单未支付'];
                    return $this->renderSuccess(compact('payment', 'message'), $message);
                    break;
                
                
                case 30: 
                    // 构建汉特支付
                    $payment = PaymentService::Hantepay(
                        $user,
                        $pack['id'],
                        $payorderSn,
                        $amount,
                        OrderTypeEnum::TRAN
                    );
                    // 支付状态提醒
                    $message = ['success' => '支付成功', 'error' => '订单未支付'];
                    return $this->renderSuccess(compact('payment', 'message'), $message);
                     break;
                case 40:
                    $payment = PaymentService::Omipay(
                        $user,
                        $pack['id'],
                        $payorderSn,
                        $amount,
                        OrderTypeEnum::TRAN
                    );
                    $message = ['success' => '支付成功', 'error' => '订单未支付'];
                    return $this->renderSuccess(compact('payment', 'message'), $message);
                     break;
                     
                 default:
                     // code...
                     break;
             }

             
         //处理通知信息
         $clerk = (new Clerk())->where('shop_id',$pack['storage_id'])->where('mes_status',0)->where('is_delete',0)->select();
       
         if(!empty($clerk)){
         $data=[
            'amount' =>$amount,
            'paytime' => getTime(),
            'packid' => $id,
            'wxapp_id' => \request()->get('wxapp_id'),
            'remark' => $pack['remark'],
          ];
           
          foreach ($clerk as $key => $val){
                 $data['clerkid'] = $val['user_id'];
                 $reeee = Message::send('order.paymessage',$data);  
          }
         }
          // 处理分销逻辑的源头
          $this->dealerData(['amount'=>$amount,'order_id'=>$id],$user);
          
         }catch(\Exception $e){
              dump($e);die;
             return $this->renderError('支付失败,请重试');
         }
         Db::commit();
         return $this->renderSuccess('支付成功');
     }
     

     // 包裹信息
     public function details(){
        $field_group = [
           'edit' => [
              'id,order_sn,storage_id,country_id,express_name,express_num,express_id,free,pack_free,price,address_id,status,line_id,remark,weight,usermark'
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
     
     
     //集运订单信息详情
     public function details_pack(){
         $field_group = [
            'edit' => [
              'id,order_sn,pack_ids,storage_id,free,pack_free,other_free,address_id,weight,cale_weight,volume,length,width,height,status,line_id,remark,country_id,t_order_sn,user_coupon_id,user_coupon_money,pay_type,is_pay,is_pay_type'
            ],
         ];
        $id = \request()->post('id');
        $couponId = \request()->post('coupon_id');//优惠券id
        
        // 修复：如果没有传 method 参数，默认使用 'edit'
        $methodData = $this->postData('method');
        $method = isset($methodData[0]) ? $methodData[0] : 'edit';
        
        $data = (new Inpack())->getDetails($id,$field_group[$method]);
        $package = (new PackageModel())->where('id','in',explode(',',$data['pack_ids']))->field('id,express_num,price,express_name,entering_warehouse_time,remark,weight,height,length,width')->with(['packageimage.file'])->select();
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
        $data['free_total'] = round($data['free'] + $data['pack_free'] + $data['other_free'],2);
        $data['fyouhui_total'] = $this->UseConponPrice($couponId,$data['free_total']);
        $data['item'] = $package;
        if (isset($data['line']['image'])){
            $data['image'] = $data['line']['image'];
        }
        return $this->renderSuccess($data);
     }
     
     // 添加驼峰命名的别名方法，用于前端调用
     public function detailsPack(){
         return $this->details_pack();
     }
     
     //计算使用优惠券后的价格；
     public function UseConponPrice($couponId,$total){
         $totalFree = 0;
        if(isset($couponId)){
             $couponData = (new UserCoupon())->where('user_coupon_id',$couponId)->find();
             
             switch ($couponData['coupon_type']['value']) {
                 case '10':
                     $totalFree = $total - $couponData['reduce_price'];
                     break;
                 case '20':
                     $totalFree = $total*($couponData['discount']/10);
                     break;
             }
             return sprintf("%01.2f", $totalFree);
        }
       return sprintf("%01.2f", $totalFree);
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
     
     /**
      * 轨迹列表
      * 重构时间 2023年12月08日
      * 输入参数进行国际和国内信息的查询，对所有快递单号进行检索属于的集运单以及对应的国际单号
      * 并将该国际单号数据进行获取展示；
      * */
     public function getlogistics(){
        $express = $this->postData('code')[0];
        $Logistics = new Logistics();
        $PackageModel = new PackageModel();
        $DitchModel= new DitchModel();
        $Inpack = new Inpack();
        $Express = new Express();
        //查询单号是否有国内包裹的物流信息;
        $packData = $PackageModel->where(['express_num'=>$express,'is_delete' => 0])->find();
        $inpackData = $Inpack->where('order_sn|t_order_sn',$express)->where(['is_delete' => 0])->find();  //国际单号
        $inpackData2 = $Inpack->where(['t2_order_sn'=>$express,'is_delete' => 0])->find();  //转单号
        $inpackData3 = $Inpack->where(['t2_order_sn'=>$express,'is_delete' => 0])->find();  //转单号
        
        $logic = $Logistics->getZdList($packData['express_num'],$express_code,$packData['wxapp_id']);
        //查询包裹系统内部的轨迹
        
        //查询系统内部订单的轨迹
        
        //查询发货后的物流轨迹
        return $this->renderSuccess(compact('logic'));
     }
     
     /**
      * 轨迹列表
      * 重构时间 2022年06月27日
      * 输入参数进行国际和国内信息的查询，对所有快递单号进行检索属于的集运单以及对应的国际单号
      * 并将该国际单号数据进行获取展示；
      * */
     public function logicist(){
        $express = $this->postData('code')[0];
        $logic = $logic4 = $logictik =[];
        $result=[];
        $logib = [];
        $logia = [];
        $logguoji=[];
        $logzd = [];
        $logici = [];
        $logicti = [];
        $Logistics = new Logistics();
        $PackageModel = new PackageModel();
        $DitchModel= new DitchModel();
        $Inpack = new Inpack();
        $Express = new Express();
        $setting = SettingModel::detail("notice")['values'];
        //查询出来这个单号是包裹单号、国际单号、转单号
        $packData = $PackageModel->where(['express_num'=>$express,'is_delete' => 0])->find();
        
        $inpackData = $Inpack->where('t_order_sn',$express)->where(['is_delete' => 0])->find(); //国际单号
        $inpackData2 = $Inpack->where(['t2_order_sn'=>$express,'is_delete' => 0])->find();  //转单号
        $inpackData4 = $Inpack->where(['order_sn'=>$express,'is_delete' => 0])->find();
        //如果是包裹单号，可以反查下处于哪个集运单；
        //   dump($inpackData);die;
        
        if(!empty($packData)){
            //查出的系统内部物流信息
            $logic = $Logistics->getList($express);
              
            if(count($logic)>0){
                // dump($inpackData);die;
                $logia = $Logistics->getorderno($logic[0]['order_sn']);
            }
            $express_code = $Express->getValueById($packData['express_id'],'express_code');
            // dump($express_code);die; 
            if($setting['is_track_yubao']['is_enable']==1){//如果预报推送物流，则查询出来
                $logib = $Logistics->getZdList($packData['express_num'],$express_code,$packData['wxapp_id']);
                
            }
            
            
            // $inpackData3 = $Inpack->where('id', $packData['inpack_id'])->where('is_delete',0)->find();
            // if(!empty($inpackData3) && !empty($inpackData3['t_order_sn'])){
            //     $logzd = $Logistics->getZdList($inpackData3['t_order_sn'],$inpackData3['t_number'],$inpackData3['wxapp_id']);
            // }
            // if(!empty($inpackData3) && !empty($inpackData3['t2_order_sn'])){
            //     $logguoji = $Logistics->getZdList($inpackData3['t2_order_sn'],$inpackData3['t2_number'],$inpackData3['wxapp_id']);
            // }
        
            $logic = array_merge($logia,$logib,$logic);
            if(empty($logic)){
                $inpackData2 = $Inpack->where('id',$packData['inpack_id'])->where(['is_delete' => 0])->find(); //国际单号
                // dump($inpackData2);die;
            }
        }
        

        if(!empty($inpackData) ){
            if($inpackData['transfer']==0){
                $ditchdatas = $DitchModel->where('ditch_id','=',$inpackData['t_number'])->find();
                // dump($ditchdatas);die;
                 //锦联
                if($ditchdatas['ditch_no']==10001){
                    $jlfba =  new jlfba(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token']]);
                    $result = $jlfba->query($express);
                }
                //百顺达
                if($ditchdatas['ditch_no']==10002){
                    $bsdexp =  new bsdexp(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token']]);
                    //   dump($bsdexp);die;
                    $result = $bsdexp->query($express);
                }
                //K5
                if($ditchdatas['ditch_no']==10003){
                    $kingtrans =  new kingtrans(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $kingtrans->query($express);
                }
                //华磊api
                if($ditchdatas['ditch_no']==10004){
                    $Hualei =  new Hualei(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $Hualei->query($express);
                    //  dump($result);die;
                }
                
                //星泰api
                if($ditchdatas['ditch_no']==10005){
                    $Xzhcms5 =  new Xzhcms5(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $Xzhcms5->query($express);
                }
                
                //澳联
                if($ditchdatas['ditch_no']==10006){
                    $Aolian =  new Aolian(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $Aolian->query($express);
                }
                
                //易抵达
                if($ditchdatas['ditch_no']==10007){
                    $Yidida =  new Yidida(['key'=>$ditchdatas['app_key'],'token'=>$ditchdatas['app_token'],'apiurl'=>$ditchdatas['api_url']]);
                    $result = $Yidida->query($express);
                }
                //当是自有专线物流时
                // $logictjki = [];
                // if($ditchdatas['type']==0){
                //   $logictjki = $Logistics->getorderno($inpackData['order_sn']);  
                // }
                // //查询国际物流部分
                // // $logic = $Logistics->getZdList($inpackData['t_order_sn'],$inpackData['t_number'],$inpackData['wxapp_id']);
                // // dump($result);die;
                // $logic = array_merge($result,$logictjki);
                $logic = $result;
             
            }else{
                $logic = $Logistics->getZdList($inpackData['t_order_sn'],$inpackData['t_number'],$inpackData['wxapp_id']);
            }
            // dump($inpackData);die;
            $packinck = $PackageModel->where(['inpack_id'=>$inpackData['id'],'is_delete' => 0])->find();
           
            if(!empty($packinck)){
                $logictik = $Logistics->getList($packinck['express_num']);
                //  dump($packinck);die;
            }
            if(empty($logia) && (empty($result) || $ditchdatas['type']==0)){
                $logic543 = $Logistics->getorderno($inpackData['order_sn']); 
                $logic = array_merge($logic,$logic543);
           
            }
            
            // $logictii = $Logistics->getlogisticssn($inpackData['t_order_sn']);dump($logictii);die;
            $logic = array_merge($logic,$logictik);
        //   dump($logic);die;
            // $logic = array_merge($logic,$result);
        }
        
        if(!empty($inpackData2)){
            $logici = $Logistics->getZdList($inpackData2['t2_order_sn'],$inpackData2['t2_number'],$inpackData2['wxapp_id']);
        }
 
        $logic = array_merge($logic,$logici);

        return $this->renderSuccess(compact('logic'));
     }
     
     // 包裹统计
     public function packTotal(){
         $model =  (new Inpack());
         $return = [
           'no_pay' => $model->where('status',2)->where('member_id',$this->user['user_id'])->where('is_delete',0)->count(),
           'verify' => $model->whereIn('status',[1])->where('member_id',$this->user['user_id'])->where('is_delete',0)->count(),
           'no_send' => $model->whereIn('status',[3,4,5])->where('member_id',$this->user['user_id'])->where('is_delete',0)->count(),
           'send' => $model->whereIn('status',[6,7])->where('member_id',$this->user['user_id'])->where('is_delete',0)->count(),
           'complete' => $model->whereIn('status',[8])->where('member_id',$this->user['user_id'])->where('is_delete',0)->count(),
         ];
         return $this->renderSuccess($return);
     }

     // 签收
     public function signedin(){
         $id = $this->postData('id')[0];
         $pack = (new Inpack())->find($id);
         if (!$pack){
             return $this->renderError('包裹数据错误');
         }
         if ($pack['status']!=6){
             return $this->renderError('包裹状态错误');
         }

         // 更新订单状态为已完成(8)而不是已收货(7)
         (new Inpack())->where(['id'=>$id])->update(['status'=>8]);
         $pack_ids = explode(',',$pack['pack_ids']);
         $up = (new PackageModel())->where('id','in',$pack_ids)->update(['status'=>10]);
         foreach($pack_ids as $v){
            Logistics::add($v,'包裹已经本人签收,如有问题,请联系客服');
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
   
     //获取默认国家
     public function getCountryName(){
         //根据用户获取该用户的默认地址，获取默认地址上的默认国家id
         //根据国家id获取国家名称，id等信息
         $this->user = $this->getUser();
         $counrty = (new UserAddress())->where(['address_id'=>($this->user)['address_id']])->find();
         return $this->renderSuccess($counrty);
     }
   
     // 国家列表
     public function country(){
        $where = '';
        $k = input('keyword');
        $newDataPyin = [];
        if ($k){
            $where = $k; 
        }else{
            $queryHotCountry = (new Country())->queryHotCountry($where);
            count($queryHotCountry)>0 && $newDataPyin['热门'] = $queryHotCountry;
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
        
        foreach ($AZGROUP as $v){
            if (isset($dataPyin[$v]))
                $newDataPyin[$v] = $dataPyin[$v];  
        }
        return $this->renderSuccess($newDataPyin);
     }
   
     // 运费查询
     public function getFree(){
         $country = $this->postData('country_id');
     }

     // 快递列表
     public function express(){
        $data = (new Express())->queryExpress();
        return $this->renderSuccess($data);
     }

    // 线路列表  过20220916后淘汰
    public function line(){
        $data = (new Line())->getLine([]);
        return $this->renderSuccess($data);
    }
    
    // 线路列表   版本20220916
    public function lineplus(){
        $addressId= input('address_id');
        if(!empty($addressId)){
            $data = (new Line())->getLineplus($addressId);
        }else{
            $data = (new Line())->getLine([]); 
        }
        
        return $this->renderSuccess($data);
    }

     // 仓库列表
     public function storage(){
        $this->user = $this->getUser();  
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
     
     // 处理分销逻辑
     public function dealerData($data,$user){
        // 分销商基本设置
        $setting = SettingDealerModel::getItem('basic');
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
        // 注意：API端使用 DealerUser::detail 获取分销商详情及等级
        $firstUser = DealerUser::detail($firstUserId);
        if ($firstUser && isset($firstUser['rating']['setting']['first_money'])) {
             $firstRate = $firstUser['rating']['setting']['first_money'];
        }
        $firstMoney = $data['amount'] * ($firstRate / 100);

        //给用户分配余额
        $dealeruser->grantMoney($firstUserId,$firstMoney);
        // API端 RefereeModel 应该是 app\api\model\dealer\Referee，检查是否兼容 updateRefereeStats
        // app\api\model\dealer\Referee 继承 common\Referee，common\Referee 有 updateRefereeStats
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
                $secondUser = DealerUser::detail($secondUserId);
                if ($secondUser && isset($secondUser['rating']['setting']['second_money'])) {
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
                $thirdUser = DealerUser::detail($thirdUserId);
                if ($thirdUser && isset($thirdUser['rating']['setting']['third_money'])) {
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
        
        $resCapi = (new Capital())->saveAll($dealerCapital);
        $resDeal = (new DealerOrder())->save($dealerOrder);
        if(!$resCapi || !$resDeal){
            return false;
        }
        return true;
     }
     
}
