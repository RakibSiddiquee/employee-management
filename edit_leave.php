<?php
if(!$user->isLoggedIn() && $user->data()->isAdmin !=1){
	Redirect::to('home');
}

$db = DB::getInstance();
$validate = new Validate();

$success = '';
$leaveid= '';
$errorType = '';
$errorLeaveFrom = '';
$errorLeaveTo = '';

if(isset($_POST['btnEdit'])){
	$leave_id = $_POST['leave_id'];
	$db->query("SELECT l.*, CONCAT(u.firstname, ' ', u.lastname) AS fullname FROM `emp_leave` l LEFT JOIN users u ON l.user_id = u.id WHERE l.id = {$leave_id} ORDER BY l.time_inserted DESC");
	$leaveData = $db->first();
	//var_dump($leaveData);
}

if(isset($_POST['btnUpdate'])){
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
				$db->update('emp_leave', Input::get('leaveid'), array(
					'type_id' => Input::get('type'),
					'leave_from' => Input::get('leave_from'),
					'leave_to' => Input::get('leave_to'),
					'days' => date_diff(date_create(Input::get('leave_from')), date_create(Input::get('leave_to')))->format("%a") + 1,
					'reason' => Input::get('reason'),					
					'user_id_updated' => $user->data()->id,
					'time_updated' => date('Y-m-d H:i:s')
				));
				//var_dump($db);
				Session::flash('success', 'Leave application has been updated successfully!');
				Redirect::to('leaves');
			} catch (Exception $e){
				die('There was a problem updating the leave application.');
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
		}
	}
}


// Region of getdata of reason table
	$db->get('leave_type', array('1', '=', '1'));
	$typeData = $db->results();
	//var_dump($reasonData);
// End region of getdata of reason table

?>

<script>
// for datepicker
jQuery(function() {
  jQuery( "#datepickerto" ).datepicker({dateFormat: "yy-mm-dd"});
});

</script>  

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 style="display: inline;">
	    Edit leave of <?php if(!empty($leaveData->fullname)) echo $leaveData->fullname; ?>
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
			  <!-- Horizontal Form -->
			  <div class="box box-info">
			  	<div class="box-body">
			    <!-- form start -->
			    <form class="form-horizontal" action="" method="post">
			      <input type="hidden" name="leaveid" value="<?php echo $leaveData->id; ?>">
			      <div class="box-body col-sm-10">
			        <div class="form-group">
			          <label for="type" class="col-sm-3 control-label">Type:</label>
			          <div class="col-sm-6">
			            	<select name="type" id="type" class="form-control">
								<option value="">Choose a type</option>		            		
								<?php 
									foreach ($typeData as $typeValue) {
								?>
								<option value="<?php echo $typeValue->id; ?>" <?php if($leaveData->type_id == $typeValue->id) echo 'selected'; ?>><?php echo $typeValue->type; ?></option>

								<?php } ?>
							</select>
			          </div>			          
			          <small class="error col-sm-3"><?php if(!empty($errorType)) echo $errorType; ?></small>
			        </div>
			        <div class="form-group">
			          <label for="datepicker" class="col-sm-3 control-label">Leave from:</label>
			          <div class="col-sm-6">
			            <input type="date" name="leave_from" id="datepicker" class="form-control" value="<?php if(!empty($leaveData->leave_from)) echo $leaveData->leave_from; ?>"/>
			          </div>
			          <small class="error col-sm-3"><?php if(!empty($errorLeaveFrom)) echo $errorLeaveFrom; ?></small>
			        </div>
			        <div class="form-group">
			          <label for="datepickerto" class="col-sm-3 control-label">Leave to:</label>
			          <div class="col-sm-6">
			            <input type="date" name="leave_to" id="datepickerto" class="form-control" value="<?php if(!empty($leaveData->leave_to)) echo $leaveData->leave_to; ?>" placeholder="Leave to"/>
			          </div>
			          <small class="error col-sm-3"><?php if(!empty($errorLeaveTo)) echo $errorLeaveTo; ?></small>
			        </div>
			        <div class="form-group">
			          <label for="reason" class="col-sm-3 control-label">Reason:</label>
			          <div class="col-sm-6">
			            <textarea name="reason" id="reason" class="form-control" rows="5" placeholder="Leave reason"><?php echo $leaveData->reason; ?></textarea>
			          </div>
			          <small class="error col-sm-3"><?php if(!empty($errorReason)) echo $errorReason; ?></small>
			        </div>
			        

			        <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			        <div class="form-group">
			          <div class="col-sm-3"></div>
			          <div class="col-sm-6">
			            <input type="submit" name="btnUpdate" class="btn btn-success" value="Update">
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