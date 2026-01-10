<?php
    $saved_cards = Paytpv::savedActiveCards(get_current_user_id());
    $store_card = (sizeof($saved_cards) == 0) ? "none" : "";

    $paytpvBase = new woocommerce_paytpv(false);
    
    // Mensaje de PayPal alternativo para JavaScript
    $paypal_alt_msg = PAYTPV_PAYPAL_ALTERNATIVE_MSG;
?>
<form role="form" name="aux"></form>
<form role="form" name="paycometPaymentForm" id="paycometPaymentForm" action="javascript:jetIframeValidated()" method="POST">

<div id="saved_cards" style="display:<?=$store_card;?>">
    <div class="form-group">
        <label for="card"><?php print __('Card', 'wc_paytpv'); ?></label>        
        <select name="jet_iframe_card" id="jet_iframe_card" onChange="checkSelectedCard()" class="form-control select2" aria-hidden="true" style="width:100%">
        <?php
            foreach ($saved_cards as $card){
                $card_desc = ($card["card_desc"] != "") ? (" - " . $card["card_desc"]) : "";
        ?>
            <option value="<?= $card['id'] ?>"><?= $card['paytpv_cc'] . $card_desc ?></option>
        <?php
            }
        ?>
            <option value="0"><?php print __('NEW CARD', 'wc_paytpv') ?></option>
        </select>
    </div>
</div>

<div id="toHide" style="display:none;">
    <input type="hidden" data-paycomet="jetID" value="<?php print $jet_id ?>">

    <input type="hidden" class="form-control" name="username" data-paycomet="cardHolderName" placeholder="" value="NONAME" style="height:30px; width: 290px">

    <div class="row">
        <div class="form-group">
            <label for="cardNumber"><?php print __('Card number', 'wc_paytpv');?></label>
            <div class="input-group">
                <div id="paycomet-pan" style="<?php print $pan_div_style ?>"></div>   
                <input paycomet-style="<?php print $pan_input_style ?>" paycomet-name="pan">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-md-9" style="padding-left:0px;">
            <div class="form-group">
                <label><span class="hidden-xs"><?php print __('Expiration date', 'wc_paytpv');?></span> </label>
                <div class="form-inline">
                    <select id="paycomet_card_month" class="form-control" class="form-control select2" aria-hidden="true" style="width:142px; border: 1px solid #dcd7ca; font-size: 18px; padding: 0 0 0 10px!important;" data-paycomet="dateMonth">
                        <option><?php print __('Month', 'wc_paytpv');?></option>
                        <option value="01"><?php print __('01 - January', 'wc_paytpv');?></option>
                        <option value="02"><?php print __('02 - February', 'wc_paytpv');?></option>
                        <option value="03"><?php print __('03 - March', 'wc_paytpv');?></option>
                        <option value="04"><?php print __('04 - April', 'wc_paytpv');?></option>
                        <option value="05"><?php print __('05 - May', 'wc_paytpv');?></option>
                        <option value="06"><?php print __('06 - June', 'wc_paytpv');?></option>
                        <option value="07"><?php print __('07 - July', 'wc_paytpv');?></option>
                        <option value="08"><?php print __('08 - August', 'wc_paytpv');?></option>
                        <option value="09"><?php print __('09 - September', 'wc_paytpv');?></option>
                        <option value="10"><?php print __('10 - October', 'wc_paytpv');?></option>
                        <option value="11"><?php print __('11 - November', 'wc_paytpv');?></option>
                        <option value="12"><?php print __('12 - December', 'wc_paytpv');?></option>
                    </select>

                    <select id="paycomet_card_year" class="form-control" class="form-control select2" aria-hidden="true" style="width:142px; border: 1px solid #dcd7ca; font-size: 18px; padding: 0 0 0 10px!important;"data-paycomet="dateYear">
                        <option><?php print __('Year', 'wc_paytpv');?></option>

                        <?php
                            $firstYear = (int) date('Y');
                            for($i = 0; $i <= 8; $i++) { ?>
                            <option value="<?= substr($firstYear, 2, 2) ?>"><?= $firstYear?></option>
                        <?php
                                $firstYear++;
                            }
                        ?>
                    </select>

                </div>
            </div>
        </div>
    </div>
    <div class="row">

        <div class="col-xs-12 col-md-3" style="padding-left:0px;">

            <div class="form-group">

                <label data-toggle="tooltip" title=""
                    data-original-title="3 digits code on back side of the card">
                    CVV <i class="fa fa-question-circle"></i>
                </label>

                <div id="paycomet-cvc2" style="<?php print $cvc2_div_style ?>"></div>

                <input paycomet-name="cvc2" paycomet-style="<?php print $cvc2_input_style ?>" class="form-control" required="" type="text">

            </div>
        </div>
    </div>
</div>

    <?php if(get_current_user_id() > 0 && $disable_offer_savecard == 0) { ?>
        <div id="storingStep" class="box" style="display:none;">
            <label class="checkbox"><input type="checkbox" name="jetiframe_savecard" id="jetiframe_savecard"> <?php print __('Save card for future purchases', 'wc_paytpv' ) ?><span class="paytpv-pci"> <?php print __('Card data is protected by the Payment Card Industry Data Security Standard (PCI DSS)', 'wc_paytpv' ) ?></span></label>
        </div>
    <?php
        }
    ?>
    <input type="submit" style="width: 290px; display:none;" name="jetiframe-button" id="jetiframe-button" value="<?php print __('Make payment', 'wc_paytpv');?>">
</form>

<div id="paymentErrorMsg" style="display:none; color: #fff; background: #b22222; margin-top: 10px; padding: 10px; text-align: center; border-radius: 3px;">

</div>

<script type="text/javascript">
// PAYCOMET script loading state management
var paycometScriptLoaded = false;
var paycometScriptError = false;
var paycometScriptRetries = 0;
var paycometScriptMaxRetries = 3;

// Toggle payment form visibility based on saved card selection
function checkSelectedCard() {
    if (document.getElementById('jet_iframe_card').value != 0){
        document.getElementById('toHide').style.display = "none";
        document.getElementById('storingStep').style.display = "none";
        jQuery('#paymentErrorMsg').hide();
    } else {
        if (document.getElementById('toHide')) {
            document.getElementById('toHide').style.display = "block";
        }
        if (document.getElementById('storingStep')) {
            document.getElementById('storingStep').style.display = "block";
        }
        checkPaycometScript();
    }

    document.getElementById('hiddenCardField').value = document.getElementById('jet_iframe_card').value;
};

function showPaymentError(message) {
    var errorDiv = document.getElementById('paymentErrorMsg');
    errorDiv.innerHTML = message;
    errorDiv.style.display = 'block';
    jQuery('#place_order').prop("disabled", false);
}

// Verify PAYCOMET script is loaded before payment attempt
function checkPaycometScript() {
    if (document.getElementById('jet_iframe_card') && document.getElementById('jet_iframe_card').value == 0) {
        if (paycometScriptError) {
            showPaymentError('<?php echo esc_js(__("Error loading payment system", "wc_paytpv") . ". " . $paypal_alt_msg . " " . __("or disable your ad blocker and refresh the page", "wc_paytpv")); ?>');
            return false;
        } else if (!paycometScriptLoaded && typeof window.PaycometClient === 'undefined') {
            showPaymentError('<?php echo esc_js(__("Payment system is loading... Please wait a moment and try again", "wc_paytpv") . ". " . __("If the error persists", "wc_paytpv") . ", " . strtolower($paypal_alt_msg)); ?>');
            return false;
        }
    }
    return true;
}

// Handle JetIframe validation and form submission
function jetIframeValidated(){
    jQuery('#paymentErrorMsg').hide();
    
    if (document.getElementById("jetiframe_savecard") != null) {
        document.getElementById("savecard_jetiframe").checked = document.getElementById("jetiframe_savecard").checked;
    }

    // Validate token exists and has value
    var paytpvTokenElement = document.getElementsByName("paytpvToken")[0];
    if (!paytpvTokenElement || !paytpvTokenElement.value) {
        showPaymentError('<?php echo esc_js(__("Payment validation error. Please check your card details and try again", "wc_paytpv") . ", " . strtolower($paypal_alt_msg)); ?>');
        return false;
    }

    document.getElementById("jetiframe-token").value = paytpvTokenElement.value;
    if (jQuery("#jetiframe-token").val() != "") {
        jQuery('#place_order').parents('form:first').submit();
    } else {
        showPaymentError('<?php echo esc_js(__("Error processing payment", "wc_paytpv") . ". " . $paypal_alt_msg); ?>');
        return false;
    }
}

function enablePlaceOrder() {
    jQuery('#place_order').prop("disabled",false);
}

// Load PAYCOMET script with automatic retry mechanism
function loadPaycometScript() {
    var scriptUrl = 'https://api.paycomet.com/gateway/paycomet.jetiframe.js?lang=<?=strtolower($paytpvBase->_getLanguange("EN"));?>';
    
    jQuery.getScript(scriptUrl)
        .done(function() {
            paycometScriptLoaded = true;
            paycometScriptRetries = 0;
            if (typeof console !== 'undefined' && console.log) {
                console.log('PAYCOMET: Script loaded successfully');
            }
            jQuery('#paymentErrorMsg').hide();
        })
        .fail(function() {
            paycometScriptRetries++;
            if (typeof console !== 'undefined' && console.error) {
                console.error('PAYCOMET: Script load failed (attempt ' + paycometScriptRetries + '/' + paycometScriptMaxRetries + ')');
            }
            
            if (paycometScriptRetries < paycometScriptMaxRetries) {
                var retryDelay = paycometScriptRetries * 1000;
                if (typeof console !== 'undefined' && console.log) {
                    console.log('PAYCOMET: Retrying in ' + (retryDelay/1000) + 's...');
                }
                setTimeout(function() {
                    loadPaycometScript();
                }, retryDelay);
            } else {
                paycometScriptError = true;
                if (typeof console !== 'undefined' && console.error) {
                    console.error('PAYCOMET: Script failed after ' + paycometScriptMaxRetries + ' attempts');
                }
                if (document.getElementById('jet_iframe_card') && document.getElementById('jet_iframe_card').value == 0) {
                    showPaymentError('<?php echo esc_js(__("Error loading payment system", "wc_paytpv") . ". " . $paypal_alt_msg . " " . __("or disable your ad blocker and refresh the page", "wc_paytpv")); ?>');
                }
            }
        });
}

// Initialize payment form and event handlers
jQuery( function( $ ) {
    if (typeof $.fn.select2 !== 'undefined') {
        $('#jet_iframe_card, #paycomet_card_month, #paycomet_card_year').select2();
    }
    
    if ($("#paycometPaymentForm").val() == "") {
        loadPaycometScript();
    }

    $( "#place_order").on('click',function( event ) {
        if ($( '#payment_method_paytpv' ).is( ':checked' )) {
            event.preventDefault();

            new_card = (document.getElementById('jet_iframe_card').value == 0)?true:false;

            if (new_card) {
                if (!checkPaycometScript()) {
                    return false;
                }
                
                jQuery('#place_order').prop("disabled",true);
                $("#jetiframe-button").click();
            } else {
                $('#place_order').parents('form:first').submit();
            }

            setTimeout(() => {  enablePlaceOrder() }, 2000);
        }
    });

    setTimeout(() => {  
        checkSelectedCard();
        checkPaycometScript();
    }, 100);
});

</script>