<?php
if(!$user->isLoggedIn()){
	Redirect::to('login');
}

$db = DB::getInstance();

// Message delete 

if(isset($_POST['btnDelete']) && empty(Input::get('chkbox'))){
	Session::flash('error', 'Please select a message first!');
	Redirect::to('messages');

}elseif(isset($_POST['btnDelete']) && !empty(Input::get('chkbox'))){
	$ids = implode(',', Input::get('chkbox'));
	$db->query("DELETE FROM messages WHERE id in ({$ids})");	
	Session::flash('success', 'The message has been deleted successfully!');
	Redirect::to('messages');
}
// end of message

if(Session::exists('success')){
  $success = Session::get('success');
  Session::delete('success');
}
if(Session::exists('error')){
  $error = Session::get('error');
  Session::delete('error');
}

// get inbox data

$db->query("SELECT m.*, DATE_FORMAT(m.created_at, '%h:%i %p, %D-%b-%Y') AS time, CONCAT(u.firstname, ' ', u.lastname) AS fullname, u.designation FROM messages m LEFT JOIN users u ON m.from_user_id = u.id WHERE m.to_user_id = {$user->data()->id} ORDER BY m.created_at DESC");
$inboxData = $db->results();

// count read messages
$inboxCount = $db->count();

// count sent messages

$db->get('messages', array('from_user_id', '=', $user->data()->id));
$sentData = $db->results();
$sentCount = $db->count();
//var_dump($getData);

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 style="display: inline;">
	    <?php if(isset($_POST['btnSent'])){echo "Sent messages";}else{ echo "Inbox messages";}?>
	  </h1>
	  
	  <?php if(!empty($success)){ ?>
	  <div class="alert alert-success fade in text-center col-sm-offset-3" style="padding: 4px; width: 40%; margin-top: -30px; margin-bottom: 0;">
	  	<?php echo $success; ?>
	  	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
	  </div>
	  <?php }elseif(!empty($error)){ ?>
	   <div class="alert alert-danger fade in text-center col-sm-offset-3" style="padding: 3px; width: 40%; margin-top: -30px; margin-bottom: 0;">
	  	<?php echo $error; ?>
	  	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
	  </div>
	  <?php } ?>

	  <ol class="breadcrumb">
	    <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
	    <li class="active"><?php echo $title; ?></li>
	  </ol>
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
				            <form method="post">
				            	<button type="submit" name="btnDelete" id="btnDelete" class="btn btn-danger btn-sm glyphicon glyphicon-trash" onclick="return confirm('Are you sure to delete the message?')" title="Delete these message"  style="margin-bottom: 5px; display: none;"></button>
					            <table id="msgTable" class="table table-bordered">
					              <thead>
					                <tr>
					                  <th class="tableCheckbox"><input type="checkbox" id="select-all" onchange="if(this.checked == true){document.getElementById('btnDelete').style.display=''; }else{document.getElementById('btnDelete').style.display='none';}">
					                  </th>       	
					                  <th>From</th>
					                  <th>Subject</th>
					                  <th>Date &amp; time</th>
					                </tr>
					              </thead>
					              <tbody>
					              <?php foreach ($inboxData as $data) { ?>
					                <tr style="background-color: <?php if($data->isRead == 0) echo '#F0F8FF';?>">					                	
					                  <td style="width: 45px;">
					                  	<input type="checkbox" name="chkbox[]" id="chkbox" value="<?php echo $data->id;?>"  onchange="if(this.checked == true){document.getElementById('btnDelete').style.display=''; }else{document.getElementById('btnDelete').style.display='none';}">
					                  </td>
					                  <td>
					                  	<a href="view_inbox_message?<?php echo $data->id; ?>">
					                		<?php echo $data->fullname . ' (' . $data->designation . ')'; ?>
					                	</a>
					                  </td>
					                  <td>
					                  	<a href="view_inbox_message?<?php echo $data->id; ?>">
					                  		<?php echo $data->subject . ' - <span style="color: gray;">' . strip_tags($data->body) . '</span>'; ?>
					                  	</a>
					                  	<?php if(!empty($data->attachment)){ ?><i class="fa fa-paperclip pull-right"></i><?php } ?>
					                  </td>
					                  <td><?php echo $data->time; ?></td>
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