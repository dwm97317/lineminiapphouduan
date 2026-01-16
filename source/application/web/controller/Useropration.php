<?php
namespace app\web\controller;

use app\web\model\Inpack;
use app\web\model\Line;
use app\web\model\Logistics;
use app\web\model\Package;
use app\web\model\ShelfUnit;
use app\web\model\ShelfUnitItem;
use app\web\model\PackageItem as PackageItemModel;
use app\web\model\SendPreOrder;
use app\web\model\store\shop\Clerk;
use app\web\model\User as UserModel;
use app\web\model\UserAddress;

/**
 * 用户管理
 * Class User
 * @package app\web
 */
class Useropration extends Controller
{
    
    /* @var \app\web\model\User $user */
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
          case 4:
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
            2 => 4,
            1 => 5
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
         'no' => $packModel ->where(['status'=>4,'storage_id'=>$clerk['shop_id']])->count(),
         'in' => $packModel -> where(['status'=>5,'storage_id'=>$clerk['shop_id']])->count()
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
          $pack =  (new Package())->whereIn('id',$item)->with(['country'])->select();
          $pack = $this->getPackItemList($pack);
          $data['item'] = $pack;
          return $this->renderSuccess($data);
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
        $shelfUnit['shelf_unit_code'] = (new ShelfUnit())->getShelfUnit($shelfUnit['shelf_id']);
        if (!$shelfUnit){
          return $this->renderError('为查询到货架');
        }
        return $this->renderSuccess($shelfUnit);
    }

    // 检查用户角色权限
    public function checkRole($role){
        $currentUserRole = $this->userRole;
        if ($currentUserRole['role_type']==5){
            $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
            $clerk_type = explode(',',$clerk['clerk_type']);
            if (in_array($role,$clerk_type)){
                return true; 
            }else{
                return false;
            }
        }
        if ($currentUserRole['role_type'] == $role){
            return true; 
        }
        return false;
    }
     
    // 仓管员 - 入库校验
    public function checkPack(){
        if (!$this->checkRole(1)){
            return $this->renderError('角色权限非法');
        }
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
           return $this->renderError('角色权限非法');
        }
        $post = $this->postData('code')[0];
        $map[] = ['status','=',1];
        $map[] = ['is_delete','=',0];
        $map[] = ['storage_id','=',$clerk['shop_id']];
        $map[] = ['is_take','=',2];
        $map[] = ['express_num','=',$post]; 
        $res = (new Package())->setQuery($map)->field('id,express_num,order_sn,member_id,storage_id,status')->with('storage')->find();
        if (!$res){
          return $this->renderError('该包裹不存在');
        }
        if ($res['status']!=1){
           return $this->renderError('包裹入库失败');
        }
     
        return $this->renderSuccess($res);
    }
 
    // 提交入库
    public function instorage() {
        if (!$this->checkRole(1)){
            return $this->renderError('角色权限非法');
        }
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
           return $this->renderError('角色权限非法');
        }
       $id = $this->postData('id')[0];
       $image = $this->postData('image')[0];
       $user_id = $this->postData('user_id')[0];
       $is_pre = $this->postData('is_pre')[0];
       $express_num = $this->postData('express_num')[0];
       if ($is_pre==false){
            if ($user_id){
                $user = (new UserModel())->find($user_id);
                if (!$user)
                    return $this->renderError('用户不存在');
            }
            $res = (new Package())->where(['express_num'=>$express_num])->find();
            if ($res){
                return $this->renderError('该包裹已在库存中'); 
            }
            $order['status'] = 2;
            $order['is_take'] = 1;
            $order['storage_id'] = $clerk['shop_id'];
            if ($user_id){
                $order['member_id'] = $user_id;
                $order['is_take'] = 2;
            }
            $order['order_sn'] = createSn();
            $order['image'] = $image;
            $order['express_num'] = $express_num;
            $order['updated_time'] = getTime();
            $order['created_time'] = getTime();
            $order['entering_warehouse_time'] = getTime();
            $res = (new Package())->saveData($order);
            if (!$res){
                 return $this->renderError('包裹入库失败');
            }
            return $this->renderSuccess('包裹入库成功');
       }
       $data = (new Package())->find($id);
       if ($data['status']==2){
         return $this->renderError('包裹已入库');
       }
       $update['status'] = 2;
       $update['image'] = $image;
       $update['updated_time'] = getTime();
       $update['entering_warehouse_time'] = getTime();
       $res = (new Package())->where(['id'=>$id])->update($update);
       Logistics::add($id,'包裹已到达仓库');
       if (!$res){
        return $this->renderError('包裹入库失败');
       }
       $data = array_merge($data->toArray(),$update);
       (new Package())->sendEnterMessage([$data]);
       return $this->renderSuccess('包裹入库成功');
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
         'no' => $packModel ->where(['status'=>1,'storage_id'=>$clerk['shop_id']])->count(),
         'in' => $packModel -> where(['status'=>2,'storage_id'=>$clerk['shop_id']])->count()
      ];
      return $this->renderSuccess($res);
    }

    // 仓库管理员 包裹列表 
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
        $keyword = $this->postData("keyword")[0];
        $map = ['status'=>$status];
        $map['storage_id'] = $clerk['shop_id'];
        $map['is_delete'] = 0;
   
        $packModel = (new Package());
        if ($keyword){
            $packModel ->where(function($query)use($keyword){
                $query->where('express_num','like','%'.$keyword."%")->whereOr('member_id','=',$keyword);
            });
        }
        $data = $packModel ->where($map)->with('member')->order('created_time DESC')->paginate(15);
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
          $data = $packModel ->with(['member','country'])->find($id);
          // 获取物品详情
          $packItem = (new PackageItemModel())->where(['order_id'=>$data['id']])->field('class_name,id,class_id,order_id')->select();
          $data['shop'] = '';
          if ($packItem){
              $data['shop'] = implode(',',array_column($packItem->toArray(),'class_name'));
          }
          return $this->renderSuccess($data);
    }

    // 拣货清单
    public function loadingpack(){
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $status = $this->postData('status')[0]?$this->postData('status')[0]:6;
        $keyword = $this->postData('keyword')[0];
        $map['status'] = $status;
        $map['is_delete'] = 0;
        $map['storage_id'] = $clerk['shop_id'];
        $map['is_take'] = 2;
        $packModel = (new Package());
        if ($keyword){
            $packModel ->where(function($query)use($keyword){
                $query->where('express_num','like','%'.$keyword."%")->whereOr('member_id','=',$keyword);
            });
        }
        $data = $packModel->where($map)->with('member')->order('updated_time DESC')->paginate(15);
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
        $packModel = (new Package());
        $res = [
           'no' => $packModel ->where(['status'=>6,'storage_id'=>$clerk['shop_id']])->count(),
           'in' => $packModel -> where(['status'=>7,'storage_id'=>$clerk['shop_id']])->count()
        ];
        return $this->renderSuccess($res);
    }

    /**
     *包裹转移 
     */
    public function packageMove(){
        $id = $this->postData('id')[0];
        $shelf_unit_id = $this->postData('shelf_id')[0];
        if (!$this->checkRole(2)){
            return $this->renderError('角色权限非法');
        }
        // 员工信息
        $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
        if (!$clerk){
            return $this->renderError('角色权限非法');
        }
        $pack_info = (new Package())->field('id,status')->find($id);
        if ($pack_info['status']<3 || $pack_info['status']>6){
            return $this->renderError('包裹状态错误');
        }
        $shelfUnitItem =  (new ShelfUnitItem())->where(['pack_id'=>$id])->find();
        if (!$shelfUnitItem){
            return $this->renderError('货位数据错误');
        }
        if ($shelfUnitItem['shelf_unit_id'] == $shelf_unit_id){
            return $this->renderError('无效转移');
        }
        $shelf = (new ShelfUnit())->find($shelf_unit_id);
        if (!$shelf){
            return $this->renderError('货架单元不存在');
        }
        (new ShelfUnitItem())->where(['pack_id'=>$pack_info['id']])->delete();
        $upShelf = [
          'shelf_unit' => $shelf_unit_id,
          'pack_id' => $id,
        ];
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
      $shelfUnitItem = (new ShelfUnitItem())->where(['pack_id'=>$id])->find();
      // 下架 
      $res = (new ShelfUnitItem())->where(['shelf_unit_item_id'=>$shelfUnitItem['shelf_unit_item_id']])->delete();
      if (!$res){
          return $this->renderError('包裹操作失败');
      }
      $update['status'] = 7;
      $up = (new Package())->where(['id'=>$id])->update($update);
      if (!$up){
        return $this->renderError('包裹操作失败');
      }
      Logistics::add($id,'包裹已拣货下架完毕,等待打包员进行打包');
      $map['storage_id'] = $clerk['shop_id'];
      (new Inpack())->CheckISShelf($map); 
      
      return $this->renderSuccess('包裹从货架拣货');
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
        $packModel = (new Package());
        $id = $this->postData('id')?$this->postData('id')[0]:1;
        $data = $packModel ->with(['member','country'])->find($id);
        // 查询货架货位数据
        $data['shelf'] = (new ShelfUnitItem())->getShelfUnitByPackId($id);
        
        // 获取物品详情
        $packItem = (new PackageItemModel())->where(['order_id'=>$data['id']])->field('class_name,id,class_id,order_id')->select();

        $data['shop'] = '';
        if ($packItem){
            $data['shop'] = implode(',',array_column($packItem->toArray(),'class_name'));
        }
        if ($data['address_id']){
            $data['address'] = (new UserAddress())->find($data['address_id']);
        }
        if ($data['line_id']){
            $data['line'] = (new Line())->field('name')->find($data['line_id']);
        }
        return $this->renderSuccess($data);
    }

    // 分拣员 - 查询包裹
    public function checkPackInStorage(){
      // 员工信息
      $clerk = (new Clerk())->where(['user_id'=>$this->user['user_id'],'is_delete'=>0])->find();
      if (!$clerk){
          return $this->renderError('角色权限非法');
      }
      $code = $this->postData('code')[0];
      $map[] = ['status','in',[2,6,7]];
      $map[] = ['is_delete','=',0];
      $map[] = ['storage_id','=',$clerk['shop_id']];
      $map[] = ['is_take','=',2];
      $map[] = ['express_num','=',$code]; 
      $res = (new Package())->setQuery($map)->with('storage')->find();
      if (!$res){
        return $this->renderError('包裹未查询到');
      }
      return $this->renderSuccess($res);
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
