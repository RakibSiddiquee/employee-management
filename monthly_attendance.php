<?php
if(!$user->isLoggedIn() && $user->data()->isAdmin != 1){
	Redirect::to('home');
}

$db = DB::getInstance();

if(isset($_POST['btnSearch'])){
	$month = Input::get('month');
}else{
	$month = date('Y-m');
}

// area to count working days of a month
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
//var_dump($holidays);
$totalWorkingDays = count($workdays) - $holidays->totalholidays;
//var_dump($totalWorkingDays);

$totalholidays = $holidays->totalholidays + ($day_count - count($workdays));

// End area of count working days 

$db->query("SELECT a.*, COUNT(a.in_time) AS totalPresent, SUM(a.in_time > '" . $settings->startTime() . "') AS totalLate, SUM(a.out_time < '" . $settings->closeTime() . "') AS earlyLeft, DATE_FORMAT(a.date, '%D %b, %Y') AS attenDate, CONCAT(HOUR(TIMEDIFF(a.out_time, a.in_time)), ':', MINUTE(TIMEDIFF(a.out_time, a.in_time))) AS hours, CONCAT(u.firstname, ' ', u.lastname) AS fullname, u.id AS user_id FROM attendance a RIGHT JOIN users u ON a.user_id = u.id AND a.date LIKE '%$month%' GROUP BY u.id");

//var_dump($db);
$results = $db->results();
//var_dump($results);


$db->query("SELECT user_id, SUM(days) AS totalLeave FROM emp_leave WHERE status = 'Granted' and (leave_from and leave_to like '%$month%') GROUP BY user_id");
$leaveData = $db->results();
//var_dump($leaveData);

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
    		"order": [[ 1, "desc" ]],
	        lengthChange: false,
	        buttons: [ 'print', 'pdf', 'excel', 'colvis' ]
    	} );
 
    	table.buttons().container()
        .appendTo( '#attendanceTable_wrapper .col-sm-6:eq(0)' );
	} );
</script>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 style="display: inline;">
	    Monthly attendance
	  </h1>
	  <!--<ol class="breadcrumb">
	    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
	    <li class="active">Dashboard</li>
	  </ol>-->
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
									<label for="month">Search by month: </label>
				            		<input type ="month" name="month" id="month" class="form-control" placeholder="yyyy-mm">
				            	</div>
				            	<div class="form-group">
				            		<input type="submit" name="btnSearch" value="Get" class="btn btn-success">
				            	</div>
				            	<div class="pull-right">
					            	<div class="form-group">
					            		<button type="button" class="btn btn-info">Total Working Days <span class="badge"><?php if(!empty($totalWorkingDays)) echo $totalWorkingDays; ?></span></button>
					            	</div>
					            	<div class="form-group">
					            		<button type="button" class="btn btn-danger">Total Holidays <span class="badge"><?php if(!empty($totalholidays)) echo $totalholidays; ?></span></button>
					            	</div>
				            	</div>

			            	</form>

						  	<table id="attendanceTable" class="table table-bordered table-striped">
							    <thead>
							      <tr>
							        <th>Name</th>
							        <th>Total present</th>
							        <th>Total absent</th>
							        <th>Total late</th>
							        <th>Total leave</th>
							      </tr>
							    </thead>
							    <tbody>
								  	<?php
								  		foreach ($results as $result) {
								  	?>
							      <tr>
							        <td><?php echo $result->fullname; ?></td>
							        <td>
							        	<?php 
							        		if($result->totalPresent > 0){
							        			if($result->totalPresent > 1){
							        				echo $result->totalPresent . ' days';
							        			}else{
							        				echo $result->totalPresent . ' day';
							        			}
							        		}
							        	 ?>
							        </td>
							        <td>
							        	<?php 
							        		foreach ($leaveData as $leave){
							        			if($leave->user_id == $result->user_id){
							        				if($absent = $totalWorkingDays - ($result->totalPresent + $leave->totalLeave)){
							        					if(!($absent < 1) && $absent > 1){
							        						echo $absent . ' days';
								        				}elseif(!($absent < 1)){
								        					echo $absent . ' day';
								        				}
								        			}
							        			}
							        		}
							        	?>
							        </td>
							        <td>
							        	<?php 
							        		if($result->totalLate > 0){
							        			if($result->totalLate > 1){
							        				echo $result->totalLate . ' days';
							        			}else{
							        				echo $result->totalLate . ' day';
							        			}
							        		}
							        	 ?>
							    	</td>
							        <td>
							        	<?php 
							        		foreach ($leaveData as $leave){
							        			if($leave->user_id == $result->user_id){
							        				if($leave->totalLeave > 1){
							        					echo $leave->totalLeave . ' days';
							        				}else{
							        					echo $leave->totalLeave . ' day';
							        				}
							        			}
							        		}							        	 
							        	?>
							    	</td>

							      </tr>
							 	<?php } ?>
							    </tbody>
						  	</table>
						</div>
			  		</div>
			    </div>
			  </div><!-- /.box -->
			</div>


		</div>
	 
	</section><!-- /.content -->
</div>

<!--Area for month picker-->

<script src="3rdparty/dist/js/jquery.mtz.monthpicker.js"></script>
<?php echo '<script> var year = '. date('Y') . '</script>';?>
<script type="text/javascript">
	jQuery('#month').monthpicker({pattern: 'yyyy-mm', 
	    selectedYear: year,
	    startYear: 2010,
	    finalYear: year
	});
</script>