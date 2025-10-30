<div id="ukrauto-calculator">
    <h2 class="calc-title">Калькулятор митних платежів на гібридні автомобілі</h2>
    
    <div id="apiStatus" class="api-status"></div>
    
    <form id="calculatorForm">
        <!-- Тип особи -->
        <div class="form-group">
            <label class="form-label"><?php _e( 'Тип особи:', 'hybrid-auto-calc' ); ?></label>
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="user" value="0" checked>
                    <span><?php _e( 'Фізична особа', 'hybrid-auto-calc' ); ?></span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="user" value="1">
                    <span><?php _e( 'Юридична особа', 'hybrid-auto-calc' ); ?></span>
                </label>
            </div>
        </div>
        
        <!-- Тип двигуна -->
        <div class="form-group">
            <label class="form-label"><?php _e( 'Тип двигуна:', 'hybrid-auto-calc' ); ?></label>
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="motor" value="2" checked>
                    <span><?php _e( 'Гібрид (бензин)', 'hybrid-auto-calc' ); ?></span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="motor" value="4">
                    <span><?php _e( 'Гібрид (дизель)', 'hybrid-auto-calc' ); ?></span>
                </label>
            </div>
        </div>
        
        <!-- Особливості -->
        <div class="form-group" id="chargeOption">
            <label class="radio-label">
                <input type="checkbox" name="feature" value="2">
                <span><?php _e( 'Здатний заряджатися від зовнішнього джерела електроенергії', 'hybrid-auto-calc' ); ?></span>
            </label>
        </div>
        
        <!-- Вік (для бензин) -->
        <div class="form-group" id="agePetrol">
            <label class="form-label"><?php _e( 'Вік:', 'hybrid-auto-calc' ); ?></label>
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="age" value="0" checked>
                    <span><?php _e( 'Новий', 'hybrid-auto-calc' ); ?></span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="age" value="1">
                    <span><?php _e( 'До 5-и років', 'hybrid-auto-calc' ); ?></span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="age" value="2">
                    <span><?php _e( 'Понад 5-и років', 'hybrid-auto-calc' ); ?></span>
                </label>
            </div>
        </div>
        
        <!-- Вік (для дизель) -->
        <div class="form-group hidden" id="ageDiesel">
            <label class="form-label"><?php _e( 'Вік:', 'hybrid-auto-calc' ); ?></label>
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="ageDiesel" value="0" checked>
                    <span><?php _e( 'Новий', 'hybrid-auto-calc' ); ?></span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="ageDiesel" value="3">
                    <span><?php _e( 'Б/в', 'hybrid-auto-calc' ); ?></span>
                </label>
            </div>
        </div>
        
        <!-- Рік випуску -->
        <div class="form-group" id="yearGroup">
            <label class="form-label"><?php _e( 'Рік випуску:', 'hybrid-auto-calc' ); ?></label>
            <select name="year" class="form-control input-small">
                <?php
                $current_year = intval( date( 'Y' ) );
                for ( $i = 0; $i < 17; $i++ ) {
                    $year = $current_year - $i;
                    $label = ( $year === 2009 ) ? '2009 і раніше' : $year;
                    echo '<option value="' . esc_attr( $year ) . '">' . esc_html( $label ) . '</option>';
                }
                ?>
            </select>
        </div>
        
        <!-- Країна -->
        <div class="form-group">
            <label class="form-label"><?php _e( 'Країна походження:', 'hybrid-auto-calc' ); ?></label>
            <select name="country" class="form-control">
                <option value=""><?php _e( 'Не визначена', 'hybrid-auto-calc' ); ?></option>
                <?php foreach ( $enabled_countries as $code => $country ) : ?>
                    <option value="<?php echo esc_attr( $country['code'] ); ?>">
                        <?php echo esc_html( $country['name'] ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Об'єм -->
        <div class="form-group">
            <label class="form-label"><?php _e( 'Об\'єм:', 'hybrid-auto-calc' ); ?></label>
            <div class="input-row">
                <input type="number" name="capacity" class="form-control input-small" 
                       placeholder="0" value="3000" required>
                <span>см³</span>
            </div>
        </div>
        
        <!-- Валюта -->
        <div class="form-group">
            <label class="form-label"><?php _e( 'Валюта:', 'hybrid-auto-calc' ); ?></label>
            <div class="input-row">
                <select name="currency" id="currency" class="form-control">
                    <?php if ( is_array( $enabled_currencies ) ) : ?>
                        <?php foreach ( $enabled_currencies as $curr ) : 
                            $code = $curr['code'] ?? '';
                            $name = $curr['name_ua'] ?? '';
                            $rate = isset( $curr['value'] ) ? $curr['value'] : ( $curr['rate'] ?? 0 );
                        ?>
                            <option value="<?php echo esc_attr( $code ); ?>" data-rate="<?php echo esc_attr( $rate ); ?>">
                                <?php echo esc_html( $name ); ?> (<?php echo esc_html( $code ); ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <span class="info-text">
                    <?php _e( 'Курс:', 'hybrid-auto-calc' ); ?> <strong id="currencyRate">-</strong>
                </span>
            </div>
        </div>
        
        <!-- Вартість -->
        <div class="form-group">
            <label class="form-label"><?php _e( 'Вартість:', 'hybrid-auto-calc' ); ?></label>
            <div class="input-row">
                <input type="number" name="cost" class="form-control input-small" 
                       placeholder="0.00" value="30000.00" step="0.01" required>
                <span id="currencySymbol">USD</span>
            </div>
        </div>
        
        <!-- Кнопки -->
        <div class="btn-group">
            <button type="submit" class="btn btn-primary" id="calculateBtn">
                <?php _e( 'Розрахунок', 'hybrid-auto-calc' ); ?>
            </button>
            <button type="button" class="btn btn-secondary" onclick="hybridAutoCalcReset()">
                <?php _e( 'Очищення', 'hybrid-auto-calc' ); ?>
            </button>
        </div>
    </form>
    
    <!-- Результат -->
    <div id="resultBox" class="result-box">
        <h3 class="result-title"><?php _e( 'Результат розрахунку', 'hybrid-auto-calc' ); ?></h3>
        <div class="result-row">
            <span><?php _e( 'Вартість автомобіля:', 'hybrid-auto-calc' ); ?></span>
            <strong id="resultCost">0.00 грн</strong>
        </div>
        <div class="result-row">
            <span><?php _e( 'Мито:', 'hybrid-auto-calc' ); ?></span>
            <strong id="resultDuty">0.00 грн</strong>
        </div>
        <div class="result-row">
            <span><?php _e( 'ПДВ:', 'hybrid-auto-calc' ); ?></span>
            <strong id="resultVAT">0.00 грн</strong>
        </div>
        <div class="result-row">
            <span><?php _e( 'Акциз:', 'hybrid-auto-calc' ); ?></span>
            <strong id="resultExcise">0.00 грн</strong>
        </div>
        <div class="result-row result-total">
            <span><?php _e( 'Загальна сума:', 'hybrid-auto-calc' ); ?></span>
            <strong class="value" id="resultTotal">0.00 грн</strong>
        </div>
        
        <div class="result-currency">
            <div class="result-currency-title"><?php _e( 'Для довідки (в обраній валюті):', 'hybrid-auto-calc' ); ?></div>
            <div class="result-row">
                <span><?php _e( 'Загальна сума:', 'hybrid-auto-calc' ); ?></span>
                <strong id="resultTotalCurrency">0.00</strong>
            </div>
        </div>
    </div>
</div>
