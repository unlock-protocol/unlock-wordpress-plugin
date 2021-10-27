<?php
/**
 * Helper class for all helper function.
 *
 * @package unlock-protocol
 * @since 3.0.0
 */

namespace Unlock_Protocol\Inc\Utils;

/**
 * Class Helper
 *
 * @since 3.0.0
 */
class Helper {

	/**
	 * This method is an improved version of PHP's filter_input() and
	 * works well on PHP Cli as well which PHP default method does not.
	 *
	 * Reference: https://bugs.php.net/bug.php?id=49184
	 *
	 * @param int    $type          One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV.
	 * @param string $variable_name Name of a variable to get.
	 * @param int    $filter        The ID of the filter to apply.
	 * @param mixed  $options       filter to apply.
	 *
	 * @since 3.0.0
	 *
	 * @return mixed Value of the requested variable on success, FALSE if the filter fails, or NULL if the
	 *  variable_name variable is not set.
	 */
	public static function filter_input( $type, $variable_name, $filter = FILTER_DEFAULT, $options = null ) {

		if ( 'cli' !== php_sapi_name() ) {

			/**
			 * We can not have code coverage since.
			 * Since this will only execute when sapi is "fpm-fcgi".
			 * While Unit test case run on "cli"
			 */
			// @codeCoverageIgnoreStart

			/**
			 * Code is not running on PHP Cli and we are in clear.
			 * Use the PHP method and bail out.
			 */
			switch ( $filter ) {
				case FILTER_SANITIZE_STRING:
					$sanitized_variable = filter_input( $type, $variable_name, $filter );
					break;
				default:
					$sanitized_variable = filter_input( $type, $variable_name, $filter, $options );
					break;
			}

			return $sanitized_variable;
			// @codeCoverageIgnoreEnd
		}

		/**
		 * Code is running on PHP Cli and INPUT_SERVER returns NULL
		 * even for set vars when run on Cli
		 * See: https://bugs.php.net/bug.php?id=49184
		 *
		 * This is a workaround for that bug till its resolved in PHP binary
		 * which doesn't look to be anytime soon. This is a friggin' 10 year old bug.
		 */

		$input = '';

		$allowed_html_tags = wp_kses_allowed_html( 'post' );

		/**
		 * Marking the switch() block below to be ignored by PHPCS
		 * because PHPCS squawks on using superglobals like $_POST or $_GET
		 * directly but it can't be helped in this case as this code
		 * is running on Cli.
		 */

		// @codingStandardsIgnoreStart

		switch ( $type ) {

			case INPUT_GET:
				if ( ! isset( $_GET[ $variable_name ] ) ) {
					return null;
				}

				$input = wp_kses( $_GET[ $variable_name ], $allowed_html_tags );
				break;

			case INPUT_POST:
				if ( ! isset( $_POST[ $variable_name ] ) ) {
					return null;
				}

				$input = wp_kses( $_POST[ $variable_name ], $allowed_html_tags );
				break;

			case INPUT_COOKIE:
				if ( ! isset( $_COOKIE[ $variable_name ] ) ) {
					return null;
				}

				$input = wp_kses( $_COOKIE[ $variable_name ], $allowed_html_tags );
				break;

			case INPUT_SERVER:
				if ( ! isset( $_SERVER[ $variable_name ] ) ) {
					return null;
				}

				$input = wp_kses( $_SERVER[ $variable_name ], $allowed_html_tags );
				break;

			case INPUT_ENV:
				if ( ! isset( $_ENV[ $variable_name ] ) ) {
					return null;
				}

				$input = wp_kses( $_ENV[ $variable_name ], $allowed_html_tags );
				break;

			default:
				return null;
				break;

		}

		// @codingStandardsIgnoreEnd

		return filter_var( $input, $filter );

	}

	/**
	 * Checks if username exists, if it does, creates a
	 * unique username by appending digits.
	 *
	 * @param string $username Username.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function unique_username( $username ) {
		$uname = $username;
		$count = 1;

		while ( username_exists( $uname ) ) {
			$uname = $uname . '' . $count;
		}

		return $uname;
	}
}
