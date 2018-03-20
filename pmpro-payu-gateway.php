<?php
/*
Plugin Name: Pmpro payU Gateway
Description: payU gateway for Paid Memberships Pro.
Version: 1.0.0
Author: Saul Morales Pacheco
Author URI: https://saulmoralespa.com
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: pmpro-payu-gateway
Domain Path: /languages/
*/

define("PMPRO_PAYUGATEWAY_DIR", dirname(__FILE__));
define('PMPRO_PAYUGATEWAY_FILE',__FILE__);

//load payment gateway class
require_once(PMPRO_PAYUGATEWAY_DIR . "/classes/class.pmprogateway_payu.php");