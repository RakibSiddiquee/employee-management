<?php

if(!$user->isLoggedIn()){
	Redirect::to('home');
}

require '3rdparty/PHPMailer/PHPMailerAutoload.php';
$mail = new PHPMailer;

$db = DB::getInstance();

$url = explode("/", $_SERVER['REQUEST_URI']);
$task_id = substr($url[count($url) - 1],strpos($url[count($url) - 1], '?')+1);
$taskData = $db->get('tasks', array('id', '=', $task_id))->first();

$mail_type = '';
$db->get('settings', array('1', '=', '1'));
$mailSetting = $db->first();
$mail_type = $mailSetting->mail_type;

$db->get('email_templates', array('template_name', '=', 'Task assignment'));
$temData = $db->first();

if(isset($_POST['btnSave'])){
	$db->update('tasks', Input::get('taskid'), array(
		'status' => Input::get('status'),
		'updated_id' => $user->data()->id,
		'updated_at' => date('Y-m-d H:i:s')
	));


	$fullname = $user->data()->firstname . " " . $user->data()->lastname;
	$subject = 'Task updated';
	$body = 'Mr./Ms. ' . $temData->name_from. ',<br/><br/>The <strong>'. $taskData->title . '</strong> task has been updated. It is now <strong>' . Input::get('status') . '</strong><br/><br/>Regards<br/>' . $mailSetting->email_signature;
	//var_dump($body);

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
		$mail->setFrom($user->data()->email, $fullname);
		//Set an alternative reply-to address
		$mail->addReplyTo($user->data()->email, $fullname);
	
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
			Session::flash('success', 'The task has been submitted successfully!');
			Redirect::to('task_list');
		}
	
	}else{
	//Set who the message is to be sent from
		$mail->setFrom($user->data()->email, $fullname);
		
		//Set an alternative reply-to address
		$mail->addReplyTo($user->data()->email, $fullname);
		
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
			Session::flash('success', 'The status has been updated successfully!');
			Redirect::to('task_list');				
		}
	}
	
}

$userTaskData = $db->query("SELECT t.*, CONCAT(u.firstname, ' ', u.lastname) AS fullname, u.designation, u.email FROM tasks t LEFT JOIN users u ON t.user_id = u.id WHERE t.id = {$task_id}")->first();
//var_dump($userTaskData);

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 style="display: inline;">
	    View task
	  </h1>

	  <ol class="breadcrumb">
	    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
	    <li class="active">View task</li>
	  </ol>
	</section>

	<!-- Main content -->
	<section class="content">

		<div class="row">
			<div class="col-md-12">
			  <div class="box box-info">
			  	<div class="box-body">
					<div class="row">
						<div class="col-sm-12">
							<table class="table table-striped" style="border: 1px solid lightgray;">
								<?php if($user->data()->isAdmin == 1){?>
							    <thead>
							      <tr>
							        <th class="col-sm-2">Name:</th>
							        <th><?php echo $userTaskData->fullname . ' (' . $userTaskData->designation . ')'; ?><small class="pull-right"><em><?php echo $userTaskData->email; ?></em></small></th>
							      </tr>
							    </thead>
							    <?php } ?>
							    <tbody>
							      <tr>
							        <th class="col-sm-2">Task title:</th>
							        <th><em><?php echo $userTaskData->title; ?></em></th>
							      </tr>							    	
							      <tr>
							      	<th class="col-sm-2">Start date:</th>
							        <td colspan="2"><?php echo date('jS M, Y', strtotime($userTaskData->start_date));?></td>
							      </tr>
							      <tr>
							      	<th class="col-sm-2">End date:</th>
							        <td colspan="2"><?php echo date('jS M, Y', strtotime($userTaskData->end_date));?></td>
							      </tr>
							      <tr>
							      	<th class="col-sm-2">Status:</th>
							        <td><span id="editStatus"
				                  		<?php 
					                  		if($userTaskData->status == 'Not started'){
					                  			echo 'class="label label-danger"';
					                  		} elseif ($userTaskData->status == 'In progress') {
					                  			echo 'class="label label-info"';
					                  		}elseif ($userTaskData->status == 'Completed') {
					                  			echo 'class="label label-success"';
					                  		}elseif ($userTaskData->status == 'Pending') {
					                  			echo 'class="label label-warning"';
					                  		}
					                  		echo '>' . $userTaskData->status;
				                  		?>
				                  		
				                  		</span>
				                  		<a style="margin-left: 3px;" class="btn btn-primary btn-xs" onclick="document.getElementById('editForm').style.display = ''; document.getElementById('editStatus').style.display = 'none'; this.style.display = 'none'" title="Edit status"><span class="glyphicon glyphicon-pencil"></span></a>
				                  		<form id="editForm" method="post" class="form-inline" style="display: none">
				                  			<input type="hidden" name="taskid" value="<?php echo $userTaskData->id; ?>">
							            	<select name="status" id="status" class="form-control">
												<option value="">Choose a status</option>				
												<option value="Not started" <?php if($userTaskData->status == 'Not started') echo 'selected'; ?>>Not started</option>
												<option value="Pending" <?php if($userTaskData->status == 'Pending') echo 'selected'; ?>>Pending</option>			
												<option value="In progress" <?php if($userTaskData->status == 'In progress') echo 'selected'; ?>>In progress</option>	
												<option value="Completed" <?php if($userTaskData->status == 'Completed') echo 'selected'; ?>>Completed</option>
											</select>

				                  			<input type="submit" name="btnSave" value="Save" class="btn btn-primary btn-sm">
				                  		</form>

							        </td>
							      </tr>
							      <tr>
							      	<th class="col-sm-2">Description:</th>
							        <td colspan="2"><?php echo $userTaskData->description;?></td>
							      </tr>

							    </tbody>
							</table>
						</div>					
			  		</div>
			    </div>
			  </div><!-- /.box -->
			</div>


		</div>
	 
	</section><!-- /.content -->	
</div><!-- /.content-wrapper -->
