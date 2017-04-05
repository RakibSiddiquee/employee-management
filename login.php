<?php
require_once 'core/init.php';
$settings = new Settings();
$db = DB::getInstance();
$user = new User();
if($user->isLoggedIn()){
	Redirect::to('home');
}

$errorUsername = '';
$errorPassword = '';

if(Input::exists()){
	if(Token::check(Input::get('token'))){
		$validate = new validate();
		$validation = $validate->check($_POST, array(
			'username' => array('required' =>true),
			'password' => array('required' => true)
		));

		if($validation->passed()){

			$remember = (Input::get('remember') === 'on') ? true : false;
			$login = $user->login(Input::get('username'), Input::get('password'), $remember);

			if($login){

				// For attendance 
				$getData = $db->query("SELECT * FROM attendance WHERE (`date` = '" . date('Y-m-d') . "' AND `user_id` = '" .$user->data()->id. "')")->first();
				//var_dump($getData);

				if(empty($getData->in_time)){
					
					$db->insert('attendance', array(
						'user_id' => $user->data()->id,
						'date' => date('Y-m-d'),
						'in_time' => date('H:i:s')
					));


				}

				// End of attendance

				Redirect::to('home');
			}else{
				$errorLogin = "Username or password is incorrect!";
			}


		} else {
			foreach ($validation->errors() as $error) {
				foreach ($error as $key => $value) {
					$errors[$key] = $value;
				}
			}
			if(!empty($errors['Username'])) $errorUsername = $errors['Username'];
			if(!empty($errors['Password'])) $errorPassword = $errors['Password'];			
		}
	}
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $settings->companyName(); ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- FAVICON -->
    <link rel="shortcut icon" href="uploads/<?php echo $settings->companyLogo(); ?>">
    
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="3rdparty/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="3rdparty/dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="3rdparty/dist/css/skins/_all-skins.min.css">
    <!-- Morris chart -->
    <link rel="stylesheet" href="3rdparty/plugins/morris/morris.css">

    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script>
        // Auto remove alert box
   	 window.setTimeout(function() {
            jQuery(".alert").fadeTo(2000, 0).slideUp(2000, function(){
              jQuery(this).remove(); 
            });
    	}, 2000);
    
    </script>
  </head>
  <body class="hold-transition skin-blue sidebar-mini">
	<div class="wrapper" style="background-color: #ecf0f5;">

      <header class="main-header">
        <!-- Logo -->
        <!-- Logo -->
        <a href="home" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="xlogo-mini"><img class="logoImg" src="uploads/<?php echo $settings->companyLogo(); ?>" alt="Logo"></span>
          <!-- logo for regular state and mobile devices -->          
          <span class="logo-lg" style="display: inline;"><b><?php echo $settings->companyName(); ?></b></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">

        </nav>
      </header>

		<!-- Content Wrapper. Contains page content -->
		<div class="content-wrapper" style="margin-left: 0;">

			<!-- Main content -->
			<section class="content">

				<div class="row">
					<div class="col-md-6 col-sm-offset-3">

						<div class="box box-info" >
							<div class="box-header with-border">
							  <h3 class="box-title">Login</h3>
							  <?php if(!empty($errorLogin)){ ?>
							  <div class="alert alert-danger fade in text-center col-sm-offset-2" style="padding: 4px; width: 40%; margin-top: -30px; margin-bottom: 0;">
							  	<?php echo $errorLogin; ?>
							  	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
							  </div>
							  <?php }?>
							</div><!-- /.box-header -->
							<!-- form start -->
							<form action="" method="post" class="form-horizontal">
							  <div class="box-body">
							  	<div class="col-sm-12">
								    <div class="form-group">
								      <label for="username" class="col-sm-2 control-label">Username: </label>
								      <div class="col-sm-7">
								        <input type="text" name="username" class="form-control" id="username" placeholder="Username">
								      </div>
			         				  <small class="error col-sm-3"><?php if(count($errorUsername) > 0) echo $errorUsername; ?></small>
								    </div>
								    <div class="form-group">
								      <label for="password" class="col-sm-2 control-label">Password:</label>
								      <div class="col-sm-7">
								        <input type="password" name="password" class="form-control" id="password" placeholder="Password">
								      </div>
			         				  <small class="error col-sm-3"><?php if(count($errorPassword) > 0) echo $errorPassword; ?></small>
								    </div>

								    <div class="form-group">
								      <div class="col-sm-offset-2 col-sm-10">
								        <div class="checkbox">
								        	<label for="remember">
							       				<input type="checkbox" name="remember" id="remember">Remember me
							       			</label>
								        </div>
								      </div>
								    </div>

							        <input type="hidden" name ="token" value="<?php echo Token::generate(); ?>">
								  <div class="form-group">
								  	<div class="col-sm-2"></div>
								  	<div class="col-sm-7">
							      	  <input type="submit" class="btn btn-info btn-block" value="Log in">
							          <p class="text-center"><a href="#">Forgot your password?</a></p> 
							        </div>
							        <span class="col-sm-3"></span>
								  </div><!-- /.box-footer -->
								</div>
								<div class="col-sm-2"></div>
							  </div><!-- /.box-body -->
							</form>
						</div><!-- /.box -->
					</div>
				</div>	 
			</section><!-- /.content -->
		</div><!-- /.content-wrapper -->


      <footer class="main-footer" style="margin-left: 0;">
        <span class="col-sm-offset-4"><strong>Copyright &copy; 2015-<?php echo date('Y');?> <a href="login"><?php echo $settings->companyName(); ?></a>.</strong> All rights reserved.</span>
      </footer>
	</div><!-- ./wrapper -->

    <!-- jQuery 2.1.4 -->
    <script src="3rdparty/plugins/jQuery/jQuery-2.1.4.min.js"></script>

    <!-- Bootstrap 3.3.5 -->
    <script src="3rdparty/bootstrap/js/bootstrap.min.js"></script>
    <!-- Morris.js charts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="3rdparty/plugins/morris/morris.min.js"></script>

    <!-- AdminLTE App -->
    <script src="3rdparty/dist/js/app.min.js"></script>
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <script src="3rdparty/dist/js/pages/dashboard.js"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="3rdparty/dist/js/demo.js"></script>
  </body>
</html>