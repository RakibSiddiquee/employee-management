<?php
if(!$user->isLoggedIn() || $user->data()->isAdmin != 1){
	Redirect::to('home');
}

$db = DB::getInstance();
$validate = new Validate();

$success = '';
$statusid = '';
$status = '';
$errorStatus = '';

if(isset($_POST['submit'])){
	if(Token::check(Input::get('token'))){

		$validation = $validate->check($_POST, array(
			'status' => array(
				'required' => true,
				'min' => 2,
				'max' => 30,
				'unique' => 'leave_status'
			)
		));

		if($validation->passed()){

			try{
				$db->insert('leave_status', array(
					'status' => Input::get('status'),
					'user_id_inserted' => $user->data()->id,
					'time_inserted' => date('Y-m-d H:i:s')
				));

				Session::flash('success', 'Leave status has been submitted successfully!');

			} catch (Exception $e){
				die('There was a problem submitting a status.');
			}
		} else {
			foreach ($validation->errors() as $error) {
				foreach ($error as $key => $value) {
					$errors[$key] = $value;
				}
			}
			if(!empty($errors['Status'])) $errorStatus = $errors['Status'];

		}
	}
}elseif(isset($_POST['edit'])){
	$data = $db->get('leave_status', array('id', '=', Input::get('statusid')))->first();
	$statusid = $data->id;
	$status = $data->status;
} elseif(isset($_POST['update'])){

	$validation = $validate->check($_POST, array(
		'status' => array(
			'required' => true,
			'min' => 2,
			'max' => 30,
			'unique' => 'leave_status'
		)
	));

	if($validation->passed()){

		try{
			$db->update('leave_status', Input::get('statusId'), array(
				'status' => Input::get('status'),
				'user_id_updated' => $user->data()->id,
				'time_updated' => date('Y-m-d H:i:s')
			));

			Session::flash('success', 'Leave status has been updated successfully!');

		} catch (Exception $e){
			die('There was a problem updating a status.');
		}
	} else {
		foreach ($validation->errors() as $error) {
			foreach ($error as $key => $value) {
				$errors[$key] = $value;
			}
		}
		if(!empty($errors['Status'])) $errorStatus = $errors['Status'];

	}
}elseif(isset($_POST['delete'])){
	//var_dump(Input::get('user_id'));
	$db->delete('leave_status', array('id', '=', Input::get('statusid')));
	Session::flash('success', 'Leave status has been deleted successfully!');
}

// get table data

$db->get('leave_status', array('1', '=', '1'));
$results = $db->results();
// end region of table data

if(Session::exists('success')){
	$success = Session::get('success');
	Session::delete('success');
}

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->
	<section class="content-header">
	  <h1 style="display: inline;">
	    Leave status
	  </h1>
	  <!--<ol class="breadcrumb">
	    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
	    <li class="active">Dashboard</li>
	  </ol>-->
	  <?php if(!empty($success)) echo '<span class="success col-sm-offset-1">' . $success . '</span>'; ?>

 
	</section>

	<!-- Main content -->
	<section class="content">

		<div class="row">
			<div class="col-md-12">
			  <!-- Horizontal Form -->
			  <div class="box box-info">
				<?php if(!empty($errorStatus)) echo '<small class="error col-sm-offset-3">' . $errorStatus . '</small>'; ?>
			  	
			  	<div class="box-body col-sm-offset-2">
					<form action="" method="post" class="form-inline">			
						<div class="col-sm-10">
							<div class="form-group">
								<label for="status">Add leave status: </label>
								<input type="text" name="status" id="status" class="form-control" value="">
							</div>
							<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
							<input type="submit" name="submit" value="Submit" class="btn btn-info">

						</div>
						<div class="col-sm-2"></div>
					</form>

					<div class="col-sm-5" style="margin-top: 20px;">
					  	<table class="table table-bordered table-striped">
					  		<?php
					  			if($db->emptyTable()){
									echo $db->emptyTable();
								} else {
					  		?>
						    <thead>
						      <tr>
						        <th>Leave status</th>
						        <th colspan="2">Options</th>
						      </tr>
						    </thead>
						    <tbody>
							  	<?php foreach ($results as $result) { ?>
						      	<tr>
							    <?php if(isset($_POST['edit']) && ($result->id == $statusid)){ ?>
							    	<tr><td colspan="3">
										<form action="" method="post" class="form-inline">
											<input type="hidden" name="statusId" value="<?php echo $statusid; ?>">
											<div class="form-group col-sm">
												<label for="status">Status name: </label>
												<input type="text" name="status" id="status" class="form-control" value="<?php if(!empty($status)) echo $status; ?>">
											</div>
											<input type="hidden" name="token" value="' . Token::generate() . '">
											<input type="submit" value="Update" name="update" class="btn btn-success btn-sm">
										</form>

									</td></tr>
							 		<?php  }else{ ?>

							        <td><?php echo $result->status; ?></td>
							        <td>
							        	<form action="" method="post">
							        		<input type="hidden" name="statusid" value="<?php echo $result->id; ?>">
							        		<button type="submit" name="edit" class="btn btn-warning btn-xs" title="Edit leave category"><span class="glyphicon glyphicon-pencil"></span></button>
							        	</form>
							    	</td>
							        <td>
							        	<form action="" method="post">
							        		<input type="hidden" name="statusid" value="<?php echo $result->id; ?>">
							        		<button type="submit" name="delete" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure to delete this data?')" title="Delete leave category"><span class="glyphicon glyphicon-remove"></span></button>
							        	</form>

							        </td>
						      	</tr>
							 	<?php } } } ?>

						    </tbody>
						    <!--<tfoot>
						      <tr>
						        <th>Total category</th>
						        <th colspan="2"><?php echo $db->count(); ?></th>
						      </tr>
						    </tfoot>-->
					  	</table>
			  		</div>
			  		<div class="col-sm-5"></div>
			  </div>
			  </div><!-- /.box -->
			</div>


		</div>
	 
	</section><!-- /.content -->

</div><!-- /.content-wrapper -->