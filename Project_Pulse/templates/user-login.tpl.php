
				<section class="page-content">
					<div class="row asc">
						<form name="form1" id="form1" method="post" action="include/submit.php">
						<div class="input-foil">
							<header class="input-header">
								<h6>Login</h6><!--<a href=""><i class="fa-solid fa-square-xmark"></i></a>-->
							</header>
							<main class="input-main">
								<div class="row aife">
									<label><input type="email" name="txt_username" placeholder="Enter username" required><span>Username:</span></label>
									<aside>e.g. user@server.ext</aside>
								</div>
								<div class="row">
									<label><input type="password" name="txt_password" placeholder="Enter password" required><span>Password:</span></label>
								</div>
								<div class="row aife">
									<label><input type="text" name="txt_captcha" placeholder="Enter code" required><span>Security Code:</span></label>
									<div class="captcha"><figure><?php echo $captcha->SecurityCode(); ?></figure><a href="javascript:void(0);"><i class="fa-solid fa-rotate"></i></a></div>
								</div>
							</main>
							<footer class="input-footer row aic cg-10">
								<button name="btn_login">Login</button>
								<button type="reset" class="btn-alt red">Clear</button>
								<div><a href="user-pwd-request.php">Forgot Password</a></div>
							</footer>
							
						</div>
						</form>
					</div>
				</section>
<script>
	
	$('button[name="btn_login"]').click((e)=>{
		e.preventDefault();

		showAlert({type:'notice',text:['Notice: Please wait...'],options:{autoClose:false}});

		$.post('ajax/user-login.php',$('#form1').serializeArray(),(res)=>{
			console.log(res);
			const{type,text,data} = res;
			showAlert({type,text});
			if(type === 'success')
				return window.location.replace(data.goLink);
			
		},'JSON').fail((xhr,ajaxOption,thrownError)=>{
			console.error(xhr.responseText);
			showAlert({type:'error',text:[`Error: ${thrownError}`]});
		});
	});
	
	$('.captcha a').click((e)=>{
		e.preventDefault();
		$.get('ajax/_captcha.php',(res)=>{
			$('.captcha figure').html(res);
		}).fail((xhr,ajaxOption,thrownError)=>{
			console.error(xhr.responseText);
			showAlert({type:'error',text:[`Error: ${thrownError}`]});
		});
	});

</script>
