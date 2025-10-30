<?php

class Hybrid_Auto_Calc_Admin {
    
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __( 'Калькулятор митних платежів (гібриди)', 'hybrid-auto-calc' ),
            __( 'Митний Калькулятор', 'hybrid-auto-calc' ),
            'manage_options',
            'hybrid-auto-calc',
            array( $this, 'render_main_page' ),
            'dashicons-calculator',
            25
        );
        
        add_submenu_page(
            'hybrid-auto-calc',
            __( 'Налаштування', 'hybrid-auto-calc' ),
            __( 'Налаштування', 'hybrid-auto-calc' ),
            'manage_options',
            'hybrid-auto-calc-settings',
            array( $this, 'render_settings_page' )
        );
        
        add_submenu_page(
            'hybrid-auto-calc',
            __( 'Тарифи', 'hybrid-auto-calc' ),
            __( 'Тарифи', 'hybrid-auto-calc' ),
            'manage_options',
            'hybrid-auto-calc-tariffs',
            array( $this, 'render_tariffs_page' )
        );
        
        add_submenu_page(
            'hybrid-auto-calc',
            __( 'Країни', 'hybrid-auto-calc' ),
            __( 'Країни', 'hybrid-auto-calc' ),
            'manage_options',
            'hybrid-auto-calc-countries',
            array( $this, 'render_countries_page' )
        );
        
        add_submenu_page(
            'hybrid-auto-calc',
            __( 'Валюти', 'hybrid-auto-calc' ),
            __( 'Валюти', 'hybrid-auto-calc' ),
            'manage_options',
            'hybrid-auto-calc-currencies',
            array( $this, 'render_currencies_page' )
        );
    }
    
    public function register_settings() {
        register_setting( 'hybrid_auto_calc_settings', 'hybrid_auto_calc_settings' );
        register_setting( 'hybrid_auto_calc_tariffs', 'hybrid_auto_calc_tariffs' );
        register_setting( 'hybrid_auto_calc_countries', 'hybrid_auto_calc_countries' );
        register_setting( 'hybrid_auto_calc_nbu_currencies', 'hybrid_auto_calc_nbu_currencies' );
    }
    
    public function render_main_page() {
        ?>
        <div class="wrap">
            <h1><?php _e( 'Калькулятор митних платежів для гібридних авто', 'hybrid-auto-calc' ); ?></h1>
            
            <div class="card" style="border-left: 4px solid #10b981;">
                <h2 style="color: #10b981;">✓ <?php _e( 'Local Calculations', 'hybrid-auto-calc' ); ?></h2>
                <p><?php _e( 'Цей плагін розраховує мито, ПДВ та акціз <strong>локально</strong> за формулами українського законодавства, без залежності від зовнішніх API.', 'hybrid-auto-calc' ); ?></p>
            </div>
            
            <div class="card">
                <h2><?php _e( 'Вітання', 'hybrid-auto-calc' ); ?></h2>
                <p><?php _e( 'Калькулятор митних платежів для гібридних автомобілів', 'hybrid-auto-calc' ); ?></p>
                
                <h3><?php _e( 'Швидкий старт', 'hybrid-auto-calc' ); ?></h3>
                <ol>
                    <li>✓ <?php _e( 'Налаштуйте ставки', 'hybrid-auto-calc' ); ?> - <a href="<?php echo admin_url( 'admin.php?page=hybrid-auto-calc-tariffs' ); ?>"><?php _e( 'Тарифи', 'hybrid-auto-calc' ); ?></a></li>
                    <li><?php _e( 'Додайте країни', 'hybrid-auto-calc' ); ?> - <a href="<?php echo admin_url( 'admin.php?page=hybrid-auto-calc-countries' ); ?>"><?php _e( 'Країни', 'hybrid-auto-calc' ); ?></a></li>
                    <li><?php _e( 'Виберіть валюти', 'hybrid-auto-calc' ); ?> - <a href="<?php echo admin_url( 'admin.php?page=hybrid-auto-calc-currencies' ); ?>"><?php _e( 'Валюти', 'hybrid-auto-calc' ); ?></a></li>
                    <li><?php _e( 'Додайте шорткод', 'hybrid-auto-calc' ); ?>: <code>[hybrid-auto-calculator]</code></li>
                </ol>
            </div>
            
            <div class="card">
                <h2><?php _e( 'Формула розрахунку', 'hybrid-auto-calc' ); ?></h2>
                <p><strong><?php _e( 'Мито', 'hybrid-auto-calc' ); ?></strong> = Вартість × Ставка мита (залежить від об'єму)</p>
                <p><strong><?php _e( 'Акціз', 'hybrid-auto-calc' ); ?></strong> = Акцизна база (залежить від об'єму) × Курс валюти</p>
                <p><strong><?php _e( 'ПДВ', 'hybrid-auto-calc' ); ?></strong> = (Вартість + Мито) × 20%</p>
                <p><strong><?php _e( 'Всього', 'hybrid-auto-calc' ); ?></strong> = Мито + Акціз + ПДВ</p>
            </div>
            
            <div class="card">
                <h2><?php _e( 'Статус', 'hybrid-auto-calc' ); ?></h2>
                <p>
                    <?php
                    $currencies = get_transient( 'hybrid_auto_calc_currencies' );
                    if ( $currencies ) {
                        printf( __( '✓ Валюти завантажено: %d записів', 'hybrid-auto-calc' ), count( $currencies ) );
                    } else {
                        _e( '⚠ Валюти не завантажено. Синхронізуйте в розділі Налаштування.', 'hybrid-auto-calc' );
                    }
                    ?>
                </p>
                
                <?php
                $tariffs = get_option( 'hybrid_auto_calc_tariffs' );
                if ( $tariffs ) {
                    echo '<p>✓ Тарифи налаштовано</p>';
                } else {
                    echo '<p>⚠ Тарифи не налаштовано. <a href="' . admin_url( 'admin.php?page=hybrid-auto-calc-tariffs' ) . '">Налаштувати</a></p>';
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    public function render_settings_page() {
        $settings = get_option( 'hybrid_auto_calc_settings', Hybrid_Auto_Calc::get_default_settings() );
        
        if ( isset( $_POST['sync_currencies'] ) && check_admin_referer( 'hybrid_auto_calc_sync_nonce' ) ) {
            Hybrid_Auto_Calc::instance()->sync_nbu_currencies();
            echo '<div class="notice notice-success"><p>' . __( 'Currencies synced successfully!', 'hybrid-auto-calc' ) . '</p></div>';
        }
        ?>
        <div class="wrap">
            <h1><?php _e( 'Налаштування', 'hybrid-auto-calc' ); ?></h1>
            
            <div class="hybrid-auto-calc-info">
                <strong><?php _e( '✓ Local Calculations', 'hybrid-auto-calc' ); ?></strong><br>
                <?php _e( 'Цей калькулятор розраховує усі платежі локально за формулами українського законодавства. Налаштування ставок знаходяться в розділі "Tariffs".', 'hybrid-auto-calc' ); ?>
            </div>
            
            <form method="POST" action="options.php">
                <?php settings_fields( 'hybrid_auto_calc_settings' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="enable_logging"><?php _e( 'Увімкнути логування', 'hybrid-auto-calc' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="enable_logging" name="hybrid_auto_calc_settings[enable_logging]" 
                                   value="1" <?php checked( $settings['enable_logging'] ?? true, 1 ); ?>>
                            <p class="description"><?php _e( 'Логувати розрахунки у консоль браузера для налагодження', 'hybrid-auto-calc' ); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <hr>
            
            <h2><?php _e( 'Синхронізація валют НБУ', 'hybrid-auto-calc' ); ?></h2>
            <p><?php _e( 'Оновити курси валют від Національного банку України', 'hybrid-auto-calc' ); ?></p>
            
            <form method="POST">
                <?php wp_nonce_field( 'hybrid_auto_calc_sync_nonce' ); ?>
                <button type="submit" name="sync_currencies" class="button button-primary">
                    <?php _e( 'Синхронізувати зараз', 'hybrid-auto-calc' ); ?>
                </button>
            </form>
            
            <hr>
            
            <h2><?php _e( 'Кроки налаштування', 'hybrid-auto-calc' ); ?></h2>
            <ol>
                <li>Перейдіть до розділу <strong><a href="<?php echo admin_url( 'admin.php?page=hybrid-auto-calc-tariffs' ); ?>">Тарифи</a></strong> для налаштування ставок мита, ПДВ та акцизу</li>
                <li>Налаштуйте <strong><a href="<?php echo admin_url( 'admin.php?page=hybrid-auto-calc-countries' ); ?>">Країни</a></strong> та коефіцієнти</li>
                <li>Виберіть валюти в <strong><a href="<?php echo admin_url( 'admin.php?page=hybrid-auto-calc-currencies' ); ?>">Валюти</a></strong></li>
                <li>Додайте шорткод <code>[hybrid-auto-calculator]</code> на сторінку</li>
            </ol>
        </div>
        <?php
    }
    
    public function render_tariffs_page() {
        $tariffs = get_option( 'hybrid_auto_calc_tariffs', Hybrid_Auto_Calc_API::get_default_tariffs() );
        
        if ( isset( $_POST['action'] ) && check_admin_referer( 'hybrid_auto_calc_tariffs_nonce' ) ) {
            if ( $_POST['action'] === 'save' ) {
                $new_tariffs = array(
                    'duty_base_rate' => floatval( $_POST['duty_base_rate'] ?? 10 ),
                    'vat_rate' => floatval( $_POST['vat_rate'] ?? 20 ),
                    'pension_fund_rate' => floatval( $_POST['pension_fund_rate'] ?? 5 ),
                    'excise_rates' => array(
                        'petrol_small' => floatval( $_POST['excise_petrol_small'] ?? 0 ),
                        'petrol_medium' => floatval( $_POST['excise_petrol_medium'] ?? 150 ),
                        'petrol_large' => floatval( $_POST['excise_petrol_large'] ?? 250 ),
                        'diesel_small' => floatval( $_POST['excise_diesel_small'] ?? 0 ),
                        'diesel_medium' => floatval( $_POST['excise_diesel_medium'] ?? 200 ),
                        'diesel_large' => floatval( $_POST['excise_diesel_large'] ?? 300 ),
                    ),
                    'hybrid_reduction' => floatval( $_POST['hybrid_reduction'] ?? 20 ) / 100,
                    'plugin_hybrid_reduction' => floatval( $_POST['plugin_hybrid_reduction'] ?? 50 ) / 100,
                );
                update_option( 'hybrid_auto_calc_tariffs', $new_tariffs );
                echo '<div class="notice notice-success"><p>' . __( 'Tariffs saved successfully!', 'hybrid-auto-calc' ) . '</p></div>';
                $tariffs = $new_tariffs;
            }
        }
        ?>
        <div class="wrap">
            <h1><?php _e( 'Налаштування тарифів', 'hybrid-auto-calc' ); ?></h1>
            
            <div class="hybrid-auto-calc-info">
                <strong><?php _e( 'Ставки згідно законодавства України', 'hybrid-auto-calc' ); ?></strong><br>
                <?php _e( 'Налаштуйте мито, ПДВ, акциз та інші ставки відповідно до митного законодавства України.', 'hybrid-auto-calc' ); ?>
            </div>
            
            <form method="POST">
                <?php wp_nonce_field( 'hybrid_auto_calc_tariffs_nonce' ); ?>
                
                <h2><?php _e( 'Основні податкові ставки', 'hybrid-auto-calc' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="duty_base_rate"><?php _e( 'Базова ставка мита (%)', 'hybrid-auto-calc' ); ?></label>
                        </th>
                        <td>
                            <input type="number" id="duty_base_rate" name="duty_base_rate" 
                                   value="<?php echo esc_attr( $tariffs['duty_base_rate'] ); ?>" 
                                   step="0.5" min="0" max="100" class="small-text">
                            <p class="description"><?php _e( 'Базова ставка мита на автомобілі, %', 'hybrid-auto-calc' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="vat_rate"><?php _e( 'Ставка ПДВ (%)', 'hybrid-auto-calc' ); ?></label>
                        </th>
                        <td>
                            <input type="number" id="vat_rate" name="vat_rate" 
                                   value="<?php echo esc_attr( $tariffs['vat_rate'] ); ?>" 
                                   step="0.5" min="0" max="100" class="small-text">
                            <p class="description"><?php _e( 'Податок на додану вартість, %', 'hybrid-auto-calc' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="pension_fund_rate"><?php _e( 'Плата до Пенсійного фонду (%)', 'hybrid-auto-calc' ); ?></label>
                        </th>
                        <td>
                            <input type="number" id="pension_fund_rate" name="pension_fund_rate" 
                                   value="<?php echo esc_attr( $tariffs['pension_fund_rate'] ); ?>" 
                                   step="0.5" min="0" max="100" class="small-text">
                            <p class="description"><?php _e( 'Плата до Пенсійного фонду, %', 'hybrid-auto-calc' ); ?></p>
                        </td>
                    </tr>
                </table>
                
                <h2><?php _e( 'Ставки акцизу (EUR)', 'hybrid-auto-calc' ); ?></h2>
                <p class="description"><?php _e( 'Акцизні збори залежать від об\'єму двигуна. Вказувати в EUR.', 'hybrid-auto-calc' ); ?></p>
                
                <table class="wp-list-table striped">
                    <thead>
                        <tr>
                            <th><?php _e( 'Тип двигуна та об\'єм', 'hybrid-auto-calc' ); ?></th>
                            <th><?php _e( 'Ставка акцизу (EUR)', 'hybrid-auto-calc' ); ?></th>
                            <th><?php _e( 'Опис', 'hybrid-auto-calc' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php _e( 'Бензин', 'hybrid-auto-calc' ); ?></strong></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>
                                <label><?php _e( 'Малий (до 2000 см³)', 'hybrid-auto-calc' ); ?></label>
                            </td>
                            <td>
                                <input type="number" name="excise_petrol_small" 
                                       value="<?php echo esc_attr( $tariffs['excise_rates']['petrol_small'] ); ?>" 
                                       step="10" min="0" class="small-text">
                            </td>
                            <td><?php _e( 'EUR', 'hybrid-auto-calc' ); ?></td>
                        </tr>
                        <tr>
                            <td>
                                <label><?php _e( 'Середній (2000-3000 см³)', 'hybrid-auto-calc' ); ?></label>
                            </td>
                            <td>
                                <input type="number" name="excise_petrol_medium" 
                                       value="<?php echo esc_attr( $tariffs['excise_rates']['petrol_medium'] ); ?>" 
                                       step="10" min="0" class="small-text">
                            </td>
                            <td><?php _e( 'EUR', 'hybrid-auto-calc' ); ?></td>
                        </tr>
                        <tr>
                            <td>
                                <label><?php _e( 'Великий (понад 3000 см³)', 'hybrid-auto-calc' ); ?></label>
                            </td>
                            <td>
                                <input type="number" name="excise_petrol_large" 
                                       value="<?php echo esc_attr( $tariffs['excise_rates']['petrol_large'] ); ?>" 
                                       step="10" min="0" class="small-text">
                            </td>
                            <td><?php _e( 'EUR', 'hybrid-auto-calc' ); ?></td>
                        </tr>
                        
                        <tr>
                            <td><strong><?php _e( 'Дизель', 'hybrid-auto-calc' ); ?></strong></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>
                                <label><?php _e( 'Малий (до 2000 см³)', 'hybrid-auto-calc' ); ?></label>
                            </td>
                            <td>
                                <input type="number" name="excise_diesel_small" 
                                       value="<?php echo esc_attr( $tariffs['excise_rates']['diesel_small'] ); ?>" 
                                       step="10" min="0" class="small-text">
                            </td>
                            <td><?php _e( 'EUR', 'hybrid-auto-calc' ); ?></td>
                        </tr>
                        <tr>
                            <td>
                                <label><?php _e( 'Середній (2000-3000 см³)', 'hybrid-auto-calc' ); ?></label>
                            </td>
                            <td>
                                <input type="number" name="excise_diesel_medium" 
                                       value="<?php echo esc_attr( $tariffs['excise_rates']['diesel_medium'] ); ?>" 
                                       step="10" min="0" class="small-text">
                            </td>
                            <td><?php _e( 'EUR', 'hybrid-auto-calc' ); ?></td>
                        </tr>
                        <tr>
                            <td>
                                <label><?php _e( 'Великий (понад 3000 см³)', 'hybrid-auto-calc' ); ?></label>
                            </td>
                            <td>
                                <input type="number" name="excise_diesel_large" 
                                       value="<?php echo esc_attr( $tariffs['excise_rates']['diesel_large'] ); ?>" 
                                       step="10" min="0" class="small-text">
                            </td>
                            <td><?php _e( 'EUR', 'hybrid-auto-calc' ); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <h2><?php _e( 'Hybrid Vehicle Reductions', 'hybrid-auto-calc' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="hybrid_reduction"><?php _e( 'Знижка для звичайних гібридів (%)', 'hybrid-auto-calc' ); ?></label>
                        </th>
                        <td>
                            <input type="number" id="hybrid_reduction" name="hybrid_reduction" 
                                   value="<?php echo esc_attr( $tariffs['hybrid_reduction'] * 100 ); ?>" 
                                   step="5" min="0" max="100" class="small-text">
                            <p class="description"><?php _e( 'Знижка для звичайних гібридів (від акцизу), %', 'hybrid-auto-calc' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="plugin_hybrid_reduction"><?php _e( 'Знижка для plug-in гібридів (%)', 'hybrid-auto-calc' ); ?></label>
                        </th>
                        <td>
                            <input type="number" id="plugin_hybrid_reduction" name="plugin_hybrid_reduction" 
                                   value="<?php echo esc_attr( $tariffs['plugin_hybrid_reduction'] * 100 ); ?>" 
                                   step="5" min="0" max="100" class="small-text">
                            <p class="description"><?php _e( 'Знижка для plug-in гібридів (від акцизу), %', 'hybrid-auto-calc' ); ?></p>
                        </td>
                    </tr>
                </table>
                
                <input type="hidden" name="action" value="save">
                <?php submit_button( __( 'Зберегти тарифи', 'hybrid-auto-calc' ), 'primary', 'submit', true ); ?>
            </form>
        </div>
        <?php
    }
    
    public function render_countries_page() {
        $countries = get_option( 'hybrid_auto_calc_countries', self::get_default_countries() );
        
        if ( isset( $_POST['action'] ) && check_admin_referer( 'hybrid_auto_calc_countries_nonce' ) ) {
            if ( $_POST['action'] === 'save' ) {
                $countries = array();
                if ( isset( $_POST['countries'] ) && is_array( $_POST['countries'] ) ) {
                    foreach ( $_POST['countries'] as $code => $data ) {
                        $countries[ $code ] = array(
                            'code' => sanitize_text_field( $code ),
                            'name' => sanitize_text_field( $data['name'] ?? '' ),
                            'coefficient' => floatval( $data['coefficient'] ?? 1 ),
                            'enabled' => isset( $data['enabled'] ) ? 1 : 0,
                        );
                    }
                }
                update_option( 'hybrid_auto_calc_countries', $countries );
                echo '<div class="notice notice-success"><p>' . __( 'Countries saved successfully!', 'hybrid-auto-calc' ) . '</p></div>';
            }
        }
        ?>
        <div class="wrap">
            <h1><?php _e( 'Керування країнами', 'hybrid-auto-calc' ); ?></h1>
            
            <form method="POST">
                <?php wp_nonce_field( 'hybrid_auto_calc_countries_nonce' ); ?>
                
                <table class="wp-list-table striped">
                    <thead>
                        <tr>
                            <th><?php _e( 'Увімкнено', 'hybrid-auto-calc' ); ?></th>
                            <th><?php _e( 'Код країни', 'hybrid-auto-calc' ); ?></th>
                            <th><?php _e( 'Назва країни', 'hybrid-auto-calc' ); ?></th>
                            <th><?php _e( 'Коефіцієнт', 'hybrid-auto-calc' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $countries as $code => $country ) : ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="countries[<?php echo esc_attr( $code ); ?>][enabled]" 
                                           value="1" <?php checked( $country['enabled'] ?? 0, 1 ); ?>>
                                </td>
                                <td>
                                    <input type="text" name="countries[<?php echo esc_attr( $code ); ?>][code]" 
                                           value="<?php echo esc_attr( $country['code'] ); ?>" class="small-text" readonly>
                                </td>
                                <td>
                                    <input type="text" name="countries[<?php echo esc_attr( $code ); ?>][name]" 
                                           value="<?php echo esc_attr( $country['name'] ); ?>" class="regular-text">
                                </td>
                                <td>
                                    <input type="number" name="countries[<?php echo esc_attr( $code ); ?>][coefficient]" 
                                           value="<?php echo esc_attr( $country['coefficient'] ); ?>" 
                                           step="0.1" min="0" class="small-text">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <input type="hidden" name="action" value="save">
                <?php submit_button( __( 'Зберегти країни', 'hybrid-auto-calc' ), 'primary', 'submit', true ); ?>
            </form>
        </div>
        <?php
    }
    
    public function render_currencies_page() {
        $nbu_currencies = get_option( 'hybrid_auto_calc_nbu_currencies', array() );
        
        if ( isset( $_POST['action'] ) && check_admin_referer( 'hybrid_auto_calc_currencies_nonce' ) ) {
            if ( $_POST['action'] === 'save' ) {
                if ( isset( $_POST['currencies'] ) && is_array( $_POST['currencies'] ) ) {
                    foreach ( $nbu_currencies as $code => $currency ) {
                        $nbu_currencies[ $code ]['enabled'] = isset( $_POST['currencies'][ $code ]['enabled'] ) ? 1 : 0;
                    }
                }
                update_option( 'hybrid_auto_calc_nbu_currencies', $nbu_currencies );
                echo '<div class="notice notice-success"><p>' . __( 'Currencies saved successfully!', 'hybrid-auto-calc' ) . '</p></div>';
            }
        }
        ?>
        <div class="wrap">
            <h1><?php _e( 'Керування валютами', 'hybrid-auto-calc' ); ?></h1>
            
            <p><?php _e( 'Оберіть валюти НБУ для відображення у калькуляторі', 'hybrid-auto-calc' ); ?></p>
            
            <form method="POST">
                <?php wp_nonce_field( 'hybrid_auto_calc_currencies_nonce' ); ?>
                
                <table class="wp-list-table striped">
                    <thead>
                        <tr>
                            <th><?php _e( 'Увімкнено', 'hybrid-auto-calc' ); ?></th>
                            <th><?php _e( 'Код', 'hybrid-auto-calc' ); ?></th>
                            <th><?php _e( 'Назва', 'hybrid-auto-calc' ); ?></th>
                            <th><?php _e( 'Курс', 'hybrid-auto-calc' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $nbu_currencies as $code => $currency ) : ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="currencies[<?php echo esc_attr( $code ); ?>][enabled]" 
                                           value="1" <?php checked( $currency['enabled'] ?? 0, 1 ); ?>>
                                </td>
                                <td><?php echo esc_html( $currency['code'] ); ?></td>
                                <td><?php echo esc_html( $currency['name_ua'] ); ?></td>
                                <td><?php echo esc_html( number_format( floatval( $currency['rate'] ), 4 ) ); ?> грн</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <input type="hidden" name="action" value="save">
                <?php submit_button( __( 'Зберегти валюти', 'hybrid-auto-calc' ), 'primary', 'submit', true ); ?>
            </form>
        </div>
        <?php
    }
    
    public function enqueue_admin_scripts( $hook ) {
        if ( strpos( $hook, 'hybrid-auto-calc' ) === false ) {
            return;
        }
        
        wp_enqueue_style(
            'hybrid-auto-calc-admin',
            HYBRID_AUTO_CALC_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            HYBRID_AUTO_CALC_VERSION
        );
    }
    
    public static function get_default_countries() {
        return array(
            '276' => array(
                'code' => '276',
                'name' => 'Німеччина',
                'coefficient' => 1.0,
                'enabled' => 1,
            ),
            '840' => array(
                'code' => '840',
                'name' => 'США',
                'coefficient' => 1.0,
                'enabled' => 1,
            ),
            '392' => array(
                'code' => '392',
                'name' => 'Японія',
                'coefficient' => 1.0,
                'enabled' => 1,
            ),
            '250' => array(
                'code' => '250',
                'name' => 'Франція',
                'coefficient' => 1.0,
                'enabled' => 1,
            ),
            '380' => array(
                'code' => '380',
                'name' => 'Італія',
                'coefficient' => 1.0,
                'enabled' => 1,
            ),
            '826' => array(
                'code' => '826',
                'name' => 'Велика Британія',
                'coefficient' => 1.0,
                'enabled' => 1,
            ),
            '616' => array(
                'code' => '616',
                'name' => 'Польща',
                'coefficient' => 1.0,
                'enabled' => 1,
            ),
            '203' => array(
                'code' => '203',
                'name' => 'Чехія',
                'coefficient' => 1.0,
                'enabled' => 1,
            ),
            '703' => array(
                'code' => '703',
                'name' => 'Словаччина',
                'coefficient' => 1.0,
                'enabled' => 1,
            ),
            '036' => array(
                'code' => '036',
                'name' => 'Австралія',
                'coefficient' => 1.0,
                'enabled' => 1,
            ),
        );
    }
}
