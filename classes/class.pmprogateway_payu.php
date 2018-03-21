<?php
if (!defined( 'ABSPATH' )) exit; // Exit if accessed directly
if(!function_exists('payu_pmp_gateway_load')){
    add_action( 'plugins_loaded', 'payu_pmp_load_textdomain' );
    add_action( 'plugins_loaded', 'payu_pmp_gateway_load', 20);

    function payu_pmp_load_textdomain()
    {
        load_plugin_textdomain( 'pmpro-payu-gateway', false, dirname( plugin_basename( PMPRO_PAYUGATEWAY_FILE ) ) . '/languages' );
    }

    function payu_pmp_gateway_load()
    {
        if (!class_exists('PMProGateway')) return;
        //load classes init method
        add_action('init', array('PMProGateway_Payu', 'init'));
        add_filter('pmpro_currencies', array('PMProGateway_Payu', 'pmpro_currencies'), 100, 1);
        add_filter('plugin_action_links_' . plugin_basename(PMPRO_PAYUGATEWAY_FILE), array('PMProGateway_Payu', 'plugin_action_links'));


        class PMProGateway_Payu extends PMProGateway
        {
            function PMProGateway($gateway = NULL)
            {

                $this->gateway = $gateway;
                return $this->gateway;
            }

            /**
             * Run on WP init
             *
             * @since 1.8
             */
            static function init()
            {
                //make sure PayPal Express is a gateway option
                add_filter('pmpro_gateways', array('PMProGateway_Payu', 'pmpro_gateways'));
                add_filter('pmpro_include_billing_address_fields', '__return_false');
                add_filter('pmpro_include_payment_information_fields', '__return_false');

                //add fields to payment settings
                add_filter('pmpro_payment_options', array('PMProGateway_Payu', 'pmpro_payment_options'));
                add_filter('pmpro_payment_option_fields', array('PMProGateway_Payu', 'pmpro_payment_option_fields'), 10, 2);
                add_filter('pmpro_required_billing_fields', array('PMProGateway_Payu', 'pmpro_required_billing_fields'));
                add_filter('pmpro_checkout_confirmed', array('PMProGateway_Payu', 'pmpro_checkout_confirmed'));
            }

            static function plugin_action_links($links)
            {

                $mylinks[] = '<a href="'.admin_url('admin.php?page=pmpro-paymentsettings').'">'.__( 'Settings', 'pmpro-payu-gateway' ).'</a>';
                $mylinks[] = '<a href="https://saulmoralespa.github.io/pmpro-payu-gateway/" target="_blank">'.__( 'Support', 'pmpro-payu-gateway' ).'</a>';
                return array_merge($links, $mylinks);
            }

            /**
             * Make sure this gateway is in the gateways list
             *
             * @since 1.8
             */
            static function pmpro_gateways($gateways)
            {
                if(empty($gateways['payu']))
                    $gateways['payu'] = __('PayU', 'pmpro-payu-gateway' );

                return $gateways;
            }

            /**
             * Get a list of payment options that the this gateway needs/supports.
             *
             * @since 1.8
             */
            static function getGatewayOptions()
            {
                $options = array(
                    'sslseal',
                    'nuclear_HTTPS',
                    'gateway_environment',
                    'merchant_id',
                    'account_id',
                    'apikey',
                    'apilogin',
                    'currency',
                    'use_ssl',
                    'tax_state',
                    'tax_rate',
                    'accepted_credit_cards'
                );

                return $options;
            }

            /**
             * Set payment options for payment settings page.
             *
             * @since 1.8
             */
            static function pmpro_payment_options($options)
            {
                //get stripe options
                $payu_options = PMProGateway_Payu::getGatewayOptions();

                //merge with others.
                $options = array_merge($payu_options, $options);

                return $options;
            }

            /**
             * Display fields for this gateway's options.
             *
             * @since 1.8
             */
            static function pmpro_payment_option_fields($values, $gateway)
            {
                ?>
                <tr class="pmpro_settings_divider gateway gateway_payu" <?php if($gateway != "payu") { ?>style="display: none;"<?php } ?>>
                    <td colspan="2">
                        <?php _e('payU Settings', 'pmpro-payu-gateway'); ?>
                    </td>
                </tr>
                <tr class="gateway gateway_payu" <?php if($gateway != "payu") { ?>style="display: none;"<?php } ?>>
                    <th scope="row" valign="top">
                        <label for="merchant_id"><?php _e('Merchant ID', 'pmpro-payu-gateway' );?>:</label>
                    </th>
                    <td>
                        <input type="text" id="merchant_id" name="merchant_id" size="60" value="<?php echo esc_attr($values['merchant_id'])?>" />
                    </td>
                </tr>
                <tr class="gateway gateway_payu" <?php if($gateway != "payu") { ?>style="display: none;"<?php } ?>>
                    <th scope="row" valign="top">
                        <label for="account_id"><?php _e('Account ID', 'pmpro-payu-gateway' );?>:</label>
                    </th>
                    <td>
                        <input type="text" id="account_id" name="account_id" size="60" value="<?php echo esc_attr($values['account_id'])?>" />
                    </td>
                </tr>
                <tr class="gateway gateway_payu" <?php if($gateway != "payu") { ?>style="display: none;"<?php } ?>>
                    <th scope="row" valign="top">
                        <label for="apikey"><?php _e('Apikey', 'pmpro-payu-gateway' );?>:</label>
                    </th>
                    <td>
                        <input type="text" id="apikey" name="apikey" size="60" value="<?php echo esc_attr($values['apikey'])?>" />
                    </td>
                </tr>
                <tr class="gateway gateway_payu" <?php if($gateway != "payu") { ?>style="display: none;"<?php } ?>>
                    <th scope="row" valign="top">
                        <label for="apilogin"><?php _e('Apilogin', 'pmpro-payu-gateway' );?>:</label>
                    </th>
                    <td>
                        <input type="text" id="apilogin" name="apilogin" size="60" value="<?php echo esc_attr($values['apilogin'])?>" />
                    </td>
                </tr>
                <?php
            }

            /**
             * Remove required billing fields
             *
             * @since 1.8
             */
            static function pmpro_required_billing_fields($fields)
            {
                unset($fields['bfirstname']);
                unset($fields['blastname']);
                unset($fields['baddress1']);
                unset($fields['bcity']);
                unset($fields['bstate']);
                unset($fields['bzipcode']);
                unset($fields['bphone']);
                unset($fields['bemail']);
                unset($fields['bcountry']);
                unset($fields['CardType']);
                unset($fields['AccountNumber']);
                unset($fields['ExpirationMonth']);
                unset($fields['ExpirationYear']);
                unset($fields['CVV']);

                return $fields;
            }

            static function pmpro_currencies( $currencies )
            {
                $currencies['COP'] = __('Colombian pesos (&#36;)', 'pmpro-payu-gateway' );
                $currencies['PEN'] = __('Peruvian sol (S/)', 'pmpro-payu-gateway' );
                return $currencies;
            }

            /**
             * Review and Confirmation code.
             *
             * @since 1.8
             */
            static function pmpro_checkout_confirmed($pmpro_confirmed)
            {
                if (isset($_REQUEST['transactionState'])){
                    $morder = new MemberOrder();
                    var_dump($morder);
                    $morder->payment_transaction_id = $_REQUEST['transactionId'];
                    $morder->updateStatus("success");

                }
            }


            /**
             * Process checkout.
             *
             */
            function process(&$order)
            {
                $order->payment_type = "PayU";
                $order->cardtype = "";
                $order->ProfileStartDate = date_i18n("Y-m-d", strtotime("+ " . $order->BillingFrequency . " " . $order->BillingPeriod)) . "T0:0:0";
                $order->ProfileStartDate = apply_filters("pmpro_profile_start_date", $order->ProfileStartDate, $order);

                $print = print_r($order, true);
                $this->log($print);
                global $pmpro_currency;
                if(empty($order->code))
                $order->code = $order->getRandomCode();
                $apiKey = pmpro_getOption("apikey");
                $apiLogin = pmpro_getOption("apilogin");
                $merchantId = pmpro_getOption("merchant_id");
                $account_id = pmpro_getOption("account_id");
                $tax = $order->getTax(true);
                $reference = $order->code . time();
                $amount = round((float)$order->subtotal, 2);
                $signature = md5("$apiKey~$merchantId~$reference~$amount~$pmpro_currency");
                $urlPay = $this->urlCheckout();
                $environment = $this->environment();
                $confirmation = admin_url("admin-ajax.php") . "?action=payu-ins";
                $responseUrl = pmpro_url("checkout", "?level=" . $order->membership_level->id . "&review=" . $order->code);

                echo '<form method="post" id="payuForm" action="'.$urlPay.'">
  <input name="merchantId"    type="hidden"  value="'.$merchantId.'"   >
  <input name="accountId"     type="hidden"  value="'.$account_id.'" >
  <input name="description"   type="hidden"  value="'.$order->membership_level->name . 'Membership'.'"  >
  <input name="referenceCode" type="hidden"  value="'.$reference.'" >
  <input name="amount"        type="hidden"  value="'.$amount.'"   >
  <input name="tax"           type="hidden"  value="'.$tax.'"  >
  <input name="currency"      type="hidden"  value="'.$pmpro_currency.'" >
  <input name="signature"     type="hidden"  value="'.$signature.'"  >
  <input name="test"          type="hidden"  value="'.$environment.'" >
  <input name="buyerEmail"    type="hidden"  value="'.$order->Email.'" >
  <input name="responseUrl"    type="hidden"  value="'.$responseUrl.'" >
  <input name="confirmationUrl"    type="hidden"  value="'.$confirmation.'" >
</form>
';
                echo '<script>
document.getElementById("payuForm").submit();
</script>';
            }


            function charge(&$order)
            {
                global $pmpro_currency;
                if(empty($order->code))
                    $order->code = $order->getRandomCode();
                require_once(PMPRO_PAYUGATEWAY_DIR . "/lib/PayU.php");
                $urlPay = $this->urlPayu() . 'payments-api/4.0/service.cgi';
                $reports = $this->urlPayu() . 'reports-api/4.0/service.cgi';
                $subscriptions = $this->urlPayu() . 'payments-api/rest/v4.3/';
                $apiKey = pmpro_getOption("apikey");
                $apiLogin = pmpro_getOption("apilogin");
                $merchantId = pmpro_getOption("merchant_id");
                $account_id = pmpro_getOption("account_id");
                PayU::$apiKey = $apiKey;
                PayU::$apiLogin = $apiLogin;
                PayU::$merchantId = $merchantId;
                PayU::$language = SupportedLanguages::ES;
                PayU::$isTest = $this->environment();
                Environment::setPaymentsCustomUrl($urlPay);
                Environment::setReportsCustomUrl($reports);
                Environment::setSubscriptionsCustomUrl($subscriptions);

                $accountnumber = str_replace(' ', '', $order->accountnumber);

                if ($order->cardtype == 'Mastercard' || $order->cardtype == 'Visa' )
                    $cardtype = strtoupper($order->cardtype);
                if (strpos($order->cardtype, 'American') !== false)
                    $cardtype = 'AMEX';
                if (strpos($order->cardtype, 'Diners') !== false)
                    $cardtype = 'DINERS';
                $currency = $pmpro_currency;
                $datecaduce = "$order->expirationyear/$order->expirationmonth";
                if ($currency == 'ARS')
                    $payuCountry = PayUCountries::AR;
                if ($currency == 'BRL')
                    $payuCountry = PayUCountries::BR;
                if ($currency == 'COP')
                    $payuCountry = PayUCountries::CO;
                if ($currency == 'MXN')
                    $payuCountry = PayUCountries::MX;
                if ($currency == 'USD')
                    $payuCountry = PayUCountries::PA;
                if ($currency == 'PEN')
                    $payuCountry = PayUCountries::PE;

                $amount = $order->InitialPayment;
                //tax
                $order->subtotal = $amount;
                $tax = $order->getTax(true);
                $amount = round((float)$order->subtotal + (float)$tax, 2);
                $address2 = !empty($order->Address2) ? $order->Address2 : $order->Address1;
                if(!isset($order->membership_level->name))
                    $order->membership_level->name = "";

                $parameters = array(
                    //Ingrese aquí el identificador de la cuenta.
                    PayUParameters::ACCOUNT_ID => $account_id,
                    //Ingrese aquí el código de referencia.
                    PayUParameters::REFERENCE_CODE => $order->code . time(),
                    //Ingrese aquí la descripción.
                    PayUParameters::DESCRIPTION => $order->membership_level->name . " Membership",
                    // -- Valores --
                    //Ingrese aquí el valor.
                    PayUParameters::VALUE => $amount,
                    //Ingrese aquí la moneda.
                    PayUParameters::CURRENCY => $currency,
                    // -- Comprador
                    //Ingrese aquí el nombre del comprador.
                    PayUParameters::BUYER_NAME => $order->FirstName,
                    //Ingrese aquí el email del comprador.
                    PayUParameters::BUYER_EMAIL => $order->Email,
                    //Ingrese aquí el teléfono de contacto del comprador.
                    PayUParameters::BUYER_CONTACT_PHONE => $order->billing->phone,
                    //Ingrese aquí el documento de contacto del comprador.
                    PayUParameters::BUYER_DNI => '0000000',
                    //Ingrese aquí la dirección del comprador.
                    PayUParameters::BUYER_STREET => $order->Address1,
                    PayUParameters::BUYER_STREET_2 => $address2,
                    PayUParameters::BUYER_CITY => $order->billing->city,
                    PayUParameters::BUYER_STATE => $order->billing->state,
                    PayUParameters::BUYER_COUNTRY => $order->billing->country,
                    PayUParameters::BUYER_POSTAL_CODE => $order->billing->zip,
                    PayUParameters::BUYER_PHONE => $order->billing->phone,
                    // -- pagador --
                    //Ingrese aquí el nombre del pagador.
                    PayUParameters::PAYER_NAME => "APPROVED",
                    //Ingrese aquí el email del pagador.
                    PayUParameters::PAYER_EMAIL => $order->Email,
                    //Ingrese aquí el teléfono de contacto del pagador.
                    PayUParameters::PAYER_CONTACT_PHONE => $order->billing->phone,
                    //Ingrese aquí el documento de contacto del pagador.
                    PayUParameters::PAYER_DNI => '0000000',
                    //Ingrese aquí la dirección del pagador.
                    PayUParameters::PAYER_STREET => $order->Address1,
                    PayUParameters::PAYER_STREET_2 => $order->Address1,
                    PayUParameters::PAYER_CITY => $order->billing->city,
                    PayUParameters::PAYER_STATE => $order->billing->state,
                    PayUParameters::PAYER_COUNTRY => $order->billing->country,
                    PayUParameters::PAYER_POSTAL_CODE => $order->billing->zip,
                    PayUParameters::PAYER_PHONE => $order->billing->phone,
                    // -- Datos de la tarjeta de crédito --
                    //Ingrese aquí el número de la tarjeta de crédito
                    PayUParameters::CREDIT_CARD_NUMBER => $accountnumber,
                    //Ingrese aquí la fecha de vencimiento de la tarjeta de crédito
                    PayUParameters::CREDIT_CARD_EXPIRATION_DATE => $datecaduce,
                    //Ingrese aquí el código de seguridad de la tarjeta de crédito
                    PayUParameters::CREDIT_CARD_SECURITY_CODE => $order->CVV2,
                    //Ingrese aquí el nombre de la tarjeta de crédito
                    //VISA||MASTERCARD||AMEX||DINERS
                    PayUParameters::PAYMENT_METHOD => $cardtype,
                    //Ingrese aquí el número de cuotas.
                    PayUParameters::INSTALLMENTS_NUMBER => "1",
                    //Ingrese aquí el nombre del pais.
                    PayUParameters::COUNTRY => $payuCountry,
                    //Session id del device.
                    PayUParameters::DEVICE_SESSION_ID => md5(session_id().microtime()),
                    //IP del pagadador
                    PayUParameters::IP_ADDRESS => $this->getIP(),
                    //Cookie de la sesión actual.
                    PayUParameters::PAYER_COOKIE => md5(session_id().microtime()),
                    //Cookie de la sesión actual.
                    PayUParameters::USER_AGENT => $_SERVER['HTTP_USER_AGENT']
                );

                try{
                    $response = PayUPayments::doAuthorizationAndCapture($parameters);
                    if($response->transactionResponse->state=="APPROVED")
                    {
                        $order->payment_transaction_id = $response->transactionResponse->transactionId;
                        $order->updateStatus("success");
                        return 'success';
                    }elseif ($response->transactionResponse->state=="PENDING"){
                        $order->updateStatus("pending");
                        return 'pending';
                    }
                    else
                    {
                        //$order->status = "error";
                        $order->errorcode = $response->transactionResponse->state;
                        return false;
                    }
                }catch(PayUException $ex){
                    $this->log($ex->getMessage());
                }

            }

            function getIP(){
                return ($_SERVER['REMOTE_ADDR'] == '::1' || $_SERVER['REMOTE_ADDR'] == '::' ||
                        !preg_match('/^((?:25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9]).){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9])$/m',
                            $_SERVER['REMOTE_ADDR'])) ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
            }

            function environment(){
                $gateway_environment = pmpro_getOption("gateway_environment");
                if($gateway_environment == "live"){
                    $test = false;
                }else{
                    $test = true;
                }
                return $test;
            }

            function urlPayu(){
                if($this->environment()){
                    $host = "https://sandbox.api.payulatam.com/";
                }else{
                    $host = "https://api.payulatam.com/";
                }
                return $host;
            }

            function urlCheckout(){
                if($this->environment()){
                    $host = "https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/";
                }else{
                    $host = " https://checkout.payulatam.com/ppp-web-gateway-payu/";
                }
                return $host;
            }

            function cancel(&$order) {
                //require a subscription id
                if(empty($order->subscription_transaction_id))
                    return false;

                //code to cancel a subscription at the gateway and test results would go here
                //simulate a successful cancel
                $order->updateStatus("cancelled");
                return true;
            }

            function subscribe(&$order)
            {
                //create a code for the order
                if(empty($order->code))
                    $order->code = $order->getRandomCode();

                //filter order before subscription. use with care.
                $order = apply_filters("pmpro_subscribe_order", $order, $this);

                //code to setup a recurring subscription with the gateway and test results would go here
                //simulate a successful subscription processing
                $order->status = "success";
                $order->subscription_transaction_id = $order->code;
                return true;
            }

            static function log($message)
            {
                $file = PMPRO_PAYUGATEWAY_DIR . '/logpmpropayu.log';
                $handle = fopen($file,'a+');
                fwrite($handle,$message);
                fclose($handle);
            }
        }
    }
}