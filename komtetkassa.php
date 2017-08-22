<?php
/*
Plugin Name: KOMTET Kassa
Plugin URI: http://wordpress.org/plugins/komtetkassa/
Author: Potalius
Version: 1.0.0
Author URI: http://kassa.komtet.ru/
*/

use Komtet\KassaSdk\Client;
use Komtet\KassaSdk\QueueManager;
use Komtet\KassaSdk\Check;
use Komtet\KassaSdk\Payment;
use Komtet\KassaSdk\Position;
use Komtet\KassaSdk\Vat;
use Komtet\KassaSdk\Exception\ClientException;
use Komtet\KassaSdk\Exception\SdkException;

final class KomtetKassa {

    const DEFAULT_QUEUE_NAME = 'default';
    const DISCOUNT_NOT_AVAILABLE = 0;

    private static $_instance = null;

    public static function instance() {
		if (is_null(self::$_instance) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    public function __construct()
    {
        $this->define('KOMTETKASSA_ABSPATH', plugin_dir_path( __FILE__));
        $this->define('KOMTETKASSA_ABSPATH_VIEWS', plugin_dir_path( __FILE__) . 'includes/views/');
    
        $this->includes();
        $this->wp_hooks();
    }

    public function wp_hooks()
    {
        register_activation_hook( __FILE__, array('KomtetKassa_Install', 'activation'));
        register_activation_hook( __FILE__, array('KomtetKassa_Install', 'deactivation'));

        add_action('woocommerce_order_status_completed', array($this, 'fiscalize'));
    }

    public function includes()
    {
        require_once(KOMTETKASSA_ABSPATH . 'includes/class-komtetkassa-install.php');
        require_once(KOMTETKASSA_ABSPATH . 'includes/libs/komtet-kassa-php-sdk/autoload.php');
        
        if (is_admin()) {
            require_once(KOMTETKASSA_ABSPATH . 'includes/class-komtetkassa-admin.php');
            add_action('init', array( 'KomtetKassa_Admin', 'init'));
        }
    }

    private function define($name, $value)
    {
		if (!defined( $name )) {
			define( $name, $value );
		}
    }

    public function load_options() {
        $this->server_url = get_option('komtetkassa_server_url');
        $this->shop_id = get_option('komtetkassa_shop_id');
        $this->secret_key = get_option('komtetkassa_secret_key');
        $this->queue_id = get_option('komtetkassa_queue_id');
    }
    
    public function init()
    {
        do_action('before_komtetkassa_init');
        $this->load_options();
        $this->client = new Client($this->shop_id, $this->secret_key);
        $this->client->setHost($this->server_url);
        $this->queueManager = new QueueManager($this->client);
        $this->queueManager->registerQueue(self::DEFAULT_QUEUE_NAME, $this->queue_id);
		$this->queueManager->setDefaultQueue(self::DEFAULT_QUEUE_NAME);
        do_action('komtetkassa_init');
    }

    public function taxSystems() {
		return array(
			Check::TS_COMMON => 'ОСН',
			Check::TS_SIMPLIFIED_IN => 'УСН доход',
			Check::TS_SIMPLIFIED_IN_OUT => 'УСН доход - расход',
			Check::TS_UTOII => 'ЕНВД',
			Check::TS_UST => 'ЕСН',
			Check::TS_PATENT => 'Патент'
		);
	}

    public function fiscalize($order_id)
    {
        $this->init();

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $tax_system = intval(get_option('komtetkassa_tax_system'));

        $check = new Check($order_id, $order->get_billing_email(), Check::INTENT_SELL, $tax_system);

        $check->setShouldPrint(get_option('komtetkassa_should_print'));

        if (sizeof($order->get_items()) > 0 ) {
            // products
            foreach ($order->get_items('line_item') as $item) {
                $check->addPosition(new Position(
                     $item->get_name(),
                     $order->get_item_total($item, false, true),
                     $item->get_quantity(),
                     $item->get_total(),
                     $order->get_item_subtotal($item, false, true) - $order->get_item_total($item, false, true),
                     new Vat(0)
                ));
            }
            // shipping
            foreach ($order->get_items('shipping') as $item) {
                $check->addPosition(new Position(
                    $item->get_name(),
                    $order->get_item_total($item, false, true),
                    $item->get_quantity(),
                    floatval($item->get_total()),
                    self::DISCOUNT_NOT_AVAILABLE,
                    new Vat(0)
               ));
            }
        }

		$check->addPayment(Payment::createCard(floatval($order->get_total())));

        try {
            $this->queueManager->putCheck($check);
        } catch (SdkException $e) {
            die($e->getMessage());
        }
    }
}

function Komtet_Kassa() {
	return KomtetKassa::instance();
}

$GLOBALS['komtetkassa'] = Komtet_Kassa();
