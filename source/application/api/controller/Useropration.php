<?php
namespace app\api\controller;

use app\api\model\Inpack;
use app\api\model\Line;
use app\api\model\Category;
use app\api\model\Logistics;
use app\api\model\Package;
use app\api\model\ShelfUnit;
use app\api\model\ShelfUnitItem;
use app\api\model\PackageItem as PackageItemModel;
use app\api\model\SendPreOrder;
use app\api\model\store\shop\Clerk;
use app\api\model\User as UserModel;
use app\api\model\UserAddress;
use app\store\model\PackageImage;
use app\common\service\Email;
use app\common\service\Message;
use app\common\model\User as UserCommonModel;
use app\common\model\store\Shop;
use think\Db;
use app\api\model\InpackImage;
use app\common\model\InpackService;
use app\api\model\Setting as SettingModel;
use app\common\model\store\shop\Capital;
/**
 * 用户管理
 * Class User
 * @package app\api
 */
class Useropration extends Controller
{
    
    /* @var \app\api\model\User $user */
    private $user;
    private $role = [];

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
            $this->user['user_id'] = 1;
        }
        $this->setRole();
    }

    
    // 设置角色
    private function setRole(){
        $userInfo = $this->user;
        switch($userInfo['user_type']){
          case 0:
          $role_name = '普通用户';
          break;
          case 1:
          $role_name = '入库员';
          break;
          case 2:
          $role_name = '分拣员';
          break;
          case 3:
          $role_name = '打包员';
          break; 
          case 4:
          $role_name = '签收员'; 
          break; 
          case 5:
          $role_name = '仓管员'; 
          break; 
          default:
          $role_name = '未知角色';
          break;  
        }
        $userRole['role_name'] = $role_name;
        $userRole['role_type'] = $userInfo['user_type'];
        $this->userRole = $userRole;
    } 
    
    // 仓库打包员 打包列表
    public function inpack(){
        if (!$this->checkRole(3)){
          return $this->renderError('角色权限非法');
        }
         // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        $status_map = [
            2 => 1,
            1 => [1,2]
        ];
        $status = isset($this->postData('status')[0])?$this->postData('status')[0]:0;
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $keyword = $this->postData("keyword")[0];
        $map['storage_id'] = $clerk['shop_id'];
        $map['status'] = $status_map[$status]; 
        $inpack = (new Inpack());
        if ($keyword){
            $inpack ->where(function($query)use($keyword){
                $query->whereOr('member_id','=',$keyword);
            });
        }
        $data = $inpack->with('member')->where($map)->order(['created_time DESC'])->paginate(15); 
        foreach($data as $k => $v){
            $data[$k]['num'] = count(explode(',',$v['pack_ids']));
        }  
       
        return $this->renderSuccess($data);
    }
    
    //保存集运单签收图片
    public function saveOrderImage(){
        $data = $this->postData();
        $indata = $data;
        unset($indata['id']);
        unset($indata['token']);
        unset($indata['imageIds']);
        $inpack = new Inpack();
       
        //更新集运单图片
        $resimg = $resdeleteimg = true;
        $dataimg = [
                'inpack_id' =>$data['id'],
                'wxapp_id' =>  \request()->get('wxapp_id'),
        ];
        if(!empty($data['imageIds'])){
            foreach ($data['imageIds'] as $key => $value){
              $dataimg['image_id'] = $value;
              $resimg  = (new InpackImage())->save($dataimg);
            }
            $indata['is_focus_image'] = 1;
            $res = $inpack->where('id',$data['id'])->update($indata);
        }


        if($res ||  $resimg){
            return $this->renderSuccess('编辑成功');
        }
        return $this->renderError('更新失败');
    }
    // 海外入库员 - 入库校验
    public function checkzPack(){
        if (!$this->checkRole(6)){
            return $this->renderError('角色权限非法');
        }
        
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->with('storage')->find();
        if (!$clerk){
           return $this->renderError('角色权限非法');
        }
    
        $post = $this->postData('code')[0];
        //特殊处理京东单号
        // if(stristr($post,'JD')){
        //      $post = explode('-',$post)[0];
        // }
        
        //处理USPS单号问题
        //处理FEDEX单号问题
        $map[] = ['is_delete','=',0];
        $map[] = ['status','=',6];
        $map[] = ['order_sn','=',$post]; 
        $res = (new Inpack())
            ->setQuery($map)
            ->field('id,order_sn,member_id,line_id,storage_id,status,width,height,weight,length,remark')
            ->with(['storage','line'])
            ->find();
        //当查询不到包裹时，尝试查询是否是集运单的国际单号；
// dump($res);die;
        if(empty($res)){
            $Inpack = new Inpack();
            $maps['is_delete'] =0;
            $maps['status'] = 6;
            $maps['t_order_sn'] = $post; 
            $inpackres = $Inpack->where($maps)->with('storage')->find();
            if(!empty($inpackres)){
                $where = ['user_id'=>$inpackres['member_id']];
                $userdata = UserModel::detail($where,$with=[]);
                if(!empty($userdata)){
                   $inpackres['is_pre'] =20; //是集运单号
                   $inpackres['user_code'] =  $userdata['user_code'];
                   return $this->renderSuccess($inpackres);
                }else{
                    
                    $clerk['is_pre'] = 0; //是包裹单号
                    return $this->renderSuccessPlus($clerk,'单号状态不不存在或未到货');
                }
            }else{
                return $this->renderSuccessPlus($clerk,'单号状态不不存在或未到货');
            }
        }
        $where = ['user_id'=>$res['member_id']];
        $userdata = UserModel::detail($where,$with=[]);
        !empty($userdata) && $res['user_code'] =  $userdata['user_code'];
        if ($res){
          $res['is_pre'] = 10; //是包裹单号
          return $this->renderSuccess($res);
        }
        return $this->renderSuccessPlus($clerk);
    }
    
     
    /**
     * 仓管端直邮入库
     * @param Array
     * @return bool
     * @throws \think\exception\DbException
     */
    public function inzyStoragePlus() {
        
        //检验入库人员权限 1 入库员 2分拣 3打包 4 签收 5仓管
        if (!$this->checkRole(6)){
            return $this->renderError('角色权限非法');
        }
        // 员工信息
       $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->with('shop')->find();
       if (!$clerk){return $this->renderError('角色权限非法');}
       $Inpack = new Inpack();
       $id = $this->postData('id')[0];
       if (!$id){
             return $this->renderError('单号不存在');
        }
       $user_id = $this->postData('user_id')[0];
       $user_code = $this->postData('user_Code')[0];
       $express_num = $this->postData('express_num')[0];
       
       $weight = $this->postData('weight')[0];
       $height = $this->postData('height')[0];
       $length = $this->postData('length')[0];
       $width = $this->postData('width')[0];
       
       $imageIds = $this->postData('imageIds');
       $wxapp_id = \request()->get('wxapp_id');
       $remark = $this->postData('remark')[0];
       $shelf_unit = $this->postData('shelf_unit')[0];
       //1、当是集运订单的单号时，存储当前的入库仓库id，使用字段shop_id
       
        $packData = $Inpack::detail($id);

        $userInfo = $this->user;
        
        $order['status'] = 7;
        $order['shop_id'] = $clerk['shop_id'];
        if ($user_id){
            $order['member_id'] = $user_id;
        }
        $order['weight'] = $weight;
        $order['source'] = 8;
        $order['length'] = $length;
        $order['width'] = $width;
        $order['height'] = $height;
        $order['updated_time'] = getTime();
        $order['shoprk_time'] = getTime();
        $order['remark'] = $remark;
        $order['exceed_date'] = 0;
        $restwo = $packData->save($order); //获取到包裹的id
        if (!$restwo){
             return $this->renderError('包裹入库失败');
        }
        //存储上传的图片
        $this->inorderImages($packData['id'],$imageIds,$wxapp_id);
        $settingdata  = SettingModel::getItem('store');
        if($settingdata['is_auto_free']==1){
            getpackfree($id);   
        }
        //存入货架信息
        $takecode = rand(100000,999999);
        if(!empty($shelf_unit)){
            $this->saveToShelf($packData['id'],$userInfo['user_id'],$packData['t_order_sn'],$shelf_unit);
            $takecode =$this->takecode($id);
        }

        $logis['code'] = $express_num;
        $logis['logistics_describe']='包裹已到达'.$clerk['shop']['shop_name'].'取货码：'.$takecode;
        // dump($clerk['shop']['shop_name']);die;
        $Inpack->UpdateShop($id,$clerk['shop_id'],$takecode);
        //2、添加一条入库记录； 
        Logistics::addInpackGetLog($id,'包裹已到达'.$clerk['shop']['shop_name'].'请及时前来取货',$express_num);
        //3、发送模板消息到货并通知用户取货；
        $data['id'] = $packData['id'];
        $data['order_sn'] = $packData['order_sn'];
        $data['order'] = $packData;
        $data['order']['total_free'] = $packData['free'];
        $data['order']['userName'] = $userInfo['nickName'];
        $data['order_type'] = 10;
        $data['order']['remark'] = $logis['logistics_describe'];
        $data['t_order_sn'] =  $packData['t_order_sn']; //运单号
        $data['wxapp_id'] =  $packData['wxapp_id']; 
        $data['shop_id'] =  $packData['shop_id']; 
        $data['shoprk_time'] =  $packData['shoprk_time'];
        $data['member_id'] = $packData['member_id'];
        
        $tplmsgsetting = SettingModel::getItem('tplMsg');
        if($tplmsgsetting['is_oldtps']==1){
            $resss = Message::send('order.payment',$data);
        }else{
             Message::send('package.toshop',$data);
        }
        //4、发送邮件通知
        !empty($userInfo['email']) && (new Email())->sendemail($userInfo,$logis,$type=1);
        return $this->renderSuccess('集运单入库成功');    
    }
 
    /**
     * $id 包裹的id
     * $imageIds 图片数组
     */
    public function inorderImages($id,$imageIds,$wxapp_id){
 
        $InpackImage =  new InpackImage();
        if(isset($imageIds) && count($imageIds)>0){
                foreach ($imageIds as $key =>$val){
                    //校验图片是否又重复的
                     $result = $InpackImage->where('inpack_id',$id)->where('image_id',$val)->find();
                     if(empty($result)){
                         $update['inpack_id'] = $id;
                         $update['image_id'] = $val;
                         $update['wxapp_id'] =$wxapp_id;
                         $update['create_time'] = strtotime(getTime());
                         $resthen= $InpackImage->save($update);
                         if(!$resthen){
                              return false;
                         }
                     }
                }
            }    
        return true;
    } 
    
    //包裹批量上架
    public function updatetoshelf(){
       $param = $this->postData();
       $this->user = $this->getUser();
       $shelfdata =['shelf_unit' => $param['shelf_unit_id']];
       $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
       foreach ($param['code'] as $key=>$val){
           $shelfdata['express_num'] = $val;
           $package = (new Package())->where(['express_num'=>$val,'is_delete'=>0])->find();
           $Inpack = (new Inpack())->where(['t_order_sn'=>$val,'is_delete'=>0])->find();
           
           if(!empty($package)){
                $shelfdata['pack_id'] = $package['id'];
                $shelfdata['user_id'] = $package['member_id'];
                (new ShelfUnitItem())->post($shelfdata);
           }elseif(!empty($Inpack)){
                 $shelfdata['pack_id'] = $Inpack['order_sn'];
                 $shelfdata['user_id'] = $Inpack['member_id'];
                (new ShelfUnitItem())->post($shelfdata);
           }
          else{
              //查询不到就直接入库；
                $update = [
                  'order_sn' => createSn(),
                  'status' => 2,
                  'storage_id' =>$clerk['shop_id'],
                  'express_num' =>$val,
                  'updated_time'=>getTime(),
                  'created_time'=>getTime(),
                  'entering_warehouse_time'=>getTime(),
                  'wxapp_id' =>  \request()->get('wxapp_id'),
                ];
                $pid =(new Package())->insertGetId($update);
                $shelfdata['pack_id'] = $update['order_sn'];
                $shelfdata['user_id'] = '';
                (new ShelfUnitItem())->post($shelfdata);
          }
       }
        return $this->renderSuccess("上架成功");
    }


    // 仓库打包员 打包列表
    public function inpackTotal(){
      if (!$this->checkRole(3)){
        return $this->renderError('角色权限非法');
      }
       // 员工信息
      $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      if (!$clerk){
          return $this->renderError('角色权限非法');
      }
      $packModel = (new Inpack());
      $res = [
         'no' => $packModel ->where(['status'=>1,'storage_id'=>$clerk['shop_id']])->count(),
         'in' => $packModel -> where(['status'=>2,'storage_id'=>$clerk['shop_id']])->count()
      ];
      return $this->renderSuccess($res);
  }

    // 打包详情
    public function inpackDetails(){
          $id = $this->postData('id')[0];
          $data = (new Inpack())->alias('p')->with('member')->find($id);
          $item = explode(',',$data['pack_ids']);
          if ($data['address_id']){
              $data['address'] = (new UserAddress())->find($data['address_id']);
          }
          if ($data['line_id']){
              $data['line'] = (new Line())->field('name')->find($data['line_id']);
          }
          $pack =  (new Package())->whereIn('id',$item)->with(['country','packitem','packageimage.file'])->select();
          $pack = $this->getPackItemList($pack);
          $data['item'] = $pack;
          return $this->renderSuccess($data);
    }
    
    
    //编辑集运单
    public function editOrder(){
        $data = $this->postData();
        $indata = $data;
        unset($indata['id']);
        unset($indata['token']);
        unset($indata['imageIds']);
        unset($indata['deleteIds']);
        $inpack = new Inpack();
        // dump($indata);die;
        $res = $inpack->where('id',$data['id'])->update($indata);
        //更新集运单图片
        $resimg = $resdeleteimg = true;
        $dataimg = [
                'inpack_id' =>$data['id'],
                'wxapp_id' =>  \request()->get('wxapp_id'),
        ];
        if(!empty($data['imageIds'])){
            foreach ($data['imageIds'] as $key => $value){
              $dataimg['image_id'] = $value;
              $resimg  = (new InpackImage())->save($dataimg);
            }
        }
        //删除集运单图片
        if(!empty($data['deleteIds'])){
            foreach ($data['deleteIds'] as $key => $value){
              $resdeleteimg  = (new InpackImage())->where('id',$value)->delete();
            }
        }
        //完成集运单价格的计算；
        $settingdata  = SettingModel::getItem('store');
        if($settingdata['is_auto_free']==1){
            getpackfree($data['id']);   
        }
        if($res || $resimg || $resdeleteimg){
            return $this->renderSuccess('编辑成功');
        }
        return $this->renderError('更新失败');
    }
    
    // 包裹数据
    public function getPackItemList($data){
        $orderItem = [];
        foreach($data as $k => $v){
            $orderItem[] = $v['id'];
        }
        $orderIdItem = [];
        $orderItemList = (new PackageItemModel())->whereIn('order_id',$orderItem)->field('order_id,id,class_name')->select();
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

    // 仓库分拣员 - 扫货架码
    public function getShelf(){
        if (!$this->checkRole(2)){
            return $this->renderError('角色权限非法');
        }
        $code = $this->postData('code')[0];
        
        $shelfUnit = (new ShelfUnit())->where(['shelf_unit_code'=>$code])->find();
        // dump($shelfUnit);die;
        // $shelfUnit['shelf_unit_code'] = (new ShelfUnit())->getShelfUnit($shelfUnit['shelf_id']);
        if (!$shelfUnit){
          return $this->renderError('未查询到货架');
        }
        return $this->renderSuccess($shelfUnit);
    }
    
    
    /**
     * 仓管查询包裹
     * @param int $payType
     * @return bool
     * @throws \think\exception\DbException
     */
    public function searchPack(){
        if (!$this->checkRole(4)){
            return $this->renderError('角色权限非法');
        }
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        
        $code = $this->postData()['search'];
        $type = $this->postData()['type'];
        $Inpack = new Inpack();
        //搜索
        $pack = [];$i=0;
        if($type==1){
            //查询是否是手机尾号，用户id，用户code，取货码
            $UserModel = new UserModel();
            $UserAddress = new UserAddress();
            $userData = $UserModel->where('user_id|user_code|mobile','like','%'.$code.'%')->field('user_id')->select()->toArray();
         
            $userData2 = $UserAddress->field('user_id,region')->where('phone','like','%'.$code.'%')->select()->toArray();
            $userCon = array_merge($userData,$userData2);
            $userArr =[];
            if(count($userCon)>0){
                foreach ($userCon as $k => $v){
                    $userArr[$k] = $v['user_id'];
                }
            }
            $usernewArr= array_unique($userArr);
            foreach ($usernewArr as $key => $val){
                //查询出所有的shop_id为仓管所在仓库的集运单；
               $rest= $Inpack->where('member_id',$val)->where('shop_id',$clerk['shop_id'])->where('status','<',8)->with(['Member','address','service','shelfunititem.shelfunit.shelf'])->select();
               if(count($rest)>0){
                   $pack[$i] = $rest;
                   $i += 1;
               }
            }
         
            $packs = $Inpack->where('take_code|order_sn|t_order_sn',$code)->where('shop_id',$clerk['shop_id'])->where('status','<',8)->with(['Member','address','service','shelfunititem.shelfunit.shelf'])->select();
            //   dump($packs);die;
            if(count($packs)>0){
                $pack[0] = $packs;
            }
            return $this->renderSuccess($pack);
        }
        
        //扫码签收
        if($type==2){
            $pack[$i] = $Inpack->where('shop_id',$clerk['shop_id'])->where('status','<',8)->where('order_sn|t_order_sn',$code)->with(['Member','address','service','shelfunititem.shelfunit.shelf'])->select();
            return $this->renderSuccess($pack); 
        }
    }
    
     /**
     * 仓管签收包裹
     * @param int $payType
     * @return bool
     * @throws \think\exception\DbException
     */
    public function changeStatusByAdmin(){
        if (!$this->checkRole(4)){
            return $this->renderError('角色权限非法');
        }
        $id = $this->postData()['id'];
        if(!$id){
             return $this->renderError('参数错误');
        }
        $Inpack = new Inpack();
        $res = $Inpack->where('id',$id)->update(['status'=> 8,'receipt_time'=>getTime()]);
        $packData = $Inpack::detail($id);
        //进行模板消息，邮件通知
        $userInfo = $this->user;
        $logis['code'] = $packData['t_order_sn'];
        $logis['logistics_describe']='包裹已签收，感谢您的支持';
        //2、添加一条入库记录； 
        Logistics::addInpackGetLog($id,'包裹已签收，感谢您的支持',$packData['t_order_sn']);
        //3、发送模板消息到货并通知用户取货；
        $data['order_sn'] = $packData['order_sn'];
        $data['order'] = $packData;
        $data['order']['total_free'] = $packData['free'];
        $data['order']['userName'] = $userInfo['nickName'];
        $data['order_type'] = 10;
        $data['order']['remark'] = $logis['logistics_describe'];
        $resss = Message::send('order.payment',$data);  
        //4、发送邮件通知
        !empty($userInfo['email']) && (new Email())->sendemail($userInfo,$logis,$type=1);
        //5、清空货架数据
        (new ShelfUnitItem())->where(['pack_id'=>$packData['order_sn']])->delete();
        return $this->renderSuccess('',"操作成功");
    }

    /**
     * 检查权限是否非法
     * @param int $payType
     * @return bool
     * @throws \think\exception\DbException
     */
    public function checkRole($role){
        $currentUserRole = $this->userRole;
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        $clerk_type = explode(',',$clerk['clerk_type']);
        if (in_array($role,$clerk_type)){
            return true; 
        }else{
            return false;
        }
    }
     
    // 仓管员 - 入库校验
    public function checkPack(){
        if (!$this->checkRole(1)){
            return $this->renderError('角色权限非法');
        }
        
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->with('storage')->find();
        if (!$clerk){
           return $this->renderError('角色权限非法');
        }
    
        $post = $this->postData('code')[0];
        //特殊处理京东单号

        
      
        $map[] = ['is_delete','=',0];
        $map[] = ['express_num','=',$post]; 
        $res = (new Package())
            ->setQuery($map)
            ->field('id,express_num,order_sn,member_id,storage_id,status,width,height,weight,length,remark,admin_remark')
            ->with('storage')
            ->find();
        //当查询不到包裹时，尝试查询是否是集运单的国际单号；
        //当查询是JD包裹时，可以用原来的单号再查询一遍
        $tyoi = stripos($post, "JD");
        if(empty($res) && $tyoi==0){
            $ex = explode('-',$post);
            $post = $ex[0];
            $maps[] = ['is_delete','=',0];
            $maps[] = ['express_num','=',$post]; 
            $res = (new Package())
                ->setQuery($maps)
                ->field('id,express_num,order_sn,member_id,storage_id,status,width,height,weight,length,remark,admin_remark')
                ->with('storage')
                ->find();
           
        }
      
        if(empty($res) && !$tyoi){
      
            $Inpack = new Inpack();
            $mapss['is_delete'] =0;
            $mapss['t_order_sn'] = $post; 
                    //   dump($maps);die;
            $inpackres = $Inpack->where($mapss)->with('storage')->find();
            $where = ['user_id'=>$inpackres['member_id']];
            $userdata = UserModel::detail($where,$with=[]);
            if(!empty($userdata)){
               $inpackres['is_pre'] =20; //是集运单号
               $inpackres['user_code'] =  $userdata['user_code'];
               return $this->renderSuccess($inpackres);
            }else{
                
                $clerk['is_pre'] = 0; //是包裹单号
                return $this->renderSuccess($clerk);
            }
        }
        $where = ['user_id'=>$res['member_id']];
        $userdata = UserModel::detail($where,$with=[]);
        !empty($userdata) && $res['user_code'] =  $userdata['user_code'];
        if ($res){
          $res['is_pre'] = 10; //是包裹单号
          return $this->renderSuccess($res);
        }
        return $this->renderSuccessPlus($clerk);
    }
 
    /**
     * 仓管端提交入库
     * BY FENG 2022年12月27日 
     * 代码比较乱，等后续有时间再优化
     * @param Array
     * @return bool
     * @throws \think\exception\DbException
     */
    public function inStoragePlus() {
        
        //检验入库人员权限 1 入库员 2分拣 3打包 4 签收 5仓管
        if (!$this->checkRole(1)){
            return $this->renderError('角色权限非法');
        }
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->with('shop')->find();
        if (!$clerk){return $this->renderError('角色权限非法');}
        //   dump(23);die;
       $packItemModel = new PackageItemModel();
       $id = $this->postData('id')[0];
       $user_id = $this->postData('user_id')[0];
       $class_ids = $this->postData('class_ids')[0];
       $user_code = $this->postData('user_Code')[0];
       $is_pre = $this->postData('is_pre')[0];
       $express_num = $this->postData('express_num')[0];
       $weight = $this->postData('weight')[0];
       $height = $this->postData('height')[0];
       $length = $this->postData('length')[0];
       $width = $this->postData('width')[0];
       $imageIds = $this->postData('imageIds');
       $wxapp_id = \request()->get('wxapp_id');
       $remark = $this->postData('remark')[0];
       $shelf_unit = $this->postData('shelf_unit')[0];
  
       //再加一层校验；
        $map[] = ['is_delete','=',0];
        $map[] = ['express_num','=',$express_num];
        //对京东单号进行特殊处理
        // if(strstr($express_num,'JD')){
        //     $a = explode('-',$express_num);
        //     count($a)>1 && $express_num = $a[0];
        // }
        
       //$is_pre==0 为没有查询到包裹数据的情况，需要插入新数据
       if ($is_pre==0){
            if ($user_id){
                $user = (new UserModel())->find($user_id);
                if (!$user)
                    return $this->renderError('用户不存在');
            }
              
            if($user_code){
                $users = (new UserModel())->where('user_code',$user_code)->find();
                if (!$users){
                    return $this->renderError('用户不存在');}
                $user_id = $users['user_id'];
            }

            $order['status'] = 2;
            $order['is_take'] = 1;
            $order['storage_id'] = $clerk['shop_id'];
            if ($user_id){
                $order['member_id'] = $user_id;
                $order['is_take'] = 2;
            }
            $order['order_sn'] = createSn();
            $order['weight'] = $weight;
            $order['source'] = 8;
            $order['length'] = $length;
            $order['width'] = $width;
            $order['height'] = $height;
            $order['express_num'] = $express_num;
            $order['updated_time'] = getTime();
            $order['created_time'] = getTime();
            $order['admin_remark'] = $remark;
            $order['entering_warehouse_time'] = getTime();
            if($length>0 && $width>0 && $height>0){
                 $order['volume'] = $width*$length*$height/1000000;
            }
           
        
            $restwo = (new Package())->saveData($order); //获取到包裹的id
            if (!$restwo){
                 return $this->renderError('包裹入库失败');
            }
            //存储包裹的分类信息；
            $classItem = [];
             if ($class_ids){
                 $classItem = $this->parseClass($class_ids);
                 foreach ($classItem as $k => $val){
                       $classItem[$k]['class_id'] = $val['category_id'];
                       $classItem[$k]['express_name'] = '';
                       $classItem[$k]['class_name'] = $val['name'];
                       $classItem[$k]['express_num'] = $express_num;
                       $classItem[$k]['all_price'] = '';
                       unset($classItem[$k]['category_id']); 
                       unset($classItem[$k]['name']);        
                 }
             }
             if ($classItem){
                 $packItemRes = $packItemModel->saveAllData($classItem,$restwo);
                 if (!$packItemRes){
                    return $this->renderError('包裹类目更新失败');
                 }
             }
            //存储上传的图片
            $this->inImages($restwo,$imageIds,$wxapp_id);
            $shopData =  (new Shop())->where('shop_id',$clerk['shop_id'])->find();
            //存入货架信息
             $shelfunit = new ShelfUnitItem();
             $shelf = [
                'pack_id' => $restwo,
                'user_id' => $user_id,
                'express_num' => $express_num,
                'shelf_unit' => $shelf_unit,
             ];  
             $shelfunit->post($shelf);
            //邮件通知
            //判断是否有用户id，发送邮件
              if(isset($user_id) || !empty($user_id)){
                  $EmailUser = UserCommonModel::detail($user_id);
                  if($EmailUser['email']){
                      $EmailData['code'] = $order['express_num'];
                      $EmailData['logistics_describe']='包裹已入库，可提交打包';
                     (new Email())->sendemail($EmailUser,$EmailData,$type=1);
                  }
                    $data['order'] = [];
                    //发送订阅消息以及模板消息
                    //发送订阅消息，模板消息
                    $order['id'] = $restwo;
                    $order['wxapp_id'] = $wxapp_id;
                    $order['shop_name'] = $shopData['shop_name'];
                    $data['order'] = $order;
                    $data['order']['member_name'] = $EmailUser['nickName'];
                    $data['order_type'] = 10;
                    $data['order']['remark'] ="包裹已入库，可提交打包" ;
                    if($user_id!=0){
                      Message::send('order.enter',$data);  
                    }
              }
            Logistics::add($restwo,'仓管员手动入库');
            return $this->renderSuccess('包裹入库成功');
       }elseif($is_pre==10){
       //有对应数据情况下
       $data = (new Package())->find($id);
    //   dump($id);die;
    //   if ($data['status']==2){
    //      return $this->renderError('包裹已入库');
    //   }
       if ($user_id){
            $user = (new UserModel())->find($user_id);
            if (!$user)
            return $this->renderError('用户不存在');
        }
              
        if($user_code){
            $users = (new UserModel())->where('user_code',$user_code)->find();
            if (!$users){
                return $this->renderError('用户不存在');}
            $user_id = $users['user_id'];
        }
       //更新包裹信息
       $update['member_id'] = !empty($user_id)?$user_id:$data['member_id'];
       $update['length'] = !empty($length)?$length:$data['length'];
       $update['height'] = !empty($height)?$height:$data['height'];
       $update['width'] = !empty($width)?$width:$data['width'];
       $update['weight'] = !empty($weight)?$weight:$data['weight'];
       $update['remark'] = !empty($data['remark'])?$data['remark'].';'.$remark:$remark;
       $update['storage_id'] = $clerk['shop_id'];
       $update['status'] = 2;
       $update['updated_time'] = getTime();
       $update['entering_warehouse_time'] = getTime();
       if($length>0 && $width>0 && $height>0){
             $update['volume'] = $width*$length*$height/1000000;
       }
    //   dump($update);die;
       $res = (new Package())->where(['id'=>$id])->update($update);
       //插入图片
       $this->inImages($id,$imageIds,$wxapp_id);
       //更新日志
       Logistics::add($id,'包裹已到达仓库');
       if (!$res){
        return $this->renderError('包裹入库失败');
       }
       //存储包裹的分类信息；
        $classItem = [];
         if ($class_ids){
             //清理之前的类目，更新掉
             $packItemModel->where('order_id',$id)->delete();
             $classItem = $this->parseClass($class_ids);
             foreach ($classItem as $k => $val){
                   $classItem[$k]['class_id'] = $val['category_id'];
                   $classItem[$k]['express_name'] = $data['express_name'];
                   $classItem[$k]['class_name'] = $val['name'];
                   $classItem[$k]['express_num'] = $data['express_num'];
                   $classItem[$k]['all_price'] = '';
                   unset($classItem[$k]['category_id']); 
                   unset($classItem[$k]['name']);        
             }
         }
         if ($classItem){
             $packItemRes = $packItemModel->saveAllData($classItem,$id);
             if (!$packItemRes){
                return $this->renderError('包裹类目更新失败');
             }
         }
       
       //存入货架信息
         $shelfunit = new ShelfUnitItem();
         $shelf = [
            'pack_id' => $data['id'],
            'user_id' => isset($user_id)?$user_id:$data['member_id'],
            'express_num' => $data['express_num'],
            'shelf_unit' => $shelf_unit,
         ];  
         $shelfunit->post($shelf);
       
       //仓库id存在，则查询到仓库名称，传入模板消息
        if($data['storage_id']){
            $shopData =  (new Shop())->where('shop_id',$data['storage_id'])->find();
            $post['shop_name'] = $shopData['shop_name'];
        }
       //包裹入库通知
       $data = array_merge($data->toArray(),$update);
       $data['shop_name']= $shopData['shop_name'];
       (new Package())->sendEnterMessage([$data]);
       
       if($data['member_id']){
          //邮件通知
           $data['code'] = $data['express_num'];
           $data['logistics_describe']='包裹已入库，可提交打包';
           $user = (new UserModel())->find($data['member_id']);
           if($user['email']){
               (new Email())->sendemail($user,$data,$type=1);
           } 
       }
       
       return $this->renderSuccess('包裹入库成功'); 
           
       }elseif($is_pre==20){
        //1、当是集运订单的单号时，存储当前的入库仓库id，使用字段shop_id
        $Inpack = new Inpack();
        $packData = $Inpack::detail($id);
        $userInfo = $this->user;
        //存入货架信息
        $takecode = rand(100000,999999);
        if(!empty($shelf_unit)){
            $this->saveToShelf($packData['id'],$userInfo['user_id'],$packData['t_order_sn'],$shelf_unit);
            $takecode =$this->takecode($id);
        }
        
        
        $logis['code'] = $express_num;
        $logis['logistics_describe']='包裹已到达'.$clerk['shop']['shop_name'].'取货码：'.$takecode;
        // dump($clerk['shop']['shop_name']);die;
        $Inpack->UpdateShop($id,$clerk['shop_id'],$takecode);
        //2、添加一条入库记录； 
        Logistics::addInpackGetLog($id,'包裹已到达'.$clerk['shop']['shop_name'].'请及时前来取货',$express_num);
        //3、发送模板消息到货并通知用户取货；
        
        $data['order_sn'] = $packData['order_sn'];
        $data['order'] = $packData;
        $data['order']['total_free'] = $packData['free'];
        $data['order']['userName'] = $userInfo['nickName'];
        $data['order_type'] = 10;
        $data['order']['remark'] = $logis['logistics_describe'];
        $resss = Message::send('order.payment',$data);  
        //4、发送邮件通知
        !empty($userInfo['email']) && (new Email())->sendemail($userInfo,$logis,$type=1);
        
        
        return $this->renderSuccess('集运单入库成功');    
       }
       
    }
    
    //存入货架信息
    public function saveToShelf($order_id,$user_id,$express_num,$shelf_unit){
        $shelfunit = new ShelfUnitItem();
        $shelf = [
            'pack_id' => $order_id,
            'user_id' => $user_id,
            'express_num' => $express_num,
            'shelf_unit' => $shelf_unit,
         ];
         return $shelfunit->post($shelf);
    }
    
    //存入货架信息
    public function takecode($id){
        $shelfunit = new ShelfUnitItem();
        $take_code = $shelfunit->getShelfUnitByPackId($id);
        return $take_code;
    }
    
    
    // 仓管端提交入库
    public function instorage() {
        $packItemModel = new PackageItemModel();
        if (!$this->checkRole(1)){
            return $this->renderError('角色权限非法');
        }
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      
        if (!$clerk){
           return $this->renderError('角色权限非法');
        }
       $id = $this->postData('id')[0];
       $user_id = $this->postData('user_id')[0];
       $class_ids = $this->postData('class_ids')[0];
       $user_code = $this->postData('user_Code')[0];
       $is_pre = $this->postData('is_pre')[0];
       $express_num = $this->postData('express_num')[0];
       $weight = $this->postData('weight')[0];
       $height = $this->postData('height')[0];
       $length = $this->postData('length')[0];
       $width = $this->postData('width')[0];
       $imageIds = $this->postData('imageIds');
       $wxapp_id = \request()->get('wxapp_id');
       $remark = $this->postData('remark')[0];
       $shelf_unit = $this->postData('shelf_unit')[0];
       
       if(preg_match("/^42097321/i",$express_num,$resd)){
            $express_num = mb_substr($express_num,8);
        }
        //处理FEDEX单号问题
        if(preg_match("/^96220/i",$express_num,$resds) && strlen($express_num) > 30){
            $express_num = substr($express_num,-12);
        }
       //再加一层校验；
        $map[] = ['is_delete','=',0];
        $map[] = ['express_num','=',$express_num]; 
        $is_exitres = (new Package())->setQuery($map)->find();
        if($is_exitres){
            $is_pre =true;
        }
       //$is_pre 为没有查询到包裹数据的情况，需要插入新数据
       if ($is_pre==false){
            if ($user_id){
                $user = (new UserModel())->find($user_id);
                if (!$user)
                    return $this->renderError('用户不存在');
            }
              
            if($user_code){
                $users = (new UserModel())->where('user_code',$user_code)->find();
                if (!$users){
                    return $this->renderError('用户不存在');}
                $user_id = $users['user_id'];
            }
           
            $order['status'] = 2;
            $order['is_take'] = 1;
            $order['storage_id'] = $clerk['shop_id'];
            if ($user_id){
                $order['member_id'] = $user_id;
                $order['is_take'] = 2;
            }
            $order['order_sn'] = createSn();
            $order['weight'] = $weight;
            $order['source'] = 8;
            $order['length'] = $length;
            $order['width'] = $width;
            $order['height'] = $height;
            $order['express_num'] = $express_num;
            $order['updated_time'] = getTime();
            $order['created_time'] = getTime();
            $order['remark'] = $remark;
            $order['entering_warehouse_time'] = getTime();
            $restwo = (new Package())->saveData($order); //获取到包裹的id
            if (!$restwo){
                 return $this->renderError('包裹入库失败');
            }
            //存储包裹的分类信息；
            $classItem = [];
             if ($class_ids){
                 $classItem = $this->parseClass($class_ids);
                 foreach ($classItem as $k => $val){
                       $classItem[$k]['class_id'] = $val['category_id'];
                       $classItem[$k]['express_name'] = '';
                       $classItem[$k]['class_name'] = $val['name'];
                       $classItem[$k]['express_num'] = $express_num;
                       $classItem[$k]['all_price'] = '';
                       unset($classItem[$k]['category_id']); 
                       unset($classItem[$k]['name']);        
                 }
             }
             if ($classItem){
                 $packItemRes = $packItemModel->saveAllData($classItem,$restwo);
                 if (!$packItemRes){
                    return $this->renderError('包裹类目更新失败');
                 }
             }
            //存储上传的图片
            $this->inImages($restwo,$imageIds,$wxapp_id);
            $shopData =  (new Shop())->where('shop_id',$clerk['shop_id'])->find();
            //存入货架信息
             $shelfunit = new ShelfUnitItem();
             $shelf = [
                'pack_id' => $restwo,
                'user_id' => $user_id,
                'express_num' => $express_num,
                'shelf_unit' => $shelf_unit,
             ];  
             $shelfunit->post($shelf);
            //邮件通知
            //判断是否有用户id，发送邮件
              if(isset($user_id) || !empty($user_id)){
                  $EmailUser = UserCommonModel::detail($user_id);
                  if($EmailUser['email']){
                      $EmailData['code'] = $order['express_num'];
                      $EmailData['logistics_describe']='包裹已入库，可提交打包';
                     (new Email())->sendemail($EmailUser,$EmailData,$type=1);
                  }
                    $data['order'] = [];
                    //发送订阅消息以及模板消息
                    //发送订阅消息，模板消息
                    $order['id'] = $restwo;
                    $order['wxapp_id'] = $wxapp_id;
                    $order['shop_name'] = $shopData['shop_name'];
                    $data['order'] = $order;
                    $data['order']['member_name'] = $EmailUser['nickName'];
                    $data['order_type'] = 10;
                    $data['order']['remark'] ="包裹已入库，可提交打包" ;
                    if($user_id!=0){
                      Message::send('order.enter',$data);  
                    }
              }
            
            return $this->renderSuccess('包裹入库成功');
       }
       
       //有对应数据情况下
       $data = (new Package())->find($id);
       
       if ($data['status']==2){
         return $this->renderError('包裹已入库');
       }
       $update['status'] = 2;
       $update['updated_time'] = getTime();
       $update['entering_warehouse_time'] = getTime();
       $res = (new Package())->where(['id'=>$id])->update($update);
       //插入图片
       $this->inImages($id,$imageIds,$wxapp_id);
       //更新日志
       Logistics::add($id,'包裹已到达仓库');
       if (!$res){
        return $this->renderError('包裹入库失败');
       }
       //存储包裹的分类信息；
      
        
        $classItem = [];
         if ($class_ids){
             //清理之前的类目，更新掉
             $packItemModel->where('order_id',$id)->delete();
             $classItem = $this->parseClass($class_ids);
             foreach ($classItem as $k => $val){
                   $classItem[$k]['class_id'] = $val['category_id'];
                   $classItem[$k]['express_name'] = '';
                   $classItem[$k]['class_name'] = $val['name'];
                   $classItem[$k]['express_num'] = $data['express_num'];
                   $classItem[$k]['all_price'] = '';
                   unset($classItem[$k]['category_id']); 
                   unset($classItem[$k]['name']);        
             }
         }
         if ($classItem){
             $packItemRes = $packItemModel->saveAllData($classItem,$id);
             if (!$packItemRes){
                return $this->renderError('包裹类目更新失败');
             }
         }
       
       //存入货架信息
         $shelfunit = new ShelfUnitItem();
         $shelf = [
            'pack_id' => $data['id'],
            'user_id' => isset($user_id)?$user_id:$data['member_id'],
            'express_num' => $data['express_num'],
            'shelf_unit' => $shelf_unit,
         ];  
         $shelfunit->post($shelf);
       
       //仓库id存在，则查询到仓库名称，传入模板消息
        if($data['storage_id']){
            $shopData =  (new Shop())->where('shop_id',$data['storage_id'])->find();
            $post['shop_name'] = $shopData['shop_name'];
        }
       //包裹入库通知
       $data = array_merge($data->toArray(),$update);
       $data['shop_name']= $shopData['shop_name'];
       (new Package())->sendEnterMessage([$data]);
       
       if($data['member_id']){
          //邮件通知
           $data['code'] = $data['express_num'];
           $data['logistics_describe']='包裹已入库，可提交打包';
           $user = (new UserModel())->find($data['member_id']);
           if($user['email']){
               (new Email())->sendemail($user,$data,$type=1);
           } 
       }
       
       
       return $this->renderSuccess('包裹入库成功');
    }
    
    // 格式化
     public function parseClass($class_ids){
         $class_item = [];
         $class_ids = explode(',',$class_ids);
         $class = (new Category())->whereIn('category_id',$class_ids)->field('category_id,name')->select()->toArray(); 
         return $class;
     }
    
    /**
     * $id 包裹的id
     * $imageIds 图片数组
     */
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
    
    
    /**
     *  BY FENG 2022年12月27日
     * 包裹列表统计
     */
    public function packTotaldata(){
      // 员工信息
      $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      if (!$clerk){
          return $this->renderError('角色权限非法');
      }
      $packModel = (new Package());
      $inpackModel = (new Inpack());
      $today = date('Y-m-d');
      $tomorrow = date('Y-m-d', strtotime('+1 day'));
      $values = SettingModel::getItem('store')['retention_day'];
      $res = [
         'today' => $packModel ->where('status','in',[2,3,4,5])->where(['storage_id'=>$clerk['shop_id']])->whereTime('entering_warehouse_time','between',[$today,$tomorrow])->count(),
         'all' => $packModel -> where(['storage_id'=>$clerk['shop_id'],'is_delete'=>0])->where('status','in',[-1,2,3,4,5,6,7])->count(),
         'pack' => $inpackModel ->where(['status'=>1,'storage_id'=>$clerk['shop_id']])->count(), //待打包的集运单
         'waitTake' => $inpackModel->where(['status'=>7,'shop_id'=>$clerk['shop_id']])->count(),
         'appointment' => $packModel->where(['status'=>1,'storage_id'=>$clerk['shop_id'],'source'=>7])->count(),
         'retention' => $inpackModel->where(['status'=>7,'shop_id'=>$clerk['shop_id']])->where('updated_time','<= time',date('Y-m-d',time()-7*86400))->count(),
      ];
      return $this->renderSuccess($res);
    }
    
    // 包裹列表统计
    public function packTotal(){
      // 员工信息
      $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      if (!$clerk){
          return $this->renderError('角色权限非法');
      }
      $packModel = (new Package());
      $res = [
         'no' => $packModel ->where(['status'=>1,'storage_id'=>$clerk['shop_id'],'is_delete'=>0])->count(),
         'in' => $packModel -> where(['status'=>array('in',[2,3,4]),'storage_id'=>$clerk['shop_id'],'is_delete'=>0])->count()
      ];
      return $this->renderSuccess($res);
    }

    /**
     * 仓库管理员 包裹列表
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
    public function packlist(){
        if (!$this->checkRole(1)){
           return $this->renderError('角色权限非法');
        }
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $status = $this->postData('status')?$this->postData('status')[0]:1;
        $map = ['status'=>$status];
        if($status==2){
            $map['status'] = [2,3,4];
        }
        $keyword = $this->postData("keyword")[0];
        $map['storage_id'] = $clerk['shop_id'];
        $map['is_delete'] = 0;
        $map['keyword'] = $keyword;
        $shelfunit = new ShelfUnitItem();
        $packModel = (new Package());
        if($status==1){
            $data = $packModel->getGList($map,'created_time');
        }else{
            $data = $packModel->getGList($map,'entering_warehouse_time');
        }
        return $this->renderSuccess($data);
    }

    // 包裹详情
    public function packdetails(){
          if (!$this->checkRole(1)){
            return $this->renderError('角色权限非法');
          }
          // 员工信息
          $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
          if (!$clerk){
              return $this->renderError('角色权限非法');
          }
          $packModel = (new Package());
          $id = $this->postData('id')?$this->postData('id')[0]:1;
        //   dump( $this->postData('id'));die;
          $data = $packModel ->with(['member','country','packageimage.file'])->find($id);//->with('packageimage.file')
          // 获取物品详情
          $packItem = (new PackageItemModel())->where(['order_id'=>$data['id']])->field('class_name,id,class_id,order_id')->select();
          $data['shop'] = '';
          if ($packItem){
              $data['shop'] = implode(',',array_column($packItem->toArray(),'class_name'));
          }
          return $this->renderSuccess($data);
    }
    
    // 获取包裹详情
    public function getdetails(){
          if (!$this->checkRole(1)){
            return $this->renderError('角色权限非法');
          }
          // 员工信息
          $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
          if (!$clerk){
              return $this->renderError('角色权限非法');
          }
          $packModel = (new Package());
          $id = $this->postData('id')?$this->postData('id')[0]:1;
        //   dump( $this->postData('id'));die;
          $data = $packModel ->with(['member','country','packageimage.file'])->find($id);//->with('packageimage.file')
          // 获取物品详情
          $packItem = (new PackageItemModel())->where(['order_id'=>$data['id']])->field('class_name,id,class_id,order_id')->select();
          $data['shop'] = '';
          if ($packItem){
              $data['shop'] = implode(',',array_column($packItem->toArray(),'class_name'));
          }
          return $this->renderSuccess($data);
    }
    //编辑包裹
    public function editpack(){
        $data = $this->request->param();
        $packModel = (new Package());
        $res= $packModel->editData($data);
        if($res){
            return $this->renderSuccess("编辑成功");
        }
        return $this->renderError('编辑失败');
    }

    // 待查验清单
    public function loadingpack(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $status = $this->postData('status')[0];
        if($status=='2'){
            $status = [2,3,4,5];
        }
        $keyword = $this->postData('keyword')[0];
        $map['status'] = $status;
        $map['storage_id'] = $clerk['shop_id'];
        $map['keyword'] = $keyword;
        $packModel = (new Inpack());
        $data = $packModel->getGList($map,'updated_time');
        // dump($data->toArray());die;
        return $this->renderSuccess($data);
    }
    
     
    /**
     * 预约集运单
     * BY FENG 2022年12月27日
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
    public function appointment(){
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        if (!$this->checkRole(4)){
            return $this->renderError('角色权限非法');
        }
        $status = $this->postData('status')[0]?$this->postData('status')[0]:1;
        $keyword = $this->postData('keyword')[0];
        $map = ['is_take'=>2,'source'=>7,'status'=>$status,'keyword'=>$keyword,'storage_id'=>$clerk['shop_id']];
        $packModel = (new Package());
        $data = $packModel->getYList($map);
        return $this->renderSuccess($data);
    }
    
    
    /**
     * 待取件集运单统计数据
     * BY FENG 2022年12月27日
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
     public function waitPickPackTotal(){
        if (!$this->checkRole(4)){
            return $this->renderError('角色权限非法');
        }
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $Inpack = (new Inpack());
        
        $res = [
           'no' => $Inpack ->where(['status'=>7,'storage_id'=>$clerk['shop_id'],'is_delete'=>0])->count(),
           'in' => $Inpack -> where(['status'=>8,'storage_id'=>$clerk['shop_id'],'is_delete'=>0])->count()
        ];
        return $this->renderSuccess($res);
    }
    
    /**
     * 待取件集运单
     * BY FENG 2023年1月4日
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
    public function waitPickPack(){
        if (!$this->checkRole(4)){
            return $this->renderError('角色权限非法');
        }
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        // dump($clerk);die;
        $status = $this->postData('status')[0]?$this->postData('status')[0]:8;
        $keyword = $this->postData('keyword')[0];
        // dump($status);
        $map = ['status'=>$status,'keyword'=>$keyword,'storage_id'=> $clerk['shop_id']];
        $Inpack = (new Inpack());
        if($status==7){
            $data = $Inpack->getWList($map);
        }
        if($status==8){
            $data = $Inpack->getWYList($map);
        }
        
        return $this->renderSuccess($data);
    }
    
    /**
     * 预约集运单统计数据
     * BY FENG 2022年12月27日
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
     public function appointmentTotal(){
        if (!$this->checkRole(4)){
            return $this->renderError('角色权限非法');
        }
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $packModel = (new Package());
        $res = [
           'no' => $packModel ->where(['status'=>1,'is_take'=>2,'source'=>7,'storage_id'=>$clerk['shop_id']])->count(),
           'in' => $packModel -> where(['status'=>2,'is_take'=>2,'source'=>7,'storage_id'=>$clerk['shop_id']])->count()
        ];
        return $this->renderSuccess($res);
    }
    
    
    /**
     * 仓管端数据统计
     * BY FENG 2022年12月27日
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
    public function screenData(){
        $data = $this->request->param();
        // $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        $packModel = (new Package());
        $map['status']  = array('in',[2,3,4,5,6,7]);
        // dump(base_url());die;
        return $this->renderSuccess([
            ['icon' => base_url().'assets/api/images//dzx_img212.png' ,'content'=>'未入库包裹','num'=> $packModel->where(['status' => 1,'is_delete'=>0,'storage_id' =>$data['shop_id']])->count()], //未入库包裹
            ['icon' => base_url().'assets/api/images//dzx_img213.png','content'=>'已入库包裹','num'=> $packModel->where(['status' => 2,'is_delete'=>0,'storage_id' =>$data['shop_id']])->count()], //已入库包裹
            ['icon' => base_url().'assets/api/images//dzx_img214.png','content'=>'在途中包裹','num'=> $packModel->where(['status' => 9,'is_delete'=>0,'storage_id' =>$data['shop_id']])->count()], //在途中包裹
            ['icon' => base_url().'assets/api/images//dzx_img215.png','content'=>'已完成包裹','num'=> $packModel->where(['status' => 11,'is_delete'=>0,'storage_id' =>$data['shop_id']])->count()],//已完成包裹
            ['icon' => base_url().'assets/api/images//dzx_img216.png','content'=>'待打包包裹','num'=> $packModel->where($map)->where(['is_delete'=>0,'storage_id' => $data['shop_id'],'is_take' =>2])->count()],//待打包包裹
            ['icon' => base_url().'assets/api/images//dzx_img217.png','content'=>'待发货包裹','num'=> $packModel->where(['status' => 8,'is_delete'=>0,'storage_id' =>$data['shop_id']])->count()], //待发货包裹
            ['icon' => base_url().'assets/api/images//dzx_img218.png','content'=>'待认领包裹','num'=> $packModel->where(['is_take' => 1,'is_delete'=>0,'storage_id' =>$data['shop_id']])->count()], //待认领包裹
            ['icon' => base_url().'assets/api/images//dzx_img219.png','content'=>'问题件包裹','num'=> $packModel->where(['status' => -1,'is_delete'=>0,'storage_id' =>$data['shop_id']])->count()] //问题件包裹
        ]);
    }
    
    //环形数据
    public function getConsolidationOrderData(){
      $data = $this->request->param();
      $Inpack = new Inpack;
      $daichayan = $Inpack->where(['storage_id'=>$data['shop_id'],'status'=>1])->count();
      $daifahuo = $Inpack->where(['storage_id'=>$data['shop_id'],'status' => ['in',[2,3,4,5]] ])->count();
      $zaituzhong = $Inpack->where(['storage_id'=>$data['shop_id'],'status' => ['in',[6,7,8]]  ])->count();
      $total = $daichayan + $daifahuo + $zaituzhong;
      $datas = [
          ['name' =>'待查验','value'=>$daichayan,'labelShow'=>false],
          ['name' =>'待发货','value'=>$daifahuo,'labelShow'=>false],
          ['name' =>'在途中','value'=>$zaituzhong,'labelShow'=>false],
      ];
      return $this->renderSuccess([ 'series' => [0 => ['data'=>$datas]],'tongji' => ['total' => $total, 'daichayan'=> $daichayan,'daifahuo'=>$daifahuo,'zaituzhong'=>$zaituzhong] ]); 
            
      
    }
    
    //用户增长数据
    public function getUserGrowthChartData(){
        $UserModel = new UserModel();
        $start = strtotime(date('Y-m-d',time()));
        $total = $UserModel->where('is_delete',0)->count();
        $today = $UserModel->where('is_delete',0)->where('create_time','between',[$start,$start + 86400])->count();
        // dump(date('j',time()-86400));die;
        $userD = [
            $UserModel->where('is_delete',0)->where('create_time','between',[$start-86400*6,$start-86400*5])->count(),
            $UserModel->where('is_delete',0)->where('create_time','between',[$start-86400*5,$start-86400*4])->count(),
            $UserModel->where('is_delete',0)->where('create_time','between',[$start-86400*4,$start-86400*3])->count(),
            $UserModel->where('is_delete',0)->where('create_time','between',[$start-86400*3,$start-86400*2])->count(),
            $UserModel->where('is_delete',0)->where('create_time','between',[$start-86400*2,$start-86400])->count(),
            $UserModel->where('is_delete',0)->where('create_time','between',[$start-86400,$start])->count()
        ];
        $datas = ['name' =>'用户数量','data'=>$userD,'labelShow'=>false];
        return $this->renderSuccess([
            'result'=>['categories'=>[
                date('j',time()-86400*6).'号',
                date('j',time()-86400*5).'号',
                date('j',time()-86400*4).'号',
                date('j',time()-86400*3).'号',
                date('j',time()-86400*2).'号',
                date('j',time()-86400).'号'], 
                'series' =>[0=>$datas] ],
                'tongji' => ['total' => $total, 'today'=>$today] 
        ]);
    }
    
    //包裹增长数据
    public function getPackageGrowthData(){
        $data = $this->request->param();
        $packModel = (new Package());
        $Inpack = new Inpack;
        $start = strtotime(date('Y-m-d',time()));
        $benzhou = [
            $packModel->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*6),date('Y-m-d',$start-86400*5)])->count(),
            $packModel->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*5),date('Y-m-d',$start-86400*4)])->count(),
            $packModel->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*4),date('Y-m-d',$start-86400*3)])->count(),
            $packModel->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*3),date('Y-m-d',$start-86400*2)])->count(),
            $packModel->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*2),date('Y-m-d',$start-86400)])->count(),
            $packModel->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400),date('Y-m-d',$start)])->count(),
        ];
        
        $benyue = [
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*6),date('Y-m-d',$start-86400*5)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*5),date('Y-m-d',$start-86400*4)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*4),date('Y-m-d',$start-86400*3)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*3),date('Y-m-d',$start-86400*2)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*2),date('Y-m-d',$start-86400)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400),date('Y-m-d',$start)])->count(),
        ];
        $datas = [0=>['name' =>'包裹','data'=>$benzhou,'labelShow'=>false],1=>['name' =>'订单','data'=>$benyue,'labelShow'=>false]];
        return $this->renderSuccess([
            'result'=>[
                'categories'=>[
                    date('j',time()-86400*6).'号',
                    date('j',time()-86400*5).'号',
                    date('j',time()-86400*4).'号',
                    date('j',time()-86400*3).'号',
                    date('j',time()-86400*2).'号',
                    date('j',time()-86400).'号'], 
                'series' =>$datas,
                'min' => 0
            ],
                
        ]);
    }
    
    
    //集运单走势
    public function getConsolidationOrderTransactionData(){
        $data = $this->request->param();
        $Inpack = new Inpack;
        $start = strtotime(date('Y-m-d',time()));
        $benyue = [
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*6),date('Y-m-d',$start-86400*5)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*5),date('Y-m-d',$start-86400*4)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*4),date('Y-m-d',$start-86400*3)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*3),date('Y-m-d',$start-86400*2)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400*2),date('Y-m-d',$start-86400)])->count(),
            $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[date('Y-m-d',$start-86400),date('Y-m-d',$start)])->count(),
        ];
        $datas = ['name' =>'订单数量','data'=>$benyue];
        return $this->renderSuccess([
            'result'=>[
                'categories'=>[
                    date('j',time()-86400*6).'号',
                    date('j',time()-86400*5).'号',
                    date('j',time()-86400*4).'号',
                    date('j',time()-86400*3).'号',
                    date('j',time()-86400*2).'号',
                    date('j',time()-86400).'号'], 
                'series' =>[0=>$datas],
            ],
        ]);
    }
    
    //排名
    public function getRankingList(){
        $data = $this->request->param();
        $Inpack = new Inpack;
        $line = new Line();
        $linedata = $line->getListAll([]);
        $total = $Inpack->where('storage_id',$data['shop_id'])->count();  
        foreach ($linedata as $key=>$val){
           $datas[$key]['name'] = $val['name'];
           $datas[$key]['num'] = $Inpack->where('storage_id',$data['shop_id'])->where('line_id',$val['id'])->count();  
           $datas[$key]['total'] = $total;
        }
        $sort = array_column($datas,'num');
        array_multisort($sort,SORT_DESC,$datas);
        
        // dump($datas);die;
        return $this->renderSuccess([
            'result'=>array_slice($datas,0,10),
        ]);
    }
    
    /**
     * 预约件确认入库
     * BY FENG 2022年12月27日
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
     public function appointmentToShop(){
        if (!$this->checkRole(4)){
            return $this->renderError('角色权限非法');
        }
        $packModel = (new Package());
        $data = $this->postData();
        $res = $packModel->where('id',$data['id'])->update(['status'=>2]);
        if(!$res){
            return $this->renderError('取货失败');
        }
        return $this->renderSuccess('确认取货');
    }
    
    
    
    // 集运清单是否查验完成
    public function isoverpack(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $status = $this->postData('status')[0]?$this->postData('status')[0]:1;
        $keyword = $this->postData('keyword')[0];
        $map['status'] = $status;
        if($status==2){
            $map['status'] = [2,3,4,5];
        }
        $map['is_delete'] = 0;
        $map['keyword'] = $keyword;
        $packModel = (new Inpack());
        $Package = new Package();
        $sdata = $packModel->getGList($map,'unpack_time');
        foreach ($sdata as $key => $val){
            $pack_id = explode(',',$val['pack_ids']);
            $sdata[$key]['is_scan'] = 2;
            foreach ($pack_id as $k =>$va){
                $is_scan = $Package->where('id',$va)->value('is_scan');
                if($is_scan==1){
                    $sdata[$key]['is_scan'] = 1;
                }
                break;
            }
        }
        $data =$sdata;
        return $this->renderSuccess($data);
    }

    public function loadingpackTotal(){
        if (!$this->checkRole(2)){
            return $this->renderError('角色权限非法');
        }
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $packModel = (new Inpack());
       
        $res = [
           'no' => $packModel->where(['status'=>1,'storage_id'=>$clerk['shop_id'],'is_delete'=>0])->count(),
           'in' => $packModel-> where(['status'=> array('in',[2,3,4,5]),'storage_id'=>$clerk['shop_id'],'is_delete'=>0])->count()
        ];
        //  dump($packModel->getLastsql());die;
        return $this->renderSuccess($res);
    }

    /**
     *包裹转移 
     */
    public function packageMove(){
        $id = $this->postData('id')[0];
        $shelf_unit_id = $this->postData('shelf_unit_id')[0];
        if (!$this->checkRole(2)){
            return $this->renderError('角色权限非法');
        }
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $shelf = (new ShelfUnit())->find($shelf_unit_id);
        if (!$shelf){
            return $this->renderError('货架单元不存在');
        }
        $pack_info = (new Package())->field('id,status,wxapp_id,express_num,member_id')->find($id);
        if(!empty($pack_info)){
           if ($pack_info['status']>6){
                return $this->renderError('包裹状态错误');
           }
          (new ShelfUnitItem())->where(['pack_id'=>$pack_info['id']])->delete(); 
           $upShelf = [
              'shelf_unit' => $shelf_unit_id,
              'wxapp_id'=> $pack_info['wxapp_id'],
              'user_id' =>$pack_info['member_id'],
              'express_num' =>$pack_info['express_num'],
              'pack_id' => $id,
            ];
        }else{
           $Inpack = new Inpack();
           $inpackinfo = $Inpack->where(['id'=> $id,'is_delete'=>0])->find();
           if($inpackinfo){
               (new ShelfUnitItem())->where(['pack_id'=>$inpackinfo['order_sn']])->whereOr('express_num',$inpackinfo['t_order_sn'])->delete();
               $upShelf = [
                  'shelf_unit' => $shelf_unit_id,
                  'wxapp_id'=> $inpackinfo['wxapp_id'],
                  'user_id' =>$inpackinfo['member_id'],
                  'express_num' =>$inpackinfo['t_order_sn'],
                  'pack_id' => $inpackinfo['order_sn'],
                ];
           }else{
               return $this->renderError('包裹不存在');
           }
        }

        
        $res = (new ShelfUnitItem())->post($upShelf);
        if (!$res){
            return $this->renderError('包裹操作失败');
        }
        return $this->renderSuccess('包裹操作成功');
    }
    
      // 分拣员 - 分拣下架
    public function checkDownShelf(){
      if (!$this->checkRole(2)){
          return $this->renderError('角色权限非法');
      }
      // 员工信息
      $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      if (!$clerk){
            return $this->renderError('角色权限非法');
      }
      $id = $this->postData('id')[0];
      // 查询包裹所在货位
      $pack =  (new Package());
      $packdata = $pack->where(['id'=>$id])->find();
  
      if(!empty($packdata)){
            $res = $shelfUnitItem = (new ShelfUnitItem())->where(['pack_id'=>$id])->delete();
            if(!$res){
                  return $this->renderError('包裹操作失败');
            }
           Logistics::add($id,'包裹已拣货下架完毕,等待打包员进行打包'); 
      }else{
          $inpackData = (new Inpack())->where('id',$id)->find();
     
          if(!empty($inpackData)){
            $res = $shelfUnitItem = (new ShelfUnitItem())->where(['pack_id'=>$inpackData['order_sn']])->whereOr('express_num',$inpackData['t_order_sn'])->delete();
            if(!$res){
                  return $this->renderError('包裹操作失败');
            }
          }
      }
     
      // 下架 
    // //   $res = (new ShelfUnitItem())->where(['shelf_unit_item_id'=>$shelfUnitItem['shelf_unit_item_id']])->delete();
    //   if (!$res){
    //       return $this->renderError('包裹操作失败');
    //   }
    //   $update['status'] = 7;
    //   $up = (new Package())->where(['id'=>$id])->update($update);
    //   if (!$up){
    //     return $this->renderError('包裹操作失败');
    //   }
    //   $map['storage_id'] = $clerk['shop_id'];
    //   (new Inpack())->CheckISShelf($map); 
      
      return $this->renderSuccess('下架成功');
    }
    
    // 拣货详情
    public function loadingpackdetails(){
        if (!$this->checkRole(2)){
          return $this->renderError('角色权限非法');
        }
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $packModel = (new Inpack());
        $id = $this->postData('id')?$this->postData('id')[0]:1;
        //完成集运单价格的计算；
        // getpackfree($id);
        
        $data = $packModel ->with(['member','inpackimage.file'])->find($id);
        
        $InpackService = new InpackService();//包装服务 
        $data['service'] = $InpackService->with('service')->where('inpack_id',$id)->select();

        $packData = explode(',',$data['pack_ids']);
        $dataPack =[];
        $pack =  (new Package());
        $shelf = new ShelfUnitItem();

        foreach($packData as $key => $val){
          $dataPack[$key] = $pack->where('id',$val)->with(['packageimage.file','packitem','shelfunititem.shelfunit.shelf'])->find();
        //   $dataPack[$key]['shelfNo'] = $shelf->getShelfUnitByPackId($val);
       
        }
        
        $data['packs'] =json_encode($dataPack);
          
        $data['packs'] =json_decode($data['packs']);
        // 获取物品详情
        // $packItem = (new PackageItemModel())->where(['order_id'=>$data['id']])->field('class_name,id,class_id,order_id')->select();
       
        $data['shop'] = '';
        // if ($packItem){
        //     $data['shop'] = implode(',',array_column($packItem->toArray(),'class_name'));
        // }
    
        if ($data['address_id']){
            $data['address'] = (new UserAddress())->find($data['address_id']);
        }
         
        if ($data['line_id']){
            $data['line'] = (new Line())->field('name')->find($data['line_id']);
        }
        $data['total'] =round($data['free'] +  $data['other_free'] +  $data['pack_free'],2);
      
        return $this->renderSuccess($data);
    }

    // 分拣员 - 查询包裹，并下架
    public function checkPackInStorage(){
      // 员工信息
      $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      if (!$clerk){
          return $this->renderError('角色权限非法');
      }
      $code = $this->postData('code')[0];
   
    //   $map[] = ['status','in',[2,6,7]];
      $map[] = ['is_delete','=',0];
      $map[] = ['storage_id','=',$clerk['shop_id']];
    //   $map[] = ['is_take','=',2];
      $map[] = ['express_num','=',$code]; 
      $res = (new Package())->setQuery($map)->with('storage')->find();
      if (!$res){
        $Inpack = (new Inpack());
        $ress = $Inpack->where(['t_order_sn'=> $code,'is_delete'=>0])->where(['shop_id'=>$clerk['shop_id']])->find();
        if($ress){
            $ress['express_num'] = $code;
            return $this->renderSuccess($ress);
        }
        return $this->renderError('包裹未查询到');
      }
      return $this->renderSuccess($res);
    }
    
    
    /**
     * 该方法将扫描到的包裹状态设置为is_scan =2 已扫描状态，方便仓管核对
     * 2022年5月26日
     */
    public function checkPackScan(){
      
      // 员工信息
      $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      if (!$clerk){
          return $this->renderError('角色权限非法');
      }
      
      $code = $this->postData('code')[0];
      //特殊处理京东单号
      if(stristr($code,'JD')){
         $code = explode('-',$code)[0];
      }
      $jid = $this->postData('jid')[0];
      if(empty($jid)){
         return $this->renderError('集运单id不存在');  
      }
      $inpack= new Inpack();
      $packageData = $inpack->where('id',$jid)->find(); 
      if(empty($packageData)){
         return $this->renderError('集运单不存在');  
      }
      $arrayPackid = explode(',',$packageData['pack_ids']);
      $packid = (new Package())->where('express_num',$code)->where('is_delete',0)->find();
      if (!$packid){
        return $this->renderError('未查询到此快递单');
      }
      if(in_array($packid['id'],$arrayPackid)){
           $res =(new Package())->where('express_num',$code)->update(['is_scan'=>2]);
           return $this->renderSuccess($code);
      }else{
           return $this->renderError('此单号不在此集运单中');
      }
    }
    
    /***
     * 当前用户角色
     * 仓管员功能
     * parem: $id
    */
    public function role(){
         // 当前用户信息
        $userInfo = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if(!$userInfo){
            $role_name = '普通用户';
            $role_type = 0;
        }else{
            $clerkType = explode(',',$userInfo['clerk_type']);
            if(in_array('5',$clerkType)){
                $role_name = '仓库长';
                $role_type = 10;
            }else{
                $role_name = '仓管员';
                $role_type = 20;
            }
        }
         $userRole['role_name'] = $role_name;
         $userRole['role_type'] = $role_type;
         $this->userRole = $userRole;
         return $this->renderSuccess(compact('userRole'));
    }
    
    /***
     * 集运单状态变更
     * 仓管员封箱功能
     * parem: $id
     * param：$status
     */
    public function changeOrderStatus($id,$status){
        if (!$this->checkRole(3)){
          return $this->renderError('角色权限非法');
        }
        $inpack= new Inpack();
        $res = $inpack->where('id',$id)->update(['status' => $status,'pick_time' => getTime()]);
        if($res){
        //进行通知打包员
        $clerkdd = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerkdd){
            return $this->renderError('角色权限非法');
        } 
        
         $packData = $inpack::detail($id);
         $clerk = (new Clerk())->where('shop_id',$packData['storage_id'])->where(['send_status'=>0,'is_delete' => 0])->select();
            //  if(!empty($clerk)){
            //     $data['order_sn'] = $packData['order_sn'];
            //     $data['order'] = $packData;
            //     $data['order']['total_free'] = $packData['free'];
            //     $data['order']['userName'] = $this->user['nickName'];
            //     $data['order_type'] = 10;
            //     $data['order']['remark'] = "包裹已封箱完成，请尽快安排发货";
            //       foreach ($clerk as $key => $val){
            //           $data['clerkid'] = $val['user_id'];
            //           Message::send('order.payment',$data);   
            //       }
            // }
            
            //通知用户付款
            $noticesetting = SettingModel::getItem('notice');
            // dump($noticesetting);die;
           //根据设置内容，判断是否需要发送通知；
            $userData = (new UserModel)->where('user_id',$packData['member_id'])->find();
            $packData['userName']=$userData['nickName'];
            $packData['order'] = $packData;
            $packData['order_type'] = 10;
            $packData['remark']= $noticesetting['check']['describe'];
            $packData['total_free'] = $packData['free']+$packData['other_free']+$packData['pack_free'];
            Message::send('order.payment',$packData);
            //发送邮件通知
            if(isset($packData['member_id']) || !empty($packData['member_id'])){
                $EmailUser = UserModel::detail($packData['member_id']);
                $EmailData['code'] = $packData['id'];
                $EmailData['logistics_describe']=$noticesetting['check']['describe'];
                (new Email())->sendemail($EmailUser,$EmailData,$type=1);
            }
            return $this->renderSuccess("包裹已封箱");
         }
    }
    
    //点击切换包裹扫描状态
    public function changeStatus(){
        $express_num = input('express_num');
        $scan = input('scan');
        if(!$express_num){
             return $this->renderError('参数错误');
        }
        $scan = $scan==1?2:1;
        $res =(new Package())->where('express_num',$express_num)->update(['is_scan'=>$scan]);
        return $this->renderSuccess("操作成功");
    }

    // 分拣员 - 分拣上架
    public function checkUpShelf(){
        if (!$this->checkRole(2)){
          return $this->renderError('角色权限非法');
        }
        $id = $this->postData('id')[0];
        $shelf_id = $this->postData('shelf_id')[0];
        $length = $this->postData('length')[0];
        $width = $this->postData('width')[0];
        $height = $this->postData('height')[0];
        $weight = $this->postData('weight')[0];
        $pack_info = (new Package())->field('id,status,express_num,member_id')->find($id);
        if ($pack_info['status']!=2){
            return $this->renderError('包裹状态错误');
        }

        $shelf = (new ShelfUnit())->find($shelf_id);
        if (!$shelf){
            return $this->renderError('货架单元不存在');
        }
        $upShelf = [
          'shelf_unit' => $shelf_id,
          'created_time' => getTime(),
          'express_num' => $pack_info['express_num'],
          'user_id' => $pack_info['member_id'],
          'pack_id' => $id,
        ];
        $res = (new ShelfUnitItem())->post($upShelf);
        if (!$res){
            return $this->renderError('包裹操作失败');
        }
        $update['status'] = 3;
        $update['length'] = $length;
        $update['width'] = $width;
        $update['height'] = $height;
        $update['weight'] = $weight;
        $up = (new Package())->where(['id'=>$id])->update($update);
        Logistics::add($id,'包裹已拣货查验');
        if (!$up){
          return $this->renderError('包裹操作失败');
        }
        return $this->renderSuccess('包裹已入货架');
    }

    public function createdOrderByScan(){
      if (!$this->checkRole(3)){
          return $this->renderError('角色权限非法');
      }
       // 员工信息
      $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      if (!$clerk){
          return $this->renderError('角色权限非法');
      }
      $code = $this->postData('code')[0];
      $package = (new Package())->where(['express_num'=>$code])->find();
      if (!$package){
          return $this->renderError('包裹不存在');
      }
      if ($package['status']!=7){
         return $this->renderError('该包裹未处在已拣货状态');
      }
      $order = [
        'order_sn' => 'P'.createSn(),
        'pack_ids' => $package['id'],
        'num' => 1,
        'opration_name' => $clerk['real_name'],
        'opration_id' => $clerk['user_id'],
      ];
      // 创建预发货单
      $res = (new SendPreOrder())->post($order);
      (new Package())->where(['id'=>$package['id']])->update(['status'=>8]);
      Logistics::add($package['id'],'包裹已打包封箱,等待集中发货');
      $map['storage_id'] = $clerk['shop_id'];
      (new Inpack())->CheckISPack($map); 
      if (!$res){
        return $this->renderError('打包封箱失败');
      }
      return $this->renderSuccess('打包封箱完成');
    }

    // 仓库打包员 封箱
    public function createdOrder(){
        if (!$this->checkRole(3)){
          return $this->renderError('角色权限非法');
        }
         // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
         $id = $this->postData('id')[0];
         if(empty($id)){
            return $this->renderError('包裹封箱参数错误');
         }
         $package = (new Inpack())->find($id);
         if (!$package) 
             return $this->renderError('包裹封箱参数错误');
         $check = (new Inpack())->checkPack($package);
         if (!$check){
             return $this->renderError('包裹状态错误,请检查包裹是否分拣完成');
         }   
         $order = [
           'order_sn' => 'P'.createSn(),
           'pack_ids' => $package['pack_ids'],
           'num' => count(explode(',',$package['pack_ids'])),
           'opration_name' => $clerk['real_name'],
           'opration_id' => $clerk['user_id'],
         ];
         $packIds = explode(',',$package['pack_ids']);
         // 创建预发货单
         $res = (new SendPreOrder())->post($order);
         (new Package())->whereIn('id',$packIds)->update(['status'=>8]);
         (new Inpack())->where('id',$id)->update(['status'=>5]);
         foreach($packIds as $v){
             Logistics::add($v,'包裹已打包封箱,等待集中发货');
         } 
         if (!$res){
           return $this->renderError('打包封箱失败');
         }
         return $this->renderSuccess('打包封箱完成');
    }

    // 上一个 入库单号
    public function lastEnterPack(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $data = (new Package())->where(['storage_id'=>$clerk['shop_id']])->whereIn('status',[2,3,4,5,6,7,8,9,10,11])->field('id,express_num')->order("entering_warehouse_time DESC")->find();
        return $this->renderSuccess($data);
    }
}
