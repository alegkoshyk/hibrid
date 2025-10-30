<?php

class Hybrid_Auto_Calc_API {
    
    private $nbu_api = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchangenew?json';
    
    /**
     * Get currencies from NBU API
     */
    public function get_nbu_currencies() {
        $response = wp_remote_get( $this->nbu_api, array(
            'timeout' => 10,
            'sslverify' => true,
        ) );
        
        if ( is_wp_error( $response ) ) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        
        if ( ! is_array( $data ) ) {
            return new WP_Error( 'invalid_response', __( 'Invalid NBU API response', 'hybrid-auto-calc' ) );
        }
        
        // Format currencies
        $currencies = array();
        $exclude_codes = array( 'XAU', 'XAG', 'XPT', 'XPD' ); // метали: золото, срібло, платина, паладій
        foreach ( $data as $item ) {
            if ( isset( $item['cc'] ) && isset( $item['rate'] ) ) {
                $code = strtoupper( $item['cc'] );
                if ( in_array( $code, $exclude_codes, true ) ) {
                    continue; // пропускаємо метали
                }
                $currencies[] = array(
                    'code' => $code,
                    'name_ua' => $item['txt'] ?? '',
                    'value' => $item['rate'],
                );
            }
        }
        
        return $currencies;
    }
    
    /**
     * Get specific currency rate
     */
    public function get_currency( $code ) {
        // Normalize input code to uppercase to match stored NBU codes
        $code = strtoupper( trim( (string) $code ) );

        // Handle UAH locally
        if ( $code === 'UAH' ) {
            return array(
                'code' => 'UAH',
                'name_ua' => 'Українська гривня',
                'value' => 1,
            );
        }

        $currencies = get_transient( 'hybrid_auto_calc_currencies' );
        
        if ( ! is_array( $currencies ) ) {
            $currencies = $this->get_nbu_currencies();
            if ( is_wp_error( $currencies ) ) {
                return $currencies;
            }
            set_transient( 'hybrid_auto_calc_currencies', $currencies, 24 * HOUR_IN_SECONDS );
        }
        
        foreach ( $currencies as $currency ) {
            if ( isset( $currency['code'] ) && strtoupper( $currency['code'] ) === $code ) {
                return $currency;
            }
        }
        
        return new WP_Error( 'currency_not_found', __( 'Currency not found', 'hybrid-auto-calc' ) );
    }
    
    /**
     * Calculate customs duties based on Ukrainian legislation
     * 
     * Формули розрахунку:
     * 1. Мито = Вартість в грн × Ставка мита (залежить від об'єму і типу)
     * 2. Акціз = Акцизна база × курс валюти
     * 3. ПДВ = (Вартість в грн + Мито) × 20%
     * 4. Всього = Мито + Акціз + ПДВ
     */
    public function calculate( $params ) {
        // Validate required parameters (do not treat '0' as missing)
        if ( ! isset( $params['motor'] ) || ! isset( $params['age'] ) ||
             ! isset( $params['capacity'] ) || ! isset( $params['cost'] ) ||
             ! isset( $params['currency'] ) ) {
            return new WP_Error( 'missing_params', __( 'Missing required parameters', 'hybrid-auto-calc' ) );
        }
        // Numeric validations
        $params['capacity'] = intval( $params['capacity'] );
        $params['cost'] = floatval( $params['cost'] );
        if ( $params['capacity'] <= 0 ) {
            return new WP_Error( 'invalid_capacity', __( 'Engine capacity must be greater than zero', 'hybrid-auto-calc' ) );
        }
        if ( $params['cost'] <= 0 ) {
            return new WP_Error( 'invalid_cost', __( 'Cost must be greater than zero', 'hybrid-auto-calc' ) );
        }
        if ( $params['currency'] === '' ) {
            return new WP_Error( 'invalid_currency', __( 'Currency is required', 'hybrid-auto-calc' ) );
        }
        
        // Get settings
        $settings = get_option( 'hybrid_auto_calc_settings', Hybrid_Auto_Calc::get_default_settings() );
        $tariffs = get_option( 'hybrid_auto_calc_tariffs', self::get_default_tariffs() );
        
        // Convert cost to UAH
        $currency_code = $params['currency'];
        $cost = floatval( $params['cost'] );
        
        $currency_info = $this->get_currency( $currency_code );
        if ( is_wp_error( $currency_info ) ) {
            return $currency_info;
        }
        
        $exchange_rate = floatval( $currency_info['value'] );
        $cost_uah = $cost * $exchange_rate;
        
        // Get tariff rates
        $capacity = intval( $params['capacity'] );
        $motor_type = $params['motor']; // 2 = петролий, 4 = дизель
        $age = intval( $params['age'] );
        $year = intval( $params['year'] ?? date( 'Y' ) );
        
        // ========== МИТО ==========
        $duty_rate = $this->get_duty_rate( $capacity, $motor_type, $age, $year, $tariffs );
        $duty = $cost_uah * $duty_rate / 100;
        
        // ========== АКЦІЗ ==========
        $excise_base = $this->get_excise_base( $capacity, $motor_type, $age, $params, $tariffs );
        // Акціз часто подається в EUR, але може залежати від налаштувань
        $excise_currency_rate = ( $currency_code === '978' ) ? 1 : $exchange_rate;
        $excise = $excise_base * $excise_currency_rate;
        
        // ========== ПДВ ==========
        $vat_base = $cost_uah + $duty + $excise;
        $vat = $vat_base * 0.20; // 20% VAT

        // ========== Пенсійний фонд ==========
        $pension_rate = floatval( $tariffs['pension_fund_rate'] ?? 5 );
        $pension_sum = $cost_uah * ( $pension_rate / 100 );
        
        // ========== ВСЬОГО ==========
        $total_uah = $duty + $excise + $vat + $pension_sum;
        $total_original = $total_uah / $exchange_rate;
        
        // Prepare response
        $response = array(
            'cost_uah' => $cost_uah,
            'exchange_rate' => $exchange_rate,
            'payments_ua_sum' => $total_uah,
            'payments_sum' => $total_original,
            'payments' => array(
                'duty' => array(
                    'name_ua' => 'Вивіз (імпортне) мито',
                    'rate' => $duty_rate,
                    'base' => $cost_uah,
                    'sum_ua' => $duty,
                ),
                'excise' => array(
                    'name_ua' => 'Акцизний збір',
                    'base' => $excise_base,
                    'unit' => $currency_code,
                    'sum_ua' => $excise,
                ),
                'vat' => array(
                    'name_ua' => 'Податок на додану вартість (ПДВ)',
                    'rate' => 20,
                    'base' => $vat_base,
                    'sum_ua' => $vat,
                ),
                'pension_fund' => array(
                    'name_ua' => 'Плата до Пенсійного фонду',
                    'rate' => $pension_rate,
                    'base' => $cost_uah,
                    'sum_ua' => $pension_sum,
                ),
            ),
            'additional_fees' => array(),
            'calc_info' => array(
                'motor_type' => $motor_type === '2' ? 'гібрид (бензин)' : 'гібрид (дизель)',
                'age' => $age,
                'capacity' => $capacity,
                'year' => $year,
            ),
        );
        
        return $response;
    }
    
    /**
     * Get duty rate based on parameters
     */
    private function get_duty_rate( $capacity, $motor_type, $age, $year, $tariffs ) {
        $base_rate = floatval( $tariffs['duty_base_rate'] ?? 10 );
        
        // Коефіцієнт за об'ємом (гібриди можуть мати знижки)
        $volume_coeff = 1.0;
        if ( $capacity <= 2000 ) {
            $volume_coeff = $motor_type === '2' ? 0.8 : 0.85; // Знижка для гібридів
        } elseif ( $capacity <= 3000 ) {
            $volume_coeff = $motor_type === '2' ? 0.9 : 0.95;
        }
        
        // Коефіцієнт за віком
        $age_coeff = 1.0;
        if ( $age === 1 ) { // До 5 років
            $age_coeff = 1.5;
        } elseif ( $age === 2 ) { // Понад 5 років
            $age_coeff = 2.0;
        }
        
        return $base_rate * $volume_coeff * $age_coeff;
    }
    
    /**
     * Get excise base (акцизна база)
     * 
     * Акціз залежить від об'єму та типу двигуна
     */
    private function get_excise_base( $capacity, $motor_type, $age, $params, $tariffs ) {
        $excise_rates = $tariffs['excise_rates'] ?? array(
            'petrol_small' => 0,    // До 2000 см³ - 0
            'petrol_medium' => 150, // 2000-3000 см³
            'petrol_large' => 250,  // Понад 3000 см³
            'diesel_small' => 0,
            'diesel_medium' => 200,
            'diesel_large' => 300,
        );
        
        $is_petrol = $motor_type === '2';
        $is_plugin = isset( $params['feature'] ) && $params['feature'] === '2';
        
        // Plug-in гібриди можуть мати знижки
        $reduction = $is_plugin ? 0.5 : 1.0;
        
        if ( $is_petrol ) {
            if ( $capacity <= 2000 ) {
                return $excise_rates['petrol_small'] * $reduction;
            } elseif ( $capacity <= 3000 ) {
                return $excise_rates['petrol_medium'] * $reduction;
            } else {
                return $excise_rates['petrol_large'] * $reduction;
            }
        } else {
            if ( $capacity <= 2000 ) {
                return $excise_rates['diesel_small'] * $reduction;
            } elseif ( $capacity <= 3000 ) {
                return $excise_rates['diesel_medium'] * $reduction;
            } else {
                return $excise_rates['diesel_large'] * $reduction;
            }
        }
    }
    
    /**
     * Get default tariffs
     */
    public static function get_default_tariffs() {
        return array(
            'duty_base_rate' => 10, // Базова ставка мита %
            'vat_rate' => 20, // ПДВ %
            'pension_fund_rate' => 5, // Плата до Пенсійного фонду %
            'excise_rates' => array(
                'petrol_small' => 0,    // До 2000 см³
                'petrol_medium' => 150, // 2000-3000 см³ EUR
                'petrol_large' => 250,  // Понад 3000 см³ EUR
                'diesel_small' => 0,
                'diesel_medium' => 200,
                'diesel_large' => 300,
            ),
            'hybrid_reduction' => 0.2, // 20% знижка для гібридів
            'plugin_hybrid_reduction' => 0.5, // 50% знижка для plug-in гібридів
        );
    }
}
