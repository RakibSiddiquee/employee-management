<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $title; ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- FAVICON -->
    <link rel="shortcut icon" href="uploads/<?php echo $settings->companyLogo(); ?>">
    
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="3rdparty/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="3rdparty/datatable/css/dataTables.bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>  
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="3rdparty/dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="3rdparty/dist/css/skins/_all-skins.min.css">
    <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.js"></script> 
    <script src="3rdparty/datatable/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" language="javascript" src="3rdparty/datatable/js/dataTables.bootstrap.min.js"></script>
    <link href='3rdparty/dist/css/fullcalendar.css' rel='stylesheet' />
    <link href='3rdparty/dist/css/fullcalendar.print.css' rel='stylesheet' media='print' />
    <script src='3rdparty/dist/js/moment.min.js'></script>
    <!--<script src='x3rdparty/dist/js/jquery.min.js'></script>-->
    <script src='3rdparty/dist/js/fullcalendar.min.js'></script>
    <!--following link is for datepicker-->
    <script src="http://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

    <!-- Morris chart -->
    <link rel="stylesheet" href="3rdparty/plugins/morris/morris.css">
    <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/themes/smoothness/jquery-ui.css">

    <link href="css/summernote.css" rel="stylesheet">

    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script>
      jQuery(document).ready(function() {
          jQuery('#table').DataTable();
      } );

  // for datepicker
    jQuery(function() {
      jQuery( "#datepicker" ).datepicker({dateFormat: "yy-mm-dd"});

    });

    // Auto remove alert box
    window.setTimeout(function() {
        jQuery(".alert").fadeTo(2000, 0).slideUp(2000, function(){
            jQuery(this).remove(); 
        });
    }, 2000);    
    </script>  

  </head>
  <body class="hold-transition skin-blue sidebar-mini">
    <?php if($user->isLoggedIn()){ ?><div class="wrapper"> <?php } ?>

      <header class="main-header">
        <!-- Logo -->
        <a href="home" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="logo-mini"><img class="logoImg" src="uploads/<?php echo $settings->companyLogo(); ?>" alt="Logo"></span>
          <!-- logo for regular state and mobile devices -->          
          <span class="logo-lg"><b><?php echo $settings->companyName(); ?></b></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <?php
            if($user->isLoggedIn()){
            $db = DB::getInstance();
            $inboxData = $db->query("SELECT m.*, DATE_FORMAT(m.created_at, '%h:%i %p, %D-%b-%y') AS time, CONCAT(u.firstname, ' ', u.lastname) AS fullname, u.designation FROM messages m LEFT JOIN users u ON m.from_user_id = u.id WHERE m.to_user_id = {$user->data()->id} AND isRead = 0 ORDER BY m.created_at DESC")->results();
            //var_dump($inboxData);
            $totalMessage = count($inboxData);
            //var_dump($messages);

            $leaveData = $db->query("SELECT l.*, CONCAT(u.firstname, ' ', u.lastname) AS fullname FROM emp_leave l LEFT JOIN users u ON l.user_id = u.id WHERE l.status = 'Pending'")->results();
            //var_dump($leaveData);
            $totalEmpLeave = count($leaveData);

            $taskData = $db->query("SELECT t.*, CONCAT(u.firstname, ' ', u.lastname) AS fullname FROM tasks t LEFT JOIN users u ON t.user_id = u.id WHERE t.user_id = {$user->data()->id} AND t.status != 'Completed'")->results();
            //var_dump($taskData);
            $totalTask = count($taskData);
            //var_dump($totalTask);

            $adminTaskData = $db->query("SELECT t.*, CONCAT(u.firstname, ' ', u.lastname) AS fullname FROM tasks t LEFT JOIN users u ON t.user_id = u.id WHERE t.status != 'Completed'")->results();
            //var_dump($adminTaskData);
            $totalAdminTask = count($adminTaskData);
            //var_dump($totalTask);
	    
	    $clockedIn = $db->query("SELECT * FROM attendance WHERE (`date` = '" . date('Y-m-d') . "' AND `user_id` = '" .$user->data()->id. "')")->first();
	    //var_dump($clockedIn);
	    if(isset($_POST['btnClock'])){
		// For attendance 
		$getData = $db->query("SELECT * FROM attendance WHERE (`date` = '" . date('Y-m-d') . "' AND `user_id` = '" .$user->data()->id. "')")->first();
		//var_dump($getData);
	
		if(!empty($getData->in_time)){
			$db->update('attendance', $getData->id, array(
				'out_time' => date('H:i:s')
			));
			Redirect::to('attendance');
		}
	    }
	    
          ?>

          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <!--<span class="sr-only">Toggle navigation</span>-->
          </a>
          <div class="col-sm-4 col-sm-offset-3" style="bottom: -7px;">
              <?php if($clockedIn->out_time != '00:00:00'){ ?>
                <span class="btn btn-primary">You are clocked out</span>
              <?php  }else{?>
              	<span class="btn btn-primary">You are clocked in</span>                
               <?php } ?>
              <form action="" method="post" style="display: inline">
                <input type="submit" name="btnClock" value="Clocked out" class="btn btn-info">
              </form>
          </div>  
                     
          <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">

              <!-- Messages: style can be found in dropdown.less-->
              <li class="dropdown messages-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <i class="fa fa-envelope-o"></i>
                  <span class="label label-warning"><?php if(!empty($totalMessage)) echo $totalMessage; ?></span>
                </a>
                <ul class="dropdown-menu">
                  <li class="header">You have <?php echo $totalMessage; ?> messages</li>
                  <li>
                    <!-- inner menu: contains the actual data -->
                    <ul class="menu">
                      <?php foreach ($inboxData as $data) { ?>
                      <li><!-- start message -->
                        <a href="view_inbox_message?<?php echo $data->id; ?>">
                          <div class="pull-left">
                            <img src="3rdparty/dist/img/user.jpg" class="img-circle" alt="User Image">
                          </div>
                          <h4>
                            <?php echo $data->fullname; ?>
                            <small><i class="fa fa-clock-o"></i> <?php echo str_replace(', ', '<br/>', $data->time); ?></small>
                          </h4>
                          <p><?php echo $data->subject; ?></p>                          
                        </a>
                      </li><!-- end message -->
                      <?php } ?>
                    </ul>
                  </li>
                  <li class="footer"><a href="messages">See All Messages</a></li>
                </ul>
              </li>
              <?php if($user->data()->isAdmin == 1){ ?>
              <!-- Notifications: style can be found in dropdown.less -->
              <li class="dropdown notifications-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <i class="fa fa-bell-o"></i>
                  <span class="label label-warning"><?php if(!empty($totalEmpLeave)) echo $totalEmpLeave; ?></span>
                </a>
                <ul class="dropdown-menu">
                  <li class="header">You have <?php echo $totalEmpLeave; ?> notifications</li>
                  <li>
                    <!-- inner menu: contains the actual data -->
                    <ul class="menu">
                      <?php foreach ($leaveData as $leave) {?>                        
                      <li>
                        <a href="leaves">
                          <i class="fa fa-user text-aqua"></i> <?php echo $leave->fullname; ?> applied for leave.
                        </a>
                      </li>
                      <?php } ?>
                    </ul>
                  </li>
                  <li class="footer"><a href="leaves">View all notifications</a></li>
                </ul>
              </li>
              <?php } ?>
              <!-- Tasks: style can be found in dropdown.less -->
              <li class="dropdown tasks-menu">

 <!--User area-->                             
                <?php if($user->data()->isAdmin != 1){ ?>    

                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <i class="fa fa-flag-o"></i>
                  <span class="label label-warning"><?php if(!empty($totalTask)) echo $totalTask; ?></span>
                </a>
                <ul class="dropdown-menu">

                  <li class="header">You have <?php echo $totalTask; ?> tasks</li>
                  <li>
                    <!-- inner menu: contains the actual data -->
                    <ul class="menu">
                      <?php foreach ($taskData as $userTask) { ?>
                      <li><!-- Task item -->
                        <a href="view_task?<?php echo $userTask->id; ?>">
                          <h3>
                            <?php if(!empty($userTask->title)) echo $userTask->title; ?>
                            <small class="pull-right label 
                              <?php
                                if($userTask->status == 'Not started'){
                                  echo 'label-danger';
                                } elseif ($userTask->status == 'In progress') {
                                  echo 'label-info';
                                } elseif ($userTask->status == 'Pending') {
                                  echo 'label-warning';
                                }
                              ?>
                            " style="margin-top: -12px;">
                            <?php if(!empty($userTask->status)) echo $userTask->status; ?>
                          </small>
                          </h3>
                          <div class="progress xs">
                            <div class="progress-bar <?php if($userTask->status == 'Not started'){
                                    echo 'progress-bar-danger" style="width: 10%';
                                  } elseif ($userTask->status == 'In progress') {
                                    echo 'progress-bar-info" style="width: 70%';
                                  } elseif ($userTask->status == 'Pending') {
                                    echo 'progress-bar-warning" style="width: 50%';
                                  }
                            ?>" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                              <span class="sr-only">20% Complete</span>
                            </div>
                          </div>
                        </a>
                      </li><!-- end task item -->
                      <?php } ?>
                    </ul>
                  </li>
<!--End User area-->
                  <?php }else{ ?>
<!--Admin area-->
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <i class="fa fa-flag-o"></i>
                  <span class="label label-danger"><?php if(!empty($totalAdminTask)) echo $totalAdminTask; ?></span>
                </a>
                <ul class="dropdown-menu">
                  <li class="header">There are <?php echo $totalAdminTask; ?> tasks</li>
                  <li>
                    <!-- inner menu: contains the actual data -->
                    <ul class="menu">
                      <?php foreach ($adminTaskData as $AdminTask) { ?>
                      <li><!-- Task item -->
                        <a href="view_task?<?php echo $AdminTask->id; ?>">
                          <h3>
                            <?php if(!empty($AdminTask->title)) echo $AdminTask->title; ?>
                            <small class="pull-right label 
                              <?php
                                if($AdminTask->status == 'Not started'){
                                  echo 'label-danger';
                                } elseif ($AdminTask->status == 'In progress') {
                                  echo 'label-info';
                                } elseif ($AdminTask->status == 'Pending') {
                                  echo 'label-warning';
                                }
                              ?>
                            " style="margin-top: -5px;">
                            <?php if(!empty($AdminTask->status)) echo $AdminTask->status; ?>
                          </small>
                          </h3>
                          <div class="progress xs">
                            <div class="progress-bar <?php if($AdminTask->status == 'Not started'){
                                    echo 'progress-bar-danger" style="width: 10%';
                                  } elseif ($AdminTask->status == 'In progress') {
                                    echo 'progress-bar-info" style="width: 70%';
                                  } elseif ($AdminTask->status == 'Pending') {
                                    echo 'progress-bar-warning" style="width: 50%';
                                  }
                            ?>" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                              <span class="sr-only">20% Complete</span>
                            </div>
                          </div>
                        </a>
                      </li><!-- end task item -->
                      <?php } ?>
                    </ul>
                  </li>

                  <?php } ?>
                  <li class="footer">
                    <a href="task_list">View all tasks</a>
                  </li>
                </ul>
              </li>
              <!-- User Account: style can be found in dropdown.less -->
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <img src="3rdparty/dist/img/user.jpg" class="user-image" alt="User Image">
                  <span class="hidden-xs"><?php echo ucfirst(escape($user->data()->firstname . ' ' . $user->data()->lastname)); ?></span>
                </a>
                <ul class="dropdown-menu">
                  <!-- User image -->
                  <li class="user-header">
                    <img src="3rdparty/dist/img/user.jpg" class="img-circle" alt="User Image">
                    <p>
                      <?php echo $user->data()->firstname . ' ' . $user->data()->lastname . ' - ' . $user->data()->designation; ?>
                      <small>Employee since <?php echo date('jS M, Y', strtotime($user->data()->joined)); ?></small>
                    </p>
                  </li>
 
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    <div class="pull-left">
                      <a href="password_change" class="btn btn-default btn-flat">Change password</a>
                    </div>
                    <div class="pull-right">
                      <a href="logout" class="btn btn-default btn-flat">Logout</a>
                    </div>
                  </li>
                </ul>
              </li>

            </ul>
          </div>
          <?php } ?>
        </nav>
      </header>