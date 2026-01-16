<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑物流信息</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">发货单号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="sendOrder[order_sn]"
                                           value="<?= $sendOrder['order_sn'] ;?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">物流动态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="sendOrder[logistics]"
                                           value="" required placeholder="包裹已到达……">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">物流时间 </label>
                                 <div class="am-u-sm-9 am-u-end">
                                     <input type="text"  name="sendOrder[created_time]" placeholder="请选择起始日期" value="<?php echo date("Y-m-d H:i:s",time()) ?>" id="datetimepicker" class="am-form-field">
                                </div>
                            </div>
                           
                            <div class="am-form-group">
                                
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <div style="margin-right:20px;" onclick="javascript :history.back(-1);" class="am-btn am-btn-secondary">返回</div>
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交</button>
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

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
        
        $('#datetimepicker').datetimepicker({
          format: 'yyyy-mm-dd hh:ii'
        });
        
        
        $('#datetimepicker').datetimepicker().on('changeDate', function(ev){
            $('#datetimepicker').datetimepicker('hide');
          });
    });
     
</script>
