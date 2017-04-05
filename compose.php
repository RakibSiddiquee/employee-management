<?php
if(!$user->isLoggedIn()){
	Redirect::to('home');
}


require '3rdparty/PHPMailer/PHPMailerAutoload.php';
$mail = new PHPMailer;

$db = DB::getInstance();
$validate = new Validate();

$success = '';
$error = '';
$errorName = '';
$errorSubject = '';
$errorBody = '';
// Insert region start

if(isset($_POST['btnSend'])){
	$validation = $validate->check($_POST, array(
		'name' => array(
			'required' => true
		),
		'subject' => array(
			'required' => true
		),
		'body' => array(
			'required' => true
		)
	));

  	$file = '';
  	$uploads = '';
  	if(!empty($_FILES['attachment']['name'])){
    	$file = $_FILES['attachment']['name'];
  	}

  	if($validation->passed()){
	    $dir = "uploads/files/";
	    //$target_dir = $dir . $base = basename($file);
	    $ext = pathinfo($file, PATHINFO_EXTENSION);
	    $base = basename(basename($file), '.'.$ext);
	    $fileUp = $base . '.' . $ext;
	    if(empty($file)) $fileUp = '';
	    
		try{
			$db->insert('messages', array(
				'from_user_id' => $user->data()->id,
				'to_user_id' => Input::get('name'),
				'cc' => Input::get('cc'),
				'bcc' => Input::get('bcc'),
				'subject' => Input::get('subject'),
				'body' => Input::get('body'),
				'attachment' => $fileUp,
				'created_id' => $user->data()->id,
				'created_at' => date('Y-m-d H:i:s')
			));

     		move_uploaded_file($_FILES['attachment']['tmp_name'], $dir . $fileUp);

     		$db->get('settings', array('1', '=', '1'));
     		$mailSetting = $db->first();
     		$mail_type = $mailSetting->mail_type;


     		$db->get('users', array('id', '=', Input::get('name')));
     		$userData = $db->first();

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

				if(!empty(Input::get('cc'))){
		     		$db->get('users', array('id', '=', Input::get('cc')));
		     		$ccData = $db->first()->email;				
					$mail->addCC($ccData);
				}

				if(!empty(Input::get('bcc'))){
		     		$db->get('users', array('id', '=', Input::get('bcc')));
		     		$bccData = $db->first()->email;				
					$mail->addBCC($bccData);
				}

				//Set who the message is to be sent to
				$mail->addAddress($userData->email, $userData->firstname . ' ' . $userData->lastname);
				$mail->isHTML(true);  
				//Set the subject line
				$mail->Subject = Input::get('subject');
				// Body
				$mail->Body = Input::get('body');
				if(!empty($fileUp)){
					$mail->addAttachment($dir . $fileUp);
				}
				//send the message, check for errors
				if(!$mail->send()){
					echo 'Mailer error: ' . $mail->ErrorInfo;
				}else{
					Session::flash('success', 'Message has been sent successfully!');
					Redirect::to('messages');
				}

     		}else{
	     		//Set who the message is to be sent from
				$mail->setFrom($user->data()->email, $user->data()->firstname . ' ' . $user->data()->lastname);
				
				//Set an alternative reply-to address
				$mail->addReplyTo($user->data()->email, $user->data()->firstname . ' ' . $user->data()->lastname);

				if(!empty(Input::get('cc'))){
		     		$db->get('users', array('id', '=', Input::get('cc')));
		     		$ccData = $db->first()->email;				
					$mail->addCC($ccData);
				}

				if(!empty(Input::get('bcc'))){
		     		$db->get('users', array('id', '=', Input::get('bcc')));
		     		$bccData = $db->first()->email;				
					$mail->addBCC($bccData);
				}
				
				//Set who the message is to be sent to
				$mail->addAddress($userData->email, $userData->firstname . ' ' . $userData->lastname);
				//Set the subject line
				$mail->Subject = Input::get('subject');
				// Body
				$mail->msgHTML(Input::get('body'));
				if(!empty($fileUp)){
					$mail->addAttachment($dir . $fileUp);
				}
				//send the message, check for errors
				if(!$mail->send()){
					echo 'Mailer error: ' . $mail->ErrorInfo;
				}else{
					Session::flash('success', 'Message has been sent successfully!');			
					Redirect::to('messages');					
				}
			}

		} catch (Exception $e){
			die('There was a problem sending the message.');
		}
	} else {
		foreach ($validation->errors() as $error) {
			foreach ($error as $key => $value) {
				$errors[$key] = $value;
			}
		}
		if(!empty($errors['Name'])) $errorName = $errors['Name'];
		if(!empty($errors['Subject'])) $errorSubject = $errors['Subject'];
		if(!empty($errors['Body'])) $errorBody = $errors['Body'];
	}
// End Insert region

}

$db->query("SELECT m.*, DATE_FORMAT(m.created_at, '%D-%b-%Y, %h:%i %p') AS time, CONCAT(u.firstname, ' ', u.lastname) AS fullname, u.designation FROM messages m LEFT JOIN users u ON m.from_user_id = u.id WHERE to_user_id = {$user->data()->id} ORDER BY m.created_at DESC");
$inboxData = $db->results();
$inboxCount = $db->count();

$db->query("SELECT m.*, DATE_FORMAT(m.created_at, '%D-%b-%Y, %h:%i %p') AS time, CONCAT(u.firstname, ' ', u.lastname) AS fullname, u.designation FROM messages m LEFT JOIN users u ON m.to_user_id = u.id WHERE from_user_id = {$user->data()->id} ORDER BY m.created_at DESC");
$sentData = $db->results();
$sentCount = $db->count();

?>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 style="display: inline;">
	    <?php if(isset($_POST['btnSent'])){echo "Sent messages";}else{ echo "Inbox messages";}?>
	  </h1>
	  <ol class="breadcrumb">
	    <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
	    <li class="active"><?php echo $title; ?></li>
	  </ol>
	  <?php if(!empty($success)) echo '<span class="success col-sm-offset-3">' . $success . '</span>'; ?>
	  <?php if(!empty($error)) echo '<span class="success col-sm-offset-3">' . $error . '</span>'; ?>
	</section>

	<!-- Main content -->
	<section class="content">

		<div class="row">
			<div class="col-md-12">
			  <div class="box box-info">
			  	<div class="box-body">
					<div class="row">
						<div class="col-sm-2">
							<a href="compose" class="btn btn-warning btn-block md-trigger">Compose</a>
							<div class="list-group" style="margin: 20px 0 0">
								<a href="messages" name="btnInbox" class="list-group-item active">Inbox<span class="badge"><?php echo $inboxCount; ?></span></a>
								<a href="sent_messages" class="list-group-item">Sent<span class="badge"><?php echo $sentCount; ?></span></a>
							</div>

						</div>

						<div class="col-sm-10">
							<form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
								<div class="form-group">
									<div class="col-sm-10">
						            	<select name="name" id="name" class="form-control">
											<option value="">To</option>
											<?php
												$results = $db->get('users', array('1', '=', '1'))->results();
												foreach ($results as $value) {
											?>
											<option value="<?php echo $value->id; ?>"><?php echo $value->firstname . ' ' . $value->lastname . ' (' . $value->designation . ')'; ?> </option>
											<?php } ?>
										</select>
									</div>
									<small class="error col-sm-2"><?php if(!empty($errorName)) echo $errorName; ?></small>

								</div>
								<div class="form-group">
									<div class="col-sm-10">
						            	<select name="cc" id="cc" class="form-control">
											<option value="">Cc</option>
											<?php
												foreach ($results as $value) {
											?>
											<option value="<?php echo $value->id; ?>"><?php echo $value->firstname . ' ' . $value->lastname . ' (' . $value->designation . ')'; ?> </option>
											<?php } ?>
										</select>
									</div>

								</div>
								<div class="form-group">
									<div class="col-sm-10">
						            	<select name="bcc" id="bcc" class="form-control">
											<option value="">Bcc</option>
											<?php
												foreach ($results as $value) {
											?>
											<option value="<?php echo $value->id; ?>"><?php echo $value->firstname . ' ' . $value->lastname . ' (' . $value->designation . ')'; ?> </option>
											<?php } ?>
										</select>
									</div>						
								</div>
								<div class="form-group">
									<div class="col-sm-10">
		           						<input type="text" name="subject" id="subject" class="form-control" value="<?php //echo $getData->smtp_port; ?>" placeholder="Subject"/>
		           					</div>
									<small class="error col-sm-2"><?php if(!empty($errorSubject)) echo $errorSubject; ?></small>
								</div>
						        <div class="form-group" style="margin-bottom: 0;">
						        	<div class="col-sm-10">
						           		<textarea name="body" id="body" class="form-control" value=""></textarea>
						            </div>
						         	 <small class="error col-sm-2"><?php if(!empty($errorBody)) echo $errorBody; ?></small>
						        </div>
								<div class="form-group col-sm-10">
		           					<input type="file" name="attachment" id="attachment" class="btn btn-default" value="" title="Attachment"/>
								</div>
		
						        <div class="form-group col-sm-10">
									<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
									<input type="submit" name="btnSend" value="Send" class="btn btn-success">
									<a href="message" class="btn btn-danger">Clear</a>
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
		jQuery('#body').summernote({
		  	height: 200,
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
				['help', ['help']]
			  ],
			placeholder: 'Message body...',
		});
	});
</script>