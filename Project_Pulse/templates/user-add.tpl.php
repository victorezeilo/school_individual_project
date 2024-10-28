				<section class="page-content">
					<div class="row asc">
						<form name="form1" id="form1" method="post" action="include/submit.php">
							<div class="input-foil">
								<header class="input-header"><h6><?php echo empty($id) === false ? 'Edit User':'Add User'; ?></h6><a href="javascript:void(location.href='user-list.php');" title="close"><i class="fa-solid fa-square-xmark"></i></a></header>
								<main class="input-main">
									<div class="row">
										<label>
											<select name="txt_usergroup" class="normal" required>
											<option value="">-- Select --</option>
											<?php foreach($usergroup_list as $item){?>
											<option value="<?php echo $item['fGroupID']; ?>" <?php echo $usergroup == $item['fGroupID'] ? 'selected':''; ?>><?php echo $item['fGroupName']; ?></option>
											<?php }?>
											</select>
											<span>User Group:</span>
										</label>
									</div>
									<div class="row aife">
										<label>
											<input type="text" name="txt_firstname" placeholder="Enter first name" value="<?php echo $firstname; ?>" <?php echo empty($id) === false && $enable != 19 ? 'disabled':''; ?> required>
											<span>First Name:</span>
										</label>
										<aside>e.g. Victor</aside>
									</div>
									<div class="row aife">
										<label>
											<input type="text" name="txt_lastname" placeholder="Enter last name" value="<?php echo $lastname; ?>" <?php echo empty($id) === false && $enable != 19 ? 'disabled':''; ?> required>
											<span>Last Name:</span>
										</label>
										<aside>e.g. Desouza</aside>
									</div>
									<div class="row aife">
										<label>
											<input type="email" name="txt_email" placeholder="Enter email..." value="<?php echo $email; ?>" <?php echo empty($id) === false && $enable != 19 ? 'disabled':''; ?> required>
											<span>Email:</span>
										</label>
										<aside>e.g. john@projectpulse.local</aside>
									</div>
									<?php if(empty($row['fUserID']) === false && ($row['fStatus'] == 1 || $row['fStatus'] == 10)){?>
									<div>
										<div class="switch">
											<label>
												<input type="checkbox" name="txt_enable" value="1" <?php echo $enable == 1 ? 'checked':''; ?>>
												<span class="slider round"></span>
											</label>
										</div>
									</div>
									<?php  } ?>
								</main>
								<footer class="input-footer jcc">
									<button name="btn_submit" value="save">Save</button>
									<button type="reset" class="btn-alt mlr-10">Clear</button>
									<button type="button" class="btn-alt mr-10" onClick="location.href='user-add.php'">New</button>
									<button type="button" class="btn-alt" onClick="location.href='user-list.php'">Close</button>
									<input type="hidden" name="txt_id" value="<?php echo $id; ?>">
								</footer>
							</div>
						</form>
					</div>
				</section>
