<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑仓库分红规则</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 仓库名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" disabled="true"
                                           placeholder="请输入仓库名称" value="<?= $model['shop']['shop_name'] ?>" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 线路名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" disabled="true"
                                           placeholder="请输入仓库名称" value="<?= $model['line']['name'] ?>" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 金额/比例 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="bonus[proportion]"
                                           placeholder="请输入金额/比例" value="<?= $model['proportion'] ?>" required>
                                    <small>请填写0-100的正数，如：10</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 分成类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bonus[bonus_type]" value="0" data-am-ucheck
                                            <?= $model['bonus_type'] == 0 ? 'checked' : '' ?>>
                                        固定金额
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bonus[bonus_type]" value="1" data-am-ucheck
                                            <?= $model['bonus_type'] == 1 ? 'checked' : '' ?>>
                                        比例分成
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 寄取类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bonus[sr_type]" value="0" data-am-ucheck
                                            <?= $model['sr_type'] == 0 ? 'checked' : '' ?>>
                                        寄件
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bonus[sr_type]" value="1" data-am-ucheck
                                            <?= $model['sr_type'] == 1 ? 'checked' : '' ?>>
                                        取件
                                    </label>
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
