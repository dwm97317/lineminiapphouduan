<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl"> 编辑分销商</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 分销商ID </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end am-form--static">
                                    <span><?= $model['user_id'] ?></span>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 微信头像 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end am-form--static">
                                    <img width="60" height="60" src="<?= $model['user']['avatarUrl'] ?>" alt="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 姓名 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="model[real_name]"
                                           value="<?= $model['real_name'] ?>" placeholder="请输入分销商姓名" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 手机号 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="model[mobile]"
                                           value="<?= $model['mobile'] ?>" placeholder="请输入分销商手机号" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 成为时间 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end am-form--static">
                                    <span><?= $model['create_time'] ?></span>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-sm am-btn-secondary">提交
                                    </button>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                             <div class="widget-head am-cf">
                                <div class="widget-title am-fl"> 白牌设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">品牌名称 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="model[brand_name]"
                                           value="<?= isset($model['user']['brand_name']) ? $model['user']['brand_name'] : '' ?>" placeholder="请输入代理商品牌名称">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">品牌 Logo </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <div class="am-form-file">
                                        <div class="am-form-file">
                                            <button type="button"
                                                    class="upload-file am-btn am-btn-secondary am-radius">
                                                <i class="am-icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div class="uploader-list am-cf">
                                                <?php if (isset($model['user']['brandLogo']) && !empty($model['user']['brandLogo'])): ?>
                                                    <div class="file-item">
                                                        <a href="<?= $model['user']['brandLogo']['file_path'] ?>" title="点击查看大图" target="_blank">
                                                            <img src="<?= $model['user']['brandLogo']['file_path'] ?>">
                                                        </a>
                                                        <input type="hidden" name="model[brand_logo_id]"
                                                               value="<?= $model['user']['brand_logo_id'] ?>">
                                                        <i class="iconfont icon-shanchu file-item-delete"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="help-block">
                                            <small>建议尺寸：200x200像素</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">主题色 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="color" class="tpl-form-input" name="model[theme_color]"
                                           value="<?= isset($model['user']['theme_color']) ? $model['user']['theme_color'] : '#000000' ?>" style="height: 40px; width: 100px;">
                                </div>
                            </div>
                             <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-sm am-btn-secondary">提交
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

<!-- 图片文件列表模板 -->
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script>
    $(function () {

        // 选择图片
        $('.upload-file').selectImages({
            name: 'model[brand_logo_id]'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
