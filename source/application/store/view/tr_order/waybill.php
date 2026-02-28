<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <input type="hidden" name="sf[inpack_id]" value="<?= $detail['id'] ?>">
                    
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">生成顺丰运单 (丰桥)</div>
                            </div>
                            
                            <!-- 订单简要 -->
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">集运单号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-content" style="padding-top: 7px;">
                                        <?= $detail['order_sn'] ?>
                                    </div>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">收件人 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-content" style="padding-top: 7px;">
                                        <?= $detail['address']['name'] ?> (<?= $detail['address']['phone'] ?>) <br>
                                        <?= $detail['address']['region']['province'] ?> <?= $detail['address']['region']['city'] ?> <?= $detail['address']['region']['region'] ?> <?= $detail['address']['detail'] ?>
                                    </div>
                                </div>
                            </div>

                            <!-- 快递产品选择 -->
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">快递服务 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="sf[express_service_id]" required
                                            data-am-selected="{searchBox: 1, btnSize: 'sm',  placeholder:'请选择快递服务'}">
                                        <option value=""></option>
                                        <?php if (isset($expressServices['10'])): ?>
                                            <?php foreach ($expressServices['10'] as $item): ?>
                                                <option value="<?= $item['service_id'] ?>">
                                                    <?= $item['service_name'] ?> (<?= $item['service_code'] ?>)
                                                    <?= $item['price'] > 0 ? '+'.$item['price'].'元' : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <small>请选择基础运输产品（必选）</small>
                                </div>
                            </div>

                            <!-- 增值服务选择 -->
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">增值服务 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <?php if (isset($expressServices['20']) && !empty($expressServices['20'])): ?>
                                        <?php foreach ($expressServices['20'] as $item): ?>
                                            <div class="am-checkbox">
                                                <label>
                                                    <input type="checkbox" name="sf[vas][<?= $item['service_id'] ?>]" value="1" 
                                                        <?= $item['is_check'] ? 'checked' : '' ?>> 
                                                    <?= $item['service_name'] ?> (<?= $item['service_code'] ?>)
                                                    
                                                    <!-- 如果服务代码是 INSURE 或 COD，显示金额输入框 -->
                                                    <?php if (in_array(strtoupper($item['service_code']), ['INSURE', 'COD'])): ?>
                                                    <input type="number" class="am-form-field am-input-sm" 
                                                           style="display:inline-block; width: 100px; margin-left: 10px;"
                                                           name="sf[vas][<?= $item['service_id'] ?>]" 
                                                           placeholder="输入金额" >
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="am-text-grey" style="padding-top: 7px;">暂无增值服务可选</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">包裹重量 (kg) </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="sf[weight]" step="0.01"
                                           value="<?= $detail['weight'] ?>" required>
                                </div>
                            </div>
                           
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <div style="margin-right:20px;" onclick="javascript :history.back(-1);" class="am-btn am-btn-secondary">返回</div>
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">确认生成运单</button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm({
            // form data
            buildData: function () {
                return {};
            },
            // 自定义url
            url: '<?= url("store/trOrder/createSfOrder") ?>'
        });
    });
</script>
