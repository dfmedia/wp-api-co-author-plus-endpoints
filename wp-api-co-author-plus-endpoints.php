<?php
/**
 * Plugin Name: WP REST API - Co-Authors Plus Endpoints
 * Description: WP REST API companion plugin for Co-Authors Plus endpoints
 * Author: Michael Jacobsen
 * Author URI: https://mjacobsen4dfm.wordpress.com/
 * Version: 0.1.3
 * Plugin URI: https://github.com/mjacobsen4DFM/wp-api-co-author-plus-endpoints
 * License: GPL2+
 */

function co_authors_rest_api_init(){
	/**
	 * ensure WP-API classes and this plugin's classes are available
	 */
	if ( class_exists( 'WP_REST_Controller' ) ) {
		/**
		 * WP_REST_CoAuthors_AuthorTerms classes.
		 */
		if ( ! class_exists( 'WP_REST_CoAuthors_AuthorTerms_Endpoint' ) ) {
			require_once dirname( __FILE__ ) . '/lib/endpoints/class-wp-rest-coauthors-authorterms-endpoint.php';
		}

		/**
		 * WP_REST_CoAuthors_AuthorPosts classes.
		 */
		if ( ! class_exists( 'WP_REST_CoAuthors_AuthorPosts_Endpoint' ) ) {
			require_once dirname( __FILE__ ) . '/lib/endpoints/class-wp-rest-coauthors-authorposts-endpoint.php';
		}

		/**
		 * WP_REST_CoAuthors_AuthorUsers classes.
		 */
		if ( ! class_exists( 'WP_REST_CoAuthors_AuthorUsers_Endpoint' ) ) {
			require_once dirname( __FILE__ ) . '/lib/endpoints/class-wp-rest-coauthors-authorusers-endpoint.php';
		}
	}

	/**
	 * Register the routes for the objects of the controllers.
	 * Ensure CoAuthors_Guest_Authors is registered
	 */
	if ( class_exists( 'CoAuthors_Guest_Authors' ) ) {
		// access 'co-authors/author-terms'
		$coauthors_authorterms_controller = new WP_REST_CoAuthors_AuthorTerms_Endpoint( 'post' );
		$coauthors_authorterms_controller->register_routes();

		// access 'co-authors/author-posts'
		$coauthors_authorposts_controller = new WP_REST_CoAuthors_AuthorPosts_Endpoint( 'post' );
		$coauthors_authorposts_controller->register_routes();

		// access 'co-authors/author-users'
		$coauthors_authorusers_controller = new WP_REST_CoAuthors_AuthorUsers_Endpoint( 'post' );
		$coauthors_authorusers_controller->register_routes();

	}
}

add_action( 'rest_api_init', 'co_authors_rest_api_init', 11, 0 );