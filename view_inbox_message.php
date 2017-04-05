<?php

if(!$user->isLoggedIn()){
	Redirect::to('home');
}

$db = DB::getInstance();

$url = explode("/", $_SERVER['REQUEST_URI']);
$msg_id = substr($url[count($url) - 1],strpos($url[count($url) - 1], '?')+1);


$result = $db->query("SELECT m.*, DATE_FORMAT(m.created_at, '%D-%b-%Y, %h:%i %p') AS time, CONCAT(u.firstname, ' ', u.lastname) AS fullname, u.designation, u.email FROM messages m LEFT JOIN users u ON m.from_user_id = u.id WHERE m.id = {$msg_id} ORDER BY m.created_at DESC")->first();
//var_dump($result);

$db->update('messages', $msg_id, array('isRead' => 1));

$inboxData = $db->query("SELECT COUNT(id) AS total FROM messages WHERE to_user_id = {$user->data()->id}")->first();
$inboxCount = $inboxData->total;

$db->get('messages', array('from_user_id', '=', $user->data()->id));
$sentData = $db->results();
$sentCount = $db->count();
// delete message

if(isset($_POST['btnDelete'])){
	$db->delete('messages', array('id', '=', Input::get('deleteId')));
	Session::flash('success', 'Message has been deleted successfully!');
	Redirect::to('messages');
}

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 style="display: inline;">
	    <?php if(isset($_POST['btnSent'])){echo "Sent messages";}else{ echo "Inbox messages";}?>
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
							<a href="compose" class="btn btn-warning btn-block md-trigger">Compose</a>
							<div class="list-group" style="margin: 20px 0 0">
								<a href="messages" name="btnInbox" class="list-group-item active">Inbox<span class="badge"><?php echo $inboxCount; ?></span></a>
								<a href="sent_messages" class="list-group-item">Sent<span class="badge"><?php echo $sentCount; ?></span></a>
							</div>
						</div>

						<div class="col-sm-10">
							<form method="post" style="margin-bottom: 5px;">
								<input type="hidden" name="deleteId" value="<?php echo $result->id; ?>">
								<button type="submit" name="btnDelete" class="btn btn-danger btn-sm glyphicon glyphicon-trash" onclick="return confirm('Are you sure to delete the message?')" title="Delete this message"></button>
							</form>
							<table class="table table-striped" style="border: 1px solid lightgray;">
							    <thead>
							      <tr>
							        <th class="col-sm-2">From:</th>
							        <th><?php echo $result->fullname . ' (' . $result->designation . ')'; ?><small class="pull-right"><em><?php echo $result->email; ?></em></small></th>
							      </tr>

							    </thead>
							    <tbody>
							      <tr style="font-weight: bold;">
							        <td class="col-sm-2">Subject:</td>
							        <td><em><?php echo $result->subject; ?></em></td>
							      </tr>							    	
							      <tr>
							        <td colspan="2"><?php echo $result->body;?></td>
							      </tr>
							      <?php if(!empty($result->attachment)){?>
							      <tr style="font-weight: bold;">
							        <td colspan="2">
							          <img src="uploads/files/<?php echo $result->attachment; ?>" alt="Attachment" style="width: 200px;">
							          <a href="uploads/files/<?php echo $result->attachment; ?>" target="_blank" style="display: block;">
							            <i class="fa fa-download fa-lg"></i>
							          </a>
							        </td>
							      </tr>							    	
							      <?php } ?>
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
