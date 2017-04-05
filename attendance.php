<?php
if(!$user->isLoggedIn()){
	Redirect::to('home');
}

$db = DB::getInstance();
$validate = new Validate();

$from = '';
$success = '';
$attendanceid = '';
$atten_id = '';
$user_id = '';

$errorName = '';
$errorDate = '';
$errorInTime = '';
$errorOutTime = '';

// Insert region

if(isset($_POST['submit'])){
	$validation = $validate->check($_POST, array(
		'name' => array(
			'required' => true
		),
		'date' => array(
			'required' => true
		),
		'in_time' => array(
			'required' => true
		),
		'out_time' => array(
			'required' => true
		)
	));

	if($validation->passed()){

		try{
			$db->insert('attendance', array(
				'user_id' => Input::get('name'),
				'date' => Input::get('date'),
				'in_time' => Input::get('in_time'),
				'out_time' => Input::get('out_time')
			));

			Session::flash('success', 'Attendance has been submitted successfully!');
			Redirect::to('attendance');

		} catch (Exception $e){
			die('There was a problem creating a attendance.');
		}
	} else {
		foreach ($validation->errors() as $error) {
			foreach ($error as $key => $value) {
				$errors[$key] = $value;
			}
		}
		if(!empty($errors['Name'])) $errorName = $errors['Name'];
		if(!empty($errors['Date'])) $errorDate = $errors['Date'];
		if(!empty($errors['In time'])) $errorInTime = $errors['In time'];
		if(!empty($errors['Out time'])) $errorOutTime = $errors['Out time'];

	}
// End Insert region

}elseif(isset($_POST['delete'])){
	//var_dump(Input::get('attendanceId'));
	$db->delete('attendance', array('id', '=', Input::get('attendanceId')));
	Session::flash('success', 'Attendance has been deleted successfully!');
	Redirect::to('attendance');
}

// Query for table data

if($user->data()->isAdmin == 1){
	$db->query("SELECT a.*, DATE_FORMAT(a.date, '%D %b, %Y') AS attenDate, CONCAT(HOUR(TIMEDIFF(a.out_time, a.in_time)), ':', MINUTE(TIMEDIFF(a.out_time, a.in_time))) AS hours, CONCAT(u.firstname, ' ', u.lastname) AS fullname FROM attendance a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.date DESC");
}else{
	$db->query("SELECT a.*, DATE_FORMAT(a.date, '%D %b, %Y') AS attenDate, CONCAT(HOUR(TIMEDIFF(a.out_time, a.in_time)), ':', MINUTE(TIMEDIFF(a.out_time, a.in_time))) AS hours, CONCAT(u.firstname, ' ', u.lastname) AS fullname FROM attendance a LEFT JOIN users u ON a.user_id = u.id WHERE user_id = {$user->data()->id} ORDER BY a.date DESC");			
}

$data = $db->results();
// End region of table data

// Region for note

if(isset($_POST['btnNote'])){
	$noteId = Input::get('noteId');
	$noteData = $db->get('attendance', array('id', '=', $noteId))->first();
	$noteid = $noteData->id;
	//var_dump($noteData);

}elseif(isset($_POST['btnSend'])){
	$noteData = Input::get('note');
	//var_dump($noteData);
	try{
		$db->update('attendance', Input::get('noteid'), array(
			'note' => Input::get('note')
		));

		Session::flash('success', 'Note has been sent successfully!');
		Redirect::to('attendance');

	} catch (Exception $e){
		die('There was a problem sending the note.');
	}
}

// End region of note


if(Session::exists('success')){
	$success = Session::get('success');
	Session::delete('success');
}

?>

<script type="text/javascript">
  jQuery(document).ready(function() {
      jQuery('#attendanceTable').DataTable({
      	"order": [],
      });
  });
</script>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

<?php if($user->data()->isAdmin == 1){ ?>

<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 style="display: inline;">
	    Attendance
	  </h1>
	  <?php if(!empty($success)){ ?>
	  <div class="alert alert-success fade in text-center col-sm-offset-3" style="padding: 4px; width: 40%; margin-top: -30px; margin-bottom: 0;">
	  	<?php echo $success; ?>
	  	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
	  </div>
	  <?php } ?>
	  <ol class="breadcrumb">
	    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
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

						<form action="" method="post" class="form-inline">	
						<fieldset class="col-sm-12"><legend style="margin-bottom: 5px;">Insert attendance</legend>
						<?php
							if(count($errorName) > 0 ) {echo '<span class="error col-sm-offset-1">' . $errorName . '</span>';}
							if(count($errorDate) > 0 ) echo '<small class="error col-sm-offset-1">' . $errorDate . '</small>';
							if(count($errorInTime) > 0 ) echo '<small class="error col-sm-offset-1">' . $errorInTime . '</small>';
							if(count($errorOutTime) > 0 ) echo '<small class="error col-sm-offset-1">' . $errorOutTime . '</small>';
						?>

							<div class="col-sm-12" style="border-bottom: 1px dashed #3c8dbc; padding: 10px 10px 20px; margin-bottom: 30px;">
								<div class="form-group">
									<label for="name">Name: </label>
					            	<select name="name" id="name" class="form-control">
										<option value="">Choose a name</option>		            		
										<?php
											$results = $db->get('users', array('1', '=', '1'))->results();
											foreach ($results as $value) {
										?>
										<option value="<?php echo $value->id; ?>"><?php echo $value->firstname . ' ' . $value->lastname; ?></option>

										<?php } ?>
									</select>
								</div>
								<div class="form-group">
									<label for="datepicker">&nbsp;Date: </label>
			           				<input type="text" name="date" id="datepicker" class="form-control" value="" placeholder="date"/>
								</div>
								<div class="form-group">
									<label for="in_time">&nbsp;In time: </label>
			           				<input type="time" name="in_time" id="in_time" class="form-control" value="" placeholder="In time"/>
								</div>
								<div class="form-group">
									<label for="out_time">&nbsp;Out time: </label>
			           				<input type="time" name="out_time" id="out_time" class="form-control" value="" placeholder="Out time"/>
								</div>

								<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
								
								<input type="submit" name="submit" value="Submit" class="btn btn-info">
							</div>
							</fieldset>	
						</form>

						<div class="col-sm-12">
						  	<table id="attendanceTable" class="table table-bordered table-striped">
							    <thead>
							      <tr>
							        <th>Name</th>
							        <th style="width: 65px;">Date</th>
							        <th style="width: 40px;">Entry</th>
							        <th style="width: 40px;">Exit</th>
							        <th style="width: 30px;">Duty</th>
							        <th style="width: 30px;">Late</th>
							        <th style="width: 35px;">Extra</th>
							        <th style="width: 40px;">Status</th>
							        <th>Note</th>
									<th style="width: 25px;"><span class="btn btn-default btn-xs glyphicon glyphicon-cog"></span></th>
							      </tr>
							    </thead>
							    <tbody>
								  	<?php
								  		foreach ($data as $row) { 
								  	?>
							      <tr id="<?php echo $row->id; ?>">
							        <td><?php echo $row->fullname; ?></td>
							        <td><?php echo $row->attenDate; ?></td>
							        <td> <!--In time-->
							        	<?php if($row->in_time > $settings->startTime() && $row->in_time != '00:00:00') echo '<mark>';
							        	
							        	if($row->in_time != '00:00:00'){
							        		echo date('h:i a', strtotime($row->in_time));
							        	}else{echo '00:00';}
							        	
							        	if($row->in_time < $settings->startTime()) echo '</mark>'; ?>
							        </td>
							        <td><!--Out time-->
							        	<?php if($row->out_time < $settings->closeTime() && $row->out_time != '00:00:00') echo '<mark>';
							        	
							        	if($row->out_time != '00:00:00'){
							        		echo date('h:i a', strtotime($row->out_time));
							        	}else{echo '';}
							        	
							        	if($row->out_time < $settings->closeTime()) echo '</mark>'; ?>
							    	</td>
							        <td><!--Duty time-->
							        	<?php if($row->out_time != '00:00:00'){echo $row->hours;}else{echo '';} ?>
							    	</td>

							        <td><!--Late-->
							        	<?php
							        	if($row->in_time < $settings->startTime()){
							        		echo '';
							        	}else{
							        		$startTime = new DateTime($settings->startTime());
							        		echo $startTime->diff(new DateTime($row->in_time))->format('%h:%i');
							        	} ?>
							        </td>

							        <td><!--Extra time-->
							        	<?php
							        	if($row->out_time < $settings->closeTime()){
							        		echo '';
							        	}else{
							        		$closeTime = new DateTime($row->out_time);
							        		echo $closeTime->diff(new DateTime($settings->closeTime()))->format('%h:%i');
							        	} ?>
							        </td>
							        <td>
							        	<?php // Status
								        	if($row->in_time){
								        		echo '<span class="label label-success">Present</span>';
								        	}
							        	 ?>
							        </td>

							        <td><?php echo $row->note; ?></td><!--Note-->
							        <td>
							        	<form action="edit_attendance" method="post">
							        		<input type="hidden" name="attendanceId" value="<?php echo $row->id; ?>">
							        		<button type="submit" name="edit" class="btn btn-warning btn-xs" title="Edit attendance"><span class="glyphicon glyphicon-pencil"></span></button>
							        		<!--<button type="submit" name="delete" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure to delete this data?')" title="Delete attendance"><span class="glyphicon glyphicon-remove"></span></button>-->

							        	</form>
							    	</td>
							      </tr>
							 	<?php }  ?>
							    </tbody>
						  	</table>
						</div>
			  		</div>
			    </div>
			  </div><!-- /.box -->
			</div>


		</div>
	 
	</section><!-- /.content -->

<!-- End region for Admin Panel -->
<?php }else{ ?>

<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1>
	    Attendance of <?php echo $data[0]->fullname;?>
	  </h1>
	  <!--<ol class="breadcrumb">
	    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
	    <li class="active">Dashboard</li>
	  </ol>-->
	  <?php if(!empty($success)) echo '<span class="success col-sm-offset-5">' . $success . '</span>'; ?>	  
	</section>

	<!-- Main content -->
	<section class="content">

		<div class="row">
			<div class="col-md-12">
			  <div class="box box-info">
			  	<div class="box-body">
					<div class="row" style="margin-top: 10px;">

						<div class="col-sm-12">
						  	<table id="attendanceTable" class="table table-bordered table-striped">
							    <thead>
							      <tr>
							        <th>Date</th>
							        <th>In time</th>
							        <th>Out time</th>
							        <th>Duty time</th>
							        <th>Overtime</th>
							        <th>Note</th>
							      </tr>
							    </thead>
							    <tbody>
								  	<?php
								  		foreach ($data as $row) { 
								  	?>
							      <tr>
							        <td><?php echo $row->attenDate; ?></td>
							        <td>
							        	<?php if($row->in_time > $settings->startTime() && $row->in_time != '00:00:00') echo '<mark>';
							        	
							        	if($row->in_time != '00:00:00'){
							        		echo date('h:i a', strtotime($row->in_time));
							        	}else{echo '';}
							        	
							        	if($row->in_time < $settings->startTime()) echo '</mark>'; ?>
							        </td>
							        <td><?php if($row->out_time < $settings->closeTime() && $row->out_time != '00:00:00') echo '<mark>';
							        	
							        	if($row->out_time != '00:00:00'){
							        		echo date('h:i a', strtotime($row->out_time));
							        	}else{echo '';}
							        	
							        	if($row->out_time < $settings->closeTime()) echo '</mark>'; ?>
							    	</td>
							        <td><!--Duty time-->
							        	<?php if($row->out_time != '00:00:00'){echo $row->hours;}else{echo '';} ?>
							    	</td>
							        <td><!--Over time-->
							        	<?php
							        	if($row->out_time < $settings->closeTime()){
							        		echo '';
							        	}else{
							        		$closeTime = new DateTime($row->out_time);
							        		echo $closeTime->diff(new DateTime($settings->closeTime()))->format('%h:%i');
							        	} ?>
							        </td>
							    	<?php if(isset($_POST['btnNote']) && $row->id == $noteid){ ?>
							    	<td colspan="2" style="width: 28%">
										<form action="" method="post" class="form-inline pull-right">
											<input type="hidden" name="noteid" value="<?php echo $noteid; ?>">
											<div class="form-group">
											  	<textarea class="form-control" name="note" id="note" rows="1" cols="25"></textarea>
								           		<script type="text/javascript">
								            		document.getElementById('note').value = "<?php echo $row->note; ?>";
								            	</script>
											</div>
											<input type="hidden" name="token" value="' . Token::generate() . '">
											<input type="submit" value="Save" name="btnSend" class="btn btn-info btn-sm">

									</td>
										</form>

									<?php }else { ?>
							        <td><?php echo $row->note; ?>
							        	<form action="" method="post" class="pull-right">
							        		<input type="hidden" name="noteId" value="<?php echo $row->id; ?>">
							        		<button type="submit" name="btnNote" class="btn btn-info btn-xs" title="Write a note"><span class="glyphicon glyphicon-comment"></span></button>
							        	</form>
							        </td>
									<?php } ?>									

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
<?php } ?>	
</div><!-- /.content-wrapper -->