<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
<!-- sidebar: style can be found in sidebar.less -->
<section class="sidebar">
  <!-- Sidebar user panel -->
  <div class="user-panel">
    <div class="pull-left image">
      <img src="3rdparty/dist/img/user.jpg" class="img-circle" alt="User Image">
    </div>
    <div class="pull-left info">
      <p><?php echo ucfirst(escape($user->data()->firstname . ' ' . $user->data()->lastname)); ?></p>
      <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
    </div>
  </div>
  <!-- sidebar menu: : style can be found in sidebar.less -->
  <ul class="sidebar-menu">
    <li>
      <a href="home">
        <i class="fa fa-dashboard"></i> <span>Dashboard</span>
      </a>
    </li>
    <?php if($user->data()->isAdmin == 1){ ?>
    <li class="treeview">
      <a href="#">
        <i class="fa fa-cogs"></i>
        <span>Settings</span>
        <i class="fa fa-angle-left pull-right"></i>
      </a>
      <ul class="treeview-menu">
        <li><a href="general_settings"><i class="fa fa-cog"></i> General setting</a></li>
        <li><a href="mail_settings"><i class="fa fa-cog"></i> Mail setting</a></li>
        <li><a href="email_templates"><i class="fa fa-cog"></i> Email templates</a></li>
      </ul>  
    </li>
    <li>
      <a href="holiday">
        <i class="fa fa-pause"></i>
        <span>Holiday</span> 
      </a>
    </li> 
    <li class="treeview">
      <a href="#">
        <i class="fa fa-group"></i>
        <span>Employee</span>
        <i class="fa fa-angle-left pull-right"></i>
      </a>
      <ul class="treeview-menu">
        <li><a href="add_employee"><i class="fa fa-user-plus"></i> Add Employee</a></li>
        <li><a href="employee_list"><i class="fa fa-eye"></i> View Employee</a></li>
      </ul>
    </li>
    <li class="treeview">
      <a href="designation">
        <i class="fa fa-graduation-cap"></i>
        <span>Designation</span>
      </a>              
    </li>
    <?php } ?>    
    <li><a href="attendance">
      <i class="fa fa-hand-paper-o"></i>
      <span>Attendance</span>
    </a>
    </li>

    <li class="treeview">
      <a href="#">
        <i class="fa fa-plane"></i>
        <span>Leave</span>
        <i class="fa fa-angle-left pull-right"></i>
      </a>
      <ul class="treeview-menu">
    <?php if($user->data()->isAdmin == 1){ ?>        
        <li><a href="leave_type"><i class="fa fa-pencil-square-o"></i> Leave type</a></li>
        <li><a href="leaves"><i class="fa fa-eye"></i> All leave requests</a></li>
    <?php } else { ?>
        <li><a href="apply_leave"><i class="fa fa-pencil-square-o"></i> Apply leave</a></li>
        <li><a href="leaves"><i class="fa fa-circle-o"></i> Leave status</a></li>
    <?php } ?>
      </ul>
    </li>
    <?php if($user->data()->isAdmin == 1){ ?>      
    <li class="treeview">
      <a href="">
        <i class="fa fa-pie-chart"></i>
        <span>Reports</span>
        <i class="fa fa-angle-left pull-right"></i>
      </a>
      <ul class="treeview-menu">
        <li><a href="daily_attendance"><i class="fa fa-bar-chart-o"></i> Daily attendance</a></li>
        <li><a href="monthly_attendance"><i class="fa fa-area-chart"></i> Monthly attendance</a></li>
        <li><a href="emp_monthly_attendance"><i class="fa fa-line-chart"></i> Emp monthly attendance</a></li>
      </ul>
    </li>
    <li class="treeview">
      <a href="">
        <i class="fa fa-tasks"></i>
        <span>Task</span>
        <i class="fa fa-angle-left pull-right"></i>
      </a>
      <ul class="treeview-menu">

        <li><a href="add_task"><i class="fa fa-plus-square"></i> Add task</a></li>
        <li><a href="task_list"><i class="fa fa-list-alt"></i> Task list</a></li>
      </ul>
    </li>    

    <?php }else{ ?> 
    <li>
      <a href="task_list">
        <i class="fa fa-tasks"></i> <span>My task</span>
      </a>
    </li>
    <?php }?>
    <li>
      <a href="messages">
        <i class="fa fa-envelope-o"></i> <span>Message</span>
      </a>
    </li>
  </ul>
</section>
<!-- /.sidebar -->
</aside>
