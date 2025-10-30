<?php
/**
 * Plugin Name: Hybrid Auto Customs Calculator
 * Plugin URI: https://github.com/yourusername/hybrid-auto-customs-calculator
 * Description: Калькулятор митних платежів для гібридних автомобілів з интеграцією курсів НБУ
 * Version: 2.0.0
 * Author: Your Name
 * Author URI: https://yoursite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hybrid-auto-calc
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'HYBRID_AUTO_CALC_VERSION', '2.0.0' );
define( 'HYBRID_AUTO_CALC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HYBRID_AUTO_CALC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'HYBRID_AUTO_CALC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Include main plugin class
require_once HYBRID_AUTO_CALC_PLUGIN_DIR . 'includes/class-hybrid-auto-calc.php';

// Initialize plugin
add_action( 'plugins_loaded', function() {
    do_action( 'hybrid_auto_calc_before_init' );
    Hybrid_Auto_Calc::instance();
    do_action( 'hybrid_auto_calc_after_init' );
} );

// Activation hook
register_activation_hook( __FILE__, array( 'Hybrid_Auto_Calc', 'activate' ) );

// Deactivation hook
register_deactivation_hook( __FILE__, array( 'Hybrid_Auto_Calc', 'deactivate' ) );
