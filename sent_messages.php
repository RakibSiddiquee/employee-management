<?php
if(!$user->isLoggedIn()){
	Redirect::to('home');
}
require '3rdparty/PHPMailer/PHPMailerAutoload.php';
$mail = new PHPMailer;

$db = DB::getInstance();

// Message delete 

if(isset($_POST['btnDelete']) && empty(Input::get('chkbox'))){
	Session::flash('error', 'Please select a message first!');
	Redirect::to('sent_messages');

}elseif(isset($_POST['btnDelete']) && !empty(Input::get('chkbox'))){
	$ids = implode(',', Input::get('chkbox'));
	$db->query("DELETE FROM messages WHERE id in ({$ids})");	
	Session::flash('success', 'The message has been deleted successfully!');
	Redirect::to('sent_messages');
}
// end of message

$inboxData = $db->query("SELECT COUNT(id) AS total FROM messages WHERE to_user_id = {$user->data()->id}")->first();
$inboxCount = $inboxData->total;

$db->query("SELECT m.*, DATE_FORMAT(m.created_at, '%h:%i %p, %D-%b-%Y') AS time, CONCAT(u.firstname, ' ', u.lastname) AS fullname, u.designation FROM messages m LEFT JOIN users u ON m.to_user_id = u.id WHERE from_user_id = {$user->data()->id} ORDER BY m.created_at DESC");
$sentData = $db->results();
$sentCount = $db->count();
//var_dump($getData);

if(Session::exists('success')){
  $success = Session::get('success');
  Session::delete('success');
}
if(Session::exists('error')){
  $error = Session::get('error');
  Session::delete('error');
}

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 style="display: inline;">
	    <?php if(isset($_POST['btnSent'])){echo "Inbox messages";}else{ echo "Sent messages";}?>
	  </h1>
	  <ol class="breadcrumb">
	    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
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
								<button type="submit" name="btnSent" class="list-group-item">Sent<span class="badge"><?php echo $sentCount; ?></span></button>
							</div>
						</div>

						<div class="col-sm-10">
				            <form method="post">
				            	<button type="submit" name="btnDelete" id="btnDelete" class="btn btn-danger btn-sm glyphicon glyphicon-trash" onclick="return confirm('Are you sure to delete the message?')" title="Delete these message"  style="display: none; margin-bottom: 5px;"></button>						
					            <table id="msgTable" class="table table-bordered table-striped">
					              <thead>
					                <tr>
					                  <th class="tableCheckbox"><input type="checkbox" id="select-all" onchange="if(this.checked == true){document.getElementById('btnDelete').style.display=''; }else{document.getElementById('btnDelete').style.display='none';}"></th>
					                  <th>To</th>
					                  <th>Subject</th>
					                  <th style="width:20%">Date &amp; time</th>
					                </tr>
					              </thead>
					              <tbody>
					              <?php foreach ($sentData as $value) { ?>
					                <tr>
					                  <td style="width: 45px;"><input type="checkbox" name="chkbox[]" value="<?php echo $value->id;?>" onchange="if(this.checked == true){document.getElementById('btnDelete').style.display=''; }else{document.getElementById('btnDelete').style.display='none';}"></td>             	
					                  <td>
					                  	<a href="view_sent_message?<?php echo $value->id; ?>">
					                  		<?php echo $value->fullname . ' (' . $value->designation . ')'; ?>
					                  	</a>
					                  </td>
					                  <td>
					                  	<a href="view_sent_message?<?php echo $value->id; ?>">
					                  	   <?php echo $value->subject . ' - ' . strip_tags($value->body);?>                  		
					                  	</a>
					                  	<?php if(!empty($value->attachment)){ ?><i class="fa fa-paperclip pull-right"></i><?php } ?>
					                  </td>
					                  <td><?php echo $value->time; ?></td>
					                </tr>
					                <?php } ?>
					              </tbody>
					            </table>
					        </form>
						</div>					
			  		</div>
			    </div>
			  </div><!-- /.box -->
			</div>


		</div>
	 
	</section><!-- /.content -->	
</div><!-- /.content-wrapper -->

<script>
  jQuery(document).ready(function() {
      jQuery('#msgTable').DataTable({
      	order: []
      });
      jQuery('#select-all').click(function(){
      	if(this.checked){
      		jQuery(':checkbox').each(function(){
      			this.checked = true;
      		});
      	}else{
      		jQuery(':checkbox').each(function(){
      			this.checked = false;
      		});
      	}
      });
  } );
</script>  

<script src="xhttp://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.js"></script> 
<script>
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
