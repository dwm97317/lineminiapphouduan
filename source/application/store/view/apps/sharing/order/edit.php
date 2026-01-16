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
    .span { display:inline-block; font-size:13px; color:#666; margin-bottom:10px;}
</style>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form  id="my-form" action="<?= url('store/apps.sharing.order/edit')?>" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑拼团订单</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">id </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class='am-form-static'><?= $detail['order_id'] ?></div>
                                    <input type="hidden" name="data[order_id]" value="<?= $detail['order_id'] ?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">系统订单</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div  class='am-form-static'><?= $detail['order_sn'] ?></div>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">团长昵称</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div  class='am-form-static'><?= $detail['user']['nickName'] ?></div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">拼团名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input"  name="data[title]"
                                           value="<?= $detail['title'] ?>" placeholder="拼团名称" autocomplete="off" required>
                                </div>
                            </div>
 
                             <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 所属仓库 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="data[storage_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" onchange="getSelectData(this)" data-select_type='shelf'>
                                        <?php if (isset($shopList) && !$shopList->isEmpty()):
                                            foreach ($shopList as $item): ?>
                                                 <?php if(isset($detail['storage_id'])): ?>
                                                      <option value="<?= $item['shop_id'] ?>"  <?= $detail['storage_id'] == $item['shop_id'] ? 'selected' : '' ?>><?= $item['shop_name'] ?></option>
                                                <?php else: ?>  
                                                     <option value="<?= $item['shop_id'] ?>"><?= $item['shop_name'] ?></option>
                                                <?php endif; ?>
                                             
                                            <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>你想录入到哪个仓库?</small>
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
                                                <option value="<?= $item['id'] ?>"  <?= $detail['line']['id'] == $item['id'] ? 'selected' : '' ?>><?= $item['name'] ?></option>
                                            <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">运往国家 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="data[country_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" >
                                        <option value=""></option>
                                        <?php if (isset($countryList) && !$countryList->isEmpty()):
                                            foreach ($countryList as $item): ?>
                                                <?php if(isset($detail['country']['id'])): ?>
                                                   <option value="<?= $item['id'] ?>" <?= $detail['country']['id'] == $item['id'] ? 'selected' : '' ?> ><?= $item['title'] ?></option>
                                                <?php else: ?>  
                                                   <option value="<?= $item['id'] ?>" ><?= $item['title'] ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>请选择包裹将要寄往的国家</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">拼团取货地址</label>
                                <div class="tpl-table-black-operation am-u-sm-9 am-u-end" style="display:inline-block">
                                     <a class='tpl-table-black-operation-green j-changeaddress' href="javascript:void(0);" data-user_id="<?= $detail['member_id'] ?>" data-id="<?= $detail['order_id'] ?>"><i class="am-icon-pencil"></i>修改地址</a>
                                     <div  class='am-form-static'>
                                        联系人：<?= $detail['address']['name'] ?>  
                                        联系电话：<?= $detail['address']['phone'] ?></div>
                                </div>
                      
                                    
                    
                                
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">拼团规则</label>
                                <div class="am-u-sm-9 am-u-end" style="position: relative">
                                     <div class="span">
                                         起运重量 <input type="text" class="tpl-form-input" style="width:180px" name="data[predict_weight]"
                                           value="<?= $detail['predict_weight']??'' ;?>" placeholder="起运重量">
                                     </div>
                                     <div class="span">
                                         拼团重量 <input type="text" class="tpl-form-input" style="width:180px" name="data[min_weight]"
                                           value="<?= $detail['min_weight']??'' ;?>" placeholder="拼团重量">
                                     </div>
                                     <div class="span">
                                         拼团人数 <input type="text" class="tpl-form-input" style="width:180px" name="data[max_people]"
                                           value="<?= $detail['max_people']??'' ;?>" placeholder="拼团人数">
                                     </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">开团时间 </label>
                                 <div class="am-u-sm-9 am-u-end wd">
                                     <input autocomplete="off" type="text"  name="data[start_time]" placeholder="请选择开团日期" value="<?php echo date("Y-m-d H:i:s",$detail['start_time']) ?>" id="datetimepicker" class="am-form-field">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">结束时间 </label>
                                 <div class="am-u-sm-9 am-u-end wd">
                                     <input autocomplete="off"  type="text"  name="data[end_time]" placeholder="请选择结束日期" value="<?php echo date("Y-m-d H:i:s",$detail['end_time']) ?>" id="datetimepicker2" class="am-form-field">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">备注</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="data[group_leader_remark]"
                                           value="<?= $detail['group_leader_remark'] ?>" placeholder="请输入备注" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否通过审核 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="data[is_verify]" value="1" data-am-ucheck
                                               <?= $detail['is_verify'] == 1 ? 'checked' : '' ?>>
                                        通过
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="data[is_verify]" value="2" data-am-ucheck <?= $detail['is_verify'] == 2 ? 'checked' : '' ?>>
                                        不通过
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否推荐 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="data[is_recommend]" value="0" data-am-ucheck
                                               <?= $detail['is_recommend'] == 0 ? 'checked' : '' ?>>
                                        不置顶
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="data[is_recommend]" value="1" data-am-ucheck <?= $detail['is_recommend'] == 1 ? 'checked' : '' ?>>
                                        置顶
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 是否热门 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="data[is_hot]" value="0" data-am-ucheck
                                               <?= $detail['is_hot'] == 0 ? 'checked' : '' ?>>
                                        否
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="data[is_hot]" value="1" data-am-ucheck <?= $detail['is_hot'] == 1 ? 'checked' : '' ?>>
                                        是
                                    </label>
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
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<!-- 图片文件列表模板 -->
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}
<script>
   
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
           console.log(this.data.data);
           this.data.data.map((item,index)=>{
               _temp+= '<p data-id="'+item['id']+'">'+item['title']+'</p>';
           });
           $('.country-search-panle').show();
           $('.country-search-content').html(_temp);
           this.select();
        },
        select:function(){
           $('.country-search-content p').click(function(e){
               var dataId = $(this).attr('data-id');
               $('#oInp').val(dataId);
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
    
            // 变更地址
        $('.j-changeaddress').click(function (e) {
            var user_id = $(this).data().user_id;
            if(!user_id){
                layer.alert('用户信息有误', {icon: 5});
                return false;
            }
            var id=  $(this).data().id;
            $.selectData({
                title: '变更地址',
                uri: 'Address/AddressAllList'+'/user_id/'+user_id,
                dataIndex: 'address_id',
                done: function (list) {
                    var data = {};
                    var select_ids = [];
                    if (list.length>1){
                        layer.alert('只能勾选一个', {icon: 5});
                        return;
                    }
                    console.log(list);
                    // 请求服务器修改地址
                    $.ajax({
                        type:"POST",
                        url:'<?= url('store/apps.sharing.order/updateAddress') ?>',
                        data:{id:id,address_id:list[0]["address_id"]},
                        dataType:"JSON",
                        success:function(result){
                           window.location.reload(true);
                        }
                    })
                    
                }
            });
        });
    
    document.onclick = function () {
        $('.country-search-panle').hide();
    }
    
    $('#datetimepicker').datetimepicker({
          format: 'yyyy-mm-dd hh:ii'
        });
        
        
        $('#datetimepicker').datetimepicker().on('changeDate', function(ev){
            $('#datetimepicker').datetimepicker('hide');
          });
          
              $('#datetimepicker2').datetimepicker({
          format: 'yyyy-mm-dd hh:ii'
        });
        
        
        $('#datetimepicker2').datetimepicker().on('changeDate', function(ev){
            $('#datetimepicker2').datetimepicker('hide');
          });
    
     $(function () {
         
         /* 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
         
        // 选择图片
        $('.upload-file').selectImages({
            name: 'data[images][]' , multiple: true
        }); 
     })
     
        
    
</script>
