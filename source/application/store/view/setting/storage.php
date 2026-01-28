<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">文件上传设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label">
                                    默认上传方式
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="storage[default]" value="local" data-am-ucheck
                                            <?= $values['default'] === 'local' ? 'checked' : '' ?>> 本地 (不推荐)
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="storage[default]" value="qiniu" data-am-ucheck
                                            <?= $values['default'] === 'qiniu' ? 'checked' : '' ?>> 七牛云存储
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="storage[default]" value="aliyun" data-am-ucheck
                                            <?= $values['default'] === 'aliyun' ? 'checked' : '' ?>> 阿里云OSS
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="storage[default]" value="qcloud" data-am-ucheck
                                            <?= $values['default'] === 'qcloud' ? 'checked' : '' ?>> 腾讯云COS
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="storage[default]" value="cloudflare" data-am-ucheck
                                            <?= $values['default'] === 'cloudflare' ? 'checked' : '' ?>> Cloudflare R2
                                    </label>
                                </div>
                            </div>
                            <div id="qiniu"
                                 class="form-tab-group <?= $values['default'] === 'qiniu' ? 'active' : '' ?>">
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label">
                                        存储空间名称 <span class="tpl-form-line-small-title">Bucket</span>
                                    </label>
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input" name="storage[engine][qiniu][bucket]"
                                               value="<?= $values['engine']['qiniu']['bucket'] ?>">
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label">
                                        ACCESS_KEY <span class="tpl-form-line-small-title">AK</span>
                                    </label>
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input"
                                               name="storage[engine][qiniu][access_key]"
                                               value="<?= $values['engine']['qiniu']['access_key'] ?>">
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label">
                                        SECRET_KEY <span class="tpl-form-line-small-title">SK</span>
                                    </label>
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input"
                                               name="storage[engine][qiniu][secret_key]"
                                               value="<?= $values['engine']['qiniu']['secret_key'] ?>">
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label">
                                        空间域名 <span class="tpl-form-line-small-title">Domain</span>
                                    </label>
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input" name="storage[engine][qiniu][domain]"
                                               value="<?= $values['engine']['qiniu']['domain'] ?>">
                                        <small>请补全http:// 或 https://，例如：http://static.cloud.com</small>
                                    </div>
                                </div>
                            </div>
                            <div id="aliyun"
                                 class="form-tab-group <?= $values['default'] === 'aliyun' ? 'active' : '' ?>">
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label">
                                        存储空间名称 <span class="tpl-form-line-small-title">Bucket</span>
                                    </label>
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input" name="storage[engine][aliyun][bucket]"
                                               value="<?= $values['engine']['aliyun']['bucket'] ?>">
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label"> AccessKeyId </label>
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input"
                                               name="storage[engine][aliyun][access_key_id]"
                                               value="<?= $values['engine']['aliyun']['access_key_id'] ?>">
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label"> AccessKeySecret </label>
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input"
                                               name="storage[engine][aliyun][access_key_secret]"
                                               value="<?= $values['engine']['aliyun']['access_key_secret'] ?>">
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label">
                                        空间域名 <span class="tpl-form-line-small-title">Domain</span>
                                    </label>
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input"
                                               name="storage[engine][aliyun][domain]"
                                               value="<?= $values['engine']['aliyun']['domain'] ?>">
                                        <small>请补全http:// 或 https://，例如：http://static.cloud.com</small>
                                    </div>
                                </div>
                            </div>
                            <div id="qcloud"
                                 class="form-tab-group <?= $values['default'] === 'qcloud' ? 'active' : '' ?>">
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label">
                                        存储空间名称 <span class="tpl-form-line-small-title">Bucket</span>
                                    </label>
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input" name="storage[engine][qcloud][bucket]"
                                               value="<?= $values['engine']['qcloud']['bucket'] ?>">
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label">
                                        所属地域 <span class="tpl-form-line-small-title">Region</span>
                                    </label>
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input"
                                               name="storage[engine][qcloud][region]"
                                               value="<?= $values['engine']['qcloud']['region'] ?>">
                                        <small>请填写地域简称，例如：ap-beijing、ap-hongkong、eu-frankfurt</small>
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label">
                                        SecretId
                                    </label>
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input"
                                               name="storage[engine][qcloud][secret_id]"
                                               value="<?= $values['engine']['qcloud']['secret_id'] ?>">
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label">
                                        SecretKey
                                    </label>
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input"
                                               name="storage[engine][qcloud][secret_key]"
                                               value="<?= $values['engine']['qcloud']['secret_key'] ?>">
                                    </div>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label">
                                        空间域名 <span class="tpl-form-line-small-title">Domain</span>
                                    </label>
                                    <div class="am-u-sm-9">
                                        <input type="text" class="tpl-form-input" name="storage[engine][qcloud][domain]"
                                               value="<?= $values['engine']['qcloud']['domain'] ?>">
                                        <small>请补全http:// 或 https://，例如：http://static.cloud.com</small>
                                    </div>
                                </div>
                            </div>
                            <div id="cloudflare"
                                 class="form-tab-group <?= $values['default'] === 'cloudflare' ? 'active' : '' ?>">
                                <?php
                                    $cf = $values['engine']['cloudflare'];
                                    $accounts = isset($cf['accounts']) && is_array($cf['accounts']) && !empty($cf['accounts'])
                                        ? $cf['accounts'] : [];
                                    if (empty($accounts)) {
                                        $aid = isset($cf['account_id']) ? $cf['account_id'] : 'default';
                                        $accounts = [
                                            $aid => [
                                                'bucket' => isset($cf['bucket']) ? $cf['bucket'] : '',
                                                'access_key' => isset($cf['access_key']) ? $cf['access_key'] : '',
                                                'secret_key' => isset($cf['secret_key']) ? $cf['secret_key'] : '',
                                                'account_id' => isset($cf['account_id']) ? $cf['account_id'] : '',
                                                'domain' => isset($cf['domain']) ? $cf['domain'] : ''
                                            ]
                                        ];
                                    }
                                    $activeId = isset($cf['active_account_id']) ? $cf['active_account_id'] : array_keys($accounts)[0];
                                ?>
                                <input type="hidden" id="cf-active-id" name="storage[engine][cloudflare][active_account_id]" value="<?= $activeId ?>">
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label">账号选择</label>
                                    <div class="am-u-sm-9">
                                        <div class="am-btn-group">
                                            <?php foreach ($accounts as $aid => $conf): ?>
                                                <button type="button" class="am-btn am-btn-default cf-tab <?php if($aid===$activeId) echo 'am-btn-secondary'; ?>" data-aid="<?= $aid ?>">
                                                    <?= htmlspecialchars($aid) ?>
                                                </button>
                                            <?php endforeach; ?>
                                            <button type="button" class="am-btn am-btn-success" id="cf-add-account">＋</button>
                                        </div>
                                        <small>点击切换不同R2账号；＋可新增一份账号配置</small>
                                    </div>
                                </div>
                                <div id="cf-forms">
                                    <?php foreach ($accounts as $aid => $conf): ?>
                                    <div class="cf-form" data-aid="<?= $aid ?>" style="<?php if($aid!==$activeId) echo 'display:none;'; ?>">
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">
                                                存储空间名称 <span class="tpl-form-line-small-title">Bucket</span>
                                            </label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="tpl-form-input" name="storage[engine][cloudflare][accounts][<?= $aid ?>][bucket]"
                                                       value="<?= $conf['bucket'] ?>">
                                            </div>
                                        </div>
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">Access Key ID</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="tpl-form-input" name="storage[engine][cloudflare][accounts][<?= $aid ?>][access_key]"
                                                       value="<?= $conf['access_key'] ?>">
                                            </div>
                                        </div>
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">Secret Access Key</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="tpl-form-input" name="storage[engine][cloudflare][accounts][<?= $aid ?>][secret_key]"
                                                       value="<?= $conf['secret_key'] ?>">
                                            </div>
                                        </div>
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">Account ID</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="tpl-form-input" name="storage[engine][cloudflare][accounts][<?= $aid ?>][account_id]"
                                                       value="<?= $conf['account_id'] ?>">
                                                <small>Cloudflare Dashboard -> R2 -> Account ID</small>
                                            </div>
                                        </div>
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">空间域名 <span class="tpl-form-line-small-title">Domain</span></label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="tpl-form-input" name="storage[engine][cloudflare][accounts][<?= $aid ?>][domain]"
                                                       value="<?= $conf['domain'] ?>">
                                                <small>请补全http:// 或 https://，例如：https://pub-xxx.r2.dev</small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label">自动切换</label>
                                    <div class="am-u-sm-9">
                                        <label class="am-checkbox">
                                            <input type="checkbox" name="storage[engine][cloudflare][auto_switch]" value="1" data-am-ucheck <?= isset($cf['auto_switch']) && $cf['auto_switch']==1 ? 'checked' : '' ?> >
                                            当当前账号≥95%时自动切换到其他可用账号
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交</button>
                                    <button type="button" id="cf-refresh" class="am-btn am-btn-default">手动刷新用量</button>
                                    <div id="cf-usage" class="am-progress am-margin-top">
                                        <div class="am-progress-bar" id="cf-usage-bar" style="width:0%">0%</div>
                                    </div>
                                    <div id="cf-usage-text" class="am-margin-top-sm"></div>
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

        // 切换默认上传方式
        $("input:radio[name='storage[default]']").change(function (e) {
            $('.form-tab-group').removeClass('active');
            $('#' + e.currentTarget.value).addClass('active');
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

        // Cloudflare 账号切换
        $('.cf-tab').on('click', function () {
            var aid = $(this).data('aid');
            $('#cf-active-id').val(aid);
            $('.cf-tab').removeClass('am-btn-secondary');
            $(this).addClass('am-btn-secondary');
            $('.cf-form').hide();
            $('.cf-form[data-aid="'+aid+'"]').show();
            // 校验必填项，未完善时不请求后端
            var form = $('.cf-form[data-aid="'+aid+'"]');
            var bucket = form.find('input[name^="bucket"]').val();
            var access = form.find('input[name^="access_key"]').val();
            var secret = form.find('input[name^="secret_key"]').val();
            var account = form.find('input[name^="account_id"]').val();
            if (!bucket || !access || !secret || !account) {
                $('#cf-usage-bar').css('width', '0%').text('0%');
                $('#cf-usage-text').text('请先完善并提交该账号配置后，再刷新用量');
                return;
            }
            fetchUsage(aid, false);
        });

        // 新增账号
        $('#cf-add-account').on('click', function () {
            var aid = 'r2_' + (new Date().getTime());
            var tpl =
            '<div class="cf-form" data-aid="'+aid+'">'+
              '<div class="am-form-group">'+
                '<label class="am-u-sm-3 am-form-label">存储空间名称 <span class="tpl-form-line-small-title">Bucket</span></label>'+
                '<div class="am-u-sm-9">'+
                  '<input type="text" class="tpl-form-input" name="storage[engine][cloudflare][accounts]['+aid+'][bucket]" value="">'+
                '</div>'+
              '</div>'+
              '<div class="am-form-group">'+
                '<label class="am-u-sm-3 am-form-label">Access Key ID</label>'+
                '<div class="am-u-sm-9">'+
                  '<input type="text" class="tpl-form-input" name="storage[engine][cloudflare][accounts]['+aid+'][access_key]" value="">'+
                '</div>'+
              '</div>'+
              '<div class="am-form-group">'+
                '<label class="am-u-sm-3 am-form-label">Secret Access Key</label>'+
                '<div class="am-u-sm-9">'+
                  '<input type="text" class="tpl-form-input" name="storage[engine][cloudflare][accounts]['+aid+'][secret_key]" value="">'+
                '</div>'+
              '</div>'+
              '<div class="am-form-group">'+
                '<label class="am-u-sm-3 am-form-label">Account ID</label>'+
                '<div class="am-u-sm-9">'+
                  '<input type="text" class="tpl-form-input" name="storage[engine][cloudflare][accounts]['+aid+'][account_id]" value="">'+
                  '<small>Cloudflare Dashboard -> R2 -> Account ID</small>'+
                '</div>'+
              '</div>'+
              '<div class="am-form-group">'+
                '<label class="am-u-sm-3 am-form-label">空间域名 <span class="tpl-form-line-small-title">Domain</span></label>'+
                '<div class="am-u-sm-9">'+
                  '<input type="text" class="tpl-form-input" name="storage[engine][cloudflare][accounts]['+aid+'][domain]" value="">'+
                  '<small>请补全http:// 或 https://，例如：https://pub-xxx.r2.dev</small>'+
                '</div>'+
              '</div>'+
            '</div>';
            $('#cf-forms').append(tpl);
            // 添加tab并切换
            $('<button type="button" class="am-btn am-btn-default cf-tab am-btn-secondary" data-aid="'+aid+'">'+aid+'</button>').insertBefore('#cf-add-account');
            $('.cf-form').hide();
            $('.cf-form[data-aid="'+aid+'"]').show();
            $('#cf-active-id').val(aid);
        });

        // 手动刷新
        $('#cf-refresh').on('click', function () {
            var aid = $('#cf-active-id').val();
            var form = $('.cf-form[data-aid="'+aid+'"]');
            var bucket = form.find('input[name^="bucket"]').val();
            var access = form.find('input[name^="access_key"]').val();
            var secret = form.find('input[name^="secret_key"]').val();
            var account = form.find('input[name^="account_id"]').val();
            if (!bucket || !access || !secret || !account) {
                $('#cf-usage-text').text('请先完善并提交该账号配置后，再刷新用量');
                return;
            }
            fetchUsage(aid, true);
        });

        function fetchUsage(aid, refresh) {
            if (!aid) return;
            $.get("<?= url('setting.cloudflare_r2/usage') ?>", {aid: aid, refresh: refresh ? 1 : 0}, function (res) {
                if (res.code === 1) {
                    var p = res.data.percent;
                    $('#cf-usage-bar').css('width', p + '%').text(p + '%');
                    $('#cf-usage-text').text(res.data.used_text);
                } else {
                    $('#cf-usage-text').text('获取用量失败：' + (res.msg || '未知错误'));
                }
            });
        }

        // 初始化加载当前账号用量
        fetchUsage($('#cf-active-id').val(), false);

    });
</script>
