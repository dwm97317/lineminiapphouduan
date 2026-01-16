<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加服务项目</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">项目名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="package[name]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">项目收费模式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="package[type]" onclick="switchLineMode(this)"   value="0" data-am-ucheck
                                               checked>
                                        固定金额
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="package[type]" onclick="switchLineMode(this)"   value="1" data-am-ucheck>
                                        按运费的百分比收费
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group xmfree">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">项目价格 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input   type="text" class="tpl-form-input" name="package[price]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="am-form-group xmfrees" style="display:none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">项目收费百分比 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input   type="text" class="tpl-form-input" name="package[percentage]"
                                           value="" required>
                                    <div class="help-block">
                                        <small>注：请填写0-100的数字</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">项目说明</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="package[service_des]"
                                           value="" required>
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
    function switchLineMode(_this){
        var _mode = _this.value;
        console.log(_mode);
        $('.xmfree').css('display','none');
        $('.xmfrees').css('display','none');
        if(_mode==0){
            var freeMode = '.xmfree';
        }
        if(_mode==1){
            var freeMode = '.xmfrees';
        }
        $(freeMode).css('display','block');
    }
    
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
