<?php
/**
 * Class Name: WP_REST_CoAuthors_AuthorTerms_Controller
 * Author: Michael Jacobsen
 * Author URI: https://mjacobsen4dfm.wordpress.com/
 * License: GPL2+
 *
 * CoAuthors_AuthorTerms controller class.
 */

if ( ! class_exists( 'WP_REST_CoAuthors_AuthorTerms' ) ) {
	require_once dirname( __FILE__ ) . '/../inc/class-wp-rest-coauthors-authorterms.php';
}

abstract class WP_REST_CoAuthors_AuthorTerms_Controller extends WP_REST_Controller {
	/**
	 * Taxonomy for Co-Authors.
	 *
	 * @var string
	 */
	protected $coauthor_taxonomy;

	/**
	 * Post_type for Co-Authors.
	 *
	 * @var string
	 */
	protected $coauthor_post_type;

	/**
	 * Associated co-author object type.
	 *
	 * @var WP_REST_CoAuthors_AuthorTerms
	 */
	protected $AuthorTerm = null;

	/**
	 * Associated parent type.
	 *
	 * @var string ("post")
	 */
	protected $parent_type = null;

	/**
	 * Associated parent post type name.
	 *
	 * @var string
	 */
	protected $parent_base = null;


	/**
	 * WP_REST_CoAuthors_Controller constructor.
	 */
	public function __construct() {
		if ( empty( $this->parent_type ) ) {
			_doing_it_wrong( 'WP_REST_Meta_Controller::__construct', __( 'The object type must be overridden' ), 'WPAPI-2.0' );

			return;
		}
		if ( empty( $this->parent_base ) ) {
			_doing_it_wrong( 'WP_REST_Meta_Controller::__construct', __( 'The parent base must be overridden' ), 'WPAPI-2.0' );

			return;
		}

		if ( class_exists( 'WP_REST_CoAuthors_AuthorTerms' ) ) {
			$this->AuthorTerm         = new WP_REST_CoAuthors_AuthorTerms( $this->namespace, $this->rest_base, $this->parent_base, $this->parent_type );
			$this->coauthor_post_type = $this->AuthorTerm->coauthor_post_type;
		}
	}


	/**
	 * Register the authors-related routes.
	 */
	public function register_routes() {
		/**
		 * co-authors base
		 */
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this->AuthorTerm, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),

			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this->AuthorTerm, 'get_item' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),

			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->parent_base . '/(?P<parent_id>[\d]+)/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this->AuthorTerm, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this->AuthorTerm, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),

			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->parent_base . '/(?P<parent_id>[\d]+)/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this->AuthorTerm, 'get_item' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this->AuthorTerm, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),

			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$query_params                       = parent::get_collection_params();
		$query_params['context']['default'] = 'view';

		return $query_params;
	}

	/**
	 * Get the Term's schema, conforming to JSON Schema
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema   = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->coauthor_taxonomy,
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'description' => __( 'Unique identifier for the resource.' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
					'required'    => true,
				),
				'count'       => array(
					'description' => __( 'Number of published posts for the resource.' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'description' => array(
					'description' => __( 'HTML description of the resource.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'link'        => array(
					'description' => __( 'URL to the resource.' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
				),
				'name'        => array(
					'description' => __( 'HTML title for the resource.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
				),
				'slug'        => array(
					'description' => __( 'An alphanumeric identifier for the resource unique to its type.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
				),
				'taxonomy'    => array(
					'description' => __( 'Type attribution for the resource.' ),
					'type'        => 'string',
					'enum'        => array_keys( get_taxonomies() ),
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
				),
			),
		);
		$taxonomy = get_taxonomy( $this->coauthor_taxonomy );
		if ( ! empty( $taxonomy->hierarchical ) ) {
			$schema['properties']['parent'] = array(
				'description' => __( 'The id for the parent of the resource.' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
			);
		}

		return $this->add_additional_fields_schema( $schema );
	}
}
