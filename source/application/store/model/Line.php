<?php
namespace app\store\model;
use think\Model;
use app\common\model\Line as LineModel;
/**
 * 线路模型
 * Class Delivery
 * @package app\common\model
 */
class Line extends LineModel
{
    /**
     * 添加新记录
     * @param $data
     * @return bool|int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function add($data)
    {
        // 表单验证

        if (!$this->onValidate($data)) return false;
        $rule = $this->parseRuleData($data);
 
        $data['free_rule'] = json_encode($rule);
        // 保存数据
        $data['wxapp_id'] = self::$wxapp_id;
        $data['created_time'] = time();

        unset($data['weight']);
        unset($data['weight_price']);
        if ($this->allowField(true)->save($data)) {
            return true;
        }
        return false;
    }

    // 格式化计费规则
    public function parseRuleData($data){
                 
    
       $ruleData = [];
       if ($data['free_mode']==1){
           foreach($data['weight_start'] as $k => $v){
               $spilt_weight[0] = $v;
               $spilt_weight[1] = $data['weight_max'][$k];
               $ruleData[] = [
                  'weight' => $spilt_weight,
                  'weight_price' => $data['weight_price'][$k],
               ];
           }
       }
       
       if ($data['free_mode']==2){
               $ruleData[] = [
                  'first_weight' =>$data['first_weight'],
                  'first_price' => $data['first_price'],
                  'next_weight' =>$data['next_weight'],
                  'next_price' => $data['next_price'],
               ];
       }
       
       if ($data['free_mode']==3){
           foreach($data['weight_start'] as $k => $v){
               if(!empty($v)){
                   $spilt_weight[0] = $v;
                   $spilt_weight[1] = $data['weight_max'][$k];
                   $ruleData[] = [
                      'weight' => $spilt_weight,
                      'weight_price' => $data['weight_price'][$k],
                   ];
               }
           }
       }
       
       if ($data['free_mode']==4){

           foreach($data['weight_start'] as $k => $v){
               if(!empty($v)){
                   $spilt_weight[0] = $v;
                   $spilt_weight[1] = $data['weight_max'][$k];
                   $ruleData[] = [
                      'weight' => $spilt_weight,
                      'weight_price' => $data['weight_price'][$k],
                      'weight_unit' => $data['weight_unit'][$k],
                   ];
               }
               
               
           }
       }
       return $ruleData;
    }
    
    public function getList($query){
      !empty($query) && $this->setListQueryWhere($query);
      return $this
        ->with('image')
        ->order('created_time','desc')
        ->paginate(10,false,[
            'query'=>\request()->request()
        ]);
    }
    
    public function getListAll(){
      return $this
        ->order('created_time','desc')
        ->select();
    }

    public function setListQueryWhere($query){
        if(!empty($query['name'])){
            $this->where('name','like','%'.$query['name'].'%');
        }
        return $this;
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
        $rule = $this->parseRuleData($data);
        
        $data['free_rule'] = json_encode($rule);
           
        // 保存数据
        $data['wxapp_id'] = self::$wxapp_id;
        if($data['countrys']=='' || $data['countrys']==null){
             unset($data['countrys']);
        }
        unset($data['weight']);
        unset($data['weight_price']);
        // 保存数据
        if ($this->allowField(true)->save($data)) {
             return true;
        }
        return false;
    }

    /**
     * 表单验证
     * @param $data
     * @return bool
     */
    private function onValidate($data)
    {
        if (!isset($data['name']) || empty($data['name'])) {
            $this->error = '请输入线路名称';
            return false;
        }
        if ($data['free_mode'] == 1){
            if (empty($data['weight_min'])){
               $this->error = '请完善计费规则';
               return false;
            } 
        }
        if ($data['free_mode'] == 2){
            if (empty($data['first_weight'])){
               $this->error = '请填写首重';
               return false;
            } 
            if (empty($data['first_price'])){
               $this->error = '请填写首重价格';
               return false;
            }
            if (empty($data['next_weight'])){
               $this->error = '请填写续重';
               return false;
            }
            if (empty($data['next_price'])){
               $this->error = '请填写续重价格';
               return false;
            }
        }
        if ($data['free_mode'] == 3){
            if (empty($data['weight_min'])){
               $this->error = '请完善计费规则';
               return false;
            } 
        }
        
        if ($data['free_mode'] == 4){
            if (empty($data['weight_min'])){
               $this->error = '请完善计费规则';
               return false;
            } 
        }
        return true;
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
}
