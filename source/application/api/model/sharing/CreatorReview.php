<?php

namespace app\api\model\sharing;

use app\common\model\BaseModel;
use app\api\model\sharing\Active;
use app\common\model\sharing\ActiveUsers;

/**
 * 团长评价模型
 */
class CreatorReview extends BaseModel
{
    protected $name = 'sharing_creator_review';

    /**
     * 提交评价
     */
    public function submit($userId, $data)
    {
        // 验证拼团状态
        $active = Active::detail($data['active_id']);
        if (!$active || $active['status']['value'] != 20) {
            $this->error = '拼团未完成或不存在，无法评价';
            return false;
        }

        // 验证团长自己不能评价
        if ($active['creator_id'] == $userId) {
            $this->error = '团长不能评价自己';
            return false;
        }

        // 验证是否已评价
        if ($this->where(['active_id' => $data['active_id'], 'reviewer_id' => $userId])->count()) {
            $this->error = '您已评价过该团长';
            return false;
        }

        // 验证是否是成员
        if (!ActiveUsers::where(['active_id' => $data['active_id'], 'user_id' => $userId])->count()) {
            $this->error = '您未参与该拼团，无法评价';
            return false;
        }

        $this->startTrans();
        try {
            $this->save([
                'active_id' => $data['active_id'],
                'creator_id' => $active['creator_id'],
                'reviewer_id' => $userId,
                'service_score' => $data['service_score'] ?? 5,
                'package_score' => $data['package_score'] ?? 5,
                'speed_score' => $data['speed_score'] ?? 5,
                'communication_score' => $data['communication_score'] ?? 5,
                'content' => $data['content'] ?? '',
                'wxapp_id' => $active['wxapp_id']
            ]);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }
}
