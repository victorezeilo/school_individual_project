			<!-- jQuery UI Dialog -->
			<div id="confirm-action" style="display:none;"><p class="m-10"></p></div>
		</main>
	</div>
		<!--Smart Alert-->
		<script type="text/javascript">showAlert(<?php print($response); ?>)</script>
		<script type="text/javascript">
			jQuery(($) => {
					
/*				$('.side-nav > li > label').click(function(e){
					e.preventDefault();
					const element = this;
					$(element).addClass('show');
					$(element).closest('li').siblings().find('label:not(.active)').removeClass('show');
				});*/
				
				$('.side-nav > li > label:not(.active)').click(function(e){
					e.preventDefault();
					const el = this;					
					$(el).toggleClass('show');
					$(el).closest('li').siblings().find('label:not(.active)').removeClass('show');
				});
				
			});
		</script>
	</body>
</html>