<?php
/**
 * The template for displaying header.
 *
 * @package HelloElementor
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
$site_name = get_bloginfo('name');
$tagline = get_bloginfo('description', 'display');
$header_nav_menu = wp_nav_menu([
	'theme_location' => 'menu-1',
	'fallback_cb' => false,
	'echo' => false,
]);
?>
<div class="lds-spinner-wrap sitewide_spinner">
	<div class="lds-spinner">
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
	</div>
	<span>Processing...</span>
</div>
<section id="fullwidth-header">

	<header id="site-header" class="site-header" role="banner">

		<div class="site-branding">
			<?php
			if (has_custom_logo()) {
				the_custom_logo();
			} elseif ($site_name) {
				?>
				<h1 class="site-title">
					<a href="<?php echo esc_url(home_url('/')); ?>" title="<?php esc_attr_e('Home', 'hello-elementor'); ?>"
						rel="home">
						<?php echo esc_html($site_name); ?>
					</a>
				</h1>
				<p class="site-description">
					<?php
					if ($tagline) {
						echo esc_html($tagline);
					}
					?>
				</p>
			<?php } ?>
		</div>

		<?php if ($header_nav_menu): ?>
			<?php if (is_current_user_admin() || is_current_user_editor()): ?>
				<nav class="site-navigation" role="navigation">
					<?php echo $header_nav_menu; ?>
				</nav>
			<?php endif; ?>
			<?php if (is_current_user_contributor()): ?>
				<nav class="site-navigation designer-nav" role="navigation">
					<ul>
						<li><a href="<?php echo esc_url(home_url('/')); ?>clients-without-logos">Clients Without Logos</a></li>
					</ul>
				</nav>
			<?php endif; ?>

		<?php endif; ?>
	</header>

</section>