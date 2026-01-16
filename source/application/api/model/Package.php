<?php
namespace app\api\model;
use think\Model;
use app\common\model\Package as PackageModel;
use app\common\model\PackageItem;
use think\Db;
use traits\model\SoftDelete;
use app\store\model\PackageImage;

/**
 * 订单管理
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
       'line_id',
       'source',
       'line_name',
       'height',
       'width',
       'volume',
       'length',
       'weight',
       'address_id',

    ];
    
    // 预报包裹保存
    public function saveData($data){
// dump($data);die;
        $insertData = [];
        foreach ($data as $k => $val){
           if (in_array($k,$this->field)){
               $insertData[$k] = $val;
           }
        }
        $insertData['wxapp_id'] = self::$wxapp_id;
        if (isset($data['id'])){
            $data['updated_time'] = getTime();
            return $this->where(['id'=>$data['id']])->update($data);
        }
        $insertData['updated_time'] = getTime();
        $insertData['created_time'] = getTime();
        return $this->insertGetId($insertData);
    }
    
    // 预约包裹编辑
    public function editData($data){
        unset($data['token']);
        if (isset($data['id'])){
            $data['updated_time'] = getTime();
            return $this->where(['id'=>$data['id']])->update($data);
        }
        return true;
    }
    
    public function remove($id){
        $this->where(['id'=>$id])->update(['is_delete'=>1]);
        (new PackageItem())->where(['order_id'=>$id])->delete();
        return true;
    }
    
    /**
     * 预约取件单
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
    public function getYList($query = [])
    {
        return $this->setListQueryWhere($query)
            ->with(['Member','country','storage','address'])
            ->where('is_delete',0)
            ->order('updated_time DESC')
            ->paginate(10,false,[
                'query'=>\request()->request()
            ]);
    }
    
    /**
     * 订单列表
     * @param 
     * @return bool
     * @throws \think\exception\DbException
     */
    public function getGList($query = [],$order)
    {
        return $this->setGListQueryWhere($query)
            ->alias('pa')
            ->with(['Member','country','storage','address','packageimage.file','shelfunititem.shelfunit.shelf'])
            ->where('pa.is_delete',0)
            ->field('pa.*,u.user_id,u.user_code,u.nickName,u.avatarUrl')
            ->join('user u','u.user_id = pa.member_id','left')
            ->order(['pa.'.$order => 'desc'])
            ->paginate(10,false,[
                'query'=>\request()->request()
            ]);
    }
    
         //查询条件
    private function setGListQueryWhere($param = [])
    {
        // 查询参数
        // dump($param['keyword']);die;
        // !empty($param['is_take'])&& $this->where('pa.is_take','in',$param['is_take']);
        !empty($param['storage_id'])&& $this->where('pa.storage_id','=',$param['storage_id']);
        !empty($param['source'])&& $this->where('pa.source','=',$param['source']);
        !empty($param['status'])&& $this->where('pa.status','in',$param['status']);
        !empty($param['keyword']) && $this->where('pa.express_num|pa.member_id|u.nickName|u.user_code', 'like', '%'.$param['keyword'].'%');
        return $this;
    }

    
        //查询条件
    private function setListQueryWhere($param = [])
    {
        // 查询参数
        !empty($param['is_take'])&& $this->where('is_take','in',$param['is_take']);
        !empty($param['source'])&& $this->where('source','=',$param['source']);
        !empty($param['storage_id'])&& $this->where('storage_id','=',$param['storage_id']);
        !empty($param['status'])&& $this->where('status','in',$param['status']);
        !empty($param['keyword']) && $this->where('express_num|member_id', 'like', '%'.$param['keyword'].'%');
        return $this;
    }
    
    // 查询数据
    public function query($where,$field){
        if(isset($where['status']) && $where['status']==2){
            unset($where['status']);
            $this->whereIn('status',[2,3,4]);
        }
        // dump($where);die;
        if(isset($where['status']) && $where['status']==3){
            unset($where['status']);
            $this->whereIn('status',[5,6,9,10,11]);
        }
        return  $this->where($where)->with(['country','storage','packageimage.file','inpack'])->field($field)->Order('created_time DESC')->paginate(15);
    }
    
    // 查询数据
    public function querysearch($where,$field,$keyword){
        if($where['status']==2){
            unset($where['status']);
            $this->whereIn('status',[2,3,4]);
        }else if($where['status']==3){
            unset($where['status']);
            $this->whereIn('status',[5,6,9,10,11]);
        }
        if(!empty($keyword)){
            return  $this->where($where)->where('express_num','like','%'.$keyword.'%')->with(['country','storage','packageimage.file'])->field($field)->Order('created_time DESC')->paginate(15);
        }
        return  $this->where($where)->with(['country','storage','packageimage.file'])->field($field)->Order('created_time DESC')->select();
    }
        
    // 查询数据
    public function Dbquery($where,$field){
      return $this->setQuery($where)->with(['country','storage'])->field($field)->Order('created_time DESC')->paginate(300);
    }
    
     // 查询数据
    public function Dbquery300($where,$field){
      return $this->setQuery($where)->with(['country','storage'])->field($field)->Order('created_time DESC')->paginate(300);
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
    
    // 统计数据
    public function querycount($where,$status){
        if($status==2){
            $this->whereIn('status',[2,3,4]);
            return  $this->where($where)->Order('created_time DESC')->count();
        }
        if($status==3){
            $this->whereIn('status',[5,6,9,10,11]);
            return  $this->where($where)->Order('created_time DESC')->count();
        }
        return  $this->where($where)->where('status',$status)->Order('created_time DESC')->count();
    }

    public function getDetails($id,$field){
         return $this->with(['country','storage','packageimage.file'])->field($field)->find($id);
    }

    public function country(){
        return $this->belongsTo('Country','country_id');
    }
    
    public function address(){
        return $this->belongsTo('UserAddress','address_id');
    }

    public function storage(){
        return $this->belongsTo('app\api\model\store\\Shop','storage_id')->field('shop_name,shop_id,province_id,city_id,region_id');
    }

    public function Member(){
      return $this->belongsTo('app\api\model\User','member_id')->field('user_id,nickName,avatarUrl,user_code');
    }
    
    public function inpack(){
        return $this->belongsTo('app\api\model\Inpack','inpack_id','id');
    }
    
    public function packitem(){
      return $this->hasMany('app\api\model\PackageItem','order_id');
    }
}
