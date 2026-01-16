<?php

namespace app\api\model\store\shop;

use app\common\exception\BaseException;
use app\common\model\store\shop\Withdraw as WithdrawModel;
use app\api\model\User;
/**
 * 分销商提现明细模型
 * Class Withdraw
 * @package app\api\model\dealer
 */
class Withdraw extends WithdrawModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'update_time',
    ];

    /**
     * 获取分销商提现明细
     * @param $user_id
     * @param int $apply_status
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($shop_id, $apply_status = -1,$from=1)
    {
        $this->where('shop_id', '=', $shop_id);
        $this->where('cash_from','=',$from);
        $apply_status > -1 && $this->where('apply_status', '=', $apply_status);
        return $this->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 提交申请
     * @param User $shop
     * @param $data
     * @return false|int
     * @throws BaseException
     */
    public function submit($shop, $data)
    {

        // 数据验证
        $this->validation($shop, $data);
        // 新增申请记录
        unset($data['token']);
        $this->save(array_merge($data, [
            'shop_id' => $shop['shop_id'],
            'apply_status' => 10,
            'wxapp_id' => self::$wxapp_id,
        ]));
        // 冻结用户资金
       
        $shop->freezeMoney($data['money']);
        return true;
    }
    
    /**
     *用户提现申请 
     */
    public function submitUser($shop, $data){
        // 数据验s证
        $this->validationUser($shop, $data);
        // 新增申请记录
        $this->save(array_merge($data, [
            'shop_id' => $shop['shop_id'],
            'apply_status' => 10,
            'cash_from' => 2,
            'wxapp_id' => self::$wxapp_id,
        ]));
        $user = (new User());
        // 冻结用户资金
        $user->freezeMoney($data['money'],$shop['shop_id']);
        return true;
    }
   
    /**
     * 数据验证
     * @param $shop
     * @param $data
     * @throws BaseException
     */
    private function validation($shop, $data)
    {
        // 结算设置
  
        $settlement = Setting::getItem('settlement');
                   
        // 最低提现佣金
        if ($data['money'] <= 0) {
            throw new BaseException(['msg' => '提现金额不正确']);
        }
              
        if ($shop['income'] <= 0) {
            throw new BaseException(['msg' => '当前用户没有可提现佣金']);
        }
    
        if ($data['money'] > $shop['income']) {
            throw new BaseException(['msg' => '提现金额不能大于可提现佣金']);
        }
        if ($data['money'] < $settlement['min_money']) {
            throw new BaseException(['msg' => '最低提现金额为' . $settlement['min_money']]);
        }
        if (!in_array($data['pay_type'], $settlement['pay_type'])) {
            throw new BaseException(['msg' => '提现方式不正确']);
        }
        if ($data['pay_type'] == '20') {
            if (empty($data['alipay_name']) || empty($data['alipay_account'])) {
                throw new BaseException(['msg' => '请补全提现信息']);
            }
        } elseif ($data['pay_type'] == '30') {
            if (empty($data['bank_name']) || empty($data['bank_account']) || empty($data['bank_card'])) {
                throw new BaseException(['msg' => '请补全提现信息']);
            }
        }elseif ($data['pay_type'] == '40') {
            if (empty($data['weixinhao'])) {
                throw new BaseException(['msg' => '请补全提现信息']);
            }
        }
    }
    
    /**
     * 数据验证
     * @param $shop
     * @param $data
     * @throws BaseException
     */
    private function validationUser($shop, $data)
    {
        // 结算设置
        $settlement = Setting::getItem('settlement');
        // 最低提现佣金
        if ($data['money'] <= 0) {
            throw new BaseException(['msg' => '提现金额不正确']);
        }
        if ($shop['points'] < $settlement['min_points']){
             throw new BaseException(['msg' => '当前用户积分未达到提现要求']);
        }
        if ($shop['balance'] <= 0) {
            throw new BaseException(['msg' => '当前用户没有可提现金额']);
        }
        if ($data['money'] > $shop['balance']) {
            throw new BaseException(['msg' => '提现金额不能大于可提现金额']);
        }
        if ($data['money'] < $settlement['min_money']) {
            throw new BaseException(['msg' => '最低提现金额为' . $settlement['min_money']]);
        }
        if (!in_array($data['pay_type'], $settlement['pay_type'])) {
            throw new BaseException(['msg' => '提现方式不正确']);
        }
        if ($data['pay_type'] == '20') {
            if (empty($data['alipay_name']) || empty($data['alipay_account'])) {
                throw new BaseException(['msg' => '请补全提现信息']);
            }
        } elseif ($data['pay_type'] == '30') {
            if (empty($data['bank_name']) || empty($data['bank_account']) || empty($data['bank_card'])) {
                throw new BaseException(['msg' => '请补全提现信息']);
            }
        }
    }


}