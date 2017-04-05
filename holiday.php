<?php
if($user->data()->isAdmin != 1){
	Redirect::to('login');
}

$db = DB::getInstance();

// Insert area
if(isset($_POST['btnSave'])){
	if(Token::check(Input::get('token'))){
		try{
			$db->insert('holiday', array(
				'title' => Input::get('description'),				
				'start' => Input::get('date') . ' 00:00:00',
				'created_id' => $user->data()->id,
				'created_at' => date('Y-m-d H:i:s')
			));

			Session::flash('success', 'Holiday has been saved successfully!');

		}catch(Exception $e){
			die('There is a problem save the holiday.');
		}
	}

} elseif (isset($_POST['btnEdit'])) {
	$editData = $db->get('holiday', array('id', '=', Input::get('id')))->first();
	//var_dump($editData);
} elseif (isset($_POST['btnUpdate'])) {
	if(Token::check(Input::get('token'))){
		try {
			$db->update('holiday', Input::get('updateId'), array(
				'title' => Input::get('description'),				
				'start' => Input::get('date') . ' 00:00:00',
				'updated_id' => $user->data()->id,
				'updated_at' => date('Y-m-d H:i:s')
			));

			Session::flash('success', 'Holiday has been updated successfully!');
			Redirect::to('holiday');

		} catch (Exception $e){
			die('There is a problem updating holiday!');
		}
	}
} elseif (isset($_POST['btnDelete'])){
	$db->delete('holiday', array('id', '=', Input::get('id')));	
	Session::flash('success', 'The holiday has been deleted successfully!');
	Redirect::to('holiday');
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

$db->query("SELECT * FROM holiday ORDER BY start DESC");
$results = $db->results();

?>

<script type="text/javascript">
	jQuery(document).ready(function() {
    	jQuery('#holidayTable').DataTable( {
    		"order": []
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
	  <h1 style="display: inline;">
	    <?php if(isset($_POST['btnEdit'])){echo 'Edit holiday';}else{echo 'Holidays';}?>
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
			<?php if(isset($_POST['btnEdit'])){ ?> <!--Edit Area-->
				<div class="col-md-12">
					<div class="box box-info">
						<div class="col-sm-offset-2">
							<div class="box-body">
								<form action="" method="post" role="form" class="col-sm-6">
									<input type="hidden" name="updateId" value="<?php echo $editData->id; ?>">
								    <div class="form-group">
								    	<label for="datepicker">Date: </label>
								    	<input type="text" name="date" class="form-control" id="datepicker" placeholder="yyyy-mm-dd" value="<?php if(!empty($editData->start)) echo $editData->start; ?>" required>
								    </div>
								    <div class="form-group">
								      	<label for="description">Description:</label>
							           	<textarea name="description" rows="5" id="description" class="form-control" value="" required></textarea>
							           	<script type="text/javascript">
							           		document.getElementById('description').value = "<?php if(!empty($editData->title)) echo $editData->title; ?>";
							           	</script>
								    </div>
							        <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
							      	<input type="submit" name="btnUpdate" class="btn btn-primary" value="Update">
								</form>
							</div>
						</div>
					</div>				<!--End edit area-->
				</div>
			<?php }else{ ?>	
				<div class="col-sm-4">
					<div class="box box-info">
						<div class="box-body">
							<h4 style="border-bottom: 2px solid lightgray; padding-bottom: 10px;"><strong>Add new</strong></h4>
							<!-- form start -->
							<form action="" method="post" role="form">
							    <div class="form-group">
							    	<label for="datepicker">Date: </label>
							    	<input type="text" name="date" class="form-control" id="datepicker" placeholder="yyyy-mm-dd" required>
							    </div>
							    <div class="form-group">
							      	<label for="description">Description:</label>
						           	<textarea name="description" rows="5" id="description" class="form-control" value="" required></textarea>
							    </div>
						        <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
						      	<input type="submit" name="btnSave" class="btn btn-primary" value="Save">
							</form>
						</div>
					</div>
				</div>

				<div class="col-sm-8">
					<div class="box box-info">					
						<div class="box-body">
				            <table id="holidayTable" class="table table-bordered">
				              <thead>
				                <tr>      	
				                  <th>Date</th>
				                  <th>Description</th>
				                  <th style="width: 70px;">Option</th>
				                </tr>
				              </thead>
				              <tbody>
				              <?php foreach ($results as $data) { ?>
				                <tr>
				                	<td>
				                		<?php echo date('jS M, Y', strtotime($data->start)); ?>
				                  	</td>
				                  	<td>
				                  		<?php echo $data->title; ?>
				                  	</td>
				                  	<td>
				                  		<form action="" method="post">
				                  			<input type="hidden" name="id" value="<?php echo $data->id; ?>">
					                  		<button type="submit" name="btnEdit" class="btn btn-warning btn-xs">
					                  			<span class="glyphicon glyphicon-pencil"></span>
					                  		</button>
					                  		<button type="submit" name="btnDelete" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure want to delete the holiday?')">
					                  			<span class="glyphicon glyphicon-remove"></span>
					                  		</button>
				                  		</form>
				                  	</td>
				                </tr>
				               <?php } ?>
				              </tbody>
				            </table>
				        </div>
			        </div>
				</div>
			<?php } ?>
		</div>
	 
	</section><!-- /.content -->	
</div><!-- /.content-wrapper -->
