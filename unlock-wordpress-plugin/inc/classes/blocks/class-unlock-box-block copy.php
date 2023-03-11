<?php
/**
 * Unlock box dynamic block class.
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
 * Class Unlock_Box_Block
 *
 * @since 3.0.0
 */
class Unlock_Box_Block {

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
		/**
		 * Actions.
		 */
		add_action( 'init', array( $this, 'register_block_type' ) );

		//option1
		// add_action('add_meta_boxes', array( $this, 'add_meta_box' ) );
		// add_action('save_post', array( $this, 'save_custom_meta' ) );

		//Option2
		add_action('add_meta_boxes', array( $this, 'unlock_pv2_add_custom_meta_box' ) );
		add_action('save_post', array( $this, 'unlock_pv2_save_custom_meta' ) );
		add_action('the_content', array( $this, 'unlock_pv2_render_content' ) );



	}

	/**
	 * Register block type.
	 *
	 * @since 3.0.0
	 */
	public function register_block_type() {
		register_block_type(
			'unlock-protocol/unlock-box',
			array(
				'render_callback' => array( $this, 'render_block' ),
				'attributes'      => array(
					'locks'      => array(
						'type'    => 'array',
						'default' => array(),
					),
					'ethereumNetworks' => array(
						'type'    => 'array',
						'default' => array(),
					),
				),
				'supports'        => array(
					'align' => true,
				),
			)
		);
	}




	/**
	 * Render block.
	 *
	 * @param array  $attributes List of attributes passed in block.
	 * @param string $content Block Content.
	 *
	 * @since 3.0.0
	 *
	 * @return string HTML elements.
	 */
	public function render_block( $attributes, $content ) {
		// Bail out if current user is admin or the author.
		if ( current_user_can( 'manage_options' ) || ( get_the_author_meta( 'ID' ) === get_current_user_id() ) ) {
			return $content;
		}

		if (
			! is_user_logged_in() ||
			( is_user_logged_in() && ! up_get_user_ethereum_address() )
		) {
			$login_button_text       = up_get_general_settings( 'login_button_text', __( 'Login with Unlock', 'unlock-protocol' ) );
			$login_button_bg_color   = up_get_general_settings( 'login_button_bg_color', '#000' );
			$login_button_text_color = up_get_general_settings( 'login_button_text_color', '#fff' );
			$blurred_image_activated = wp_validate_boolean( up_get_general_settings( 'login_blurred_image_button', false ) );

			$template_data = array(
				'login_url'               => Unlock::get_login_url( get_permalink() ),
				'login_button_text'       => $login_button_text,
				'login_button_bg_color'   => $login_button_bg_color,
				'login_button_text_color' => $login_button_text_color,
				'blurred_image_activated' => $blurred_image_activated,
			);

			// Fetching some more data if blurred image button type is activated.
			if ( $blurred_image_activated ) {
				$login_button_description = up_get_general_settings( 'login_button_description', __( 'To view this content please', 'unlock-protocol' ) );
				$login_bg_image           = up_get_general_settings( 'login_bg_image' );

				$template_data['login_button_description'] = $login_button_description;
				$template_data['login_bg_image']           = $login_bg_image;
			}

			$html_template = unlock_protocol_get_template( 'login/button', $template_data );

			return apply_filters( 'unlock_protocol_login_content', $html_template, $template_data );
		}

		$locks = $attributes['locks'];

		$settings = get_option( 'unlock_protocol_settings', array() );
		$networks = isset( $settings['networks'] ) ? $settings['networks'] : array();

		if ( Unlock::has_access( $networks, $locks ) ) {
			return $content;
		}

		return $this->get_checkout_url( $attributes );
	}



	/**
	 * Get checkout url for block
	 *
	 * @param array $attributes attributes.
	 * @param array $networks networks from configuration.
	 *
	 * @return mixed|void
	 */
	private function get_checkout_url( $attributes ) {
		$checkout_url = Unlock::get_checkout_url( $attributes["locks"], get_permalink() );

		$checkout_button_text       = up_get_general_settings( 'checkout_button_text', __( 'Purchase this', 'unlock-protocol' ) );
		$checkout_button_bg_color   = up_get_general_settings( 'checkout_button_bg_color', '#000' );
		$checkout_button_text_color = up_get_general_settings( 'checkout_button_text_color', '#fff' );
		$blurred_image_activated    = wp_validate_boolean( up_get_general_settings( 'checkout_blurred_image_button', false ) );

		$template_data = array(
			'checkout_url'               => $checkout_url,
			'checkout_button_text'       => $checkout_button_text,
			'checkout_button_bg_color'   => $checkout_button_bg_color,
			'checkout_button_text_color' => $checkout_button_text_color,
			'blurred_image_activated'    => $blurred_image_activated,
		);

		// Fetching some more data if blurred image button type is activated.
		if ( $blurred_image_activated ) {
			$checkout_button_description = up_get_general_settings( 'checkout_button_description', __( 'To view this content please', 'unlock-protocol' ) );
			$checkout_bg_image           = up_get_general_settings( 'checkout_bg_image' );

			$template_data['checkout_button_description'] = $checkout_button_description;
			$template_data['checkout_bg_image']           = $checkout_bg_image;
		}

		$html_template = unlock_protocol_get_template( 'login/checkout-button', $template_data );

		return apply_filters( 'unlock_protocol_checkout_content', $html_template, $template_data );
	}




// ===================================================================START OF FULL POST/PAGE CONTENT LOCK/UNLOCK FEATURE
// Create and Add custom meta box with "Add Lock" button
function unlock_pv2_add_custom_meta_box() 
{
    $post_types = get_post_types(); //detect any custom post types like courses and add lock features automatically
    foreach ($post_types as $post_type) {
        add_meta_box(
            'unlock_pv2_meta_box', // ID
            'Unlock Protocol', // title
			array( $this, 'unlock_pv2_show_custom_meta_box' ), // callback function
            $post_type, // post type
            'side', // position
            'high' // priority
        );
    }
}


// Show custom meta box with "Lock Networks" dropdown and "Lock Contract ID" input box
function unlock_pv2_show_custom_meta_box() 
{
    global $post;
    $meta_network = get_post_meta($post->ID, 'unlock_pv2_network_meta', true);
    $meta_id = get_post_meta($post->ID, 'unlock_pv2_id_meta', true);
    
    ?>
  
        <button id="add-lock-button" onclick="toggleLockInputs()">Add Lock</button>

        <div id="lock-inputs" style="display: none;">
            <label for="unlock_pv2_network_meta">Lock Networks:</label>
            <select name="unlock_pv2_network_meta" id="unlock-network-select" onchange="toggleLockIdInput()">
            <option value="" <?php selected($meta_network, ""); ?>></option>
            <option value="mainnet" <?php selected($meta_network, "mainnet"); ?>>Mainnet</option>
            <option value="polygon" <?php selected($meta_network, "polygon"); ?>>Polygon</option>
            <option value="gnosis" <?php selected($meta_network, "gnosis"); ?>>Gnosis</option>
            <option value="bnbchain" <?php selected($meta_network, "bnbchain"); ?>>BNBChain</option>
            <option value="optimism" <?php selected($meta_network, "optimism"); ?>>Optimism</option>
            <option value="arbitrum" <?php selected($meta_network, "arbitrum"); ?>>Arbitrum</option>
            </select>

            <div id="lock-id-input" style="display: <?php echo ($meta_network ? "block" : "none"); ?>;">
            <label for="unlock_pv2_id_meta">Lock Contract ID:</label>
            <input type="text" name="unlock_pv2_id_meta" id="unlock-id-input" value="<?php echo esc_attr($meta_id); ?>" />
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
  



// Network Input: Validate and sanitize network input
function unlock_pv2_validate_network_input($input) 
{
    $valid_networks = array('mainnet', 'polygon', 'gnosis', 'bnb', 'optimism', 'arbitrum');
    if (in_array($input, $valid_networks)) {
        return sanitize_text_field($input);
    } else {
        return '';
    }
}




// Lock Contract ID: Validate and sanitize Contract ID input
function unlock_pv2_validate_contract_id_input($input) 
{
    // Sanitize input
    $input = sanitize_text_field($input);

    // Check if input is a valid Ethereum address
    if (preg_match('/^(0x)?[0-9a-f]{40}$/i', $input)) {
        return $input;
    } else {
        return 'error: invalid lock contract address ID added';
    }
}



// Save lock custom meta data to the meta of post/page it was added
function unlock_pv2_save_custom_meta($post_id) 
{
    if (isset($_POST['unlock_pv2_network_meta'])) {
        
        $network_input = unlock_pv2_validate_network_input($_POST['unlock_pv2_network_meta']);
        
        if ($network_input) {

            //Save lock network to the meta of specific post/page it was added
            update_post_meta($post_id, 'unlock_pv2_network_meta', $network_input);

            if (isset($_POST['unlock_pv2_id_meta'])) {

                $id_input = unlock_pv2_validate_contract_id_input($_POST['unlock_pv2_id_meta']);

                if (!is_wp_error($id_input)) {

                    //Save lock address id to the meta of specific post/page it was added
                    update_post_meta($post_id, 'unlock_pv2_id_meta', $id_input);

                }
            }
        }
    }
}




// Lock Full post/Page Content and Add Unlock form for user to put lock id used to lock the post to unlock it
function unlock_pv2_render_content($content) 
{
    global $post;

    //If user admin/author of the locked content, auto-unlock it
    if ( current_user_can( 'manage_options' ) || ( get_the_author_meta( 'ID' ) === get_current_user_id() ) ) {
        return $content;
    }    

    // Get the lock ID and network from post meta
    $network = get_post_meta($post->ID, 'unlock_pv2_network_meta', true);
    $lockAddressId = get_post_meta($post->ID, 'unlock_pv2_id_meta', true);

    // If the post has been locked
    if (!empty($network) && !empty($lockAddressId)) {

        // Check if user has entered the correct lock address
        if (isset($_POST['unlock_pv2_lock_id']) && !empty($_POST['unlock_pv2_lock_id'])) {
            $user_lock_id = sanitize_text_field($_POST['unlock_pv2_lock_id']);
            if ($user_lock_id === $lockAddressId) {
                return $content; // Post unlocked
            } else {
                $error_message = "Incorrect Lock Address Entered, Unable to unlock content";
            }
        }

        // Show input box
        $output = '<div class="unlock-pv2-content-locked">';
        $output .= '<p>Please enter the correct Lock Address to unlock the content:</p>';
        if (!empty($error_message)) {
            $output .= '<p class="unlock-pv2-error">' . $error_message . '</p>';
        }
        $output .= '<form method="post">';
        $output .= '<input type="text" name="unlock_pv2_lock_id" id="unlock-pv2-lock-id" />';
        $output .= '<input type="submit" name="unlock_pv2_submit" value="Unlock" />';
        $output .= '</form>';
        $output .= '</div>';

        return $output;        

    } else {

        return $content;  

    }
}



// ============================================================================END OF FULL POST/PAGE CONTENT LOCK/UNLOCK FEATURE



}
