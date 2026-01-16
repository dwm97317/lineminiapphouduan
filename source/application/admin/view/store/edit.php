<div class="row">
    <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
        <div class="widget am-cf">
            <form id="my-form" class="am-form tpl-form-line-form" method="post">
                <div class="widget-body">
                    <fieldset>
                        <div class="widget-head am-cf">
                            <div class="widget-title am-fl">编辑账号</div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 商家名称 </label>
                            <div class="am-u-sm-9 am-u-end">
                                <input type="text" class="tpl-form-input" name="store[store_name]"   value="<?= $data['store_name'] ?>"
                                       required>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 版本 </label>
                            <div class="am-u-sm-9 am-u-end">
                                <input type="text" class="tpl-form-input" name="store[version]"   value="<?= $data['version'] ?>"
                                       required>
                            </div>
                        </div>
                        <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否展示版权信息 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[copyright]" value="0" <?= $data['copyright'] == 0 ? 'checked' : '' ?> 
                                               data-am-ucheck>
                                        否
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[copyright]" value="1" <?= $data['copyright'] == 1 ? 'checked' : '' ?> data-am-ucheck>
                                        是
                                    </label>
                                </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 版权说明 </label>
                            <div class="am-u-sm-9 am-u-end">
                                <input type="text" class="tpl-form-input" name="store[copyright_des]"   value="<?= $data['copyright_des'] ?>">
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 版权联系电话 </label>
                            <div class="am-u-sm-9 am-u-end">
                                <input type="text" class="tpl-form-input" name="store[copyright_phone]"   value="<?= $data['copyright_phone'] ?>">
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 备案号 </label>
                            <div class="am-u-sm-9 am-u-end">
                                <input type="text" class="tpl-form-input" name="store[filing_number]"   value="<?= $data['filing_number'] ?>">
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 有效期至 </label>
                            <div class="am-u-sm-9 am-u-end">
                               <input type="text" name="store[end_time]" autocomplete="off"
                                               class="am-form-field"
                                               value="<?= $data['end_time'] ?>" placeholder="<?= $data['end_time'] ?>"
                                               data-am-datepicker>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-u-lg-2 am-form-label"> AI识别剩余次数 </label>
                            <div class="am-u-sm-9 am-u-end">
                                <input type="text" class="tpl-form-input" name="store[baiduai]"   value="<?= $data['baiduai'] ?>">
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
<script>
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
