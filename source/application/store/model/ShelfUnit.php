<?php
namespace app\store\model;
use think\Model;
use app\common\model\ShelfUnit as ShelfUnitModel;
use app\common\service\QrcodeService;
/**
 * 线路模型
 * Class Delivery
 * @package app\common\model
 */
class ShelfUnit extends ShelfUnitModel
{
    public function getList($query){
        return $this->setListQueryWhere($query)
        ->alias('a')
        ->paginate(30,false,[
            'query'=>\request()->request()
        ]);
    }

    public function setListQueryWhere($query){
        return $this->where(['shelf_id'=>$query]);
    }
    
    public function setQueryWhere($query){
        !empty($query['shelf_ids']) && $this->where('shelf_id','in',$query['shelf_ids']);
        !empty($query['shelf_id']) && $this->where('shelf_id','=',$query['shelf_id']);
        !empty($query['shelf_unit_id']) && $this->where('shelf_unit_id','=',$query['shelf_unit_id']);
        !empty($query['search']) && $this->where('shelf_unit_no|shelf_unit_id','like','%'.$query['search'].'%');
        return $this;
    }
    
    public function add($data){
       // 表单验证
       if (!$this->onValidate($data)) return false;
       // 保存数据
       $data['wxapp_id'] = self::$wxapp_id;
       if ($this->allowField(true)->save($data)) {
           return true;
       }
       return false;
    }

    
    public function details($id){
       return $this->find($id);
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function edit($data)
    {
        // 表单验证
        if (!$this->onValidate($data)) return false;
        // 保存数据
        if ($this->allowField(true)->save($data)) {
        }
        return false;
    }
    
    // 获取货位数据
    public function getWithShelf($query){
        return $this->setQueryWhere($query)
        ->with('shelf')
        ->alias('a')
        ->paginate(30,false,[
            'query'=>\request()->request()
        ]);
    }
    
    // 重新生成二维码
    public function resetCode($ids){
        $list = $this->where('shelf_unit_id','in',$ids)->select();
        $qrcodeService = (new QrcodeService());
        foreach ($list as $v){
        
            $update['shelf_unit_qrcode'] = $qrcodeService->create($v['shelf_unit_code']);
            $this->where(['shelf_unit_id'=>$v['shelf_unit_id']])->update($update);
        } 
        return true;
    }
    
    public function onValidate($data){
      if (!isset($data['shelf_name']) || empty($data['shelf_name'])) {
         $this->error = '请输入货架名称';
         return false;
      }
      if (!isset($data['shelf_no']) || empty($data['shelf_no'])) {
        $this->error = '请输入货架编号';
        return false;
      }
      $res = $this->where(['shelf_no'=>$data['shelf_no']])->find();
      if ($res){
          $this->error = '该货架编号已存在';
          return false;
      }
      return true;
    }
    
    // 删除货位存储
    public function shelfUnitDataLog($content,$shelf_unit_id){
        $_log = './storage/shelf/';
        if (!is_dir($_log)){
            mkdir($_log,0777);
        }
        $_log_file = $_log.'/'.$shelf_unit_id;
        if (!file_exists($_log_file)){
            file_put_contents($_log_file,$content);
            return true;
        }
        $old = file_get_contents($_log_file);
        file_put_contents($_log_file,$old."\r\n".$content);
        return true;
    }

    // 生成货位数据
    public function getShelfUnitData($data,$shelf){
        $shelf_unit = [];
          
        $allrang = $data['shelf_row']*$data['shelf_column'];
        $qrcodeService = (new QrcodeService());
        for ($i = 1; $i <= $allrang ; $i++){
            //   dump(createQrcodeCode($data['shelf_no'].'S'.$i));die;
                $shelf_unit[$i]['shelf_unit_no'] = 'S'.$i;
                $shelf_unit[$i]['shelf_unit_code'] = $data['shelf_no'].'S'.$i;
                $shelf_unit[$i]['shelf_unit_qrcode'] = $qrcodeService->create($data['shelf_no'].'S'.$i);
                $shelf_unit[$i]['shelf_id'] = $shelf;
                $shelf_unit[$i]['shelf_unit_floor'] = $i; //层数
                $shelf_unit[$i]['wxapp_id'] = self::$wxapp_id;
        } 
        return $shelf_unit;
    }    
    
    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->delete();
    }
    
     // 关联货架
    public function Shelf(){
      return $this->belongsTo('app\api\model\Shelf','shelf_id')->field('id,shelf_no,shelf_name'); 
    }
    
    // 根据货架移除货位
    public function remove($shelf_id){
        $shelf_unit = $this->where(['shelf_id'=>$shelf_id])->select();
        if ($shelf_unit->isEmpty()){
            return true;
        }
        $shelfunititem = new ShelfUnitItem();
        $_count = 0;
        foreach ($shelf_unit as $v){
            // 查询货位是否存在数据
            $_map = ['shelf_unit_id'=>$v['shelf_unit_id']];
            $res = $shelfunititem->where($_map)->select();
            if (!$res->isEmpty()){
                 $content = var_export($res,true);
                 $this->shelfUnitDataLog($content,$v['shelf_unit_id']);
            }
            $del = $shelfunititem->where($_map)->delete();
            if ($del)
                $_count++;
        }
        return $_count==count($shelf_unit)?true:false;
    }
}
