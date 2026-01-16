<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">拼团设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否开启拼团 </label>
                                <div class="am-u-sm-9 am-u-end">
                                      <label class="am-radio-inline">
                                        <input type="radio" name="share[is_open]" value="1" data-am-ucheck
                                               <?= $data['is_open'] == '1' ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="share[is_open]" value="0" data-am-ucheck <?= $data['is_open'] == '0' ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                </div>
                            </div>  
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否允许团长参与拼团 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="share[is_own_join]" value="1" data-am-ucheck
                                                <?= $data['is_own_join'] == '1' ? 'checked' : '' ?>>
                                        允许
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="share[is_own_join]" value="0" data-am-ucheck  <?= $data['is_own_join'] == '0' ? 'checked' : '' ?>>
                                        不允许
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 用户参与拼团是否需要审核 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="share[is_shenhe]" value="1" data-am-ucheck
                                                <?= $data['is_shenhe'] == '1' ? 'checked' : '' ?>>
                                        需要
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="share[is_shenhe]" value="0" data-am-ucheck  <?= $data['is_shenhe'] == '0' ? 'checked' : '' ?>>
                                        不需要
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 团长发布拼团是否审核 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="share[is_verify]" value="1" data-am-ucheck
                                                <?= $data['is_verify'] == '1' ? 'checked' : '' ?>>
                                        审核
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="share[is_verify]" value="0" data-am-ucheck  <?= $data['is_verify'] == '0' ? 'checked' : '' ?>>
                                        不审核
                                    </label>
                                </div>
                            </div>
         
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 拼团规则 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <textarea id="container" name="share[describe]"
                                              type="text/plain"><?= $data['describe'] ?></textarea>
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

<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/store/js/select.region.js?v=1.2"></script>

<script>

    $(function () {

        // 选择图片
        $('.upload-file').selectImages({
            name: 'shop[logo_image_id]'
        });
        
        
        // 富文本编辑器
        UM.getEditor('container', {
            initialFrameWidth: 375 + 15,
            initialFrameHeight: 400
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
