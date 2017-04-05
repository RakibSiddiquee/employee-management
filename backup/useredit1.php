<?php
require_once 'core/init.php';

$user = new User();
if($user->isLoggedIn()){
	$db = DB::getInstance();
	$user_id = '';
	if(isset($_POST['user_id'])){
	$user_id = $_POST['user_id'];
	$rows = $db->get('users', array('id', '=', $user_id))->results();
	}

	$errors = [];
	$success = '';
	$errorFirstName = '';
	$errorLastName = '';
	$errorUsername = '';
	$errorEmail = '';
	$errorPassword = '';
	$errorRetypePassword = '';
	$errorContactNumber = '';

	$data = $db->get('designation', array('1', '=', '1'))->results();


	if(Input::exists()){
		if(Token::check(Input::get('token'))){

			$validate = new Validate();
			$validation = $validate->check($_POST, array(
				'first_name' => array(
					'min' => 2,
					'max' => 20
				),
				'last_name' =>array(
					'min' => 2,
					'max' => 20
				),
				'username' => array(
					'min' => 4,
					'max' => 10
				),			
				'email' => array(
					'email' =>  true
				),
				'password' => array(
					'min' => 8,
				),
				'retype_password' => array(
					'matches' => 'password',
				),
				'contact_number' => array(
					'min' => 7,
					'max' => 13
				),
			));

			if($validation->passed()){
				$user = new User();

				$salt = Hash::salt(32);


				try{
					$db->update('users', $user_id, array(
						'firstname' => Input::get('first_name'),
						'lastname' => Input::get('last_name'),
						'username' => Input::get('username'),			
						'email' => Input::get('email'),			
						'password' => Hash::make(Input::get('password'), $salt),
						'salt' => $salt,
						'contact_number' => Input::get('contact_number'),
						'address' => Input::get('address'),
						'designation_id' => Input::get('designation'),
						'joined' => Input::get('joining_date'),
						'user_id_inserted' => $user->data()->id,
						'time_inserted' => date('Y-m-d H:i:s')
					));

					Session::flash('home', 'Data has been updated successfully!');
					Redirect::to('userlist');
					//$success = Session::get('home');


				} catch(Exception $e){
					die($e->getMessage());
				}

			} else {
				foreach ($validation->errors() as $error) {
					foreach ($error as $key => $value) {
						$errors[$key] = $value;
					}
				}
				if(!empty($errors['First name'])) $errorFirstName = $errors['First name'];
				if(!empty($errors['Last name'])) $errorLastName = $errors['Last name'];
				if(!empty($errors['Username'])) $errorUsername = $errors['Username'];
				if(!empty($errors['Email'])) $errorEmail = $errors['Email'];
				if(!empty($errors['Password'])) $errorPassword = $errors['Password'];
				if(!empty($errors['Retype password'])) $errorRetypePassword = $errors['Retype password'];
				if(!empty($errors['Contact number'])) $errorContactNumber = $errors['Contact number'];
		          
			}
		}
	}

	?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1>
	    Add new employee
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
			      <div class="box-body col-sm-10">
			        <div class="form-group">
			          <span class="col-sm-3"></span>
			          <span class="success col-sm-6">
			           <?php //echo $success; ?>
			          </span>
			          <span class="error col-sm-3"></span>
			         
			        </div>

			        <div class="form-group">
			          <label for="first_name" class="col-sm-3 control-label">First name:</label>
			          <div class="col-sm-6">
			            <input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo escape($rows[0]->firstname); ?>"/>
			          </div>
			          <small class="error col-sm-3"><?php if(count($errorFirstName) > 0 ) echo $errorFirstName; ?></small>
			         
			        </div>

			        <div class="form-group">
			          <label for="last_name" class="col-sm-3 control-label">Last name:</label>
			          <div class="col-sm-6">
			            <input type="text" name="last_name" id="last_name" class="form-control" value="<?php echo escape($rows[0]->lastname); ?>"/>
			          </div>
			          <small class="error col-sm-3"><?php if(count($errorLastName) > 0) echo $errorLastName; ?></small>
			        </div>

			        <div class="form-group">
			          <label for="username" class="col-sm-3 control-label">Username:</label>
			          <div class="col-sm-6">
			            <input type="text" name="username" id="username" class="form-control" value="<?php echo escape($rows[0]->username); ?>"/>
			          </div>
			          <small class="error col-sm-3"><?php if(count($errorUsername) > 0) echo $errorUsername; ?></small>
			        </div>

			        <div class="form-group">
			          <label for="email" class="col-sm-3 control-label">Email:</label>
			          <div class="col-sm-6">
			            <input type="email" name="email" id="email" class="form-control" value="<?php echo escape($rows[0]->email); ?>"/>
			          </div>
			          <small class="error col-sm-3"><?php if(count($errorEmail) > 0) echo $errorEmail; ?></small>
			        </div>

			        <div class="form-group">
			          <label for="password" class="col-sm-3 control-label">Password:</label>
			          <div class="col-sm-6">
			            <input type="password" name="password" id="password" class="form-control"/>
			          </div>
			          <small class="error col-sm-3"><?php if(!empty($errorPassword)) echo $errorPassword; ?></small>
			        </div>

			        <div class="form-group">
			          <label for="retype_password" class="col-sm-3 control-label">Retype password:</label>
			          <div class="col-sm-6">
			            <input type="password" name="retype_password" id="retype_password" class="form-control"/>
			          </div>
			          <small class="error col-sm-3"><?php if(count($errorRetypePassword) > 0) echo $errorRetypePassword; ?></small>
			        </div>

			        <div class="form-group">
			          <label for="contact_number" class="col-sm-3 control-label">Contact number:</label>
			          <div class="col-sm-6">
			            <input type="text" name="contact_number" id="contact_number" class="form-control" value="<?php echo escape($rows[0]->contact_number); ?>"/>
			          </div>
			          <small class="error col-sm-3"><?php if(count($errorContactNumber) > 0) echo $errorContactNumber; ?></small>
			        </div>

			        <div class="form-group">
			          <label for="address" class="col-sm-3 control-label">Address:</label>
			          <div class="col-sm-6">
			            <textarea name="address" id="address" class="form-control"></textarea>
			            <script type="text/javascript">
			            	document.getElementById('address').value = "<?php echo escape($rows[0]->address); ?>";
			            </script>
			          </div>
			          <small class="error col-sm-3"><?php if(count($errorAddress) > 0) echo $errorAddress; ?></small>

			        </div>

			        <div class="form-group">
			          <label for="designation" class="col-sm-3 control-label">Designation:</label>
			          <div class="col-sm-6">
			            	<select name="designation" id="designation" class="form-control">
								<option value="">Choose a designation</option>            		
								<?php 
									foreach ($data as $value) {
								?>
								<option value="<?php echo $value->id; ?>"><?php echo $value->designation; ?></option>

								<?php } ?>

							</select>
			          </div>
			        </div>

			        <div class="form-group">
			          <label for="joining_date" class="col-sm-3 control-label">Joining date:</label>
			          <div class="col-sm-6">
			            <input type="date" name="joining_date" id="joining_date" class="form-control" value="<?php echo escape($rows[0]->joined); ?>">
			          </div>
			        </div>
			        <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			        <div class="form-group">
			          <div class="col-sm-3"></div>
			          <div class="col-sm-6">
			            <input type="submit" class="btn btn-info btn-block" value="Submit">
			          </div>
			          <div class="col-sm-3"></div>
			        </div>
			      </div><!-- /.box-body -->
			      <div class="box-footer">

			      </div><!-- /.box-footer -->
			    </form>
			  </div><!-- /.box -->
			</div>


		</div>
	 
	</section><!-- /.content -->

</div><!-- /.content-wrapper -->

<?php

}

?>