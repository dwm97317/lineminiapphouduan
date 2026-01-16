<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="tips am-margin-top-sm">
                                <div class="pre">
                                    <p class="">智能AI识别能够通过包裹图片识别出包裹所属的用户，并自动将包裹与用户进行关联，如需开通请联系开发者</p>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">包裹所属用户识别</div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 剩余识别次数 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-1 am-u-end">
                                    <input type="text" disabled="true" class="tpl-form-input" value="<?= $values['baiduai'] ?>">
                                </div>
                                <div class="help-block">
                                            <small><a href="index.php?s=/store/setting/aiidentifylog">查看识别记录</a></small>
                                        </div>
                                
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 百度AI识别 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                     <div class="am-u-sm-9">
                                        <label class="am-radio-inline">
                                            <input type="radio" name="aiidentify[is_enable]" value="1"
                                                   data-am-ucheck
                                                <?= $values['is_enable'] == '1' ? 'checked' : '' ?>
                                                   required>
                                            开启
                                        </label>
                                        <label class="am-radio-inline">
                                            <input type="radio" name="aiidentify[is_enable]" value="0"
                                                   data-am-ucheck
                                                <?= $values['is_enable'] == '0' ? 'checked' : '' ?>>
                                            关闭
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> APIKEY </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input" name="aiidentify[apikey]"
                                               value="<?= $values['apikey'] ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> APISECRET </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input" name="aiidentify[apisecret]"
                                               value="<?= $values['apisecret'] ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 关键词 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <div class="am-u-sm-3">
                                        <input type="text" class="tpl-form-input" name="aiidentify[keyword1]"
                                               value="<?= $values['keyword1'] ?>" required>
                                    </div>
                                    <div class="am-u-sm-3" style="float:left;">
                                        <input type="text" class="tpl-form-input" name="aiidentify[keyword2]"
                                               value="<?= $values['keyword2'] ?>" required>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="am-form-group">
                               <label class="am-u-sm-3 am-u-lg-2 am-form-label"></label>
                                    <small>关键词请勿使用容易被识别错误的字母或数字如：0,O,Q,D,I,J,L等,一旦存在此类字母数字，可能会大幅度降低识别的准确度，影响功能体验,建议使用双字母，如AA，KK，WE，或汉字词语如：编号，集货，发财等作为关键词1;</small>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 电子秤AI识别 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                     <div class="am-u-sm-9">
                                        <label class="am-radio-inline">
                                            <input type="radio" name="aiidentify[is_dianzicheng]" value="1"
                                                   data-am-ucheck
                                                <?= $values['is_dianzicheng'] == '1' ? 'checked' : '' ?>
                                                   required>
                                            开启
                                        </label>
                                        <label class="am-radio-inline">
                                            <input type="radio" name="aiidentify[is_dianzicheng]" value="0"
                                                   data-am-ucheck
                                                <?= $values['is_dianzicheng'] == '0' ? 'checked' : '' ?>>
                                            关闭
                                        </label>
                                        <div class="help-block">
                                            <small>注：开启后可以在电子秤拍照上传面单时，系统将自动识别面单上的用户编号</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 后台管理端AI识别 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                     <div class="am-u-sm-9">
                                        <label class="am-radio-inline">
                                            <input type="radio" name="aiidentify[is_houtaiduan]" value="1"
                                                   data-am-ucheck
                                                <?= $values['is_houtaiduan'] == '1' ? 'checked' : '' ?>
                                                   required>
                                            开启
                                        </label>
                                        <label class="am-radio-inline">
                                            <input type="radio" name="aiidentify[is_houtaiduan]" value="0"
                                                   data-am-ucheck
                                                <?= $values['is_houtaiduan'] == '0' ? 'checked' : '' ?>>
                                            关闭
                                        </label>
                                        <div class="help-block">
                                            <small>注：开启后可以在后台管理端拍照上传面单，系统将自动识别面单上的用户编号</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 仓管端AI识别 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                     <div class="am-u-sm-9">
                                        <label class="am-radio-inline">
                                            <input type="radio" name="aiidentify[is_cangguanduan]" value="1"
                                                   data-am-ucheck
                                                <?= $values['is_cangguanduan'] == '1' ? 'checked' : '' ?>
                                                   required>
                                            开启
                                        </label>
                                        <label class="am-radio-inline">
                                            <input type="radio" name="aiidentify[is_cangguanduan]" value="0"
                                                   data-am-ucheck
                                                <?= $values['is_cangguanduan'] == '0' ? 'checked' : '' ?>>
                                            关闭
                                        </label>
                                        <div class="help-block">
                                            <small>注：开启后可以在仓管端拍照上传面单，系统将自动识别面单上的用户编号</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="shuttle" class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    
                                    <button type="submit"
                                            class="j-submit am-btn am-btn-sm am-btn-secondary am-margin-right-sm">保存
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

        // 一键配置
        $('.j-shuttle').on('click', function () {
            var url = "<?= url('wxapp.submsg/shuttle') ?>";
            var load = layer.load();
            layer.confirm('该操作将自动为您的小程序添加订阅消息<br>请先确保 "订阅消息" - "我的模板" 中没有记录<br>确定添加吗？', {
                title: '友情提示'
            }, function (index) {
                $.post(url, {}, function (result) {
                    result.code === 1 ? $.show_success(result.msg, result.url)
                        : $.show_error(result.msg);
                    layer.close(load);
                });
                layer.close(index);
            });
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
