<?php
/**
 * Rest Routes
 *
 * @package WordPress
 */
namespace WebinaneCommerce\Classes;

use WP_REST_Request;

/**
 * Class Rest Routes.
 */
class RestRoutes {

	/**
	 * Initialize.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'rest_init' ) );
	}

	/**
	 * Initialize Rest API Routes.
	 *
	 * @return void
	 */
	public static function rest_init() {

		register_rest_route(
			'webinane-commerce/v1',
			'/countries',
			array(
				'methods' => 'GET',
				'callback' => array( __CLASS__, 'countries' ),
				'permission_callback' => '__return_true',
				'args' => array(
				),
			)
		);
		register_rest_route(
			'webinane-commerce/v1',
			'/countries/(?P<country>\w+)/states',
			array(
				'methods' => 'GET',
				'callback' => array( __CLASS__, 'states' ),
				'permission_callback' => '__return_true',
				'args' => array(
					'country' => array(
						'validate_callback' => function( $param, $request, $key ) {
							return ( is_string( $param ) && strlen( $param ) === 3 );
						},
						'required' => true,
					),
				),
			)
		);
		register_rest_route(
			'webinane-commerce/v1',
			'/states',
			array(
				'methods' => 'GET',
				'callback' => array( __CLASS__, 'allStates' ),
				'permission_callback' => '__return_true',
				'args' => array(
				),
			)
		);
		
	}

	/**
	 * States REST API route.
	 *
	 * @param  WP_REST_Request $request
	 * @return array
	 */
	public static function states( WP_REST_Request $request ) {
		$country = $request->get_param( 'country' );

		$list = include WNCM_PATH . 'assets/data/states.php';
		$states = array_get( $list, strtoupper( $country ) );

		return array( 'states' => $states );
	}

	/**
	 * States REST API route.
	 *
	 * @param  WP_REST_Request $request
	 * @return array
	 */
	public static function countries( WP_REST_Request $request ) {
		
		$list = include WNCM_PATH . 'assets/data/_countries.php';

		return array( 'countries' => $list );
	}
	
	/**
	 * States REST API route.
	 *
	 * @param  WP_REST_Request $request
	 * @return array
	 */
	public static function allStates( WP_REST_Request $request ) {
		
		$list = include WNCM_PATH . 'assets/data/states.php';

		return array( 'states' => $list );
	}
	
}
