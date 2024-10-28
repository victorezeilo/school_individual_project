
				<section class="page-content">
					<div class="spinner-foil">
						<span class="spinner">
						<i class="fa-brands fa-gg fa-pulse fa-2x"></i>
						<span>Loading...</span>
						</span>
					</div>
					<div class="column fill jcsb">
						<form name="form1" id="form1" method="post" action="include/submit.php" enctype="multipart/form-data">
							
							<!--Grid Options-->
							<fieldset id="gridOptions">
								<ul>
									<li>
										<button class="ml-10" name="btn_upload">Select File...</button>
										<input type="file" name="txt_upload" style="display:none">
										<input type="hidden" name="txt_action" value="import-user">
										<input type="hidden" name="txt_filename">
									</li>
									<li class="fill">
										<span class="ml-10" id="filename"></span>
									</li>
									<li>
										<button type="button" class="btn-alt red mr-10" onClick="location.href='user-list.php'">Cancel</button><button class="mr-10" name="btn_next" disabled>Next</button>
									</li>
								</ul>
							</fieldset>
							<!--::Grid Options-->

							<!-- Data Grid-->
							<article class="data-grid mt">
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
	$('button[name="btn_upload"]').click((e)=>{
		e.preventDefault();
		//console.log(element);
		$('input[name="txt_upload"]').click();
	});
	
	//File inpult change handler
	$('input[name="txt_upload"]').change(function() {

		$('input[name="txt_filename"]').val('');
		$('button[name="btn_next"]').attr('disabled',true);
		
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
				const {type,text,data} = res;
				showAlert({type,text});
				if(type === "success"){
					$('#filename').html(data.fileName);
					$('input[name="txt_filename"]').val(data.fileName);
					$('button[name="btn_next"]').attr('disabled',false);
				}
	
			},
			error : function(xhr,ajaxOption,thrownError){
				console.error(xhr.responseText);
			}
		});
	});
	
	let step = 1;	
	const importUser = () =>{
		showAlert({type:'notice',text:['Notice: Please wait...']});
		$('.spinner').addClass('show');
		
		$.post('ajax/user-import.php',{txt_step:step,txt_filename:$('input[name="txt_filename"]').val()},(res)=>{
			console.log(res)
			const{type,text,data} = res;
			showAlert({type,text,options:{autoClose:false}});
			$('.data-grid').html(data.html);
			if(type === 'success'){
				step = data.step;
				$('button[name="btn_next"]').html('Import');
			}
			$('.spinner').removeClass('show');
			
		},'JSON').fail((xhr,ajaxOption,thrownError)=>{
			console.error(xhr.responseText);
			showAlert({type:'error',text:[`Error: ${thrownError}`]});
			$('.spinner').removeClass('show');
		});
	}

	$('button[name="btn_next"]').click((e)=>{
		e.preventDefault();
		importUser();
	});
	
</script>
