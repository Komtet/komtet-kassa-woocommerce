<?php

if (!defined('ABSPATH')) {
    exit;
}

?>
<h2>Настройки</h2>
<div>
    <form method="post" name="settings_form">
        <table class="form-table">
            <tr>
                <th>
                    <label>ID магазина:</label>
                </th>
                <td>
                    <input type="text" name="komtetkassa_shop_id" value="<?php echo get_option("komtetkassa_shop_id") ?>" />
                </td>
            </tr>
            <tr>
                <th>
                    <label>Секретный ключ магазина:</label>
                </th>
                <td>
                    <input type="text" name="komtetkassa_secret_key" value="<?php echo get_option("komtetkassa_secret_key") ?>" />
                </td>
            </tr>
            <tr>
                <th>
                    <label>Печатать чек:</label>
                </th>
                <td>
                    <input type="checkbox" name="komtetkassa_should_print" value="1" <?php echo get_option("komtetkassa_should_print") == "1" ? "checked" : "" ?> />
                </td>
            </tr>
            <tr>
                <th>
                    <label>ID очереди:</label>
                </th>
                <td>
                    <input type="text" name="komtetkassa_queue_id" value="<?php echo get_option("komtetkassa_queue_id") ?>" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="tax_system">Система налогообложения:</label>
                </th>
                <td>
                    <select name="komtetkassa_tax_system" id="tax_system">
                        <?php foreach (Komtet_Kassa()->taxSystems() as $val => $name) : ?>
                            <option value="<?php echo $val ?>" <?php echo get_option("komtetkassa_tax_system") == $val ? "selected" : ""  ?>><?php echo $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="komtetkassa_product_vat_rate">Ставка НДС для товаров:</label>
                </th>
                <td>
                    <select name="komtetkassa_product_vat_rate" id="vat_rate">
                        <?php foreach (Komtet_Kassa()->vatRates() as $val => $name) : ?>
                            <option value="<?php echo $val ?>" <?php echo get_option("komtetkassa_product_vat_rate") == $val ? "selected" : ""  ?>><?php echo $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="komtetkassa_delivery_vat_rate">Ставка НДС для доставки:</label>
                </th>
                <td>
                    <select name="komtetkassa_delivery_vat_rate" id="vat_rate">
                        <?php foreach (Komtet_Kassa()->vatRates() as $val => $name) : ?>
                            <option value="<?php echo $val ?>" <?php echo get_option("komtetkassa_delivery_vat_rate") == $val ? "selected" : ""  ?>><?php echo $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="komtet_kassa_payment_systems">Платёжные системы для которых будет происходить фискализация:</label>
                </th>
                <td>
                    <select id="payment_systems" name="komtet_kassa_payment_systems[]" multiple>
                        <?php
                        $payment_systems = WC()->payment_gateways->get_available_payment_gateways();
                        foreach ($payment_systems as $payment_system_code => $payment_system_desc) :
                            // var_dump(selected(in_array($payment_system_code, (get_option("komtet_kassa_payment_systems"))), false)); die();
                            // $payment_system_code = [];
                            // $list[$payment_system_code] = $payment_system_desc->get_title();
                            // var_dump($payment_system_codes); die();
                            // $payment_system_code = $payment_system_code;
                        ?>
                            <option value="<?php echo esc_attr($payment_system_code) ?>" <?php echo selected(in_array($payment_system_code, (get_option("komtet_kassa_payment_systems"))), true) ?>>
                                <?php echo esc_html($payment_system_desc->get_title()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="pre_payment_full">Статус заказа при котором будет фискализирован чек полной предоплаты(pre_payment_full):</label>
                </th>
                <td>
                    <select id="order_status" name="komtetkassa_fiscalize_pre_payment_full">
                        <?php
                        $statuses = wc_get_order_statuses();
                        $statuses['no_check'] = 'Не выдавать';
                        foreach ($statuses as $status => $status_name) :
                            $status = str_replace('wc-', '', $status);
                        ?>
                            <option value="<?php echo esc_attr($status) ?>" <?php echo selected($status, get_option("komtetkassa_fiscalize_pre_payment_full"), false) ?>><?php echo esc_html($status_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="full_payment">Статус заказа при котором будет фискализирован чек полного расчёта(full_payment):</label>
                </th>
                <td>
                    <select id="order_status" name="komtetkassa_fiscalize_full_payment">
                        <?php
                        $statuses = wc_get_order_statuses();
                        foreach ($statuses as $status => $status_name) :
                            $status = str_replace('wc-', '', $status);
                        ?>
                            <option value="<?php echo esc_attr($status) ?>" <?php echo selected($status, get_option("komtetkassa_fiscalize_full_payment"), false) ?>><?php echo esc_html($status_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input class="button button-primary" type="submit" value="Cохранить" name="submit">
        </p>
    </form>
</div>