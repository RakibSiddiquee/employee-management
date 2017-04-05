<?php
if(!$user->isLoggedIn() || $user->data()->isAdmin != 1){
	Redirect::to('home');
}

$db = DB::getInstance();
$validate = new Validate();

$success = '';
$typeid = '';
$type = '';
$errorType = '';

if(isset($_POST['submit'])){
	if(Token::check(Input::get('token'))){

		$validation = $validate->check($_POST, array(
			'type' => array(
				'required' => true,
				'min' => 2,
				'max' => 30,
				'unique' => 'leave_type'
			)
		));

		if($validation->passed()){

			try{
				$db->insert('leave_type', array(
					'type' => Input::get('type'),
					'user_id_inserted' => $user->data()->id,
					'time_inserted' => date('Y-m-d H:i:s')
				));

				Session::flash('success', 'Type has been submitted successfully!');

			} catch (Exception $e){
				die('There was a problem submitting a type.');
			}
		} else {
			foreach ($validation->errors() as $error) {
				foreach ($error as $key => $value) {
					$errors[$key] = $value;
				}
			}
			if(!empty($errors['Type'])) $errorType = $errors['Type'];

		}
	}
}elseif(isset($_POST['edit'])){
	$data = $db->get('leave_type', array('id', '=', Input::get('typeid')))->first();
	$typeid = $data->id;
	$type = $data->type;
} elseif(isset($_POST['update'])){

	$validation = $validate->check($_POST, array(
		'type' => array(
			'required' => true,
			'min' => 2,
			'max' => 30,
			'unique' => 'leave_type'
		)
	));

	if($validation->passed()){

		try{
			$db->update('leave_type', Input::get('typeid'), array(
				'type' => Input::get('type'),
				'user_id_updated' => $user->data()->id,
				'time_updated' => date('Y-m-d H:i:s')
			));

			Session::flash('success', 'Type has been updated successfully!');

		} catch (Exception $e){
			die('There was a problem updating a type.');
		}
	} else {
		foreach ($validation->errors() as $error) {
			foreach ($error as $key => $value) {
				$errors[$key] = $value;
			}
		}
		if(!empty($errors['Type'])) $errorType = $errors['Type'];

	}
}elseif(isset($_POST['delete'])){
	//var_dump(Input::get('user_id'));
	$db->delete('leave_type', array('id', '=', Input::get('typeid')));
	Session::flash('success', 'Type has been deleted successfully!');
}

// region of table get table data
$db->get('leave_type', array('1', '=', '1'));
$results = $db->results();
// endt region of get table data


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
	    Leave type
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
				<?php if(!empty($errortype)) echo '<small class="error col-sm-offset-3">' . $errortype . '</small>'; ?>
			  	<div class="box-body col-sm-offset-2">
					<form action="" method="post" class="form-inline">			
						<div class="col-sm-10">
							<div class="form-group">
								<label for="type">Add new type: </label>
								<input type="text" name="type" id="type" class="form-control" value="">
							</div>
							<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
							<input type="submit" name="submit" value="Submit" class="btn btn-info">

						</div>
						<div class="col-sm-2"></div>
					</form>

					<div class="col-sm-5" style="margin-top: 20px;">
					  	<table class="table table-bordered table-striped">
					  		<?php if($db->emptyTable()){ echo $db->emptyTable(); } else { ?>
						    <thead>
						      <tr>
						        <th>Leave type</th>
						        <th colspan="2">Options</th>
						      </tr>
						    </thead>
						    <tbody>
							  	<?php foreach ($results as $result) { ?>
						      	<tr>
							    <?php if(isset($_POST['edit']) && ($result->id == $typeid)){ ?>
							    	<tr><td colspan="3">
										<form action="" method="post" class="form-inline">
											<input type="hidden" name="typeid" value="<?php echo $typeid; ?>">
											<div class="form-group col-sm">
												<label for="type">Type: </label>
												<input type="text" name="type" id="type" class="form-control" value="<?php if(!empty($type)) echo $type; ?>">
											</div>
											<input type="hidden" name="token" value="' . Token::generate() . '">
											<input type="submit" value="Update" name="update" class="btn btn-success btn-sm">
										</form>

									</td></tr>
							 		<?php  }else{ ?>

							        <td><?php echo $result->type; ?></td>
							        <td>
							        	<form action="" method="post">
							        		<input type="hidden" name="typeid" value="<?php echo $result->id; ?>">
							        		<button type="submit" name="edit" class="btn btn-warning btn-xs" title="Edit leave type"><span class="glyphicon glyphicon-pencil"></span></button>
							        	</form>
							    	</td>
							        <td>
							        	<form action="" method="post">
							        		<input type="hidden" name="typeid" value="<?php echo $result->id; ?>">
							        		<button type="submit" name="delete" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure to delete this data?')" title="Delete leave type"><span class="glyphicon glyphicon-remove"></span></button>
							        	</form>

							        </td>
						      	</tr>
							 	<?php } } } ?>

						    </tbody>
						    <!--<tfoot>
						      <tr>
						        <th>Total type</th>
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