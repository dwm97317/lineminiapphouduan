<?php

namespace app\store\service\statistics;

use app\common\service\Basics;
use app\store\service\statistics\data\Survey;
use app\store\service\statistics\data\Trade7days;
use app\store\service\statistics\data\GoodsRanking;
use app\store\service\statistics\data\UserExpendRanking;
use app\store\service\statistics\data\OrderRanking;
use app\store\service\statistics\data\CategoryRanking;
use app\store\service\statistics\data\DitchRanking;

/**
 * 数据概况服务类
 * Class Data
 * @package app\store\service\statistics
 */
class Data extends Basics
{
    /**
     * 获取数据概况
     * @param null $startDate
     * @param null $endDate
     * @return array
     * @throws \think\Exception
     */
    public function getSurveyData($startDate = null, $endDate = null)
    {
        return (new Survey)->getSurveyData($startDate, $endDate);
    }

    /**
     * 近7日走势
     * @return array
     * @throws \think\Exception
     */
    public function getTransactionTrend()
    {
        return (new Trade7days)->getTransactionTrend();
    }
    
    /**
     * 目的地排行榜
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function geOrderCountryRanking()
    {
        return (new OrderRanking)->getOrderCountryRanking();
    }

    /**
     * 商品销售榜
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsRanking()
    {
        return (new GoodsRanking)->getGoodsRanking();
    }
    
    /**
     * 目的地排行榜getCategoryRanking
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCategoryRanking()
    {
        return (new CategoryRanking)->getCategoryRanking();
    }
    
    /**
     * 渠道商排行榜
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function geOrderDitchRanking()
    {
        return (new DitchRanking)->getOrderDitchRanking();
    }
    

    /**
     * 用户消费榜
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function geUserExpendRanking()
    {
        return (new UserExpendRanking)->getUserExpendRanking();
    }

}