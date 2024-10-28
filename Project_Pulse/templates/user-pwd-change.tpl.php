				<section class="page-content">
					<div class="row asc">
						<form name="form1" id="form1" method="post" action="include/submit.php">
							<div class="input-foil">
								<header class="input-header"><h6>Change Password</h6></header>
								<main class="input-main">

									<div class="row aife">
										<label>
											<input type="password" name="txt_pwd_current" placeholder="Enter current password" value="<?php echo $pwd_current; ?>" required>
											<span>Current Password:</span>
										</label>
										<aside></aside>
									</div>
									<div class="row aife">
										<label>
											<input type="password" name="txt_pwd_new" placeholder="Enter new password" value="<?php echo $pwd_new; ?>" required>
											<span>New Password:</span>
										</label>
										<aside>Allowed characters: !@#$%^&*()\-_+.</aside>
									</div>
									<div class="row aife">
										<label>
											<input type="password" name="txt_pwd_confirm" placeholder="Retype password" value="<?php echo $pwd_confirm; ?>" required>
											<span>Confirm Password:</span>
										</label>
										<aside></aside>
									</div>
									<div class="column rg-5">
										<p>Password must be minimum 8 characters and must contain</p>
										<p>2 Upper, 2 Lower, 2 Numeric and 1 Special characters</p>
									</div>
								</main>
								<footer class="input-footer jcc">
									<button name="btn_submit" value="save">Change</button>
									<button type="reset" class="btn-alt mlr-10">Reset</button>
								</footer>
							</div>
						</form>
					</div>
				</section>
