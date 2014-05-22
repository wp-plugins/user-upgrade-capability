<?php get_header(); ?>

	<div id="content">

		<div class="hfeed">

			<h1 class="entry-title">Member Content</h1>

			<div class="alert">
				<?php if ( !is_user_logged_in() ) { ?>
					<p>This page can only be viewed by members.  If you're already a member, please take a moment to log into the site.</p>
				<?php } else { ?>
					<p>Sorry but this page can only be viewed by members.</p>
				<?php } ?>
			</div>

		</div><!-- .hfeed -->

	</div><!-- #content -->

<?php get_footer(); // Loads the footer.php template. ?>