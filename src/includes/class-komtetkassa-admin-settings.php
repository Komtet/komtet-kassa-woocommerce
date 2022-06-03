<?php

final class KomtetKassa_AdminSettings
{

    private static $options = array(
        'komtetkassa_shop_id' => 'string',
        'komtetkassa_secret_key' => 'string',
        'komtetkassa_should_print' => 'bool',
        'komtetkassa_queue_id' => 'string',
        'komtetkassa_tax_system' => 'integer',
        'komtetkassa_product_vat_rate' => 'string',
        'komtetkassa_delivery_vat_rate' => 'string',
        'komtet_kassa_payment_systems' => 'array',
        'komtetkassa_fiscalize_pre_payment_full' => 'string',
        'komtetkassa_fiscalize_full_payment' => 'string'
    );

    public static function out()
    {
        if (!empty($_POST)) {
            self::save();
        }
        include(KOMTETKASSA_ABSPATH_VIEWS . 'html-admin-settings.php');
    }

    public static function save()
    {

        foreach (self::$options as $key => $type) {
            $value = filter_input(INPUT_POST, $key);

            if ($type == 'string') {
                update_option($key, $value);
            } else if ($type == 'bool') {
                update_option($key, $value === "1" ? "1" : "0");
            } else if ($type == 'integer') {
                update_option($key, intval($value));
            } else if ($type == 'array') {
                // var_dump($_POST[$key]); die();
                // update_option($key, $value);
                if (isset($_POST[$key]) && array_key_exists($key, $_POST)) {
                    // $new_arr = array();
                    // foreach ($_POST[$key] as $val) {
                    //     $new_arr[] = sanitize_text_field(
                    //         strip_tags(stripslashes($val))
                    //     );
                    // }
                    update_option($key, $_POST[$key]);
                }
            }
        }
    }
}
