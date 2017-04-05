<?php
require_once 'core/init.php';
if(!$user->isLoggedIn() || $user->data()->isAdmin != 1){
	Redirect::to('home');
}

$db = DB::getInstance();
$validate = new Validate();

$success = '';
$error = '';
$settingsId = '';

$errorMailType = '';
$errorMailEncoding = '';
$errorSmtpSslType = '';
$errorSmtpPort = '';
$errorSmtpHost = '';
$errorSmtpUsername = '';
$errorSmtpPassword = '';
$errorEmailSignature = '';

$db->get('settings', array('1', '=', '1'));
$getResult = $db->first();
$insertId = $getResult->id;


if(isset($_POST['submit']) && Input::get('mail_type') === 'SMTP'){
	if(Token::check(Input::get('token'))){

		$validation = $validate->check($_POST, array(
			'smtp_port' => array(
				'required' => true
			),
			'smtp_host' => array(
				'required' => true
			),
			'smtp_username' => array(
				'required' => true
			),
			'smtp_password' => array(
				'required' => true
			),
			'email_signature' => array(
				'required' => true
			)
		));

		if($validation->passed()){

			try{
				$db->update('settings', $insertId, array(
					'mail_type' => Input::get('mail_type'),
					'mail_encoding' => Input::get('mail_encoding'),
					'ssl_type' => Input::get('ssl_type'),
					'smtp_port' => Input::get('smtp_port'),
					'smtp_host' => Input::get('smtp_host'),
					'smtp_username' => Input::get('smtp_username'),
					'smtp_password' => Input::get('smtp_password'),
					'email_signature' => Input::get('email_signature'),
					'user_id_updated' => $user->data()->id,
					'time_updated' => date('Y-m-d H:i:s')
				));

				Session::flash('success', 'Mail settings have been saved successfully!');
				Redirect::to('mail_settings');

			} catch (Exception $e){
				die('There was a problem saving the mail settings.');
			}
		} else {
			foreach ($validation->errors() as $error) {
				foreach ($error as $key => $value) {
					$errors[$key] = $value;
				}
			}
			if(!empty($errors['Smtp port'])) $errorSmtpPort = $errors['Smtp port'];
			if(!empty($errors['Smtp host'])) $errorSmtpHost = $errors['Smtp host'];
			if(!empty($errors['Smtp username'])) $errorSmtpUsername = $errors['Smtp username'];
			if(!empty($errors['Smtp password'])) $errorSmtpPassword = $errors['Smtp password'];
			if(!empty($errors['Email signature'])) $errorEmailSignature = $errors['Email signature'];

		}
	}
}elseif(isset($_POST['submit']) && Input::get('mail_type') === 'PHPMail'){
	try{
		$db->update('settings', $insertId, array(
			'mail_type' => Input::get('mail_type'),
			'mail_encoding' => '',
			'ssl_type' => '',
			'smtp_port' => 0,
			'smtp_host' => '',
			'smtp_username' => '',
			'smtp_password' => '',
			'email_signature' => '',
			'user_id_updated' => $user->data()->id,
			'time_updated' => date('Y-m-d H:i:s')

		));
		Session::flash('success', 'Mail settings have been saved successfully!');
		Redirect::to('mail_settings');

	}catch(Exception $e){
		die('There is a problem saving the mail settings.');
	}
}

if(isset($_POST['edit'])){
	$settings_id = Input::get('settings_id');
	$editData = $db->get('settings', array('id', '=', $settings_id))->first();
	$settingsId = $editData->id;

}elseif(isset($_POST['update']) && Input::get('mail_type') === 'SMTP'){
	if(Token::check(Input::get('token'))){

		$validation = $validate->check($_POST, array(
			'smtp_port' => array(
				'required' => true
			),
			'smtp_host' => array(
				'required' => true
			),
			'smtp_username' => array(
				'required' => true
			),
			'smtp_password' => array(
				'required' => true
			),
			'email_signature' => array(
				'required' => true
			)
		));

		if($validation->passed()){
			$id = Input::get('settingsId');
			try{
				$db->update('settings', $id, array(
					'mail_type' => Input::get('mail_type'),
					'mail_encoding' => Input::get('mail_encoding'),
					'ssl_type' => Input::get('ssl_type'),
					'smtp_port' => Input::get('smtp_port'),
					'smtp_host' => Input::get('smtp_host'),
					'smtp_username' => Input::get('smtp_username'),
					'smtp_password' => Input::get('smtp_password'),
					'email_signature' => Input::get('email_signature'),
					'user_id_updated' => $user->data()->id,
					'time_updated' => date('Y-m-d H:i:s')
				));

				Session::flash('success', 'Mail settings have been saved successfully!');
				Redirect::to('mail_settings');

			} catch (Exception $e){
				die('There was a problem saving the mail settings.');
			}
		} else {
			foreach ($validation->errors() as $error) {
				foreach ($error as $key => $value) {
					$errors[$key] = $value;
				}
			}
			if(!empty($errors['Smtp port'])) $errorSmtpPort = $errors['Smtp port'];
			if(!empty($errors['Smtp host'])) $errorSmtpHost = $errors['Smtp host'];
			if(!empty($errors['Smtp username'])) $errorSmtpUsername = $errors['Smtp username'];
			if(!empty($errors['Smtp password'])) $errorSmtpPassword = $errors['Smtp password'];
			if(!empty($errors['Email signature'])) $errorEmailSignature = $errors['Email signature'];

		}
	}
}elseif(isset($_POST['update']) && Input::get('mail_type') === 'PHPMail'){
	try{
		$db->update('settings', $insertId, array(
			'mail_type' => Input::get('mail_type'),
			'mail_encoding' => '',
			'ssl_type' => '',
			'smtp_port' => 0,
			'smtp_host' => '',
			'smtp_username' => '',
			'smtp_password' => '',
			'email_signature' => Input::get('email_signature'),
			'user_id_updated' => $user->data()->id,
			'time_updated' => date('Y-m-d H:i:s')

		));
		Session::flash('success', 'Mail settings have been saved successfully!');
		Redirect::to('mail_settings');		

	}catch(Exception $e){
		die('There is a problem saving the mail settings.');
	}
}

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
	    Mail settings
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
			  <!-- Horizontal Form -->
			  <div class="box box-info">
			  	<div class="box-body">

					<div class="col-sm-12">
						<?php if(empty($getResult->mail_type)){ ?>

						<form action="" method="post" class="form-horizontal">
							<div class="row">

								<div class="form-group">
									<label for="mail_type" class="col-sm-3 control-label">Mail Type: </label>
									<div class="col-sm-5">
						            	<select name="mail_type" id="mail_type" class="form-control" onchange="if(this.value=='SMTP'){document.getElementById('showForm').style.display = '';}else{document.getElementById('showForm').style.display = 'none'}">
											<option value="SMTP">SMTP</option>			            		
											<option value="PHPMail">PHP Mail</option>						
										</select>
									</div>
								</div>
								<span id="showForm">
									<div class="form-group">
										<label for="mail_encoding" class="col-sm-3 control-label">Mail Encoding: </label>
										<div class="col-sm-5">
							            	<select name="mail_encoding" id="mail_encoding" class="form-control">
												<option value="8bit">8bit</option>	
												<option value="7bit">7bit</option>
												<option value="binary">binary</option>
												<option value="base64">base64</option>
												<option value="quoted-printable">quoted-printable</option>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label for="ssl_type" class="col-sm-3 control-label">SMTP SSL Type: </label>				
										<div class="col-sm-5">
									    <label class="radio-inline">
									      <input type="radio" name="ssl_type" value="none" onClick="document.getElementById('smtp_port').value=25">None
									    </label>
									    <label class="radio-inline">
									      <input type="radio" name="ssl_type" value="ssl" onClick="document.getElementById('smtp_port').value=465">SSL
									    </label>
									    <label class="radio-inline">
									      <input type="radio" name="ssl_type" value="tls" onClick="document.getElementById('smtp_port').value=587" checked>TLS
									    </label>
										</div>
									</div>

									<div class="form-group">
										<label for="smtp_port" class="col-sm-3 control-label">SMTP Port: </label>
										<div class="col-sm-5">
				           					<input type="text" name="smtp_port" id="smtp_port" class="form-control" value="587" placeholder="SMTP Port"/>
				           				</div>
										<small class="error col-sm-4"><?php if(!empty($errorSmtpPort)) echo $errorSmtpPort; ?></small>
									</div>
									<div class="form-group">
										<label for="smtp_host" class="col-sm-3 control-label">SMTP Host: </label>
										<div class="col-sm-5">
				           					<input type="text" name="smtp_host" id="smtp_host" class="form-control" value="" placeholder="SMTP Host"/>
				           				</div>
										<small class="error col-sm-4"><?php if(!empty($errorSmtpHost)) echo $errorSmtpHost; ?></small>
									</div>
									<div class="form-group">
										<label for="smtp_username" class="col-sm-3 control-label">SMTP Username: </label>
										<div class="col-sm-5">
				           					<input type="text" name="smtp_username" id="smtp_username" class="form-control" value="" placeholder="SMTP Username"/>
				           				</div>
										<small class="error col-sm-4"><?php if(!empty($errorSmtpUsername)) echo $errorSmtpUsername; ?></small>
									</div>
									<div class="form-group">
										<label for="smtp_password" class="col-sm-3 control-label">SMTP Password: </label>
										<div class="col-sm-5">
				           					<input type="password" name="smtp_password" id="smtp_password" class="form-control" value="" placeholder="SMTP Password"/>
				           				</div>
										<small class="error col-sm-4"><?php if(!empty($errorSmtpPassword)) echo $errorSmtpPassword; ?></small>
									</div>

							        <div class="form-group">
							          <label for="email_signature" class="col-sm-3 control-label">Email Signature:</label>
							          <div class="col-sm-5">
							            <textarea name="email_signature" id="email_signature" class="form-control" rows="5" placeholder="Email signature"></textarea>
							          </div>
							          <small class="error col-sm-3"><?php if(!empty($errorEmailSignature)) echo $errorEmailSignature; ?></small>
							        </div>
						        </span>
						        <div class="form-group">
						        	<div class="col-sm-3"></div>
						        	<div class="col-sm-6">
										<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
										<input type="submit" name="submit" value="Save" class="btn btn-success">
									</div>
			         				<div class="col-sm-3"></div>
								</div>

							</div>
						</form>
<!--End Insert region-->

<!--Start edit region-->
						<?php }else{ 
							$db->get('settings', array('1', '=', '1'));
							$getData = $db->first();
						?>

						<form action="" method="post" class="form-horizontal">
							<input type="hidden" name="settingsId" value="<?php echo $getData->id; ?>">					
							<div class="row">
								<div class="form-group">
									<label for="mail_type" class="col-sm-3 control-label">Mail Type: </label>
									<div class="col-sm-5">
						            	<select name="mail_type" id="mail_type" class="form-control" onchange="if(this.value=='PHPMail'){document.getElementById('showForm').style.display = 'none';}else{document.getElementById('showForm').style.display = '';}">
											<option value="SMTP" <?php if($getData->mail_type == 'SMTP') echo 'selected';?>>SMTP</option>			            		
											<option value="PHPMail" <?php if($getData->mail_type == 'PHPMail') echo 'selected';?>>PHP Mail</option>						
										</select>
									</div>
								</div>
								<span id="showForm" style="display: <?php if($getData->mail_type == 'PHPMail') echo 'none';?>">
									<div class="form-group">
										<label for="mail_encoding" class="col-sm-3 control-label">Mail Encoding: </label>
										<div class="col-sm-5">
							            	<select name="mail_encoding" id="mail_encoding" class="form-control">
												<option value="8bit" <?php if($getData->mail_encoding == '8bit') echo 'selected'; ?>>8bit</option>	
												<option value="7bit" <?php if($getData->mail_encoding == '7bit') echo 'selected'; ?>>7bit</option>
												<option value="binary" <?php if($getData->mail_encoding == 'binary') echo 'selected'; ?>>binary</option>
												<option value="base64" <?php if($getData->mail_encoding == 'base64') echo 'selected'; ?>>base64</option>
												<option value="quoted-printable" <?php if($getData->mail_encoding == 'quoted-printable') echo 'selected'; ?>>quoted-printable</option>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label for="ssl_type" class="col-sm-3 control-label">SMTP SSL Type: </label>				
										<div class="col-sm-5">
									    <label class="radio-inline">
									      <input type="radio" name="ssl_type" value="none" onClick="document.getElementById('smtp_port').value=25" <?php if($getData->ssl_type == 'none') echo 'checked';?>>None
									    </label>
									    <label class="radio-inline">
									      <input type="radio" name="ssl_type" value="ssl" onClick="document.getElementById('smtp_port').value=465" <?php if($getData->ssl_type == 'ssl') echo 'checked';?>>SSL
									    </label>
									    <label class="radio-inline">
									      <input type="radio" name="ssl_type" value="tls" onClick="document.getElementById('smtp_port').value=587" <?php if($getData->ssl_type == 'tls') echo 'checked';?>>TLS
									    </label>
										</div>
									</div>

									<div class="form-group">
										<label for="smtp_port" class="col-sm-3 control-label">SMTP Port: </label>
										<div class="col-sm-5">
				           					<input type="text" name="smtp_port" id="smtp_port" class="form-control" value="<?php echo $getData->smtp_port; ?>" placeholder="SMTP Port"/>
				           				</div>
										<small class="error col-sm-4"><?php if(!empty($errorSmtpPort)) echo $errorSmtpPort; ?></small>
									</div>
									<div class="form-group">
										<label for="smtp_host" class="col-sm-3 control-label">SMTP Host: </label>
										<div class="col-sm-5">
				           					<input type="text" name="smtp_host" id="smtp_host" class="form-control" value="<?php echo $getData->smtp_host; ?>" placeholder="SMTP Host"/>
				           				</div>
										<small class="error col-sm-4"><?php if(!empty($errorSmtpHost)) echo $errorSmtpHost; ?></small>
									</div>
									<div class="form-group">
										<label for="smtp_username" class="col-sm-3 control-label">SMTP Username: </label>
										<div class="col-sm-5">
				           					<input type="text" name="smtp_username" id="smtp_username" class="form-control" value="<?php echo $getData->smtp_username; ?>" placeholder="SMTP Username"/>
				           				</div>
										<small class="error col-sm-4"><?php if(!empty($errorSmtpUsername)) echo $errorSmtpUsername; ?></small>
									</div>
									<div class="form-group">
										<label for="smtp_password" class="col-sm-3 control-label">SMTP Password: </label>
										<div class="col-sm-5">
				           					<input type="password" name="smtp_password" id="smtp_password" class="form-control" value="" placeholder="SMTP Password"/>
				           				</div>
										<small class="error col-sm-4"><?php if(!empty($errorSmtpPassword)) echo $errorSmtpPassword; ?></small>
									</div>
								</span>
							        <div class="form-group">
							          <label for="email_signature" class="col-sm-3 control-label">Email Signature:</label>
							          <div class="col-sm-5">
							            <textarea name="email_signature" id="signature" class="form-control" rows="5" placeholder="Email signature"></textarea>
							            <script type="text/javascript">
							            	document.getElementById('signature').value = "<?php echo $getData->email_signature; ?>";
							            </script>

							          </div>
							          <small class="error col-sm-3"><?php if(!empty($errorEmailSignature)) echo $errorEmailSignature; ?></small>
							        </div>
						        
						        <div class="form-group">

						        	<div class="col-sm-3"></div>
						        	<div class="col-sm-6">
										<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
										<input type="submit" name="update" value="Save" class="btn btn-success">
									</div>
			         				 <div class="col-sm-3"></div>
								</div>

							</div>
						</form>
<!--End edit region -->						
						<?php } ?>

			  		</div>
			  	</div>
			  </div><!-- /.box -->
			</div>


		</div>
	 
	</section><!-- /.content -->

</div><!-- /.content-wrapper -->