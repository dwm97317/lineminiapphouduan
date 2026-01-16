<?php

namespace app\store\model;

use app\common\library\helper;
use app\common\model\Store as StoreModel;

/**
 * 商城模型
 * Class Store
 * @package app\store\model
 */
class Store extends StoreModel
{
    /* @var Goods $GoodsModel */
    private $GoodsModel;

    /* @var Order $GoodsModel */
    private $OrderModel;

    /* @var User $GoodsModel */
    private $UserModel;
    
    private $PackageModel;
    
    private $InpackModel;
    /**
     * 构造方法
     */
    public function initialize()
    {
        parent::initialize();
        /* 初始化模型 */
        $this->GoodsModel = new Goods;
        $this->OrderModel = new Order;
        $this->PackageModel = (new Package());
        $this->UserModel = new User;
        $this->InpackModel = new Inpack;
    }

    /**
     * 后台首页数据
     * @return array
     * @throws \think\Exception
     */
    public function getHomeData()
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        // 最近七天日期
        $lately7days = $this->getLately7days();
        $data = [
            'widget-card' => [
                // 商品总量
                'package_total' => $this->getPackageTotal(),
                'package_total_today' => $this->getPackageTotalByToday(),
                // 用户总量
                'user_total' => $this->getUserTotal(),
                // 订单总量
                'order_total' => $this->getOrderTotal(),
                // 评价总量
                'comment_total' => $this->getCommentTotal()
            ],
            'widget-outline' => [
                // 销售额(元)
                'order_total_price' => [
                    'tday' => $this->getPackageTotalPrice($today),
                    'ytd' => $this->getPackageTotalPrice($yesterday)
                ],
                // 支付订单数
                'order_total' => [
                    'tday' => $this->getPackageTotals($today),
                    'ytd' => $this->getPackageTotals($yesterday)
                ],
                // 新增用户数
                'new_user_total' => [
                    'tday' => $this->getUserTotal($today),
                    'ytd' => $this->getUserTotal($yesterday)
                ],
                // 下单用户数
                'order_user_total' => [
                    'tday' => $this->getPayPackageUserTotal($today),
                    'ytd' => $this->getPayPackageUserTotal($yesterday)
                ]
            ],
            'widget-echarts' => [
                // 最近七天日期
                'date' => helper::jsonEncode($lately7days),
                'order_total' => helper::jsonEncode($this->getOrderTotalByDate($lately7days)),
                'order_total_price' => helper::jsonEncode($this->getOrderTotalPriceByDate($lately7days)),
                'packageday_total'=>helper::jsonEncode($this->getPackageByDate($lately7days))
            ]
        ];

        return $data;
    }
    
    /**
     * 获取包裹入库总量 (指定日期)
     * @param $days
     * @return array
     */
    private function getPackageByDate($days)
    {
        $data = [];
        foreach ($days as $day) {
            $data[] = $this->getPackageOnday($day);
        }
        return $data;
    }
    
    /**
     * 获取某天的入库包裹数量
     * @param null $day
     * @return string
     */
    private function getPackageOnday($day = null)
    {
        return helper::number2($this->PackageModel->getPackageOnday($day, $day));
    }
    
    /**
     * 最近七天日期
     */
    private function getLately7days()
    {
        // 获取当前周几
        $date = [];
        for ($i = 0; $i < 30; $i++) {
            $date[] = date('Y-m-d', strtotime('-' . $i . ' days'));
        }
        return array_reverse($date);
    }

    /**
     * 获取商品总量
     * @return string
     * @throws \think\Exception
     */
    private function getGoodsTotal()
    {
        return number_format($this->GoodsModel->getGoodsTotal());
    }
    
    /**
     * 获取包裹总量
     * @return string
     * @throws \think\Exception
     */
    private function getPackageTotal()
    {
        return number_format($this->PackageModel->getPackTotal());
    }

/**
     * 获取今天包裹总量
     * @return string
     * @throws \think\Exception
     */
    private function getPackageTotalByToday()
    {
        $time = [];
        $t=time();//获取当前时间戳
    	$start=date("Y-m-d",mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t)))." 00:00:00";
    	$end=date("Y-m-d",mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t)))." 23:59:59";
        $time['start'] = $start;
        $time['end'] = $end;
        return number_format($this->PackageModel->getPackTotal($time));
    }


    /**
     * 获取用户总量
     * @param null $day
     * @return string
     * @throws \think\Exception
     */
    private function getUserTotal($day = null)
    {
        return number_format($this->UserModel->getUserTotal($day));
    }

    /**
     * 获取订单总量
     * @param null $day
     * @return string
     * @throws \think\Exception
     */
    private function getOrderTotal($day = null)
    {
        return number_format($this->InpackModel->getPayOrderTotal($day, $day));
    }
    
    /**
     * 获取订单总量
     * @param null $day
     * @return string
     * @throws \think\Exception
     */
    private function getPackageTotals($day = null)
    {
        return number_format($this->InpackModel->getPayPackageTotal($day, $day));
    }

    /**
     * 获取订单总量 (指定日期)
     * @param $days
     * @return array
     * @throws \think\Exception
     */
    private function getOrderTotalByDate($days)
    {
        $data = [];
        foreach ($days as $day) {
            $data[] = $this->getOrderTotal($day);
        }
        return $data;
    }

    /**
     * 获取评价总量
     * @return string
     */
    private function getCommentTotal()
    {
        $model = new Comment;
        return number_format($model->getCommentTotal());
    }

    /**
     * 获取某天的总销售额
     * @param null $day
     * @return string
     */
    private function getOrderTotalPrice($day = null)
    {
        return helper::number2($this->InpackModel->getOrderTotalPrice($day, $day));
    }
    
    /**
     * 获取某天的包裹成交额
     * @param null $day
     * @return string
     */
    private function getPackageTotalPrice($day = null)
    {
        return helper::number2($this->InpackModel->getPackageTotalPrice($day, $day));
    }

    /**
     * 获取订单总量 (指定日期)
     * @param $days
     * @return array
     */
    private function getOrderTotalPriceByDate($days)
    {
        $data = [];
        foreach ($days as $day) {
            $data[] = $this->getOrderTotalPrice($day);
        }
        return $data;
    }

    /**
     * 获取某天的下单用户数
     * @param $day
     * @return float|int
     */
    private function getPayOrderUserTotal($day)
    {
        return number_format($this->OrderModel->getPayOrderUserTotal($day));
    }
    
    
    /**
     * 获取某天的下单用户数
     * @param $day
     * @return float|int
     */
    private function getPayPackageUserTotal($day)
    {
        return number_format($this->InpackModel->getPayPackageUserTotal($day));
    }

}