
<?php include($__TEMPLATE__.'common/__header__.php'); ?>
<body>
    <div class="app  side-nav-dark header-dark">
        <div class="layout">
            <!-- Header START app  header-info-gradient side-nav-default --> 
            <div class="header navbar">
                <div class="header-container">
                    <div class="nav-logo">
                        <a href="<?php echo(urlCreate('/web/page/data')) ?>">
                            <div class="logo logo-white sichuangshe" style="display: inline-block;font-size: 1.5rem;line-height: 4rem;color:#Fff;"><?= $storetitle ?></div>
                        </a>
                    </div>
                    <ul class="nav-left">
                        <li>
                            <a class="sidenav-fold-toggler" href="javascript:void(0);">
                                <i class="mdi mdi-menu"></i>
                            </a>
                            <a class="sidenav-expand-toggler" href="javascript:void(0);">
                                <i class="mdi mdi-menu"></i>
                            </a>
                        </li>
                    </ul>
                    <ul class="nav-right">

                        <li class="notifications dropdown dropdown-animated scale-left">
                            <span class="counter">2</span>
                            <a href="" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="mdi mdi-bell-ring-outline"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-lg p-v-0">
                                
                                 <?php if (!$message->isEmpty()): foreach ($message as $item): ?>
                                <li class="list-item border bottom">
                                    <a href="javascript:void(0);" class="media-hover p-15">
                                        <div class="info">
                                            <span class="title"><?= $item['content'] ?></span>
                                            <span class="sub-title"><?= $item['created_time'] ?></span>
                                        </div>
                                    </a>
                                </li>

                                <?php endforeach; else: ?>
                                <li class="p-v-15 p-h-20 border bottom text-dark">
                                    <h5 class="m-b-0">
                                        <i class="mdi mdi-bell-ring-outline p-r-10"></i>
                                        <span>暂无通知</span>
                                    </h5>
                                </li>
                                <li class="p-v-15 p-h-20 text-center">
                                    <span>
                                        <a href="" class="text-gray">查看所有通知 <i class="ei-right-chevron p-l-5 font-size-10"></i></a>
                                    </span>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <li class="user-profile dropdown dropdown-animated scale-left">
                            <a href="" class="dropdown-toggle" data-toggle="dropdown">
                                <img class="profile-img img-fluid" src="<?= $user['user']['avatarUrl']?$user['user']['avatarUrl']:'/web/static/picture/thumb-16.jpg' ?>" alt="">
                            </a>
                            <ul class="dropdown-menu dropdown-md p-v-0">
                                <li>
                                    <ul class="list-media">
                                        <li class="list-item p-15">
                                            <div class="media-img">
                                                <img src="<?= $user['user']['avatarUrl']?$user['user']['avatarUrl']:'/web/static/picture/thumb-16.jpg' ?>" alt="">
                                            </div>
                                            <div class="info">
                                                <span class="title text-semibold"><?= $user['user']['user_name'] ?></span>
                                                <span class="sub-title"><?= $user['user']['mobile'] ?></span>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                <li role="separator" class="divider"></li>
                                <!--<li>-->
                                <!--    <a href="##">-->
                                <!--        <i class="ti-settings p-r-10"></i>-->
                                <!--        <span>设置</span>-->
                                <!--    </a>-->
                                <!--</li>-->
                                <li>
                                    <a href="<?php echo(urlCreate('/web/user/person')) ?>">
                                        <i class="ti-user p-r-10"></i>
                                        <span>我的资料</span>
                                    </a>
                                </li>
                                <!--<li>-->
                                <!--    <a href="##">-->
                                <!--        <i class="ti-email p-r-10"></i>-->
                                <!--        <span>通知</span>-->
                                <!--        <span class="badge badge-pill badge-success pull-right">2</span>-->
                                <!--    </a>-->
                                <!--</li>-->
                                <li>
                                    <a href="<?php echo(urlCreate('/web/passport/logout')) ?>">
                                        <i class="ti-power-off p-r-10"></i>
                                        <span>退出</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!--<li class="m-r-10">-->
                        <!--    <a class="quick-view-toggler" href="javascript:void(0);">-->
                        <!--        <i class="mdi mdi-format-indent-decrease"></i>-->
                        <!--    </a>-->
                        <!--</li>-->
                    </ul>
                </div>
            </div>
            <!-- Header END -->

            <!-- Side Nav START -->
            <div class="side-nav expand-lg">
                <div class="side-nav-inner">
                    <ul class="side-nav-menu scrollable">
                        <?php include($__TEMPLATE__.'common/__menu__.php'); ?>
                    </ul>
                </div>
            </div>
            <!-- Side Nav END -->

            <!-- Page Container START -->
            <div class="page-container">
                <!-- Quick View START -->
                <div class="quick-view">
                    <ul class="quick-view-tabs nav nav-tabs nav-justified" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" href="#config" role="tab" data-toggle="tab">
                                <span>Config</span>
                            </a>
                        </li>

                    </ul>
                    <div class="tab-content scrollable">
                        <!-- config START -->
                        <div id="config" role="tabpanel" class="tab-pane fade in active">
                            <div class="theme-configurator p-20">
                                <div class="m-v-20 border bottom">
                                    <p class="text-dark text-semibold m-b-0">Solid Header</p>
                                    <p class="m-b-15">Config header background (solid)</p>
                                    <div class="theme-selector p-b-20">
                                        <label>
                                            <input type="radio" value="default" name="header-theme">
                                            <span class="theme-color bg-white border"></span>
                                        </label>
                                        <label>
                                            <input type="radio" value="primary" name="header-theme">
                                            <span class="theme-color bg-primary"></span>
                                        </label>
                                        <label>
                                            <input type="radio" value="info" name="header-theme">
                                            <span class="theme-color bg-info"></span>
                                        </label>
                                        <label>
                                            <input type="radio" value="success" name="header-theme">
                                            <span class="theme-color bg-success"></span>
                                        </label>
                                        <label>
                                            <input type="radio" value="warning" name="header-theme">
                                            <span class="theme-color bg-warning"></span>
                                        </label>
                                        <label>
                                            <input type="radio" value="danger" name="header-theme">
                                            <span class="theme-color bg-danger"></span>
                                        </label>
                                        <label>
                                            <input type="radio" value="dark" name="header-theme">
                                            <span class="theme-color bg-dark"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="m-v-15 border bottom">
                                    <p class="text-dark text-semibold m-b-0">Gradient Header</p>
                                    <p class="m-b-15">Config header background (gradient)</p>
                                    <div class="theme-selector p-b-15">
                                        <label>
                                            <input type="radio" value="primary-gradient" name="header-theme">
                                            <span class="theme-color bg-gradient-primary"></span>
                                        </label>
                                        <label>
                                            <input type="radio" value="info-gradient" name="header-theme">
                                            <span class="theme-color bg-gradient-info"></span>
                                        </label>
                                        <label>
                                            <input type="radio" value="success-gradient" name="header-theme">
                                            <span class="theme-color bg-gradient-success"></span>
                                        </label>
                                        <label>
                                            <input type="radio" value="warning-gradient" name="header-theme">
                                            <span class="theme-color bg-gradient-warning"></span>
                                        </label>
                                        <label>
                                            <input type="radio" value="danger-gradient" name="header-theme">
                                            <span class="theme-color bg-gradient-danger"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="m-v-15 border bottom">
                                    <p class="text-dark text-semibold m-b-0">Side Nav Color</p>
                                    <p class="m-b-15">Config side nav background</p>
                                    <div class="theme-selector p-b-15">
                                        <label>
                                            <input type="radio" value="default" name="side-nav-color">
                                            <span class="theme-color bg-white border"></span>
                                        </label>
                                        <label>
                                            <input type="radio" value="dark" name="side-nav-color">
                                            <span class="theme-color bg-dark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- config END -->

                    </div>
                </div>
                <!-- Side Panel END -->

                <!-- Content Wrapper START -->
                <div class="main-content">
                    <div class="container-fluid">
                         {__CONTENT__}     
                    </div>
                </div>
                <!-- Content Wrapper END -->

                <!-- Footer START -->
                <footer class="content-footer">
                    <div class="footer">
                        <div class="copyright">
                            <span>思创社出品</span>
                        </div>
                    </div>
                </footer>
                <!-- Footer END -->

            </div>
            <!-- Page Container END -->

        </div>
    </div>
    
    <?php include($__TEMPLATE__.'common/__footer__.php'); ?>
   
</body>

</html>