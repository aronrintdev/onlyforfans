<nav class="navbar socialite navbar-default no-bg guest-nav">
	<div class="container">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<!-- <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-4" aria-expanded="false">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button> -->
			<a class="navbar-brand socialite" href="<?php echo e(url('/')); ?>" style="padding-top:20px;">
			    Fans Platform
                
			</a>
		</div>
		<?php if(Auth::guest()): ?>
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">












			</form>
		</div>
		<?php endif; ?>
		
	</div><!-- /.container-fluid -->
</nav>	
