<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf"> 订单详情 </div>
                </div>
                <?php $taker_status = [1=>'待认领',2=>'已认领',3=>'已丢弃',4=>'退件']; ?>
                <?php $status = [1=>'待查验',2=>'待发货',3=>'待发货','4'=>'待发货','5'=>'待发货','6'=>'已发货','7'=>'已到货','8'=>'已完成','-1'=>'问题件']; ?>
                <div class="widget__order-detail widget-body am-margin-bottom-lg">
                        <!-- 基本信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">基本信息</div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <tbody>
                            <tr>
                                <th>订单号</th>
                                <th>买家</th>
                                <th>包裹情况</th>
                                <th>寄送仓库</th>
                                <th>寄送国家</th>
                                <th>包裹状态</th>
                                <th>时间</th>
                            </tr>
                            <tr>
                                <td>
                                    平台单号：<?= $detail['order_sn'] ?><br>
                                    支付单号：<?= $detail['pay_order'] ?><br>
                                    集运单号：<?= $detail['t_order_sn'] ?><br>
                                    
                                </td>
                                <td>
                                    <p><?= $detail['user']['nickName'] ?></p>
                                    <p class="am-link-muted">(用户id：<?= $detail['user']['user_id'] ?>)</p>
                                </td>
                                <td class="">
                                    <div class="td__order-price am-text-left">
                                        <ul class="am-avg-sm-2">
                                            <li class="am-text-right">长：</li>
                                            <li class="am-text-right"><?= $detail['length'] ?> <?= $set['size_mode']['unit'] ?></li>
                                        </ul>
                                            <ul class="am-avg-sm-2">
                                                <li class="am-text-right">宽：</li>
                                                <li class="am-text-right"><?= $detail['width'] ?> <?= $set['size_mode']['unit'] ?></li>
                                            </ul>
                                            <ul class="am-avg-sm-2">
                                                <li class="am-text-right">高：</li>
                                                <li class="am-text-right"><?= $detail['height'] ?> <?= $set['size_mode']['unit'] ?></li>
                                            </ul>
                                        <ul class="am-avg-sm-2">
                                            <li class="am-text-right">重量：</li>
                                            <li class="am-text-right"><?= $detail['weight'] ?> <?= $set['weight_mode']['unit'] ?>(<?= $set['weight_mode']['unit_name'] ?>)</li>
                                        </ul>
                                    </div>
                                </td>
                                <td>
                                       <p><?= $detail['storage']['shop_name'] ?></p>
                                       <p class="am-link-muted">(仓库id：<?= $detail['storage']['shop_id'] ?>)</p> 
                                </td>
                                <td>
                                       <p><?= $detail['country']?$detail['country']:'暂未选择' ?></p>
                                </td>
                                <td>
                                    <p>支付状态：
                                      <span class="am-badge <?=  $detail['is_pay']==1?"am-badge-success":"am-badge-danger" ?>"> <?=  $detail['is_pay']==1?"已支付":"未支付" ?></span>
                                    </p>
                                    <p>订单状态：
                                      <span class="am-badge am-badge-success"> <?=  $status[$detail['status']] ?></span>
                                    </p>
                                </td>
                                <td>
                                    <p>申请打包：
                                        <span> <?= $detail['created_time'] ?></span>
                                    </p>
                                    <p>更新时间：
                                      <span> <?=  $detail['updated_time'] ?></span>
                                    </p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- 订单信息 -->
                     <!-- 打包服务项目 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">打包服务项目</div>
                        <div class="am-fr tpl-table-black-operation">
                            <a id="j-inpack" href="javascript:void(0)">
                                <i class="am-icon-pencil"></i> 新增服务项目
                            </a>
                        </div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table width="100%" class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <thead>
                            <tr>
                                <th>项目ID</th>
                                <th>项目名称</th>
                                <th>项目类型</th>
                                <th>类目价格</th>
                                <th>数量</th>
                                <th>总价格</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php  foreach ($detail['service'] as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle"><?= $item['service']['name'] ?></td>
                                    <td class="am-text-middle"><?= $item['service']['type']==0?'固定金额':'运费百分比' ?></td>
                                    <td class="am-text-middle"><?= $item['service']['type']==0?$item['service']['price']:$item['service']['percentage'] ?></td>
                                    <td class="am-text-middle"><?= $item['service_sum'] ?></td>
                                    <td class="am-text-middle"><?= $item['service']['type']==0?$item['service_sum']*$item['service']['type']==0?$item['service']['price']:$item['service']['percentage']:'' ?></td>
                                    <td class="am-text-middle"><a href="javascript:void(0);" class="item-deletet tpl-table-black-operation-del" data-id="<?= $item['id'] ?>" ><i class="am-icon-trash"></i> 删除</a></td>
                                </tr>
                            <?php endforeach?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- 日志信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">物流记录</div>
                        <div class="am-fr tpl-table-black-operation">
                            <a href="<?= url('store/trOrder/logistics', ['id' =>$detail['id']]) ?>">
                                <i class="am-icon-pencil"></i> 物流更新
                            </a>
                        </div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table width="100%" class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>状态值</th>
                                <th>状态名</th>
                                <th>内容</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php  foreach ($detail['log'] as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle"><?= $item['status'] ?></td>
                                    <td class="am-text-middle"><?= $item['status_cn'] ?></td>
                                    <td class="am-text-middle"><?= $item['logistics_describe'] ?></td>
                                    <td class="am-text-middle"><?= $item['created_time'] ?></td>
                                    <td class="am-text-middle"><a href="javascript:void(0);" class="item-delete tpl-table-black-operation-del" data-id="<?= $item['id'] ?>" ><i class="am-icon-trash"></i> 删除</a></td>
                                </tr>
                            <?php endforeach?>
                            </tbody>
                        </table>
                    </div>
                    
                     <!-- 包裹信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">包裹信息</div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table width="100%" class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <thead>
                            <tr>
                                <th>包裹ID</th>
                                <th>包裹单号</th>
                                <th>快递公司</th>
                                <th>类目名称</th>
                                <th>数量</th>
                                <th>单位重量</th>
                                <th>商品价值</th>
                                <th>备注</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php  foreach ($packageItem as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['order_id'] ?></td>
                                    <td class="am-text-middle"><?= $item['express_num'] ?></td>
                                    <td class="am-text-middle"><?= $item['express_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['class_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['product_num'] ?></td>
                                    <td class="am-text-middle"><?= $item['product_num'] ?></td>
                                    <td class="am-text-middle"><?= $item['all_price'] ?></td>
                                    <td class="am-text-middle"><?= $item['product_num'] ?></td>
                                    <td class="am-text-middle"><a href="javascript:void(0);" class="item-deletet tpl-table-black-operation-del" data-id="<?= $item['id'] ?>" ><i class="am-icon-trash"></i> 删除</a></td>
                                </tr>
                            <?php endforeach?>
                            </tbody>
                        </table>
                    </div>
                    
                    
                    
                    
                    
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">订单图片</div>
                    </div>
                    
                    <figure style="display:inline-flex;" data-am-widget="figure" class="am am-figure am-figure-default "   data-am-figure="{  pureview: 'true' }">
                            <?php  foreach ($detail['inpackimage'] as $item): ?>
                            <a href="<?= $item['file_path'] ?>"><img style="max-width: 200px;max-height: 200px;" src="<?= $item['file_path'] ?>"/></a>
                            <?php endforeach?>
                       </figure>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<script id="tpl-inpack" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">
                        选择包装服务
                    </label>
                    <div class="am-u-sm-8 am-u-end">
                      <select name="inpack[service_id]" data-am-selected="{btnSize: 'sm', placeholder: '请选择服务项目'}">
                        <?php foreach ($packageService as $item): ?>
                                <option value="<?= $item['id'] ?>"><?= $item['name'] ?></option>
                            <?php endforeach; ?>
                            </select>
                    </div>
                </div>
            </div>
            <input type="hidden" name="inpack[id]" value="<?= $detail['id'] ?>"/>
            <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 服务项目数量 </label>
                    <div class="am-u-sm-8 am-u-end">
                             <select name="inpack[service_sum]" data-am-selected="{btnSize: 'sm', placeholder: '请选择服务项目'}">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </select>
                    </div>
            </div>
        </form>
    </div>
</script>
<script>
 $(function () {
     
        /**
         * 新增服务项目
         */
        $('#j-inpack').on('click', function () {
            var $tabs, data = $(this).data();
            $.showModal({
                title: '新增服务项目'
                , area: '460px'
                , content: template('tpl-inpack', data)
                , uCheck: true
                , success: function ($content) {
                    $tabs = $content.find('.j-tabs');
                    $tabs.tabs({noSwipe: 1});
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('store/InpackService/add') ?>',
                        data: {
                            service_id:data.selectId,
                        }
                    });
                    return true;
                }
            });
        });
        // 删除元素
        var url = "<?= url('store/Logistics/delete') ?>";
        $('.item-delete').delete('id', url);
        
        var url = "<?= url('store/PackageItem/delete') ?>";
        $('.item-deletet').delete('id', url);
        
        var urle = "<?= url('store/InpackService/delete') ?>";
        $('.item-deletet').delete('id', urle);
 });
</script>