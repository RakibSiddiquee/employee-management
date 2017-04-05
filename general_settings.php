<?php
if(!$user->isLoggedIn() || $user->data()->isAdmin != 1){
	Redirect::to('home');
}

$db = DB::getInstance();
$validate = new Validate();

$success = '';
$error = '';
$settingsId = '';

$errorCompanyName = '';
$errorCompanyLogo = '';
$errorEmailAddress = '';
$errorTimeZone = '';
$errorStartTime = '';
$errorCloseTime = '';
$errorWeeklyHoliday = '';

if(isset($_POST['submit'])){
	if(Token::check(Input::get('token'))){

		$validation = $validate->check($_POST, array(
			'company_name' => array(
				'required' => true,
				'min' => 2,
				'max' => 30,
			),
			'email_address' => array(
				'required' => true,
			),			
			'time_zone' => array(
				'required' => true,
			),
			'start_time' => array(
				'required' => true,
			),
			'close_time' => array(
				'required' => true,
			)
		));

		$validationFile = $validate->checkFile('company_logo', 'required', array('jpg', 'gif', 'png'), 50);
		//var_dump($validationFile->errors());

		if($validation->passed() && empty($validationFile->errors())){
			$dir = "uploads/";
			$target_dir = $dir . basename($_FILES['company_logo']['name']);

			try{
				$db->insert('settings', array(
					'company_name' => Input::get('company_name'),
					'company_logo' => $_FILES['company_logo']['name'],
					'company_email' => Input::get('email_address'),
					'time_zone' => Input::get('time_zone'),
					'start_time' => Input::get('start_time'),
					'close_time' => Input::get('close_time'),
					'records_perpage' => Input::get('records_perpage'),
					'maint_mode' => Input::get('maint_mode'),
					'maint_msg' => Input::get('maint_msg'),
					'user_id_inserted' => $user->data()->id,
					'time_inserted' => date('Y-m-d H:i:s')
				));

				move_uploaded_file($_FILES['company_logo']['tmp_name'], $target_dir);

				Session::flash('success', 'Settings have been submitted successfully!');

			} catch (Exception $e){
				die('There was a problem creating a settings.');
			}
		} else {
			foreach ($validation->errors() as $error) {
				foreach ($error as $key => $value) {
					$errors[$key] = $value;
				}
			}
			if(!empty($errors['Company name'])) $errorCompanyName = $errors['Company name'];
			if(!empty($errors['Company logo'])) $errorCompanyLogo = $errors['Company logo'];
			if(!empty($errors['Email address'])) $errorEmailAddress = $errors['Email address'];
			if(!empty($errors['Time zone'])) $errorTimeZone = $errors['Time zone'];
			if(!empty($errors['Start time'])) $errorStartTime = $errors['Start time'];
			if(!empty($errors['Close time'])) $errorCloseTime = $errors['Close time'];
		}
	}
}elseif(isset($_POST['update'])){
	if(Token::check(Input::get('token'))){

		$validation = $validate->check($_POST, array(
			'company_name' => array(
				'required' => true,
				'min' => 2,
				'max' => 30,
			),
			'email_address' => array(
				'required' => true,
			),			
			'time_zone' => array(
				'required' => true,
			),
			'start_time' => array(
				'required' => true,
			),
			'close_time' => array(
				'required' => true,
			)
		));
		$image = '';
		$validationFile = $validate->checkFile('company_logo', null, array('jpg', 'gif', 'png'), 50);
		if(empty($validationFile->errors()))$image = $_FILES['company_logo']['name'];

		if($validation->passed() && empty($validationFile->errors())){
			$dir = "uploads/";
			$db->get('settings', array('1', '=', '1'));
			$extPic = $db->first()->company_logo;
			if(!empty($extPic)){
				if(empty($image) || $extPic == $image){
					$image = $extPic;
				}elseif($extPic != $image){
					unlink($dir . $extPic);
				}
			}
			$target_dir = $dir . basename($image);

			$weekly_holiday = !empty(Input::get('weekly_holiday')) ? implode(',', Input::get('weekly_holiday')) : null;

			try{
				$db->update('settings', Input::get('settingsid'), array(
					'company_name' => Input::get('company_name'),
					'company_logo' => $image,
					'company_email' => Input::get('email_address'),
					'time_zone' => Input::get('time_zone'),
					'start_time' => Input::get('start_time'),
					'close_time' => Input::get('close_time'),
					'wholiday' => $weekly_holiday,
					'records_perpage' => Input::get('records_perpage'),
					'maint_mode' => Input::get('maint_mode'),
					'maint_msg' => Input::get('maint_msg'),
					'user_id_updated' => $user->data()->id,
					'time_updated' => date('Y-m-d H:i:s')
				));

				if(!empty($image))move_uploaded_file($_FILES['company_logo']['tmp_name'], $target_dir);

				Session::flash('success', 'Settings have been updated successfully!');

			} catch (Exception $e){
				die('There was a problem updating a settings.');
			}
		} else {
			foreach ($validation->errors() as $error) {
				foreach ($error as $key => $value) {
					$errors[$key] = $value;
				}
			}
			if(!empty($errors['Company name'])) $errorCompanyName = $errors['Company name'];
			if(!empty($errors['Company logo'])) $errorCompanyLogo = $errors['Company logo'];
			if(!empty($errors['Email address'])) $errorEmailAddress = $errors['Email address'];
			if(!empty($errors['Time zone'])) $errorTimeZone = $errors['Time zone'];
			if(!empty($errors['Start time'])) $errorStartTime = $errors['Start time'];
			if(!empty($errors['Close time'])) $errorCloseTime = $errors['Close time'];
			if(!empty($errors['Weekly holiday'])) $errorWeeklyHoliday = $errors['Weekly holiday'];
		}
	}
}

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
	    General settings
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
			  	<div class="box-body">

					<div class="col-sm-12">
						<?php
							$getData = $db->get('settings', array('1', '=', '1'));
							if(empty($getData->count())){
						?>

						<form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
							<div class="row">

								<div class="form-group">
									<label for="company_name" class="col-sm-3 control-label">Company name: </label>
									<div class="col-sm-5">
			           					<input type="text" name="company_name" id="company_name" class="form-control" value="" placeholder="Company name"/>
			           				</div>
									<small class="error col-sm-4"><?php if(!empty($errorCompanyName)) echo $errorCompanyName; ?></small>
								</div>
								<div class="form-group">
									<label for="company_logo" class="col-sm-3 control-label">Company logo: </label>
									<div class="col-sm-5">
			           					<input type="file" name="company_logo" id="company_logo" class="form-control" value="" placeholder="Company name"/>
			           				</div>
									<small class="error col-sm-4"><?php if(!empty($errorCompanyLogo)) echo $errorCompanyLogo; ?></small>
								</div>
								<div class="form-group">
									<label for="email_address" class="col-sm-3 control-label">Email Address: </label>
									<div class="col-sm-5">
			           					<input type="email" name="email_address" id="email_address" class="form-control" value="" placeholder="Email address"/>
			           				</div>
									<small class="error col-sm-4"><?php if(!empty($errorEmailAddress)) echo $errorEmailAddress; ?></small>
								</div>
								<div class="form-group">
									<label for="time_zone" class="col-sm-3 control-label">Time zone: </label>
									<div class="col-sm-5">
						            	<select name="time_zone" id="time_zone" class="form-control">
											<option value="">Choose a time zone</option>	
											<?php foreach (timezone_identifiers_list(4095) as $timezone){ ?>          		
											<option value="<?php echo $timezone; ?>">
												<?php echo $timezone; ?>
											</option>
											<?php } fclose($file); ?>
										</select>
									</div>
									<small class="error col-sm-4"><?php if(!empty($errorTimeZone)) echo $errorTimeZone; ?></small>
								</div>
								<div class="form-group">
									<label for="start_time" class="col-sm-3 control-label">Start time: </label>
									<div class="col-sm-2">									
			           					<input type="time" name="start_time" id="start_time" class="form-control" value="" placeholder="Start time"/>
			           				</div>
									<label for="close_time" class="col-sm-1 control-label" style="padding: 7px 10px 0">Close time: </label>
									<div class="col-sm-2">									
			           					<input type="time" name="close_time" id="close_time" class="form-control" value="" placeholder="Close time"/>
			           				</div>
									<small class="error col-sm-3">
										<?php
											if(!empty($errorStartTime)) echo $errorStartTime . '<br/>';
											if(!empty($errorCloseTime)) echo $errorCloseTime;
										?>
									</small>			           				
								</div>

								<div class="form-group">
									<label for="records_perpage" class="col-sm-3 control-label">Records per page: </label>
									<div class="col-sm-2">
						            	<select name="records_perpage" id="records_perpage" class="form-control">
											<option value="10">10</option>         		
											<option value="20">20</option>
											<option value="50">50</option>
											<option value="100">100</option>
										</select>
			           				</div>
									<small class="error col-sm-4"><?php if(!empty($errorRecordPerpage)) echo $errorRecordPerpage; ?></small>
								</div>

							    <!--<div class="form-group">
							      <div class="col-sm-offset-3 col-sm-8">
							        <div class="checkbox">
							        	<label for="maint_mode">
						       				<input type="checkbox" name="maint_mode" id="maint_mode" value="1" onchange="if(this.checked == true){document.getElementById('mMode').style.display=''; }else{document.getElementById('mMode').style.display='none';}">Maintenance mode
						       			</label>
							        </div>
							      </div>
							    </div>
						        <div class="form-group" id="mMode" style="display: none;">
						          <label for="maint_msg" class="col-sm-3 control-label">Maintenance message:</label>
						          <div class="col-sm-5">
						            <textarea name="maint_msg" id="maint_msg" class="form-control" rows="5" placeholder="Maintenance message"></textarea>
						            <script type="text/javascript">
						            	document.getElementById('maint_msg').value = "<?php echo $editData->maint_msg; ?>";
						            </script>

						          </div>
						          <small class="error col-sm-3"><?php if(!empty($errorMaintenanceMessage)) echo $errorMaintenanceMessage; ?></small>
						        </div>-->
								
						        <div class="form-group">
						        	<div class="col-sm-3"></div>
						        	<div class="col-sm-6">
										<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
										<input type="submit" name="submit" value="Save" class="btn btn-info">
									</div>
			         				 <div class="col-sm-3"></div>
								</div>

							</div>
						</form>
<!--End Insert region-->

<!--Start Edit region-->
						<?php } else {

					  		$db->get('settings', array('1', '=', '1'));
							$result = $db->first();
							$wholiday = explode(',', $result->wholiday);

						?>

						<form action="" method="post" class="form-horizontal" enctype="multipart/form-data">
							<input type="hidden" name="settingsid" value="<?php echo $result->id; ?>">
							<div class="row">
								<div class="form-group">
									<label for="company_name" class="col-sm-3 control-label">Company name: </label>
									<div class="col-sm-5">
			           					<input type="text" name="company_name" id="company_name" class="form-control" value="<?php echo $result->company_name; ?>" placeholder="Company name"/>
			           				</div>
									<small class="error col-sm-4"><?php if(!empty($errorCompanyName)) echo $errorCompanyName; ?></small>
								</div>
								<div class="form-group" style="display: <?php if(empty($result->company_logo)) echo 'none'; ?>">
									<label for="company_logo" class="col-sm-3 control-label">Company logo: </label>
									<div class="col-sm-5">
			           					<img src="uploads/<?php echo $result->company_logo; ?>" class="tableLogo" alt="Logo">
			           				</div>
								</div>
								<div class="form-group">
									<label for="company_logo" class="col-sm-3 control-label">Change logo: </label>
									<div class="col-sm-5 inputfile">
			           					<input type="file" name="company_logo" id="company_logo" class="btn btn-default btn-file" value="" placeholder="Company logo"/>
			           				</div>
									<small class="error col-sm-4"><?php if(!empty($errorCompanyLogo)) echo $errorCompanyLogo; ?></small>
								</div>

								<div class="form-group">
									<label for="email_address" class="col-sm-3 control-label">Email Address: </label>
									<div class="col-sm-5">
			           					<input type="email" name="email_address" id="email_address" class="form-control" value="<?php echo $result->company_email; ?>" placeholder="Email address"/>
			           				</div>
									<small class="error col-sm-4"><?php if(!empty($errorEmailAddress)) echo $errorEmailAddress; ?></small>
								</div>
								<div class="form-group">
									<label for="time_zone" class="col-sm-3 control-label">Time zone: </label>
									<div class="col-sm-5">
						            	<select name="time_zone" id="time_zone" class="form-control">
						            		<?php
												$file = fopen('includes/timezone/timezone.csv', 'r');
												$file = fgetcsv($file);
												sort($file)
											?>
											<option>Choose a time zone</option>	
											<?php foreach ($file as $timezone){ ?>          		
											<option value="<?php echo $timezone; ?>" <?php if($result->time_zone == $timezone) echo 'selected'; ?>>
												<?php echo $timezone; ?>
											</option>
											<?php } fclose($file); ?>
										</select>
									</div>
									<small class="error col-sm-4"><?php if(!empty($errorTimeZone)) echo $errorTimeZone; ?></small>
								</div>
								<div class="form-group">
									<label for="start_time" class="col-sm-3 control-label">Start time: </label>
									<div class="col-sm-2">									
			           					<input type="time" name="start_time" id="start_time" class="form-control" value="<?php echo $result->start_time; ?>" placeholder="Start time"/>
			           				</div>
									<label for="close_time" class="col-sm-1 control-label" style="padding: 7px 10px 0">Close time: </label>
									<div class="col-sm-2">									
			           					<input type="time" name="close_time" id="close_time" class="form-control" value="<?php echo $result->close_time; ?>" placeholder="Close time"/>
			           				</div>
									<small class="error col-sm-3">
										<?php
											if(!empty($errorStartTime)) echo $errorStartTime . '<br/>';
											if(!empty($errorCloseTime)) echo $errorCloseTime;
										?>
									</small>			           				

								</div>
								<div class="form-group">
									<label for="" class="col-sm-3 control-label">Weekly holiday: </label>
									<div class="col-sm-7">
		           					    <label class="checkbox-inline">
									      <input type="checkbox" name="weekly_holiday[]" value="Sunday" <?php if(in_array('Sunday', $wholiday)) echo 'checked';?>>Sunday
									    </label>
									    <label class="checkbox-inline">
									      <input type="checkbox" name="weekly_holiday[]" value="Monday" <?php if(in_array('Monday', $wholiday)) echo 'checked';?>>Monday
									    </label>
									    <label class="checkbox-inline">
									      <input type="checkbox" name="weekly_holiday[]" value="Tuesday" <?php if(in_array('Tuesday', $wholiday)) echo 'checked';?>>Tuesday
									    </label>
		           					    <label class="checkbox-inline">
									      <input type="checkbox" name="weekly_holiday[]" value="Wednesday" <?php if(in_array('Wednesday', $wholiday)) echo 'checked';?>>Wednesday
									    </label>
									    <label class="checkbox-inline">
									      <input type="checkbox" name="weekly_holiday[]" value="Thursday" <?php if(in_array('Thursday', $wholiday)) echo 'checked';?>>Thursday
									    </label>
									    <label class="checkbox-inline">
									      <input type="checkbox" name="weekly_holiday[]" value="Friday" <?php if(in_array('Friday', $wholiday)) echo 'checked';?>>Friday
									    </label>
									    <label class="checkbox-inline">
									      <input type="checkbox" name="weekly_holiday[]" value="Saturday" <?php if(in_array('Saturday', $wholiday)) echo 'checked';?>>Saturday
									    </label>
			           				</div>
									<small class="error col-sm-2"><?php if(!empty($errorEmailAddress)) echo $errorWeeklyHoliday; ?></small>
								</div>

								<div class="form-group">
									<label for="records_perpage" class="col-sm-3 control-label">Records per page: </label>
									<div class="col-sm-2">
						            	<select name="records_perpage" id="records_perpage" class="form-control">
											<option value="10" <?php if($result->records_perpage == 10) echo 'selected';?>>10</option>         		
											<option value="20" <?php if($result->records_perpage == 20) echo 'selected';?>>20</option>
											<option value="50" <?php if($result->records_perpage == 50) echo 'selected';?>>50</option>
											<option value="100" <?php if($result->records_perpage == 100) echo 'selected';?>>100</option>
										</select>
			           				</div>
									<small class="error col-sm-4"><?php if(!empty($errorRecordPerpage)) echo $errorRecordPerpage; ?></small>
								</div>

							    <!--<div class="form-group">
							      <div class="col-sm-offset-3 col-sm-8">
							        <div class="checkbox">
							        	<label for="maint_mode">
						       				<input type="checkbox" name="maint_mode" id="maint_mode" value="1" <?php if($result->maint_mode == 1) echo 'checked';?> onchange="if(this.checked == true){document.getElementById('mMode').setAttribute('style', 'display: visible;'); }else{document.getElementById('mMode').style.display='none';}">Maintenance mode
						       			</label>
							        </div>
							      </div>
							    </div>
						        <div class="form-group" id="mMode" style="display: <?php if($result->maint_mode == 1){echo 'visible';}else{echo 'none';} ?>">
						          <label for="maint_msg" class="col-sm-3 control-label">Maintenance message:</label>
						          <div class="col-sm-5">
						            <textarea name="maint_msg" id="maint_msg" class="form-control" rows="5" placeholder="Maintenance message"></textarea>
						            <script type="text/javascript">
						            	document.getElementById('maint_msg').value = "<?php echo $result->maint_msg; ?>";
						            </script>

						          </div>
						          <small class="error col-sm-3"><?php if(!empty($errorMaintenanceMessage)) echo $errorMaintenanceMessage; ?></small>
						        </div>-->

						        <div class="form-group">
						        	<div class="col-sm-3"></div>
						        	<div class="col-sm-6">
										<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
										<input type="submit" name="update" value="Save" class="btn btn-primary">
									</div>
			         				 <div class="col-sm-3"></div>
								</div>

							</div>
						</form>
<!--End edit region -->						
						<?php }?>				
			  		</div>
			  	</div>
			  </div><!-- /.box -->
			</div>


		</div>
	 
	</section><!-- /.content -->

</div><!-- /.content-wrapper -->