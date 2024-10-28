
				<section class="page-content">
					<div class="spinner-foil">
						<span class="spinner">
						<i class="fa-brands fa-gg fa-pulse fa-2x"></i>
						<span>Loading...</span>
						</span>
					</div>
					<div class="column fill jcsb">
						<form name="form1" id="form1" method="post" action="include/submit.php">
							
							<!--Grid Options-->
							<fieldset id="gridOptions">
								<ul>
									<li>
										<label>
											<span class="ml-10">Look in:</span>
											<select name="filtercolumn">
												<option value="">None</option>
											<?php foreach($filtercolumn_list as $key=>$val){?>
												<option value="<?php echo $key; ?>" <?php echo $filtercolumn == $key ? "selected":""; ?>><?php echo $val; ?></option>
											<?php } ?>
											</select>
										</label>
										<label>
											<input type="search" name="filtervalue" placeholder="Search for..." value="<?php echo $filtervalue; ?>" <?php echo empty($filtercolumn) ? 'disabled':'';?>/>
										</label>
										<button class="mr-10">Search</button>
									</li>
									<li>
										<div>
											<label>
												<span>Sort:</span>
												<select name="sortcolumn">
													<option value="">Default</option>
												<?php foreach($sortcolumn_list as $key=>$val){?>
													<option value="<?php echo $key; ?>" <?php echo $sortcolumn == $key ? "selected":""; ?>><?php echo $val; ?></option>
												<?php  } ?>
												</select>
											</label>
											<label>
												<input type="radio" name="sort" value="ASC" <?php echo $sort == 'ASC' ? "checked":""; ?>/>
												<i class="fa-solid fa-sort-alpha-down"></i>
											</label>
											<label>
												<input type="radio" name="sort" value="DESC" <?php echo $sort == 'DESC' ? "checked":""; ?>/>
												<i class="fa-solid fa-sort-alpha-up"></i>
											</label>
											<button class="mr-10">Sort</button>
										</div>
									</li>
								</ul>
								<ul>
									<li>
										<figure class="mr-5">
											<!--<i class="fa-solid fa-arrow-turn-up fa-rotate-90"></i>-->
										</figure>
										<label>
											<select name="txt_action">
												<option value="">--Select--</option>
											<?php foreach($action_list as $key=>$val){?>
												<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
											<?php  } ?>
											</select>
										</label>
										<input type="hidden" name="btn_submit">
										<button name="btn_update" class="mr-10">Go</button>
									</li>
									<li></li>
									<li>
										<label>
											<span>Row Count:</span>
											<select name="rows_per_page">
												<option value="25">25</option>
											<?php foreach($resultcount_list as $key=>$val){?>
												<option value="<?php echo $key; ?>" <?php echo $rows_per_page == $key ? "selected":""; ?>><?php echo $val; ?></option>
											<?php  } ?>
											</select>
										</label>
										<button class="mr-10">set</button>
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
	jQuery(($) => {
	
		$('.spinner').addClass('show');

		let position = 0; //current position
		let loading  = false; //to prevents multipal ajax loads
		const url = 'ajax/project-list.php';

		//load first group
		$(function(){loadData()});

		$('.pagination').on('click', 'a', function(e){
			e.preventDefault();
			position = $(this).data('position');
			$('.spinner').addClass('show');
			loadData();
		});

		function loadData(){

			if(loading === false){
				loading = true;
				const postData = $('#gridOptions').serializeArray();
				postData.push({name: 'position', value: position});

				$.post(url,postData,function(res){
					//console.log(res);
					const {type,text,data} = res;
					showAlert({type,text});

					switch(type){
						case 'none':
						$(".data-grid").html(text[0]);
						break;

						default:
						$(".data-grid").html(data.html);
						$(".pagination").html(data.pagination);
						$(".pagenumber").html(data.pagenumber);
					}				

					setTimeout(()=>{$('.spinner').removeClass('show')},300);
					loading=false;

				},'JSON').fail(function(xhr,ajaxOption,thrownError){
					console.error(xhr.responseText);
					showAlert({type:'error',text:['Error: '+ thrownError]});
					loading = false;
				});
			}

		}

		$('select[name="filtercolumn"]').change(function() {
			//console.log($(this).val());
			filtercolumn = $(this).val();
			switch(filtercolumn){
				case '':
				$('input[name="filtervalue"]').val('');
				$('input[name="filtervalue"]').attr('disabled',true);
				break;

				default:
				$('input[name="filtervalue"]').attr('disabled',false);
			}
		});

		$('button[name="btn_update"]').click(function(e){
			e.preventDefault();

			const action = $('select[name="txt_action"]').val();
			if(action === '') 
				return showAlert({type:'notice',text:['Notice: Please select operation to perform']});

			if($('input[name="ids[]"]:checked').length === 0) 
				return showAlert({type:'notice',text:['Notice: Please select minimum one list item']});

			const options = {message : `Are you sure you want to '${action}' selected project(s)?`}

			showDialog(options).then(function(response){
				if(response) {
					$('input[name="btn_submit"]').val('update');
					$('#form1').submit();	
				}
			});

		});

		$('.data-grid').on('click','a[data-action]', function(e){
			e.preventDefault();
			const element = this;

			const action = $(element).data('action');

			const options = {message : `Are you sure you want to '${action}' this project?`}

			showDialog(options).then(function(response){
				if(response) {
					$('select[name="txt_action"]').val(action);
					$(element).closest('.data-list').find('input[name="ids[]"]').prop('checked',true);
					$('input[name="btn_submit"]').val('update');
					$('#form1').submit();
				}
			});
		});
	});
</script>
