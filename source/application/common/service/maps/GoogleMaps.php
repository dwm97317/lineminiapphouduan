<?php

namespace app\common\service\maps;

use app\common\model\Setting as SettingModel;
use GuzzleHttp\Client;

/**
 * Google Maps API 服务
 * Class GoogleMaps
 * @package app\common\service\maps
 */
class GoogleMaps
{
    private $apiKey;
    private $client;

    public function __construct()
    {
        $config = SettingModel::getItem('line_config');
        $this->apiKey = $config['google_maps_key'] ?? '';
        $this->client = new Client([
            'base_uri' => 'https://maps.googleapis.com/',
            'timeout'  => 5.0,
        ]);
    }

    /**
     * 逆地理编码: 坐标转地址
     * @param $lat
     * @param $lng
     * @return array|bool
     */
    public function reverseGeocoding($lat, $lng)
    {
        if (empty($this->apiKey)) {
            return false;
        }

        try {
            $response = $this->client->get('maps/api/geocode/json', [
                'query' => [
                    'latlng' => "{$lat},{$lng}",
                    'key'    => $this->apiKey,
                    'language' => 'en' // 默认使用英文，方便解析泰国行政区
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            if ($result['status'] === 'OK') {
                return $this->parseThaiAddress($result['results'][0]);
            }
        } catch (\Exception $e) {
            \think\Log::error('Google Maps API Error: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * 解析泰国地址结构
     * @param $result
     * @return array
     */
    private function parseThaiAddress($result)
    {
        $address = [
            'country' => 'Thailand',
            'province' => '',
            'city' => '',
            'region' => '',
            'sub_district' => '',
            'postal_code' => '',
            'detail' => $result['formatted_address']
        ];

        foreach ($result['address_components'] as $component) {
            $types = $component['types'];
            
            // 邮编
            if (in_array('postal_code', $types)) {
                $address['postal_code'] = $component['long_name'];
            }
            // 省 (Administrative Area Level 1)
            elseif (in_array('administrative_area_level_1', $types)) {
                $address['province'] = $component['long_name'];
            }
            // 县 (Administrative Area Level 2 - Amphoe)
            elseif (in_array('administrative_area_level_2', $types)) {
                $address['city'] = $component['long_name'];
                $address['region'] = $component['long_name'];
            }
            // 区 (Locality / Sub-locality - Tambon)
            elseif (in_array('locality', $types) || in_array('sublocality', $types)) {
                $address['sub_district'] = $component['long_name'];
            }
        }

        return $address;
    }
}
