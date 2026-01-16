<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<script type="text/javascript" src="assets/store/js/webcam.min.js"></script>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" action="<?= url('store/package.index/uodatepackStatus')?>" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">手动录入 <a href="<?= url('store/package.index/import')?>">批量导入</a></div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">快递单号[包裹单号] </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" id="express_num" name="data[express_num]"
                                           value="<?= $data['express_num']??'' ;?>" placeholder="请输入包裹单号" required>
                                    <div class="am-block">
                                         <small>可使用扫码枪进行输入</small>
                                    </div>       
                                </div>
                            </div>
                       
                            <div class="am-form-group" style="<?= $set['usercode_mode']['is_show']!=1?'display：block':'display:none' ;?>">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label"> 输入用户ID </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="widget-become-goods am-form-file am-margin-top-xs">
                                        <input type="text" class="tpl-form-input" name="data[user_id]"
                                           value="<?= $data['member_id']??'' ;?>" placeholder="输入用户ID">
                                        <div class="am-block">
                                            <small>输入用户ID与【选择用户】两者选其一</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group" style="<?= $set['usercode_mode']['is_show']!=0?'display：block':'display:none' ;?>">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label"> 输入用户编号（CODE） </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="widget-become-goods am-form-file am-margin-top-xs">
                                        <input type="text" class="tpl-form-input" name="data[user_code]"
                                           value="<?= $data['member']['user_code']??'' ;?>" placeholder="输入用户CODE">
                                        <div class="am-block">
                                            <small>输入用户CODE与【选择用户】两者选其一</small>
                                        </div>
                                    </div>
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
                                        </div>
                                        <div class="am-block">
                                            <small>点击选择包裹所属用户</small>
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
                                                <?php if(isset($data['country'])): ?>
                                                   <option value="<?= $item['id'] ?>" <?= $data['country_id'] == $item['id'] ? 'selected' : '' ?> ><?= $item['title'] ?></option>
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
                                    <select name="data[shop_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" onchange="getSelectData(this)" data-select_type='shelf'>
                                        <?php if (isset($shopList) && !$shopList->isEmpty()):
                                            foreach ($shopList as $item): ?>
                                                 <?php if(isset($data['storage_id'])): ?>
                                                      <option  value="<?= $item['shop_id'] ?>"<?= $data['storage_id'] == $item['shop_id'] ? 'selected' : '' ?>><?= $item['shop_name'] ?></option>
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
                                                <?php if(isset($data['express_id'])): ?>
                                                   <option value="<?= $item['express_id'] ?>" <?= $data['express_id'] == $item['express_id'] ? 'selected' : '' ?> ><?= $item['express_name'] ?></option>
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
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">包裹信息 (可选填)</label>
                                <div class="am-u-sm-9 am-u-end" style="position: relative">
                                     <div class="span">
                                         长(<?= $set['size_mode']['unit'] ?>) <input type="text" class="tpl-form-input" style="width:80px" name="data[length]"
                                           value="<?= $data['length']??'' ;?>" placeholder="请输入长">
                                     </div>
                                     <div class="span">
                                         宽(<?= $set['size_mode']['unit'] ?>) <input type="text" class="tpl-form-input" style="width:80px" name="data[width]"
                                           value="<?= $data['width']??'' ;?>" placeholder="请输入宽">
                                     </div>
                                     <div class="span">
                                         高(<?= $set['size_mode']['unit'] ?>) <input type="text" class="tpl-form-input" style="width:80px" name="data[height]"
                                           value="<?= $data['height']??'' ;?>" placeholder="请输入高">
                                     </div>
                                     <div class="span">
                                         称重(<?= $set['weight_mode']['unit'] ?>) <input type="text" id="weight" class="tpl-form-input" style="width:80px" name="data[weigth]"
                                           value="<?= $data['weigth']??'' ;?>" placeholder="请输入重量">
                                     </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">总价值</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="data[price]"
                                           value="<?= $data['price']??'' ;?>" placeholder="请输入价格">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">物品品类</label>
                                <div class="am-u-sm-9 am-u-end wd">
                                    <input name="data[class_ids]" type="hidden" id="class">   
                                     <div class="category">
                                         <?php if(isset($data['shop_class'])): ?>
                                             <?php foreach($data['shop_class'] as $item): ?>
                                             <span><?= $item; ?></span>
                                             <?php endforeach ;?>
                                         <?php endif ?>
                                         <span class="cursor" onclick="CategorySelect.display()">+</span></div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">备注</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="data[remark]"
                                           value="<?= $data['remark']??'' ;?>" placeholder="请输入备注" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">包裹扩展信息 (可选填)</label>
                                <div class="am-u-sm-9 am-u-end" style="position: relative">
                                      <div class="" style="display:inline-block;" >
                                            <div class="am-form-file">
                                                <div class=" am-form-file">
                                                    <button type="button"
                                                            class="upload-file_enter am-btn am-btn-secondary am-radius">
                                                        <i class="am-icon-cloud-upload"></i> 选择图片
                                                    </button>
                                                    <div id="uploadsf" class="uploader-list am-cf">
                                                        <?php if(isset($data['packageimage'])) foreach ($data['packageimage'] as $key => $item): ?>
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
                                            </div>
                                      </div>
                                </div>
                            </div>
                             <?php if ($set['is_camera']==1): ?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">本地拍照</label>
                                <div class="am-u-sm-9 am-u-end" style="position: relative">
                                      <div class="" style="display:inline-block;" >
                                            <div class="am-form-file">
                                                <div class=" am-form-file">
                                                    <form>
                                                		<input type=button value="点击拍照" onClick="take_snapshot()">
                                                	</form>
                                                </div>
                                            </div>
                                            <div id="results"></div>
                                            <a href="#" type='primary' class='j-uploadimg'>确认上传</a>
                                      </div>
                                </div>
                            </div>
                            <div id="my_camera" style="width: 320px;height: 240px;position: fixed;top: 150px;right: 200px;"></div>
                            <script language="JavaScript">
                        		Webcam.set({
                        			width: 480,
                        			height: 360,
                        			image_format: 'jpg',
                        			jpeg_quality: 100,
                        			flip_horiz: true
                        		});
                        		Webcam.attach( '#my_camera' );
                        	</script>
                        	 <?php endif; ?>
                        
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">包裹存放位置</label>
                                
                                <div class="am-u-sm-9 am-u-end" style="position: relative">
                                     <select id="select-shelf"  data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'选择货架', maxHeight: 400}" onchange="getSelectData(this)" data-select_type = 'shelf_unit'>
                                            <option value=""></option>
                                            <?php if (isset($shelf) && isset($data['shelfunititem'])): foreach ($shelf as $itemr): ?>
                                                   <option value="<?= $itemr['id'] ?>" <?= $data['shelfunititem']['shelfunit']['shelf']['id'] == $itemr['id'] ? 'selected' : '' ?>><?= $itemr['shelf_name']; ?></option>
                                            <?php endforeach; endif; ?>
                                     </select> - <select id="select_shelf_unit" name="data[shelf_unit_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择货位', maxHeight: 400}">
                                        <option value=""></option>
                                        <?php if (isset($shelfitem) && isset($data['shelfunititem']['shelfunit'])): foreach ($shelfitem as $itemrr): ?>
                                                <option value="<?= $itemrr['shelf_unit_id']; ?>"   <?= $data['shelfunititem']['shelfunit']['shelf_unit_id'] == $itemrr['shelf_unit_id'] ? 'selected' : '' ?>><?= $itemrr['shelf_unit_no']; ?>号</option>
                                        <?php endforeach;endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>包裹存放位置：请先选择货架 - 然后在选择货位</small>
                                </div>
                                </div>
                            </div>
                           
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <input type="hidden" name="data[id]" value="<?= $data['id']??'' ?>">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">确认入库
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
<!-- 图片文件列表模板 -->
<script id="tpl-file-item" type="text/template">
    {{ each list }}
    <div class="file-item">
        <a href="{{ $value.file_path }}" title="点击查看大图" target="_blank">
            <img src="{{ $value.file_path }}">
        </a>
        <input type="hidden" name="{{ name }}" value="{{ $value.file_id }}">
        <i class="iconfont icon-shanchu file-item-delete"></i>
    </div>
    {{ /each }}
</script>

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>

	<script language="JavaScript">
	    var shutter = new Audio();
		var data_img = '';
		shutter.autoplay = false;
		shutter.src = navigator.userAgent.match(/Firefox/) ? 'assets/store/img/shutter.ogg' : 'assets/store/img/shutter.mp3';
		
	
    	window.ExecBarcode = function(mailNo,mailWeight,picPackage,picPerson) {
            // var params = "单号：" + mailNo + "，\n重量：" + mailWeight + "Kg，\n底单照片：" + picPackage + "，\n人像照片：" + picPerson;
            // console.log(params);
            document.getElementById("weight").value = mailWeight?mailWeight:0;
            document.getElementById("express_num").value = mailNo?mailNo:'';
            //图片上传
            var blob = dataURLtoBlob(picPackage);
            shutter.src ='assets/store/img/yinxiao6002.mp3';
			    var filedata = new File([blob],'ffff.jpg');
			    var formdata = new FormData();
			    formdata.append('iFile',filedata);
				// display results in page
				 $.ajax({
                    type:"POST",
                    url:'store/upload/image',
                    data: formdata,
                      async: false,//同步上传
                      cache: false,//上传文件无需缓存
                      processData: false, // 不处理数据
                      contentType: false, // 不设置内容类型
                      dataType:'json',
                      success:function(res){
                          console.log(res.data.file_path,8888)
                        document.getElementById('uploadsf').innerHTML += '<div class="file-item"><a href="' + res.data.file_path +'" title="点击查看大图" target="_blank"><img src="'+res.data.file_path+'"></a><input type="hidden" name="data[enter_image_id][]" value = "'+ res.data.file_id+'"><i class="iconfont icon-shanchu file-item-delete"></i></div>';
                        setTimeout(function(){
                            shutter.play();
                            $('#my-form').submit();
                        },1000)
                        
                       }
                })
        }
		// preload shutter audio clip
		
		function take_snapshot() {
			// play sound effect
			shutter.play();
			
			// take snapshot and get image data
			Webcam.snap( function(data_uri) {
                data_img = data_uri;
				document.getElementById('results').innerHTML = '<img class="imggood" src="'+data_uri+'"/>';
			} );
		}
		
        function dataURLtoBlob(dataurl) {
             var arr = dataurl.split(','),
                 mime = arr[0].match(/:(.*?);/)[1],
                 bstr = atob(arr[1]),
                 n = bstr.length,
                 u8arr = new Uint8Array(n);
             while (n--) {
                 u8arr[n] = bstr.charCodeAt(n);
             }
             // return new Blob([u8arr], {
             //     type: mime
             // });
             return u8arr
         }
        // 选择确认上传
        $('.j-uploadimg').click(function () {
            console.log(data_img,7890)
                var blob = dataURLtoBlob(data_img);
			    var filedata = new File([blob],'ffff.jpg');
			    var formdata = new FormData();
			    formdata.append('iFile',filedata);
				// display results in page
				 $.ajax({
                    type:"POST",
                    url:'store/upload/image',
                    data: formdata,
                      async: false,//同步上传
                      cache: false,//上传文件无需缓存
                      processData: false, // 不处理数据
                      contentType: false, // 不设置内容类型
                      dataType:'json',
                      success:function(res){
                          alert(res.data.file_path);
                        document.getElementById('results').innerHTML += '<input hidden name="data[enter_image_id][]" value = '+res.data.file_id+'  />';
                    }
                })
        });
         
         
         
             
	</script>
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
    
    function getSelectData(_this){
        
    }
    

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
            var $selected1 = $('#select-shelf');
            var data = {'shop_id':_this.value}
        }
        if (sType=='shelf_unit'){
            var $selected2 = $('#select_shelf_unit');
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
               //console.log(1111);
               $('#oInp').val(dataId);
               console.log(111,33);
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
            name: 'data[enter_image_id][]' , multiple: true
        });


        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
    });
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