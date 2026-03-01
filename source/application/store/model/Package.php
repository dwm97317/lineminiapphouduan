<?php
namespace app\store\model;
use app\common\model\Package as PackageModel;
use app\common\model\User;
use think\Model;
use think\Db;
use traits\model\SoftDelete;
use app\api\model\Logistics;
use app\store\model\PackageImage;
use app\common\service\Email;
use app\store\model\store\Shop;
use app\common\service\Message;
use app\common\model\Setting as SettingModel;

use app\api\model\Express;
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
 * 订单管理
 * Class Order
 * @package app\store\model
 */
class Package extends PackageModel
{

    protected $createTime = null;
    protected $updateTime = null;
    
    public function getWxappId(){
        return self::$wxapp_id;
    }
    
    //保存货架
    public function saveshelf($shelf,$package_id){
        $upShelf = [
          'shelf_unit_id' => $shelf['shelf_unit_id'],
          'express_num' => $shelf['express_num'],
          'user_id' => $shelf['user_id']??0,
          'created_time' => getTime(),
          'pack_id' => $package_id,
         ];
         $shelfdata = (new ShelfUnitItem())->where('pack_id',$package_id)->find();
         if($shelfdata){
                (new ShelfUnitItem())->where('pack_id',$package_id)->update(['shelf_unit_id'=>$shelf['shelf_unit_id']]);
         }else{
            $res = (new ShelfUnitItem())->post($upShelf);
         }
    }
    
    //查询物流轨迹
    public function getlog($query=[]){
        $PackageModel = new PackageModel();
        $Logistics = new Logistics();
        $Express = new Express();
        $setting = SettingModel::detail("notice")['values'];
        $packData = $PackageModel->where(['express_num'=>$query['number'],'is_delete' => 0])->find();
        $logib = [];
        if(!empty($packData)){
            //查出的系统内部物流信息
            $logic = $Logistics->getList($query['number']); 
            $express_code = $Express->getValueById($packData['express_id'],'express_code');
           
            if($setting['is_track_yubao']['is_enable']==1 && !empty($express_code)){//如果预报推送物流，则查询出来
                $logib = $Logistics->getZdList($packData['express_num'],$express_code,$packData['wxapp_id']);
            }
     
            $logic = array_merge($logic,$logib);
            return $logic;
        }
        return [];
    }
    
    /**
     * 获取某天的总入库包裹
     * @param null $startDate
     * @param null $endDate
     * @return float|int
     */
    public function getPackageOnday($startDate = null, $endDate = null)
    {
        $filter = [
            'is_delete' => 0,
            // 'status' => 2
        ];
        if (!is_null($startDate) && !is_null($endDate)) {
            $filter['entering_warehouse_time'] = ["between time",[$startDate, date('Y-m-d',strtotime($endDate)+86400)]];        
        }
        return $this->where($filter)->count('id');
    }
    
    //通过单号查询是否有对应单号
    public function getNumber($number){
       return $this->alias('pa')
       ->with(['packageimage.file','Member','country','shelfunititem.shelfunit.shelf'])
       ->where('pa.express_num',$number)
       ->find();
    }

    //PC端包裹后台录入
    public function uodatepackStatus($data){
        if(!$this->onCheck($data)){
             return false;
        }
        //   dump($data);die;
        // self::$wxapp_id == 10013 && file_put_contents("包裹入库.txt", "时间：".getTime().", 数据:".json_encode($data)."\r\n", FILE_APPEND);
        $tyoi = stripos($data['express_num'], "JD");
        if($tyoi==0){
            $ex = explode('-',$data['express_num']);
            $data['express_num'] = $ex[0];
        }
        
        $result = $this->where('express_num',$data['express_num'])->where('is_delete',0)->find();
   
        if($result){
            $data['id'] = $result['id'];
        }

        
        $noticesetting = SettingModel::getItem('notice');
        
        $resull = strstr($data['express_num'],'JD');
      
        if(!$resull){
            $res=preg_match('^\w+$^',$data['express_num']);
            $res1=preg_match('/^[^\s]*$/',$data['express_num']);
            // dump($res);die;
            if(!$res || !$res1){
                $this->error = "快递单号包含特殊字符";
                return false;
            }
        }

        
        if(!empty($data['user_code'])){
            $userData = (new User())->where('user_code',$data['user_code'])->find();
            isset($userData) && $data['user_id'] = $userData['user_id'];
        }
        
        if($result['member_id']){
            $data['user_id'] = $result['member_id'];
        }
        
        // dump($data['class_ids']);die;
         
         if(isset($data['class_ids'])){
            //  $class_ids = isset($data['class_ids'])?$data['class_ids']:[];
            $classItem = $this->parseClass($data['class_ids']);
             foreach ($classItem as $k => $val){
                   $classItem[$k]['class_id'] = $val['category_id'];
                   $classItem[$k]['express_name'] = '';
                   $classItem[$k]['class_name'] = $val['name'];
                   $classItem[$k]['express_num'] = $data['express_num'];
                   $classItem[$k]['wxapp_id'] = self::$wxapp_id;
                   unset($classItem[$k]['category_id']); 
                   unset($classItem[$k]['name']);        
             }
         }
           
         $status = (isset($data['shelf_unit_id']) && !empty($data['shelf_unit_id']))?3:2;
// dump($status);die;
         //将图片存进去
        //  $package_image_id = isset($data['package_image_id'])?$data['package_image_id']:'';
         $image = isset($data['enter_image_id'])?$data['enter_image_id']:'';
         $post = [
            'status' => $status,
            'member_id' => !empty($data['user_id'])?$data['user_id']:$result['member_id'],
            'express_num' =>$data['express_num'],
            'storage_id' => isset($data['shop_id'])?$data['shop_id']:$result['storage_id'],
            'country_id' => isset($data['country'])?$data['country']:$result['country_id'],
            'width' => isset($data['width'])?$data['width']:$result['width'],
            'length' => isset($data['length'])?$data['length']:$result['length'],
            'height' => isset($data['height'])?$data['height']:$result['height'],
            'weight' => isset($data['weigth'])?$data['weigth']:$result['weight'],
            'remark' => isset($data['remark'])?$data['remark']:$result['remark'],
            'express_id' => isset($data['express_id'])?$data['express_id']:$result['express_id'],
            'image' => json_encode($image),
            'price' => isset($data['price'])?$data['price']:$result['price'],
            'num'=>isset($data['num'])?$data['num']:$result['num'],
            'usermark'=> isset($data['mark']) && !empty($data['mark']) ? $data['mark'] : ($result['usermark'] ?? ''),
            'visit_free' => isset($data['visit_free'])?$data['visit_free']:0,
            'member_name' => isset($this->userName)?$this->userName:'',
            'wxapp_id' =>self::$wxapp_id,
            'is_take'=> empty($data['user_id'])?($result['member_id']?2:1):2,
            'source' => isset($result['source'])?$result['source']:2, //记录后台录入状态
            'operate_id' => 0,
            'created_time' => $result['created_time']?$result['created_time']:getTime(),
            'updated_time' => $result['updated_time']?$result['updated_time']:getTime(),
            'entering_warehouse_time' => $result['entering_warehouse_time']?$result['entering_warehouse_time']:getTime(),
         ];
         //计算包裹的体积
         if($post['length']>0 && $post['width']>0 && $post['height']>0){
             $post['volume'] = $post['length']*$post['width']*$post['height']/1000000;
         }
         //存在id则为更新
         if ($data['id']){
              $post['status'] = 2;
              $res = $this->where('id',$data['id'])->update($post);
              
              if (!$res){
                $this->error = "包裹录入失败";
                return false;
              } 
              //此处更新包裹的图片信息
              if(isset($data['enter_image_id'])){
              foreach ($image as $value ) {
                  $updataimg['package_id'] = $data['id'];
                  $updataimg['image_id'] = $value;
                  $updataimg['wxapp_id'] = self::$wxapp_id;
                  $updataimg['create_time'] = strtotime(getTime());
                 (new PackageImage())->insert($updataimg);
              }}
              
              $post['id'] = $data['id'];
              //判断是否需要添加物流信息
              if($noticesetting['enter']['is_enable']==1){
                   Logistics::add($post,$noticesetting['enter']['describe']);
              }
              
              //更新货架
               if (isset($data['shelf_unit_id'])){
                     $upShelf = [
                      'shelf_unit_id' => $data['shelf_unit_id'],
                      'express_num' => $data['express_num'],
                      'user_id' => $data['user_id']??0,
                      'created_time' => getTime(),
                      'pack_id' => $data['id'],
                     ];
                     $shelfdata = (new ShelfUnitItem())->where('pack_id',$data['id'])->find();
                     if($shelfdata){
                            (new ShelfUnitItem())->where('pack_id',$data['id'])->update(['shelf_unit_id'=>$data['shelf_unit_id']]);
                     }else{
                        $res = (new ShelfUnitItem())->post($upShelf);
                     }
                 }
             
              //仓库id存在，则查询到仓库名称，传入模板消息
              if($data['shop_id']){
                 $shopData =  (new Shop())->where('shop_id',$data['shop_id'])->find();
                 $post['shop_name'] = $shopData['shop_name'];
              }
              $tplmsgsetting = SettingModel::getItem('tplMsg');
              if($tplmsgsetting['is_oldtps']==1){
                  //发送旧版本订阅消息以及模板消息
                  $sub = $this->sendEnterMessage([$post]);
              }else{
                  //发送新版本订阅消息以及模板消息
                  Message::send('package.inwarehouse',$post);
              }
              
            
              //判断是否有用户id，发送邮件
              if(isset($data['user_id']) || !empty($data['user_id'])){
                  $EmailUser = User::detail($data['user_id']);
                  if($EmailUser['email']){
                      $EmailData['code'] = $data['express_num'];
                      $EmailData['logistics_describe']=$noticesetting['enter']['describe'];
                     (new Email())->sendemail($EmailUser,$EmailData,$type=1);
                  }
              }
              return true;
         }else{ //新订单
              $post['order_sn'] = createSn();
              $res = $this->insertGetId($post);
              if(isset($data['enter_image_id'])){
                 foreach ($image as $value ) {
                  $updataimg['package_id'] = $res;
                  $updataimg['image_id'] = $value;
                  $updataimg['wxapp_id'] = self::$wxapp_id;
                  $updataimg['create_time'] = strtotime(getTime());
                 (new PackageImage())->insert($updataimg);
                } 
              }
              //判断是否需要添加物流信息
              if($noticesetting['enter']['is_enable']==1){
                   Logistics::add($res,$noticesetting['enter']['describe']);
              }
         }
      
         
         if (!$res){
            $this->error = "包裹录入失败";
            return false;
         } 
         if (isset($data['shelf_unit_id'])){
                
             $upShelf = [
              'shelf_unit_id' => $data['shelf_unit_id'],
              'express_num' => $data['express_num'],
              'user_id' => $data['user_id']??0,
              'created_time' => getTime(),
              'pack_id' => $res,
             ];
             $ress = (new ShelfUnitItem())->post($upShelf);
         }
         $packItemModel = new PackageItem();
         if (!empty($classItem)){
             $packItemRes = $packItemModel->saveAllData($classItem,$res);
             if (!$packItemRes){
                $this->error = "包裹录入失败";
                return false;
             }
         }
       $post['id'] = $res;
       //仓库id存在，则查询到仓库名称，传入模板消息
        if(isset($data['shop_id'])){
            $shopData =  (new Shop())->where('shop_id',$data['shop_id'])->find();
            $post['shop_name'] = $shopData['shop_name'];
        }
        $tplmsgsetting = SettingModel::getItem('tplMsg');
        if($tplmsgsetting['is_oldtps']==1){
            //触发旧版本订阅消息
            $sub= $this->sendEnterMessage([$post]);
        }else{
            //触发新版本订阅消息
            Message::send('package.inwarehouse',$post);
        }
       
        //  dump($sub);die;
       //判断是否有用户id，发送邮件
        if(isset($data['user_id']) || !empty($data['user_id'])){
            $EmailUser = User::detail($data['user_id']);
            $EmailData['code'] = $data['express_num'];
            $EmailData['logistics_describe']=$noticesetting['enter']['describe'];
            (new Email())->sendemail($EmailUser,$EmailData,$type=1);
        }      
       return true;
    }
    
    /*后台录入包裹*
     * 疑似丢弃 2022年11月5日
     */
    public function post($data){
         if(!$this->onCheck($data)){
             return false;
         }
        //  dump($data);die;
        $res=preg_match('^\w{3,20}$^',$data['express_num']);
        $res1=preg_match('/^[^\s]*$/',$data['express_num']);
        if(!$res || !$res1){
            $this->error = "快递单号包含特殊字符";
            return false;
        }
         $class_ids = $data['class_ids'];
         $classItem = $this->parseClass($class_ids);
         foreach ($classItem as $k => $val){
               $classItem[$k]['class_id'] = $val['category_id'];
               $classItem[$k]['express_name'] = '';
               $classItem[$k]['class_name'] = $val['name'];
               $classItem[$k]['express_num'] = $data['express_num'];
               $classItem[$k]['wxapp_id'] = self::$wxapp_id;
               unset($classItem[$k]['category_id']); 
               unset($classItem[$k]['name']);        
         }
         $status = $data['shelf_unit_id']?3:2;
        
         //将图片存进去
         $image = isset($data['enter_image_id'])?$data['enter_image_id']:'';
         $post = [
            'order_sn' => createSn(),
            'status' => $status,
            'member_id' => $data['user_id']??0,
            'express_num' =>$data['express_num'],
            'storage_id' => $data['shop_id'],
            'country_id' => $data['country'],
            'width' => $data['width'],
            'length' => $data['length'],
            'height' => $data['height'],
            'weight' => $data['weigth'],
            'remark' => $data['remark'],
            'pack_attr' => $data['pack_attr'],
            'num'=>$data['num'],
            'goods_attr' => isset($data['goods_attr'])?json_encode($data['goods_attr']):'',
            'image' => json_encode($image),
            'price' => $data['price'],
            'usermark' => isset($data['mark'])?$data['mark']:'',
            'member_name' => isset($this->userName)?$this->userName:'',
            'wxapp_id' =>self::$wxapp_id,
            'is_take'=> empty($data['user_id'])?1:2,
            'source' => 2,
            'created_time' => getTime(),
            'updated_time' => getTime(),
            'entering_warehouse_time' => getTime(),
         ];
         //存在id则为更新
         if ($data['id']){
              $post['status'] = 2;
              $res = $this->where('id',$data['id'])->update($post);
              
              if (!$res){
                $this->error = "包裹录入失败";
                return false;
              } 
              //此处更新包裹的图片信息
              if(isset($data['enter_image_id'])){
              foreach ($image as $value ) {
                  $updataimg['package_id'] = $data['id'];
                  $updataimg['image_id'] = $value;
                  $updataimg['wxapp_id'] = self::$wxapp_id;
                  $updataimg['create_time'] = strtotime(getTime());
                 (new PackageImage())->insert($updataimg);
              }}
              
              $post['id'] = $data['id'];
               //判断是否需要添加物流信息
              if($noticesetting['enter']['is_enable']==1){
                   Logistics::add($post,$noticesetting['enter']['describe']);
              }
              
              //仓库id存在，则查询到仓库名称，传入模板消息
              if($data['shop_id']){
                 $shopData =  (new Shop())->where('shop_id',$data['shop_id'])->find();
                 $post['shop_name'] = $shopData['shop_name'];
              }
              
              //发送订阅消息以及模板消息
              $sub = $this->sendEnterMessage([$post]);
              //判断是否有用户id，发送邮件
              if(isset($data['user_id']) || !empty($data['user_id'])){
                  $EmailUser = User::detail($data['user_id']);
                  if($EmailUser['email']){
                      $EmailData['code'] = $data['express_num'];
                      $EmailData['logistics_describe']=$noticesetting['enter']['describe'];
                     (new Email())->sendemail($EmailUser,$EmailData,$type=1);
                  }
              }
              return true;
         }else{ //新订单
              $res = $this->insertGetId($post);
         
              if(isset($data['enter_image_id'])){
                 foreach ($image as $value ) {
                  $updataimg['package_id'] = $res;
                  $updataimg['image_id'] = $value;
                  $updataimg['wxapp_id'] = self::$wxapp_id;
                  $updataimg['create_time'] = strtotime(getTime());
                 (new PackageImage())->insert($updataimg);
                } 
              }
               //判断是否需要添加物流信息
              if($noticesetting['enter']['is_enable']==1){
                   Logistics::add($res,$noticesetting['enter']['describe']);
              }
         }
         if (!$res){
            $this->error = "包裹录入失败";
            return false;
         } 
         if ($data['shelf_unit_id']){
             $upShelf = [
              'shelf_unit_id' => $data['shelf_unit_id'],
              'express_num' => $data['express_num'],
              'user_id' => $data['user_id']??0,
              'created_time' => getTime(),
              'pack_id' => $res,
             ];
             $res = (new ShelfUnitItem())->post($upShelf);
         }
         $packItemModel = new PackageItem();
         if ($classItem){
             $packItemRes = $packItemModel->saveAllData($classItem,$res);
             if (!$packItemRes){
                $this->error = "包裹录入失败";
                return false;
             }
         }
       $post['id'] = $res;
       //仓库id存在，则查询到仓库名称，传入模板消息
        if($data['shop_id']){
            $shopData =  (new Shop())->where('shop_id',$data['shop_id'])->find();
            $post['shop_name'] = $shopData['shop_name'];
        }      
       //触发订阅消息
       $this->sendEnterMessage([$post]);
       //判断是否有用户id，发送邮件
        if(isset($data['user_id']) || !empty($data['user_id'])){
            $EmailUser = User::detail($data['user_id']);
            $EmailData['code'] = $data['express_num'];
            $EmailData['logistics_describe']=$noticesetting['enter']['describe'];
            (new Email())->sendemail($EmailUser,$EmailData,$type=1);
        }      
       return true;
    }
    
    
    /**
     * 处理包裹重物品类目
     * 
    */
    public function doClassIds($class_ids,$express_num,$id){
         $classItem = $this->parseClass($class_ids);
         $packItemModel = new PackageItem();
         $packItemModel->where('order_id',$id)->delete();
         foreach ($classItem as $k => $val){
               $classItem[$k]['class_id'] = $val['category_id'];
               $classItem[$k]['class_name'] = $val['name'];
               $classItem[$k]['express_num'] = $express_num;
               $classItem[$k]['wxapp_id'] = self::$wxapp_id;
               unset($classItem[$k]['category_id']); 
               unset($classItem[$k]['name']);        
         }
         $packItemRes = $packItemModel->saveAllData($classItem,$id);
         if (!$packItemRes){
            $this->error = "包裹类目录入失败";
            return false;
         }
         return true;
    }
    
    // 格式化
     public function parseClass($class_ids){
         $class_item = [];
         $class_ids = explode(',',$class_ids);
         $class = (new Category())->whereIn('category_id',$class_ids)->field('category_id,name')->select()->toArray(); 
         return $class;
     }
    
    /**
     * 处理包裹重物品类目
     * 
    */
    public function doClassIdstwo($param,$express_num,$id){
        $classItem = [];
        if(!empty($param['class_ids'])){
            $classItems = $this->parseClasstwo($param['class_ids']);
        }
        // DUMP($param);DIE;

         $packItemModel = new PackageItem();
         $packItemModel->where('order_id',$id)->delete();
         
        //  $classItem['class_id'] = $param['class_ids'];
         $classItem['width'] = isset($param['width'])?$param['width']:0;
         $classItem['height'] = isset($param['height'])?$param['height']:0;
         $classItem['length'] = isset($param['length'])?$param['length']:0;
         $classItem['all_weight'] = (isset($param['weight']) && isset($param['product_num']))?($param['weight']*$param['product_num']):0;
         $classItem['unit_weight'] = isset($param['weight'])?$param['weight']:0;
         $classItem['product_num'] = isset($param['product_num'])?$param['product_num']:0;
         $classItem['one_price'] = isset($param['one_price'])?$param['one_price']:0;
         $classItem['all_price'] = (isset($param['one_price']) && !empty($param['one_price']) && isset($param['product_num']))?($param['one_price']*$param['product_num']):0;
         $classItem['goods_name'] =isset($param['goods_name'])?$param['goods_name']:0;
         $classItem['volume'] = isset($param['volume'])?$param['volume']:0;
         $classItem['volumeweight'] =isset($param['volumeweight'])?$param['volumeweight']:0;
         $classItem['class_name'] = !empty($classItems)?$classItems:'';
         $classItem['express_num'] = $express_num;
         $classItem['wxapp_id'] = self::$wxapp_id;
         $packItemRes = $packItemModel->saveAllDataTWO($classItem,$id);
         if (!$packItemRes){
            $this->error = "包裹类目录入失败";
            return false;
         }
         return true;
    }
    
    // 格式化
     public function parseClasstwo($class_ids){
         $class_item = [];
         $class_ids = explode(',',$class_ids);
         $class = (new Category())->whereIn('category_id',$class_ids)->field('name')->select()->toArray(); 
         if(count($class)>0){
             foreach ($class as $v){
                 $class_item[] = $v['name'];
             }
         }
         return implode(',',$class_item);
     }
    
    public function onCheck($data){
      $adminsetting = SettingModel::getItem('adminstyle');
    //   dump($adminsetting);die;
       if (!isset($data['express_num']) || !$data['express_num']){
            $this->error = "快递单号,为必填";
            return false;
       }  

       if ($adminsetting['is_force_shop']==1 && (!isset($data['shop_id']) || !$data['shop_id'])){
            $this->error = "所在仓库为必填";
            return false;
       }
       if ($adminsetting['is_force_country']==1 && (!isset($data['country']) || !$data['country'])){
            $this->error = "目的地国家为必填";
            return false;
       }
       if ($adminsetting['is_force_usermark']==1 && (!isset($data['mark']) || !$data['mark'])){
            $this->error = "用户唛头为必填";
            return false;
       }
       if ($adminsetting['is_force_express']==1 && (!isset($data['express_id']) || !$data['express_id'])){
            $this->error = "快递公司为必填";
            return false;
       }
       if ($adminsetting['is_force_packinfo']==1){
            if (empty($data['length']) || empty($data['width']) || empty($data['height']) || empty($data['weigth'])){
                $this->error = "包裹长宽高重量,为必填";
                return false;
            }
       }
       if ($adminsetting['is_force_totalvalue']==1 && (!isset($data['price']) || !$data['price'])){
            $this->error = "总价值为必填";
            return false;
       }
       if ($adminsetting['is_force_category']==1 && (!isset($data['class_ids']) || !$data['class_ids'])){
            $this->error = "物品品类为必填";
            return false;
       }
       if ($adminsetting['is_force_adminremark']==1 && (!isset($data['remark']) || !$data['remark'])){
            $this->error = "备注为必填";
            return false;
       }
       if ($adminsetting['is_force_packimage']==1 && (!isset($data['enter_image_id']) || !$data['enter_image_id'])){
            $this->error = "包裹图片为必填";
            return false;
       }
       if ($adminsetting['is_force_shelf']==1 && (!isset($data['shelf_unit_id']) || !$data['shelf_unit_id'])){
            $this->error = "包裹货位为必填";
            return false;
       }
       if (!empty($data['user_id'])){
           $res = (new User())->find($data['user_id']);
           if (!$res){
                $this->error = "该用户不存在,请重新输入";
                return false;
           }  
           $this->userName = $res['nickName'];
       }
       
       if (!empty($data['user_code'])){
           $res = (new User())->where('user_code',$data['user_code'])->find();
           if (!$res){
                $this->error = "该用户不存在,请重新输入";
                return false;
           }  
           $this->userName = $res['nickName'];
       }
       return true;
    }
    
    /**
     * 订单列表
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    //查询
    public function getList($query = [])
    {
        return $this->setindexListQueryWhere($query)
            ->alias('a')
            ->with(['categoryAttr','Member','country','storage','inpack','packageimage.file','batch','shelfunititem.shelfunit.shelf'])
            ->field('a.inpack_id,a.num,a.batch_id,a.usermark,a.id,a.volume,a.order_sn,a.status as a_status,a.entering_warehouse_time,a.pack_attr,a.goods_attr,a.pack_free,a.source,a.is_take,a.free,a.express_num,a.express_name, a.length, a.width, a.height, a.weight,a.price,a.real_payment,a.remark,a.created_time,a.updated_time,pi.class_name,pi.class_id,pi.express_num as pnumber,u.user_id,u.nickName,u.user_code,a.member_id,s.shop_name,c.title')
            ->join('user u', 'a.member_id = u.user_id',"LEFT")
            ->join('countries c', 'a.country_id = c.id',"LEFT")
            ->join('store_shop s', 'a.storage_id = s.shop_id',"LEFT")
            ->join('package_item pi','pi.order_id = a.id','LEFT')
            ->order('updated_time DESC')
            ->group('a.id')
            ->paginate(isset($query['limitnum'])?$query['limitnum']:15,false,[
                'query'=>\request()->request()
            ]);
    }
    
     /**
     * 订单列表重量之和
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    //查询
    public function getListSum($query = [])
    {
        return $this->setindexListQueryWhere($query)
            ->alias('a')
            ->with(['categoryAttr','Member','country','storage','inpack'])
            ->field('a.id,a.batch_id,a.volume,a.order_sn,a.status as a_status,a.entering_warehouse_time,a.pack_attr,a.goods_attr,a.pack_free,a.source,a.is_take,a.free,a.num,a.express_num,a.express_name, a.length, a.width, a.height, a.weight,a.price,a.real_payment,a.remark,a.created_time,a.updated_time,pi.class_name,pi.class_id,pi.express_num as pnumber,u.user_id,u.nickName,u.user_code,a.member_id,s.shop_name,c.title')
            ->join('user u', 'a.member_id = u.user_id',"LEFT")
            ->join('countries c', 'a.country_id = c.id',"LEFT")
            ->join('store_shop s', 'a.storage_id = s.shop_id',"LEFT")
            ->join('package_item pi','pi.order_id = a.id','LEFT')
            ->order('updated_time DESC')
            // ->group('a.id')
            ->limit(isset($query['limitnum'])?$query['limitnum']:15)
            ->sum('a.weight');
    }
    
    /**
     * 获取订单统计
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getDataTotal($query){
        !empty($query['extract_shop_id']) && is_numeric($query['extract_shop_id']) && $query['extract_shop_id'] > -1 && $this->where('storage_id', '=', (int)$query['extract_shop_id']);
        $today = date('Y-m-d',time());
        $todayend = date('Y-m-d',time()+86400);
        $yestoday = date('Y-m-d',time() - 86400);
        // dump($query);die;
        return $result = [
           'todayin'=> $this->where('is_delete',0)->where('entering_warehouse_time','between',[$today,$todayend])->count(),
           'todayout'=>$this->where('is_delete',0)->where('status','in',[5,6,7,8])->where('entering_warehouse_time','between',[$today,$todayend])->count(),
           'yesin'=> $this->where('is_delete',0)->where('entering_warehouse_time','between',[$yestoday,$today])->count(),
           'yesout'=>$this->where('is_delete',0)->where('status','in',[5,6,7,8])->where('entering_warehouse_time','between',[$yestoday,$today])->count(),
           'report'=>$this->where(['status'=>1,'is_delete'=>0])->count(),
           'instore'=>$this->where('is_delete',0)->where('status','in',[2,3,4,5,6,7,8])->count(),
           'other'=>$this->where('is_delete',0)->where('status','in',[9,10,11])->count(),
        ];
    }
    
        //查询条件
    private function setindexListQueryWhere($param = [])
    {
        // 查询参数
    // dump($param);die;
        if(!empty($param['status']) && $param['status']==12){
            !empty($param['status'])&& $this->where('a.status','in',[-1,2,3,4,5,6,7]);
        }else if(!empty($param['status']) && $param['status']==13){
            !empty($param['status'])&& $this->where('a.status','in',[8,9,10,11]);
        }else{
            !empty($param['status'])&& $this->where('a.status','in',$param['status']);
        }
        empty($param['is_delete']) && $this->where('a.is_delete','=',0);
        !empty($param['class_id'])&& $this->where('class_id','in',$param['class_id']);
        !empty($param['is_take'])&& $this->where('is_take','in',$param['is_take']);
        !empty($param['source'])&& $this->where('source','=',$param['source']);
        !empty($param['is_delete'])&& $this->where('a.is_delete','=',$param['is_delete']);
        !empty($param['batch_id'])&& $this->where('a.batch_id',$param['batch_id']);
        !empty($param['extract_shop_id'])&&is_numeric($param['extract_shop_id']) && $param['extract_shop_id'] > -1 && $this->where('storage_id', '=', (int)$param['extract_shop_id']);
        !empty($param['start_time']) && $this->where('created_time', '>', $param['start_time']);
        !empty($param['end_time']) && $this->where('created_time', '<', $param['end_time']." 23:59:59");
        // !empty($param['express_num']) && $this->where('a.express_num|a.order_sn', 'like', '%'.$param['express_num'].'%');
        if(!empty($param['express_num'])){
            $express_num = str_replace("\r\n","\n",trim($param['express_num']));
            $express_num = explode("\n",$express_num);
            $express_num = implode(',',$express_num);
            $where['a.express_num|a.order_sn'] = array('in', $express_num);
            $this->where($where);
        }
        !empty($param['likesearch']) && $this->where('a.express_num','like','%'.$param['likesearch'].'%');
        !empty($param['search']) && $this->where('a.member_id|u.nickName|u.user_code|a.usermark','like','%'.$param['search'].'%');
        return $this;
    }
    
    
    /**
     * 订单列表
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    //查询
    public function getAllList($query = [])
    {
        // dump($query);die;
        // $query['category_id'] = 884;
        return $this->setListQueryWhere($query)
            ->alias('a')
            ->with(['categoryAttr','inpack'])
            // ->with(['categoryAttr','categoryAttr' => function($quer) use($query) {
            //     $quer->where('class_id','=',$query['category_id']);
            // }])
            ->field('a.id,a.num,a.batch_id,a.usermark,a.inpack_id,a.order_sn,u.nickName,a.member_id,u.user_code,s.shop_name,a.status as a_status,a.entering_warehouse_time,a.pack_attr,a.goods_attr,a.pack_free,a.source,a.is_take,a.free,a.express_num,a.express_name, a.length, a.width, a.height, a.weight,a.price,a.real_payment,a.remark,c.title,a.created_time,a.updated_time')
            ->join('user u', 'a.member_id = u.user_id',"LEFT")
            ->join('countries c', 'a.country_id = c.id',"LEFT")
            ->join('store_shop s', 'a.storage_id = s.shop_id',"LEFT")
            ->join('package_item pi','pi.order_id = a.id','LEFT')
            ->order('updated_time DESC')
            ->group('a.id')
            ->paginate(isset($query['limitnum'])?$query['limitnum']:15,false,[
                'query'=>\request()->request()
            ]);
    }
    
    /**
     * 订单列表
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    //查询
    public function getUnpackList($query = [])
    {
        // dump($query);die;
        // $query['category_id'] = 884;
        return $this->setListQueryWhere($query)
            ->alias('a')
            ->with(['categoryAttr','inpack'])
            // ->with(['categoryAttr','categoryAttr' => function($quer) use($query) {
            //     $quer->where('class_id','=',$query['category_id']);
            // }])
            ->field('a.id,a.num,a.batch_id,a.usermark,a.inpack_id,a.order_sn,u.nickName,a.member_id,u.user_code,s.shop_name,a.status as a_status,a.entering_warehouse_time,a.pack_attr,a.goods_attr,a.pack_free,a.source,a.is_take,a.free,a.express_num,a.express_name, a.length, a.width, a.height, a.weight,a.price,a.real_payment,a.remark,c.title,a.created_time,a.updated_time')
            ->join('user u', 'a.member_id = u.user_id',"LEFT")
            ->join('countries c', 'a.country_id = c.id',"LEFT")
            ->join('store_shop s', 'a.storage_id = s.shop_id',"LEFT")
            ->join('package_item pi','pi.order_id = a.id','LEFT')
            ->where('a.inpack_id',null)
            // ->whereOr('a.')
            ->order('updated_time DESC')
            ->group('a.id')
            ->paginate(isset($query['limitnum'])?$query['limitnum']:15,false,[
                'query'=>\request()->request()
            ]);
    }
    
     //查询预约包裹
    public function getYList($query = [])
    {
        return $this->setListQueryWhere($query)
            ->alias('a')
            ->field('a.id,a.num,a.batch_id,a.usermark,a.order_sn,u.nickName,a.member_id,u.user_code,s.shop_name,a.status as a_status,a.entering_warehouse_time,a.pack_attr,a.goods_attr,a.pack_free,a.source,a.is_take,a.free,a.express_num,a.express_name, a.length, a.width, a.height, a.weight,a.price,a.real_payment,a.remark,c.title,a.created_time,a.updated_time,ad.*')
            ->join('user u', 'a.member_id = u.user_id',"LEFT")
            ->join('countries c', 'a.country_id = c.id',"LEFT")
            ->join('store_shop s', 'a.storage_id = s.shop_id',"LEFT")
            ->join('user_address ad','a.address_id = ad.address_id',"LEFT")
            ->order('updated_time DESC')
            ->paginate(10,false,[
                'query'=>\request()->request()
            ]);
    }
    
    /**
     * 回收站订单列表
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    //查询
    public function getdeleteList($query = [])
    {
        // dump($this->setListQueryWhere($query));die;
        return $this->setListQueryWhere($query)
            ->alias('a')
            ->with('categoryAttr')
            ->where('a.is_delete',1)
            ->field('a.id,a.num,a.batch_id,a.inpack_id,a.usermark,a.order_sn,u.nickName,a.member_id,u.user_code,s.shop_name,a.status as a_status,a.entering_warehouse_time,a.pack_attr,a.goods_attr,a.pack_free,a.source,a.is_take,a.free,a.express_num,a.express_name, a.length, a.width, a.height, a.weight,a.price,a.real_payment,a.remark,c.title,a.created_time,a.updated_time')
            ->join('user u', 'a.member_id = u.user_id',"LEFT")
            ->join('countries c', 'a.country_id = c.id',"LEFT")
            ->join('store_shop s', 'a.storage_id = s.shop_id',"LEFT")
            ->order('updated_time DESC')
            ->paginate(isset($query['limitnum'])?$query['limitnum']:15,false,[
                'query'=>\request()->request()
            ]);
    }

    //查询条件
    private function setListQueryWhere($param = [])
    {
        // 查询参数
 
        if(!empty($param['status']) && $param['status']==12){
            !empty($param['status'])&& $this->where('a.status','in',[-1,2,3,4,5,6,7]);
        }else if(!empty($param['status']) && $param['status']==13){
            !empty($param['status'])&& $this->where('a.status','in',[8,9,10,11]);
        }else{
            !empty($param['status'])&& $this->where('a.status','in',$param['status']);
        }
        empty($param['is_delete']) && $this->where('a.is_delete','=',0);
        !empty($param['class_id'])&& $this->where('class_id','in',$param['class_id']);
        !empty($param['is_take'])&& $this->where('is_take','in',$param['is_take']);
        !empty($param['source'])&& $this->where('source','=',$param['source']);
        !empty($param['is_delete'])&& $this->where('a.is_delete','=',$param['is_delete']);
        !empty($param['extract_shop_id'])&&is_numeric($param['extract_shop_id']) && $param['extract_shop_id'] > -1 && $this->where('storage_id', '=', (int)$param['extract_shop_id']);
        !empty($param['start_time']) && $this->where('created_time', '>', $param['start_time']);
        !empty($param['end_time']) && $this->where('created_time', '<', $param['end_time']." 23:59:59");
    
        // !empty($param['express_num']) && $this->where('a.express_num|a.order_sn', 'like', '%'.$param['express_num'].'%');
        if(!empty($param['express_num'])){
            $express_num = str_replace("\r\n","\n",trim($param['express_num']));
            $express_num = explode("\n",$express_num);
            $express_num = implode(',',$express_num);
            $where['a.express_num'] = array('in', $express_num);
            $this->where($where);
        }
        !empty($param['likesearch']) && $this->where('a.express_num','like','%'.$param['likesearch'].'%');
        !empty($param['search']) && $this->where('a.member_id|u.nickName|u.user_code','like','%'.$param['search'].'%');
        return $this;
    }

    //修改statu
    public function setStatu($id){
       return $this ->where('id','=',$id)
            ->update(['status'=>2,'updated_time'=>getTime(),'entering_warehouse_time'=>getTime()]);
    }

    //删除功能
    public function setDelete($id)
    {
        return  Db::table('yoshop_package')->where('id', '=',$id)->update(['is_delete' => 1]);
    }

    //查询一条数据
    public function getOne($id){
            return $this->alias('a')
            ->where('a.id','=',$id)
            ->field('a.id,a.num,a.batch_id,a.usermark,a.order_sn,u.nickName,s.shop_name,a.country_id,a.weight,a.length,a.width,a.height,a.storage_id,a.status,a.is_take,a.price,a.real_payment,a.remark,c.title,a.created_time,a.updated_time')
            ->join('user u', 'a.member_id = u.user_id',"LEFT")
            ->join('countries c', 'a.country_id = c.id','LEFT')
            ->join('store_shop s', 'a.storage_id = s.shop_id')
            ->select()
            ->toArray();
    }

    //修改预报包裹列表
    public function  setSave($list){
        $updateTime = date('Y-m-d h:i:s',time());
        $list['updated_time'] = $updateTime;
        return $this->update($list);
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
            $filter['pay_time'] = [
                ['>=', strtotime($startDate)],
                ['<', strtotime($endDate) + 86400],
            ];
        }
        return $this->where($filter)
            ->where('is_delete', '=', 0)
            ->count();
    }
    


    
    // 统计数据
    public function getPackTotal($time=null){
        $_map = ['is_delete'=>0];
        $model = $this->where($_map);
        if ($time){
            $model->whereBetween('created_time',[$time['start'],$time['end']]);
        }
        return $model->count();
    }
    
     public function detail($id){
       return $this->alias('pa')
       ->with(['packageimage.file','Member','country','shelfunititem.shelfunit.shelf','inpack'])
       ->where('pa.id',$id)
       ->find();
    }
    
    public function inpack(){
        return $this->belongsTo('app\store\model\Inpack','inpack_id','id');
    }
    
    public function storage(){
        return $this->belongsTo('app\store\model\store\Shop','storage_id');
    }
    
    public function country(){
        return $this->belongsTo('Countries','country_id');
    }
    
    public function Member(){
      return $this->belongsTo('app\api\model\User','member_id')->field('user_id,nickName,avatarUrl,user_code');
    }
    
    public function categoryAttr(){
        return $this->hasMany('app\store\model\PackageItem','order_id','id');
    }
    
    public function batch(){
        return $this->belongsTo('app\store\model\Batch');
    }
    
    public function statement(){
        return $this->belongsTo('app\store\model\Statement', 'statement_id', 'id')
            ->field('id,statement_no,pay_status');
    }
}
