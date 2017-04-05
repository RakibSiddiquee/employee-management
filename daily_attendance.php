<?php
if($user->data()->isAdmin != 1){
	Redirect::to('home');
}

$db = DB::getInstance();

$error = '';
$totalPresent = 0;
$totalAbsent = 0;

if(isset($_POST['btnSearch'])){
	$date = Input::get('date');
	//var_dump($date);
	if(empty($date)) $error = 'Please select a date.';
}else{
	$date = date('Y-m-d');
}

$db->query("SELECT a.*, DATE_FORMAT(a.date, '%D %b, %Y') AS attenDate, CONCAT(HOUR(TIMEDIFF(a.out_time, a.in_time)), ':', MINUTE(TIMEDIFF(a.out_time, a.in_time))) AS hours, CONCAT(u.firstname, ' ', u.lastname) AS fullname, u.id AS user_id FROM attendance a RIGHT JOIN users u ON a.user_id = u.id AND a.date = '$date'");

$results = $db->results();
//var_dump($results);
$total = $db->count();


if($date > date('Y-m-d')){
	$holiday = 'Please give a past date !';
}else{
	$holiday = in_array(date('l', strtotime($date)), explode(',', $db->get('settings', array('1', '=', '1'))->first()->wholiday)) ? 'Today is weekly holiday !' : '';
}

foreach ($results as $value) {
	if($value->date){
		$totalPresent += 1;
	}
}

$totalAbsent = $total - $totalPresent;


// users of granted leave area

$leaveData = $db->get('emp_leave', array('status', '=', 'Granted'))->results();
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
	$(document).ready(function() {
    	var table = $('#attendanceTable').DataTable( {
    		"order": [[ 1, "desc" ]],
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
	    Daily attendance
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
									<label for="datepicker">Search by date: </label>
				            		<input type ="text" name="date" id="datepicker" class="form-control" placeholder="yyyy-mm-dd">
				            	</div>
				            	<div class="form-group">
				            		<input type="submit" name="btnSearch" value="Get" class="btn btn-success">
				            	</div>
				            	<div class="pull-right" style="display: <?php if($holiday === 'Friday')echo 'none;';?>">
					            	<div class="form-group">
					            		<button type="button" class="btn btn-info">Total Present <span class="badge"><?php echo $totalPresent; ?></span></button>
					            	</div>
					            	<div class="form-group">
					            		<button type="button" class="btn btn-danger">Total Absent <span class="badge"><?php echo $totalAbsent; ?></span></button>
					            	</div>
				            	</div>
			            	</form>

			            	<?php if(!empty($holiday)){ ?>
			            		<div class="alert alert-danger fade in text-center col-sm-offset-3" style="padding: 5px; margin-bottom: 10px; width: 40%"> <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><?php echo $holiday; ?></div>
							  <?php }elseif(!empty($error)){ ?>
							   <div class="alert alert-danger fade in text-center col-sm-offset-3" style="padding: 5px; margin-bottom: 10px; width: 40%">
							  	<?php echo $error; ?>
							  	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
							  </div>

			            	<?php }else{ ?>
						  	<table id="attendanceTable" class="table table-bordered table-striped">
							    <thead>
							      <tr>
							        <th>Name</th>
							        <th style="width: 65px;">Date</th>
							        <th style="width: 40px;">Entry</th>
							        <th style="width: 38px;">Exit</th>
							        <th style="width: 30px;">Duty</th>
							        <th style="width: 30px;">Late</th>
							        <th style="width: 35px;">Extra</th>
							        <th style="width: 40px;">Status</th>
							        <th>Note</th>
							      </tr>
							    </thead>
							    <tbody>
								  	<?php
								  		foreach ($results as $result) { 
								  	?>
							      <tr id="<?php echo $result->id; ?>">
							        <td><?php echo $result->fullname; ?></td>
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
						  	<?php } ?>
						</div>
			  		</div>
			    </div>
			  </div><!-- /.box -->
			</div>


		</div>
	 
	</section><!-- /.content -->
</div>