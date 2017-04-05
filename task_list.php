<?php

if(!$user->isLoggedIn()){
	Redirect::to('login');
}

if(isset($_POST['btnDelete'])){
	$db->delete('tasks', array('id', '=', input::get('taskid')));
	Session::flash('success', 'Task has been deleted successfully!');
	Redirect::to('task_list');
}

$taskResults = $db->query("SELECT t.*, CONCAT(u.firstname, ' ', u.lastname) AS fullname FROM tasks t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC")->results();
//var_dump($taskResults);

$UserTaskResults = $db->query("SELECT t.*, CONCAT(u.firstname, ' ', u.lastname) AS fullname FROM tasks t LEFT JOIN users u ON t.user_id = u.id WHERE t.user_id = {$user->data()->id} ORDER BY t.created_at DESC")->results();
//var_dump($UserTaskResults);

if(Session::exists('success')){
	$success = Session::get('success');
	Session::delete('success');
}

?>

<script type="text/javascript">
	jQuery(document).ready(function() {
    	jQuery('#taskTable').DataTable( {
    		"order": [],
    	} );
    	
    // Auto remove alert box
	window.setTimeout(function() {
	    jQuery(".alert").fadeTo(2000, 0).slideUp(2000, function(){
	        jQuery(this).remove(); 
	    });
	}, 2000);

});
</script>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 style="display: inline;">Task list</h1>

	  <?php if(!empty($success)){ ?>
	  <div class="alert alert-success fade in text-center col-sm-offset-3" style="padding: 4px; width: 40%; margin-top: -30px; margin-bottom: 0;">
	  	<?php echo $success; ?>
	  	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
	  </div>
	  <?php }?>

	  <ol class="breadcrumb">
	    <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
	    <li class="active"><?php echo $title; ?></li>
	  </ol>	  
	</section>

	<!-- Main content -->
	<section class="content">

		<div class="row">
			<div class="col-sm-12">
				<div class="box box-info">					
					<div class="box-body">

						<?php if($user->data()->isAdmin == 1){?>

			            <table id="taskTable" class="table table-bordered">
			              <thead>
			                <tr>      	
			                  <th>Title</th>
			                  <th>Assigned To</th>
			                  <th>Start Date</th>
			                  <th>End Date</th>
			                  <th>Status</th>
			                  <th style="width: 70px;">Option</th>
			                </tr>
			              </thead>
			              <tbody>
			              <?php foreach ($taskResults as $task) { ?>
			                <tr>
			                	<td>
			                		<a href="view_task?<?php echo $task->id; ?>">
			                			<?php echo $task->title; ?>
			                		</a>
			                  	</td>
			                  	<td>
			                  		<a href="view_task?<?php echo $task->id; ?>">
			                  			<?php echo $task->fullname; ?>
			                  		</a>
			                  	</td>
			                  	<td>
			                  		<a href="view_task?<?php echo $task->id; ?>">
			                  			<?php echo date('jS M, Y', strtotime($task->start_date)); ?>
			                  		</a>
			                  	</td>
			                  	<td>
			                  		<a href="view_task?<?php echo $task->id; ?>">
			                  			<?php echo date('jS M, Y', strtotime($task->end_date)); ?>
			                  		</a>
			                  	</td>
			                  	<td>
			                  		<?php 
				                  		if($task->status == 'Not started'){
				                  			echo '<span class="label label-danger">';
				                  		} elseif ($task->status == 'In progress') {
				                  			echo '<span class="label label-info">';
				                  		}elseif ($task->status == 'Completed') {
				                  			echo '<span class="label label-success">';
				                  		}elseif ($task->status == 'Pending') {
				                  			echo '<span class="label label-warning">';
				                  		}
				                  		echo $task->status;
			                  		?>
			                  		</span>
			                  	</td>
			                  	<td>
			                  		<form action="edit_task" method="post" style="float: left; margin-right: 3px; ">
			                  			<input type="hidden" name="taskid" value="<?php echo $task->id; ?>">
				                  		<button type="submit" name="btnEdit" class="btn btn-warning btn-xs" title="Edit task">
				                  			<span class="glyphicon glyphicon-pencil"></span>
				                  		</button>
			                  		</form>

			                  		<form method="post">
			                  			<input type="hidden" name="taskid" value="<?php echo $task->id; ?>">

				                  		<button type="submit" name="btnDelete" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure want to delete the holiday?')" title="Delete task">
				                  			<span class="glyphicon glyphicon-trash"></span>
				                  		</button>
			                  		</form>
			                  	</td>
			                </tr>
			               <?php } ?>
			              </tbody>
			            </table><!--End admin area-->

			            <?php }else{ ?>

			            <table id="taskTable" class="table table-bordered">
			              <thead>
			                <tr>      	
			                  <th>Title</th>
			                  <th>Start Date</th>
			                  <th>End Date</th>
			                  <th>Status</th>
			                </tr>
			              </thead>
			              <tbody>
			              <?php foreach ($UserTaskResults as $userTask) { ?>
			                <tr>
			                	<td>
			                		<a href="view_task?<?php echo $userTask->id; ?>">
			                			<?php echo $userTask->title; ?>
			                		</a>
			                  	</td>
			                  	<td>
			                  		<a href="view_task?<?php echo $userTask->id; ?>">
			                  			<?php echo date('jS M, Y', strtotime($userTask->start_date)); ?>
			                  		</a>
			                  	</td>
			                  	<td>
			                  		<a href="view_task?<?php echo $userTask->id; ?>">
			                  			<?php echo date('jS M, Y', strtotime($userTask->end_date)); ?>
			                  		</a>
			                  	</td>
			                  	<td>
			                  		<?php 
				                  		if($userTask->status == 'Not started'){
				                  			echo '<span class="label label-danger">';
				                  		} elseif ($userTask->status == 'In progress') {
				                  			echo '<span class="label label-info">';
				                  		}elseif ($userTask->status == 'Completed') {
				                  			echo '<span class="label label-success">';
				                  		}elseif ($userTask->status == 'Pending') {
				                  			echo '<span class="label label-warning">';
				                  		}
				                  		echo $userTask->status;
			                  		?>
			                  		</span>
			                  	</td>
			                </tr>
			               <?php } ?>
			              </tbody>
			            </table><!--End user area-->

			            <?php }?>
			        </div>
		        </div>
			</div>
		</div>
	 
	</section><!-- /.content -->	
</div><!-- /.content-wrapper -->
