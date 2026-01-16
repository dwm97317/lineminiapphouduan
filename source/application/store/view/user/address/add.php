<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加会员地址</div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 收件人姓名 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="address[name]"
                                           value="" placeholder="请输入收件人姓名" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label"> 选择用户 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="widget-become-goods am-form-file am-margin-top-xs">
                                        <button type="button"
                                                class="j-selectUser upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择用户
                                        </button>
                                        <div class="user-list uploader-list am-cf">
                                        </div>
                                        <div class="am-block">
                                            <small>选择后不可更改</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php if ($set['is_tel_code']==1): ?> 
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">手机号区号</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="address[tel_code]"
                                           value="" placeholder="请输入手机号区号" required>
                                </div>
                            </div>
                            <?php endif ;?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">手机号</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="address[phone]"
                                           value="" placeholder="请输入手机号" required>
                                </div>
                            </div>
                            <?php if ($set['is_identitycard']==1): ?> 
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">身份证号</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="address[identitycard]"
                                           value="" placeholder="请输入身份证号" required>
                                </div>
                            </div>
                            <?php endif ;?>
                            <?php if ($set['is_clearancecode']==1): ?> 
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">通关代码</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="address[clearancecode]"
                                           value="" placeholder="请输入通关代码" required>
                                </div>
                            </div>
                            <?php endif ;?>
                            <!--<div class="am-form-group">-->
                            <!--    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">国家</label>-->
                            <!--    <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">-->
                            <!--        <input type="text" class="tpl-form-input" name="address[country]"-->
                            <!--               value="" placeholder="请输入国家" required>-->
                            <!--    </div>-->
                            <!--</div>-->
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">国家 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="address[country_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" >
                                        <option value=""></option>
                                        <?php if (isset($countryList) && !$countryList->isEmpty()):
                                            foreach ($countryList as $item): ?>
                                                   <option value="<?= $item['id'] ?>" ><?= $item['title'] ?></option>
                                            <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>请选择包裹将要寄往的国家</small>
                                    </div>
                                </div>
                            </div>
                            <?php if ($set['is_province']==1): ?> 
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">省</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="address[province]"
                                           value="" placeholder="请输入省" required>
                                </div>
                            </div>
                            <?php endif ;?>
                            <?php if ($set['is_city']==1): ?> 
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">市</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="address[city]"
                                           value="" placeholder="请输入市" required>
                                </div>
                            </div>
                            <?php endif ;?>
                            <?php if ($set['is_street']==1): ?> 
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">区</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="address[region]"
                                           value="" placeholder="请输入区" required>
                                </div>
                            </div>
                            <?php endif ;?>
                            <?php if ($set['is_street']==1): ?> 
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">街道</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="address[street]"
                                           value="" placeholder="请输入街道" required>
                                </div>
                            </div>
                            <?php endif ;?>
                            <?php if ($set['is_door']==1): ?> 
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">门牌</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="address[door]"
                                           value="" placeholder="请输入门牌" required>
                                </div>
                            </div>
                            <?php endif ;?>
                            <?php if ($set['is_detail']==1): ?> 
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">详细地址</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="address[detail]"
                                           value="" placeholder="请输入详细地址" required>
                                </div>
                            </div>
                            <?php endif ;?>
                            <?php if ($set['is_code']==1): ?> 
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">邮编</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="address[code]"
                                           value="" placeholder="请输入邮编" required>
                                </div>
                            </div>
                            <?php endif ;?>
                            <?php if ($set['is_email']==1): ?> 
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">邮箱</label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="address[email]"
                                           value="" placeholder="请输入邮箱" required>
                                </div>
                            </div>
                            <?php endif ;?>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary"> 提交
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
<script id="tpl-user-item" type="text/template">
    {{ each $data }}
    <div class="file-item">
        <a href="{{ $value.avatarUrl }}" title="{{ $value.nickName }} (ID:{{ $value.user_id }})" target="_blank">
            <img src="{{ $value.avatarUrl }}">
        </a>
        <input type="hidden" name="address[user_id]" value="{{ $value.user_id }}">
    </div>
    {{ /each }}
</script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
    $(function () {
             // 选择用户
    $('.j-selectUser').click(function () {
        var $userList = $('.user-list');
        $.selectData({
            title: '选择用户',
            uri: 'user/lists',
            dataIndex: 'user_id',
            done: function (data) {
                var user = [data[0]];
                $userList.html(template('tpl-user-item', user));
            }
        });
    });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
