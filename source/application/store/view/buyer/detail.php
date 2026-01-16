 <div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget__order-detail widget-body am-margin-bottom-lg">
 <!-- 基本信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">基本信息</div>
                    </div>
                    <?php $status = ['-1'=>'已取消',1=>'待审核',2=>'代发货',3=>'在途中',4=>'已入库',5=>'已同步集运单',6=>'已完成',8=>'已退款']; ?>
                                <?php $pay_status = ['1'=>'已支付','0'=>'未支付']; ?>
                    <div class="am-scrollable-horizontal">
                        <table class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <tbody>
                            <tr>
                                <th>订单信息</th>
                                <th>买家</th>
                                <th style="250">代购信息</th>
                                <th>支付方式</th>
                                <th>交易状态</th>
                                <th>时间</th>
                            </tr>
                            <tr>
                                <td> <p>ID:<?= $detail['b_order_id'] ?></p>
                                    <p class="am-link-muted">(订单号：<?= $detail['order_sn'] ?>)</p> <p class="am-link-muted">(批次号：<?= $detail['batch'] ?>)</p></td>
                                </td>
                                <td class="">
                                    <p>买家ID:<?= $detail['member_id'] ?></p>
                                    <p class="am-link-muted">(用户昵称：<?= $detail['member_id'] ?>)</p>
                                </td>
                                <td>
                                    <p><a href="<?= $detail['url'] ?>">代购链接</a></p>
                                    <p class="am-link-muted">(商品价格：<?= $detail['price'] ?>)</p>
                                    <p class="am-link-muted">(代购数量：<?= $detail['num'] ?>)</p>
                                    <p class="am-link-muted">(商品规格：<?= $detail['spec'] ?>)</p>
                                    <p class="am-link-muted">(代购平台：<?= $detail['palform'] ?>)</p>
                                </td>
                                <td>
                                    <span class="am-badge am-badge-secondary"><?= $pay_status[$detail['is_pay']] ?></span>
                                </td>
                                <td>
                                     <span class="am-badge am-badge-secondary"><?= $status[$detail['status']]; ?></span>
                                </td>
                                <td>
                                     <p>创建时间:<?= $detail['created_time'] ?></p>
                                     <p>更新时间:<?= $detail['updated_time'] ?></p>
                                     <p>支付时间:<?= $detail['pay_time'] ?></p>
                                </td> 
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">支付信息</div>
                    </div>
                  
                    <div class="am-scrollable-horizontal">
                        <table class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <tbody>
                            <tr>
                                <th>实付金额</th>
                                <th style="250">运费</th>
                                <th>服务费</th>
                            </tr>
                            <tr>
                                <td><?= $detail['real_payment'] ?></td>
                                </td>
                                <td class="">
                                    <?= $detail['free'] ?>
                                </td>
                                <td>
                                   <?= $detail['service_free'] ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">备注信息</div>
                    </div>
                  
                    <div class="am-scrollable-horizontal">
                        <table class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <tbody>
                            <tr>
                                <th>审核反馈</th>
                                <th>买家备注</th>
                                <th style="250">用户反馈</th>
                            </tr>
                            <tr>
                                <td><?= $detail['reason'] ?></td>
                                </td>
                                <td class="">
                                    <?= $detail['remark'] ?>
                                </td>
                                <td>
                                   <?= $detail['feedback'] ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    
                </div>
            </div>

        </div>
    </div>
</div>

