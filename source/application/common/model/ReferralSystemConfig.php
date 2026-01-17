<?php

namespace app\common\model;

/**
 * 推荐系统配置模型
 * Class ReferralSystemConfig
 * @package app\common\model
 */
class ReferralSystemConfig extends BaseModel
{
    protected $name = 'referral_system_config';
    protected $pk = 'id';

    /**
     * 获取配置值
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getConfig($key, $default = null)
    {
        $config = self::where('config_key', $key)
            ->where('is_enabled', 1)
            ->find();

        if (!$config) {
            return $default;
        }

        // 根据配置类型转换值
        switch ($config['config_type']) {
            case 'int':
                return (int)$config['config_value'];
            case 'float':
                return (float)$config['config_value'];
            case 'bool':
                return $config['config_value'] == '1' || $config['config_value'] == 'true';
            case 'json':
                return json_decode($config['config_value'], true);
            default:
                return $config['config_value'];
        }
    }

    /**
     * 设置配置值
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @return bool
     */
    public static function setConfig($key, $value, $type = 'string')
    {
        // 转换值为字符串
        if ($type == 'json') {
            $value = json_encode($value);
        } elseif ($type == 'bool') {
            $value = $value ? '1' : '0';
        } else {
            $value = (string)$value;
        }

        $config = self::where('config_key', $key)->find();

        if ($config) {
            return $config->save([
                'config_value' => $value,
                'config_type' => $type,
            ]);
        } else {
            return self::create([
                'config_key' => $key,
                'config_value' => $value,
                'config_type' => $type,
                'is_enabled' => 1,
                'wxapp_id' => self::$wxapp_id,
            ]);
        }
    }

    /**
     * 获取所有配置
     * @return array
     */
    public static function getAllConfigs()
    {
        $configs = self::where('is_enabled', 1)->select();
        $result = [];

        foreach ($configs as $config) {
            $result[$config['config_key']] = self::getConfig($config['config_key']);
        }

        return $result;
    }
}
