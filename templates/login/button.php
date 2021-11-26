<?php
/**
 * Unlock login button
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

?>

<?php if ( $login_button_bg_color && $login_button_text_color ) : ?>
<style>
	.login .login-button-container .login-button,
	.login-button-container .login-button {
		background-color: <?php echo sanitize_hex_color( $login_button_bg_color ); ?>;
		color: <?php echo sanitize_hex_color( $login_button_text_color ); ?>;
	}

	.login .login-button-container .login-button:hover,
	.login-button-container .login-button:hover {
		background-color: <?php echo sanitize_hex_color( $login_button_text_color ); ?>;
		color: <?php echo sanitize_hex_color( $login_button_bg_color ); ?>;
	}
</style>
<?php endif; ?>

<?php do_action( 'unlock_before_login_button' ); ?>

<div class="login-button-container">
	<a href="<?php echo esc_url( $login_url ); ?>" class="login-button"><?php echo esc_html( $login_button_text ); ?></a>
</div>

<?php do_action( 'unlock_after_login_button' ); ?>
