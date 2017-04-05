<?php
require_once 'core/init.php';

$user = new User();

if($user->isLoggedIn()){
	$db = DB::getInstance();

	$errors = [];
	$errorFirstName = '';
	$firstname = '';
if(isset($_POST['user_id'])){
	$userid = $_POST['user_id'];
	$rows = $db->get('users', array('id', '=', $userid))->first();
	$hiddenId = $rows->id;
	$firstname = $rows->firstname;
}

?>


			    <form class="form-horizontal" action="userupdate" method="post">
			    	<input type="hidden" name="userid" value="<?php echo $hiddenId; ?>">
			      <div class="box-body col-sm-10">
			        <div class="form-group">
			          <span class="col-sm-3"></span>
			          <span class="success col-sm-6">
			           <?php //echo $success; ?>
			          </span>
			          <span class="error col-sm-3"></span>
			         
			        </div>

			        <div class="form-group">
			          <label for="first_name" class="col-sm-3 control-label">First name:</label>
			          <div class="col-sm-6">
			            <input type="text" name="first_name" id="first_name" class="form-control" value="<?php if(count($firstname)) echo $firstname; ?>"/>
			          </div>
			          <small class="error col-sm-3"><?php if(count($errorFirstName) > 0 ) echo $errorFirstName; ?></small>
			         
			        </div>

			        <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
			        <div class="form-group">
			          <div class="col-sm-3"></div>
			          <div class="col-sm-6">
			            <input type="submit" name="updateBtn" class="btn btn-info btn-block" value="Update">
			          </div>
			          <div class="col-sm-3"></div>
			        </div>
			      </div><!-- /.box-body -->
			      <div class="box-footer">

			      </div><!-- /.box-footer -->
			    </form>
<?php 

} else {
	Redirect::to('/emanagement');
}
?>