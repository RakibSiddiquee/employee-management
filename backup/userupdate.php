<?php
require_once 'core/init.php';

$user = new User();

if($user->isLoggedIn()){
	$db = DB::getInstance();

if(isset($_POST['updateBtn'])){
	$user_id = $_POST['userid'];
	if(Token::check(Input::get('token'))){

		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'first_name' => array(
				'required' => true,
				'min' => 2,
				'max' => 20,
			)
		));

		if($validation->passed()){
			$salt = Hash::salt(32);

			try{
				$db->update('users', $user_id, array(
					'firstname' => Input::get('first_name')
				));

				Session::flash('home', 'You have been registered and can now log in!');
				Redirect::to('userlist');

			} catch(Exception $e){
				die($e->getMessage());
			}

		} else {
			foreach ($validation->errors() as $error) {
				foreach ($error as $key => $value) {
					$errors[$key] = $value;
				}
			}
			if(!empty($errors['First name'])) $errorFirstName = $errors['First name'];
		}
	}
}

}
?>