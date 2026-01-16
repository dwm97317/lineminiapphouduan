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
                                    <input type="text" class="tpl-form-input" name="url"
                                           value="<?= $data['url'] ?>" placeholder="请输入代购链接" required>
                                   
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品单价</label>
                                <div class="am-u-sm-9 am-u-end wd">
                                    <input type="text" class="tpl-form-input" name="price"
                                           value="<?= $data['price'] ?>" placeholder="请输入商品单价" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品规格</label>
                                <div class="am-u-sm-9 am-u-end wd">
                                    <input type="text" class="tpl-form-input" name="spec"
                                           value="<?= $data['spec'] ?>" placeholder="请输入商品规格" required>
                                </div>
                            </div>
                             <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">商品数量</label>
                                <div class="am-u-sm-9 am-u-end wd">
                                    <input type="text" class="tpl-form-input" name="num"
                                           value="<?= $data['num'] ?>" placeholder="请输入商品数量" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">运费 （快递价格）</label>
                                <div class="am-u-sm-9 am-u-end wd">
                                    <input type="text" class="tpl-form-input" name="free"
                                           value="<?= $data['free'] ?>" placeholder="请输入运费" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">服务费</label>
                                <div class="am-u-sm-9 am-u-end wd">
                                    <input type="text" class="tpl-form-input" name="service_free"
                                           value="<?= $data['service_free'] ?>" placeholder="请输入服务费" >
                                </div>
                            </div>
                            <?php if ($data['status']!=-1 && $data['status']!=8): ?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">是否确认 </label>
                                <div class="am-u-sm-9 am-u-end wd">
                                    <select name="status">
                                        <option value="2"  <?= $data['status'] == 2 ? 'selectd' : '' ?>>已确认</option>
                                        <option value="-1" <?= $data['status'] == -1 ? 'selectd' : '' ?>>未通过</option>
                                    </select>
                                     <div class="help-block">
                                        <small style="color:red;">不通过将自动退款（款项包含 代购费 （物品价值）+ 运费 ）；服务费另行处理</small>
                                </div>
                                </div>
                               
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">备注</label>
                                <div class="am-u-sm-9 am-u-end wd">
                                    <input type="text" class="tpl-form-input" name="reason"
                                           value="<?= $data['reason'] ?>" placeholder="请输入备注" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
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
