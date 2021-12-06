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
	<?php if ( $login_bg_image ) : ?>
	.login .login-button-container.blurred,
	.login-button-container.blurred {
		background: url('<?php echo esc_url( $login_bg_image ); ?>') no-repeat center center;
		background-size: cover;
	}
	<?php endif; ?>

	<?php if ( $blurred_image_activated ) : ?>
	.login-button-container.blurred p {
		color: <?php echo sanitize_hex_color( $login_button_text_color ); ?>;
	}
	<?php endif; ?>

	.login .login-button-container .login-button,
	.login-button-container .login-button {
		background-color: <?php echo sanitize_hex_color( $login_button_bg_color ); ?>;
		color: <?php echo sanitize_hex_color( $login_button_text_color ); ?>;
	}

	.login .login-button-container .login-button:hover,
	.login .login-button-container .login-button:focus,
	.login-button-container .login-button:hover,
	.login-button-container .login-button:focus {
		background-color: <?php echo sanitize_hex_color( $login_button_text_color ); ?> !important; 
		color: <?php echo sanitize_hex_color( $login_button_bg_color ); ?>;
	}

</style>
<?php endif; ?>

<?php do_action( 'unlock_before_login_button' ); ?>

<div class="login-button-container <?php echo $blurred_image_activated ? esc_attr( 'blurred' ) : ''; ?>">
	<?php
	if ( $blurred_image_activated ) {
		printf( '<p>%s</p>', esc_html( $login_button_description ) );
	}
	?>

	<a href="<?php echo esc_url( $login_url ); ?>" class="login-button"><?php echo esc_html( $login_button_text ); ?></a>
</div>

<?php do_action( 'unlock_after_login_button' ); ?>
