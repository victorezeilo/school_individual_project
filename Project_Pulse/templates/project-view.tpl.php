				<section class="page-content">
					<div class="row asc">
						<form name="form1" id="form1" method="post" action="include/submit.php">
							<div class="input-foil project-add">
								<header class="input-header"><h6><?php echo $title; ?></h6><a href="javascript:void(location.href='project-list.php');" title="close"><i class="fa-solid fa-square-xmark"></i></a></header>
								<main class="input-main">
										<div class="column rg-20">
											<div>
												<h6>Project Manager: <?php echo $manager; ?></h6>
												<p>Due in 21 day(s)</p>
											</div>
											<div>
												<h6>Description:</h6>
												<p><?php echo $description; ?></p>
											</div>
											<div class="row cg-30">
												<div class="card">
													<h6>Overview</h6>
													<p>Start Date: <?php echo date('M d, Y', strtotime($startdate)); ?></p>
													<p>Due Date: <?php echo date('M d, Y', strtotime($enddate)); ?></p>
													<p>Completed: 34%</p>
												</div>
												<div class="card">
													<h6>Tasks</h6>
													<p>Total: 12</p>
													<p>Completed: 12 (40%)</p>
													<p>Pending: 12 (60%)</p>
												</div>
												<div class="card">
													<h6>Other</h6>
													<p>Team Members: 12</p>
													<p>Team Members: 12</p>
													<p>Task Count: 34</p>
												</div>
											</div>
											<div class="data-grid task-list">
												<ul class="line-item header">
													<li>#</li>
													<li class="fill">Task</li>
													<li class="w-160">Assigned To</li>
													<li class="w-100">ETA</li>
													<li>Status</li>
												</ul>
												<?php foreach($tasklist as $key=>$row){?>
												<ul class="line-item">
													<li><?php echo $key+1; ?></li>
													<li class="fill"><?php echo $row['fTaskName']; ?></li>
													<li class="w-160"><?php echo $row['fAssignedTo']; ?></li>
													<li class="w-100"><?php echo $row['fTaskETA']; ?></li>
													<li>
														<select>
															<option value="1">Inprogress</option>
															<option value="2">Review</option>
															<option value="3">Completed</option>
														</select>
													</li>
												</ul>
												<?php	} ?>
											</div>
										</div>

								</main>
								<footer class="input-footer jcfe">
									<button>Update</button>
									<button type="button" class="btn-alt ml-10" onClick="location.href='project-list.php'">Close</button>
									<input type="hidden" name="txt_id" value="<?php echo $id; ?>">
									<input type="hidden" name="txt_managerid" value="<?php echo $managerid; ?>">
								</footer>
							</div>
						</form>
					</div>
				</section>
