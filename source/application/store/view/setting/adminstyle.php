<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">包裹入库功能设置</div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    唛头设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_usermark]" value="1"
                                               data-am-ucheck  <?= $values['is_usermark'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_usermark]" value="0"
                                               data-am-ucheck <?= $values['is_usermark'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_usermark]" value="1" data-am-ucheck
                                            <?= $values['is_force_usermark']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    国家设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_country]" value="1"
                                               data-am-ucheck  <?= $values['is_country'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_country]" value="0"
                                               data-am-ucheck <?= $values['is_country'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_country]" value="1" data-am-ucheck
                                            <?= $values['is_force_country']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    仓库设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_shop]" value="1"
                                               data-am-ucheck  <?= $values['is_shop'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_shop]" value="0"
                                               data-am-ucheck <?= $values['is_shop'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_shop]" value="1" data-am-ucheck
                                            <?= $values['is_force_shop']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    物流设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_express]" value="1"
                                               data-am-ucheck  <?= $values['is_express'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_express]" value="0"
                                               data-am-ucheck <?= $values['is_express'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_express]" value="1" data-am-ucheck
                                            <?= $values['is_force_express']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    包裹信息设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_packinfo]" value="1"
                                               data-am-ucheck  <?= $values['is_packinfo'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_packinfo]" value="0"
                                               data-am-ucheck <?= $values['is_packinfo'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_packinfo]" value="1" data-am-ucheck
                                            <?= $values['is_force_packinfo']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    总价值设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_totalvalue]" value="1"
                                               data-am-ucheck  <?= $values['is_totalvalue'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_totalvalue]" value="0"
                                               data-am-ucheck <?= $values['is_totalvalue'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_totalvalue]" value="1" data-am-ucheck
                                            <?= $values['is_force_totalvalue']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    物品品类设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_category]" value="1"
                                               data-am-ucheck  <?= $values['is_category'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_category]" value="0"
                                               data-am-ucheck <?= $values['is_category'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_category]" value="1" data-am-ucheck
                                            <?= $values['is_force_category']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                   备注设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_adminremark]" value="1"
                                               data-am-ucheck  <?= $values['is_adminremark'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_adminremark]" value="0"
                                               data-am-ucheck <?= $values['is_adminremark'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_adminremark]" value="1" data-am-ucheck
                                            <?= $values['is_force_adminremark']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                   包裹图片设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_packimage]" value="1"
                                               data-am-ucheck  <?= $values['is_packimage'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_packimage]" value="0"
                                               data-am-ucheck <?= $values['is_packimage'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_packimage]" value="1" data-am-ucheck
                                            <?= $values['is_force_packimage']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                   包裹存放位置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_shelf]" value="1"
                                               data-am-ucheck  <?= $values['is_shelf'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_shelf]" value="0"
                                               data-am-ucheck <?= $values['is_shelf'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_shelf]" value="1" data-am-ucheck
                                            <?= $values['is_force_shelf']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    集运路线设置
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_line]" value="1"
                                               data-am-ucheck  <?= $values['is_line'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="adminstyle[is_line]" value="0"
                                               data-am-ucheck <?= $values['is_line'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="adminstyle[is_force_line]" value="1" data-am-ucheck
                                            <?= $values['is_force_line']==1?'checked' : '' ?>>
                                        是否必填
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
<script src="/web/static/js/selectize.min.js"></script>
<!-- 图片文件列表模板 -->
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}
<script>
    $(function () {
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
         // 选择图片
        $('.upload-file1').selectImages({
            name: 'userclient[guide][first_image]'
        });
        $('.upload-file2').selectImages({
            name: 'userclient[guide][second_image]'
        });
        $('.upload-file3').selectImages({
            name: 'userclient[guide][third_image]'
        });
    });
</script>
