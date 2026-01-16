<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<script type="text/javascript" src="assets/store/js/webcam.min.js"></script>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">代客预报<small style="padding-left:10px;color:#1686ef">(提示：你可以使用代客预报批量给客户认领预报包裹，提交的包裹单号中，未入库的部分将会做预报处理，已入库的则会自动认领）</small></div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">快递单号[包裹单号] </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <textarea cols="3" rows='4' class="tpl-form-input" id="express_num" name="data[express_num]"
                                           value="<?= $data['express_num']??'' ;?>" placeholder="请输入包裹单号,按回车健换行" required></textarea>
                                    <div class="am-block">
                                         <small>可使用扫码枪进行输入</small>
                                    </div>       
                                </div>
                            </div>
                       
                            <div class="am-form-group" style="<?= $set['usercode_mode']['is_show']!=1?'display：block':'display:none' ;?>">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label"> 输入用户ID </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="widget-become-goods am-form-file am-margin-top-xs">
                                        <input onblur="finduser()" id="member_id" type="text" class="tpl-form-input" name="data[user_id]"
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
                                        <input onblur="findusercode()" id="user_code"  type="text" class="tpl-form-input" name="data[user_code]"
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
                            
                            <?php if (isset($set['is_usermark']) && $set['is_usermark']==1): ?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">选择唛头 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select id="usermark" name="data[mark]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" >
                                    </select>
                                    <div class="help-block">
                                        <small>请选择包裹将要寄往的国家</small>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">运往国家 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="data[country_id]"
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
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 入库仓库 </label>
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
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">备注</label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="data[remark]"
                                           value="<?= $data['remark']??'' ;?>" placeholder="请输入备注" >
                                </div>
                            </div>
               
                       
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">确认预报
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
		
		function findusercode(){
            var usercode = $("#user_code")[0].value;
             $.ajax({
                   type:'post',
                   url:"<?= url('store/user/findusercode') ?>",
                   data:{member_id:usercode},
                   dataType:'json',
                   success:function (res) {
                       if (res.code==1){
                            for(var i=0;i< res.data.list.total;i++){
                               var op = '<option value=' + res.data.list.data[i].mark +'>' + res.data.list.data[i].mark + '-' + res.data.list.data[i].markdes + '</option>';
                               $("#usermark").append(op);
                           }
                       }else{
                           layer.alert(res.msg)
                           $("#user_code").val('')
                       }
                   }
               })
            console.log(member_id)
        }
		
		function finduser(){
            var member_id = $("#member_id")[0].value;
             $.ajax({
                   type:'post',
                   url:"<?= url('store/user/findUserMark') ?>",
                   data:{member_id:member_id},
                   dataType:'json',
                   success:function (res) {
                       if (res.code==1){
                           for(var i=0;i< res.data.list.total;i++){
                               var op = '<option value=' + res.data.list.data[i].mark +'>' + res.data.list.data[i].mark + '-' + res.data.list.data[i].markdes + '</option>';
                               $("#usermark").append(op);
                           }
                           
                       }else{
                           layer.alert(res.msg)
                           $("#member_id").val('')
                       }
                   }
               })
            console.log(member_id)
        }
        
        function finduserWith(e){
            var member_id = e[0].user_id;
            console.log(e,98765);
             $.ajax({
                   type:'post',
                   url:"<?= url('store/user/findUserMark') ?>",
                   data:{member_id:member_id},
                   dataType:'json',
                   success:function (res) {
                       if (res.code==1){
                           for(var i=0;i< res.data.list.total;i++){
                               var op = '<option value=' + res.data.list.data[i].mark +'>' + res.data.list.data[i].mark + '-' + res.data.list.data[i].markdes + '</option>';
                               $("#usermark").append(op);
                           }
                           
                       }else{
                           layer.alert(res.msg)
                           $("#member_id").val('')
                       }
                   }
               })
            console.log(member_id)
        }
        
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
                console.log(user,87654);
                $userList.html(template('tpl-user-item', user));
                finduserWith(user);
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
    max-height: 400px;
    overflow-y: scroll;
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