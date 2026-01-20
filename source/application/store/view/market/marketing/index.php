<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">营销设置</div>
                            </div>
                            
                            <div class="am-tabs" data-am-tabs>
                                <ul class="am-tabs-nav am-nav am-nav-tabs">
                                    <li class="am-active"><a href="#tab1">增值服务推荐 (Upsell)</a></li>
                                    <li><a href="#tab2">代理商白牌化 (Agent)</a></li>
                                </ul>

                                <div class="am-tabs-bd">
                                    <!-- Upsell Settings -->
                                    <div class="am-tab-panel am-fade am-in am-active" id="tab1">
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-u-lg-2 am-form-label">推荐锁定超时 (小时)</label>
                                            <div class="am-u-sm-9 am-u-end">
                                                <input type="number" class="tpl-form-input" name="marketing[upsell][timeout]"
                                                       value="<?= $values['upsell']['timeout'] ?>">
                                                <small>推荐发出后，包裹锁定多少小时等待用户确认。超时即释放。</small>
                                            </div>
                                        </div>
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-u-lg-2 am-form-label">强制上传凭证</label>
                                            <div class="am-u-sm-9 am-u-end">
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="marketing[upsell][require_proof]" value="1" data-am-ucheck
                                                        <?= $values['upsell']['require_proof'] == 1 ? 'checked' : '' ?>> 开启
                                                </label>
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="marketing[upsell][require_proof]" value="0" data-am-ucheck
                                                        <?= $values['upsell']['require_proof'] == 0 ? 'checked' : '' ?>> 关闭
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Agent Settings -->
                                    <div class="am-tab-panel am-fade" id="tab2">
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-u-lg-2 am-form-label">开启白牌化功能</label>
                                            <div class="am-u-sm-9 am-u-end">
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="marketing[agent][white_label_enable]" value="1" data-am-ucheck
                                                        <?= $values['agent']['white_label_enable'] == 1 ? 'checked' : '' ?>> 开启
                                                </label>
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="marketing[agent][white_label_enable]" value="0" data-am-ucheck
                                                        <?= $values['agent']['white_label_enable'] == 0 ? 'checked' : '' ?>> 关闭
                                                </label>
                                            </div>
                                        </div>
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-u-lg-2 am-form-label">最低可用等级</label>
                                            <div class="am-u-sm-9 am-u-end">
                                                <select name="marketing[agent][min_level]" data-am-selected="{btnSize: 'sm'}">
                                                    <option value="0" <?= $values['agent']['min_level'] == 0 ? 'selected' : '' ?>>无限制</option>
                                                    <option value="10" <?= $values['agent']['min_level'] == 10 ? 'selected' : '' ?>>VIP 1</option>
                                                    <option value="20" <?= $values['agent']['min_level'] == 20 ? 'selected' : '' ?>>VIP 2</option>
                                                    <option value="30" <?= $values['agent']['min_level'] == 30 ? 'selected' : '' ?>>VIP 3</option>
                                                </select>
                                                <small>仅允许达到此等级及以上的代理商使用白牌化功能</small>
                                            </div>
                                        </div>
                                    </div>
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
        $('#my-form').superForm();
    });
</script>
