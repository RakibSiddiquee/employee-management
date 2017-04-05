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

// Get data of attendance
//$queryDate = date('Y-m-d', strtotime(date('Y-m-d')) - (86400*2));
$recentAttendance = $db->query("SELECT a.*, CONCAT(a.date, ' ', a.in_time) AS time_inserted, CONCAT(a.date, ' ', a.out_time) AS time_updated, u.firstname, u.lastname FROM attendance a LEFT JOIN users u ON a.user_id = u.id ORDER BY CONCAT(a.date, ' ', a.in_time) DESC LIMIT 5")->results();
//var_dump($recentAttendance);
$recentLeave = $db->query("SELECT l.*, u.firstname, u.lastname FROM emp_leave l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.time_inserted DESC LIMIT 5")->results();
//var_dump($recentLeave);
$recentActs = array_merge($recentAttendance, $recentLeave);


if(Session::exists('success')){
  $success = Session::get('success');
  Session::delete('success');
}

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

     jQuery('#taskTable').DataTable({
        "order": [],
        "lengthMenu": [ 5, 10, 25, 50, 100 ],
        "pagingType": "simple",
     });

  });


</script>
<style type="text/css">
  <?php echo $cssclass; ?>{
    background-color: #F08A24;
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
  <?php if(!empty($success)){ ?>
  <div class="alert alert-success fade in text-center col-sm-offset-3" style="padding: 4px; width: 40%; margin-top: -30px; margin-bottom: 0;">
  	<?php echo $success; ?>
  	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
  </div>
  <?php } ?>
  
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
          <h3><?php echo $totalUsers; ?></h3>
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
          <h3><?php echo $totalPresent; ?></h3>
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
          <h3><?php echo $totalLate; ?></h3>
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
          <h3><?php echo $totalAbsent; ?></h3>
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
    <section class="col-sm-7">
      <!-- Custom tabs (Charts with tabs)-->
      <div class="box box-info">        
        <div id='calendar'></div>
      </div><!-- /.nav-tabs-custom -->

    </section><!-- /.Left col -->
    <!-- right col (We are only adding the ID to make the widgets sortable)-->
    <section class="col-sm-5 connectedSortable">

      <!-- Tasks -->
      <div class="box box-info">          
        <div class="box-body">
          <table class="table table-bordered box-footer text-black" id="taskTable">
            <thead class="bg-green-gradient">
              <th>Task</th>
              <th>Status</th>
            </thead>
            <tbody>
              <?php 
                if($user->data()->isAdmin == 1){
                  foreach ($adminTasks as $adminTask) { ?>
                    <tr>
                      <td>
                        <a href="view_task?<?php echo $adminTask->id; ?>">
                          <span class="pull-left"><?php if(!empty($adminTask->title)) echo $adminTask->title; ?></span>
                       </a>          
                      </td>
                      <td>
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
                      </td>
                    </tr>

              <?php } }else{ 

                foreach ($userTasks as $userTask) { 
              ?>
                    <tr>
                      <td>
                        <a href="view_task?<?php echo $userTask->id; ?>">
                          <span class="pull-left"><?php if(!empty($userTask->title)) echo $userTask->title; ?></span>
                       </a>          
                      </td>
                      <td>
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
                      </td>
                    </tr>

              <?php } } ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Recent Activity -->

      <div class="box box-info direct-chat direct-chat-info">
        <div class="box-header with-border">
          <h3 class="box-title">Recent Activity</h3>
          <div class="box-tools pull-right">
            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
          </div>
        </div><!-- /.box-header -->
        <div class="box-body">
          <!-- Conversations are loaded here -->
          <div class="direct-chat-messages">
            <?php 
            if($user->data()->isAdmin == 1){
              foreach ($recentActs as $recentAct) { 
                if($recentAct->time_updated > $recentAct->time_inserted || $recentAct->time_inserted > $recentAct->time_updated){
              ?>
                <!-- Message. Default to the left -->
                <div class="direct-chat-msg">
                  <div class="direct-chat-img bg-green-gradient" title="<?php echo $recentAct->firstname . ' ' . $recentAct->lastname;?>">
                    <?php if(!empty($recentAct->firstname)) echo substr($recentAct->firstname, 0, 1) . substr($recentAct->lastname, 0, 1);?>
                  </div>
                  <div class="direct-chat-text">
                    <span class="direct-chat-name pull-left">
                      <?php if(!empty($recentAct->firstname)) echo $recentAct->firstname . ' ' . $recentAct->lastname; ?>
                    </span>
                    <span class="direct-chat-timestamp pull-right">
                      <?php
                        if($recentAct->time_updated > $recentAct->time_inserted){
                          echo date('jS M-Y, h:ia', strtotime($recentAct->time_updated));
                        }else{
                          echo date('jS M-Y, h:ia', strtotime($recentAct->time_inserted));               
                        }
                      ?>
                    </span><br/><br/>
                    <em>
                    <?php
                      if(!empty($recentAct->in_time) || !empty($recentAct->out_time)){
                        if($recentAct->out_time > $recentAct->in_time){
                          echo 'Clocked out';
                        }else{
                          echo 'Clocked in';
                        }
                      }elseif(!empty($recentAct->status)){
                        if($recentAct->status == 'Granted'){
                          echo 'Leave is granted';
                        }elseif ($recentAct->status == 'Rejected') {
                          echo 'Leave is rejected';
                        }elseif ($recentAct->status == 'Pending') {
                          echo 'Leave is pending';
                        }
                      }
                    ?>
                    </em>
                  </div><!-- /.direct-chat-text -->
                </div><!-- /.direct-chat-msg -->
            <?php
              } } }else{
              foreach ($recentActs as $recentAct) {
                if($recentAct->user_id == $user->data()->id){
                  if($recentAct->time_updated > $recentAct->time_inserted || $recentAct->time_inserted > $recentAct->time_updated){
            ?>
                <!-- Message. Default to the left -->
                <div class="direct-chat-msg">
                  <div class="direct-chat-img bg-green-gradient" title="<?php echo $recentAct->firstname . ' ' . $recentAct->lastname;?>">
                    <?php if(!empty($recentAct->firstname)) echo substr($recentAct->firstname, 0, 1) . substr($recentAct->lastname, 0, 1);?>
                  </div>
                  <div class="direct-chat-text">
                    <span class="direct-chat-name pull-left">
                      <?php if(!empty($recentAct->firstname)) echo $recentAct->firstname . ' ' . $recentAct->lastname; ?>
                    </span>
                    <span class="direct-chat-timestamp pull-right">
                      <?php
                        if($recentAct->time_updated > $recentAct->time_inserted){
                          echo date('jS M-Y, h:ia', strtotime($recentAct->time_updated));
                        }else{
                          echo date('jS M-Y, h:ia', strtotime($recentAct->time_inserted));               
                        }
                      ?>
                    </span><br/><br/>
                    <em>
                    <?php
                      if(!empty($recentAct->in_time) || !empty($recentAct->out_time)){
                        if($recentAct->out_time > $recentAct->in_time){
                          echo 'Clocked out';
                        }else{
                          echo 'Clocked in';
                        }
                      }elseif(!empty($recentAct->status)){
                        if($recentAct->status == 'Granted'){
                          echo 'Leave is granted';
                        }elseif ($recentAct->status == 'Rejected') {
                          echo 'Leave is rejected';
                        }elseif ($recentAct->status == 'Pending'){
                          echo 'Leave is Pending';
                        }
                      }
                    ?>
                    </em>
                  </div><!-- /.direct-chat-text -->
                </div><!-- /.direct-chat-msg -->
            <?php } } } } ?>
          </div><!-- /.direct-chat-pane -->
        </div><!-- /.box-body -->
      </div><!--/.direct-chat -->

    </section><!-- right col -->
  </div><!-- /.row (main row) -->

</section><!-- /.content -->

</div><!-- /.content-wrapper -->