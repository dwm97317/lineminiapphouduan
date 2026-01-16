<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑物流公司</div>
                            </div>
                            <div class="am-form-group c" id="c1">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 承运商 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <select onchange="changeexpress(this)"  id="deliveryitem" data-am-selected="{searchBox: 1,maxHeight:300}">
                                         <option value="">选择承运商</option>
                                     <?php if (isset($track)):
                                            foreach ($track as $item): ?>
                                                <option value="<?= $item['key'] ?>"><?= $item['_name'] ?>-<?= $item['_name_zh-cn'] ?>-<?= $item['key'] ?></option>
                                            <?php endforeach; endif; ?>
                                     </select>
                                     <div class="help-block">
                                        <small>注：请选择对应的物流公司，并根据使用习惯填写对应的物流公司名称</small>
                                </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 物流公司名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="express[express_name]"
                                           value="<?= $model['express_name'] ?>" required>
                                    <small>请对照 <a href="<?= url('setting.express/company') ?>" target="_blank">物流公司编码表</a> 填写</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 物流公司代码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input id="express_code" type="text" class="tpl-form-input" name="express[express_code]"
                                           value="<?= $model['express_code'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 应用范围 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[type]" value="0" data-am-ucheck <?= $model['type']['value'] == 0 ?'checked' : '' ?>>
                                        全部
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[type]" value="1" data-am-ucheck <?= $model['type']['value'] == 1 ? 'checked' : '' ?>>
                                        包裹预报
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="express[type]" value="2" data-am-ucheck <?= $model['type']['value'] == 2 ? 'checked' : '' ?>>
                                        发货（转单）
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="express[sort]"
                                           value="<?= $model['sort'] ?>" required>
                                    <small>数字越小越靠前</small>
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
<script>
    function changeexpress(_this){
      var a =  _this.options[_this.selectedIndex].value;
      $("#express_code").val(a);
    }
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
