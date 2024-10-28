				<section class="page-content">
					<div class="row asc">
						<form name="form1" id="form1" method="post" action="include/submit.php">
							<div class="input-foil project-add">
								<header class="input-header"><h6><?php echo empty($id) === false ? 'Edit Project':'Add Project'; ?></h6><a href="javascript:void(location.href='project-list.php');" title="close"><i class="fa-solid fa-square-xmark"></i></a></header>
								<main class="input-main">
									<div class="row cg-30">
										<div class="column rg-20">
											<div class="row">
												<label>
													<input type="text" name="txt_title" class="large" placeholder="Enter title" value="<?php echo $title; ?>" required>
													<span>Project Title:</span>
												</label>
											</div>
											<div class="row fill">
												<label class="column fill">
													<textarea name="txt_description"  placeholder="Enter project description"><?php echo $description; ?></textarea>
													<span>Description:</span>
												</label>
											</div>
											<div class="row">
												<label>
													<input type="text" name="txt_manager" class="large" placeholder="Project manager..." value="<?php echo $manager; ?>" required>
													<span>Project Manager:</span>
												</label>
											</div>
											<div class="row cg-20 jcsb">
												<div class="row">
													<label><input type="date" name="txt_startdate" value="<?php echo $startdate; ?>" required><span>Start Date:</span></label>
												</div>
												<div class="row">
													<label><input type="date" name="txt_enddate" value="<?php echo $enddate; ?>" required><span>End Date:</span></label>
												</div>
											</div>
											<div class="row cg-20 jcsb">
												<div class="row">
													<label>
														<select name="txt_report" required>
															<option value="daily" <?php echo $report == 'daily' ? 'selected':''; ?>>daily (default)</option>
															<option value="weekly" <?php echo $report == 'weekly' ? 'selected':''; ?>>weekly</option>
															<option value="fortnightly" <?php echo $report == 'fortnightly' ? 'selected':''; ?>>fortnightly</option>
															<option value="monthly" <?php echo $report == 'monthly' ? 'selected':''; ?>>monthly</option>
															<option value="specific" <?php echo $report == 'specific' ? 'selected':''; ?>>Specific</option>
														</select><span>Reporting:</span></label>
												</div>
												<div class="row">
													<label><input type="date" name="txt_specific" value="<?php echo $specific; ?>"><span>Date:</span></label>
												</div>
											</div>
										</div>
										<div class="column rg-20">
											<div class="row aife cg-10">
												<label>
													<input type="text" name="txt_taskname" class="large" placeholder="Task name">
													<span>Task:</span>
												</label>
												<label>
													<input type="text" name="txt_member" placeholder="Search members...">
													<span>Assigned To:</span>
												</label>
												<label>
													<input type="date" name="txt_tasketa">
													<span>Report Date:</span>
												</label>
												<span><button type="button" name="btn_add" value="add"><i class="fa-solid fa-floppy-disk"></i></button></span>
											</div>
											<div class="data-grid task-list">
											</div>
										</div>
									</div>

								</main>
								<footer class="input-footer jcc">
									<button name="btn_submit" value="save">Save</button>
									<button type="reset" class="btn-alt mlr-10">Clear</button>
									<button type="button" class="btn-alt mr-10" onClick="location.href='project-add.php'">New</button>
									<button type="button" class="btn-alt" onClick="location.href='project-list.php'">Close</button>
									<input type="hidden" name="txt_id" value="<?php echo $id; ?>">
									<input type="hidden" name="txt_managerid" value="<?php echo $managerid; ?>">
								</footer>
							</div>
						</form>
					</div>
				</section>

<script type="text/javascript" src="assets/jquery-ui-1.13.2/jquery-ui.min.js"></script>
<script>
	
	let action, taskId, memberId;
	
	const loadTasks = ()=>{
		const taskName = $('input[name="txt_taskname"]').val();
		const taskETA = $('input[name="txt_tasketa"]').val();
		const member = $('input[name="txt_member"]').val();
		const id = $('input[name="txt_id"]').val();
		
		let FormData = [
			{name:'txt_action',value:action},
			{name:'txt_taskid',value:taskId},
			{name:'txt_taskname',value:taskName},
			{name:'txt_member',value:member},
			{name:'txt_memberid',value:memberId},
			{name:'txt_tasketa',value:taskETA},
			{name:'txt_id',value:id}
		];
		
		$.post('ajax/task-add.php',FormData,(res)=>{
			console.log(res);
			const{type,text,data} = res;
			switch(type){
				case 'success':
				$('.task-list').html(data.html);
				$('input[name="txt_taskname"]').val('');
				break;

				default:
				showAlert({type,text});
				break;
			}
		},'JSON').fail((xhr,ajaxOption,thrownError)=>{
			console.error(xhr.responseText);
			showAlert({type:'error',text:[`Error: ${thrownError}`]});
		});
		
	}
	
	loadTasks();
	
	$('button[name="btn_add"]').click((e)=>{
		e.preventDefault();
		//can add input valdiation here
		action = 'add';
		loadTasks();
	});

	$('input[name="txt_taskname"]').keydown(function(e) {
			if(e.keyCode == 13){e.preventDefault(); $('button[name="btn_add"]').click();}
	});


	$('.task-list').on('click','a.delete',function(e){
		taskId = $('a.delete').index(this);
		action = 'delete';
		loadTasks();
	});
	//select member
	$('input[name="txt_member"]').autocomplete({
		disabled:false,
		source: (req,res)=>{
			$.post('ajax/_autofill.php',{txt_object:'user',txt_filter:'term',txt_term:req.term},(result)=>{
					//console.log(res);
					res(result.data.list);
			},'JSON').fail((xhr,ajaxOption,thrownError)=>{
					console.error(xhr.responseText);
			});
		},
		minLength:1,
		select:(a,b) =>{memberId = b.item.id}
	});
	//select manager
	$('input[name="txt_manager"]').autocomplete({
		disabled:false,
		source: (req,res)=>{
			$.post('ajax/_autofill.php',{txt_object:'user',txt_filter:'term',txt_term:req.term},(result)=>{
					//console.log(res);
					res(result.data.list);
			},'JSON').fail((xhr,ajaxOption,thrownError)=>{
					console.error(xhr.responseText);
			});
		},
		minLength:1,
		select:(a,b) =>{$('input[name="txt_managerid"]').val(b.item.id)}
	});
</script>
