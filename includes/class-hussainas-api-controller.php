<?php
/**
 * API Controller for the Hussainas REST API Module.
 *
 * Registers custom REST API endpoints for the 'Book' CPT.
 *
 * @package   Hussainas_REST_API_Module
 * @version   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Hussainas_API_Controller
 *
 * Implements Singleton pattern and handles REST API route registration.
 */
final class Hussainas_API_Controller {

	/**
	 * The single instance of the class.
	 *
	 * @var Hussainas_API_Controller|null
	 */
	private static $instance = null;

	/**
	 * API Namespace.
	 *
	 * @var string
	 */
	private $namespace = 'hussainas/v1';

	/**
	 * REST route base.
	 *
	 * @var string
	 */
	private $rest_base = 'books';

	/**
	 * Post type slug.
	 *
	 * @var string
	 */
	private $post_type = 'hussainas_book';

	/**
	 * Gets the single instance of the class.
	 *
	 * @return Hussainas_API_Controller
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor. Hooks into 'rest_api_init'.
	 */
	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers the custom REST API routes.
	 */
	public function register_routes() {

		// Route for getting all items (GET) and creating a new item (POST)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				// GET /hussainas/v1/books
				array(
					'methods'             => WP_REST_Server::READABLE, // 'GET'
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permission_check' ),
					'args'                => $this->get_collection_params(),
				),
				// POST /hussainas/v1/books
				array(
					'methods'             => WP_REST_Server::CREATABLE, // 'POST'
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permission_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		// Route for a single item (GET, PUT, DELETE)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\\d+)', // (?P<id>\\d+) makes 'id' a required integer
			array(
				'args' => array(
					'id' => array(
						'description' => __( 'Unique identifier for the book.', 'hussainas' ),
						'type'        => 'integer',
					),
				),
				// GET /hussainas/v1/books/<id>
				array(
					'methods'             => WP_REST_Server::READABLE, // 'GET'
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permission_check' ),
				),
				// PUT /hussainas/v1/books/<id>
				array(
					'methods'             => WP_REST_Server::EDITABLE, // 'PUT', 'PATCH'
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permission_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				// DELETE /hussainas/v1/books/<id>
				array(
					'methods'             => WP_REST_Server::DELETABLE, // 'DELETE'
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permission_check' ),
					'args'                => array(
						'force' => array(
							'type'        => 'boolean',
							'default'     => false,
							'description' => __( 'Whether to bypass trash and force deletion.', 'hussainas' ),
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	// --- PERMISSION CALLBACKS ---

	/**
	 * Permission check for getting all items.
	 *
	 * @param WP_REST_Request $request
	 * @return true|WP_Error
	 */
	public function get_items_permission_check( $request ) {
		// For this example, we allow anyone to read the book list.
		// For restricted access: return current_user_can('read');
		return true;
	}

	/**
	 * Permission check for creating an item.
	 *
	 * @param WP_REST_Request $request
	 * @return true|WP_Error
	 */
	public function create_item_permission_check( $request ) {
		// Only users who can publish posts (e.g., Editor, Administrator) can create books.
		return current_user_can( 'publish_posts' );
	}

	/**
	 * Permission check for getting a single item.
	 *
	 * @param WP_REST_Request $request
	 * @return true|WP_Error
	 */
	public function get_item_permission_check( $request ) {
		// Check if post exists and is the correct post type
		$post = get_post( $request['id'] );
		if ( ! $post || $post->post_type !== $this->post_type ) {
			return new WP_Error( 'rest_post_not_found', __( 'Book not found.', 'hussainas' ), array( 'status' => 404 ) );
		}
		// Anyone can read a single book.
		return true;
	}

	/**
	 * Permission check for updating an item.
	 *
	 * @param WP_REST_Request $request
	 * @return true|WP_Error
	 */
	public function update_item_permission_check( $request ) {
		// Check if post exists
		$post = get_post( $request['id'] );
		if ( ! $post || $post->post_type !== $this->post_type ) {
			return new WP_Error( 'rest_post_not_found', __( 'Book not found.', 'hussainas' ), array( 'status' => 404 ) );
		}
		// Only users who can edit this specific post can update it.
		// 'edit_post' capability check is dynamic based on post ID.
		return current_user_can( 'edit_post', $post->ID );
	}

	/**
	 * Permission check for deleting an item.
	 *
	 * @param WP_REST_Request $request
	 * @return true|WP_Error
	 */
	public function delete_item_permission_check( $request ) {
		// Check if post exists
		$post = get_post( $request['id'] );
		if ( ! $post || $post->post_type !== $this->post_type ) {
			return new WP_Error( 'rest_post_not_found', __( 'Book not found.', 'hussainas' ), array( 'status' => 404 ) );
		}
		// Only users who can delete this specific post can delete it.
		return current_user_can( 'delete_post', $post->ID );
	}

	// --- CRUD CALLBACKS ---

	/**
	 * Get all items (Books). (READ)
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$args = array(
			'post_type'      => $this->post_type,
			'posts_per_page' => $request['per_page'],
			'paged'          => $request['page'],
			'orderby'        => $request['orderby'],
			'order'          => $request['order'],
		);

		// Handle search
		if ( ! empty( $request['search'] ) ) {
			$args['s'] = $request['search'];
		}

		$query = new WP_Query( $args );

		$posts = $query->get_posts();
		$data  = array();

		foreach ( $posts as $post ) {
			$item   = $this->prepare_item_for_response( $post, $request );
			$data[] = $this->prepare_response_for_collection( $item );
		}

		$response = rest_ensure_response( $data );

		// Set pagination headers
		$response->header( 'X-WP-Total', (int) $query->found_posts );
		$response->header( 'X-WP-TotalPages', (int) $query->max_num_pages );

		return $response;
	}

	/**
	 * Get a single item (Book). (READ)
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$id   = (int) $request['id'];
		$post = get_post( $id );

		// Permission check already ran, but we double-check existence
		if ( ! $post || $post->post_type !== $this->post_type ) {
			return new WP_Error( 'rest_post_not_found', __( 'Book not found.', 'hussainas' ), array( 'status' => 404 ) );
		}

		$data     = $this->prepare_item_for_response( $post, $request );
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Create a new item (Book). (CREATE)
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$params = $request->get_params();

		if ( empty( $params['title'] ) ) {
			return new WP_Error( 'rest_missing_title', __( 'Title is required.', 'hussainas' ), array( 'status' => 400 ) );
		}

		$post_args = array(
			'post_type'   => $this->post_type,
			'post_title'  => sanitize_text_field( $params['title'] ),
			'post_content'=> wp_kses_post( $params['content'] ),
			'post_status' => 'publish', // Default to published
		);

		$post_id = wp_insert_post( $post_args, true );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Get the created post object
		$post     = get_post( $post_id );
		$data     = $this->prepare_item_for_response( $post, $request );
		$response = rest_ensure_response( $data );

		// Set 201 Created status
		$response->set_status( 201 );
		// Add location header
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $post_id ) ) );

		return $response;
	}

	/**
	 * Update an existing item (Book). (UPDATE)
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$id     = (int) $request['id'];
		$post   = get_post( $id );
		$params = $request->get_params();

		// Permission check already ran, but we check existence
		if ( ! $post || $post->post_type !== $this->post_type ) {
			return new WP_Error( 'rest_post_not_found', __( 'Book not found.', 'hussainas' ), array( 'status' => 404 ) );
		}

		$update_args = array(
			'ID' => $id,
		);

		// Check which fields are present in the request and update them
		if ( isset( $params['title'] ) ) {
			$update_args['post_title'] = sanitize_text_field( $params['title'] );
		}
		if ( isset( $params['content'] ) ) {
			$update_args['post_content'] = wp_kses_post( $params['content'] );
		}
		if ( isset( $params['status'] ) ) {
			$update_args['post_status'] = sanitize_key( $params['status'] );
		}

		$post_id = wp_update_post( $update_args, true );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Get the updated post object
		$post     = get_post( $post_id );
		$data     = $this->prepare_item_for_response( $post, $request );
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Delete an existing item (Book). (DELETE)
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$id    = (int) $request['id'];
		$force = (bool) $request['force']; // Whether to force delete or trash

		$post = get_post( $id );

		// Permission check already ran, but we check existence
		if ( ! $post || $post->post_type !== $this->post_type ) {
			return new WP_Error( 'rest_post_not_found', __( 'Book not found.', 'hussainas' ), array( 'status' => 404 ) );
		}

		// Store post data before deleting, for the response
		$previous_data = $this->prepare_item_for_response( $post, $request );

		$deleted = wp_delete_post( $id, $force );

		if ( ! $deleted ) {
			return new WP_Error( 'rest_delete_failed', __( 'Failed to delete the book.', 'hussainas' ), array( 'status' => 500 ) );
		}

		$response = new WP_REST_Response();
		$response->set_data(
			array(
				'deleted'  => true,
				'previous' => $previous_data->get_data(),
			)
		);

		return $response;
	}


	// --- HELPER FUNCTIONS ---

	/**
	 * Prepares a single post object for response.
	 * This is where you format the data.
	 *
	 * @param WP_Post         $post    Post object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $post, $request ) {
		$data = array(
			'id'      => $post->ID,
			'date'    => $post->post_date_gmt,
			'slug'    => $post->post_name,
			'status'  => $post->post_status,
			'title'   => get_the_title( $post ),
			'content' => apply_filters( 'the_content', $post->post_content ), // Apply content filters
			'author'  => (int) $post->post_author,
		);

		// Create a new response object
		$response = rest_ensure_response( $data );

		// Add links (HATEOAS)
		$response->add_links( $this->prepare_links( $post ) );

		return $response;
	}

	/**
	 * Prepares links for the item.
	 *
	 * @param WP_Post $post Post object.
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $post ) {
		$base = sprintf( '%s/%s', $this->namespace, $this->rest_base );

		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $post->ID ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
			'author'     => array(
				'href' => rest_url( 'wp/v2/users/' . $post->post_author ),
			),
		);

		return $links;
	}

	/**
	 * Wraps the response in a 'data' object.
	 * (Helper for collection responses).
	 *
	 * @param WP_REST_Response $response
	 * @return array
	 */
	public function prepare_response_for_collection( $response ) {
		// This ensures the response structure matches WP core
		return $response->data;
	}

	/**
	 * Retrieves the query parameters for collections.
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		return array(
			'page'     => array(
				'description'       => 'Current page of the collection.',
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
				'minimum'           => 1,
			),
			'per_page' => array(
				'description'       => 'Maximum number of items to be returned in response.',
				'type'              => 'integer',
				'default'           => 10,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
				'minimum'           => 1,
				'maximum'           => 100,
			),
			'search'   => array(
				'description'       => 'Limit results to those matching a search query.',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'orderby'  => array(
				'description'       => 'Sort collection by post attribute.',
				'type'              => 'string',
				'default'           => 'date',
				'enum'              => array( 'date', 'id', 'title', 'slug' ),
				'validate_callback' => 'rest_validate_request_arg',
			),
			'order'    => array(
				'description'       => 'Order sort attribute ascending or descending.',
				'type'              => 'string',
				'default'           => 'desc',
				'enum'              => array( 'asc', 'desc' ),
				'validate_callback' => 'rest_validate_request_arg',
			),
		);
	}

	/**
	 * Retrieves the item schema.
	 *
	 * @return array Item schema.
	 */
	public function get_public_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->post_type,
			'type'       => 'object',
			'properties' => array(
				'id'      => array(
					'description' => 'Unique identifier for the book.',
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'title'   => array(
					'description' => 'The title of the book.',
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true, // Required for creation
				),
				'content' => array(
					'description' => 'The content of the book.',
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'status'  => array(
					'description' => 'The status of the book (e.g., publish, draft).',
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				// ... other fields as needed
			),
		);
		return $schema;
	}

	/**
	 * Retrieves the args for an endpoint.
	 *
	 * @param string $method
	 * @return array
	 */
	public function get_endpoint_args_for_item_schema( $method = WP_REST_Server::CREATABLE ) {
		$schema = $this->get_public_item_schema();
		$params = $schema['properties'];

		// Remove readonly fields for create/edit
		if ( $method === WP_REST_Server::CREATABLE || $method === WP_REST_Server::EDITABLE ) {
			unset( $params['id'] );
		}

		// Set 'required' fields for CREATABLE method
		if ( $method === WP_REST_Server::CREATABLE ) {
			foreach ( $params as $key => $props ) {
				if ( ! empty( $props['required'] ) ) {
					$params[ $key ]['required'] = true;
				}
			}
		}

		return $params;
	}
}
