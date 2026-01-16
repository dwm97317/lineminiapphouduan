<?php

namespace app\store\model\dealer;

use app\common\model\dealer\RatingLog as RatingLogModel;

/**
 * 用户会员等级变更记录模型
 * Class GradeLog
 * @package app\store\model\user
 */
class RatingLog extends RatingLogModel
{

    /**
     * 新增变更记录
     * @param $data
     * @return array|false
     * @throws \Exception
     */
    public function record($data)
    {
        return $this->records([$data]);
    }

}