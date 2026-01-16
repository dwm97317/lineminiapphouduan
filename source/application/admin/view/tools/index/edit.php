<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加更新日志</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">日志标题 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="log[log_title]"
                                           value="<?= $model['log_title'] ?>" placeholder="请输入日志标题" required>
                                </div>
                            </div>
                
                  
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">日志内容 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <!-- 加载编辑器的容器 -->
                                    <textarea id="container" name="log[log_content]"
                                              type="text/plain"><?= $model['log_content'] ?></textarea>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">日志排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="log[sort]"
                                            value="<?= $model['sort'] ?>"  required>
                                    <small>数字越小越靠前</small>
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

<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>
<script>
    $(function () {
         // 富文本编辑器
        UM.getEditor('container', {
            initialFrameWidth: 375 + 15,
            initialFrameHeight: 400
        });
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
<style>
/* 文件库 */
.file-library .layui-layer-title {
  background-color: #fff;
  border-bottom: none;
  font-size: 1.6rem;
  font-weight: 500; }
.file-library .layui-layer-content {
  padding: 0 1rem;
  user-select: none; }
  .file-library .layui-layer-content .file-group {
    float: left;
    width: 150px;
    padding-top: 20px; }
    .file-library .layui-layer-content .file-group .nav-new {
      overflow-y: auto;
      max-height: 340px; }
      .file-library .layui-layer-content .file-group .nav-new li {
        position: relative;
        margin: .3rem 0;
        padding: .8rem 2.3rem; }
        .file-library .layui-layer-content .file-group .nav-new li a i.iconfont {
          font-size: 1.4rem; }
        .file-library .layui-layer-content .file-group .nav-new li a.group-name {
          color: #595961;
          font-size: 1.3rem; }
        .file-library .layui-layer-content .file-group .nav-new li a.group-edit {
          display: none;
          position: absolute;
          left: .6rem; }
        .file-library .layui-layer-content .file-group .nav-new li a.group-delete {
          display: none;
          position: absolute;
          right: .6rem; }
        .file-library .layui-layer-content .file-group .nav-new li:hover, .file-library .layui-layer-content .file-group .nav-new li.active {
          background: rgba(48, 145, 242, 0.1);
          border-radius: 6px; }
          .file-library .layui-layer-content .file-group .nav-new li:hover .group-name, .file-library .layui-layer-content .file-group .nav-new li.active .group-name {
            color: #0e90d2; }
        .file-library .layui-layer-content .file-group .nav-new li:hover .group-edit, .file-library .layui-layer-content .file-group .nav-new li:hover .group-delete {
          display: inline; }
    .file-library .layui-layer-content .file-group a.group-add {
      display: block;
      margin-top: 1.8rem;
      font-size: 1.2rem;
      padding: 0 2.3rem; }
  .file-library .layui-layer-content .file-list {
    float: left; }
    .file-library .layui-layer-content .file-list .v-box-header {
      padding: 0 2rem 0 1rem;
      margin-bottom: 10px; }
      .file-library .layui-layer-content .file-list .v-box-header .h-left .tpl-table-black-operation {
        margin: 0 1rem; }
        .file-library .layui-layer-content .file-list .v-box-header .h-left .tpl-table-black-operation a {
          padding: 6px 10px; }
      .file-library .layui-layer-content .file-list .v-box-header .h-left .am-dropdown-toggle {
        font-size: 1.2rem; }
      .file-library .layui-layer-content .file-list .v-box-header .h-left .am-dropdown-content a {
        font-size: 1.3rem; }
      .file-library .layui-layer-content .file-list .v-box-header .h-rigth .upload-image .iconfont {
        font-size: 1.2rem; }
  .file-library .layui-layer-content .v-box-body {
    width: 660px; }
    .file-library .layui-layer-content .v-box-body ul.file-list-item {
      overflow-y: auto;
      height: 380px; }
      .file-library .layui-layer-content .v-box-body ul.file-list-item li {
        position: relative;
        cursor: pointer;
        border-radius: 6px;
        padding: 10px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        float: left;
        margin: 10px;
        -webkit-transition: All 0.2s ease-in-out;
        -moz-transition: All 0.2s ease-in-out;
        -o-transition: All 0.2s ease-in-out;
        transition: All 0.2s ease-in-out;
        -ms-transition: All 0.2s ease-in-out;
        /*-webkit-border-radius:;
        -moz-border-radius:;
        -ms-border-radius:;
        -o-border-radius:;*/
}
        .file-library .layui-layer-content .v-box-body ul.file-list-item li:hover {
          border: 1px solid #16bce2; }
        .file-library .layui-layer-content .v-box-body ul.file-list-item li .img-cover {
          width: 120px;
          height: 120px;
          background: no-repeat center center / 100%; }
        .file-library .layui-layer-content .v-box-body ul.file-list-item li p.file-name {
          margin: 5px 0 0 0;
          width: 120px;
          font-size: 1.3rem; }
        .file-library .layui-layer-content .v-box-body ul.file-list-item li.active .select-mask {
          display: block; }
        .file-library .layui-layer-content .v-box-body ul.file-list-item li .select-mask {
          display: none;
          position: absolute;
          top: 0;
          bottom: 0;
          left: 0;
          right: 0;
          background: rgba(0, 0, 0, 0.5);
          text-align: center;
          border-radius: 6px; }
          .file-library .layui-layer-content .v-box-body ul.file-list-item li .select-mask img {
            position: absolute;
            top: 50px;
            left: 45px; }
    .file-library .layui-layer-content .v-box-body ul.pagination {
      margin: 0; }
      .file-library .layui-layer-content .v-box-body ul.pagination > li > a, .file-library .layui-layer-content .v-box-body ul.pagination > li > span {
        padding: .3rem .9rem;
        font-size: 1.3rem; }    
    
</style>
