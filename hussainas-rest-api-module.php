<?php
/**
 * Main loader file for the Hussainas CPT REST API Module.
 *
 * This file includes necessary class files and initializes the module.
 *
 * @package   Hussainas_REST_API_Module
 * @version     1.0.0
 * @author      Hussain Ahmed Shrabon
 * @license     GPL v2
 * @link        https://github.com/iamhussaina
 * @textdomain  hussainas
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define module constants
define( 'HUSSAINAS_API_MODULE_PATH', plugin_dir_path( __FILE__ ) );
define( 'HUSSAINAS_API_MODULE_URL', plugin_dir_url( __FILE__ ) ); // Not used here, but good practice

// Include required class files
require_once HUSSAINAS_API_MODULE_PATH . 'includes/class-hussainas-cpt-controller.php';
require_once HUSSAINAS_API_MODULE_PATH . 'includes/class-hussainas-api-controller.php';

/**
 * Initializes the API module by loading the controllers.
 * Hooks into 'after_setup_theme' to ensure theme functions are available.
 */
function hussainas_initialize_api_module() {
	Hussainas_CPT_Controller::get_instance();
	Hussainas_API_Controller::get_instance();
}
add_action( 'after_setup_theme', 'hussainas_initialize_api_module' );
