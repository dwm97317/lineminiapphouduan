<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">邮件通知（QQ邮箱SMTP）</div>
                            </div>
                            <!--<input type="hidden" name="email[default]" value="aliyun">-->
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> SMTP服务器用户名 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="email[setting][Username]"
                                           value="<?= $values['setting']['Username'] ?>" required>
                                           <small>提示：请使用QQ邮箱配置邮箱发送服务器，<a target="_blank" href="https://jingyan.baidu.com/article/9faa7231de5852473c28cbdd.html">教程地址 </a></small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> SMTP服务器密码 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input"
                                           name="email[setting][Password]"
                                           value="<?= $values['setting']['Password'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 回件地址(用户回复邮件地址) </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input"
                                           name="email[setting][replyEmail] "
                                           value="<?= $values['setting']['replyEmail'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 邮件签名 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="email[setting][replyName]"
                                           value="<?= $values['setting']['replyName'] ?>" required>
                                           <small>提示：用户看到的来源名称，不填则默认为小思集运</small>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">邮箱通知模板设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启邮件提醒
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="email[is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="email[is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                       
                                    </label>
                                    
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                     <span class="tpl-form-line-small-title"><?= $values['template']['valide']['theme'] ?></span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input"
                                           name="email[template][valide][value]"
                                           value="<?= $values['template']['valide']['value'] ?>">
                                    
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3">
                                    <small>您的邮箱验证码是${code}，请注意查收！</small>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                     <span class="tpl-form-line-small-title"><?= $values['template']['status']['theme'] ?></span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input"
                                           name="email[template][status][value]"
                                           value="<?= $values['template']['status']['value'] ?>">
                                    
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3">
                                    <small>您的包裹${code}物流状态已变更为${message}，更多信息可通过用户端查询，感谢您的支持，祝您开心每一天！！</small>
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

        /**
         * 发送测试短信
         */
        $('.j-sendTestMsg').click(function () {
            var msgType = $(this).data('msg-type')
                , formData = {
                AccessKeyId: $('input[name="sms[engine][aliyun][AccessKeyId]"]').val()
                , AccessKeySecret: $('input[name="sms[engine][aliyun][AccessKeySecret]"]').val()
                , sign: $('input[name="sms[engine][aliyun][sign]"]').val()
                , msg_type: msgType
                , template_code: $('input[name="sms[engine][aliyun][' + msgType + '][template_code]"]').val()
                , accept_phone: $('input[name="sms[engine][aliyun][' + msgType + '][accept_phone]"]').val()
            };
            if (!formData.AccessKeyId.length) {
                layer.msg('请填写 AccessKeyId');
                return false;
            }
            if (!formData.AccessKeySecret.length) {
                layer.msg('请填写 AccessKeySecret');
                return false;
            }
            if (!formData.sign.length) {
                layer.msg('请填写 短信签名');
                return false;
            }
            if (!formData.template_code.length) {
                layer.msg('请填写 模板ID');
                return false;
            }
            if (!formData.accept_phone.length) {
                layer.msg('请填写 接收手机号');
                return false;
            }
            layer.confirm('确定要发送测试短信吗', function (index) {
                var load = layer.load();
                var url = "<?= url('setting/smsTest') ?>";
                $.post(url, formData, function (result) {
                    layer.msg(result.msg);
                    layer.close(load);
                });
                layer.close(index);
            });
        });

    });
</script>
