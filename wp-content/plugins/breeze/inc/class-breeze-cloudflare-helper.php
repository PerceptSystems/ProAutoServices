<?php
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * This class handles Cloudways - CloudFlare microservice action.
 *
 * @since 2.0.15
 * @final
 */
final class Breeze_CloudFlare_Helper {

	function __construct() {
		add_action( 'switch_theme', array( &$this, 'clear_cf_on_changing_theme' ), 11, 3 );
	}

	/**
	 * Define Microservice url.
	 *
	 * @return false|string
	 * @since 2.0.15
	 * @private
	 */
	private function get_microservice_url() {
		if ( false === self::is_cloudflare_enabled() ) {
			return false;
		}

		/**
		 * Contains the dynamic microservice URL.
		 */
		$fpc_microservice_url = getenv( 'FPC_ENV' );

		if ( empty( $fpc_microservice_url ) ) {
			return false;
		}

		return trailingslashit( $fpc_microservice_url );
	}

	/**
	 * Purge Cloudflare cache on theme switch.
	 *
	 * @param string $new_name Name of the new theme.
	 * @param string $new_theme WP_Theme instance of the new theme.
	 * @param string $old_theme WP_Theme instance of the old theme.
	 *
	 * @return void
	 * @since 2.0.15
	 * @public
	 */
	public function clear_cf_on_changing_theme( string $new_name, string $new_theme, string $old_theme ) {
		$list_of_urls[] = get_home_url();
		Breeze_CloudFlare_Helper::reset_all_cache( $list_of_urls );
	}

	/**
	 * Clear the cache for the given url list.
	 * Needs at least one element.
	 *
	 * @param array $specific_urls Array with the list of URLs.
	 *
	 * @return bool|string|null
	 * @since 2.0.15
	 * @access public
	 * @static
	 */
	static public function purge_cloudflare_cache_urls( array $specific_urls = array() ) {
		// If we do not have everything that we need, stop the process.
		if ( true === self::is_log_enabled() ) {
			error_log( '######### PURGE CLOUDFLARE ###: ' . var_export( 'Single URL START', true ) );
		}
		if ( false === self::is_cloudflare_enabled() ) {
			return false;
		}
		// Remove any non URL items.
		$specific_urls = ( new Breeze_CloudFlare_Helper )->remove_not_url_elements( $specific_urls );
		if ( true === self::is_log_enabled() ) {
			error_log( 'single url : ' . var_export( $specific_urls, true ) );
		}

		// Call cache reset.
		return ( new Breeze_CloudFlare_Helper )->request_cache_reset( $specific_urls, 'purge-fpc-url' );
	}

	/**
	 * Purge all cache in CloudFlare.
	 * In multisite clears for all sub-sites.
	 *
	 * @param array $home_url Used by WP-CLI
	 *
	 * @return bool|string|null
	 * @since 2.0.15
	 * @access public
	 * @static
	 */
	static public function reset_all_cache( array $home_url = array() ) {
		// If we do not have everything that we need, stop the process.
		if ( false === self::is_cloudflare_enabled() ) {
			return false;
		}

		if ( ! is_array( $home_url ) ) {
			$home_url = array();
		}

		/**
		 * Execute code if this function is not called by WP-CLI.
		 */
		if ( 'cli' !== php_sapi_name() ) {

			// For multisite network, clear cache for all sub-sites.
			if ( ( is_multisite() && is_network_admin() ) ) {
				$blogs = get_sites();
				foreach ( $blogs as $blog_data ) {
					$url        = get_home_url( $blog_data->blog_id );
					$home_url[] = trailingslashit( $url );
				}
			} else {
				$home_url[] = trailingslashit( home_url() );
			}
		}

		$purge_request_endpoint = 'purge-fpc-domain';

		if ( is_multisite() ) {
			if ( is_subdomain_install() ) {
				$home_url = ( new Breeze_CloudFlare_Helper )->clear_domain_purge_urls( $home_url );
				if ( true === self::is_log_enabled() ) {
					error_log( '######### CF SubDomains: ' . var_export( $home_url, true ) );
				}
			} else {
				$purge_request_endpoint = 'purge-fpc-sub-dir';
				if ( ! empty( $home_url ) ) {
					foreach ( $home_url as &$url ) {
						$url = untrailingslashit( $url );
					}
					if ( true === self::is_log_enabled() ) {
						error_log( '######### CF SubDirectory: ' . var_export( $home_url, true ) );
					}
				}
			}
		} else {
			$home_url = ( new Breeze_CloudFlare_Helper )->clear_domain_purge_urls( $home_url );
			if ( true === self::is_log_enabled() ) {
				error_log( '######### CF Domain: ' . var_export( $home_url, true ) );
			}
		}

		return ( new Breeze_CloudFlare_Helper )->request_cache_reset( $home_url, $purge_request_endpoint );
	}

	/**
	 * Clear the list of URLs of HTTP schema and remove the slash at the end.
	 * This is needed for domain CF purge.
	 *
	 * @param array $url_list List of URLs.
	 *
	 * @return array
	 */
	private function clear_domain_purge_urls( array $url_list = array() ): array {
		if ( empty( $url_list ) ) {
			return $url_list;
		}

		foreach ( $url_list as &$url ) {
			$url = trim( $url );
			$url = ltrim( $url, 'https:' );
			$url = ltrim( $url, '//' );
			$url = untrailingslashit( $url );
		}

		return $url_list;
	}

	/**
	 * Remove all array elements which are not a valid URL.
	 *
	 * @param array $url_list Given url list.
	 *
	 * @return array
	 *
	 * @access private
	 * @since 2.0.15
	 */
	private function remove_not_url_elements( array $url_list = array() ): array {
		// Remove any white spaces from URL list.
		$url_list = array_map( 'trim', $url_list );
		// Making sure the urls have the "/" ar the end.
		$url_list = array_map( 'trailingslashit', $url_list );
		// Making sure there are no duplicates.
		$url_list = array_unique( $url_list );

		return array_filter(
			$url_list,
			function ( $value, $index ) {
				return false !== filter_var( $value, FILTER_VALIDATE_URL );
			},
			ARRAY_FILTER_USE_BOTH
		);

	}

	/**
	 * Will return true if defined constants are found.
	 *
	 * @return bool
	 *
	 * @since 2.0.15
	 * @access public
	 * @static
	 */
	static public function is_cloudflare_enabled(): bool {
		if (
			! defined( 'CDN_SITE_ID' ) ||
			! defined( 'CDN_SITE_TOKEN' )
		) {
			if ( true === self::is_log_enabled() ) {
				error_log( 'Error: CDN_SITE_ID or CDN_SITE_TOKEN not defined' );
			}

			return false;
		}

		return true;
	}

	/**
	 * Handles the request for purge
	 *
	 * @param array $purge_url_list list of URLs for which to purge cache;
	 * @param string $endpoint_path Endpoint path to clear URL cache or whole domain cache.
	 *
	 * @return bool|string|void
	 * @access private
	 * @since 2.0.15
	 */
	private function request_cache_reset( array $purge_url_list = array(), string $endpoint_path = 'purge-fpc-url' ) {

		if (
			false === self::is_cloudflare_enabled() ||
			empty( $purge_url_list )
		) {
			return;
		}

		// Remove any white spaces from URL list.
		$purge_url_list = array_map( 'trim', $purge_url_list );
		// Making sure there are no duplicates.
		$purge_url_list = array_unique( $purge_url_list );
		// Remove empty values.
		$purge_url_list = array_values( array_filter( $purge_url_list ) );

		if ( empty( $purge_url_list ) ) {
			return;
		}

		$verify_host      = 2;
		$ssl_verification = apply_filters( 'breeze_ssl_check_certificate', true );
		if ( ! is_bool( $ssl_verification ) ) {
			$ssl_verification = true;
		}

		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
			$ssl_verification = false;
			$verify_host      = 0;
		}

		// if SSL verification is turned to false then we need to change $verify_host also.
		if ( false === $ssl_verification ) {
			$verify_host = 0;
		}

		$rop_user_agent = 'breeze-plugin-cache-reset';

		$microservice_url = $this->get_microservice_url();

		if ( false === $microservice_url ) {
			if ( true === self::is_log_enabled() ) {
				error_log( 'Error: Microservice url is not defined ' );
			}

			return false;
		}

		$call_endpoint_url = $microservice_url . $endpoint_path;
		// start connection to microservice.
		if ( true === self::is_log_enabled() ) {
			error_log( '/' . $endpoint_path );
		}

		$connection = curl_init( $call_endpoint_url );
		curl_setopt( $connection, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $connection, CURLOPT_SSL_VERIFYHOST, $verify_host );
		curl_setopt( $connection, CURLOPT_SSL_VERIFYPEER, $ssl_verification );
		curl_setopt( $connection, CURLOPT_POST, true );
		curl_setopt( $connection, CURLOPT_USERAGENT, $rop_user_agent );
		curl_setopt( $connection, CURLOPT_REFERER, home_url() );
		curl_setopt( $connection, CURLOPT_TIMEOUT, 4 );

		// Array to send to microservice.
		$data_to_send = array(
			'urls'     => $purge_url_list,
			'appToken' => CDN_SITE_TOKEN,
			'appId'    => CDN_SITE_ID,
		);
		if ( true === self::is_log_enabled() ) {
			error_log( 'List of URL(s) to be sent: ' . var_export( $data_to_send['urls'], true ) );
		}

		// Convert data to JSON.
		if ( ! empty( $data_to_send ) ) {
			$data_to_send = wp_json_encode( $data_to_send );
			curl_setopt( $connection, CURLOPT_POSTFIELDS, $data_to_send );
		}

		// Set request headers.
		curl_setopt(
			$connection,
			CURLOPT_HTTPHEADER,
			array(
				'Accept: application/json',
				'Content-Type: application/json',
				'Content-Length: ' . strlen( $data_to_send ),
			)
		);

		/**
		 * Accept up to 3 maximum redirects before cutting the connection.
		 */
		curl_setopt( $connection, CURLOPT_MAXREDIRS, 2 );
		curl_setopt( $connection, CURLOPT_FOLLOWLOCATION, true );

		$server_response_body = curl_exec( $connection );
		curl_close( $connection );
		if ( true === self::is_log_enabled() ) {
			error_log( 'Microservice response: ' . var_export( $server_response_body, true ) );
		}

		return $server_response_body;
	}

	/**
	 * Check if WP_DEBUG is set to true.
	 * if true then enable logs for this library.
	 *
	 * @return bool
	 *
	 * @since 2.0.15
	 * @access public
	 * @static
	 */
	static public function is_log_enabled(): bool {
		if (
			defined( 'BREEZE_CF_DEBUG' ) &&
			true === filter_var( BREEZE_CF_DEBUG, FILTER_VALIDATE_BOOLEAN )
		) {
			return true;
		}

		return false;
	}
}

new Breeze_CloudFlare_Helper();