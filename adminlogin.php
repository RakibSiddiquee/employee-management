<?php
if(!$user->isLoggedIn()){
  Redirect::to('login');
}

$db = DB::getInstance();
$totalPresent = 0;
$totalAbsent = 0;
$totalLate = 0;

$attData = $db->query("SELECT COUNT(in_time) AS totalPresent, SUM(in_time > '" . $settings->startTime() . "') AS totalLate FROM attendance WHERE `date`= '". date('Y-m-d') ."'")->first();

$totalUsers = $db->query("SELECT COUNT(id) AS totalUsers FROM users")->first()->totalUsers;

$totalPresent = $attData->totalPresent;
$totalLate = $attData->totalLate;
$totalAbsent = $totalUsers - $totalPresent;

$holidays = $db->query("SELECT title, start FROM holiday")->results();
foreach($holidays as $key => $holiday){
 /* if(in_array(date('l', strtotime($holiday->start)), $settings->weekend())){
    $holiday->title = 'Weekend';
  }*/

  $holiday->start = str_replace(' ', 'T', $holiday->start);

}

$jsonholidays = json_encode($holidays);
$jsonholidays = preg_replace('/"([^"]+)"\s*:\s*/', '$1:', $jsonholidays);
//var_dump($jsonholidays);
echo '<script> var data = '. $jsonholidays.'</script>';

if(Session::exists('success')){
  $success = Session::get('success');
  Session::delete('success');
}
foreach ($settings->weekend() as $weekend) {
  $day = substr(strtolower($weekend), 0, 3);
  $class[] = '.fc th.fc-' . $day .', .fc td.fc-' . $day;
  //var_dump($class);
}
$cssclass = implode(', ', $class);
//var_dump($cssclass);

// Get data of tasks

$adminTasks = $db->query("SELECT t.*, CONCAT(u.firstname, ' ', u.lastname) AS fullname FROM tasks t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC")->results();
//var_dump($adminTasks);
$userTasks = $db->query("SELECT t.*, CONCAT(u.firstname, ' ', u.lastname) AS fullname FROM tasks t LEFT JOIN users u ON t.user_id = u.id WHERE t.user_id = {$user->data()->id} ORDER BY t.created_at DESC")->results();
//var_dump($userTasks);

?>

<script>
  //document.write(holidays);

  jQuery(document).ready(function() {
    
    jQuery('#calendar').fullCalendar({
      header: {
        left: 'prev,next today',
        center: 'title',
        right: 'month,agendaWeek,agendaDay'
      },
      editable: false,
      eventLimit: true, // allow "more" link when too many events
      events: data,
      eventColor: '#378006'
    });
    
  });

</script>
<style type="text/css">
  <?php echo $cssclass; ?>{
    background-color: #CC6600;
    border-top: 1px solid lightgray;
    border-right: 1px solid lightgray;
  }
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1 class="sectionTitle">
    Dashboard
    <small>Control panel</small>
  </h1>
    <?php if(!empty($success)) echo '<span class="success col-sm-offset-3">' . $success . '</span>'; ?>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Dashboard</li>
  </ol>
</section>

<!-- Main content -->
<section class="content">
  <!-- Small boxes (Stat box) -->
  <?php if($user->data()->isAdmin == 1){?>
  <div class="row">
    <div class="col-lg-3 col-xs-6">
      <!-- small box -->
      <div class="small-box bg-aqua">
        <div class="inner">
          <h3><?php if(!empty($totalUsers)) echo $totalUsers; ?></h3>
          <p>Total Employee</p>
        </div>
        <div class="icon">
          <i class="ion ion-person-stalker"></i>
        </div>
        <a href="employee_list" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
      </div>
    </div><!-- ./col -->
    <div class="col-lg-3 col-xs-6">
      <!-- small box -->
      <div class="small-box bg-green">
        <div class="inner">
          <h3><?php if(!empty($totalPresent)) echo $totalPresent; ?></h3>
          <p>Total Present</p>
        </div>
        <div class="icon">
          <i class="ion ion-person-stalker"></i>
        </div>
        <a href="daily_attendance" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
      </div>
    </div><!-- ./col -->
    <div class="col-lg-3 col-xs-6">
      <!-- small box -->
      <div class="small-box bg-yellow">
        <div class="inner">
          <h3><?php if(!empty($totalLate)) echo $totalLate; ?></h3>
          <p>Total Late</p>
        </div>
        <div class="icon">
          <i class="ion ion-person-stalker"></i>
        </div>
        <a href="daily_attendance" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
      </div>
    </div><!-- ./col -->
    <div class="col-lg-3 col-xs-6">
      <!-- small box -->
      <div class="small-box bg-red">
        <div class="inner">
          <h3><?php if(!empty($totalAbsent)) echo $totalAbsent; ?></h3>
          <p>Total Absent</p>
        </div>
        <div class="icon">
          <i class="ion ion-person-stalker"></i>
        </div>
        <a href="daily_attendance" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
      </div>
    </div><!-- ./col -->
  </div><!-- /.row -->
  <?php } ?>
  <!-- Main row -->
  <div class="row">
    <!-- Left col -->
    <section class="col-sm-8">
      <!-- Custom tabs (Charts with tabs)-->
      <div class="box box-info">        
        <div id='calendar'></div>
      </div><!-- /.nav-tabs-custom -->

    </section><!-- /.Left col -->
    <!-- right col (We are only adding the ID to make the widgets sortable)-->
    <section class="col-sm-4 connectedSortable">

      <!-- Tasks -->
      <div class="box box-solid bg-green-gradient">
        <div class="box-header">
          <i class="fa fa-tasks"></i>
          <h3 class="box-title">Tasks</h3>
          <!-- tools box -->
          <div class="pull-right box-tools">
            <button class="btn btn-success btn-sm" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <!--<button class="btn btn-success btn-sm" data-widget="remove"><i class="fa fa-times"></i></button>-->
          </div><!-- /. tools -->
        </div><!-- /.box-header -->

        <div class="box-footer text-black">
          <div class="row">
            <?php 
              if($user->data()->isAdmin == 1){
                foreach ($adminTasks as $adminTask) { ?>
                  <a href="view_task?<?php echo $adminTask->id; ?>">
                    <div class="col-sm-12">
                      <!-- Progress bars -->
                      <div class="clearfix">
                        <span class="pull-left"><?php if(!empty($adminTask->title)) echo $adminTask->title; ?></span>
                        <small class="pull-right label 
                          <?php
                            if($adminTask->status == 'Not started'){
                              echo 'label-danger';
                            } elseif ($adminTask->status == 'In progress') {
                              echo 'label-info';
                            } elseif ($adminTask->status == 'Pending') {
                              echo 'label-warning';
                            } elseif ($adminTask->status == 'Completed') {
                              echo 'label-success';
                            }
                          ?>
                        ">
                        <?php if(!empty($adminTask->status)) echo $adminTask->status; ?>
                        </small>
                      </div>
                      <div class="progress xs">
                        <div class="progress-bar <?php if($adminTask->status == 'Not started'){
                                echo 'progress-bar-danger" style="width: 10%';
                              } elseif ($adminTask->status == 'In progress') {
                                echo 'progress-bar-info" style="width: 70%';
                              } elseif ($adminTask->status == 'Pending') {
                                echo 'progress-bar-warning" style="width: 50%';
                              } elseif ($adminTask->status == 'Completed') {
                                echo 'progress-bar-success" style="width: 100%';
                              }
                        ?>" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                          <span class="sr-only">20% Complete</span>
                        </div>
                      </div>
                    </div><!-- /.col -->
                  </a>  <!--End Admin area-->  

            <?php } }else{ 

                foreach ($userTasks as $userTask) { ?>

                  <a href="view_task?<?php echo $userTask->id; ?>">
                    <div class="col-sm-12">
                      <!-- Progress bars -->
                      <div class="clearfix">
                        <span class="pull-left"><?php if(!empty($userTask->title)) echo $userTask->title; ?></span>
                          <small class="pull-right label 
                            <?php
                              if($userTask->status == 'Not started'){
                                echo 'label-danger';
                              } elseif ($userTask->status == 'In progress') {
                                echo 'label-info';
                              } elseif ($userTask->status == 'Pending') {
                                echo 'label-warning';
                              } elseif ($userTask->status == 'Completed') {
                                echo 'label-success';
                              }
                            ?>
                          ">
                          <?php if(!empty($userTask->status)) echo $userTask->status; ?>
                        </small>
                      </div>
                      <div class="progress xs">
                        <div class="progress-bar <?php if($userTask->status == 'Not started'){
                                echo 'progress-bar-danger" style="width: 10%';
                              } elseif ($userTask->status == 'In progress') {
                                echo 'progress-bar-info" style="width: 70%';
                              } elseif ($userTask->status == 'Pending') {
                                echo 'progress-bar-warning" style="width: 50%';
                              } elseif ($userTask->status == 'Completed') {
                                echo 'progress-bar-success" style="width: 100%';
                              }
                        ?>" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                          <span class="sr-only">20% Complete</span>
                        </div>
                      </div>
                    </div><!-- /.col -->
                  </a>

          <?php } }?>

          </div><!-- /.row -->
            <ul class="pager" style="margin: 0">
              <li class="previous"><a href="#">Previous</a></li>
              <li class="next"><a href="#">Next</a></li>
            </ul>
        </div>
      </div><!-- /.box -->

    </section><!-- right col -->
  </div><!-- /.row (main row) -->

</section><!-- /.content -->

</div><!-- /.content-wrapper -->