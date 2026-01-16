<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加轮播图</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">轮播标题 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="banner[title]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">轮播跳转路径 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="banner[url]"
                                           value="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">轮播跳转类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="banner[redirect_type]" value="1" data-am-ucheck
                                               checked>
                                        小程序内部链接
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="banner[redirect_type]" value="2" data-am-ucheck>
                                        外部链接
                                    </label>
                                     <div class="help-block am-padding-top-xs">
                                        <small>请点击<a target="_blank" href="index.php?s=/store/wxapp.page/links">内部链接地址</a>查看地址</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">轮播位置 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="banner[banner_site]" value="10" data-am-ucheck
                                               checked>
                                        小程序头部
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="banner[banner_site]" value="20" data-am-ucheck>
                                        小程序中间广告图
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="banner[banner_site]" value="30" data-am-ucheck>
                                        拼团页头部
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="banner[banner_site]" value="40" data-am-ucheck>
                                        弹窗公告
                                    </label>
                                    <!--<label class="am-radio-inline">-->
                                    <!--    <input type="radio" name="banner[banner_site]" value="30" data-am-ucheck>-->
                                    <!--    PC端(预留)-->
                                    <!--</label>-->
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">轮播图片 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">分类排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="banner[sort]"
                                           value="100">
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">轮播标题 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="banner[status]" value="1" data-am-ucheck
                                               checked>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="banner[status]" value="2" data-am-ucheck>
                                        禁用
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

<!-- 图片文件列表模板 -->
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script>
   $(function () {

        // 选择图片
        $('.upload-file').selectImages({
            name: 'banner[image_id]'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
