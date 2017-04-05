<?php

if(!$user->isLoggedIn() && $user->data()->isAdmin != 1){
	Redirect::to('login');
}

	$userid = '';
	$first_name = '';
	$last_name = '';
	$username = '';
	$email = '';
	$contact_number = '';
	$address = '';
	$designation_id = '';
	$designation = '';
	$joining_date = '';
	$status = '';
	$role = '';

	$errorFirstName ='';
	$errorLastName = '';
	$errorUsername = '';
	$errorEmail = '';
	$errorContactNumber = '';
	$errorAddress = '';
	$errorDesignation = '';
	$errorJoiningDate = '';

	$db = DB::getInstance();
	$data = $db->get('designation', array('1', '=', '1'))->results();


if(isset($_POST['edit'])){
	$userid = $_POST['user_id'];

	$rows = $db->get('users', array('id', '=', $userid))->first();

	$first_name = $rows->firstname;
	$last_name = $rows->lastname;
	$fullname = $first_name . ' ' . $last_name;
	$username = $rows->username;
	$email = $rows->email;
	$contact_number = $rows->contact_number;
	$address = $rows->address;
	$designation = escape($rows->designation);
	$joining_date = $rows->joined;
	$status = $rows->status;
	$role= $rows->isAdmin;
	//var_dump($rows);
} elseif (isset($_POST['update'])) {
	$userId = Input::get('userid');

	if(Token::check(Input::get('token'))){

		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'first_name' => array(
				'required' => true,
				'min' => 2,
				'max' => 20
			),
			'last_name' => array(
				'required' => true,
				'min' => 2,
				'max' => 20
			),
			'username' => array(
				'required' => true,
				'min' => 4,
				'max' => 10
			),
			'email' => array(
				'required' => true,
				'email' => true
			),
			'contact_number' => array(
				'required' => true,
				'min' => 7,
				'max' => 13
			),
			'address' => array(
				'required' => true
			),
			'designation' => array(
				'required' => true
			),
			'joining_date' => array(
				'required' => true
			)
		));	
		

		$getDesignation = Input::get('designation');
		//var_dump($getDesignation);
		if(!empty($getDesignation)){
			$getData = $db->get('users', array('designation', '=', $getDesignation))->first();
			
			$designationUserId = $getData->id;
			$designationValue = $getData->designation;

			if($designationUserId != $userId){
				if($designationValue == 'Chairman') {
					$errorDesignation = 'You have to delete existing chairman first.';
				} elseif ($designationValue == 'MD') {
					$errorDesignation = 'You have to delete existing MD first.';
				}
			}
		}


		if($validation->passed() && empty($errorDesignation)){

			//$db = DB::getInstance();

			try{
				$db->update('users', $userId, array(
					'firstname' => Input::get('first_name'),
					'lastname' => Input::get('last_name'),
					'username' => Input::get('username'),
					'email' => Input::get('email'),
					'contact_number' => Input::get('contact_number'),
					'address' => Input::get('address'),
					'designation' => $getDesignation,
					'joined' => Input::get('joining_date'),
					'status' => Input::get('status'),
					'isAdmin' => Input::get('role'),
					'user_id_updated' => $user->data()->id,
					'time_updated' => date('Y-m-d H:i:s')
				));

				Session::flash('success', 'Employee details have been updated successfully.');
				//echo '<script>window.location = "userlist";</script>';
				Redirect::to('employee_list');
			} catch(Exception $e){

			}

		} else {
			foreach ($validation->errors() as $error) {
				foreach ($error as $key => $value) {
					$errors[$key] = $value;
				}
			}

			if(!empty($errors['First name'])) $errorFirstName = $errors['First name'];
			$first_name = Input::get('first_name');
			if(!empty($errors['Last name'])) $errorLastName = $errors['Last name'];
			$last_name = Input::get('last_name');
			if(!empty($errors['Username'])) $errorUsername = $errors['Username'];
			$username = Input::get('username');
			if(!empty($errors['Email'])) $errorEmail = $errors['Email'];
			$email = Input::get('email');
			if(!empty($errors['Contact number'])) $errorContactNumber = $errors['Contact number'];
			$contact_number = Input::get('contact_number');
			if(!empty($errors['Address'])) $errorAddress= $errors['Address'];
			$address = Input::get('address');
			if(!empty($errors['Designation'])) $errorDesignation= $errors['Designation'];
			$designation = Input::get('Designation');
			if(!empty($errors['Joining date'])) $errorJoiningDate= $errors['Joining date'];
			$joining_date = Input::get('joining_date');
		}
	}
} else {
		Redirect::to('employee_list');
}


?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1>
	    Edit details of <?php if(!empty($fullname)) echo $fullname; ?>
	  </h1>
	  <ol class="breadcrumb">
	    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
	    <li class="active">Dashboard</li>
	  </ol>
	</section>

	<!-- Main content -->
	<section class="content">

		<div class="row">
			<div class="col-md-12">
			  <!-- Horizontal Form -->
			  <div class="box box-info">
			    <!-- form start -->
				<form class="form-horizontal" action="" method="post">
					<input type="hidden" name="userid" value="<?php echo $userid; ?>">
				  	<div class="box-body">
				  		<div class="col-sm-10">
						    <div class="form-group">
						      <label for="first_name" class="col-sm-3 control-label">First name:</label>
						      <div class="col-sm-6">
						        <input type="text" name="first_name" id="first_name" class="form-control" value="<?php if(count($first_name)) echo $first_name; ?>"/>
						      </div>
						      <small class="error col-sm-3"><?php echo $errorFirstName; ?></small>     
						    </div>
					        <div class="form-group">
					          <label for="last_name" class="col-sm-3 control-label">Last name:</label>
					          <div class="col-sm-6">
					            <input type="text" name="last_name" id="last_name" class="form-control" value="<?php if(count($last_name)) echo $last_name; ?>"/>
					          </div>
					          <small class="error col-sm-3"><?php echo $errorLastName; ?></small>
					        </div>

					        <div class="form-group">
					          <label for="username" class="col-sm-3 control-label">Username:</label>
					          <div class="col-sm-6">
					            <input type="text" name="username" id="username" class="form-control" value="<?php if(count($username)) echo $username; ?>"/>
					          </div>
					          <small class="error col-sm-3"><?php if(count($errorUsername) > 0) echo $errorUsername; ?></small>
					        </div>

					        <div class="form-group">
					          <label for="email" class="col-sm-3 control-label">Email:</label>
					          <div class="col-sm-6">
					            <input type="text" name="email" id="email" class="form-control" value="<?php if(count($email)) echo $email; ?>"/>
					          </div>
					          <small class="error col-sm-3"><?php if(count($errorEmail) > 0) echo $errorEmail; ?></small>
					        </div>

					        <div class="form-group">
					          <label for="contact_number" class="col-sm-3 control-label">Contact number:</label>
					          <div class="col-sm-6">
					            <input type="text" name="contact_number" id="contact_number" class="form-control" value="<?php if(count($contact_number)) echo $contact_number; ?>"/>
					          </div>
					          <small class="error col-sm-3"><?php if(count($errorContactNumber) > 0) echo $errorContactNumber; ?></small>
					        </div>

					        <div class="form-group">
					          <label for="address" class="col-sm-3 control-label">Address:</label>
					          <div class="col-sm-6">
					            <textarea name="address" id="address" class="form-control"></textarea>
					            <script type="text/javascript">
					            	document.getElementById('address').value = "<?php if(count($address)) echo $address; ?>";
					            </script>
					          </div>
					          <small class="error col-sm-3"><?php if(count($errorAddress) > 0) echo $errorAddress; ?></small>
					        </div>
							
					        <div class="form-group">
					          <label for="designation" class="col-sm-3 control-label">Designation<?php echo $value->designation; ?>:</label>
					          <div class="col-sm-6"><?php echo $value->designation; ?>
					            	<select name="designation" id="designation" class="form-control">
										<option value="">Choose a designation</option>            		
										<?php 
											foreach ($data as $value) {
										?>
										<option value="<?php echo $value->designation; ?>" <?php if($designation == $value->designation) echo "selected"; ?>><?php echo $value->designation; ?></option>

										<?php } ?>

									</select>
					          </div>
					          <small class="error col-sm-3"><?php if(count($errorDesignation) > 0) echo $errorDesignation; ?></small> 
					        </div>
					        <div class="form-group">
					          <label for="status" class="col-sm-3 control-label">Role:</label>
					          <div class="col-sm-6">
					          	<label class="radio-inline">
					            	<input type="radio" name="role" id="role" value="0" <?php if($role == 0) echo 'checked'; ?>>User
					            </label>
					            <label class="radio-inline">
					            	<input type="radio" name="role" id="role" value="1" <?php if($role == 1) echo 'checked'; ?>>Admin
					            </label>
					          </div>
					        </div>

					        <div class="form-group">
					          <label for="datepicker" class="col-sm-3 control-label">Joining date:</label>
					          <div class="col-sm-6">
					            <input type="text" name="joining_date" id="datepicker" class="form-control" value="<?php if(count($joining_date)) echo $joining_date; ?>">
					          </div>
					          <small class="error col-sm-3"><?php if(count($errorJoiningDate) > 0) echo $errorJoiningDate; ?></small> 
					        </div>
					        
					        <div class="form-group">
					          <label for="status" class="col-sm-3 control-label">Status:</label>
					          <div class="col-sm-6">
					          	<label class="radio-inline">
					            	<input type="radio" name="status" id="status" value="1" <?php if($status == 1) echo 'checked'; ?>>Active
					            </label>
					            <label class="radio-inline">
					            	<input type="radio" name="status" id="status" value="0" <?php if($status == 0) echo 'checked'; ?>>Inactive
					            </label>
					          </div>
					        </div>


						    <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
					        <div class="form-group">
					          <div class="col-sm-3"></div>
					          <div class="col-sm-6">
					            <input type="submit" name="update" class="btn btn-info btn-block" value="Update">
					          </div>
					          <div class="col-sm-3"></div>
					        </div>					   
 				  		</div>
 				  		<div class="col-sm-2"></div>
 				  	</div>
				</form>
			  </div><!-- /.box -->
			</div>


		</div>
	 
	</section><!-- /.content -->

</div><!-- /.content-wrapper -->