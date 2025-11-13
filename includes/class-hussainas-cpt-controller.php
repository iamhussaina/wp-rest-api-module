<?php
/**
 * CPT Controller for the Hussainas REST API Module.
 *
 * Registers the 'Book' Custom Post Type.
 *
 * @package   Hussainas_REST_API_Module
 * @version   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Hussainas_CPT_Controller
 *
 * Implements Singleton pattern to register our CPT.
 */
final class Hussainas_CPT_Controller {

	/**
	 * The single instance of the class.
	 *
	 * @var Hussainas_CPT_Controller|null
	 */
	private static $instance = null;

	/**
	 * The post type slug.
	 *
	 * @var string
	 */
	private $post_type = 'hussainas_book';

	/**
	 * Gets the single instance of the class.
	 *
	 * @return Hussainas_CPT_Controller
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor to prevent direct instantiation.
	 * Hooks into WordPress 'init'.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ), 0 );
	}

	/**
	 * Registers the 'hussainas_book' custom post type.
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Books', 'Post Type General Name', 'hussainas' ),
			'singular_name'         => _x( 'Book', 'Post Type Singular Name', 'hussainas' ),
			'menu_name'             => __( 'Books', 'hussainas' ),
			'name_admin_bar'        => __( 'Book', 'hussainas' ),
			'archives'              => __( 'Book Archives', 'hussainas' ),
			'attributes'            => __( 'Book Attributes', 'hussainas' ),
			'parent_item_colon'     => __( 'Parent Book:', 'hussainas' ),
			'all_items'             => __( 'All Books', 'hussainas' ),
			'add_new_item'          => __( 'Add New Book', 'hussainas' ),
			'add_new'               => __( 'Add New', 'hussainas' ),
			'new_item'              => __( 'New Book', 'hussainas' ),
			'edit_item'             => __( 'Edit Book', 'hussainas' ),
			'update_item'           => __( 'Update Book', 'hussainas' ),
			'view_item'             => __( 'View Book', 'hussainas' ),
			'view_items'            => __( 'View Books', 'hussainas' ),
			'search_items'          => __( 'Search Book', 'hussainas' ),
			'not_found'             => __( 'Not found', 'hussainas' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'hussainas' ),
			'featured_image'        => __( 'Featured Image', 'hussainas' ),
			'set_featured_image'    => __( 'Set featured image', 'hussainas' ),
			'remove_featured_image' => __( 'Remove featured image', 'hussainas' ),
			'use_featured_image'    => __( 'Use as featured image', 'hussainas' ),
			'insert_into_item'      => __( 'Insert into book', 'hussainas' ),
			'uploaded_to_this_item' => __( 'Uploaded to this book', 'hussainas' ),
			'items_list'            => __( 'Books list', 'hussainas' ),
			'items_list_navigation' => __( 'Books list navigation', 'hussainas' ),
			'filter_items_list'     => __( 'Filter books list', 'hussainas' ),
		);

		$args = array(
			'label'                 => __( 'Book', 'hussainas' ),
			'description'           => __( 'Custom Post Type for Books', 'hussainas' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'author', 'thumbnail', 'revisions' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'post',
			'show_in_rest'          => true, // IMPORTANT: Allows Gutenberg editor
			'rest_base'             => 'books', // IMPORTANT: This sets the native WP API route to /wp/v2/books
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		);

		register_post_type( $this->post_type, $args );
	}
}
