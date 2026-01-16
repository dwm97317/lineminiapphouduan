<?php
namespace app\web\model;
use app\web\model\Inpack;
use app\web\model\PackagePc;
use app\web\model\PackageItem;
use app\common\model\Package as PackageModel;
use think\Db;

/**
 * 包裹管理
 * Class Order
 * @package app\store\model
 */
class Package extends PackageModel
{

    protected $createTime = null;
    protected $updateTime = null;
    
    public $field = [
       
       'order_sn',
       'member_id',
       'status',
       'storage_id',
       'express_id',
       'express_name',
       'express_num',
       'price',
       'remark',
       'country_id',
       'is_delete',
       'is_take',
       'entering_warehouse_time',
       'real_payment',
       'created_time',
       'updated_time',
       'line_id',
       'line_name',
       'wxapp_id',
       'source',
    ];
   
    // 创建PC预报单
    public function created($data){

        if(!$this->validata($data)){
           return false;
        } 
        // 开启事务
        $this->startTrans();
        try{
            $pack =[
                'order_sn'=> createSn(),
                'member_id' => $data['user_id'],
                'express_num'=>$data['express_num'],
                'created_time' => date("Y-m-d H:i:s",time()),
                'updated_time' => date("Y-m-d H:i:s",time()),
                'wxapp_id' =>$data['wxapp_id'],
                'remark' => $data['remark'],
                'express_id' =>$data['express_id'],
                'storage_id'=>isset($data['shop_id'])?$data['shop_id']:0,
                'source' => 5,
                'is_take' => 2,
                'country_id'=>isset($data['country_id'])?$data['country_id']:0,
                'price' => isset($data['price'])?$data['price']:0,
            ]; 

            $id = $this->insertGetId($pack);

        }catch(\Exception $e){
           $this->rollback(); 
           dump($e); die;
           return false;
        }
        $this->commit();
        return $id;
    }
    
    public function edit($data,$id){
        // 开启事务
   
        $this->startTrans();
        try{
           $array = [
            'country_id' => $data['country_id'],
            'storage_id' => $data['shop_id'],
            'express_num' => $data['express_num'],
            'express_id' => $data['express_id'],
            'price' => $data['price'],
            'remark' => $data['remark'],
            'source' => 5,
           ]; 
            
          $result = $this->where('id',$id)->update($array); 
        }catch(\Exception $e){
           $this->rollback(); 
           dump($e); die;
           return false;
        }
        $this->commit();
        return true;
    }
    

    public function validata($data){
        // 必填字段
        if(empty($data['express_num'])){
             $this->error = '快递单号不能为空';
             return false;
        }
        return true;
    }

    // 查询数据
    public function query($where,$field){
        return $this->setindexListQueryWhere($where)
            ->with(['country','storage','categoryAttr','express','address'])
            ->where('is_delete',0)
            ->field($field)->Order('created_time DESC')
            ->paginate(10, false, [
                'query' => \request()->request()
            ]);
    }
    
        //查询条件
    private function setindexListQueryWhere($param = [])
    {
        // 查询参数
        empty($param['is_delete']) && $this->where('is_delete','=',0);
        !empty($param['class_id'])&& $this->where('class_id','in',$param['class_id']);
        !empty($param['is_take'])&& $this->where('is_take','in',$param['is_take']);
        !empty($param['source'])&& $this->where('source','=',$param['source']);
        !empty($param['is_delete'])&& $this->where('a.is_delete','=',$param['is_delete']);
        
        !empty($param['extract_shop_id'])&&is_numeric($param['extract_shop_id']) && $param['extract_shop_id'] > -1 && $this->where('storage_id', '=', (int)$param['extract_shop_id']);
        !empty($param['start_time']) && $this->where('created_time', '>', $param['start_time']);
        !empty($param['end_time']) && $this->where('created_time', '<', $param['end_time']." 23:59:59");
        !empty($param['member_id']) && $this->where('member_id',$param['member_id']);
        !empty($param['number']) && $this->where('express_num','like','%'.$param['number'].'%');
        if(!empty($param['express_num'])){
            $express_num = str_replace("\r\n","\n",trim($param['express_num']));
            $express_num = explode("\n",$express_num);
            $express_num = implode(',',$express_num);
            $where['express_num'] = array('in', $express_num);
            $this->where($where);
        }
       
        !empty($param['search']) && $this->where('a.member_id|u.nickName|u.user_code','like','%'.$param['search'].'%');
        return $this;
    }
        
    // 查询数据
    public function Dbquery($where,$field){
      return $this->setQuery($where)->with(['country','storage','categoryAttr','express'])->field($field)->Order('created_time DESC')->paginate(15);
    }

    // 查询数据
    public function DbqueryNoWith($where,$field){
      return $this->setQuery($where)->with(['country','storage'])->field($field)->paginate(15);
    }
    
    // 设置查询条件
    public function setQuery($where){
        foreach ($where as $v){
            $this->where($v[0],$v[1],$v[2]);
        } 
        return $this; 
    }

    public function getDetails($id,$field){
         return $this->with(['country','storage','categoryAttr','express'])->field($field)->find($id);
    }

    public function country(){
        return $this->belongsTo('Country','country_id');
    }

    public function storage(){
        return $this->belongsTo('app\web\model\store\\Shop','storage_id')->field('shop_name,shop_id,province_id,city_id,region_id');
    }

    public function Member(){
      return $this->belongsTo('app\web\model\User','member_id')->field('user_id,nickName,avatarUrl');
    }
    
    public function address(){
      return $this->belongsTo('app\web\model\UserAddress','member_id')->field('address_id,country,region,name,province');
    }
 
    public function categoryAttr(){
        return $this->hasMany('app\web\model\PackageItem','order_id');;
    }

    public function express(){
        return $this->belongsTo('app\web\model\Express','express_id');
    }
    
    /**
     * 处理状态
     * @param $value
     * @return mixed
     */
    public function getStatusAttr($value)
    {
        $statusvalue = [1=>'未入库',2=>'已入库',3=>'已拣货上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成','-1'=>'问题件'];
        return $statusvalue[$value];
    }
    

}
