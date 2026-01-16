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
                                <div class="widget-title am-fl">添加批次</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">选择目标仓库</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="batch[shop_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" >
                                        <option value=""></option>
                                        <?php if (isset($list) && !$list->isEmpty()):
                                            foreach ($list as $item): ?>
                                                   <option value="<?= $item['shop_id'] ?>" ><?= $item['shop_name'] ?></option>
                                            <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>请选择包裹将要寄往的国家</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 批次名称 </label>
                                <div class="am-u-sm-3 am-u-end">
                                    <input id="batch_name" type="text" class="tpl-form-input" name="batch[batch_name]"
                                           placeholder="请输入批次名称" required>
                                </div>
                                <button type="button" class="am-btn am-btn-secondary"><span onclick="toClick()">生成批次号</span></button>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 装箱单号 </label>
                                <div class="am-u-sm-3 am-u-end">
                                    <input  type="text" class="tpl-form-input" name="batch[batch_no]"
                                           placeholder="请输入装箱单号" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 批次总重量 </label>
                                <div class="am-u-sm-3 am-u-end">
                                    <input  type="text" class="tpl-form-input" name="batch[weigth]"
                                           placeholder="请输入总重量" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 物流模板 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <select name="batch[template_id]" id="" data-am-selected="{searchBox: 1,maxHeight:300}">
                                         <option value="">选择模板</option>
                                     <?php if (isset($templatelist)):
                                            foreach ($templatelist as $item): ?>
                                                <option value="<?= $item['template_id'] ?>"><?= $item['template_name'] ?></option>
                                            <?php endforeach; endif; ?>
                                     </select>
                                     <div class="help-block">
                                        <small>注：选择物流模板后，系统后根据物流模板的设置，在批次发货后，开始按顺序自动更新物流轨迹</small>
                                </div>
                                </div>
                                
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 运输方式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="batch[transfer]" value="1" data-am-ucheck checked onchange="onChange('c1')"
                                               >
                                        运输商
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="batch[transfer]" value="0" data-am-ucheck   onchange="onChange('c2')">
                                        自有物流
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group c" id="c1">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 承运商 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <select name="batch[tt_number]" id="" data-am-selected="{searchBox: 1,maxHeight:300}">
                                         <option value="">选择承运商</option>
                                     <?php if (isset($track)):
                                            foreach ($track as $item): ?>
                                                <option value="<?= $item['express_code'] ?>"><?= $item['express_name'] ?>-<?= $item['express_code'] ?></option>
                                            <?php endforeach; endif; ?>
                                     </select>
                                     <div class="help-block">
                                        <small>注：选择自有物流17track不可查，请选择正确的物流商，否则国际单号无法查询；</small>
                                </div>
                                </div>
                                
                            </div>
                            
                            <div class="am-form-group c" id="c2" style="display: none;">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 承运商 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="batch[t_number]" id="" data-am-selected="{searchBox: 1,maxHeight:300}">
                                         <option value="">选择承运商</option>
                                     <?php if (isset($ditchlist)):
                                            foreach ($ditchlist as $item): ?>
                                                <option value="<?= $item['ditch_id'] ?>"><?= $item['ditch_name'] ?>-<?= $item['ditch_no'] ?></option>
                                            <?php endforeach; endif; ?>
                                     </select>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 运单号 </label>
                                <div class="am-u-sm-3 am-u-end">
                                    <input type="text" class="tpl-form-input" name="batch[t_order_sn]"
                                           placeholder="请输入运单号" >
                                </div>
                            </div>
                             <div class="am-form-group">
                                        <label class="am-u-sm-3 am-u-lg-2 am-form-label">长宽高体积重</label>
                                        <div class="am-u-sm-9 am-u-end" style="position: relative">
                                             <div class="span">
                                                 <input id="length" type="text" onblur="getweightvol()" class="tpl-form-input" style="width:80px;border: 1px solid #c2cad8;" name="batch[length]"
                                                   value="" placeholder="长<?= $set['size_mode']['unit'] ?>">
                                             </div>
                                             <div class="span">
                                                <input id="width" type="text" onblur="getweightvol()" class="tpl-form-input" style="width:80px;border: 1px solid #c2cad8;" name="batch[width]"
                                                   value="" placeholder="宽<?= $set['size_mode']['unit'] ?>">
                                             </div>
                                             <div class="span">
                                                 <input id="height" type="text" onblur="getweightvol()" class="tpl-form-input" style="width:80px;border: 1px solid #c2cad8;" name="batch[height]"
                                                   value="" placeholder="高<?= $set['size_mode']['unit'] ?>">
                                             </div>
                                             <div class="span">
                                                 <select onchange="getweightvol()" id="wvol" style="width:80px;border: 1px solid #c2cad8;" >
                                                    <option value="6000">6000</option>
                                                 </select>
                                             </div>
                                             <div class="span">
                                                 <input id="wegihtvol" type="text" class="tpl-form-input" style="width:80px;border: 1px solid #c2cad8;" name="batch[wegihtvol]"
                                                   value="" placeholder="体积重<?= $set['size_mode']['unit'] ?>">
                                             </div>
                                        </div>
                                    </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 备注 </label>
                                <div class="am-u-sm-3 am-u-end">
                                    <textarea  type="textarea" class="tpl-form-input" name="batch[remark]"
                                           placeholder="请输入备注"></textarea>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 批次类型 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <?php foreach (BatchTypeEnum::data() as $item): ?>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="batch[batch_type]" value="<?= $item['value'] ?>" data-am-ucheck
                                              <?= $item['value']==10 ? 'checked' : '' ?> >
                                        <?= $item['name'] ?>
                                    </label>
                                    <?php endforeach; ?>
                            
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