<?php
if(!$user->isLoggedIn()){
	Redirect::to('home');
}
require '3rdparty/PHPMailer/PHPMailerAutoload.php';
$mail = new PHPMailer;
$db = DB::getInstance();

$status = '';
$mail_type = '';
$leave_id = '';

$db->get('settings', array('1', '=', '1'));
$mailSetting = $db->first();
$mail_type = $mailSetting->mail_type;

$db->get('email_templates', array('template_name', '=', 'Leave grant/reject'));
$temData = $db->first();
 //var_dump($temData);
if($user->data()->isAdmin == 1){
	if(isset($_POST['btnGrant'])){
		$status = 'Granted';
		$subject = str_replace('$status', $status, $temData->subject);

		$leave_id = Input::get('leave_id');
		$db->query("SELECT u.*, l.leave_from, l.leave_to FROM users u LEFT JOIN emp_leave l ON u.id = l.user_id WHERE l.id = {$leave_id}");
		$userData = $db->first();
		$fullname = $userData->firstname . " " . $userData->lastname;
		$leaveFrom = date('jS M, Y', strtotime($userData->leave_from));
		$leaveTo = date('jS M, Y', strtotime($userData->leave_to));

		$body = str_replace('$signature', $mailSetting->email_signature, str_replace('$to', $leaveTo, str_replace('$from', $leaveFrom, str_replace('$status', $status, str_replace('$name', $fullname, trim($temData->email_body))))));
		//var_dump($body);		

		try{
			$db->update('emp_leave', Input::get('leave_id'), array(
				'status' => 'Granted',
				'user_id_updated' => $user->data()->id,
				'time_updated' => date('Y-m-d H:i:s')
			));

			$db->insert('messages', array(
				'from_user_id' => $user->data()->id,
				'to_user_id' => $userData->id,
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
					Session::flash('success', 'Leave has been granted successfully!');
					Redirect::to('leaves');
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
					Session::flash('success', 'Leave has been granted successfully!');			
					Redirect::to('leaves');					
				}
			}	

		} catch(Exception $e){
			die("There is a problem granting the leave.");
		}

	} elseif (isset($_POST['btnReject'])) {
		$leave_id = Input::get('leave_id');
		$status = 'Rejected';
		$subject = str_replace('$status', $status, $temData->subject);

		$db->query("SELECT u.* FROM users u LEFT JOIN emp_leave l ON u.id = l.user_id WHERE l.id = {$leave_id}");
		$userData = $db->first();

		$fullname = $userData->firstname . " " . $userData->lastname;

		$body = str_replace('$signature', $mailSetting->email_signature, str_replace('$to', '', str_replace('To:', '', str_replace('$from', '', str_replace('From:', '', str_replace('$status', $status, str_replace('$name', $fullname, trim($temData->email_body))))))));
		//var_dump($body);		

		try{
			$db->update('emp_leave', Input::get('leave_id'), array(
				'status' => 'Rejected',
				'user_id_updated' => $user->data()->id,
				'time_updated' => date('Y-m-d H:i:s')				
			));

			$db->insert('messages', array(
				'from_user_id' => $user->data()->id,
				'to_user_id' => $userData->id,
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
					Session::flash('success', 'Leave has been rejected successfully!');
					Redirect::to('leaves');
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
					Session::flash('success', 'Leave has been rejected successfully!');			
					Redirect::to('leaves');					
				}
			}	

		} catch(Exception $e){
			die('There is a problem rejecting the leave.');
		}
	}

	if(Session::exists('success')){
		$success = Session::get('success');
		Session::delete('success');
	}

	$db->query("SELECT l.*, DATE_FORMAT(l.leave_from, '%D %b, %Y') AS date_from, DATE_FORMAT(l.leave_to, '%D %b, %Y') AS date_to, CONCAT(u.firstname, ' ', u.lastname) AS fullname, t.type FROM `emp_leave` l LEFT JOIN users u ON l.user_id = u.id LEFT JOIN leave_type t ON l.type_id=t.id ORDER BY l.time_inserted DESC");
	$results = $db->results();

?>

<script type="text/javascript">
  jQuery(document).ready(function() {
      jQuery('#leaveTable').DataTable({
      	"order": []
      });
  });
</script>

<div class="content-wrapper">
<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 class="sectionTitle">
	   Applied leaves
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
		<div class="box">
			<div class="box-body">
			  <table id="leaveTable" class="table table-bordered table-striped">
			  	<?php if($db->emptyTable()){echo $db->emptyTable(); } else { ?>
			    <thead>
			      <tr>
			        <th>Name</th>
			        <th>Leave type</th>
			        <th style="width: 68px;">Date from</th>
			        <th style="width: 65px;">Date to</th>
			        <th style="width: 70px;">Days</th>
			        <th>Reason</th>
			        <th style="width: 40px;">Status</th>
			        <th style="width: 130px;">Options</th>
			      </tr>
			    </thead>
			    <tbody>
			    <?php foreach ($results as $result) { ?>
			      <tr>
			        <td><?php echo $result->fullname; ?></td>
			        <td><?php echo $result->type; ?></td>
			        <td><?php echo $result->date_from; ?></td>
			        <td><?php echo $result->date_to; ?></td>
			        <td><?php echo $result->days; ?> day<?php if($result->days > 1) echo 's';?></td>
			        <td><?php echo $result->reason; ?></td>
			        <td>
			        	<span class="label label-<?php
				        	if($result->status == 'Granted'){
				        		echo 'success';
				        	}elseif($result->status == 'Rejected'){
				        		echo 'danger';
				        	}else{
				        		echo 'warning'; 
				        	}?>">
			        		<?php echo $result->status; ?>
			        	</span>
			        </td>
			        <td>
			        	<form action="" method="post" class="form-inline" style="display: inline;">
			        		<input type="hidden" name="leave_id" value="<?php echo $result->id; ?>">
			        		<input type="submit" name="btnGrant" value="Grant" class="btn btn-success btn-sm<?php if($result->status === 'Granted') echo ' disabled';?>" title="Grant leave">
			        		<button type="submit" name="btnReject" class="btn btn-danger btn-sm<?php if($result->status === 'Rejected') echo ' disabled';?>" title="Reject leave">Reject</button>
			        	</form>
			        	<form action="edit_leave" method="post" class="form-inline" style="display: inline;">
			        		<input type="hidden" name="leave_id" value="<?php echo $result->id; ?>">
			        		<button type="submit" name="btnEdit" class="btn btn-warning btn-sm <?php if($result->status === 'Granted') echo 'disabled';?>" title="Edit leave"><span class="glyphicon glyphicon-pencil"></span></button>			        		
			        	</form>
			        </td>
			      </tr>
			     <?php } ?>
			    </tbody>
			    <?php } ?>
			  </table>
			</div><!-- /.box-body -->
		</div><!-- /.box -->
	</section>
</div>
<!--End region of Admin panel-->

<?php

} else {

// Start region of user specific panel.

	$db->query("SELECT l.*, DATE_FORMAT(l.leave_from, '%D %b, %Y') AS date_from, DATE_FORMAT(l.leave_to, '%D %b, %Y') AS date_to, CONCAT(u.firstname, ' ', u.lastname) AS fullname, t.type FROM `emp_leave` l LEFT JOIN users u ON l.user_id = u.id LEFT JOIN leave_type t ON l.type_id=t.id WHERE user_id={$user->data()->id} ORDER BY l.time_inserted");
	$getLeave = $db->results();
	//var_dump($getLeave);
?>

<script type="text/javascript">
  jQuery(document).ready(function() {
      jQuery('#leavTable').DataTable({
      	"order": [],
      	"columns": [
      	  {"width": "11%"},
      	  {"width": "10%"},
      	  {"width": "10%"},
      	  null,
      	  null,
      	  {"width": "9%"},
      	]
      });
  });
</script>

<div class="content-wrapper">
<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 class="sectionTitle">
	   Leaves of <?php if(!empty($getLeave[0]->fullname)) echo $getLeave[0]->fullname; ?>
	  </h1>
	  <!--<ol class="breadcrumb">
	    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
	    <li class="active">Dashboard</li>
	  </ol>-->
	  <?php if(!empty($success)) echo '<span class="success col-sm-offset-3">' . $success . '</span>'; ?>

	</section>

	<!-- Main content -->
	<section class="content">
		<div class="box">
			<div class="box-body">
			  <table id="leavTable" class="table table-bordered table-striped">
			  	<?php if($db->emptyTable()){echo $db->emptyTable(); } else { ?>
			    <thead>
			      <tr>
			        <th>Leave type</th>
			        <th>Date from</th>
			        <th>Date to</th>
			        <th>Days</th>
			        <th>Reason</th>
			        <th>Status</th>
			      </tr>
			    </thead>
			    <tbody>
			    	<?php foreach ($getLeave as $leave) {?>
			      <tr>
			        <td><?php echo $leave->type; ?></td>
			        <td><?php echo $leave->date_from; ?></td>
			        <td><?php echo $leave->date_to; ?></td>
			        <td><?php echo $leave->days; ?> day<?php if($leave->days > 1) echo 's';?></td>
			        <td><?php echo $leave->reason; ?></td>
			        <td>
			        	<span class="label label-<?php
				        	if($leave->status == 'Granted'){
				        		echo 'success';
				        	}elseif($leave->status == 'Rejected'){
				        		echo 'danger';
				        	}else{
				        		echo 'warning'; 
				        	}?>" style="margin-right: 2px;">
			        		<?php echo $leave->status; ?>
			        	</span>
			        	<?php if($leave->status == 'Pending'){ ?>
			        	  <form action="edit_leave" method="post" class="form-inline" style="display: inline;">
			        		<input type="hidden" name="leave_id" value="<?php echo $leave->id; ?>">
			        		<button type="submit" name="btnEdit" class="btn btn-danger btn-xs" title="Edit leave"><i class="fa fa-pencil"></i></button>			        		
			        	  </form>
			        	<?php } ?>
			        </td>
			      </tr>
			      <?php } ?>
			    </tbody>
			    <?php } ?>
			  </table>
			</div><!-- /.box-body -->
		</div><!-- /.box -->
	</section>
</div>

<?php } ?>