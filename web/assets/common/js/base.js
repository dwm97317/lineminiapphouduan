// 表单提交组件
var formSubmit = {
    config:{
      'formElm':'',
      'url':'',
      'formBtn':'',
    },
    is_array:function(config){
       if (typeof config != 'object') return;
       return true;
    },
    getJsonLength:function(arr){
      var l = 0; 
      for (var i in arr){
        if (arr[i]){
           l++;
        }
      } 
      return l;
    },
    in_array:function(arr,val){
       for (var i in arr){
           if (i==val){
              return true;
           }
       } 
       return false;
    },
    initConfig:function(config){
        for (var i in config){
             if (this.in_array(this.config,i)){

                 this.config[i] = config[i];
             }
        }
    },
    getFormData:function(){
       var data = $(this.config.formElm).serializeArray();
       //console.log(data);
       var params = {};
       for (var i in data){
          params[data[i]['name']] = data[i]['value'];
       }
       this.params = params;
      // console.log(params);
    },
    bindSubmit:function(){
       $(this.config.formBtn).click(() => {
            this.getFormData();
            this.doSumbit();
       })
    },
    doSumbit:function(){
       dialog.loading('拼命处理中,稍等一下哦');
       $.ajax({
          type:"post",
          url:this.config['url'],
          data: this.params,
          dataType:"JSON",
          timeout: 30000,
          success:function(result){
              setTimeout(()=>{
                  if(result.code == 1){
                      dialog.toast('success',result.msg);
                      if (result.url){
                          setTimeout(()=>{
                              window.location.href = result.url;
                          },1000);
                      }
                  }else{
                      dialog.toast('error',result.msg);
                  }

              },500);
              
          },
          error:function(res){
             dialog.toast('error','服务器错误,请重试!');
          }
       })  
    },
    init:function(config){
        if (!this.is_array(config)){
            return; 
        }
        this.initConfig(config);
        this.bindSubmit();
    }
}


function copyUrl2(_this)
    {
        var Url2= _this.getAttribute('text');
        var oInput = document.createElement('input');
        oInput.value = Url2;
        document.body.appendChild(oInput);
        oInput.select(); // 选择对象
        document.execCommand("Copy"); // 执行浏览器复制命令
        oInput.className = 'oInput';
        oInput.style.display='none';
        layer.msg('复制成功');
    }

// 删除函数
function doAjaxDelete(_this,isRefush){
    var _id = _this.getAttribute('data-id');
    var url = _this.getAttribute('data-url');
    console.log(_id); 
    layer.confirm('是否确认删除',{
            btn:['确定','取消'],
            cancel:function(index, layero){
                console.log('关闭x号');
            }
        },function () {
            $.ajax({
            type:"post",
            url:url,
            data:{id:_id},
            dataType:"JSON",
            timeout: 30000,
            success:function(result){
                setTimeout(()=>{
                    if (isRefush){
                        window.location.refresh();
                    }
                },500);
            },
            error:function(res){
               layer.msg('服务器错误');
            }
         })  
     },function(){
         console.log(22);

     });
}


var tools = {
    config:{},
    params:{},
    supportAttrs:['url','refresh','confirm','confirm_text','switch','id','mode','value','field'],
    init:function(_this){
       this.params = {};
       for (var i=0; i<this.supportAttrs.length;i++){
           this.config[this.supportAttrs[i]] = _this.getAttribute('data-'+this.supportAttrs[i]);
       }

       if (this.config['confirm']){
           var options = {};
           if (tools.config.confirm_text){
               options['content'] = tools.config.confirm_text;
           } 
           dialog.confirm(function(){
               if (tools.config['mode'] == 'redirect'){
                   tools.redirect();
                   return;
                }
              tools.doSubmit();    
           },options);
           return;
       }
       if (this.config['mode'] == 'redirect'){
           this.redirect();
           return;
       }
       this.doSubmit();
    },
    getSwitchParams:function(){
       var switchs = this.config.switch.split('|');
       var value = this.config.value;
       this.params[this.config.field] = value==switchs[0]?switchs[1]:switchs[0];
       console.log(this.params);
    },
    getParams:function(){
       if (this.config.id){
           this.params['id'] = this.config.id;
       }
    },
    redirect:function(){
       var url = this.config['url'];
       if (this.config.id){
           url += '&id='+this.config['id'];
       }
       window.location.href = url;
    },
    doSubmit:function(){
       if (this.config.mode && this.config.switch){
           this.getSwitchParams();
       }else{
           this.getParams();
       }
       dialog.loading('拼命处理中,稍等一下哦');
        $.ajax({
            type:"post",
            url:this.config['url'],
            data: this.params,
            dataType:"JSON",
            timeout: 30000,
            success:function(result){
                setTimeout(()=>{
                    if (result.code==1){
                        dialog.toast('success',result.msg);
                        if (tools.config.refresh==true){
                            setTimeout(()=>{
                                window.location.reload();
                            },1000);
                        }
                    }else{
                        dialog.toast('error',result.msg);
                    }
                },500);
                
            },
            error:function(res){
               dialog.toast('error','服务器错误,请重试!');
            }
         })  
    }
}

// 弹窗组件
var dialog = {
    template:{
       'loading':'<div class="loading"><div class="loader"><div class="loader-inner ball-scale-ripple"><div></div></div></div></div><div class="loading-content"><p>{content}</p></div>',
       'toast':'<div class="type-icon {type}"></div><div class="dialog-content"><p>{content}</p></div>',
       'confirm':'<div class="confirm-title">{title}</div><div class="confirm-content">{content}</div><div class="confirm-btn-content"><span href="javascript:;"class="confirm-btn">{confirm}</span><span href="javascript:;"class="cancle-btn">{cancle}</span></div>',
    },
    config:{
       masker:true,
    },
    init:function(){
       if (this.config.masker){
           this.createMaskerLayer();
       }
       var layerDialog = window.parent.document.getElementsByClassName('dialog-layer')[0];
       if (layerDialog){
           window.parent.document.body.removeChild(layerDialog);
       }
       var doc = document.createElement('div');
       switch(this.type){
          case 'loading':
            var layer = 'loading-layer animated bounceInDown';
            break;
          case 'toast':
            var layer = 'toast-layer animated bounceInDown';
            break;
          case 'confirm':
            var layer = 'confirm-layer';
            break;    
       }
       doc.className = 'dialog-layer '+layer;
       this.doc = doc;
       window.parent.document.body.appendChild(doc);
    },
    createMaskerLayer:function(){
        if (window.parent.document.getElementsByClassName('form-post-layer')[0]){
            return;
        }
        var masker = document.createElement('div');
        masker.className = 'form-post-layer';
        window.parent.document.body.appendChild(masker);
    },
    loading:function(text){
        this.type = 'loading';
        // 加载动画
        this.init();
        var loadingText = text?text:'处理中!请稍后^-^' 
        this.doc.innerHTML = this.template[this.type].replace('{content}',loadingText);
    },
    toast:function(type,html){
        this.type = 'toast';
        // 成功 返回
        // 错误 返回
        this.init();
        switch(type){
           case 'success':
             typeClass = 'success';
             break;
           case 'warn':
             typeClass = 'warn';
             break;
           case 'error':
             typeClass = 'error';
             break;    
        }
        var loadingText = html?html:'操作完成' ;
        this.doc.innerHTML = this.template[this.type].replace('{content}',loadingText).replace('{type}',typeClass);
        setTimeout(()=>{
           window.parent.document.body.removeChild(this.doc);
           if (this.config.masker){
              var master = window.parent.document.getElementsByClassName('form-post-layer')[0];
              if (master){
                  window.parent.document.body.removeChild(master);
              }
           }
        },2000);
    },
    confirm:function(fn,config){
        this.type = 'confirm';
        // 确认框
        this.init();
        var default_config = {
            title:'确认',
            content:'请确认操作？',
            confirm:'确认',
            cancle:'取消',
        }
        if (config){
           for (var i in config){
              default_config[i] = config[i];
           }
        }
        var _temp = this.template[this.type];
        console.log(_temp);
        for (k in default_config){
            _temp = _temp.replace('{'+k+'}',default_config[k]);
        }
        this.doc.innerHTML = _temp;
        setTimeout(()=>{
           this.doc.style.opacity = 1;
           this.doc.style.top = 40+"%";
           $(".confirm-btn").click(()=>{
              window.parent.document.body.removeChild(this.doc);
              if (this.config.masker){
                var master = window.parent.document.getElementsByClassName('form-post-layer')[0];
                if (master){
                    window.parent.document.body.removeChild(master);
                }
              }
              fn(); 
           });
           $(".cancle-btn").click(()=>{
              window.parent.document.body.removeChild(this.doc);
              if (this.config.masker){
                var master = window.parent.document.getElementsByClassName('form-post-layer')[0];
                if (master){
                    window.parent.document.body.removeChild(master);
                }
              }
           })
        },20);
    },   
}