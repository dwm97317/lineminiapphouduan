<?php

namespace app\store\controller\user;

use app\store\controller\Controller;
use app\store\model\User as UserModel;

/**
 * 用户控制器
 */
class User extends Controller
{
    /**
     * 获取会员列表（用于下拉选择）
     */
    public function getMemberList()
    {
        try {
            // 获取会员列表
            $list = UserModel::where('is_delete', 0)
                ->field('user_id, nickName, mobile')
                ->order('user_id', 'desc')
                ->limit(1000)
                ->select();
            
            return $this->renderSuccess('', '', [
                'list' => $list
            ]);
            
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
}
