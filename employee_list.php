<?php
//$user = new User();
if(!$user->isLoggedIn() || $user->data()->isAdmin != 1){
	Redirect::to('login');

}
	if(Session::exists('success')){
		$success = Session::get('success');
		Session::delete('success');
	}

	$db = DB::getInstance();
	$db->query("SELECT u.*, DATE_FORMAT(u.joined, '%D %b, %Y') AS joining_date, d.designation FROM users u LEFT JOIN designation d ON u.designation = d.designation ORDER BY d.designation");
	$results = $db->results();
?>

<script type="text/javascript">
  jQuery(document).ready(function() {
      jQuery('#employeeTable').DataTable({
      	"order": []
      });
  });
</script>

<div class="content-wrapper">
<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 class="sectionTitle">
	   Employee list
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
			  <table id="employeeTable" class="table table-bordered table-striped">
			  	<?php if($db->emptyTable()){echo $db->emptyTable(); } else { ?>
			    <thead>
			      <tr>
			        <th>First name</th>
			        <th>Last name</th>
			        <th>Username</th>
			        <th>Email</th>
			        <th>Contact number</th>
			        <th>Address</th>
			        <th>Designation</th>
			        <th>Joined</th>
			        <th>Status</th>
			        <th xcolspan="3">Options</th>
			      </tr>
			    </thead>

			    <tbody>
			    <?php foreach ($results as $result) { ?>
			      <tr>
			        <td><?php echo $result->firstname; ?></td>
			        <td><?php echo $result->lastname; ?></td>
			        <td><?php echo $result->username; ?></td>
			        <td><?php echo $result->email; ?></td>
			        <td><?php echo $result->contact_number; ?></td>
			        <td><?php echo $result->address; ?></td>
			        <td><?php echo $result->designation; ?></td>
			        <td><?php echo $result->joining_date; ?></td>
			        <td><?php if($result->status == 1){ ?>
			        	<span class="glyphicon glyphicon-ok-sign"></span>
			        	<?php } else { ?>
			        	<span class="glyphicon glyphicon-remove-sign"></span>
						<?php } ?>
			        </td>
			        <td style="width: 9%;">
			        	<form action="employee_edit" method="post" style="display: inline;">
			        		<input type="hidden" name="user_id" value="<?php echo $result->id; ?>">
			        		<button type="submit" name="edit" class="btn btn-warning btn-xs" title="Edit employee"><span class="glyphicon glyphicon-pencil"></span></button>
			        	</form>
			    	
			        	<form action="change_password" method="post" style="display: inline;">
			        		<input type="hidden" name="user_id" value="<?php echo $result->id; ?>">
			        		<button type="submit" name="passwordEdit" class="btn btn-warning btn-xs" title="Change password"><span class="glyphicon glyphicon-lock"></span></button>
			        	</form>
			    
			        	<form action="userdelete" method="post" style="display: inline;">
			        		<input type="hidden" name="user_id" value="<?php echo $result->id; ?>">
			        		<button type="submit" name="delete" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure to delete this data?')" title="Delete employee"><span class="glyphicon glyphicon-remove"></span></button>
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
