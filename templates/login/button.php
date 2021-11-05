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
	.login-button-container .login-button {
		background-color: <?php echo esc_attr( $login_button_bg_color ); ?>;
		color: <?php echo esc_attr( $login_button_text_color ); ?>;
	}

	.login-button-container .login-button:hover {
		background-color: <?php echo esc_attr( $login_button_text_color ); ?>;
		color: <?php echo esc_attr( $login_button_bg_color ); ?>;
	}
</style>
<?php endif; ?>

<div class="login-button-container">
	<a href="<?php echo esc_url( $login_url ); ?>" class="login-button"><?php echo esc_html( $login_button_text ); ?></a>
</div>
