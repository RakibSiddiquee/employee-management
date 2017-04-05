<?php ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>E-Management</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/5.5.2/css/foundation.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/foundicons/3.0.0/foundation-icons.css">
  <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
  <script src="http://cdnjs.cloudflare.com/ajax/libs/foundation/5.5.2/js/foundation.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.js"></script>
</head>
<body>
	<div class="row">
	<header>
		<nav class="top-bar" data-topbar data-options="is_hover: false">
		  <ul class="title-area">
		    <li class="name">
		      <h1><a href="#">E-Management</a></h1>
		    </li>
		    <!-- Collapsible Button on small screens: remove the .menu-icon class to get rid of icon. 
		    Remove the "Menu" text if you only want to show the icon -->
		    <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
		  </ul>

		  <section class="top-bar-section">
		    <ul class="left">
		      <li class="active"><a href="#">Home</a></li>
		     	<li><a href="designation">Designation</a></li>
				<li><a href="register">Add new employee</a></li>
		      <li class="has-dropdown">
		        <a href="#">Dropdown</a>
		        <ul class="dropdown">
		          <li><a href="#">First link in dropdown</a></li>
		          <li><a href="#">Second link in dropdown</a></li>
		          <li class="active"><a href="#">Active link in dropdown</a></li>
		        </ul>
		      </li>
		    </ul>
		        <ul class="right">
		      <li class="has-dropdown"><a href="profile.php?user=<?php echo escape($user->data()->username); ?>"><?php echo ucfirst(escape($user->data()->username)); ?></a>
		      	<ul class="dropdown">
			      <li><a href="update">Update details</a></li>
			      <li><a href="changepassword">Change password</a></li>
			      <li><a href="logout">Log out</a></li>	      
			    </ul>
			   </li>
		    </ul>
		  </section>
		</nav>
	</header>

	<div class="medium-12 columns">
		<?php require_once 'functions/parseurl.php'; ?>
	</div>

	<footer>
		<h3>Footer Section</h3>
	</footer>
</div>
