<?php
namespace app\store\model\sharing;
use app\common\model\sharing\Setting as SettingModel;
use think\Cache;
class Setting extends SettingModel {
      protected $createTime = false;
      /**
     * 设置项描述
     * @var array
     */
    private $describe = [
        'sharp' => '拼团设置',
    ];

    
    /**
     * 更新系统设置
     * @param $data
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function edit($data)
    {
        $this->startTrans();
        try {
            $this->saveValues("sharp",$data);
            $this->commit();
            // 删除系统设置缓存
            Cache::rm('sharp_setting_' . self::$wxapp_id);
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 保存设置项
     * @param $key
     * @param $values
     * @return false|int
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    private function saveValues($key, $values)
    {   
        $model = $this;
        if ($detail = $this->where(['key'=>'sharp'])->find()){
            $model = $detail;
        }
        return $model->save([
            'key' => $key,
            'describe' => $this->describe[$key],
            'values' => $values,
            'wxapp_id' => self::$wxapp_id,
        ]);
    }

    /**
     * 数据验证
     * @param $key
     * @param $values
     * @return bool
     */
    private function validValues($key, $values)
    {
//        if ($key === 'condition') {
//            // 验证分销商条件
//            return $this->validCondition($values);
//        }
        return true;
    }

    /**
     * 验证结算方式
     * @param $values
     * @return bool
     */
    private function validSettlement($values)
    {
       
        return true;
    }
}  

?>