<?php
if(!$user->isLoggedIn() || $user->data()->isAdmin != 1){
	Redirect::to('home');
}

$db = DB::getInstance();
$validate = new Validate();

$success = '';
$leaveid= '';
$errorType = '';
$errorLeaveFrom = '';
$errorLeaveTo = '';
$errorReason = '';

if(isset($_POST['edit'])){
	$db->get('emp_leave', array('id', '=', $_POST['leave_id']));
	$editData = $db->first();
	//var_dump($editData);
}

if(isset($_POST['update'])){
	if(Token::check(Input::get('token'))){

		$validation = $validate->check($_POST, array(
			'type' => array(
				'required' => true
			),
			'leave_from' => array(
				'required' => true
			),
			'leave_to' => array(
				'required' => true
			)
		));

		if($validation->passed()){
			//var_dump(Input::get('reason'));
			try{
				$db->insert('emp_leave', array(
					'user_id' => $user->data()->id,
					'type_id' => Input::get('type'),
					'leave_from' => Input::get('leave_from'),
					'leave_to' => Input::get('leave_to'),
					'days' => date_diff(date_create(Input::get('leave_from')), date_create(Input::get('leave_to')))->format("%a") + 1,
					'reason' => Input::get('reason'),
					'user_id_inserted' => $user->data()->id,
					'time_inserted' => date('Y-m-d H:i:s')
				));
				//var_dump($db);
				Session::flash('success', 'Leave application has been submitted successfully!');

			} catch (Exception $e){
				die('There was a problem submitting a leave application.');
			}
		} else {
			foreach ($validation->errors() as $error) {
				foreach ($error as $key => $value) {
					$errors[$key] = $value;
				}
			}
			if(!empty($errors['Type'])) $errorType = $errors['Type'];
			if(!empty($errors['Leave from'])) $errorLeaveFrom = $errors['Leave from'];
			if(!empty($errors['Leave to'])) $errorLeaveTo = $errors['Leave to'];
			if(!empty($errors['Reason'])) $errorReason = $errors['Reason'];

		}
	}
}


// Region of getdata of reason table
	$db->get('leave_type', array('1', '=', '1'));
	$typeData = $db->results();
	//var_dump($reasonData);
// End region of getdata of reason table

// Region of getdata of status table
	$db->get('leave_status', array('1', '=', '1'));
	$statusData = $db->results();
// End region of getdata of status table

if(Session::exists('success')){
	$success = Session::get('success');
	Session::delete('success');
}	

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 style="display: inline;">
	    Edit leave
	  </h1>
	  <!--<ol class="breadcrumb">
	    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
	    <li class="active">Dashboard</li>
	  </ol>-->
	  <?php if(!empty($success)) echo '<span class="success col-sm-offset-1">' . $success . '</span>'; ?>

 
	</section>

	<!-- Main content -->
	<section class="content">

		<div class="row">
			<div class="col-md-12">
			  <!-- Horizontal Form -->
			  <div class="box box-info">
			  	<div class="box-body">
			    <!-- form start -->
			    <form class="form-horizontal" action="" method="post">
			      <input type="hidden" name="leaveid" id="leaveid" value="<?php echo $editData->id; ?>">
			      <div class="box-body col-sm-10">
			        <div class="form-group">
			          <label for="type" class="col-sm-3 control-label">Status:</label>
			          <div class="col-sm-6">
			            	<select name="type" id="type" class="form-control">
								<option value="">Choose a status</option>		            		
								<?php 
									foreach ($statusData as $statusValue) {
								?>
								<option value="<?php echo $statusValue->status; ?>" <?php if($editData->status == $statusValue->status) echo 'selected'?>><?php echo $statusValue->status; ?></option>

								<?php } ?>
							</select>
			          </div>			          
			          <small class="error col-sm-3"><?php if(!empty($errorType)) echo $errorType; ?></small>
			        </div>

			        <div class="form-group">
			          <label for="type" class="col-sm-3 control-label">Leave type:</label>
			          <div class="col-sm-6">
			            	<select name="type" id="type" class="form-control">
								<option value="">Choose a type</option>		            		
								<?php 
									foreach ($typeData as $typeValue) {
								?>
								<option value="<?php echo $typeValue->id; ?>" <?php if($editData->type_id == $typeValue->id) echo 'selected'?>><?php echo $typeValue->type; ?></option>

								<?php } ?>
							</select>
			          </div>			          
			          <small class="error col-sm-3"><?php if(!empty($errorType)) echo $errorType; ?></small>
			        </div>
			        <div class="form-group">
			          <label for="leave_from" class="col-sm-3 control-label">Leave from:</label>
			          <div class="col-sm-6">
			            <input type="date" name="leave_from" id="leave_from" class="form-control" value="<?php echo $editData->leave_from; ?>" placeholder="Leave from"/>
			          </div>
			          <small class="error col-sm-3"><?php if(!empty($errorLeaveFrom)) echo $errorLeaveFrom; ?></small>
			        </div>
			        <div class="form-group">
			          <label for="leave_to" class="col-sm-3 control-label">Leave to:</label>
			          <div class="col-sm-6">
			            <input type="date" name="leave_to" id="leave_to" class="form-control" value="<?php echo $editData->leave_to; ?>" placeholder="Leave to"/>
			          </div>
			          <small class="error col-sm-3"><?php if(!empty($errorLeaveTo)) echo $errorLeaveTo; ?></small>
			        </div>
			        <div class="form-group">
			          <label for="reason" class="col-sm-3 control-label">Reason:</label>
			          <div class="col-sm-6">
			            <textarea name="reason" id="reason" class="form-control" rows="10" placeholder="Leave reason"></textarea>
			          </div>
			          <small class="error col-sm-3"><?php if(!empty($errorReason)) echo $errorReason; ?></small>
			        </div>
		            <script type="text/javascript">
		            	document.getElementById('reason').value = "<?php echo $editData->reason; ?>";
		            </script>

			        <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			        <div class="form-group">
			          <div class="col-sm-3"></div>
			          <div class="col-sm-6">
			            <input type="submit" name="update" class="btn btn-info" value="Update">
			          </div>
			          <div class="col-sm-3"></div>
			        </div>
			      </div><!-- /.box-body -->
 				  <div class="col-sm-2"></div>

			    </form>
			  </div><!-- /.box -->
			</div>


		</div>
	 
	</section><!-- /.content -->

</div><!-- /.content-wrapper -->