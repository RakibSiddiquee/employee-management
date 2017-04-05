<?php
	if(!$user->isLoggedIn() && $user->data()->isAdmin != 1){
		Redirect::to('/emanagement');
	}

	if(isset($_POST['delete'])){
		$user_id = $_POST['user_id'];
		
		$db = DB::getInstance();
		
		$db->delete('users', array('id', '=', $user_id));

		Session::flash('success', 'Employee has been deleted successfully!');
		Redirect::to('employee_list');
	}

?>