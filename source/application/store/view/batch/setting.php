<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">批次规则</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否加入首字母
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="batchrule[firstword_mode]" value="1"
                                               data-am-ucheck  <?= $values['firstword_mode'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="batchrule[firstword_mode]" value="0"
                                               data-am-ucheck <?= $values['firstword_mode'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 首字母 </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="batchrule[firstword]"
                                           value="<?= $values['firstword'] ?>" >
                                </div>
                            </div>
            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否加入时间
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="batchrule[ftime_mode]" value="1"
                                               data-am-ucheck  <?= $values['ftime_mode'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="batchrule[ftime_mode]" value="0"
                                               data-am-ucheck <?= $values['ftime_mode'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    尾数规则
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="batchrule[random]" value="0"
                                               data-am-ucheck  <?= $values['random'] == 0 ? 'checked' : '' ?>>
                                        随机模式
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="batchrule[random]" value="1"
                                               data-am-ucheck <?= $values['random'] == 1 ? 'checked' : '' ?>>
                                        顺序模式
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    自动更新物流
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="batchrule[is_autolog]" value="1"
                                               data-am-ucheck <?= $values['is_autolog'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="batchrule[is_autolog]" value="0"
                                               data-am-ucheck  <?= $values['is_autolog'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                               <label class="am-u-sm-3 am-form-label"></label>
                                    <small>注：如以上条件全部开启，则生成批次号为 字母+时间+尾数。如JY20230504-0001</small>
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
