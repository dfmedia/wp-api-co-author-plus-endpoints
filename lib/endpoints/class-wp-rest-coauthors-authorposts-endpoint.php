<?php
/**
 * Class Name: WP_REST_CoAuthors_AuthorPosts_Endpoint
 * Author: Michael Jacobsen
 * Author URI: https://mjacobsen4dfm.wordpress.com/
 * License: GPL2+
 *
 * CoAuthors_AuthorPosts endpoint class.
 */

if ( ! class_exists( 'WP_REST_CoAuthors_AuthorPosts_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/../controllers/class-wp-rest-coauthors-authorposts-controller.php';
}

class WP_REST_CoAuthors_AuthorPosts_Endpoint extends WP_REST_CoAuthors_AuthorPosts_Controller {
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
	protected $parent_post_type = null;

	/**
	 * Associated parent controller class object.
	 *
	 * @var WP_REST_Posts_Controller
	 */
	protected $parent_controller = null;

	/**
	 * Base path for parent endpoints.
	 *
	 * @var string
	 */
	protected $parent_base = null;

	/**
	 * WP_REST_CoAuthors_Initializer constructor.
	 *
	 * @param $parent_post_type
	 */
	public function __construct( $parent_post_type ) {
		$this->parent_type       = 'CoAuthors';
		$this->parent_post_type  = $parent_post_type;
		$this->parent_controller = new WP_REST_Posts_Controller( $this->parent_post_type );
		$obj                     = get_post_type_object( $this->parent_post_type );
		$this->parent_base       = ! empty( $obj->rest_base ) ? $obj->rest_base : $obj->name;
		$this->namespace         = 'co-authors/v1';
		$this->rest_base         = 'author-posts';
		parent::__construct();
	}
	
	/**
	 * Check if a given request has access to get authors for a post.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! empty( $request['parent_id'] ) ) {
			$parent = get_post( (int) $request['parent_id'] );

			if ( empty( $parent ) || empty( $parent->ID ) ) {
				return new WP_Error( 'rest_post_invalid_id', __( 'Invalid post id.' ), array( 'status' => 404 ) );
			}

			return true;
		}

		return true;
	}

	/**
	 * Check if a given request has access to create an author association for a post.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return boolean
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! empty( $request['parent_id'] ) ) {
			$parent = get_post( (int) $request['parent_id'] );

			if ( empty( $parent ) || empty( $parent->ID ) ) {
				return new WP_Error( 'rest_post_invalid_id', __( 'Invalid post id.' ), array( 'status' => 404 ) );
			}

			$post_type = get_post_type_object( $parent->post_type );
			if ( ! current_user_can( $post_type->cap->edit_post, $parent->ID ) ) {
				return new WP_Error( 'rest_forbidden', __( 'Sorry, you cannot create an author association for this post.' ), array( 'status' => rest_authorization_required_code() ) );
			}

			return true;
		}

		return new WP_Error( 'rest_forbidden', __( 'Creating an author is not supported.' ), array( 'status' => rest_authorization_required_code() ) );
	}

	/**
	 * Check if a given request has access to update the author association for a post.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return boolean
	 */
	public function update_item_permissions_check( $request ) {
		if ( ! empty( $request['parent_id'] ) ) {
			$parent = get_post( (int) $request['parent_id'] );

			if ( empty( $parent ) || empty( $parent->ID ) ) {
				return new WP_Error( 'rest_post_invalid_id', __( 'Invalid post id.' ), array( 'status' => 404 ) );
			}

			$post_type = get_post_type_object( $parent->post_type );
			if ( ! current_user_can( $post_type->cap->edit_post, $parent->ID ) ) {
				return new WP_Error( 'rest_forbidden', __( 'Sorry, you cannot update the author association for this post.' ), array( 'status' => rest_authorization_required_code() ) );
			}

			return true;
		}

		return new WP_Error( 'rest_forbidden', __( 'Updating an author is not supported.' ), array( 'status' => rest_authorization_required_code() ) );
	}

	/**
	 * Check if a given request has access to delete authors for a post.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 *
	 * @return boolean, always false: delete is not supported
	 */
	public function delete_item_permissions_check( $request ) {
		return new WP_Error( 'rest_forbidden', __( 'Deleting an author is not supported.' ), array( 'status' => rest_authorization_required_code() ) );
	}
}
