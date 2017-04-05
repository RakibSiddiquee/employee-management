<?php
if(!$user->isLoggedIn() && $user->data()->isAdmin != 1){
	Redirect::to('home');
}

$db = DB::getInstance();

$success = '';
$error = array();
$userid = '';
$month = '';
$totalPresent = 0;
$totalAbsent = 0;
$totalLate = 0;
$totalLeave = 0;
if(isset($_POST['btnSearch'])){
	$userid = Input::get('name');
	if(empty($userid)) $error[] = 'Please select a name.';
	$month = Input::get('month');
	if(empty($month)) $error[] = 'Please select a month.';

	if(empty($error)){
		$db->query("SELECT a.*, DATE_FORMAT(a.date, '%D %b, %Y') AS attenDate, CONCAT(HOUR(TIMEDIFF(a.out_time, a.in_time)), ':', MINUTE(TIMEDIFF(a.out_time, a.in_time))) AS hours, CONCAT(u.firstname, ' ', u.lastname) AS fullname FROM attendance a RIGHT JOIN users u ON a.user_id = u.id WHERE a.date LIKE '%$month%' AND a.user_id = {$userid}");
	

		$results = $db->results();
		//var_dump($results);
		$total = $db->count();

		if($month > date('Y-m')){
			$holiday = 'Please give a past month !';
		}

		foreach ($results as $value) {
			if($value->date){
				$totalPresent += 1;
			}
			if($value->in_time > $settings->startTime()) $totalLate += 1;
		}

		// users of granted leave area


		// area to count working days of a month
		if(!empty($month)){
			$monthYear = explode('-', $month);

		//var_dump($month);

			$workdays = array();
			$day_count = cal_days_in_month(CAL_GREGORIAN, $monthYear[1], $monthYear[0]); // Get the amount of days

			//loop through all days
			for ($i = 1; $i <= $day_count; $i++) {
			    $date = $month.'-'.$i; //format date
			    $day = date('l', strtotime($date)); //get week day
			    //$day_name = substr($get_name, 0, 3); // Trim day name to 3 chars

			    //if not a weekend add day to array
			    if(!in_array($day, $settings->weekend())){
			        $workdays[] = $i;
			    }

			}
			//var_dump($workdays);

			$holidays = $db->query("SELECT COUNT(start) AS totalholidays FROM holiday WHERE start LIKE '%$month%'")->first();
			$leaveData = $db->query("SELECT SUM(days) AS totalLeave FROM emp_leave WHERE user_id = {$userid} AND (leave_from LIKE '%$month%' AND status = 'Granted')")->first();

			//var_dump($holidays);
			$totalWorkingDays = count($workdays) - $holidays->totalholidays;
			//var_dump($totalWorkingDays);

			$totalholidays = $holidays->totalholidays + ($day_count - count($workdays));
			//var_dump($totalholidays);
			// End area of count working days
			if(!empty($totalPresent)){
				$totalAbsent = $totalWorkingDays - ($totalPresent + $leaveData->totalLeave);
			}
		}
	}
}

?>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.1.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.1.1/js/buttons.bootstrap.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
<script type="text/javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
<script type="text/javascript" src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
<script type="text/javascript" src="//cdn.datatables.net/buttons/1.1.1/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="//cdn.datatables.net/buttons/1.1.1/js/buttons.print.min.js"></script>
<script type="text/javascript" src="//cdn.datatables.net/buttons/1.1.1/js/buttons.colVis.min.js"></script>

<link rel="stylesheet" type="text/css" href="//xmaxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.1.1/css/buttons.bootstrap.min.css">

<script type="text/javascript">
	jQuery(document).ready(function() {
    	var table = jQuery('#attendanceTable').DataTable( {
    		"order": [[ 0, "desc" ]],
	        lengthChange: false,
	        buttons: [ 'print', 'pdf', 'excel', 'colvis' ]
    	} );
 
    	table.buttons().container()
        .appendTo( '#attendanceTable_wrapper .col-sm-6:eq(0)' );

    	// Auto remove alert box
		window.setTimeout(function() {
		    jQuery(".alert").fadeTo(2000, 0).slideUp(2000, function(){
		        jQuery(this).remove(); 
		    });
		}, 2000);

	} );
</script>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 style="display: inline;">
	    <?php if(!empty($month)) echo date('F', strtotime($month)); ?> attendance of <?php if(!empty($results[0]->fullname)) echo $results[0]->fullname; ?>
	  </h1>

	  <ol class="breadcrumb">
	    <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
	    <li class="active"><?php echo $title; ?></li>
	  </ol>
	</section>

	<!-- Main content -->
	<section class="content">

		<div class="row">
			<div class="col-md-12">
			  <div class="box box-info">
			  	<div class="box-body">

					<div class="row">
						<div class="col-sm-12">
							<form action="" method="post" class="well well-sm form-inline" style="padding: 5px;">
								<div class="form-group">
									<label for="date">Name: </label>
					            	<select name="name" id="name" class="form-control">
										<option value="">Choose a name...</option>
										<?php
											$userResults = $db->get('users', array('1', '=', '1'))->results();
											foreach ($userResults as $value) {
										?>
										<option value="<?php echo $value->id; ?>"><?php echo $value->firstname . ' ' . $value->lastname . ' (' . $value->designation . ')'; ?> </option>
										<?php } ?>
									</select>
				            		
				            	</div>
								<div class="form-group">
									<label for="month">Month: </label>
				            		<input type ="text" name="month" id="month" class="form-control" placeholder="yyyy-mm">
				            	</div>
				            	<div class="form-group">
				            		<input type="submit" name="btnSearch" value="Get" class="btn btn-success">
				            	</div>
				            	<div class="pull-right" style="display: <?php if(isset($_POST['btnSearch'])){echo '';}else{echo 'none;';}?>">
					            	<div class="form-group">
					            		<button type="button" class="btn btn-info">Present <span class="badge"><?php echo $totalPresent; ?></span></button>
					            	</div>
					            	<div class="form-group">
					            		<button type="button" class="btn btn-danger">Absent <span class="badge"><?php if($totalAbsent > 0){echo $totalAbsent;}else{echo '0';} ?></span></button>
					            	</div>
					            	<div class="form-group">
					            		<button type="button" class="btn btn-warning">Late <span class="badge"><?php echo $totalLate; ?></span></button>
					            	</div>
					            	<div class="form-group">
					            		<button type="button" class="btn btn-primary">Leave <span class="badge"><?php if(!empty($leaveData->totalLeave)){echo $leaveData->totalLeave;}else{echo '0';} ?></span></button>
					            	</div>
				            	</div>
			            	</form>

							  <?php if(!empty($success)){ ?>
							  <div class="alert alert-danger fade in text-center col-sm-offset-3" style="padding: 5px; margin-bottom: 10px; width: 40%">
							  	<?php echo $success; ?>
							  	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
							  </div>
							  <?php }elseif(!empty($error)){ ?>
							   <div class="alert alert-danger fade in text-center col-sm-offset-3" style="padding: 5px; margin-bottom: 10px; width: 40%">
							  	<?php echo implode(' ', $error); ?>
							  	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
							  </div>
							  <?php }

			            	 if(isset($_POST['btnSearch']) && empty($error)){

			            		if(!empty($holiday)){ ?>
			            			<div class="alert alert-danger fade in text-center col-sm-offset-3" style="padding: 5px; margin-bottom: 10px; width: 40%"> <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><?php echo $holiday; ?></div>
			            	<?php }else{ ?>
						  	<table id="attendanceTable" class="table table-bordered table-striped">
							    <thead>
							      <tr>
							        <th>Date</th>
							        <th>Entry</th>
							        <th>Exit</th>
							        <th>Duty</th>
							        <th>Late</th>
							        <th>Extra</th>
							        <th>Status</th>
							        <th>Note</th>
							      </tr>
							    </thead>
							    <tbody>
								  	<?php
								  		foreach ($results as $result) { 
								  	?>
							      <tr id="<?php echo $result->id; ?>">
							        <td><?php echo $result->attenDate; ?></td>
							        <td> <!--In time-->
							        	<?php if($result->in_time > $settings->startTime() && $result->in_time != null) echo '<mark>';
							        	
							        	if($result->in_time != null){
							        		echo date('h:i a', strtotime($result->in_time));
							        	}else{echo '';}
							        	
							        	if($result->in_time < $settings->startTime()) echo '</mark>'; ?>
							        </td>
							        <td><!--Out time-->
							        	<?php if($result->out_time < $settings->closeTime() && $result->out_time != null) echo '<mark>';
							        	
							        	if($result->out_time != null){
							        		echo date('h:i a', strtotime($result->out_time));
							        	}else{echo '';}
							        	
							        	if($result->out_time < $settings->closeTime()) echo '</mark>'; ?>
							    	</td>
							        <td><!--Duty time-->
							        	<?php if($result->out_time != null){echo $result->hours;}else{echo '';} ?>
							    	</td>

							        <td><!--Late-->
							        	<?php
							        	if($result->in_time < $settings->startTime()){
							        		echo '';
							        	}else{
							        		$startTime = new DateTime($settings->startTime());
							        		echo $startTime->diff(new DateTime($result->in_time))->format('%h:%i');
							        	} ?>
							        </td>

							        <td><!--Extra time-->
							        	<?php
							        	if($result->out_time < $settings->closeTime()){
							        		echo '';
							        	}else{
							        		$closeTime = new DateTime($result->out_time);
							        		echo $closeTime->diff(new DateTime($settings->closeTime()))->format('%h:%i');
							        	} ?>
							        </td>
							        <td>
							        	<?php // Status
								        	if(!empty($result->in_time)){
								        		echo '<span class="label label-success">Present</span>';
								        	}else{
								        		foreach ($leaveData as $leave) {
								        			if($leave->user_id == $result->user_id){
								        				$startDate = strtotime($leave->leave_from);
														$endDate = strtotime($leave->leave_to);
														$inputDate = strtotime($date);
														if($inputDate >= $startDate && $inputDate <= $endDate) $alert = '<span class="label label-warning">Onleave</span>';
								        			}
								        		}
								        		if(!empty($alert)){
								        			echo $alert; 
								        		}else{
								        			echo '<span class="label label-danger">Absent</span>';
								        		}
								        		
								        			
								        	}
							        	 ?>
							        </td>

							        <td><?php echo $result->note; ?></td><!--Note-->
							      </tr>
							 	<?php }  ?>
							    </tbody>
						  	</table>
						  	<?php }  ?>
						</div>
			  		</div>
			    </div>
			  </div><!-- /.box -->
			  <?php } ?>
			</div>


		</div>
	 
	</section><!-- /.content -->
</div>

<link rel="stylesheet" href="xhttp://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/themes/smoothness/jquery-ui.css">
<script src="3rdparty/dist/js/jquery.mtz.monthpicker.js"></script>
<?php echo '<script> var year = '. date('Y') . '</script>';?>
<script type="text/javascript">
	jQuery('#month').monthpicker({pattern: 'yyyy-mm', 
	    selectedYear: year,
	    startYear: 2010,
	    finalYear: year
	});
</script>