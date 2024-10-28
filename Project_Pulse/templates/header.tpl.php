<!doctype html>
<html lang="en">
<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<title>Project Pulse | CRM</title>
		<!--Style Sheet-->
		<link rel="stylesheet" href="assets/css/reset.css"/>
		<link rel="stylesheet" href="assets/fontawesome-free-6.3.0-web/css/fontawesome.min.css"/>
		<link rel="stylesheet" href="assets/fontawesome-free-6.3.0-web/css/brands.min.css"/>
		<link rel="stylesheet" href="assets/fontawesome-free-6.3.0-web/css/solid.min.css"/>
		<link rel="stylesheet" href="assets/jquery-ui-1.13.2/jquery-ui.min.css">
		<link type="text/css" href="assets/smartalert/smartalert.css" rel="stylesheet"/>
		<link rel="stylesheet" href="assets/css/style.css"/>
		
		<script type="text/javascript" src="assets/smartalert/smartalert.js"></script>
		<script type="text/javascript">const {showAlert} = new SmartAlert();</script>
		<!--jQuery -->
		<script type="text/javascript" src="assets/jquery-3.6.4/jquery-3.6.4.min.js"></script>
		<!--Highcharts -->
		<script type="text/javascript" src="assets/highcharts-10.3.3/code/highcharts.js"></script>
		<!--Common Functions-->
		<script type="text/javascript" src="assets/js/functions.js"></script>
</head>

<body>
	<div id="root">
			<aside class="sidebar">
				
				<div class="logo"><h5>Project Pulse</h5></div>
				
				<?php if(empty($user->userid) === false){?>
				<nav class="side-nav-foil">
					<ul class="side-nav">
						<?php if($user->usergroup <= 3){?>
            <li>
              <a href="task-manage.php"<?php echo $mainNav[0] == 1 ? ' class="active"':'';?>><i class="fa-solid fa-house-user fa-fw"></i><span>Dashboard</span></a>
            </li>
						<?php }elseif($user->usergroup > 3){?>
            <li>
              <a href="task-list.php"<?php echo $mainNav[0] == 5 ? ' class="active"':'';?>><i class="fa-solid fa-house-user fa-fw"></i><span>Dashboard</span></a>
            </li>
						<?php } ?>
						<?php if($user->usergroup == 2){?>
            <li>
              <label<?php echo $mainNav[0] == 2 ? ' class="active show"':'';?>><i class="fa-solid fa-users"></i><span>Users</span></label>
							<ul>
								<li><a href="user-add.php"<?php echo $mainNav[0] == 2 && $mainNav[1] == 1 ? ' class="active"':'';?>><span>Add User</span></a></li>
								<li><a href="user-list.php"<?php echo $mainNav[0] == 2 && $mainNav[1] == 2 ? ' class="active"':'';?>><span>List Users</span></a></li>
							</ul>
            </li>
						<?php }?>
						<?php if($user->usergroup == 2 || $user->usergroup == 3 ){?> 
						<li>
							<label<?php echo $mainNav[0] == 3 ? ' class="active show"':'';?>><i class="fa-solid fa-diagram-project fa-fw"></i><span>Projects</span></label>
							<ul>
								<li><a href="project-add.php"<?php echo $mainNav[0] == 3 && $mainNav[1] == 1 ? ' class="active"':'';?>><span>Add Project</span></a></li>
								<li><a href="project-list.php"<?php echo $mainNav[0] == 3 && $mainNav[1] == 2 ? ' class="active"':'';?>><span>List Projects</span></a></li>
							</ul>
            </li>
						<?php }else{?>
						<li><a href="project-list.php"<?php echo $mainNav[0] == 3 && $mainNav[1] == 2 ? ' class="active"':'';?>><i class="fa-solid fa-diagram-project fa-fw"></i><span>Projects</span></a></li>
						<?php } ?>
						<?php if($user->usergroup <= 3){?>
						<li>
							<a href="task-list.php"<?php echo $mainNav[0] == 5 ? ' class="active"':'';?>><i class="fa-solid fa-list-check fa-fw"></i><span>Task List</span></a>
            </li>
						<?php }?>
						<li>
							<a href="user-im.php"<?php echo $mainNav[0] == 4 ? ' class="active"':'';?>><i class="fa-solid fa-envelopes-bulk fa-fw"></i><span>Message Center</span></a>
            </li>
            <li>
							<a href="user-pwd-change.php" <?php echo $mainNav[0] == 9 ? ' class="active"':'';?>><i class="fa-solid fa-key fa-fw"></i><span>Change Password</span></a>
            </li>
            <li>
              <a href="user-logout.php"><i class="fa-solid fa-power-off fa-fw"></i><span>Logout</span></a>
            </li>
          </ul>
				</nav>
				<?php }?>
				
			</aside>
			<main class="page-main">
				<header class="page-header">
					<span class="bread-crumb"><i class="fa-solid fa-building-circle-arrow-right"></i><span>:/ <?php echo $breadCrumb; ?></span></span>
					<span>
						<input type="search" class="large" placeholder="Search project...">
						<button><i class="fa-solid fa-magnifying-glass"></i></button>
					</span>
					<span class="row aic cg-20">
						
						<nav class="top-nav">
							<a href="javascript:void(0);" title="" class="bell"><i class="fa-regular fa-bell"></i></a>
						</nav>
						<span class="row aic cg-10">
							<figure><img src="uploads/user/images/avatar/<?php echo empty($user->userid) === false ? $user->avatar:'user_01.jpg'; ?>" alt="" width="35" style="border-radius:50%"></figure>
							<div class="profile-nav-foil">
								<?php if(empty($user->userid) === false){?>
								<span><?php echo "$user->firstname $user->lastname";?></span>
								<i class="fa-solid fa-caret-down ml-10"></i>
								<nav class="profile-nav">
									<a href="user-profile.php"><i class="fa-solid fa-circle-user fa-fw mr-10"></i>My Profile</a>									
									<a href="user-pwd-change.php"><i class="fa-solid fa-key fa-fw mr-10"></i>Change Password</a>									
									<a href="user-logout.php"><i class="fa-solid fa-power-off fa-fw mr-10"></i>Logout</a>									
								</nav>
								<?php }else{ ?>
								<span>Login</span>
								<i class="fa-solid fa-right-to-bracket ml-20 fa-rotate-180"></i>
								<?php } ?>
							</div>
						</span>
					</span>
				</header>
				