<?php

$user = new User();

require '3rdparty/PHPMailer/PHPMailerAutoload.php';
$mail = new PHPMailer;

if(!$user->isLoggedIn() || $user->data()->isAdmin != 1){
	Redirect::to('login');
}
	$errors = [];
	$success = '';
	$designationValue = '';
	$errorFirstName = '';
	$errorLastName = '';
	$errorUsername = '';
	$errorEmail = '';
	$errorPassword = '';
	$errorRetypePassword = '';
	$errorContactNumber = '';
	$errorAddress = '';
	$errorDesignation = '';	
	$errorJoiningDate = '';

	$db = DB::getInstance();

	$data = $db->get('designation', array('1', '=', '1'))->results();

	$db->get('settings', array('1', '=', '1'));
	$mailSetting = $db->first();
	$mail_type = $mailSetting->mail_type;

	$db->get('email_templates', array('template_name', '=', 'Welcome'));
	$temData = $db->first();

	// $body = $temData->email_body;
	// var_dump($body);

	if(Input::exists()){
		if(Token::check(Input::get('token'))){

			$validate = new Validate();
			$validation = $validate->check($_POST, array(
				'first_name' => array(
					'required' => true,
					'min' => 2,
					'max' => 20
				),
				'last_name' =>array(
					'required' => true,
					'min' => 2,
					'max' => 20
				),
				'username' => array(
					'required' => true,
					'min' => 4,
					'max' => 20,
					'unique' => 'users'
				),			
				'email' => array(
					'required' => true,
					'email' =>  true,
					'unique' => 'users'
				),
				'password' => array(
					'required' => true,
					'min' => 8
				),
				'retype_password' => array(
					'required' => true,
					'matches' => 'password'
				),
				'contact_number' => array(
					'required' => true,
					'min' => 7,
					'max' => 13,
					'unique' => 'users'
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
			
			
			if(!preg_match('/^.*(?=.*[a-z])(?=.*\d)(?=.*[A-Z]).*$/', Input::get('password'))) $errors['Password'] = 'Password must contain uppercase, lowercase letter and number.';
			
			if(Input::get('designation') == 'Chairman' || Input::get('designation') == 'MD') $getDesignation = Input::get('designation');
			//var_dump($getDesignation);
			if(!empty($getDesignation)){
				$getData = $db->get('users', array('designation', '=', $getDesignation))->first();
				
				//$designationUserId = $getData->id;
				$designationValue = $getData->designation;

				if($designationValue == 'Chairman') {
					$errorDesignation = 'You have to delete existing chairman first.';
				} elseif ($designationValue == 'Managing Director') {
					$errorDesignation = 'You have to delete existing Managing Director first.';
				} elseif ($designationValue == 'MD') {
					$errorDesignation = 'You have to delete existing MD first.';
				}
		
			}

			if($validation->passed() && empty($errorDesignation) && empty($errors['Password'])){
				$user = new User();

				$salt = Hash::salt(32);

				$fullname = Input::get('first_name') . Input::get('last_name');
				$email = Input::get('email');
				$username = Input::get('username');				
				$password = Input::get('password');
				$url = $_SERVER['HTTP_HOST'];
				$body = str_replace('$name', $fullname, str_replace('$signature', $mailSetting->email_signature, str_replace('$password', $password, str_replace('$url', $url, str_replace('$username', $username, $temData->email_body)))));

				try{
					$user->create(array(
						'firstname' => Input::get('first_name'),
						'lastname' => Input::get('last_name'),
						'username' => $username,			
						'email' => $email,			
						'password' => Hash::make(Input::get('password'), $salt),
						'salt' => $salt,
						'contact_number' => Input::get('contact_number'),
						'address' => Input::get('address'),
						'designation' => Input::get('designation'),
						'joined' => Input::get('joining_date'),
						'isAdmin' => Input::get('role'),
						'user_id_inserted' => $user->data()->id,
						'time_inserted' => date('Y-m-d H:i:s')
					));

// Send email to user...
			
			     		if($mail_type === 'SMTP'){
							//Tell PHPMailer to use SMTP
							$mail->isSMTP();
							//Enable SMTP debugging
							// 0 = off (for production use)
							// 1 = client messages
							// 2 = client and server messages
							$mail->SMTPDebug = 0;
							//Ask for HTML-friendly debug output
							$mail->Debugoutput = 'html';
							//Set the hostname of the mail server
							$mail->Host = $mailSetting->smtp_host;
							//Set the SMTP port number - likely to be 25, 465 or 587
							$mail->Port = $mailSetting->smtp_port;
							//Whether to use SMTP authentication
							$mail->SMTPAuth = true;
							//Username to use for SMTP authentication
							$mail->Username = $mailSetting->smtp_username;
							//Password to use for SMTP authentication
							$mail->Password = $mailSetting->smtp_password;
							//Set who the message is to be sent from
							$mail->setFrom($temData->email_from, $temData->name_from);
							//Set an alternative reply-to address
							$mail->addReplyTo($temData->email_from, $temData->name_from);
			
							//Set who the message is to be sent to
							$mail->addAddress($email, $fullname);
							$mail->isHTML(true);  
							//Set the subject line
							$mail->Subject = $temData->subject;
							// Body
							$mail->Body = $body;
			
							//send the message, check for errors
							if(!$mail->send()){
								echo 'Mailer error: ' . $mail->ErrorInfo;
						}else{
							Session::flash('success', 'A new employee has been added successfully!');
							Redirect::to('employee_list');
						}
			
			     		}else{
				     		//Set who the message is to be sent from
							$mail->setFrom($temData->email_from, $temData->name_from);
							
							//Set an alternative reply-to address
							$mail->addReplyTo($temData->email_from, $temData->name_from);
							
							//Set who the message is to be sent to
							$mail->addAddress($email, $fullname);
							//Set the subject line
							$mail->Subject = $temData->subject;
							// Body
							$mail->msgHTML($body);
			
							//send the message, check for errors
							if(!$mail->send()){
								echo 'Mailer error: ' . $mail->ErrorInfo;
						}else{
							Session::flash('success', 'A new employee has been added successfully!');
							Redirect::to('employee_list');					
						}
					}
						
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
				if(!empty($errors['Address'])) $errorAddress = $errors['Address'];
				if(!empty($errors['Designation'])) $errorDesignation = $errors['Designation'];
				if(!empty($errors['Joining date'])) $errorJoiningDate = $errors['Joining date'];
		          
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
	    <li class="active"><?php echo $title; ?></li>
	  </ol>
	</section>

	<!-- Main content -->
	<section class="content">

		<div class="row">
			<div class="col-md-12">
			  <!-- Register Form -->
			  <div class="box box-info">
			  	<div class="box-body">
			        <!-- form start -->
				    <form class="form-horizontal" action="" method="post">
				      <div class="box-body col-sm-10">

				        <div class="form-group">
				          <label for="first_name" class="col-sm-3 control-label">First name:</label>
				          <div class="col-sm-6">
				            <input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo Input::get('first_name'); ?>" placeholder="First name"/>
				          </div>
				          <small class="error col-sm-3"><?php if(count($errorFirstName) > 0 ) echo $errorFirstName; ?></small>
				         
				        </div>

				        <div class="form-group">
				          <label for="last_name" class="col-sm-3 control-label">Last name:</label>
				          <div class="col-sm-6">
				            <input type="text" name="last_name" id="last_name" class="form-control" value="<?php echo Input::get('last_name'); ?>" placeholder="Last name"/>
				          </div>
				          <small class="error col-sm-3"><?php if(count($errorLastName) > 0) echo $errorLastName; ?></small>
				        </div>

				        <div class="form-group">
				          <label for="username" class="col-sm-3 control-label">Username:</label>
				          <div class="col-sm-6">
				            <input type="text" name="username" id="username" class="form-control" value="<?php echo Input::get('username'); ?>" placeholder="Username"/>
				          </div>
				          <small class="error col-sm-3"><?php if(count($errorUsername) > 0) echo $errorUsername; ?></small>
				        </div>

				        <div class="form-group">
				          <label for="email" class="col-sm-3 control-label">Email:</label>
				          <div class="col-sm-6">
				            <input type="email" name="email" id="email" class="form-control" value="<?php echo Input::get('email'); ?>" placeholder="john@example.com"/>
				          </div>
				          <small class="error col-sm-3"><?php if(count($errorEmail) > 0) echo $errorEmail; ?></small>
				        </div>

				        <div class="form-group">
				          <label for="password" class="col-sm-3 control-label">Password:</label>
				          <div class="col-sm-6">
				            <input type="password" name="password" id="password" class="form-control" placeholder="********"/>
				          </div>
				          <small class="error col-sm-3"><?php if(!empty($errorPassword)) echo $errorPassword; ?></small>
				        </div>

				        <div class="form-group">
				          <label for="retype_password" class="col-sm-3 control-label">Retype password:</label>
				          <div class="col-sm-6">
				            <input type="password" name="retype_password" id="retype_password" class="form-control" placeholder="********"/>
				          </div>
				          <small class="error col-sm-3"><?php if(count($errorRetypePassword) > 0) echo $errorRetypePassword; ?></small>
				        </div>

				        <div class="form-group">
				          <label for="contact_number" class="col-sm-3 control-label">Contact number:</label>
				          <div class="col-sm-6">
				            <input type="text" name="contact_number" id="contact_number" class="form-control" value="<?php echo Input::get('contact_number'); ?>" placeholder="Contact number"/>
				          </div>
				          <small class="error col-sm-3"><?php if(count($errorContactNumber) > 0) echo $errorContactNumber; ?></small>
				        </div>

				        <div class="form-group">
				          <label for="address" class="col-sm-3 control-label">Address:</label>
				          <div class="col-sm-6">
				            <textarea name="address" id="address" class="form-control" placeholder="Address"></textarea>
				          </div>
				          <small class="error col-sm-3"><?php if(count($errorAddress) > 0) echo $errorAddress; ?></small>
				        </div>
				            <script type="text/javascript">
				            	document.getElementById('address').value = "<?php echo Input::get('address'); ?>";
				            </script>

				        <div class="form-group">
				          <label for="designation" class="col-sm-3 control-label">Designation:</label>
				          <div class="col-sm-6">
				            	<select name="designation" id="designation" class="form-control">
									<option value="">Choose a designation</option>		            		
									<?php 
										foreach ($data as $value) {
									?>
									<option value="<?php echo $value->designation; ?>"><?php echo $value->designation; ?></option>

									<?php } ?>

								</select>
				          </div>
				          <small class="error col-sm-3"><?php if(count($errorDesignation) > 0) echo $errorDesignation; ?></small>
				        </div>

				        <div class="form-group">
				          <label for="role" class="col-sm-3 control-label">Role:</label>
				          <div class="col-sm-6">
		           			<label class="radio-inline">
						  <input type="radio" name="role" value="0" id="role" checked>User
						</label>				          
		           			<label class="radio-inline">
						  <input type="radio" name="role" value="1" id="role">Admin
						</label>
					  </div>						
				        </div>
				        <div class="form-group">
				          <label for="datepicker" class="col-sm-3 control-label">Joining date:</label>
				          <div class="col-sm-6">
				            <input type="date" name="joining_date" id="datepicker" class="form-control" value="<?php echo Input::get('joining_date'); ?>">
				          </div>
				          <small class="error col-sm-3"><?php if(count($errorJoiningDate) > 0) echo $errorJoiningDate; ?></small>
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
	 				  <div class="col-sm-2"></div>

				    </form>
			    </div>
			  </div><!-- /.box -->
			</div>


		</div>
	 
	</section><!-- /.content -->

</div><!-- /.content-wrapper -->