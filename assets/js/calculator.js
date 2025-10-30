// Hybrid Auto Calculator JavaScript

(function($) {
    'use strict';
    
    const log = function(message, data = null) {
        if (hybridAutoCalc.isLogging) {
            if (data) {
                console.log('[HybridAutoCal] ' + message, data);
            } else {
                console.log('[HybridAutoCal] ' + message);
            }
        }
    };
    
    $(document).ready(function() {
        log('Initializing calculator');
        
        // Initialize
        initializeCalculator();
        setupEventHandlers();
        updateCurrencyRate();
    });
    
    function initializeCalculator() {
        // Set default motor type visibility
        const motorType = $('input[name="motor"]:checked').val();
        updateMotorType(motorType);
    }
    
    function setupEventHandlers() {
        // Motor type change
        $(document).on('change', 'input[name="motor"]', function() {
            const motorType = $(this).val();
            updateMotorType(motorType);
        });
        
        // Currency change
        $(document).on('change', '#currency', function() {
            updateCurrencyRate();
        });
        
        // Form submit
        $('#calculatorForm').on('submit', function(e) {
            e.preventDefault();
            handleCalculate();
        });
    }
    
    function updateMotorType(motorType) {
        const isPetrol = motorType === '2';
        
        if (isPetrol) {
            $('#chargeOption').show();
            $('#agePetrol').show();
            $('#ageDiesel').hide();
            $('#yearGroup').show();
        } else {
            $('#chargeOption').hide();
            $('#agePetrol').hide();
            $('#ageDiesel').show();
            $('#yearGroup').hide();
        }
        
        log('Motor type changed to: ' + (isPetrol ? 'petrol' : 'diesel'));
    }
    
    function updateCurrencyRate() {
        const $currencySelect = $('#currency');
        const selectedOption = $currencySelect.find('option:selected');
        const rate = selectedOption.data('rate');
        const code = selectedOption.val();
        const text = selectedOption.text();
        
        // Show rate only if not UAH
        if (code === 'UAH') {
            $('#currencyRate').text('-');
        } else {
            $('#currencyRate').text(rate ? parseFloat(rate).toFixed(4) : '-');
        }
        $('#currencySymbol').text(code);
        
        log('Currency updated', {
            code: code,
            rate: rate
        });
    }
    
    function handleCalculate() {
        const $btn = $('#calculateBtn');
        const $status = $('#apiStatus');
        
        // Show loading status
        $status.removeClass('success error').addClass('loading');
        $status.text('Розрахунок...').show();
        $btn.prop('disabled', true);
        
        // Collect form data
        const formData = {
            action: 'hybrid_auto_calc_calculate',
            _ajax_nonce: hybridAutoCalc.nonce,
            user: $('input[name="user"]:checked').val(),
            motor: $('input[name="motor"]:checked').val(),
            age: $('input[name="motor"]:checked').val() === '2' 
                ? $('input[name="age"]:checked').val() 
                : $('input[name="ageDiesel"]:checked').val(),
            capacity: $('input[name="capacity"]').val(),
            currency: $('#currency').val(),
            cost: $('input[name="cost"]').val(),
            year: $('select[name="year"]').val(),
            country: $('select[name="country"]').val(),
            feature: $('input[name="feature"]:checked').val() || ''
        };
        
        log('Sending calculate request', formData);
        
        $.ajax({
            type: 'POST',
            url: hybridAutoCalc.ajaxUrl,
            data: formData,
            dataType: 'json',
            success: function(response) {
                log('Calculate response received', response);
                
                if (response.success) {
                    $status.removeClass('loading error').addClass('success');
                    $status.text('✓ Розрахунок завершено').fadeOut(2000);
                    displayResults(response.data);
                } else {
                    showError($status, response.data?.message || 'Помилка розрахунку');
                }
            },
            error: function(xhr, status, error) {
                log('Error during calculation', {
                    status: status,
                    error: error
                });
                showError($status, 'Помилка з\'єднання з сервером');
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    }
    
    function displayResults(data) {
        log('Displaying results', data);
        
        const $resultBox = $('#resultBox');
        
        // Format cost in UAH
        const costUa = parseFloat(data.cost_uah || 0).toFixed(2);
        $('#resultCost').text(costUa + ' грн');
        
        // Parse payments from new structure
        let duty = 0, vat = 0, excise = 0;
        
        if (data.payments && typeof data.payments === 'object') {
            duty = parseFloat(data.payments.duty?.sum_ua || 0);
            vat = parseFloat(data.payments.vat?.sum_ua || 0);
            excise = parseFloat(data.payments.excise?.sum_ua || 0);
        }
        
        $('#resultDuty').text(duty.toFixed(2) + ' грн');
        $('#resultVAT').text(vat.toFixed(2) + ' грн');
        $('#resultExcise').text(excise.toFixed(2) + ' грн');
        
        // Total
        const totalUa = parseFloat(data.payments_ua_sum || 0).toFixed(2);
        $('#resultTotal').text(totalUa + ' грн');
        
        // Currency conversion
        const currencyCode = $('#currency').val();
        const totalCurrency = parseFloat(data.payments_sum || 0).toFixed(2);
        $('#resultTotalCurrency').text(currencyCode + ' ' + totalCurrency);
        
        // Show result box
        $resultBox.addClass('show');
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $resultBox.offset().top - 100
        }, 800);
    }
    
    function showError($statusEl, message) {
        $statusEl.removeClass('loading success').addClass('error');
        $statusEl.text('⚠ ' + message).show();
    }
    
    // Public function for reset button
    window.hybridAutoCalcReset = function() {
        log('Resetting calculator');
        $('#calculatorForm')[0].reset();
        $('#resultBox').removeClass('show');
        $('#chargeOption').show();
        $('#agePetrol').show();
        $('#ageDiesel').hide();
        $('#yearGroup').show();
        updateCurrencyRate();
    };
    
})(jQuery);
