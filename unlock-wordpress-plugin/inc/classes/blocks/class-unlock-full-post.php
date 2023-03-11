<?php
/**
 * Unlock box dynamic full post class.
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc\Blocks;

 use Unlock_Protocol\Inc\Login;
 use Unlock_Protocol\Inc\Traits\Singleton;
 use Unlock_Protocol\Inc\Unlock;
 
 /**
  * Class Unlock_Full_Post
  *
  * @since 3.0.0
  */
class Unlock_Full_Post {

	use Singleton;

	/**
	 * Construct method.
	 *
	 * @since 3.0.0
	 */
	protected function __construct() {

		$this->setup_hooks();

	}

	/**
	 * Setup hooks.
	 *
	 * @since 3.0.0
	 */
	protected function setup_hooks() {

		add_action('add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action('save_post', array( $this, 'save_custom_meta' ) );
	}



	// Add custom meta box with "Add Lock" button
	public function add_meta_box() {
		add_meta_box(
			'unlock_meta_box', // ID
			'Unlock', // title
			array( $this, 'show_custom_meta_box' ), // callback function
			'post', // post type
			'side', // position
			'high' // priority
		);
	}

	// Show custom meta box with "Lock Networks" dropdown and "Lock Contact ID" input box
	function show_custom_meta_box() 
	{
		global $post;
		$meta_network = get_post_meta($post->ID, 'unlock_network_meta', true);
		$meta_id = get_post_meta($post->ID, 'unlock_id_meta', true);
		
		?>
	
			<button id="add-lock-button" onclick="toggleLockInputs()">Add Lock</button>
		
			<div id="lock-inputs" style="display: none;">
				<label for="unlock_network_meta">Lock Networks:</label>
				<select name="unlock_network_meta" id="unlock-network-select" onchange="toggleLockIdInput()">
					<option value="" <?php selected($meta_network, ""); ?>></option>
					<option value="mainnet" <?php selected($meta_network, "mainnet"); ?>>Mainnet</option>
					<option value="polygon" <?php selected($meta_network, "polygon"); ?>>Polygon</option>
					<option value="gnosis" <?php selected($meta_network, "gnosis"); ?>>Gnosis</option>
					<option value="bnb" <?php selected($meta_network, "bnb"); ?>>BNB Chain</option>
				</select>
			
				<div id="lock-id-input" style="display: <?php echo ($meta_network ? "block" : "none"); ?>;">
					<label for="unlock_id_meta">Lock Contact ID:</label>
					<input type="text" name="unlock_id_meta" id="unlock-id-input" value="<?php echo esc_attr($meta_id); ?>" />
				</div>
			</div>
		
			<script>
				function toggleLockInputs() {
					var lockInputs = document.getElementById("lock-inputs");
					if (lockInputs.style.display === "none") {
					lockInputs.style.display = "block";
					document.getElementById("add-lock-button").innerHTML = "Remove Lock";
					} else {
					lockInputs.style.display = "none";
					document.getElementById("add-lock-button").innerHTML = "Add Lock";
					}
				}
			
				function toggleLockIdInput() {
					var lockIdInput = document.getElementById("lock-id-input");
					if (document.getElementById("unlock-network-select").value === "") {
					lockIdInput.style.display = "none";
					} else {
					lockIdInput.style.display = "block";
					}
				}
			</script>
		<?php 
	}
  
  // Save custom meta data
  function save_custom_meta($post_id) {
	if (isset($_POST['unlock_network_meta'])) {
	  update_post_meta($post_id, 'unlock_network_meta', sanitize_text_field($_POST['unlock_network_meta']));
	  if (isset($_POST['unlock_id_meta'])) {
		update_post_meta($post_id, 'unlock_id_meta', sanitize_text_field($_POST['unlock_id_meta']));
	  } else {
		delete_post_meta($post_id, 'unlock_id_meta');
	  }
	} else {
	  delete_post_meta($post_id, 'unlock_network_meta');
	  delete_post_meta($post_id, 'unlock_id_meta');
	}
  }



}
