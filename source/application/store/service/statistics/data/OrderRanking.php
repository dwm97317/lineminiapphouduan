<?php

namespace app\store\service\statistics\data;

use app\common\service\Basics as BasicsService;
use app\store\model\OrderGoods as OrderGoodsModel;
use app\store\model\Countries as CountriesModel;
use app\common\enum\order\Status as OrderStatusEnum;
use app\common\enum\order\PayStatus as OrderPayStatusEnum;

/**
 * 数据统计-商品销售榜
 * Class GoodsRanking
 * @package app\store\service\statistics\data
 */
class OrderRanking extends BasicsService
{
    /**
     * 商品销售榜
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderCountryRanking()
    {
        return (new CountriesModel)->alias('co')
            ->field(['co.id','co.title','ad.country_id','ad.address_id','inpack.real_payment',
                'SUM(inpack.real_payment) AS sales_volume',
                'COUNT(ad.country_id) AS total_sales_num',
            ])
            ->join('user_address ad','ad.country_id = co.id')
            ->join('inpack', 'inpack.address_id = ad.address_id')
            // ->where('inpack.is_pay', '=',1)
            ->where('inpack.is_delete', '=',0)
            ->group('co.id, co.title')
            // order：此处按总销售额排序，如需按销量改为total_sales_num
            ->order(['sales_volume' => 'DESC'])
            // ->limit(10)
            ->select();
    }

}