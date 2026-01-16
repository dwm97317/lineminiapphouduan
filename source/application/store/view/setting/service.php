<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">服务费设置 <a href="JavaScript:;" onclick="toAddItem()">新增费率</a></div>
                            </div>
                            <div id="body">
                            <?php foreach($vars['values'] as $v) :?>
                             <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">费率项 <span style="color:#ff6600;" onclick="toDelItem(this)">移除</span></label>
                                <div style="margin-left:50px;">
                                    <div class="am-u-sm-9">
                                        <div class="am-u-sm-6">
                                            <input type="number" class="am-form-field"
                                                   value="" style="visibility:hidden;">
                                        </div>
                                    </div>
                                    <label class="am-u-sm-3 am-form-label form-require">件数范围</label>
                                    <div class="am-u-sm-9">
                                        <div class="am-u-sm-6">
                                            <input type="number" class="am-form-field" style="width:50px;display:inline-block;" name="service[service][num_start][]"
                                                   value="<?= $v['num']['min']; ?>" required > -  <input type="number" class="am-form-field" style="width:50px; width:50px;display:inline-block;" name="service[service][num_end][]"
                                                   value="<?= $v['num']['max']; ?>" required>
                                        </div>
                                    </div>
                                    <label class="am-u-sm-3 am-form-label form-require">服务费</label>
                                    <div class="am-u-sm-9">
                                        <div class="am-u-sm-6">
                                            <input type="number" class="am-form-field" name="service[service][price][]"
                                                   value="<?= $v['price']; ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
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
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
        
        
       
    });
    
    function toDelItem(_this){
        var node = $(_this).parent().parent();
        node.remove();
    }
    
    function toAddItem(){
           $item = '<div class="am-form-group"><label class="am-u-sm-3 am-form-label form-require">费率项</label><div style="margin-left:50px;"><div class="am-u-sm-9"><div class="am-u-sm-6"><input type="number"class="am-form-field"value=""style="visibility:hidden;"></div></div><label class="am-u-sm-3 am-form-label form-require">件数范围</label><div class="am-u-sm-9"><div class="am-u-sm-6"><input type="number"class="am-form-field"style="width:50px;display:inline-block;"name="service[service][num_start][]"value=""required>-<input type="number"class="am-form-field"style="width:50px; width:50px;display:inline-block;"name="service[service][num_end][]"value=""required></div></div><label class="am-u-sm-3 am-form-label form-require">服务费</label><div class="am-u-sm-9"><div class="am-u-sm-6"><input type="number"class="am-form-field"name="service[service][price][]"value=""required></div></div></div></div>';
           $('#body').append($item);
    } 
</script>
