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
    
    let currentRate = 1;
    let currentCode = 'UAH';

    $(document).ready(function() {
        log('Initializing calculator');
        
        // Initialize
        initializeCalculator();
        setupEventHandlers();
        // Ensure initial rate fetch after DOM is ready
        setTimeout(updateCurrencyRate, 0);
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
        
        currentCode = code || 'UAH';

        // Show/fetch rate only if not UAH
        if (currentCode === 'UAH') {
            currentRate = 1;
            $('#currencyRate').text('-');
        } else {
            // Always fetch fresh to ensure accuracy, then cache to option
            $.ajax({
                type: 'POST',
                url: hybridAutoCalc.ajaxUrl,
                data: {
                    action: 'hybrid_auto_calc_get_currency',
                    _ajax_nonce: hybridAutoCalc.nonce,
                    currency_code: currentCode
                },
                dataType: 'json',
                success: function(resp){
                    if (resp.success && resp.data && resp.data.value) {
                        currentRate = parseFloat(resp.data.value);
                        selectedOption.data('rate', currentRate);
                        $('#currencyRate').text(currentRate.toFixed(4));
                    } else {
                        // Fallback to data-rate if present
                        currentRate = rate ? parseFloat(rate) : NaN;
                        $('#currencyRate').text(isNaN(currentRate) ? '-' : currentRate.toFixed(4));
                    }
                },
                error: function(){
                    currentRate = rate ? parseFloat(rate) : NaN;
                    $('#currencyRate').text(isNaN(currentRate) ? '-' : currentRate.toFixed(4));
                }
            });
        }
        $('#currencySymbol').text(currentCode);
        
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
        let duty = 0, vat = 0, excise = 0, pension = 0;
        
        if (data.payments && typeof data.payments === 'object') {
            duty = parseFloat(data.payments.duty?.sum_ua || 0);
            vat = parseFloat(data.payments.vat?.sum_ua || 0);
            excise = parseFloat(data.payments.excise?.sum_ua || 0);
            pension = parseFloat(data.payments.pension_fund?.sum_ua || 0);
        }
        
        $('#resultDuty').text(duty.toFixed(2) + ' грн');
        $('#resultVAT').text(vat.toFixed(2) + ' грн');
        $('#resultExcise').text(excise.toFixed(2) + ' грн');
        // Ensure Pension row exists and fill it
        if ($('.result-row.result-pension').length === 0) {
            $('<div class="result-row result-pension">'
              + '<span>Пенсійний фонд:</span>'
              + '<strong id="resultPension">0.00 грн</strong>'
              + '</div>').insertBefore($('.result-row.result-total'));
        }
        $('#resultPension').text(pension.toFixed(2) + ' грн');
        
        // Total
        const totalUa = parseFloat(data.payments_ua_sum || 0).toFixed(2);
        $('#resultTotal').text(totalUa + ' грн');
        
        // Currency conversion + show selected rate and cost in selected currency
        const currencyCode = $('#currency').val();
        const totalCurrency = parseFloat(data.payments_sum || 0).toFixed(2);
        $('#resultTotalCurrency').text(currencyCode + ' ' + totalCurrency);
        // Inject rate row (if not present)
        const $resultCurrencyBox = $('.result-currency');
        // Cost in selected currency
        const costOriginal = (parseFloat(data.cost_uah || 0) && parseFloat(data.exchange_rate || 0))
            ? (parseFloat(data.cost_uah) / parseFloat(data.exchange_rate))
            : 0;
        if ($resultCurrencyBox.find('.result-cost-currency-row').length === 0) {
            $('<div class="result-row result-cost-currency-row">'
              + '<span>Вартість у валюті ('+currencyCode+'):</span>'
              + '<strong>'+ (currencyCode==='UAH' ? '-' : costOriginal.toFixed(2)) +'</strong>'
              + '</div>').insertAfter($resultCurrencyBox.find('.result-currency-title'));
        } else {
            $resultCurrencyBox.find('.result-cost-currency-row strong').text(currencyCode==='UAH' ? '-' : costOriginal.toFixed(2));
            $resultCurrencyBox.find('.result-cost-currency-row span').text('Вартість у валюті ('+currencyCode+'):');
        }
        if ($resultCurrencyBox.find('.result-rate-row').length === 0) {
            $('<div class="result-row result-rate-row">'
              + '<span>Курс ('+currencyCode+'):</span>'
              + '<strong>'+ (currencyCode==='UAH' ? '-' : (currentRate ? currentRate.toFixed(4) : '-')) +'</strong>'
              + '</div>').insertAfter($resultCurrencyBox.find('.result-currency-title'));
        } else {
            $resultCurrencyBox.find('.result-rate-row strong').text(currencyCode==='UAH' ? '-' : (currentRate ? currentRate.toFixed(4) : '-'));
            $resultCurrencyBox.find('.result-rate-row span').text('Курс ('+currencyCode+'):');
        }
        // ===== Формула розрахунку (пояснення) =====
        const dutyRate = (data.payments && data.payments.duty && typeof data.payments.duty.rate !== 'undefined') ? data.payments.duty.rate : 10;
        const vatRate = (data.payments && data.payments.vat && typeof data.payments.vat.rate !== 'undefined') ? data.payments.vat.rate : 20;
        const dutySum = parseFloat(data.payments && data.payments.duty ? (data.payments.duty.sum_ua || 0) : 0);
        const exciseBase = parseFloat(data.payments && data.payments.excise ? (data.payments.excise.base || 0) : 0);
        const exciseSum = parseFloat(data.payments && data.payments.excise ? (data.payments.excise.sum_ua || 0) : 0);
        const vatSum = parseFloat(data.payments && data.payments.vat ? (data.payments.vat.sum_ua || 0) : 0);
        // pension already parsed above
        const transport = 0; // якщо додасте на бекенді — підставиться з data
        const exch = parseFloat(data.exchange_rate || currentRate || 0);
        const costUaNum = parseFloat(costUa || 0);
        const dutyLine = `Мито = ${costUaNum.toLocaleString()} × ${dutyRate}% = ${dutySum.toFixed(2)} грн`;
        const exciseLine = `Акциз = ${exciseBase.toFixed(2)} × курс (${currencyCode === 'EUR' ? '1.0000' : (exch ? exch.toFixed(4) : (currentRate ? currentRate.toFixed(4) : '-'))}) = ${exciseSum.toFixed(2)} грн`;
        const vatLine = `ПДВ = (${costUaNum.toLocaleString()} + ${dutySum.toFixed(2)} + ${exciseSum.toFixed(2)}) × ${vatRate}% = ${vatSum.toFixed(2)} грн`;
        const totalLine = `Разом = ${dutySum.toFixed(2)} + ${exciseSum.toFixed(2)} + ${vatSum.toFixed(2)} + ${pension.toFixed(2)} + ${transport.toFixed(2)} = ${(dutySum+exciseSum+vatSum+pension+transport).toFixed(2)} грн`;

        const formulaBlock = (
            '<div class="result-currency" style="margin-top:15px;">'
          +   '<div class="result-currency-title">Формула розрахунку</div>'
          +   '<div class="result-row"><span>' + dutyLine + '</span></div>'
          +   '<div class="result-row"><span>' + exciseLine + '</span></div>'
          +   '<div class="result-row"><span>' + vatLine + '</span></div>'
          +   '<div class="result-row"><span>' + totalLine + '</span></div>'
          + '</div>'
        );

        if ($resultBox.find('.result-currency .result-currency-title').filter(function(){return $(this).text()==='Формула розрахунку';}).length === 0) {
            $resultBox.append(formulaBlock);
        } else {
            const $formula = $resultBox.find('.result-currency').filter(function(){return $(this).find('.result-currency-title').text()==='Формула розрахунку';});
            const $rows = $formula.find('.result-row');
            $rows.eq(0).find('span').text(dutyLine);
            $rows.eq(1).find('span').text(exciseLine);
            $rows.eq(2).find('span').text(vatLine);
            $rows.eq(3).find('span').text(totalLine);
        }
        
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
