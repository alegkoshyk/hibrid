<?php

class Hybrid_Auto_Calc_Frontend {
    
    public function __construct() {
        add_shortcode( 'hybrid-auto-calculator', array( $this, 'render_calculator' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }
    
    public function render_calculator() {
        wp_enqueue_style( 'hybrid-auto-calc-frontend' );
        wp_enqueue_script( 'hybrid-auto-calc-frontend' );
        
        $countries = get_option( 'hybrid_auto_calc_countries', Hybrid_Auto_Calc_Admin::get_default_countries() );
        $nbu_currencies = get_option( 'hybrid_auto_calc_nbu_currencies', array() );
        $currencies = get_transient( 'hybrid_auto_calc_currencies' );
        
        // Filter enabled countries
        $enabled_countries = array_filter( $countries, function( $c ) {
            return $c['enabled'] ?? false;
        } );
        
        // Filter enabled currencies
        $enabled_currencies = array_filter( $nbu_currencies, function( $c ) {
            return $c['enabled'] ?? false;
        } );
        
        // Always include UAH with rate 1
        $uah = array( 'code' => 'UAH', 'name_ua' => 'Українська гривня', 'value' => 1, 'enabled' => true );
        $has_uah = false;
        if ( is_array( $enabled_currencies ) ) {
            foreach ( $enabled_currencies as $c ) {
                if ( isset( $c['code'] ) && $c['code'] === 'UAH' ) { $has_uah = true; break; }
            }
        }
        if ( ! $has_uah ) {
            // Prepend UAH
            $enabled_currencies = is_array( $enabled_currencies ) ? array_merge( array( $uah ), $enabled_currencies ) : array( $uah );
        }
        
        // If no currencies are enabled, get first 5 from NBU
        if ( empty( $enabled_currencies ) && is_array( $currencies ) ) {
            $enabled_currencies = array_slice( $currencies, 0, 5 );
        }
        
        ob_start();
        include HYBRID_AUTO_CALC_PLUGIN_DIR . 'includes/calculator-template.php';
        return ob_get_clean();
    }
    
    public function enqueue_scripts() {
        wp_register_style(
            'hybrid-auto-calc-frontend',
            HYBRID_AUTO_CALC_PLUGIN_URL . 'assets/css/calculator.css',
            array(),
            HYBRID_AUTO_CALC_VERSION
        );
        
        wp_register_script(
            'hybrid-auto-calc-frontend',
            HYBRID_AUTO_CALC_PLUGIN_URL . 'assets/js/calculator.js',
            array( 'jquery' ),
            HYBRID_AUTO_CALC_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script(
            'hybrid-auto-calc-frontend',
            'hybridAutoCalc',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'hybrid_auto_calc_nonce' ),
                'isLogging' => (bool) get_option( 'hybrid_auto_calc_settings', array() )['enable_logging'] ?? true,
            )
        );
    }
}
