

    <body class="hold-transition sidebar-mini layout-fixed">
        <div class="wrapper">
            <!-- Navbar -->
            <nav class="main-header navbar navbar-expand navbar-white navbar-light">
                <!-- Left navbar links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="<?php echo base_url('home'); ?>" class="nav-link">Home</a>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="<?php echo base_url('analysis'); ?>" class="nav-link">Analysis</a>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="<?php echo base_url('account/logs'); ?>" class="nav-link">Access Logs</a>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="<?php echo base_url('account/commands'); ?>" class="nav-link">Requests</a>
                    </li>
                </ul>
                <!-- Right navbar links -->
                <ul class="navbar-nav ml-auto">
                    <!-- Notifications Dropdown Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge">15</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <span class="dropdown-item dropdown-header">15 Notifications</span>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                            <i class="fas fa-envelope mr-2"></i> 4 new messages
                            <span class="float-right text-muted text-sm">3 mins</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                            <i class="fas fa-users mr-2"></i> 8 friend requests
                            <span class="float-right text-muted text-sm">12 hours</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                            <i class="fas fa-file mr-2"></i> 3 new reports
                            <span class="float-right text-muted text-sm">2 days</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                        </a>
                    </li>
                    <?php 
					    //$person_info = $this->model_user->get_vars($this->session->userdata('log_id'));
					    //$avatar = (base64_decode($person_info->Avatar));
					    $avatar = null;
					?>
                    <li class="nav-item dropdown user-menu">
		                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
		                    <img src="<?php if ($avatar == NULL){
		                                        echo base_url('assets/img/avatar2.png');}
		                                        else{ echo base_url().'/uploads/profiles/'.$avatar; }
		                               ?>" class="user-image img-circle elevation-2" alt="User Image">
		                    <span class="d-none d-md-inline">
		                    	<?php echo ucwords($user_info['username']);?>
		                    </span>
		                </a>
		                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
		                    <!-- Menu Footer-->
		                    <li class="user-footer">
		                        <a href="<?php echo base_url('accountprofile') ?>" class="btn btn-default btn-flat">Profile</a>
		                        <a href="<?php echo base_url('logout') ?>" class="btn btn-default btn-flat float-right">Sign out</a>
		                    </li>
		                </ul>
		            </li>
                </ul>
            </nav>
            <!-- /.navbar -->
            <!-- Main Sidebar Container -->
            <aside class="main-sidebar sidebar-dark-primary elevation-4">
                <!-- Brand Logo --> 
                <a href="<?php echo base_url('home');?>" class="brand-link">
                <img src="Logo.png" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">Prj Images</span>
                </a>
                <!-- Sidebar -->
                <div class="sidebar">
                    <!-- Sidebar user panel (optional) -->
                    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                        <div class="image">
                            <img src="<?php if ($avatar == NULL){
                                                echo base_url('assets/img/avatar2.png');}
                                                else{ echo base_url().'/uploads/profiles/'.$avatar; } 
                                       ?>" class="img-circle elevation-2" alt="User Image">
                        </div>
                        <div class="info">
                            <a href="#" class="d-block">
                                <?php echo ucwords($user_info['username']);?>
                            </a>
                        </div>
                    </div>
                    <!-- Sidebar Menu -->
                    <nav class="mt-2">
                        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                            <li class="nav-item">
                                <?php
                                    if ($pag == 'home') {
                                        echo '<a href="'.base_url('home').'" class="nav-link active">';
                                    }else{
                                        echo '<a href="'.base_url('home').'" class="nav-link ">';
                                }?>
                                    <i class="nav-icon fas fa-tachometer-alt"></i>
                                    <p>
                                        Dashboard
                                    </p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <?php
                                    if ($pag == 'apps') {
                                        echo '<a href="'.base_url('apps').'" class="nav-link active">';
                                    }else{
                                        echo '<a href="'.base_url('apps').'" class="nav-link ">';
                                }?>
                                    <i class="nav-icon fas fa-mobile-alt"></i>
                                    <p>
                                        Apps
                                    </p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <?php
                                    if ($pag == 'call_logs') {
                                        echo '<a href="'.base_url('call_logs').'" class="nav-link active">';
                                    }else{
                                        echo '<a href="'.base_url('call_logs').'" class="nav-link ">';
                                }?>
                                    <i class="nav-icon fas fa-phone-alt"></i>
                                    <p>
                                        Call Logs
                                    </p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <?php
                                    if ($pag == 'contacts') {
                                        echo '<a href="'.base_url('contacts').'" class="nav-link active">';
                                    }else{
                                        echo '<a href="'.base_url('contacts').'" class="nav-link ">';
                                }?>
                                    <i class="nav-icon fas fa-id-card"></i>
                                    <p>
                                        Contacts
                                    </p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <?php
                                    if ($pag == 'files') {
                                        echo '<a href="'.base_url('files').'" class="nav-link active">';
                                    }else{
                                        echo '<a href="'.base_url('files').'" class="nav-link ">';
                                }?>
                                    <i class="nav-icon fas fa-file"></i>
                                    <p>
                                        Files
                                    </p>
                                </a>
                            </li>
							<li class="nav-item">
                                <?php
                                    if ($pag == 'media') {
                                        echo '<a href="'.base_url('media').'" class="nav-link active">';
                                    }else{
                                        echo '<a href="'.base_url('media').'" class="nav-link ">';
                                }?>
                                    <i class="nav-icon fas fa-photo-video"></i>
                                    <p>
                                        Media
                                    </p>
                                </a>
                            </li>	
                            <li class="nav-item">
                                <?php
                                    if ($pag == 'sms') {
                                        echo '<a href="'.base_url('sms').'" class="nav-link active">';
                                    }else{
                                        echo '<a href="'.base_url('sms').'" class="nav-link ">';
                                }?>
                                    <i class="nav-icon fas fa-sms"></i>
                                    <p>
                                        SMS
                                    </p>
                                </a>
                            </li>


                            <li class="nav-header">Account.</li>
                            
                            <li class="nav-item">
                                <?php
                                    if ($pag == 'account_profile') {
                                        echo '<a href="'.base_url('account/profile').'" class="nav-link active">';
                                    }else{
                                        echo '<a href="'.base_url('account/profile').'" class="nav-link ">';
                                }?>
                                    <i class="nav-icon fas fa-user-alt"></i>
                                    <p>
                                        Profile
                                    </p>
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <?php
                                    if ($pag == 'account_setting') {
                                        echo '<a href="'.base_url('account/setting').'" class="nav-link active">';
                                    }else{
                                        echo '<a href="'.base_url('account/setting').'" class="nav-link ">';
                                }?>
                                    <i class="nav-icon fas fa-cogs nav-icon"></i>
                                    <p>
                                        Setting
                                    </p>
                                </a>
                            </li>
                            
                            
                                <?php
                                    if ($pag == 'account_billing1') {
                                        echo '<a href="'.base_url('account/billing').'" class="nav-link active">';
                                    }
                                ?>
                                    

                            <li class="nav-item">
                                <?php
                                    if ($pag == 'requests') {
                                        echo '<a href="'.base_url('account/requests').'" class="nav-link active">';
                                    }else{
                                        echo '<a href="'.base_url('account/requests').'" class="nav-link ">';
                                }?>
                                    <i class="fas fa-terminal nav-icon"></i>
                                    <p>
                                        Requests
                                    </p>
                                </a>
                            </li>
                            
                                <?php
                                    if ($pag == 'account_logs1') {
                                        echo '<a href="'.base_url('account/logs').'" class="nav-link active">';
                                    }
                                ?>

							<li class="nav-header">Miscellaneous.</li>
                            
                                <?php
                                    if ($pag == 'pricing1') {
                                        echo '<a href="'.base_url('pricing').'" class="nav-link active">';
                                    }
                                ?>
                            
                            <li class="nav-item">
                                <?php
                                    if ($pag == 'faqs') {
                                        echo '<a href="'.base_url('faqs').'" class="nav-link active">';
                                    }else{
                                        echo '<a href="'.base_url('faqs').'" class="nav-link ">';
                                }?>
                                    <i class="nav-icon fas fa-info-circle"></i>
                                    <p>
                                        FAQS
                                    </p>
                                </a>
                            </li>
                            
                        </ul>
                    </nav>
                    <!-- /.sidebar-menu -->
                </div>
                <!-- /.sidebar -->
            </aside>