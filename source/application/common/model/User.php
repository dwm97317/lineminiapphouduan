<?php

namespace app\common\model;
use app\common\model\user\PointsLog as PointsLogModel;
use app\api\model\user\BalanceLog;
use app\common\enum\user\balanceLog\Scene as SceneEnum;
use app\store\model\Package;

/**
 * 用户模型类
 * Class User
 * @package app\common\model
 */
class User extends BaseModel
{
    protected $name = 'user';

    // 性别
    private $gender = ['保密', '男', '女'];
    
    // 余额不变更，只更新日志
    public function logUpdate($type,$member_id,$amount,$remark){
        $member = self::find($member_id);
        
        // 将字符串类型转换为整数
        switch ($type) {
            case 'add':
                $type = 1;
                break;
            case 'remove':
                $type = 2;
                break;
            default:
                // 如果已经是整数，保持不变
                $type = is_numeric($type) ? (int)$type : 1;
                break;
        }
        
        // 新增余额变动记录
         BalanceLog::add(SceneEnum::CONSUME, [
              'user_id' => $member['user_id'],
              'money' => 0,
              'remark' => $remark,
              'sence_type' => $type,
              'wxapp_id' => (new Package())->getWxappId(),
          ], [$member['nickName']]);
          return true;
    }
    
    // 余额变更
    public function banlanceUpdate($type,$member_id,$amount,$remark){
        $member = self::find($member_id);
        switch ($type) {
            case 'add':
                $type = 1;
                $update['balance'] = $member['balance']+$amount;
                // code...
                break;
            case 'remove':
                $type = 2;
                $update['balance'] = $member['balance']-$amount;
                $update['pay_money'] = $member['pay_money']+$amount;
                break;
            default:
                return false;
                // code...
                break;
        }
       
        // 新增余额变动记录
        BalanceLog::add(SceneEnum::CONSUME, [
              'user_id' => $member['user_id'],
              'money' => $amount,
              'remark' => $remark,
              'sence_type' => $type,
              'wxapp_id' => (new Package())->getWxappId(),
          ], [$member['nickName']]);
        return $this->where(['user_id'=>$member_id])->update($update);
    }
    
    // 余额变更,不更新日志
    public function BanlanceChange($type,$member_id,$amount,$remark){
        $member = self::find($member_id);
        switch ($type) {
            case 'add':
                $type = 1;
                $update['balance'] = $member['balance']+$amount;
                // code...
                break;
            case 'remove':
                $type = 2;
                $update['balance'] = $member['balance']-$amount;
                $update['pay_money'] = $member['pay_money']+$amount;
                break;
            default:
                return false;
                // code...
                break;
        }
        return $this->where(['user_id'=>$member_id])->update($update);
    }
    
    /**
     * 关联会员等级表
     * @return \think\model\relation\BelongsTo
     */
    public function grade()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\user\\Grade");
    }
    /**
     * 关联客服等级表
     * @return \think\model\relation\BelongsTo
     */
    public function service()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\store\\shop\\Clerk",'service_id','clerk_id');
    }


    /**
     * 关联收货地址表
     * @return \think\model\relation\HasMany
     */
    public function address()
    {
        return $this->hasMany('UserAddress');
    }
    
    /**
     * 关联收货地址表
     * @return \think\model\relation\HasMany
     */
    public function usermark()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->hasMany("app\\{$module}\\model\\user\\UserMark");
    }

    /**
     * 关联收货地址表 (默认地址)
     * @return \think\model\relation\BelongsTo
     */
    public function addressDefault()
    {
        return $this->belongsTo('UserAddress', 'address_id');
    }

    /**
     * 显示性别
     * @param $value
     * @return mixed
     */
    public function getGenderAttr($value)
    {
        return [
            'text'=> $this->gender[$value],
            'value'=>$value
        ];
    }
    
    /**
     * 关联封面图
     * @return \think\model\relation\HasOne
     */
    public function userimage()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\UploadFile", 'user_image_id','file_id');
    }

    /**
     * 关联品牌Logo
     * @return \think\model\relation\BelongsTo
     */
    public function brandLogo()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\UploadFile", 'brand_logo_id', 'file_id');
    }

    /**
     * 获取用户信息
     * @param $where
     * @param $with
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($where, $with = ['address', 'addressDefault','grade','brandLogo'])
    {
        $filter = ['is_delete' => 0];
       
        if (is_array($where)) {
            $filter = array_merge($filter, $where);
        } else {
            $filter['user_id'] = (int)$where;
        }
        return static::get($filter, $with);
    }

    /**
     * 累积用户的实际消费金额
     * @param $userId
     * @param $expendMoney
     * @return int|true
     * @throws \think\Exception
     */
    public function setIncUserExpend($userId, $expendMoney)
    {
        return $this->where(['user_id' => $userId])->setInc('expend_money', $expendMoney);
    }

    /**
     * 指定会员等级下是否存在用户
     * @param $gradeId
     * @return bool
     */
    public static function checkExistByGradeId($gradeId)
    {
        $model = new static;
        return !!$model->where('grade_id', '=', (int)$gradeId)
            ->where('is_delete', '=', 0)
            ->value('user_id');
    }

    /**
     * 累积用户总消费金额
     * @param $money
     * @return int|true
     * @throws \think\Exception
     */
    public function setIncPayMoney($money)
    {
      
        return $this->setInc('pay_money', $money);
    }

    /**
     * 累积用户实际消费的金额 (批量)
     * @param $data
     * @return array|false
     * @throws \Exception
     */
    public function onBatchIncExpendMoney($data)
    {
        foreach ($data as $userId => $expendMoney) {
            $this->where(['user_id' => $userId])->setInc('expend_money', $expendMoney);
        }
        return true;
    }

    /**
     * 累积用户的可用积分数量 (批量)
     * @param $data
     * @return array|false
     * @throws \Exception
     */
    public function onBatchIncPoints($data)
    {
        foreach ($data as $userId => $expendMoney) {
            $this->where(['user_id' => $userId])->setInc('points', $expendMoney);
        }
        return true;
    }

    /**
     * 累积用户的可用积分
     * @param $points
     * @param $describe
     * @return int|true
     * @throws \think\Exception
     */
    public function setIncPoints($points, $describe)
    {
        // 新增积分变动明细
        PointsLogModel::add([
            'user_id' => $this['user_id'],
            'value' => $points,
            'describe' => $describe,
        ]);
        // 更新用户可用积分
        return $this->setInc('points', $points);
    }
    
    // public function getMobileAttr($value){
    //     if(empty($value)){
    //         return '';
    //     }
    //     return hide_mobile($value);
    // }

}
