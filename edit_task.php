<?php
if($user->data()->isAdmin != 1){
	Redirect::to('home');
}

require '3rdparty/PHPMailer/PHPMailerAutoload.php';
$mail = new PHPMailer;

$db = DB::getInstance();
$validate = new Validate();

if(isset($_POST['btnEdit'])){
	$taskid = Input::get('taskid');
	$editTask = $db->query("SELECT t.*, CONCAT(u.firstname, ' ', u.lastname) AS fullname FROM tasks AS t LEFT JOIN users AS u ON t.user_id = u.id WHERE t.id = {$taskid}")->first();
	//var_dump($editTask);
}

$userid = '';
$errorName = '';
$errorTaskTitle = '';
$errorStartDate = '';
$errorEndDate = '';
$errorStatus = '';
$errorDescription = '';

$mail_type = '';
$db->get('settings', array('1', '=', '1'));
$mailSetting = $db->first();
$mail_type = $mailSetting->mail_type;

$db->get('email_templates', array('template_name', '=', 'Task assignment'));
$temData = $db->first();


// Insert region start
if(isset($_POST['btnUpdate'])){
	$validation = $validate->check($_POST, array(
		'name' => array(
			'required' => true
		),
		'task_title' => array(
			'required' => true
		),
		'start_date' => array(
			'required' => true
		),
		'end_date' => array(
			'required' => true
		),
		'status' => array(
			'required' => true
		),
		'description' => array(
			'required' => true
		)
	));


	if($validation->passed()){
		//var_dump(Input::get('reason'));
		try{
			$db->update('tasks', Input::get('task_id'), array(
				'user_id' => Input::get('name'),
				'title' => Input::get('task_title'),
				'start_date' => Input::get('start_date'),
				'end_date' => Input::get('end_date'),
				'description' => Input::get('description'),
				'status' => Input::get('status'),
				'updated_id' => $user->data()->id,
				'updated_at' => date('Y-m-d H:i:s')
			));

			// Area to send SMS
			//$userid = Input::get('name');
			$userData = $db->get('users', array('id', '=', Input::get('name')))->first();	
			//var_dump($userData);
			$fullname = $userData->firstname . " " . $userData->lastname;
			$subject = 'Task updated';
			$body = 'Mr./Ms. ' . $fullname . ',<br/><br/>Your <strong>'. Input::get('task_title') . '</strong> task has been updated.<br/><br/>Regards<br/>' . $mailSetting->email_signature;
			//var_dump($body);

			$db->insert('messages', array(
				'from_user_id' => $user->data()->id,
				'to_user_id' => Input::get('name'),
				'subject' => $subject,
				'body' => $body,
				'created_id' => $user->data()->id,
				'created_at' => date('Y-m-d H:i:s')
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
				$mail->addAddress($userData->email, $fullname);
				$mail->isHTML(true);  
				//Set the subject line
				$mail->Subject = $subject;
				// Body
				$mail->Body = $body;

				//send the message, check for errors
				if(!$mail->send()){
					echo 'Mailer error: ' . $mail->ErrorInfo;
				}else{
					Session::flash('success', 'The task has been submitted successfully!');
					Redirect::to('task_list');
				}

     		}else{
	     		//Set who the message is to be sent from
				$mail->setFrom($temData->email_from, $temData->name_from);
				
				//Set an alternative reply-to address
				$mail->addReplyTo($temData->email_from, $temData->name_from);
				
				//Set who the message is to be sent to
				$mail->addAddress($userData->email, $fullname);
				//Set the subject line
				$mail->Subject = $subject;
				// Body
				$mail->msgHTML($body);

				//send the message, check for errors
				if(!$mail->send()){
					echo 'Mailer error: ' . $mail->ErrorInfo;
				}else{
					Session::flash('success', 'The task has been submitted successfully!');
					Redirect::to('task_list');				
				}
			}

		} catch (Exception $e){
			die('There was a problem updating the task.');
		}
	} else {
		foreach ($validation->errors() as $error) {
			foreach ($error as $key => $value) {
				$errors[$key] = $value;
			}
		}
		if(!empty($errors['Name'])) $errorName = $errors['Name'];
		if(!empty($errors['Task title'])) $errorTaskTitle= $errors['Task title'];
		if(!empty($errors['Start date'])) $errorStartDate = $errors['Start date'];
		if(!empty($errors['End date'])) $errorEndDate = $errors['End date'];
		if(!empty($errors['Description'])) $errorDescription = $errors['Description'];
		if(!empty($errors['Status'])) $errorStatus = $errors['Status'];

	}
}

?>

<script type="text/javascript">
  // for datepicker
    jQuery(function() {
      jQuery( "#enddatepicker" ).datepicker({dateFormat: "yy-mm-dd"});

    });
</script>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 style="display: inline;">
	    Add new task
	  </h1>
	  <ol class="breadcrumb">
	    <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
	    <li class="active"><?php echo $title; ?></li>
	  </ol>
	  <?php //if(!empty($success)) echo '<span class="success col-sm-offset-3">' . $success . '</span>'; ?>
	  <?php //if(!empty($error)) echo '<span class="success col-sm-offset-3">' . $error . '</span>'; ?>
	</section>

	<!-- Main content -->
	<section class="content">

		<div class="row">
			<div class="col-md-12">
			  <div class="box box-info">
			  	<div class="box-body">
					<div class="row">
						<div class="col-sm-10">
							<form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
								<input type="hidden" name="task_id" value="<?php if(!empty($editTask->id)) echo $editTask->id; ?>">
								<div class="form-group">						          
									<label for="name" class="col-sm-2 control-label">Name:</label>
					            	<div class="col-sm-8">
						            	<select name="name" id="name" class="form-control">
											<option value="">Choose a name</option>
											<?php
												$results = $db->get('users', array('1', '=', '1'))->results();
												foreach ($results as $value) {
											?>
											<option value="<?php echo $value->id; ?>" <?php if($editTask->user_id == $value->id) echo 'selected';?> >
												<?php echo $value->firstname . ' ' . $value->lastname . ' (' . $value->designation . ')'; ?>
											</option>
											<?php } ?>
										</select>
									</div>
									<small class="error col-sm-2"><?php if(!empty($errorName)) echo $errorName; ?></small>
								</div>
								<div class="form-group">
									<label for="task_title" class="col-sm-2 control-label">Task title:</label>
									<div class="col-sm-8">
		           						<input type="text" name="task_title" id="task_title" class="form-control" value="<?php if(!empty($editTask->title)) echo $editTask->title; ?>" placeholder="Task title"/>
		           					</div>
									<small class="error col-sm-2"><?php if(!empty($errorTaskTitle)) echo $errorTaskTitle; ?></small>
								</div>
								<div class="form-group">
									<label for="datepicker" class="col-sm-2 control-label">Start date:</label>
									<div class="col-sm-8">
		           						<input type="text" name="start_date" id="datepicker" class="form-control" value="<?php if(!empty($editTask->start_date)) echo $editTask->start_date; ?>" placeholder="Start date"/>
		           					</div>
									<small class="error col-sm-2"><?php if(!empty($errorStartDate)) echo $errorStartDate; ?></small>
								</div>
								<div class="form-group">
									<label for="enddatepicker" class="col-sm-2 control-label">End date:</label>
									<div class="col-sm-8">
		           						<input type="text" name="end_date" id="enddatepicker" class="form-control" value="<?php if(!empty($editTask->end_date)) echo $editTask->end_date; ?>" placeholder="End date"/>
		           					</div>
									<small class="error col-sm-2"><?php if(!empty($errorEndDate)) echo $errorEndDate; ?></small>
								</div>
								<div class="form-group">						          
									<label for="status" class="col-sm-2 control-label">Status:</label>
					            	<div class="col-sm-8">
						            	<select name="status" id="status" class="form-control">
											<option value="">Choose a status</option>					
											<option value="Not started" <?php if($editTask->status == 'Not started') echo 'selected'; ?>>Not started</option>
											<option value="Pending" <?php if($editTask->status == 'Pending') echo 'selected'; ?>>Pending</option>			
											<option value="In progress" <?php if($editTask->status == 'In progress') echo 'selected'; ?>>In progress</option>	
											<option value="Completed" <?php if($editTask->status == 'Completed') echo 'selected'; ?>>Completed</option>
										</select>
									</div>
									<small class="error col-sm-2"><?php if(!empty($errorStatus)) echo $errorStatus; ?></small>
								</div>

						        <div class="form-group" style="margin-bottom: 0;">
									<label for="description" class="col-sm-2 control-label">Description:</label>
						        	<div class="col-sm-8">
						           		<textarea name="description" id="description" class="form-control" value=""></textarea>
						            </div>
						         	 <small class="error col-sm-2"><?php if(!empty($errorDescription)) echo $errorDescription; ?></small>
						         	 <script type="text/javascript">
						         	 	document.getElementById('description').value = '<?php echo $editTask->description; ?>';
						         	 </script>
						        </div>
						        <div class="form-group">
						        	<div class="col-sm-2"></div>
						        	<div class="col-sm-8">
										<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
										<input type="submit" name="btnUpdate" value="Update" class="btn btn-success">
									</div>
								</div>

							</form>

						</div>					
			  		</div>
			    </div>
			  </div><!-- /.box -->
			</div>


		</div>
	 
	</section><!-- /.content -->	
</div><!-- /.content-wrapper -->

<script src="xhttp://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.js"></script> 
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#description').summernote({
		  	height: 150,
			toolbar: [
				// [groupName, [list of button]]
				['undo', ['undo']],
				['redo', ['redo']],
				['style', ['bold', 'italic', 'underline', 'clear']],
				['fontname', ['fontname']],
				['fontsize', ['fontsize']],			
				['font', ['strikethrough', 'superscript', 'subscript']],
				['color', ['color']],
				['para', ['ul', 'ol', 'paragraph']],
				['link', ['link']],
				['table', ['table']],
				['hr', ['hr']],
				['codeview', ['codeview']],
				['fullscreen', ['fullscreen']],
				//['help', ['help']]
			  ],
			placeholder: 'Task details...',
		});
	});
</script>