<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑仓库</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 仓库名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shop[shop_name]"
                                           placeholder="请输入仓库名称" value="<?= $model['shop_name'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 仓库别名(或简称) </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shop[shop_alias_name]"
                                           placeholder="请输入仓库别名(或简称)" value="<?= $model['shop_alias_name'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 仓库门头 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <div class="am-form-file">
                                            <button type="button"
                                                    class="upload-filed am-btn am-btn-secondary am-radius">
                                                <i class="am-icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div class="uploader-list am-cf">
                                                <div class="file-item">
                                                    <a href="<?= $model['logo']['file_path'] ?>"
                                                       title="点击查看大图" target="_blank">
                                                        <img src="<?= $model['logo']['file_path'] ?>">
                                                    </a>
                                                    <input type="hidden" name="shop[logo_image_id]"
                                                           value="<?= $model['logo_image_id'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label"> 仓库总负责人(收钱的人) </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="widget-become-goods am-form-file am-margin-top-xs">
                                        <button type="button"
                                                class="j-selectUser upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择用户
                                        </button>
                                        <div class="user-list uploader-list am-cf">
                                           <div class="file-item">
                                               <a href="<?= $model['user']['avatarUrl'] ?>" title="<?= $model['user']['nickName'] ?>" target="_blank">
                                                 <img src="<?= $model['user']['avatarUrl'] ?>">
                                               </a>
                                               <input type="hidden" name="shop[user_id]" value="<?= $model['user']['user_id'] ?>">
                                           </div>
                                        </div>
                                        <div class="am-block">
                                         <small>点击选择用户更改包裹所属用户</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 联系人 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shop[linkman]"
                                           placeholder="请输入门店联系人" value="<?= $model['linkman'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 联系电话 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shop[phone]"
                                           placeholder="请输入门店联系电话" value="<?= $model['phone'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 营业时间 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shop[shop_hours]"
                                           placeholder="请输入门店营业时间" value="<?= $model['shop_hours'] ?>" required>
                                    <small>例如：8:30-17:30</small>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 寄件分红比例 </label>
                                <div class="am-u-sm-9 am-u-end" style="width:200px;">
                                    <input type="text" class="tpl-form-input" name="shop[send_bonus]"
                                           placeholder="请输入寄件分红比例" value="<?= $model['send_bonus'] ?>" required>
                                    <small>请填写0-100的正数，如：10</small>
                                </div>
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 取件分红比例 </label>
                                <div class="am-u-sm-9 am-u-end" style="width:200px;">
                                    <input type="text" class="tpl-form-input" name="shop[pick_bonus]"
                                           placeholder="请输入取件分红比例" value="<?= $model['pick_bonus'] ?>" required>
                                           
                                </div>
                                <label class="am-u-sm-4 am-u-lg-2 am-form-label form-require"> 打包服务分红比例 </label>
                                <div class="am-u-sm-9 am-u-end" style="width:300px;">
                                    <input type="text" class="tpl-form-input" name="shop[service_bonus]"
                                           placeholder="请输入打包服务分红比例" value="<?= $model['service_bonus'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 仓库邮编 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shop[post]"
                                           placeholder="请输入仓库邮编" required value="<?= $model['post'];?>">
                                </div>
                            </div>
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 仓库区域 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="x-region-select" data-region-selected>
                                        <select name="shop[province_id]"
                                                data-province
                                                data-id="<?= $model['province_id'] ?>"
                                                required>
                                            <option value="">请选择省份</option>
                                        </select>
                                        <select name="shop[city_id]"
                                                data-city
                                                data-id="<?= $model['city_id'] ?>"
                                                required>
                                            <option value="">请选择城市</option>
                                        </select>
                                        <select name="shop[region_id]"
                                                data-region
                                                data-id="<?= $model['region_id'] ?>"
                                                required>
                                            <option value="">请选择地区</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 详细地址 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="shop[address]"
                                           placeholder="请输入详细地址" value="<?= $model['address'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 仓库坐标 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-block">
                                        <input type="text" style="background: none !important;" id="coordinate"
                                               class="tpl-form-input" name="shop[coordinate]"
                                               placeholder="请选择门店坐标"
                                               value="<?= $model['latitude'] ?>,<?= $model['longitude'] ?>"
                                               readonly=""
                                               required>
                                    </div>
                                    <div class="am-block am-padding-top-xs">
                                        <iframe id="map" src="<?= url('shop/getpoint') ?>"
                                                width="915"
                                                height="610"></iframe>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 仓库简介 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <textarea class="am-field-valid" rows="5" placeholder="请输入仓库简介"
                                              name="shop[summary]"><?= $model['summary'] ?></textarea>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">仓库排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="shop[sort]"
                                           value="<?= $model['sort'] ?>" required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 仓库位置 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shop[type]" value="0" data-am-ucheck
                                            <?= $model['type'] == 0 ? 'checked' : '' ?>>
                                        国内仓库
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shop[type]" value="1" data-am-ucheck
                                            <?= $model['type'] == 1 ? 'checked' : '' ?>>
                                        国外仓库
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 仓库类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shop[is_join]" value="0" data-am-ucheck
                                            <?= $model['is_join'] == 0 ? 'checked' : '' ?>>
                                        自营仓库
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shop[is_join]" value="1" data-am-ucheck
                                            <?= $model['is_join'] == 1 ? 'checked' : '' ?>>
                                        加盟仓库
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 仓库状态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shop[status]" value="1" data-am-ucheck
                                            <?= $model['status'] == 1 ? 'checked' : '' ?>>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="shop[status]" value="0" data-am-ucheck
                                            <?= $model['status'] == 0 ? 'checked' : '' ?>>
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
<script id="tpl-user-item" type="text/template">
    {{ each $data }}
    <div class="file-item">
        <a href="{{ $value.avatarUrl }}" title="{{ $value.nickName }} (ID:{{ $value.user_id }})" target="_blank">
            <img src="{{ $value.avatarUrl }}">
        </a>
        <input type="hidden" name="shop[user_id]" value="{{ $value.user_id }}">
    </div>
    {{ /each }}
</script>
<!-- 图片文件列表模板 -->
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
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script src="assets/store/js/select.region.js?v=1.2"></script>
<script>
    /**
     * 设置坐标
     */
    function setCoordinate(value) {
        var $coordinate = $('#coordinate');
        $coordinate.val(value);
        // 触发验证
        $coordinate.trigger('change');
    }
</script>
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
        // 选择图片
        $('.upload-filed').selectImages({
            name: 'shop[logo_image_id]'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
    });
</script>
