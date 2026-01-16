<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" action="<?= url('store/trOrder/modify_save')?>" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑订单</div>
                            </div>
                            
                           <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">包裹信息 (可选填)</label>
                                <div class="am-u-sm-9 am-u-end" style="position: relative">
                                     <div class="span">
                                         长(<?= $set['size_mode']['unit'] ?>) <input type="text" class="tpl-form-input" style="width:80px" onblur="caleAmount()"  oninput="caleAmount()" name="data[length]"
                                           value="<?= $detail['length']??'' ;?>" placeholder="请输入长">
                                     </div>
                                     <div class="span">
                                         宽(<?= $set['size_mode']['unit'] ?>) <input type="text" class="tpl-form-input" style="width:80px"  onblur="caleAmount()" oninput="caleAmount()" name="data[width]"
                                           value="<?= $detail['width']??'' ;?>" placeholder="请输入宽">
                                     </div>
                                     <div class="span">
                                         高(<?= $set['size_mode']['unit'] ?>) <input type="text" class="tpl-form-input" style="width:80px"  onblur="caleAmount()" oninput="caleAmount()" name="data[height]"
                                           value="<?= $detail['height']??'' ;?>" placeholder="请输入高">
                                     </div>
                                     <div class="span">
                                         称重(<?= $set['weight_mode']['unit'] ?>) <input type="text" class="tpl-form-input" style="width:80px"  onblur="caleAmount()" oninput="caleAmount()" name="data[weight]"
                                           value="<?= $detail['weight']??'' ;?>" placeholder="请输入重量">
                                     </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 集运线路 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="data[line_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" onchange="caleAmount()" >
                                        <option value=""></option>
                                        <?php if (isset($line) && !$line->isEmpty()):
                                            foreach ($line as $item): ?>
                                                <option value="<?= $item['id'] ?>"  <?= $detail['line_id'] == $item['id'] ? 'selected' : '' ?>><?= $item['name'] ?></option>
                                            <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small style="color:#ff6666;">切换路线即可自动计算出对应运费</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">计费重量</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" id="oWei" name="data[cale_weight]"
                                           value="<?= $detail['cale_weight']??'' ;?>" placeholder="请输入价格">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">体积重</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" id="weigthV" name="data[volume]"
                                           value="<?= $detail['volume']??'' ;?>" placeholder="请输入价格">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">集运路线费用</label>
                                <div class="am-u-sm-9 am-u-end wd">
                                    <input type="text" class="tpl-form-input" onchange="MathFree()" id="price" name="data[free]"
                                           value="<?= $detail['free']??'' ;?>" placeholder="请输入价格">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">打包服务费用</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" id="pack_free" onchange="MathFree()" name="data[pack_free]"
                                           value="<?= $detail['pack_free']??'' ;?>" placeholder="请输入价格">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">附加费用（如关税或其他额外费用）</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" onchange="MathFree()" id="other_free"  name="data[other_free]"
                                           value="<?= $detail['other_free']??'' ;?>" placeholder="请输入价格">
                                    <div class="help-block">
                                        <small style="color:#ff6666;">包含其他增值费,清关费,等其他费用</small>
                                    </div>
                                </div>
                                 
                            </div>

                             <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">总费用（不可修改）</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input disabled="" type="text" class="tpl-form-input" id="all"
                                           value="<?= $detail['total']??'' ;?>" placeholder="">
                                           <small style="color:#ff6666;">总费用 = 集运路线费用 + 打包服务费用+ 附加费用（如需要减少总费用，请修改以上三个费用）</small>
                                </div>
                                
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">包裹图片 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                            <?php foreach ($detail['inpackimage'] as $key => $item): ?>
                                                <div class="file-item">
                                                    <a href="<?= $item['file_path'] ?>" title="点击查看大图" target="_blank">
                                                        <img src="<?= $item['file_path'] ?>">
                                                    </a>
                                                    <input type="hidden" name="data[images][]"
                                                           value="<?= $item['image_id'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="help-block am-margin-top-sm">
                                        <small>尺寸750x750像素以上，大小2M以下 (可拖拽图片调整显示顺序 )</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">反馈备注</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="data[remark]"
                                           value="<?= $detail['remark']??'' ;?>" placeholder="请输入备注" >
                                </div>
                            </div>
                            <?php if($detail['status']==1):?>
                           <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 审核状态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="data[verify]" value="1" data-am-ucheck
                                               >
                                        已查验
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="data[verify]" value="0" data-am-ucheck checked>
                                        仅保存数据
                                    </label>
                                    <div class="help-block">
                                        <small style="color:#ff6666;">仅保存数据,不改变审核状态</small>
                                </div>
                                </div>
                            </div>
                            <?php endif;?>
                            <?php if(in_array($detail['status'],[2,3,4,5]) && $detail['is_pay']==2 ):?>
                           <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 支付方式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="data[pay_type]" value="0" data-am-ucheck <?= $detail['pay_type'] == 0 ? 'checked' : '' ?>>
                                        立即支付
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="data[pay_type]" value="1" data-am-ucheck <?= $detail['pay_type'] == 1 ? 'checked' : '' ?>>
                                        货到付款
                                    </label>
                                  
                                </div>
                            </div>
                            <?php endif; ?>
                           
                            <div class="am-form-group">
                                <input name="data[id]" type="hidden" value="<?= $detail['id']??'' ;?>">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">确认操作
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
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
    $(function () {
        // 选择图片
        $('.upload-file').selectImages({
            name: 'data[images][]' , multiple: true
        }); 

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
    });
    
    function MathFree(){
        // var price = $('#price')[0].value;
        var price = parseFloat($('#price')[0].value.replace(/\,/g, ''), 10);
        var other_free = $('#other_free')[0].value;
        var pack_free = $('#pack_free')[0].value;
        
        console.log(price,456);
        var total = Math.floor(price *100 )+ Math.floor(pack_free *100)+ Math.floor(other_free *100);  
        $("#all").val(total/100)
    }
    
    // 实时计算
    function caleAmount(){
       var form = $("#my-form").serializeArray();
       var newdata = [];
       var is_auto_free = <?=$is_auto_free?>;
       console.log(is_auto_free,5);
       if(is_auto_free==0){
           return false;
       }
       form.map(function(val,key){
			newdata[val.name]=val.value;
	   });
       $.ajax({
          type:"POST",
          url:"<?= url('store/trOrder/caleAmount')?>",
          data:{pid:newdata['data[id]'],line_id:newdata['data[line_id]'],length:newdata['data[length]'],width:newdata['data[width]'],height:newdata['data[height]'],weight:newdata['data[weight]']},
          dataType:'json',
          success:function(res){
             if (res.code==1){
                 $('#price').val(res.msg.price);
                 $('#oWei').val(res.msg.oWeigth);
                 $('#weigthV').val(res.msg.weightV);
                 $('#pack_free').val(res.msg.packfree);
                 var other_free = $('#other_free').val();
                 var pack_free = $('#pack_free').val();
                 var num = parseFloat(res.msg.price);
                 var total = Number(num) + Number(pack_free) + Number(other_free);
                 $("#all").val(total)
             } 
          }
       })    
    }
</script>
<style>
    .wd {
        width: 200px;
    }
    .country-search-panle {
        width: 100%; height: auto; max-height: 300px;
        background: #fff;
        border: 1px solid #eee;
        position: absolute;
        top:25px; left: 0; z-index: 999;
    }
    .country-search-title { height: 25px; line-height: 25px; font-size: 14px; padding-left: 10px;}
    .country-search-content { width: 100%; height: auto; }
    .country-search-content p { padding-left: 10px; height: 25px; cursor: pointer; line-height: 25px; font-size: 14px;}
    .country-search-content p:hover { background: #0b6fa2; color: #fff;}
    .hidden { display: none}
    .show { display: block;}
    
    .category span { display: inline-block; padding:0 5px; font-size:14px; background:#3bb4f2; margin-right:5px; color:#fff; }
    .cursor { cursor:pointer;}
    
    .category-layer { width:100%; height:100%; position:fixed; display:none; background:rgba(0,0,0,.5); top:0; left:0;}
    .category-dialog { background:#fff; width: 450px; min-height:200px; border-radius:5px; position:absolute; top:40%; left:50%; margin-right:-175px; padding:5px;}
    .category-title { height:30px; line-height:30px; font-size:13px; padding:5px;}
    .category-content { width:95%; margin:0 auto;}
    
    .category-item { width:100%; height:auto; margin-bottom:5px;}
    .category-name { font-size:14px; color:#666;}
    
    .category-content ul { width:100%; height:auto;}
    .category-content ul li { display:inline-block; background:#eee; height:25px; line-height:25px; border-radius:5px; cursor:pointer; padding:0 8px; margin-right:5px; font-size:13px;}
     .category-content ul li.action { background:#3bb4f2;color:#fff;}
     
     .category-btn { width:95%; margin: 30px auto 0 auto;}
     .category-btn a { display:inline-block; width:auto; padding:0 5px; font-size:13px;}
     
     .span { display:inline-block; font-size:13px; color:#666; margin-bottom:10px;}
</style>