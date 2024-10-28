
				<section class="page-content im-center">
					<div class="spinner-foil">
						<span class="spinner">
						<i class="fa-brands fa-gg fa-pulse fa-2x"></i>
						<span>Loading...</span>
						</span>
					</div>
					<div class="im-container">
						<div class="im-container-header"><a href="javascript:void(0);">Message History</a></div>
						<main class="im-wrapper-box">
							<ul class="im-wrapper">
								<!--<li style="align-self: center"><i class="fab fa-gg fa-pulse fa-3x"></i></li>-->
							</ul>
						</main>
						<footer class="im-container-footer">
							<input type="text" name="txt_message" placeholder="Type your message here...">
							<button type="button" name="btn_send" title="Send"><i class="fa-regular fa-paper-plane"></i></button>
						</footer>
					</div>

					<ul class="im-contact-list" data-contactid="<?php echo $contactid; ?>">
						<li style="align-self: center; border-bottom: none;"><i class="fab fa-gg fa-pulse fa-3x"></i></li>
					</ul>
				</section>
<script>
jQuery(function($){
	
	let contactid = $('.im-contact-list').data('contactid');
  let keepid = $('.im-contact-list').data('contactid');
	let snd = new Audio("assets/audios/sms.mp3"); // buffers automatically when created
  //console.log(contactid);

  //contact_loading = false;
  let polling = false;
	let isLoadingMsg  = false; //to prevents multipal ajax loads
	let contactFirstRun = true;
  
  showAlert({type:'notice',text:['Notice: Please wait...']});
	
	const loadContact = function(){
		const defer = $.Deferred();
		
		if(isLoadingMsg) defer.reject();
		
		formData = {txt_contactid:contactid};
		
		if(keepid != contactid){$.extend(formData,{txt_keepid:keepid});}
		
		$.post('ajax/im-contact-list.php',formData,function(res){
			//console.log(res);
			const {type, text, data} = res;
			
			if(type != 'success'){showAlert({type,text}); defer.reject()}
			
			switch(contactFirstRun){
				case true:
					contactFirstRun = false;
					$('.im-contact-list li').fadeOut(300,function(){
						$('.im-contact-list').css('justify-content','flex-start');
						$('.im-contact-list').html(data.html);
						//console.log(contactid);
						switch(contactid){
							case '':
								$('.im-contact-list li:first-child').trigger('click')
							break;

							default:
								$('.im-contact-list li[data-contactid="'+contactid+'"]').trigger('click');
						}
					});
				break;
					
				default:
					$('.im-contact-list').html(data.html);
					$('.im-contact-list li[data-contactid="'+contactid+'"]').addClass('active');
			}

			defer.resolve();

		},'JSON').fail(function(xhr,ajaxOption,thrownError){
				console.error(xhr.responseText)
				//polling = false;
				defer.reject();
		});
		return defer;
	};

	loadContact();
	//poll for new messages
	const pollMessage = function(){
		
		if(isLoadingMsg) return;
		
		formData = {'txt_contactid':contactid,txt_lastimid:$('.im-wrapper li:last-child').data('imid')};
		$.post('ajax/im-message-new.php',formData,function(res){
			//console.log(res);
			const {type,text,data} = res;
			/*
			if(type !== 'success')
				return showAlert({type,text});
			*/
			
			if(type == 'success' && data.html != ''){
					//imid =  data.status_data.imid;
				$('.im-wrapper').append(data.html);
				$('.im-wrapper-box').scrollTop($('.im-wrapper-box').prop('scrollHeight'));
				if(data.playSound == true){snd.play();}
			}

		},'JSON').fail(function(xhr,ajaxOptions,thrownError){
			//we'll see what we do if msg load fails
			//polling = false;
			console.error(xhr.responseText);
		});
		
	};
	
	setInterval(function(){
	 loadContact().then(function(){pollMessage();});
	},3000);
	
  //message load function
	let position = 0; //current position
	let pageCount = 0; //total pages 'update after 1st call'

	const loadMessage = function(){

		if(isLoadingMsg) return;
		
		isLoadingMsg = true;
		
		formData = {page_count:pageCount,position:position,txt_contactid:contactid};
		
		$.post('ajax/im-message-list.php',formData,function(res){

			//console.log(res);
			const {type,text,data} = res;
			
			if(type == "success" && data.pageCount != 0){

				pageCount = data.pageCount;
				if(pageCount > 1)
					$('.im-container-header').addClass('show').html('<a href="javascript:void(0);">Load earlier messages</a>');

				$('.im-wrapper li:first-child').fadeOut(300,function(){
					$('.im-wrapper').css('justify-content','flex-start');
					$('.im-wrapper li:first-child').after(data.html);
				});
			}
			else if(type == 'notice' && typeof data.pageCount == 'undefined'){
				$('.im-wrapper li:first-child').fadeOut(500,function(){
					$('.im-wrapper').css('justify-content','flex-start');
				});
				$('.im-container-header').html(text[0]).addClass('show');

			}else{showAlert({type,text})}

			position++;
			isLoadingMsg = false;

		},'JSON').fail(function(xhr,ajaxOption,thrownError){
			console.error(xhr.responseText)
			isLoadingMsg = false;
		});

	};	
	
	//handle contact click and initial page load
	let contactRunOnce = true;
	$('.im-contact-list').on('click','li',function(e){
		e.preventDefault();
		element = this;
		//console.log(element);
		if($(element).data('contactid') != contactid || contactRunOnce){
			contactRunOnce = false;
			contactid = $(element).data('contactid');
			//console.log(contactid);
			$(element).addClass('active').siblings().removeClass('active');
			position = 0; //current position
			pageCount = 0; //total pages 'update after 1st call'
			$('.im-container-header').removeClass('show').html('');
			$('.im-wrapper').css('justify-content','center');
			$('.im-wrapper').html('<li style="align-self: center"><i class="fab fa-gg fa-pulse fa-3x"></i></li>');
			loadMessage();
		}
	});
	
	//load message history
	$('.im-container-header').on('click','a',function(e){
		e.preventDefault();
		loadMessage();
	});
	
	//handle message sending
	let message = '';
	const sendMessage = function(){
		
		if(isLoadingMsg) return;
		
		formData = {'txt_contactid':contactid,'txt_message':message};
		formData = $.extend(formData,{txt_lastimid:$('.im-wrapper li:last-child').data('imid')});

		$.post('ajax/im-message-add.php',formData,function(res){
			//console.log(res);
			const {type,text,data} = res;
			
			if(type !== 'success')
				return showAlert({type,text});

			if(type == 'success' && data.html){
				//imid = data.status_data.imid;
				$('.im-wrapper').append(data.html);
				$('.im-wrapper-box').scrollTop($('.im-wrapper-box').prop('scrollHeight'));
			}

		},'JSON').fail(function(xhr,ajaxOptins,thrownError){
				//polling = false;
				console.error(xhr.responseText);
		});	
	};
	
	//handle send button click
	$('button[name="btn_send"]').click(function(){
		
		message = $('input[name="txt_message"]').val();
		
		if($.trim(message) == '') return;
		
		$('input[name="txt_message"]').val('');

		let refreshIntervalId = setInterval(function(){
			if(isLoadingMsg == false)	{
				clearInterval(refreshIntervalId);
				sendMessage();	
			}
		},300);
		
	});
	
	//handle enter key press click
	$('input[name="txt_message"]').keyup(function(e){
		//console.log(e.keyCode);
		message = $('input[name="txt_message"]').val();
		
		if(e.keyCode != 13 || $.trim(message) == '') return;
		
		$('input[name="txt_message"]').val('');
		
		let refreshIntervalId = setInterval(function(){
			if(isLoadingMsg == false)	{
				clearInterval(refreshIntervalId);
				sendMessage();	
			}
		},300);
	});	

}); // close document.ready
</script>