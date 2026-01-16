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
                <form  id="my-form"   action="<?= url('store/package.index/save')?>" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑包裹</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">id </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class='am-form-static'><?= $detail['id'] ?></div>
                                    <input type="hidden" name="data[id]" value="<?= $detail['id'] ?>">
                                </div>
                            </div>
                          
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">预报单号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input"  name="data[express_num]"
                                           value="<?= $detail['express_num'] ?>" placeholder="预报单号" autocomplete="off" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label"> 选择用户 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="widget-become-goods am-form-file am-margin-top-xs">
                                        <button type="button"
                                                class="j-selectUser upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择用户
                                        </button>
                                        <div class="user-list uploader-list am-cf">
                                           <div class="file-item">
                                               
                                               <a href="<?= $detail['user']['avatarUrl'] ?>" title="<?= $detail['user']['nickName'] ?>" target="_blank">
                                                 <img src="<?= $detail['user']['avatarUrl'] ?>">
                                               </a>
                                               <input type="hidden" name="data[user_id]" value="<?= $detail['user']['user_id'] ?>">
                                           </div>
                                        </div>
                                        <div class="am-block">
                                         <small>
                                            昵称：<?= $detail['user']['nickName'] ?> 
                                            ID：<?= $detail['user']['user_id'] ?>
                                            CODE：<?= $detail['user']['user_code'] ?>
                                        </small> 
                                         <small>点击选择用户更改包裹所属用户</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">运往国家 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="data[country]"
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
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 所属仓库 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="data[store_id]"
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
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">选择物流 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="data[express_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" >
                                        <option value=""></option>
                                        <?php if (isset($expressList) && !$expressList->isEmpty()):
                                            foreach ($expressList as $item): ?>
                                                <?php if(isset($detail['express_id'])): ?>
                                                   <option value="<?= $item['express_id'] ?>" <?= $detail['express_id'] == $item['express_id'] ? 'selected' : '' ?> ><?= $item['express_name'] ?></option>
                                                <?php else: ?>  
                                                   <option value="<?= $item['express_id'] ?>" ><?= $item['express_name'] ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>请选择包裹运输到仓库采用的物流,默认 为 "顺丰速运"</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">总价值</label>
                                <div class="am-u-sm-9 am-u-end wd">
                                    <input type="text" class="tpl-form-input" name="data[price]"
                                           value="<?= $detail['price'] ?>" placeholder="请输入价格" required>
                                </div>
                            </div>
                            <?php if($detail['source']==7): ?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">上门取件费</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="data[visit_free]"
                                           value="<?= $detail['visit_free']??'' ;?>" placeholder="请输入上门取件费">
                                </div>
                            </div>
                            <?php endif ?>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">包裹信息 (可选填)</label>
                                <div class="am-u-sm-9 am-u-end" style="position: relative">
                                     <div class="span">
                                         长(<?= $set['size_mode']['unit'] ?>) <input type="text" class="tpl-form-input" style="width:80px" name="data[length]"
                                           value="<?= $detail['length']??'' ;?>" placeholder="请输入长">
                                     </div>
                                     <div class="span">
                                         宽(<?= $set['size_mode']['unit'] ?>) <input type="text" class="tpl-form-input" style="width:80px" name="data[width]"
                                           value="<?= $detail['width']??'' ;?>" placeholder="请输入宽">
                                     </div>
                                     <div class="span">
                                         高(<?= $set['size_mode']['unit'] ?>) <input type="text" class="tpl-form-input" style="width:80px" name="data[height]"
                                           value="<?= $detail['height']??'' ;?>" placeholder="请输入高">
                                     </div>
                                     <div class="span">
                                         称重(<?= $set['weight_mode']['unit'] ?>) <input type="text" class="tpl-form-input" style="width:80px" name="data[weight]"
                                           value="<?= $detail['weight']??'' ;?>" placeholder="请输入重量">
                                     </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">物品品类</label>
                                <div class="am-u-sm-9 am-u-end wd">
                                    <input name="data[class_ids]" type="hidden" id="class">   
                                     <div class="category">
                                         <?php if(isset($list)): ?>
                                             <?php foreach($list as $item): ?>
                                             <span><?= $item['class_name']; ?></span>
                                             <?php endforeach ;?>
                                         <?php endif ?>
                                         <span class="cursor" onclick="CategorySelect.display()">+</span></div>
                                </div>
                            </div>
                            
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">用户备注</label>
                                <div class="am-u-sm-4 am-u-end">
                                    <input type="text" disabled="true" class="tpl-form-input" name="data[remark]"
                                           value="<?= $detail['remark'] ?>" placeholder="请输入备注" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">管理员备注</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="data[admin_remark]"
                                           value="<?= $detail['admin_remark'] ?>" placeholder="请输入备注" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">包裹图片 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file am-btn am-btn-secondary am-radius package_img">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                            <?php foreach ($detail['packageimage'] as $key => $item): ?>
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
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">包裹存放位置</label>
                                <div class="am-u-sm-9 am-u-end" style="position: relative">
                                     <select id="select-shelf"  data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'选择货架', maxHeight: 400}" onchange="getSelectData(this)" data-select_type = 'shelf_unit'>
                                            <option value=""></option>
                                            <?php if (isset($shelf) && isset($detail['shelfunititem'])): foreach ($shelf as $itemr): ?>
                                                   <option value="<?= $itemr['id'] ?>" <?= $detail['shelfunititem']['shelfunit']['shelf']['id'] == $itemr['id'] ? 'selected' : '' ?>><?= $itemr['shelf_name']; ?></option>
                                            <?php endforeach; endif; ?>
                                     </select> - <select id="select_shelf_unit" name="data[shelf_unit_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择货位', maxHeight: 400}">
                                        <option value=""></option>
                                        <?php if (isset($shelfitem) && isset($detail['shelfunititem']['shelfunit'])): foreach ($shelfitem as $itemrr): ?>
                                                <option value="<?= $itemrr['shelf_unit_id']; ?>"   <?= $detail['shelfunititem']['shelfunit']['shelf_unit_id'] == $itemrr['shelf_unit_id'] ? 'selected' : '' ?>><?= $itemrr['shelf_unit_no']; ?>号</option>
                                        <?php endforeach;endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>包裹存放位置：请先选择货架 - 然后在选择货位</small>
                                </div>
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
    <!--分类-->
    <div class="category-layer">
         <div class="category-dialog" style="cursor: move;">
              <div class="layui-layer-title">选择分类</div>
              <div class="category-content layui-layer-content">
                   <?php foreach ($category as $c): ?>
                   <div class="category-item">
                        <div class="category-name"><?= $c['name'] ;?></div>
                        <ul>
                           <?php foreach ($c['child'] as $ch): ?>  
                           <li data-id="<?= $ch['category_id'] ;?>" ><?= $ch['name'] ?></li>
                           <?php endforeach; ?>
                        </ul>
                   </div>    
                   <?php endforeach; ?>
              </div>
             <div class="layui-layer-btn layui-layer-btn-r">
                 <a onclick="CategorySelect.bindConfirm()" href="javascript:;" class="layui-layer-btn0">确定</a>
                 <a onclick="CategorySelect.out()" href="javascript:;" class="layui-layer-btn1">取消</a></div>
         </div> 
    </div>
</div>
<script id="tpl-user-item" type="text/template">
    {{ each $data }}
    <div class="file-item">
        <a href="{{ $value.avatarUrl }}" title="{{ $value.nickName }} (ID:{{ $value.user_id }})" target="_blank">
            <img src="{{ $value.avatarUrl }}">
        </a>
        <input type="hidden" name="data[user_id]" value="{{ $value.user_id }}">
    </div>
    {{ /each }}
</script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<!-- 图片文件列表模板 -->
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}
<script>
     // 选择用户
    $('.j-selectUser').click(function () {
        var $userList = $('.user-list');
        $.selectData({
            title: '选择用户',
            uri: 'user/lists',
            dataIndex: 'user_id',
            done: function (data) {
                var user = [data[0]];
                $userList.html(template('tpl-user-item', user));
            }
        });
    });

    var _render = false;
    var getSelectData = function(_this){
        if (_render){
            return 
        }
        console.log(_render);
        var sType = _this.getAttribute('data-select_type');
        var api_group = {'shelf':'<?= url('store/shelf_manager.index/getShelf')?>','shelf_unit':'<?= url('store/shelf_manager.index/getshelf_unit')?>'};
        var $selected1 = $('#select-shelf');
        var $selected2 = $('#select_shelf_unit');
        
        if (sType=='shelf'){
            var data = {'shop_id':_this.value}
        }
        if (sType=='shelf_unit'){
            var data = {'shelf_id':_this.value}
        }
       
       
        $.ajax({
            type:"GET",
            url:api_group[sType],
            data:data,
            dataType:'json',
            success:function(res){
                console.log(res);
                if (sType=='shelf'){
                    $selected1.html('');
                    $selected2.html('');
                    var _shelf = res.data.shelf.data;
                    if(_shelf.length==0){
                        $selected2.html('');
                        return;
                    }
                    var _shelfunit = res.data.shelfunit.data;
                }
                if (sType=='shelf_unit'){
                    $selected2.html('');
                    var _shelfunit = res.data.shelfunit.data;
                }

                if (sType=='shelf'){
                    for (var i=0;i<_shelf.length;i++){
                        $selected1.append('<option value="' + _shelf[i]['id'] +'">' + _shelf[i]['shelf_name'] + '</option>');
                    }
                    for (var i=0;i<_shelfunit.length;i++){
                        $selected2.append('<option value="' + _shelfunit[i]['shelf_unit_id'] +'">' + _shelfunit[i]['shelf_unit_no'] + '号</option>');
                    }
                }else{
                    for (var i=0;i<_shelfunit.length;i++){
                        // _html += '<option value="">'+_data[i]['shelf_name']+'</option>';
                        $selected2.append('<option value="' + _shelfunit[i]['shelf_unit_id'] +'">' + _shelfunit[i]['shelf_unit_no'] + '号</option>');
                    }
                }
                _render = true;
                setTimeout(function() {
                    _render = false;
                }, 100);
            }
        })
         _render = true;
        setTimeout(function() {
                _render = false;
            }, 100);
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
        $('.package_img').selectImages({
            name: 'data[images][]' , multiple: true
        }); 
        
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
        
     })
       // 分类选择器
    var CategorySelect = {
        data:[],
        display:function(){
           var categoryLayer = document.getElementsByClassName('category-layer')[0];
           if (categoryLayer.style.display=='block'){
               categoryLayer.style.display = 'none';
           }else{
               categoryLayer.style.display = 'block';
           }
           this.categoryLayer = categoryLayer;
           CategorySelect.bindClick();
        },
        isExist:function(val){
          for (var key in this.data){
              if (this.data[key]['id']==val){
                  return true;
              }
          } 
          return false;
        },
        out:function(){
            var categoryLayer = document.getElementsByClassName('category-layer')[0];
            categoryLayer.style.display = 'none';
        },
        bindClick:function(){
           var _k = this.categoryLayer.getElementsByTagName('li');
           for (var _key in _k){
               _k[_key].onclick = function(){
                   var id = this.getAttribute('data-id');
                   if (CategorySelect.isExist(id)){
                       return false;
                   }
                   CategorySelect.data.push({
                       id:id,
                       name:this.innerHTML,
                   });
                   this.className = 'action';
               }
           }
        },
        bindConfirm:function(){
            var _span = '';
            var _input = '';
            for (var _k in this.data){
                _span+='<span>'+this.data[_k]['name']+'</span>';
                _input+=this.data[_k]['id']+',';
            }
            $('.category').html(_span+'<span class="cursor" onclick="CategorySelect.display()">+</span>');
            CategorySelect.display();
            $('#class').val(_input);
        }
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
    
    .category-layer { width:100%; height:100%; position:fixed; display:none; background:rgba(0,0,0,.5); top:0; left:0;z-index:9999}
    .category-dialog {background: #fff;
    width: 600px;
    min-height: 250px;
    border-radius: 0px;
    position: absolute;
    top: 30%;
    left: 30%;
    padding: 10px;}
    .category-title { height:30px; line-height:30px; font-size:13px; padding:5px;}
    .category-content { width:95%; margin:15px auto;}
    
    .category-item { width:100%; height:auto; margin-bottom:5px;}
    .category-name { font-size:16px; color:#666;padding: 5px 0px;}
    
    .category-content ul { width:100%; height:auto;}
    .category-content ul li {     display: inline-block;
    background: #eee;
    height: 30px;
    line-height: 30px;
    border-radius: 20px;
    cursor: pointer;
    padding: 0px 15px;
    margin-right: 5px;
    font-size: 13px;}
     .category-content ul li.action { background:#24d5d8;color:#fff;}
     
     .category-btn { width:95%; margin: 30px auto 0 auto;}
     .category-btn a { display:inline-block; width:auto; padding:0 5px; font-size:13px;}
     
     .span { display:inline-block; font-size:13px; color:#666; margin-bottom:10px;}
     #results{margin-top:10px;}
</style>