<?php

namespace app\store\model;

use think\Cache;
use app\common\model\Setting as SettingModel;
use app\common\enum\Setting as SettingEnum;

/**
 * 系统设置模型
 * Class Wxapp
 * @package app\store\model
 */
class Setting extends SettingModel
{
    /**
     * 更新系统设置
     * @param $key
     * @param $values
     * @return bool
     * @throws \think\exception\DbException
     */
    public function edit($key, $values)
    {
        // dump($values);die;
        $model = self::detail($key) ?: $this;
        // 数据验证
        if (!$this->validValues($key, $values)) {
            return false;
        }
        if ($key=='service'){
            $values = $this->getServiceData($values['service']); 
        }
      
        if(isset($values['weight_mode'])){
            switch ($values['weight_mode']['mode']) {
                case '10':
                    $values['weight_mode']['unit'] = 'g';
                    $values['weight_mode']['unit_name'] = '克';
                    break;
                case '20':
                    $values['weight_mode']['unit'] = 'kg';
                    $values['weight_mode']['unit_name'] = '千克';
                    break;
                case '30':
                    $values['weight_mode']['unit'] = 'lb';
                    $values['weight_mode']['unit_name'] = '磅';
                    break;
                default:
                    $values['weight_mode']['unit'] = 'g';
                    $values['weight_mode']['unit_name'] = '克';
                    break;
            }
        }
        
        if(isset($values['price_mode'])){
            switch ($values['price_mode']['mode']) {
                case '10':
                    $values['price_mode']['unit'] = '¥';
                    $values['price_mode']['unit_name'] = '元';
                    break;
                case '20':
                    $values['price_mode']['unit'] = '$';
                    $values['price_mode']['unit_name'] = '美元';
                    break;
                case '30':
                    $values['price_mode']['unit'] = 'C$';
                    $values['price_mode']['unit_name'] = '加币';
                    break;
               case '40':
                    $values['price_mode']['unit'] = '€';
                    $values['price_mode']['unit_name'] = '欧元';
                    break;
                case '50':
                    $values['price_mode']['unit'] = 'A$';
                    $values['price_mode']['unit_name'] = '澳元';
                    break;
                case '60':
                    $values['price_mode']['unit'] = 'HK$';
                    $values['price_mode']['unit_name'] = '港币';
                    break;
                default:
                    $values['price_mode']['unit'] = '¥';
                    $values['price_mode']['unit_name'] = '元';
                    break;
            }
        }
        
        if(isset($values['size_mode'])){
            switch ($values['size_mode']['mode']) {
                case '10':
                    $values['size_mode']['unit'] = 'CM';
                    $values['size_mode']['unit_name'] = '厘米';
                    break;
                case '20':
                    $values['size_mode']['unit'] = 'IN';
                    $values['size_mode']['unit_name'] = '英寸';
                    break;
                default:
                    $values['size_mode']['unit'] = 'CM';
                    $values['size_mode']['unit_name'] = '厘米';
                    break;
            }
        }
        
        if($key=='store'){
            $values['orderno']['default'] = explode(',',$values['orderno']['default']);
            if(count($values['orderno']['default'])<2){
                $this->error = '生成规则至少选择两个';
                return false; 
            }
        }
        
        if($key=='userclient'){
            $values = $this->setVlaue($values); 
        }
        if($key=='keeper'){
            $values = $this->setVlaue($values); 
        }
        if($key=='paytype'){
            $values = $this->setpayTypeVlaue($values); 
        }
        
        // 删除系统设置缓存
        Cache::rm('setting_' . self::$wxapp_id);
        return $model->save([
                'key' => $key,
                'describe' => SettingEnum::data()[$key]['describe'],
                'values' => $values,
                'wxapp_id' => self::$wxapp_id,
            ]) !== false;
    }
    
    private function setpayTypeVlaue($values){
        $array1 = ['MP-WEIXIN','H5-WEIXIN','H5','APP','WEB'];
        $paytype = ['wechat','balance','Hantepay','omipay'];
        foreach ($paytype as $pay){
            $array2 = [];
            // dump($values[$pay]);die;
            foreach ($values[$pay]['platfrom'] as $key=>$val){
                array_unshift($array2,$key);
            }
            $arr = array_diff($array1,$array2);
            foreach ($arr as $v){
             $values[$pay]['platfrom'][$v] = '0';
            }
        }
        return $values;        
    }
    
    
    private function setVlaue($values){
        if(!isset($values['yubao']['is_country_force'])){
            $values['yubao']['is_country_force'] = '0';
        }
        if(!isset($values['yubao']['is_shop_force'])){
            $values['yubao']['is_shop_force'] = '0';
        }
        if(!isset($values['yubao']['is_expressname_force'])){
            $values['yubao']['is_expressname_force'] = '0';
        }
        if(!isset($values['yubao']['is_category_force'])){
            $values['yubao']['is_category_force'] = '0';
        }
        if(!isset($values['yubao']['is_price_force'])){
            $values['yubao']['is_price_force'] = '0';
        }
        if(!isset($values['yubao']['is_remark_force'])){
            $values['yubao']['is_remark_force'] = '0';
        }
        if(!isset($values['yubao']['is_images_force'])){
            $values['yubao']['is_images_force'] = '0';
        }
        if(!isset($values['yubao']['is_xieyi_force'])){
            $values['yubao']['is_xieyi_force'] = '0';
        }
        if(!isset($values['yubao']['is_goodslist_force'])){
            $values['yubao']['is_goodslist_force'] = '0';
        }
        
        if(!isset($values['userinfo']['is_identification_card_force'])){
            $values['userinfo']['is_identification_card_force'] = '0';
        }
        if(!isset($values['userinfo']['is_birthday_force'])){
            $values['userinfo']['is_birthday_force'] = '0';
        }
        if(!isset($values['userinfo']['is_wechat_force'])){
            $values['userinfo']['is_wechat_force'] = '0';
        }
        if(!isset($values['userinfo']['is_email_force'])){
            $values['userinfo']['is_email_force'] = '0';
        }
        if(!isset($values['userinfo']['is_mobile_force'])){
            $values['userinfo']['is_mobile_force'] = '0';
        }
        
        if(!isset($values['fahuocang']['is_user_force'])){
            $values['fahuocang']['is_user_force'] = '0';
        }
        if(!isset($values['fahuocang']['is_shelf_force'])){
            $values['fahuocang']['is_shelf_force'] = '0';
        }
        if(!isset($values['fahuocang']['is_category_force'])){
            $values['fahuocang']['is_category_force'] = '0';
        }
        if(!isset($values['fahuocang']['is_weight_force'])){
            $values['fahuocang']['is_weight_force'] = '0';
        }
        if(!isset($values['fahuocang']['is_vol_force'])){
            $values['fahuocang']['is_vol_force'] = '0';
        }
        if(!isset($values['fahuocang']['is_adminremark_force'])){
            $values['fahuocang']['is_adminremark_force'] = '0';
        }
        if(!isset($values['fahuocang']['is_photo_force'])){
            $values['fahuocang']['is_photo_force'] = '0';
        }
        
        return $values;
    }
    

    /**
     * 数据验证
     * @param $key
     * @param $values
     * @return bool
     */
    private function validValues($key, $values)
    {
        $callback = [
            'store' => function ($values) {
                return $this->validStore($values);
            },
            'printer' => function ($values) {
                return $this->validPrinter($values);
            },
        ];
        // 验证商城设置
        return isset($callback[$key]) ? $callback[$key]($values) : true;
    }

    /**
     * 验证商城设置
     * @param $values
     * @return bool
     */
    private function validStore($values)
    {
        if (!isset($values['delivery_type']) || empty($values['delivery_type'])) {
            $this->error = '配送方式至少选择一个';
            return false;
        }
        return true;
    }

    /**
     * 验证小票打印机设置
     * @param $values
     * @return bool
     */
    private function validPrinter($values)
    {
        if ($values['is_open'] == false) {
            return true;
        }
        return true;
    }
    
    // 获得服务费设置数据
    public function getServiceData($values){
        $service = [];
        foreach ($values['num_start'] as $k => $v){
            $service[] = [
               'num' => [
                  'min' => $v,
                  'max' => $values['num_end'][$k],
               ],
               'price' => $values['price'][$k],
            ];
        }
        return $service;
        
    }

}
