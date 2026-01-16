<?php
namespace app\common\model;
use app\store\model\Inpack;
/**
 * 包裹日志模型
 * Class OrderAddress
 * @package app\common\model
 */
class Logistics extends BaseModel
{
    protected $name = 'logistics';
    protected $updateTime = false;

    // 状态映射  状态 1 待查验 2 待支付 3 待发货 4 拣货中 5 已打包  6已发货 7 已到货 8 已完成  9已取消
    public $map = [
     -1=>'问题件', 1=>'未入库',2=>'已入库',3=>'已拣货上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成'
    ];
    
    public $maps = [1=>'问题件', 1=>'待查验',2=>'待支付',3=>'待发货',4=>'拣货中',5=>'已打包',6=>'已发货',7=>'已到货',8=>'已完成',9=>'已取消'];
    
    public static function addLog($id,$desc,$creatime){
        $id = (new Inpack())->where('order_sn',$id)->find();
        $model = new static;
        return $model->insert([
            'order_sn' => $id['order_sn'],
            // 'express_num' => $id['t_order_sn'],
            'status' => $id['status'],
            'status_cn' => $model->maps[$id['status']],
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'logistics_sn'=> $id['t_order_sn'],
            'created_time' => $creatime,
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$id['wxapp_id'],
        ]);
    }
    
    public static function add($id,$desc){

        $id = (new Package())->find($id);
     
        $model = new static;
        return $model->insert([
            'order_sn' => $id['order_sn'],
            'express_num' => $id['express_num'],
            'status' => $id['status'],
            'status_cn' => $model->map[$id['status']],
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'created_time' => getTime(),
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$id['wxapp_id'],
        ]);
    }
    //用户提交打包的日志
    public static function addLogPack($id,$order_sn,$desc){
        $ids = (new Package())->find($id);
 
        $model = new static;
        return $model->insert([
            'order_sn' => $order_sn,
            'express_num' => $ids['express_num'],
            'status' => $ids['status'],
            'status_cn' => $model->map[$ids['status']],
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'created_time' => getTime(),
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$ids['wxapp_id'],
        ]);
    }
    
    public static function addInpackLogsPlus($id,$desc,$time){
    
    $Inpack = (new Inpack())->where('order_sn',$id)->find();
    $model = new static;
    return $model->insert([
        'order_sn' => $Inpack['order_sn'],
        // 'express_num' => $Inpack['t_order_sn'],
        'status' => $Inpack['status'],
        'status_cn' => $model->maps[$Inpack['status']],
        'logistics_describe' => $desc?$desc:'包裹状态更新',
        'logistics_sn'=> '',
        'created_time' => $time,
        'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$Inpack['wxapp_id'],
    ]);
    }
      
     public static function addInpackLogs($id,$desc){
        
        $Inpack = (new Inpack())->where('order_sn',$id)->find();
        $model = new static;
        return $model->insert([
            'order_sn' => $Inpack['order_sn'],
            // 'express_num' => $Inpack['t_order_sn'],
            'status' => $Inpack['status'],
            'status_cn' => $model->maps[$Inpack['status']],
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'logistics_sn'=> '',
            'created_time' => getTime(),
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$Inpack['wxapp_id'],
        ]);
    }
    
    public static function addbatchLogs($id,$status_cn,$desc){
        
        $Inpack = (new Inpack())->where('order_sn',$id)->find();
        $model = new static;
        return $model->insert([
            'order_sn' => $Inpack['order_sn'],
            // 'express_num' => $Inpack['t_order_sn'],
            'status' => $Inpack['status'],
            'status_cn' => $status_cn,
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'logistics_sn'=> '',
            'created_time' => getTime(),
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$Inpack['wxapp_id'],
        ]);
    }
    
     public static function addInpackLog($id,$desc,$t_order_sn){
        
        $Inpack = (new Inpack())->where('order_sn',$id)->find();
        $model = new static;
        return $model->insert([
            'order_sn' => $Inpack['order_sn'],
            // 'express_num' => $Inpack['t_order_sn'],
            'status' => $Inpack['status'],
            'status_cn' => $model->maps[$Inpack['status']],
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'logistics_sn'=> $t_order_sn,
            'created_time' => getTime(),
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$Inpack['wxapp_id'],
        ]);
    }
      //集运单到货的日志
      public static function addInpackGetLog($id,$desc,$t_order_sn){
        
        $Inpack = (new Inpack())->where('id',$id)->find();
        $model = new static;
        return $model->insert([
            'order_sn' => $Inpack['order_sn'],
            // 'express_num' => $Inpack['t_order_sn'],
            'status' => $Inpack['status'],
            'status_cn' => $model->maps[$Inpack['status']],
            'logistics_describe' => $desc?$desc:'包裹状态更新',
            'logistics_sn'=> $t_order_sn,
            'created_time' => getTime(),
            'wxapp_id'=>self::$wxapp_id?self::$wxapp_id:$Inpack['wxapp_id'],
        ]);
    }
    
     public static function updateOrderSn($packnum,$order_sn){
        return (new Logistics())->where('express_num',$packnum)->update(["order_sn" =>$order_sn ]);
    }
    
    /**
     * 删除记录
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        return $this->delete();
    }
    
    public function details($id){
        return $this->find($id);
    }
}
