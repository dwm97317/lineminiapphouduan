<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
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
</style>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" action="<?= url('store/buyer/save')?>" id="form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑代购单</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">id </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class='am-form-static'><?= $data['b_order_id'] ?></div>
                                    <input type="hidden" name="id" value="<?= $data['b_order_id'] ?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">代购单号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class='am-form-static'><?= $data['order_sn'] ?></div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">代购链接 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class='am-form-static'><?= $data['url'] ?></div>
                                </div>
                            </div>
                            <?php if($data['status']>=2) :?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">快递单号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="express_num"
                                           value="<?= $data['express_num']??''; ?>" placeholder="请输入快递单号" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">物流公司 </label>
                                 <div class="am-u-sm-9 am-u-end">
                                    <select name="express_id"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" >
                                        <option value=""></option>
                                        <?php if (isset($expressList) && !$expressList->isEmpty()):
                                            foreach ($expressList as $item): ?>
                                                <?php if(isset($data['express_id'])): ?>
                                                   <option value="<?= $item['express_id'] ?>" <?= $data['express_id'] == $item['express_id'] ? 'selected' : '' ?> ><?= $item['express_name'] ?></option>
                                                <?php else: ?>  
                                                   <option value="<?= $item['express_id'] ?>" ><?= $item['express_name'] ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>请选择物流,默认 为 "顺丰速运"</small>
                                    </div>
                                </div>
                            </div>
                            <?php endif ;?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">当前状态 </label>
                                 <div class="am-u-sm-9 am-u-end">
                                    
                                    <label class="am-radio-inline">
                                        <input type="radio" name="status" value="3" data-am-ucheck
                                        <?= $data['status'] == '3' ? 'checked' : '' ?>
                                        >
                                        已发货
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="status" value="4" data-am-ucheck
                                         <?= $data['status'] == '4' ? 'checked' : '' ?>
                                        >
                                        已入库
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="status" value="5" data-am-ucheck
                                         <?= $data['status'] == '5' ? 'checked' : '' ?>
                                        >
                                        同步集运
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="status" value="6" data-am-ucheck
                                         <?= $data['status'] == '6' ? 'checked' : '' ?>>
                                        已完成
                                    </label>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <input type="hidden" name="type" value="order_update">
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
    
   

    // dialog.toast('success','处理中');

    // 国家搜索器
    var countrySearch = {
        data:{},
        key:"",
        do:function (_this) {
           var v = _this.value;
           if (!v){
               return false;
           }

           this.key = v;
           this.doRequest();
        },
        render:function(){
           var _temp = '';
           this.data.map((item,index)=>{
               _temp+= '<p data-id="'+item['id']+'">'+item['title']+'</p>';
           });
           $('.country-search-panle').show();
           $('.country-search-content').html(_temp);
           this.select();
        },
        select:function(){
           $('.country-search-content p').click(function(e){
               var dataId = $(this).attr('data-id');
               //console.log(1111);
               $('#oInp').val(dataId);
              // console.log(111,33);
               $("#country").val($(this).html());
               stopBubble(e);
           })
        },
        doRequest:function(){
           var params = {
               k:this.key,
           };
           var that = this;
           $.ajax({
               type:'post',
               url:"<?= url('store/package.report/getCountry') ?>",
               data:params,
               dataType:'json',
               success:function (res) {
                   if (res.code==1){
                       that.data = res.msg;
                       that.render();
                   }
               }
           })
        }
    }
    function stopBubble(e){
        //e是事件对象
        if(e.stopPropagation){
            e.stopPropagation();
        }else{
            e.stopBubble = true;
        }
    }
    document.onclick = function () {
        $('.country-search-panle').hide();
    }
    
      $(function () {

         // 选择图片
        $('.upload-file_enter').selectImages({
            name: 'shop[enter_image_id]'
        });
         // 选择图片
        $('.upload-file_spilt').selectImages({
            name: 'shop[spilt_image_id]'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
    });
</script>
