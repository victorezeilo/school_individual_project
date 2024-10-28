				<section class="page-content">
					<div class="row asc">
						<form name="form1" id="form1" method="post" action="include/submit.php">
							<div class="input-foil">
								<header class="input-header"><h6>Set Password</h6></header>
								<main class="input-main">

									<div class="row aife">
										<label>
											<input type="email" value="<?php echo !empty($newuser->email) ? $newuser->email:''?>" disabled required>
											<span>Username:</span>
										</label>
										<aside></aside>
									</div>
									<div class="row aife">
										<label>
											<input type="password" name="txt_password" placeholder="Choose password" autocomplete="off" required>
											<span>Password:</span>
										</label>
										<aside>Allowed characters: !@#$%^&*()\-_+.</aside>
									</div>
									<div class="row aife">
										<label>
											<input type="password" name="txt_pwd_confirm" placeholder="Retype password" autocomplete="off" required>
											<span>Confirm Password:</span>
										</label>
										<aside></aside>
									</div>
									<div class="column rg-5">
										<p>Password must contain</p>
										<p>2 Upper, 2 Lower, 2 Numeric and 1 Special characters</p>
									</div>
								</main>
								<footer class="input-footer">
									<button name="btn_submit" value="reset">Save</button>
									<button type="reset" class="btn-alt red mlr-10">Clear</button>
								</footer>
							</div>
						</form>
					</div>
				</section>
