<?php

if(!$user->isLoggedIn()){
	Redirect::to('login');
}

require '3rdparty/PHPMailer/PHPMailerAutoload.php';
$mail = new PHPMailer;
$db = DB::getInstance();

$userid = '';
$current_password = '';
$new_password = '';
$retype_password = '';

$errorCurrentPassword = '';
$errorNewPassword = '';
$errorRetypePassword = '';

$db->get('settings', array('1', '=', '1'));
$mailSetting = $db->first();
$mail_type = $mailSetting->mail_type;
$db->get('email_templates', array('template_name', '=', 'Password reset'));
$temData = $db->first();

if (isset($_POST['update'])) {
	if(Token::check(Input::get('token'))){

		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'current_password' => array(
				'required' => true
			),
			'new_password' => array(
				'required' => true,
				'min' => 8
			),
			'retype_password' => array(
				'required' => true,
				'matches' => 'new_password'
			)
		));	
			
		if(!preg_match('/^.*(?=.*[a-z])(?=.*\d)(?=.*[A-Z]).*$/', Input::get('new_password'))) $errors['New password'] = 'Password must contain uppercase, lowercase letter and number.';
			
		$db->get('users', array('id', '=', $user->data()->id));
		$result = $db->first();
		$ext_pass = $result->password;
		$ext_salt = $result->salt;
		$cur_pass = Hash::make(Input::get('current_password'), $ext_salt);		

		if($ext_pass !== $cur_pass){
			$errorCurrentPassword = 'Current password is incorrect!';
		}

		if($validation->passed() && empty($errorCurrentPassword) && empty($errors['New password'])){

			$salt = Hash::salt(32);			
			$new_password = Input::get('new_password');
			$fullname = $user->data()->firstname . ' ' . $user->data()->lastname;
			$url = $_SERVER['HTTP_HOST'];
			$body = str_replace('$signature', $mailSetting->email_signature, str_replace('$password', $new_password, str_replace('$username', $user->data()->username, str_replace('$url', $url, str_replace('$name', $fullname, $temData->email_body)))));

			try{
				$db->update('users', $user->data()->id, array(
					'password' => Hash::make(Input::get('new_password'), $salt),
					'salt' => $salt,
					'user_id_updated' => $user->data()->id,
					'time_updated' => date('Y-m-d H:i:s')
				));


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
						$mail->addAddress($user->data()->email, $fullname);
						$mail->isHTML(true);  
						//Set the subject line
						$mail->Subject = $temData->subject;
						// Body
						$mail->Body = $body;
		
						//send the message, check for errors
						if(!$mail->send()){
							echo 'Mailer error: ' . $mail->ErrorInfo;
					}else{
						Session::flash('success', 'Your password has been changed successfully.');
						Redirect::to('home');
					}
		
		     		}else{
			     		//Set who the message is to be sent from
						$mail->setFrom($temData->email_from, $temData->name_from);
						
						//Set an alternative reply-to address
						$mail->addReplyTo($temData->email_from, $temData->name_from);
						
						//Set who the message is to be sent to
						$mail->addAddress($user->data()->email, $fullname);
						//Set the subject line
						$mail->Subject = $temData->subject;
						// Body
						$mail->msgHTML($body);
		
						//send the message, check for errors
						if(!$mail->send()){
							echo 'Mailer error: ' . $mail->ErrorInfo;
					}else{
						Session::flash('success', 'Your password has been changed successfully.');
						Redirect::to('home');					
					}
				}

			} catch(Exception $e){
				die('There was a problem updating your password.');
			}

		} else {
			foreach ($validation->errors() as $error) {
				foreach ($error as $key => $value) {
					$errors[$key] = $value;
				}
			}

			if(!empty($errors['Current password'])) $errorCurrentPassword = $errors['Current password'];
			if(!empty($errors['New password'])) $errorNewPassword = $errors['New password'];
			if(!empty($errors['Retype password'])) $errorRetypePassword = $errors['Retype password'];
		}
	}
}


?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1>
	    Change your password
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
			  <!-- change password Form -->
			  <div class="box box-info">
			    <!-- form start -->
				<form class="form-horizontal" action="" method="post">
					<!--<input type="hidden" name="userid" value="<?php echo $user->data()->id; ?>">-->
				  	<div class="box-body">
				  		<div class="col-sm-10">
						    <div class="form-group">
						      <label for="current_password" class="col-sm-3 control-label">Current password:</label>
						      <div class="col-sm-6">
						        <input type="password" name="current_password" id="current_password" class="form-control" />
						      </div>
						      <small class="error col-sm-3"><?php echo $errorCurrentPassword; ?></small>     
						    </div>
						    <div class="form-group">
						      <label for="new_password" class="col-sm-3 control-label">New password:</label>
						      <div class="col-sm-6">
						        <input type="password" name="new_password" id="new_password" class="form-control" />
						      </div>
						      <small class="error col-sm-3"><?php echo $errorNewPassword; ?></small>     
						    </div>

					        <div class="form-group">
					          <label for="retype_password" class="col-sm-3 control-label">Retype password:</label>
					          <div class="col-sm-6">
					            <input type="password" name="retype_password" id="retype_password" class="form-control" />
					          </div>
					          <small class="error col-sm-3"><?php echo $errorRetypePassword; ?></small>
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
				  	</div>
				</form>
			  </div><!-- /.box -->
			</div>


		</div>
	 
	</section><!-- /.content -->

</div><!-- /.content-wrapper -->