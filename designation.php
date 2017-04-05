<?php
require_once 'core/init.php';

if(!$user->isLoggedIn() || $user->data()->isAdmin != 1){
	Redirect::to('home');
}

$db = DB::getInstance();

$success = '';
$userid = '';
$designation = '';
$errorDesignation = '';

if(isset($_POST['submit'])){
	if(Token::check(Input::get('token'))){

		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'designation' => array(
				'required' => true,
				'min' => 2,
				'max' => 30,
				'unique' => 'designation'
			)
		));

		if($validation->passed()){

			try{
				$db->insert('designation', array(
					'designation' => Input::get('designation'),
					'user_id_inserted' => $user->data()->id,
					'time_inserted' => date('Y-m-d H:i:s')
				));

				Session::flash('success', 'Designation has been submitted successfully!');
				Redirect::to('designation');


			} catch (Exception $e){
				die('There was a problem submitting a designation.');
			}
		} else {
			foreach ($validation->errors() as $error) {
				foreach ($error as $key => $value) {
					$errors[$key] = $value;
				}
			}
			if(!empty($errors['Designation'])) $errorDesignation = $errors['Designation'];

		}
	}
}elseif(isset($_POST['edit'])){
	$userId = Input::get('user_id');
	$data = $db->get('designation', array('id', '=', $userId))->first();
	$userid = $data->id;
	$designation = $data->designation;
} elseif(isset($_POST['update'])){
	$validate = new Validate();
	$validation = $validate->check($_POST, array(
		'designation' => array(
			'required' => true,
			'min' => 2,
			'max' => 30,
			'unique' => 'designation'
		)
	));

	if($validation->passed()){

		try{
			$db->update('designation', Input::get('userid'), array(
				'designation' => Input::get('designation'),
				'user_id_updated' => $user->data()->id,
				'time_updated' => date('Y-m-d H:i:s')
			));

			Session::flash('success', 'Designation has been updated successfully!');
			Redirect::to('designation');			

		} catch (Exception $e){
			die('There was a problem updating a designation.');
		}
	} else {
		foreach ($validation->errors() as $error) {
			foreach ($error as $key => $value) {
				$errors[$key] = $value;
			}
		}
		if(!empty($errors['Designation'])) $errorDesignation = $errors['Designation'];

	}
}elseif(isset($_POST['delete'])){
	//var_dump(Input::get('user_id'));
	$db->delete('designation', array('id', '=', Input::get('user_id')));
	Session::flash('success', 'Designation has been deleted successfully!');
	Redirect::to('designation');

}

// region of get table data
$db->get('designation', array('1', '=', '1'));
$results = $db->results();
// end region of get table data


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
	    Designation
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

		<div class="row">
			<div class="col-md-12">
			  <!-- Horizontal Form -->
			  <div class="box box-info">
				<?php if(count($errorDesignation) > 0 ) echo '<small class="error col-sm-offset-3">' . $errorDesignation . '</small>'; ?>
			  	<div class="box-body col-sm-offset-2">
					<form action="" method="post" class="form-inline">		
						<?php if(!empty($userid)) echo '<input type="hidden" name="userid" value="' . $userid . '">'; ?>				
						<div class="col-sm-10">
							<div class="form-group">
								<label for="designation">Add designation: </label>
								<input type="text" name="designation" id="designation" class="form-control" value="<?php if(!empty($designation)) echo $designation; ?>">
							</div>
							<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
							<?php if(isset($_POST['edit'])){
								echo '<input type="submit" value="Update" name="update" class="btn btn-success">';
							} else { 
								echo '<input type="submit" name="submit" value="Submit" class="btn btn-info">';
							} ?>

						</div>
						<div class="col-sm-2"></div>
					</form>

					<div class="col-sm-5" style="margin-top: 20px;">
					  	<table class="table table-bordered table-striped">
						    <?php if($db->emptyTable()){echo $db->emptyTable(); } else { ?>
						    <thead>
						      <tr>
						        <th>Designation</th>
						        <th colspan="2">Options</th>
						      </tr>
						    </thead>
						    <tbody>
							  	<?php
							  		foreach ($results as $result) { 
							  	?>
						      <tr>
						        <td><?php echo $result->designation; ?></td>
						        <td>
						        	<form action="" method="post">
						        		<input type="hidden" name="user_id" value="<?php echo $result->id; ?>">
						        		<button type="submit" name="edit" class="btn btn-warning btn-xs" title="Edit employee"><span class="glyphicon glyphicon-pencil"></span></button>
						        	</form>
						    	</td>
						        <td>
						        	<form action="" method="post">
						        		<input type="hidden" name="user_id" value="<?php echo $result->id; ?>">
						        		<button type="submit" name="delete" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure to delete this data?')" title="Delete employee"><span class="glyphicon glyphicon-remove"></span></button>
						        	</form>

						        </td>
						      </tr>
						     <?php } ?>
						    </tbody>
						    <tfoot>
						      <tr>
						        <th>Total designation</th>
						        <th colspan="2"><?php echo $db->count(); ?></th>
						      </tr>
						    </tfoot>
						    <?php } ?>
					  	</table>
			  		</div>
			  		<div class="col-sm-5"></div>
			  </div>
			  </div><!-- /.box -->
			</div>


		</div>
	 
	</section><!-- /.content -->

</div><!-- /.content-wrapper -->