<?php 
if($user->data()->isAdmin != 1){
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

if(isset($_POST['edit'])){
// Start Edit region
	
	$attendanceId = Input::get('attendanceId');
	$editData = $db->get('attendance', array('id', '=', $attendanceId))->first();
	$atten_id = $editData->id;
	$user_id = $editData->user_id;

	// End edit region

	//start update region

}elseif(isset($_POST['update'])){

	$validation = $validate->check($_POST, array(
		'name' => array(
			'required' => true
		),
		'date' => array(
			'required' => true
		),
		'in_time' => array(
			'required' => true
		)
	));

	if($validation->passed()){

		try{
			$db->update('attendance', Input::get('attenid'), array(
				'user_id' => Input::get('name'),
				'date' => Input::get('date'),
				'in_time' => Input::get('in_time'),
				'out_time' => Input::get('out_time')
			));

			Session::flash('success', 'Attendance has been updated successfully!');
			Redirect::to('attendance');
		} catch (Exception $e){
			die('There was a problem updating attendance.');
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


}

?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

<?php if($user->data()->isAdmin == 1){ ?>

<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 style="display: inline;">
	    Edit attendance
	  </h1>
	  <!--<ol class="breadcrumb">
	    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
	    <li class="active">Dashboard</li>
	  </ol>-->
	  <?php if(!empty($success)) echo '<span class="success col-sm-offset-3">' . $success . '</span>'; ?>	  
	</section>

	<!-- Main content -->
	<section class="content">

		<div class="row">
			<div class="col-md-12">
			  <div class="box box-info">
			  	<div class="box-body">
					<form action="" method="post" class="form-inline">
						<input type="hidden" name="attenid" value="<?php echo $atten_id; ?>">
						<div class="form-group">
							<label for="name">Name: </label>
					    	<select name="name" id="name" class="form-control">
								<option value="">Choose a name</option>	
								<?php 
									$results = $db->get('users', array('1', '=', '1'))->results();
									foreach ($results as $value) { 
								?>          		
								<option value="<?php echo $value->id; ?>" <?php if($user_id == $value->id) echo "selected"; ?>><?php echo $value->firstname . ' ' . $value->lastname; ?>
								</option>
								<?php } ?>
							</select>
						</div>
						<div class="form-group">
							<label for="datepicker">&nbsp;Date: </label>
								<input type="text" name="date" id="datepicker" class="form-control" value="<?php echo $editData->date; ?>" placeholder="date"/>
						</div>
						<div class="form-group">
							<label for="in_time">&nbsp;In time: </label>
								<input type="time" name="in_time" id="in_time" class="form-control" value="<?php echo $editData->in_time; ?>" placeholder="In time"/>
						</div>
						<div class="form-group">
							<label for="out_time">&nbsp;Out time: </label>
								<input type="time" name="out_time" id="out_time" class="form-control" value="<?php if($editData->out_time != '00:00:00') echo $editData->out_time; ?>" placeholder="Out time"/>
						</div>

						<input type="hidden" name="token" value="' . Token::generate() . '">
						<input type="submit" value="Update" name="update" class="btn btn-success">
					</form>
			    </div>
			  </div><!-- /.box -->
			</div>


		</div>
	 
	</section><!-- /.content -->
</div>

<?php } ?>