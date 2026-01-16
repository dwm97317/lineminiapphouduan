<?php
namespace app\web\model;
use think\Model;
use app\common\model\SendOrder as SendOrderModel;
use think\Db;
use traits\model\SoftDelete;

/**
 * 订单管理
 * Class Order
 * @package app\store\model
 */
class SendOrder extends SendOrderModel
{

    protected $createTime = null;
    protected $updateTime = null;
    
    public $field = [
       'order_sn',
       'pack_ids',
    ];

    // 发货单创建
    public function post($data){
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
        $insertData['created_time'] = getTime();
        $insertData['updated_time'] = getTime();
        return $this->insertGetId($insertData);
    }
    
    // 查询数据
    public function query($where,$field){
        return $this->where($where)->with(['country','storage'])->field($field)->paginate(15);
    }
        
    // 查询数据
    public function Dbquery($where,$field){
      return $this->setQuery($where)->with(['country','storage'])->field($field)->Order('created_time DESC')->paginate(15);
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
         return $this->with(['country','storage'])->field($field)->find($id);
    }

    public function country(){
        return $this->belongsTo('Country','country_id');
    }

    public function storage(){
        return $this->belongsTo('app\web\model\store\\Shop','storage_id')->field('shop_name,shop_id,province_id,city_id,region_id');
    }
}
