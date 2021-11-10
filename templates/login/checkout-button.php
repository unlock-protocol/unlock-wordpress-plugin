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
	.checkout-button-container .checkout-button {
		background-color: <?php echo esc_attr( $checkout_button_bg_color ); ?>;
		color: <?php echo esc_attr( $checkout_button_text_color ); ?>;
	}

	.checkout-button-container .checkout-button:hover {
		background-color: <?php echo esc_attr( $checkout_button_text_color ); ?>;
		color: <?php echo esc_attr( $checkout_button_bg_color ); ?>;
	}
</style>
<?php endif; ?>

<div class="checkout-button-container">
	<a href='<?php echo $checkout_url; //phpcs:ignore ?>' class="checkout-button"><?php echo esc_html( $checkout_button_text ); ?></a>
</div>
