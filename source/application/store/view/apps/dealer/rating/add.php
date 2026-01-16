<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加分销等级</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 等级名称 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="grade[name]"
                                           value="" placeholder="请输入等级名称" required>
                                    <small>例如：普通等级、团长、渠道代理商、高级营销顾问</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 等级权重 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <div class="x-region-select">
                                        <select name="grade[weight]" style="width: 20rem;" required>
                                            <option value="">请选择等级权重</option>
                                            <?php for ($i = 1; $i <= 50; $i++): ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="help-block">
                                        <small>会员等级的权重，数字越大 等级越高</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 升级条件 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <div class="am-input-group">
                                        <span class="am-input-group-label am-input-group-label__left">推广佣金满</span>
                                        <input type="number" name="grade[upgrade][expend_money]"
                                               class="am-form-field" min="0.01" required>
                                        <span class="widget-dealer__unit am-input-group-label am-input-group-label__right">元</span>
                                    </div>
                                    <div class="help-block">
                                        <small>用户的推广佣金额满n元后，自动升级</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 佣金比例 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <div class="am-input-group">
                                        <span class="am-input-group-label am-input-group-label__left">一级</span>
                                        <input type="number" name="grade[setting][first_money]" class="am-form-field"
                                               max="100" min="0" required>
                                        <span class="widget-dealer__unit am-input-group-label am-input-group-label__right">%</span>
                                    </div>
                                    <div style="padding:5px 0px;"></div>
                                    <div class="am-input-group">
                                        <span class="am-input-group-label am-input-group-label__left">二级</span>
                                        <input type="number" name="grade[setting][second_money]" class="am-form-field"
                                               max="100" min="0" required>
                                        <span class="widget-dealer__unit am-input-group-label am-input-group-label__right">%</span>
                                    </div>
                                    <div class="help-block">
                                        <small>佣金比例范围0-100，10代表该等级的推广人推广的订单按10%提成，0表示不算提成</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 等级状态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="grade[status]" value="1" data-am-ucheck
                                               checked>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="grade[status]" value="0" data-am-ucheck>
                                        禁用
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary"> 提交
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
