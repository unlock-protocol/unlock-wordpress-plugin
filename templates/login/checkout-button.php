<?php
/**
 * Unlock checkout button
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

?>

<?php if ( $checkout_button_bg_color && $checkout_button_text_color ) : ?>
<style>
	<?php if ( $checkout_bg_image ) : ?>
	.checkout-button-container.blurred,
	.checkout-button-container.blurred {
		background: url('<?php echo esc_url( $checkout_bg_image ); ?>') no-repeat center center;
		background-size: cover;
	}

	.checkout-button-container.blurred p {
		color: <?php echo sanitize_hex_color( $checkout_button_text_color ); ?>;
	}
	<?php endif; ?>

	.checkout-button-container .checkout-button {
		background-color: <?php echo sanitize_hex_color( $checkout_button_bg_color ); ?>;
		color: <?php echo sanitize_hex_color( $checkout_button_text_color ); ?>;
	}

	.checkout-button-container .checkout-button:hover {
		background-color: <?php echo sanitize_hex_color( $checkout_button_text_color ); ?>;
		color: <?php echo sanitize_hex_color( $checkout_button_bg_color ); ?>;
	}
</style>
<?php endif; ?>

<?php do_action( 'unlock_before_checkout_button' ); ?>

<div class="checkout-button-container <?php echo $blurred_image_activated ? esc_attr( 'blurred' ) : ''; ?>">
	<?php
	if ( $blurred_image_activated ) {
		printf( '<p>%s</p>', esc_html( $checkout_button_description ) );
	}

	/**
	 * Not using esc_url() intentionally. esc_url removes the `{}`
	 * Which is mandatory for unlock protocol checkout.
	 */
	?>
	<a href='<?php echo $checkout_url; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>' class="checkout-button"><?php echo esc_html( $checkout_button_text ); ?></a>
</div>

<?php do_action( 'unlock_after_checkout_button' ); ?>
