<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">基础设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">启用集运拼团 </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="sharing_logistics[enable]" value="1"
                                            <?= $setting['enable'] == 1 ? 'checked' : '' ?> data-am-ucheck> 启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="sharing_logistics[enable]" value="0"
                                            <?= $setting['enable'] == 0 ? 'checked' : '' ?> data-am-ucheck> 禁用
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">拼团支付模式 </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="sharing_logistics[group_pay_mode]" value="10"
                                            <?= !isset($setting['group_pay_mode']) || $setting['group_pay_mode'] == 10 ? 'checked' : '' ?> data-am-ucheck> 先拼后付(推荐)
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="sharing_logistics[group_pay_mode]" value="20"
                                            <?= isset($setting['group_pay_mode']) && $setting['group_pay_mode'] == 20 ? 'checked' : '' ?> data-am-ucheck> 立减预付
                                    </label>
                                    <div class="help-block">
                                        <small>先拼后付：截单后统一计算价格，运单锁定；立减预付：加入时按目标价立即修改运单价格并支付</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label">拼团说明 </label>
                                <div class="am-u-sm-9">
                                    <textarea class="am-form-field" name="sharing_logistics[description]"
                                              placeholder="请输入集运拼团说明" rows="3"><?= $setting['description'] ?></textarea>
                                </div>
                            </div>

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">团长资格设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label">最低保证金 (元) </label>
                                <div class="am-u-sm-9">
                                    <input type="number" class="tpl-form-input" name="sharing_logistics[min_deposit]"
                                           value="<?= $setting['min_deposit'] ?>">
                                    <small>团长发起拼团时需缴纳的最低保证金金额</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label">佣金比例 (%) </label>
                                <div class="am-u-sm-9">
                                    <input type="number" class="tpl-form-input" name="sharing_logistics[commission_rate]"
                                           value="<?= $setting['commission_rate'] ?>">
                                    <small>团长可获得的佣金比例（占总运费）</small>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                    </button>
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
        $('#my-form').superForm();
    });
</script>
