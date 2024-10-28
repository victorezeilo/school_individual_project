				<section class="page-content">
					<div class="row asc">
						<form name="form1" id="form1" method="post" action="include/submit.php" enctype="multipart/form-data">
							<div class="input-foil">
								<header class="input-header"><h6>My Profile</h6><a href="javascript:void(0');" title="close"><i class="fa-solid fa-square-xmark"></i></a></header>
								<main class="input-main">
									<div class="row cg-30">
										<div class="column rg-20">
											<figure id="snap"><img src="<?php echo $avatar; ?>" alt="username" width="214"></figure>
											<div class="center">
												<button type="button" name="btn_upload" value="avatar">Upload Photo</button>
												<button name="btn_submit" value="remove" class="btn-alt red ml-5"><i class="fa-solid fa-trash-alt"></i></button>
											</div>
										</div>
										<div class="column rg-20">
											<div><label><span>Email: <?php echo $user->email; ?></span></label></div>
											<div>
												<label><input type="text" name="txt_firstname" value="<?php echo $firstname; ?>" required <?php echo $user->status != 12 ? 'disabled':''; ?>><span>First Name:</span></label>
											</div>
											<div>
												<label><input type="text" name="txt_lastname" value="<?php echo $lastname; ?>" required <?php echo $user->status != 12 ? 'disabled':''; ?>><span>Last Name:</span></label>
											</div>
											<div>
												<label><input type="text" name="txt_mobile" value="<?php echo $mobile; ?>" placeholder="(507)-876-3542"><span>Mobile:</span></label>
											</div>
										</div>
									</div>
								</main>
								<footer class="input-footer jcc">
									<button name="btn_submit" value="save">Save</button>
									<button type="reset" class="btn-alt mlr-10">Reset</button>
									<button type="button" class="btn-alt" onClick="location.href='./'">Close</button>
									<input type="hidden" name="txt_action" value="avatar"><input type="hidden" name="txt_media" value=""><input type="file" name="txt_upload" style="display:none">
								</footer>
							</div>
						</form>
					</div>
				</section>
<!-- Input Mask -->
<script src="assets/inputmask/dist/jquery.mask.min.js"></script>

<script>
		
	$('input[name="txt_mobile"]').mask('(000)-000-0000');

	$('button[name="btn_upload"]').on('click',function() {
			ele = this;
			//console.log($(this).val());
			$('input[name="txt_action"]').val($(ele).val());
			$('input[name="txt_upload"]').click();
	});

	$('input[name="txt_upload"]').on('change',function() {

		var formData = new FormData($('#form1')[0]);

		$.ajax({
				url : 'ajax/_upload.php',
				type : 'post',
				data : formData,
				processData: false,
				contentType: false,
				success : function(res){
					console.log(res);
					res = $.parseJSON(res);
					const {type, text, data} = res;
					console.log(data);					

					showAlert({type,text});

					if(type == "success"){
						//$('input[name="txt_media"]').val('');
						$('input[name="txt_media"]').val(data.media);
						$('#snap').html(data.img);
					}
				},
				error : function(xhr,ajaxOption,thrownError){
					console.error(xhr.responseText);
				}
		});
	});

</script>