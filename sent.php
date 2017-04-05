<?php
if(!$user->isLoggedIn()){
	Redirect::to('home');
}
require '3rdparty/PHPMailer/PHPMailerAutoload.php';

$db = DB::getInstance();
$validate = new Validate();

$success = '';
$errorSubject = '';


?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 style="display: inline;">
	    Message
	  </h1>
	  <!--<ol class="breadcrumb">
	    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
	    <li class="active">Dashboard</li>
	  </ol>-->
	  <?php if(!empty($success)) echo '<span class="success col-sm-offset-3">' . $success . '</span>'; ?>	  
	</section>

	<!-- Main content -->
	<section class="content">

		<div class="row">
			<div class="col-md-12">
			  <div class="box box-info">
			  	<div class="box-body">
					<div class="row">
						<div class="col-sm-2">
							<a href="" class="btn btn-warning btn-block md-trigger">Compose</a>
							<div class="list-group" style="margin: 20px 0 0">
								<a href="inbox" class="list-group-item active">Inbox<span class="badge">10</span></a>
								<a href="sent" class="list-group-item">Sent<span class="badge">15</span></a>
							</div>
						</div>

						<div class="col-sm-8">
						<form action="" method="post" class="form-horizontal">
							<input type="hidden" name="settingsId" value="<?php //echo $getData->id; ?>">
							<div class="form-group">
				            	<select name="name" id="name" class="form-control">
									<option value="">Choose a name</option>
									<?php
										$results = $db->get('users', array('1', '=', '1'))->results();
										foreach ($results as $value) {
									?>
									<option value="<?php echo $value->id; ?>"><?php echo $value->firstname . ' ' . $value->lastname; ?></option>
									<?php } ?>
								</select>								
							</div>
							<div class="form-group">
	           					<input type="text" name="subject" id="subject" class="form-control" value="<?php //echo $getData->smtp_port; ?>" placeholder="Subject"/>
								<small class="error col-sm-4"><?php if(!empty($errorSmtpPort)) echo $errorSmtpPort; ?></small>
							</div>
								<div class="form-group">
		           					<input type="file" name="company_logo" id="company_logo" class="form-control" value="" placeholder="Company name"/>
								</div>
		
					        <div class="form-group">
					            <textarea name="summernote" id="summernote" class="form-control"></textarea>
					         	 <small class="error col-sm-3"><?php if(!empty($errorEmailSignature)) echo $errorEmailSignature; ?></small>
					        </div>

					        <div class="form-group">
								<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
								<input type="submit" name="btnSend" value="Send" class="btn btn-success">
								<input type="reset" name="btnReset" value="Clear" class="btn btn-danger">

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
<script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.js"></script> 
<script>
jQuery(document).ready(function() {
	jQuery('#summernote').summernote({
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
		placeholder: 'write here...',
	});
});

</script>
