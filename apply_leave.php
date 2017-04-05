<?php
if(!$user->isLoggedIn()){
	Redirect::to('home');
}

require '3rdparty/PHPMailer/PHPMailerAutoload.php';
$mail = new PHPMailer;
$db = DB::getInstance();
$validate = new Validate();

$success = '';
$leaveid= '';
$errorType = '';
$errorLeaveFrom = '';
$errorLeaveTo = '';
$errorReason = '';

$db->get('settings', array('1', '=', '1'));
$mailSetting = $db->first();
$mail_type = $mailSetting->mail_type;

$db->get('email_templates', array('template_name', '=', 'Leave grant/reject'));
$temData = $db->first();
 //var_dump($temData);
 
if(isset($_POST['submit'])){
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
				$subject = 'Leave request';
				$body = 'A new leave request from ' . $user->data()->firstname . ' ' . $user->data()->lastname . ' for date: ' . date('jS M, Y', strtotime(Input::get('leave_from'))) . ' to ' . date('jS M, Y', strtotime(Input::get('leave_to')));
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
					$mail->setFrom($user->data()->email, $user->data()->firstname . ' ' . $user->data()->lastname);
					//Set an alternative reply-to address
					$mail->addReplyTo($user->data()->email, $user->data()->firstname . ' ' . $user->data()->lastname);
	
					//Set who the message is to be sent to
					$mail->addAddress($temData->email_from, $temData->name_from);
					$mail->isHTML(true);  
					//Set the subject line
					$mail->Subject = $subject;
					// Body
					$mail->Body = $body;
	
					//send the message, check for errors
					if(!$mail->send()){
						echo 'Mailer error: ' . $mail->ErrorInfo;
					}else{
						Session::flash('success', 'Leave has been sent successfully!');
						Redirect::to('leaves');
					}
	
	     			}else{
		     		//Set who the message is to be sent from
					$mail->setFrom($user->data()->email, $user->data()->firstname . ' ' . $user->data()->lastname);
					
					//Set an alternative reply-to address
					$mail->addReplyTo($user->data()->email, $user->data()->firstname . ' ' . $user->data()->lastname);
					
					//Set who the message is to be sent to
					$mail->addAddress($temData->email_from, $temData->name_from);
					//Set the subject line
					$mail->Subject = $subject;
					// Body
					$mail->msgHTML($body);
	
					//send the message, check for errors
					if(!$mail->send()){
						echo 'Mailer error: ' . $mail->ErrorInfo;
					}else{
						Session::flash('success', 'Leave application has been sent successfully!');			
						Redirect::to('leaves');					
					}
				}				

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

<script type="text/javascript">
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
	    Apply leave
	  </h1>
	  
	  <?php if(!empty($success)){ ?>
	  <div class="alert alert-success fade in text-center col-sm-offset-3" style="padding: 4px; width: 40%; margin-top: -30px; margin-bottom: 0;">
	  	<?php echo $success; ?>
	  	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
	  </div>
	  <?php } ?>
	  
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
			      <div class="box-body col-sm-10">
			        <div class="form-group">
			          <label for="type" class="col-sm-3 control-label">Type:</label>
			          <div class="col-sm-6">
			            	<select name="type" id="type" class="form-control">
								<option value="">Choose a type</option>		            		
								<?php 
									foreach ($typeData as $typeValue) {
								?>
								<option value="<?php echo $typeValue->id; ?>"><?php echo $typeValue->type; ?></option>

								<?php } ?>
							</select>
			          </div>			          
			          <small class="error col-sm-3"><?php if(!empty($errorType)) echo $errorType; ?></small>
			        </div>
			        <div class="form-group">
			          <label for="datepicker" class="col-sm-3 control-label">Leave from:</label>
			          <div class="col-sm-6">
			            <input type="text" name="leave_from" id="datepicker" class="form-control" value="" placeholder="Leave from"/>
			          </div>
			          <small class="error col-sm-3"><?php if(!empty($errorLeaveFrom)) echo $errorLeaveFrom; ?></small>
			        </div>
			        <div class="form-group">
			          <label for="datepickerto" class="col-sm-3 control-label">Leave to:</label>
			          <div class="col-sm-6">
			            <input type="text" name="leave_to" id="datepickerto" class="form-control" value="" placeholder="Leave to"/>
			          </div>
			          <small class="error col-sm-3"><?php if(!empty($errorLeaveTo)) echo $errorLeaveTo; ?></small>
			        </div>
			        <div class="form-group">
			          <label for="reason" class="col-sm-3 control-label">Reason:</label>
			          <div class="col-sm-6">
			            <textarea name="reason" id="reason" class="form-control" rows="5" placeholder="Leave reason"></textarea>
			          </div>
			          <small class="error col-sm-3"><?php if(!empty($errorReason)) echo $errorReason; ?></small>
			        </div>
		            <script type="text/javascript">
		            	document.getElementById('reason').value = "<?php echo Input::get('reason'); ?>";
		            </script>

			        <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			        <div class="form-group">
			          <div class="col-sm-3"></div>
			          <div class="col-sm-6">
			            <input type="submit" name="submit" class="btn btn-success" value="Send">
			            <input type="reset" name="reset" class="btn btn-info" value="Reset">
			          </div>
			          <div class="col-sm-3"></div>
			        </div>
			      </div><!-- /.box-body -->
 				  <div class="col-sm-2"></div>

			     </form>
			    </div><!-- /.box -->
			  </div>


			</div>
		</div>
	 
	</section><!-- /.content -->

</div><!-- /.content-wrapper -->