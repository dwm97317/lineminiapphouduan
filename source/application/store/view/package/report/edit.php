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
                <form action="<?= url('store/package.report/save')?>" id="form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑包裹</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">id </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div><?= $list['id'] ?></div>
                                    <input type="hidden" name="id" value="<?= $list['id'] ?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">预报单号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div><?= $list['order_sn'] ?></div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">用户昵称</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div><?= $list['nickName'] ?></div>
                                </div>
                            </div>
                            <div class="am-form-group" >
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require ">仓库</label>
                                <div class="am-u-sm-9 am-u-end wd">
                                    <select name="store_id"  >
                                        <?php if (isset($shopList)): foreach ($shopList as $item): ?>
                                            <option value="<?= $item['shop_id'] ?>"
                                                <?= $item['shop_name'] == $list['shop_name'] ? 'selected' : '' ?>><?= $item['shop_name'] ?>
                                            </option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">运往国家</label>
                                <div class="am-u-sm-9 am-u-end wd" style="position: relative">
                                    <input type="text" class="tpl-form-input" id="country" name="country"
                                           value="<?= $list['title'] ?>" placeholder="请输入国家" autocomplete="off" required oninput="countrySearch.do(this)">
                                    <input type="hidden" id="oInp" name="country_id" value="<?= $list['country_id'] ?>">
                                    <div class="country-search-panle hidden">
                                         <div class="country-search-title">搜索结果</div>
                                         <div class="country-search-content">
                                              <p>阿拉伯</p>
                                              <p>阿拉斯加</p>
                                         </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">总价值</label>
                                <div class="am-u-sm-9 am-u-end wd">
                                    <input type="text" class="tpl-form-input" name="price"
                                           value="<?= $list['price'] ?>" placeholder="请输入价格" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">备注</label>
                                <div class="am-u-sm-9 am-u-end wd">
                                    <input type="text" class="tpl-form-input" name="remark"
                                           value="<?= $list['remark'] ?>" placeholder="请输入备注" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">是否入库 </label>
                                <div class="am-u-sm-9 am-u-end wd">
                                    <select name="status">
                                        <option value="1"  <?= $list['status'] == 1 ? 'selectd' : '' ?>>未入库</option>
                                        <option value="2" <?= $list['status'] == 2 ? 'selectd' : '' ?>>已入库</option>
                                    </select>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="button" class="j-submit am-btn am-btn-secondary">提交
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
    
    formSubmit.init({
       formElm:'#form',
       url:'<?= url('store/package.report/save')?>',
       formBtn:'.am-btn-secondary',
    }); 

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
</script>
