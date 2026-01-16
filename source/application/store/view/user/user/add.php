<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加用户<small style="padding-left:10px;color:#1686ef">(提示：此处添加用户信息适用于国外无法使用微信，接收短信的用户)</small></div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">用户昵称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[nickName]"
                                           value="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">用户编号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[user_code]"
                                           value="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">用户性别 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="user[gender]" value="1" data-am-ucheck
                                               checked>
                                        男
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="user[gender]" value="2" data-am-ucheck
                                               >
                                        女
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="user[gender]" value="0" data-am-ucheck>
                                        未知
                                    </label>
                                </div>
                            </div>
                            <!--<div class="am-form-group">-->
                            <!--    <label class="am-u-sm-3 am-u-lg-2 am-form-label">用户头像</label>-->
                            <!--    <div class="am-u-sm-9 am-u-end" style="position: relative">-->
                            <!--          <div class="" style="display:inline-block;" >-->
                            <!--                <div class="am-form-file">-->
                            <!--                    <div class=" am-form-file">-->
                            <!--                        <button type="button"-->
                            <!--                                class="upload-file_enter am-btn am-btn-secondary am-radius">-->
                            <!--                            <i class="am-icon-cloud-upload"></i> 选择图片-->
                            <!--                        </button>-->
                            <!--                        <div id="uploadsf" class="uploader-list am-cf">-->
                            <!--                            <?php if(isset($data['packageimage'])) foreach ($data['packageimage'] as $key => $item): ?>-->
                            <!--                                <div class="file-item">-->
                            <!--                                    <a href="<?= $item['file_path'] ?>" title="点击查看大图" target="_blank">-->
                            <!--                                        <img src="<?= $item['file_path'] ?>">-->
                            <!--                                    </a>-->
                            <!--                                    <input type="hidden" name="data[images][]"-->
                            <!--                                           value="<?= $item['image_id'] ?>">-->
                            <!--                                    <i class="iconfont icon-shanchu file-item-delete"></i>-->
                            <!--                                </div>-->
                            <!--                            <?php endforeach; ?>-->
                            <!--                        </div>-->
                            <!--                    </div>-->
                            <!--                </div>-->
                            <!--          </div>-->
                            <!--    </div>-->
                            <!--</div>-->
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">用户手机号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[mobile]"
                                           value="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">用户邮箱 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" autocomplete="off" name="user[email]"
                                           value="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">登录密码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="user[password]"
                                           value="" placeholder="请输入登录密码" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">确认密码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[password_confirm]"
                                           value="" placeholder="请输入确认密码" required>
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
<script id="tpl-file-item" type="text/template">
    {{ each list }}
    <div class="file-item">
        <a href="{{ $value.file_path }}" title="点击查看大图" target="_blank">
            <img src="{{ $value.file_path }}">
        </a>
        <input type="hidden" name="{{ name }}" value="{{ $value.file_id }}">
        <i class="iconfont icon-shanchu file-item-delete"></i>
    </div>
    {{ /each }}
</script>

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}
<script>
    $(function () {
        
         // 选择图片
        $('.upload-file_enter').selectImages({
            name: 'user[avatarUrl]' , multiple: false
        });
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
