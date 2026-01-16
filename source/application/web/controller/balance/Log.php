<?php
namespace app\web\controller\balance;

use app\web\controller\Controller;
use app\web\model\user\BalanceLog as BalanceLogModel;

/**
 * 余额账单明细
 * Class Log
 * @package app\api\controller\balance
 */
class Log extends Controller
{
    /**
     * 余额账单明细列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function lists()
    {
        $user = $this->getUser();
        $status = $this->postData('status')[0];
        $where = [];
        if ($status){
            $where['sence_type'] = $status;
        }
        $list = (new BalanceLogModel)->getList($user['user_id'],$where);
        return $this->renderSuccess(compact('list'));
    }

}