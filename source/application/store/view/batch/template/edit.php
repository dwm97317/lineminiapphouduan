<?php
use app\common\enum\BatchType as BatchTypeEnum;
?>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑物流模板</div>
                            </div>
                           
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 批次名称 </label>
                                <div class="am-u-sm-3 am-u-end">
                                    <input type="text" class="tpl-form-input" name="batch[template_name]" value="<?= $detail['template_name']?>"
                                           placeholder="请输入批次名称" required>
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
    function getweightvol(){
        var legnth = $("#length")[0].value;
        var width = $("#width")[0].value;
        var height = $("#height")[0].value;
        var wvol = $("#wvol")[0].value;
        console.log(wvol);
        
    }
    
    function toClick(){
        var hedanurl = "<?= url('store/batch/createbatchname') ?>";
        layer.confirm('请确定是否生成批次号', {title: '生成批次号'}
        , function (index) {
            $.post(hedanurl,{}, function (result) {
                if(result.code == 1){
                    $("#batch_name").val(result.data);
                }else{
                   $.show_error(result.msg); 
                }
            });
            layer.close(index);
        });        
    } 
    
    function onChange(tab){
       $('.c').hide();
       $('#'+tab).show();
       console.log($('.c1'));
    }   
        
        
    $(function () {
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
<style>
     .span { display:inline-block; font-size:13px; color:#666; margin-bottom:10px;}
</style>