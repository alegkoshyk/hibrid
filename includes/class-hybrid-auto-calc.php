<?php

class Hybrid_Auto_Calc {
    
    private static $instance = null;
    
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        // Load text domain
        load_plugin_textdomain( 'hybrid-auto-calc', false, dirname( HYBRID_AUTO_CALC_PLUGIN_BASENAME ) . '/languages' );
        
        // Include classes
        $this->includes();
        
        // Initialize admin
        if ( is_admin() ) {
            new Hybrid_Auto_Calc_Admin();
        }
        
        // Register hooks
        $this->register_hooks();
    }
    
    private function includes() {
        require_once HYBRID_AUTO_CALC_PLUGIN_DIR . 'includes/class-hybrid-auto-calc-admin.php';
        require_once HYBRID_AUTO_CALC_PLUGIN_DIR . 'includes/class-hybrid-auto-calc-api.php';
        require_once HYBRID_AUTO_CALC_PLUGIN_DIR . 'includes/class-hybrid-auto-calc-frontend.php';
    }
    
    private function register_hooks() {
        // Frontend
        new Hybrid_Auto_Calc_Frontend();
        
        // AJAX handlers
        add_action( 'wp_ajax_hybrid_auto_calc_get_currency', array( $this, 'ajax_get_currency' ) );
        add_action( 'wp_ajax_nopriv_hybrid_auto_calc_get_currency', array( $this, 'ajax_get_currency' ) );
        
        add_action( 'wp_ajax_hybrid_auto_calc_calculate', array( $this, 'ajax_calculate' ) );
        add_action( 'wp_ajax_nopriv_hybrid_auto_calc_calculate', array( $this, 'ajax_calculate' ) );
        
        // Sync currencies daily
        add_action( 'wp_scheduled_event', array( $this, 'sync_nbu_currencies' ) );
        
        // Schedule event on activation
        if ( ! wp_next_scheduled( 'hybrid_auto_calc_sync_currencies' ) ) {
            wp_schedule_event( time(), 'daily', 'hybrid_auto_calc_sync_currencies' );
        }
        add_action( 'hybrid_auto_calc_sync_currencies', array( $this, 'sync_nbu_currencies' ) );
    }
    
    public static function activate() {
        // Create default options
        if ( ! get_option( 'hybrid_auto_calc_settings' ) ) {
            update_option( 'hybrid_auto_calc_settings', self::get_default_settings() );
        }
        
        // Sync currencies
        self::instance()->sync_nbu_currencies();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public static function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook( 'hybrid_auto_calc_sync_currencies' );
    }
    
    public static function get_default_settings() {
        return array(
            'api_key' => 'yPPhOviJmlbOymdAwomEaw1FjApmTb20XkHIrfjk',
            'api_base' => 'https://www.mdoffice.com.ua/api',
            'enable_logging' => true,
        );
    }
    
    public function ajax_get_currency() {
        check_ajax_referer( 'hybrid_auto_calc_nonce' );
        
        $currency_code = isset( $_POST['currency_code'] ) ? sanitize_text_field( $_POST['currency_code'] ) : 840;
        
        $currencies = get_transient( 'hybrid_auto_calc_currencies' );
        
        if ( ! $currencies ) {
            $this->sync_nbu_currencies();
            $currencies = get_transient( 'hybrid_auto_calc_currencies' );
        }
        
        $currency = null;
        if ( is_array( $currencies ) ) {
            foreach ( $currencies as $curr ) {
                if ( isset( $curr['code'] ) && $curr['code'] == $currency_code ) {
                    $currency = $curr;
                    break;
                }
            }
        }
        
        wp_send_json_success( $currency );
    }
    
    public function ajax_calculate() {
        check_ajax_referer( 'hybrid_auto_calc_nonce' );
        
        // Prepare request parameters
        $params = array(
            'motor' => sanitize_text_field( $_POST['motor'] ?? '' ),
            'age' => sanitize_text_field( $_POST['age'] ?? '' ),
            'capacity' => intval( $_POST['capacity'] ?? 0 ),
            'currency' => sanitize_text_field( $_POST['currency'] ?? '' ),
            'cost' => floatval( $_POST['cost'] ?? 0 ),
            'user' => intval( $_POST['user'] ?? 0 ),
            'year' => intval( $_POST['year'] ?? date( 'Y' ) ),
        );
        
        // Add optional parameters
        if ( ! empty( $_POST['country'] ) ) {
            $params['country'] = sanitize_text_field( $_POST['country'] );
        }
        
        if ( ! empty( $_POST['feature'] ) ) {
            $params['feature'] = sanitize_text_field( $_POST['feature'] );
        }
        
        // Call API
        $api = new Hybrid_Auto_Calc_API();
        $result = $api->calculate( $params );
        
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }
        
        wp_send_json_success( $result );
    }
    
    public function sync_nbu_currencies() {
        $api = new Hybrid_Auto_Calc_API();
        $currencies = $api->get_nbu_currencies();
        
        if ( ! is_wp_error( $currencies ) ) {
            set_transient( 'hybrid_auto_calc_currencies', $currencies, 24 * HOUR_IN_SECONDS );
            
            // Also store custom settings for currencies
            $nbu_currencies = get_option( 'hybrid_auto_calc_nbu_currencies', array() );
            if ( is_array( $currencies ) ) {
                foreach ( $currencies as $currency ) {
                    if ( isset( $currency['code'] ) ) {
                        if ( ! isset( $nbu_currencies[ $currency['code'] ] ) ) {
                            $nbu_currencies[ $currency['code'] ] = array(
                                'code' => $currency['code'],
                                'name_ua' => $currency['name_ua'] ?? '',
                                'rate' => $currency['value'] ?? 0,
                                'enabled' => false,
                            );
                        }
                    }
                }
                update_option( 'hybrid_auto_calc_nbu_currencies', $nbu_currencies );
            }
        }
    }
}
