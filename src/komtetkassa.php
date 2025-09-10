<?php
/*
Plugin Name: WooCommerce - КОМТЕТ Касса
Description: Фискализация платежей с помощью сервиса КОМТЕТ Касса для плагина WooCommerce
Plugin URI: http://wordpress.org/plugins/komtetkassa/
Author: Komtet
Version: 1.7.0
Author URI: http://kassa.komtet.ru/
*/

use Komtet\KassaSdk\v1\CalculationMethod;
use Komtet\KassaSdk\v1\CalculationSubject;
use Komtet\KassaSdk\v1\Check;
use Komtet\KassaSdk\v1\Client;
use Komtet\KassaSdk\v1\QueueManager;
use Komtet\KassaSdk\v1\Payment;
use Komtet\KassaSdk\v1\Position;
use Komtet\KassaSdk\v1\TaxSystem;
use Komtet\KassaSdk\v1\Vat;
use Komtet\KassaSdk\Exception\ApiValidationException;
use Komtet\KassaSdk\Exception\ClientException;
use Komtet\KassaSdk\Exception\SdkException;

final class KomtetKassa {

    public $version = '1.7.0';

    const DEFAULT_QUEUE_NAME = 'default';
    const DISCOUNT_NOT_AVAILABLE = 0;

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->define('KOMTETKASSA_ABSPATH', plugin_dir_path(__FILE__));
        $this->define('KOMTETKASSA_ABSPATH_VIEWS', plugin_dir_path(__FILE__) . 'includes/views/');
        $this->define('KOMTETKASSA_BASENAME', plugin_basename(__FILE__));

        $this->includes();
        $this->hooks();
        $this->wp_hooks();
        $this->wp_endpoints();
        $this->load_options();
        $this->init();
    }

    public function wp_hooks() {
        register_activation_hook(__FILE__, array('KomtetKassa_Install', 'activation'));
        add_action('woocommerce_order_status_' . get_option('komtetkassa_fiscalize_pre_payment_full'), array($this, 'fiscalize'));
        add_action('woocommerce_order_status_' . get_option('komtetkassa_fiscalize_full_payment'), array($this, 'fiscalize'));
    }

    public function wp_endpoints() {
        add_filter('query_vars', array($this, 'add_query_vars'), 0);
        add_action('init', array($this, 'add_endpoint'), 0);
        add_action('parse_request', array($this, 'handle_requests'), 0);
    }

    public function hooks() {
        add_action('komtet_kassa_action_success', array($this, 'action_success'));
        add_action('komtet_kassa_action_fail', array($this, 'action_fail'));
        add_action('komtet_kassa_report_create', array($this, 'report_create'), 10, 4);
        add_action('komtet_kassa_report_update', array($this, 'report_update'), 10, 3);
    }

    public function includes() {
        require_once(KOMTETKASSA_ABSPATH . 'includes/class-komtetkassa-install.php');
        require_once(KOMTETKASSA_ABSPATH . 'includes/class-komtetkassa-report.php');
        require_once(KOMTETKASSA_ABSPATH . 'includes/libs/komtet-kassa-php-sdk/autoload.php');

        if (is_admin()) {
            require_once(KOMTETKASSA_ABSPATH . 'includes/class-komtetkassa-admin.php');
            add_action('init', array('KomtetKassa_Admin', 'init'));
        }
    }

    private function define($name, $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    public function load_options() {
        $this->shop_id = get_option('komtetkassa_shop_id');
        $this->secret_key = get_option('komtetkassa_secret_key');
        $this->queue_id = get_option('komtetkassa_queue_id');
    }

    public function init() {
        do_action('before_komtetkassa_init');
        $this->client = new Client($this->shop_id, $this->secret_key);
        $this->queueManager = new QueueManager($this->client);
        $this->queueManager->registerQueue(self::DEFAULT_QUEUE_NAME, $this->queue_id);
        $this->queueManager->setDefaultQueue(self::DEFAULT_QUEUE_NAME);
        $this->report = new KomtetKassa_Report();
        do_action('komtetkassa_init');
    }

    public function taxSystems() {
        return array(
            TaxSystem::COMMON => 'ОСН',
            TaxSystem::SIMPLIFIED_IN => 'УСН доход',
            TaxSystem::SIMPLIFIED_IN_OUT => 'УСН доход - расход',
            TaxSystem::UST => 'ЕСХН',
            TaxSystem::PATENT => 'Патент'
        );
    }

    public function vatRates() {
        return array(
            'from_settings' => 'НДС из настроек',
            Vat::RATE_NO => 'Без НДС',
            Vat::RATE_0 => 'НДС 0%',
            Vat::RATE_5 => 'НДС 5%',
            Vat::RATE_7 => 'НДС 7%',
            Vat::RATE_10 => 'НДС 10%',
            Vat::RATE_20 => 'НДС 20%'
        );
    }

    # Собираем позиции для чека, если параметры соответствуют условиям, иначе прерываем процесс фискализации
    public function setPositionProps($order, $order_id, $position, $calculation_subject = CalculationSubject::PRODUCT) {
        if ($order->get_status() == get_option('komtetkassa_fiscalize_pre_payment_full')) {
            $position->setCalculationSubject(CalculationSubject::PAYMENT);
            $position->setCalculationMethod(CalculationMethod::PRE_PAYMENT_FULL);

            return $position;
        } elseif ($order->get_status() == get_option('komtetkassa_fiscalize_full_payment')) {
            if ((get_option('komtetkassa_fiscalize_pre_payment_full') == 'no_check') ||
                ($this->report->get_check_calculation_method($order_id) == CalculationMethod::PRE_PAYMENT_FULL)
            ) {
                $position->setCalculationSubject($calculation_subject);
                $position->setCalculationMethod(CalculationMethod::FULL_PAYMENT);

                return $position;
            }
        } else {
            return;
        }
    }

    public function setPaymentProps($order) {
        if (
            ($order->get_status() == get_option('komtetkassa_fiscalize_pre_payment_full')) ||
            ($order->get_status() == get_option('komtetkassa_fiscalize_full_payment') and
                (get_option('komtetkassa_fiscalize_pre_payment_full') == 'no_check'))
        ) {
            $payment = new Payment(Payment::TYPE_CARD, floatval($order->get_total()));
        } elseif (
            $order->get_status() == get_option('komtetkassa_fiscalize_full_payment')
        ) {
            $payment = new Payment(Payment::TYPE_PREPAYMENT, floatval($order->get_total()));
        }

        return $payment;
    }

    private function getVatRate($item, $vat_setting) {
        if ($vat_setting !== 'from_settings') {
            return $vat_setting;
        }

        $vatRate = Vat::RATE_NO;
        $taxes = $item->get_taxes()['total'] ?? [];

        foreach ($taxes as $rate_id => $amount) {
            if ($amount === '' || $amount === null) {
                continue;
            }

            $rate = \WC_Tax::_get_tax_rate($rate_id);
            if (!empty($rate['tax_rate'])) {
                return (string)(int)$rate['tax_rate'];
            }
        }

        return $vatRate;
    }

    # В чеках аванса и предоплаты для ставок НДС 5%, 7%, 10% и 20% необходимо использовать
    # расчетную ставку 5/105%, 7/107%, 10/110% и 20/120%. Письмо ФНС России от 03.07.2018 N ЕД-4-20/12717
    private function getVatForCheckType($order, $vat_rate) {
        if ($order->get_status() == get_option('komtetkassa_fiscalize_pre_payment_full')) {
            switch ($vat_rate) {
                case 5: return 105;
                case 7: return 107;
                case 10: return 110;
                case 20: return 120;
            }
        }
        return $vat_rate;
    }

    public function fiscalize($order_id) {
        $order = wc_get_order($order_id);

        if (!$order) {
            return;
        }

        $payment_systems = get_option("komtetkassa_payment_systems", []);
        if (!is_array($payment_systems)) {
            $payment_systems = [];
        }

        if (!in_array($order->get_payment_method(), $payment_systems)) {
            return;
        }

        $tax_system = intval(get_option('komtetkassa_tax_system'));
        $product_vat_rate = get_option('komtetkassa_product_vat_rate');
        $delivery_vat_rate = get_option('komtetkassa_delivery_vat_rate');

        $clientContact = "";

        if ($order->get_billing_email()) {
            $clientContact = $order->get_billing_email();
        } else {
            $clientContact = $order->get_billing_phone();
            $clientContact = mb_eregi_replace("[^+0-9]", '', $clientContact);
        }

        $check = new Check($order_id, $clientContact, Check::INTENT_SELL, $tax_system);

        $check->setShouldPrint(get_option('komtetkassa_should_print'));

        $check->setInternet(get_option('komtetkassa_internet'));

        if (sizeof($order->get_items()) > 0) {
            foreach ($order->get_items('line_item') as $item) {
                $vat_rate = $this->getVatRate($item, $product_vat_rate);

                $position = new Position(
                    $item->get_name(),
                    $order->get_item_total($item, true, true),
                    $item->get_quantity(),
                    $order->get_line_total($item, true, true),
                    new Vat($this->getVatForCheckType($order, $vat_rate))
                );

                $product = wc_get_product($item->get_product_id());

                if ($komtet_kassa_product_type = $product->get_attribute('komtet_kassa_product_type')) {
                    $position = self::setPositionProps($order, $order_id, $position, $calculation_subject = $komtet_kassa_product_type);
                } else {
                    $position = self::setPositionProps($order, $order_id, $position);
                }

                if ($position) {
                    $check->addPosition($position);
                } else {
                    return;
                }
            }

            // shipping
            foreach ($order->get_items('shipping') as $item) {
                $vat_rate = $this->getVatRate($item, $delivery_vat_rate);

                $deliveryPosition = new Position(
                    $item->get_name(),
                    $order->get_item_total($item, true, true),
                    $item->get_quantity(),
                    $order->get_line_total($item, true, true),
                    new Vat($this->getVatForCheckType($order, $vat_rate))
                );

                $deliveryPosition = self::setPositionProps(
                    $order,
                    $order_id,
                    $deliveryPosition,
                    $calculation_subject = CalculationSubject::SERVICE
                );

                if ($deliveryPosition) {
                    $check->addPosition($deliveryPosition);
                } else {
                    return;
                }
            }
        }

        $payment = self::setPaymentProps($order);
        $check->addPayment($payment);

        $error_message = "";
        $response = null;
        try {
            $response = $this->queueManager->putCheck($check);
        } catch (ApiValidationException $e) {
            $error_message = $e->getMessage() . " " . $e->getDescription();
        } catch (SdkException $e) {
            $error_message = $e->getMessage();
        } catch (ClientException $e) {
            $error_message = $e->getMessage();
        }
        do_action('komtet_kassa_report_create', $order_id, $check->asArray(), $response, $error_message);
    }

    public function add_query_vars($vars) {
        $vars[] = 'komtet-kassa';
        return $vars;
    }

    public static function add_endpoint() {
        add_rewrite_endpoint('komtet-kassa', EP_ALL);
    }

    public function handle_requests() {
        global $wp;

        if (empty($wp->query_vars['komtet-kassa'])) {
            return;
        }

        $komtet_kassa_action = strtolower(wc_clean($wp->query_vars['komtet-kassa']));
        do_action('komtet_kassa_action_' . $komtet_kassa_action);
        die(-1);
    }

    public function action_success() {
        $this->handle_action('success');
    }

    public function action_fail() {
        $this->handle_action('fail');
    }

    public function handle_action($action) {
        global $wp;

        if (!array_key_exists('HTTP_X_HMAC_SIGNATURE', $_SERVER)) {
            status_header(401);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            status_header(405);
            header('Allow: POST');
            exit();
        }

        if (empty($this->secret_key)) {
            error_log('Unable to handle request: komtetkassa_secret_key is not defined');
            status_header(500);
        }

        $scheme = array_key_exists('HTTPS', $_SERVER) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
        $url = sprintf('%s://%s%s', $scheme, $_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']);
        $data = file_get_contents('php://input');

        $signature = hash_hmac('md5', $_SERVER['REQUEST_METHOD'] . $url . $data, $this->secret_key);

        if ($signature != $this->request->server['HTTP_X_HMAC_SIGNATURE']) {
            status_header(403);
            exit();
        }

        $data = json_decode($data, true);

        foreach (array('external_id', 'state') as $key) {
            if (!array_key_exists($key, $data)) {
                status_header(422);
                header('Content-Type: text/plain');
                echo $key . " is required\n";
                exit();
            }
        }
        do_action('komtet_kassa_report_update', intval($data['external_id']), $data['state'], $data);
    }

    public function report_create($order_id, $request_check_data, $response_data, $error = "") {
        $this->report->create($order_id, $request_check_data, $response_data, $error);
    }

    public function report_update($order_id, $state, $report_data) {
        $this->report->update($order_id, $state, $report_data);
    }
}

function Komtet_Kassa() {
    return KomtetKassa::instance();
}

$GLOBALS['komtetkassa'] = Komtet_Kassa();
