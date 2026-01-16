<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">语言设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    默认语言
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-checkbox-inline">
                                        <input type="radio" name="lang[default]" value="zhHans"
                                               data-am-ucheck <?= $lang['default'] == 'zhHans' ? 'checked' : '' ?>>
                                        简体中文
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="radio" name="lang[default]" value="en"
                                               data-am-ucheck <?= $lang['default'] == 'en' ? 'checked' : '' ?>>
                                        英文
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="radio" name="lang[default]" value="zhHant"
                                               data-am-ucheck <?= $lang['default'] == 'zhHant' ? 'checked' : '' ?>>
                                        繁体中文
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="radio" name="lang[default]" value="thai"
                                               data-am-ucheck <?= $lang['default'] == 'thai' ? 'checked' : '' ?>>
                                        泰语
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="radio" name="lang[default]" value="vietnam"
                                               data-am-ucheck <?= $lang['default'] == 'vietnam' ? 'checked' : '' ?>>
                                        越南语
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="lang[zhHant]" value="1"
                                               data-am-ucheck <?= $lang['zhHant'] == 1 ? 'checked' : '' ?>>
                                        繁体中文
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="lang[en]" value="1"
                                               data-am-ucheck <?= $lang['en'] == 1 ? 'checked' : '' ?>>
                                        英文
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="lang[thai]" value="1"
                                               data-am-ucheck <?= $lang['thai'] == 1 ? 'checked' : '' ?>>
                                        泰语
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="lang[vietnam]" value="1"
                                               data-am-ucheck <?= $lang['vietnam'] == 1 ? 'checked' : '' ?>>
                                        越南语
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <?php if (checkPrivilege('wxapp.setting/lang')): ?>
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交</button>
                                    <?php endif; ?>
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
