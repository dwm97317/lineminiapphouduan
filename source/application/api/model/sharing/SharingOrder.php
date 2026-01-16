<?php
namespace app\api\model\sharing;
use app\common\model\sharing\SharingOrder as SharingOrderModel;
use app\api\model\sharing\SharingOrderAddress;
use app\api\model\UserAddress;
use app\common\model\sharing\Setting;
use app\common\model\Country;

class SharingOrder extends SharingOrderModel {
    
    public function getList($query){
        return $this
            ->setWhere($query)
            ->with(['country','address'])
            ->paginate(15,false, [
                'query' => \request()->request()
            ]);
    }
    
    public function setWhere($query){
        if (isset($query['member_id']) && $query['member_id']){
            $this->where(['member_id'=>$query['member_id']]);
        }
        if (isset($query['status']) && $query['status']){
            $this->where('status','in',$query['status']);
        }
        if (isset($query['is_recommend']) && $query['is_recommend']){
            $this->where(['is_recommend'=>$query['is_recommend']]);
        }
        if (isset($query['order_ids']) && $query['order_ids']){
            $this->where('order_id','in',$query['order_ids']);
        }
        if (isset($query['country_id']) && $query['country_id']){
            $this->where('country_id','=',$query['country_id']);
        }
        if (isset($query['keyword']) && $query['keyword']){
            $this->where('title','like',"%".$query['keyword']."%");
        }
        return $this;
    }
    
    // 热门国家
    public function getHotCountryIds(){
        $sql = 'SELECT country_id,count(*) as count FROM yoshop_sharing_tr_order GROUP BY country_id order by count DESC limit 0,10';
        $data = $this->query($sql);
        return array_column($data,'country_id');
    }
    
    // 获取我发布的拼团ID组合
    public function getMyOrderIds($userId,$query){
        return $this->where(['member_id'=>$userId])->where('status','in',[1,2])->field('order_id')->select();
    }
    
    public function created($form){
        if (!$this->checkData($form)){
            return false;
        }
      
        $setting = Setting::getItem('sharp');
        // 开启事务
        $this->startTrans();
        try {
            $address_id = $form['address_id'];
            $form['order_sn'] = date("YmdHis",time()).rand(00000,99999);
            $form['start_time'] = strtotime($form['start_time']);
            $form['end_time'] = strtotime($form['end_time']);
            $form['storage_id'] = $form['shop_id'];
            if (!$form['start_time']){
                 $form['start_time'] =  time();
            }
            $form['is_verify'] = $setting['is_verify']==1?2:1; // 根据设置判断是否需要审核 1是需要审核
            // 添加订单
            $this->allowField(true)->save($form);
            $this->addAddress($address_id,['is_head'=>1,'country'=>$form['country_id']]);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }
    
    // 新增地址信息
    public function addAddress($addressId,$extend){
        $orderId = $this['order_id'];
        $countrylist = (new Country())->getListAllCountry();
        // dump($extend);die;
        $addressInfo = (new UserAddress())->find($addressId);
        $address_insert = [
            'order_id' => $orderId,
            'is_head' => $extend['is_head'],
            'province' => $addressInfo['province'],
            'city' => $addressInfo['city'],
            'region' => $addressInfo['region'],
            'address' => $addressInfo['detail'].$addressInfo['door'],
            'country' => $extend['country'],
            'wxapp_id' => self::$wxapp_id
        ];
        return (new SharingOrderAddress())->save($address_insert);
    }
    
    public function updateAddress($model,$address){
        $countrylist = (new Country())->getListAllCountry();
        $addressInfo = (new UserAddress())->find($address);
        $address_insert = [
            
            'name' => $addressInfo['name'],
            'phone' => $addressInfo['phone'],
            'province' => $addressInfo['province'],
            'city' => $addressInfo['city'],
            'region' => $addressInfo['region'],
            'address' => $addressInfo['detail'].$addressInfo['door'],
            
            'wxapp_id' => self::$wxapp_id
        ];
      
        return $model->save($address_insert);
    }
    
    public function checkData($form){
        if (!$form['title']){
            $this->error = '请填写拼团活动标题';
            return false;
        }
        if (!$form['line_id']){
            $this->error = '请选择线路';
            return false;
        }
        if (!$form['shop_id']){
            $this->error = '请选择寄送仓库';
            return false;
        }
        if (!$form['country_id']){
            $this->error = '请选择寄送至国家';
            return false;
        }
        return true;
    }
    
    // 解散拼团
    public function dissolution($user){
        if ($this->status['value']>=3){
            $this->error = '无法解散该拼团活动';
            return false;
        }
        if ($user['user_id'] != $this->member_id){
            $this->error = '您不是该活动创建者，无法解散';
            return false;
        }
        $update['status'] = 8;
        $update['disband_time'] = time();
        $update['disband_reason'] = '团长解散拼团活动';
        $update['disband_member_id'] = $user['user_id'];
        return $this->save($update);
    }
    
    
    public function getListByDistane($query){
            return $this
            ->setWhere($query)
            ->with(['country','address'])
            ->order("create_time desc")
            ->paginate(15,false, [
                'query' => \request()->request()
            ]);
    }
    
    public function getListByDistanepage($query){
        $pageSize = 15;   
        $currentLimit = ($query['page']-1)*$pageSize;
        $where = '';
        if ($query['keyword']){
            $where = " and title like '%".$query['keyword']."%'";
        }
        $sql = 'SELECT
        *,
        ROUND(
            6378.138 * 2 * ASIN(
                SQRT(
                    POW(
                        SIN(
                            (
                                '.$query['lat'].' * PI() / 180 - lat * PI() / 180
                            ) / 2
                        ),
                        2
                    ) + COS('.$query['lat'].' * PI() / 180) * COS(lat * PI() / 180) * POW(
                        SIN(
                            (
                                '.$query['lng'].' * PI() / 180 - lng * PI() / 180
                            ) / 2
                        ),
                        2
                    )
                )
            ) * 1000
        ) AS distance
    FROM
        yoshop_sharing_tr_order
        where is_verify = 1 and status = 1 and country_id = '.$query['country_id'].$where.'
    ORDER BY
        distance ASC limit '.$currentLimit.','.$pageSize;
        
      $list = $this->query($sql);
      foreach ($list as $k => $v){
          $list[$k]['distance'] = $v['distance']/1000;
      }
      return $list;
    }
    
    public function country(){
        return $this->belongsTo('app\common\model\Country','country_id');
    }
    
    public function address(){
        return $this->belongsTo('app\api\model\UserAddress','address_id');
    }
    
    /**
     * 开始时间
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getStartTimeAttr($value)
    {
        return date('Y-m-d H:i:s',$value);
    }
    
     /**
     * 结束时间
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getEndTimeAttr($value)
    {
        return date('Y-m-d H:i:s',$value);
    }
    
}