<?php

namespace app\store\model\dealer;

use app\common\model\dealer\Referee as RefereeModel;
use app\common\model\User as UserModel;
use app\common\model\dealer\User;

/**
 * 分销商推荐关系模型
 * Class Referee
 * @package app\store\model\dealer
 */
class Referee extends RefereeModel
{
    /**
     * 获取下级团队成员ID集
     * @param $dealerId
     * @param int $level
     * @return array
     */
    public function getTeamUserIds($dealerId, $level = -1)
    {
        $level > -1 && $this->where('m.level', '=', $level);
        return $this->alias('m')
            ->join('user', 'user.user_id = m.user_id')
            ->where('m.dealer_id', '=', $dealerId)
            ->where('user.is_delete', '=', 0)
            ->column('m.user_id');
    }

    /**
     * 获取指定用户的推荐人列表
     * @param $userId
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getRefereeList($userId)
    {
        return (new static)->with(['dealer1'])->where('user_id', '=', $userId)->select();
    }

    /**
     * 清空下级成员推荐关系
     * @param $dealerId
     * @param int $level
     * @return int
     */
    public function onClearTeam($dealerId, $level = -1)
    {
        $level > -1 && $this->where('level', '=', $level);
        return $this->where('dealer_id', '=', $dealerId)->delete();
    }
    
     /**
     * 创建推荐关系
     * @param $user_id
     * @param $referee_id
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function createRelation($user_id, $referee_id)
    {
        // 分销商基本设置
        $setting = Setting::getItem('basic');
        // 是否开启分销功能
        if (!$setting['is_open']) {
            return false;
        }
        // 自分享
        if ($user_id == $referee_id) {
            return false;
        }
        // # 记录一级推荐关系
        // 判断当前用户是否已存在推荐关系
        if ($this->isExistReferee($user_id)) {
            return false;
        }
       
        // 判断推荐人是否为分销商
        if (!User::isDealerUser($referee_id)) {
            return false;
        }
        // 新增关系记录
        $this->add($referee_id, $user_id, 1);
        // # 记录二级推荐关系
        if ($setting['level'] >= 2) {
            // 二级分销商id
            $referee_2_id = self::getRefereeUserId($referee_id, 1, true);
            // 新增关系记录
            $referee_2_id > 0 && $model->add($referee_2_id, $user_id, 2);
        }
        // # 记录三级推荐关系
        if ($setting['level'] == 3) {
            // 三级分销商id
            $referee_3_id = self::getRefereeUserId($referee_id, 2, true);
            // 新增关系记录
            $referee_3_id > 0 && $model->add($referee_3_id, $user_id, 3);
        }
        return true;
    }
    
    /**
     * 新增关系记录
     * @param $dealer_id
     * @param $user_id
     * @param int $level
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    private function add($dealer_id, $user_id, $level = 1)
    { 
        // 新增推荐关系
        $wxapp_id = self::$wxapp_id;
        $create_time = time();
       
        $this->insert(compact('dealer_id', 'user_id', 'level', 'wxapp_id', 'create_time'));
        
        // 记录分销商成员数量
        User::setMemberInc($dealer_id, $level);
        return true;
    }
    
    /**
     * 是否已存在推荐关系
     * @param $user_id
     * @return bool
     * @throws \think\exception\DbException
     */
    private static function isExistReferee($user_id)
    {
        return !!self::get(['user_id' => $user_id]);
    }

    /**
     * 清空上级推荐关系
     * @param $userId
     * @param int $level
     * @return int
     */
    public function onClearReferee($userId, $level = -1)
    {
        $level > -1 && $this->where('level', '=', $level);
        return $this->where('user_id', '=', $userId)->delete();
    }

    /**
     * 清空2-3级推荐人的关系记录
     * @param $teamIds
     * @return int
     */
    public function onClearTop($teamIds)
    {
        return $this->where('user_id', 'in', $teamIds)
            ->where('level', 'in', [2, 3])
            ->delete();
    }

}