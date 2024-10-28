
				<section class="page-content">
					<div class="spinner-foil">
						<span class="spinner">
						<i class="fa-brands fa-gg fa-pulse fa-2x"></i>
						<span>Loading...</span>
						</span>
					</div>
					<div class="column fill jcsb">
						<form name="form1" id="form1" method="post" action="include/submit.php" enctype="multipart/form-data">
							<input type="file" name="txt_upload" style="display:none">
							<input type="hidden" name="txt_action" value="report">
							<input type="hidden" name="txt_media" value="">
							<input type="hidden" name="txt_taskid" value="">
							<input type="hidden" name="btn_submit" value="save">
							<!-- Data Grid-->
							<article class="data-grid">
								<ul class="line-item header">
									<li>#</li>
									<li class="fill">Task</li>
									<li class="w-240">Project</li>
									<li class="w-160">Manager</li>
									<li class="w-100">Due</li>
									<li class="w-100">Status</li>
									<li class="w-240">Report</li>
									<!--<li class="w-100">Comments</li>-->
								</ul>
								<?php foreach($tasklist as $key=>$row){?>
								<ul class="line-item" id="<?php echo $row['fTaskID']; ?>">
									<li><?php echo $key+1; ?><li>
									<li class="fill"><?php echo $row['fTaskName']; ?></li>
									<li class="w-240"><?php echo $row['fTitle']; ?></li>
									<li class="w-160"><?php echo $row['fManager']; ?><a href="user-im.php?contactid=<?php echo $row['fContactID']; ?>" class="ml-10" title="Message"><i class="fa-regular fa-comments"></i></a></li>
									<li class="w-100"><?php echo $row['fTaskETA']; ?></li>
									<li class="w-100"><?php echo $row['fStatus']; ?></li>
									<li class="w-240"><a href="javascript:void(0)" data-action="upload" title="Submit"><i class="fa-solid fa-paperclip"></i></a> | <?php echo empty($row['fReport']['fFileName']) ? 'N/A':'<a href="uploads/reports/'.$row['fReport']['fFileName'].'" target="_blank" title="View Report">'.$row['fReport']['fFileName'].'</a>'; ?>
									<?php if($row['fReport']['fStatus'] == 1){?>
										<span class="tag green ml-10" title="read"><i class="fa-solid fa-envelope-circle-check"></i></span>
									<?php }elseif($row['fReport']['fStatus'] == 2){?>
										<span class="tag red ml-10" title="Unread"><i class="fa-solid fa-envelope"></i></span>
									<?php }?>
									</li>
								</ul>
								<?php }?>
							</article>
							<!-- ::Data Grid-->
						</form>
						<!-- Grid Bottom -->
						<section class="grid-bottom mt-10">
							<span class="pagination"></span>
							<span class="pagenumber"></span>
						</section>
					</div>
				</section>
<!--jQuery UI-->
<script type='text/javascript' src="assets/jquery-ui-1.13.2/jquery-ui.min.js"></script>

<script>
	
	$('.spinner').addClass('show');
	setTimeout(()=>{$('.spinner').removeClass('show')},300);
	
	$('.data-grid').on('click', 'a[data-action="upload"]',function(e){
		e.preventDefault();
		const el = this;
		//console.log($(el).closest('ul').prop('id'));
		$('input[name="txt_taskid"]').val($(el).closest('ul').prop('id'));
		$('input[name="txt_upload"]').click();
	});
	
	$('input[name="txt_upload"]').change((e)=>{
		var formData = new FormData($('#form1')[0]);

		$.ajax({
				url : 'ajax/_upload.php',
				type : 'post',
				data : formData,
				processData: false,
				contentType: false,
				success : function(res){
					res = $.parseJSON(res);
					//console.log(res);
					const {type, text, data} = res;
					showAlert({type,text});

					if(type === "success"){
						//$('input[name="txt_media"]').val('');
						$('input[name="txt_upload"]').val('');
						$('input[name="txt_media"]').val(data.fileName);
						$('#form1').submit();
						//$('#snap').html(data.img);
					}
				},
				error : function(xhr,ajaxOption,thrownError){
					console.error(xhr.responseText);
				}
		});
	});

</script>
