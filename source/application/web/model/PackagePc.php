<?php
namespace app\web\model;
use think\Model;
use app\common\model\PackagePc as PackagePcModel;
use think\Db;
use traits\model\SoftDelete;

/**
 * 包裹PC模型
 * Class Order
 * @package app\store\model
 */
class PackagePc extends PackagePcModel
{

    protected $createTime = null;
    protected $updateTime = null;
    
    // 查询数据
    public function query($where,$field){
        return $this->where($where)->with(['country','storage'])->field($field)->Order('created_time DESC')->paginate(15);
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

    public function Member(){
      return $this->belongsTo('app\web\model\User','member_id')->field('user_id,nickName,avatarUrl');
    }
}
