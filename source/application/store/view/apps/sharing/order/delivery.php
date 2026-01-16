<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" action="" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">发货信息</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 包裹单号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <p class="am-form-static"><?= $detail['order_sn'] ?></p>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 运输方式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="transfer" value="1" data-am-ucheck checked onchange="onChange('c1')"
                                               >
                                        运输商
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="transfer" value="0" data-am-ucheck   onchange="onChange('c2')">
                                        自定义运单
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group c" id="c1">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 承运商 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <select name="delivery[t_name1]" id="" data-am-selected="{searchBox: 1,maxHeight:300}">
                                         <option value="">选择承运商</option>
                                     <?php if (isset($track)):
                                           
                                            foreach ($track as $item): ?>
                                                <option value="<?= $item['key'] ?>"><?= $item['_name'] ?></option>
                                            <?php endforeach; endif; ?>
                                     </select>
                                </div>
                            </div>
                            <div class="am-form-group c" id="c2" style="display: none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 承运商 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="delivery[t_name]"
                                           placeholder="请输入承运商" required>
                                           
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 转运单 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="delivery[t_order_sn]"
                                           placeholder="请输入转运单号" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <input type="hidden" name="delivery[type]" value="delivery"/>
                                <input type="hidden" name="delivery[order_id]" value="<?= $detail['order_id'] ?>"/>
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

  
    function onChange(tab){
       $('.c').hide();
       $('#'+tab).show();
    }


    $(function () {
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
        
        

    });
</script>
