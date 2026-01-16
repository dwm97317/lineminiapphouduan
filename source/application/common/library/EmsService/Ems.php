<?php

namespace app\common\library\EmsService;
use app\common\library\EmsService\Data;

Class Ems {
    
    public function __construct()
    {
       $this->data = (new Data());
    }

    // 获取物流追踪单号
    public function getBarService($data){
        dump(666);die;
        // $api = '/pcpErp-web/a/pcp/barCodesAssgine/barCodeService';
        $api = "/pcpErp-web/a/pcp/orderService/OrderReceiveBack";
        $staticParam = $this->staticsParams();
        $header = [
           "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
        ];
        // 构建post 数据
        $datas = [];
        $datas = $this->formatPostData($data,'order');
        $body['logistics_interface'] = json_encode($datas,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        // $body['logistics_interface'] = '{"created_time":"'.date("Y-m-d H:i:s",time()).'","sender_no":'. $this->data->config['ecCompanyId'].',"wh_code":"10000030","mailType":"SHANXIYIQI","logistics_order_no":"6xb5gh211","batch_no":"12234","biz_product_no":"002","weight":"10","volume":"10","length":"10","width":"10","height":"10","postage_total":"123","postage_currency":"USD","contents_total_weight":"11","contents_total_value":"11","transfer_type":"HK","battery_flag":"0","pickup_notes":"", "insurance_flag":"1", "insurance_amount":"123", "undelivery_option":"1", "valuable_flag":"0", "declare_source":"1", "declare_type":"1", "declare_curr_code":"USD", "printcode":"0", "barcode":"0323243245454", "forecastshut":"0", "mail_sign":"1", "sender":{"name":"张三", "company":"IBM", "post_code":"","phone":"1303000000","mobile":"8613123458765","email":"34456575844@qq.com","id_type":"1","id_no":"300000000000000000","nation":"CN","province":"山东","city":"济南","county":"历城","address":"xx 路 xx 号","gis":"", "linker":"张三"}, "receiver":{ "name":"王五", "company":"中国邮政", "post_code":"12345", "phone":"1303000000", "mobile":"8613123458765", "email":"34456575844@qq.com", "id_type":"1", "id_no":"300000000000000000", "nation":"US", "province":"北京", "city":"北京", "county":"西城区", "address":"xx路xx号", "gis":"","linker":"王五"},"items":[{"cargo_no":"1234","cargo_name":"小米手机","cargo_name_en":"miphone","cargo_type_name":"手机","cargo_type_name_en":"mobile phone","cargo_origin_name":"CN","cargo_link":"", "cargo_quantity":5, "cargo_value":2000, "cost":2000, "cargo_currency":"USD", "carogo_weight":200, "cargo_description":"1","cargo_serial":"1", "unit":"个", "intemsize":""},{"cargo_no":"", "cargo_name":"魅族手机", "cargo_name_en":"miphone", "cargo_type_name":"手机", "cargo_type_name_en":"mobilephone", "cargo_origin_name":"CN", "cargo_link":"","cargo_quantity":5,"cost":2000,"cargo_value":2000,"cargo_currency":"USD","carogo_weight":200,"cargo_description":"1","cargo_serial":"1","unit":"个","intemsize":""}]}';
        $body['data_digest'] = $this->dataEncode($body['logistics_interface']);
        $body = array_merge($body,$staticParam);
        $body['msg_type'] = 'B2C_TRADE';
        $body['data_type'] = 'JSON';
           dump(666);die;
        $body['biz_product_no'] = $this->getBussisData();
        $queryString = http_build_query($body);
     
        // $queryString = $this->builderQueryString($body);
        $apiRes = (new Curl())->post($this->data->config['Api'].$api,$queryString,$header);
        dump($apiRes); die;
        // $xml = $this->buildXmlFromArray($data);
    }
    
    // 获取业务类型
    function getBussisData(){
        $api = "/pcpErp-web/a/pcp/businessDataService/getBusinessData";
        $body = [
          'queryType'=>'queryBusinessType',
        ];
        $queryString = http_build_query($body); 
        $apiRes = (new Curl())->post($this->data->config['Api'].$api,$queryString);
        dump($apiRes); die;
    }
    
    /**
     * 将参数整理成查询字符串 
     */
    function builderQueryString($data){
         $queryString = '';
         $i = 0;
         foreach ($data as $k => $v) {
           if (!empty($v) && "@" != substr($v, 0, 1)) {   
             // 转换成目标字符集
             $v = $this->characet($v, $this->data->config['charset']);
             if ($i == 0) {
                 $queryString.="$k"."="."$v";
             } else {
                 $queryString.="&"."$k"."="."$v";
             }  
             $i++;
           }
         }
         $queryString = str_replace('amp;','',$queryString); 
         return $queryString;
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset) {
      
      if (!empty($data)) {
        $fileType = $this->data->config['fileCharset'];
        if (strcasecmp($fileType, $targetCharset) != 0) {
          $data = mb_convert_encoding($data, $targetCharset, $fileType);
          //				$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
        }
      }
      return $data;
    }
    
    /**
     * 格式化接口请求数据
     */
    private function formatPostData($data,$scene='order'){
        $postData = [];
        switch($scene){
           case 'order':
            //   $regionCode = array_column($this->data->region_code,null,'name');
            //   $postData['eventTime'] = date("Y-m-d H:i:s",time());
            //   $postData['ecCompanyId'] = $this->data->config['ecCompanyId'];
            //   $postData['whCode'] = $this->data->config['code']; 
            //   $postData['logisticsOrderId'] = $data['inpack']['order_sn']; 
            //   $postData['tradeId'] = 'AE'; 
            //   $postData['logisticsCompany'] = 'POST';
            //   $postData['logisticsBiz'] = '002';
            //   $postData['mailType'] = $this->data->config['mailType']; ;
            //   $postData['faceType'] = 1;             
            //   $postData['rcountry'] = $regionCode[$data['base']['country']]['code'];
            $weight = 20;
            $address = [
                'name' => $data['extend']['recive_name'],
                'nation' => $data['base']['country'],
                'address' => $data['extend']['recive_address'],
                'linker' => '',
                'province' => $data['extend']['recive_province'],
                'city' => $data['extend']['recive_city'],
            ];
            $items = [];
            foreach($data['item'] as $v){
                $item['cargo_no'] = rand(000,999);
                $item['cargo_name'] = $v['class_name'];
                $item['cargo_name_en'] = $v['class_name_en'];
                $item['cargo_type_name'] = $v['class_name'];
                $item['cargo_quantity'] = $v['product_num'];
                $item['cost'] = $v['all_price'];
                $item['weight'] = $v['unit_weight'];
                $item['cargo_currency'] = 'USD';
                $item['unit'] = '';
                $items[] = $item;
            }
            $postData['created_time'] = date("Y-m-d H:i:s",time());
            $postData['sender_no'] = $this->data->config['ecCompanyId'];
            $postData['wh_code'] =  $this->data->config['code'];
            $postData['mailType'] =  $this->data->config['mailType'];
            $postData['logistics_order_no'] = $data['extend']['transfer_order_no'];
            $postData['biz_product_no'] = '002';
            $postData['weight'] = $weight;
            $postData['declare_source'] = "2";
            $postData['declare_type'] = "2";
            $postData['declare_code'] = "USD";
            $postData['forecastshut'] = "0";
            $postData['postage_currency'] = 'USD';
            $postData['contents_total_weight'] = 10;
            $postData['contents_total_value'] = 10;
            $postData['mail_sign'] = '2';
            $postData['sender'] = $address;
            $postData['receiver'] = $address;
            $postData['items'] = $items;
            break;
           case 'sheet':
            break;
           case 'logistics':
            break;  
        }
        return $postData;
    }
    
    // 数据签名
    private function dataEncode($data){
       return base64_encode(md5($this->str_to_utf8($data.$this->data->config['key'])));  
    }

    // 将字符串转换成 UTF-8
    private function str_to_utf8($str = '') {
      $current_encode = mb_detect_encoding($str, array("ASCII","GB2312","GBK",'BIG5','UTF-8'));  //获取原来编码
      $encoded_str = mb_convert_encoding($str, 'UTF-8', $current_encode); //将原来编码转换成utf-8 大小写都可以
      return $encoded_str;
    }

    // 固定参数
    private function staticsParams(){
        return [
          'msg_type' => 'B2C_TRADE',
          'version' => '1.0',
          'ecCompanyId' =>$this->data->config['ecCompanyId'] ,
        ];
    }

}