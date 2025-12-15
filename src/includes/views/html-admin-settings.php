<?php

if (!defined('ABSPATH')) {
    exit;
}

?>
<h2>Настройки</h2>
<div style="width: 50%; float: left;">
    <form method="post" name="settings_form">
        <table class="form-table">
            <tr>
                <th>
                    <label>ID магазина:</label>
                </th>
                <td>
                    <input type="text" name="komtetkassa_shop_id" value="<?php echo get_option("komtetkassa_shop_id") ?>" />
                    <p class="description">Скопируйте из раздела "Магазины" личного кабинета Комтет Кассы</p>
                </td>
            </tr>
            <tr>
                <th>
                    <label>Секретный ключ магазина:</label>
                </th>
                <td>
                    <input type="text" name="komtetkassa_secret_key" value="<?php echo get_option("komtetkassa_secret_key") ?>" />
                    <p class="description">Скопируйте из раздела "Магазины" личного кабинета Комтет Кассы</p>
                </td>
            </tr>
            <tr>
                <th>
                    <label>ID очереди:</label>
                </th>
                <td>
                    <input type="text" name="komtetkassa_queue_id" value="<?php echo get_option("komtetkassa_queue_id") ?>" />
                    <p class="description">Скопируйте из раздела "Кассы" личного кабинета Комтет Кассы</p>
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
                    <label>Признак расчета в сети «Интернет»:</label>
                </th>
                <td>
                    <input type="checkbox" name="komtetkassa_internet" value="1" <?php echo get_option("komtetkassa_internet") == "1" ? "checked" : "" ?> />
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
                    <label for="product_vat_rate">Ставка НДС для товаров:</label>
                </th>
                <td>
                    <select name="komtetkassa_product_vat_rate" id="product_vat_rate">
                        <?php foreach (Komtet_Kassa()->vatRates() as $val => $name) : ?>
                            <option value="<?php echo strval($val) ?>" <?php echo get_option("komtetkassa_product_vat_rate") == strval($val) ? "selected" : "" ?>>
                                <?php echo $name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="delivery_vat_rate">Ставка НДС для доставки:</label>
                </th>
                <td>
                    <select name="komtetkassa_delivery_vat_rate" id="delivery_vat_rate">
                        <?php foreach (Komtet_Kassa()->vatRates() as $val => $name) : ?>
                            <option value="<?php echo strval($val) ?>"<?php echo get_option("komtetkassa_delivery_vat_rate") == strval($val) ? "selected" : "" ?>>
                                <?php echo $name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="komtet_kassapayment_systems">Платёжные системы для которых будет происходить фискализация:</label>
                </th>
                <td>
                    <select id="payment_systems" name="komtetkassa_payment_systems[]" multiple>
                        <?php
                            $payment_systems = WC()->payment_gateways->get_available_payment_gateways();
                            foreach ($payment_systems as $payment_system_code => $payment_system_desc) :
                        ?>
                            <option value="<?php echo esc_attr($payment_system_code) ?>"
                            <?php echo selected(in_array($payment_system_code, get_option("komtetkassa_payment_systems") ? get_option("komtetkassa_payment_systems") : []), true) ?>>
                                <?php echo esc_html($payment_system_desc->get_title()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="pre_payment_full">Статус заказа при котором будет фискализирован чек 100% предоплаты:</label>
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
                    <label for="full_payment">Статус заказа при котором будет фискализирован чек полного расчёта:</label>
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
        <p class=" submit">
            <input class="button button-primary" type="submit" value="Cохранить" name="submit">
        </p>
    </form>
</div>

<div style="width: 50%; float: right;">
    <table>
        <tr>
            <th>
                <details open>
                    <summary style="max-width: 457px; text-align:center; font-size: 16px; cursor: pointer">Инструкция по изменению признака предмета расчёта:</summary>
                    <div style="max-width: 457px; text-align:left;">
                        <p>
                            По 54-ФЗ, при продаже в кассовый чек должна попадать информация о признаке предмета расчета.
                            По умолчанию, из Wordpress в Комтет Кассу этот признак передается как "Товар" для всех товаров из каталога.
                        </p>

                        <p>
                            Если продаёте в своём онлайн-магазине не товары, а, например, услуги и.т.д., то вы можете указать это в настройках
                            товара в Wordpress, чтобы в чек передавалась правильная информация. Это настраивается при помощи атрибутов.
                        </p>
                        <p>Порядок добавления атрибута к товару: </p>

                        <ol>
                            <li>В панеле управления Wordpress перейдите в <b>Товары</b>(Products)--><b>Все товары</b>(All Products);</li>
                            <li>Выберите необходимый товар --> Нажмите "Изменить"</li>
                            <li>В разделе "Данные товара" выберите "Артибуты" --> "Добавить";</li>
                            <li>В качестве <b>имени аттрибута</b> задайте komtet_kassa_product_type</li>
                            <li>В качестве <b>значения аттрибута </b> задайте одно значение из списка "Списка поддерживаемых признаков расчёта" ниже</li>
                            <li>Нажмите <b>Сохранить</b>(Save);</li>
                        </ol>

                        <p style="max-width: 457px; font-size: 16px;">Список поддерживаемых признаков расчёта:</p>
                        <table style="border-collapse: collapse;">
                            <tr style="border-bottom: 1px solid #000;">
                                <th style="text-align: left; font-size: 16px; padding-bottom: 15px;">Значение</th>
                                <td style="text-align: left; font-size: 16px; padding-bottom: 15px;">Описание</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #000;">
                                <th style="padding: 15px 180px 15px 0px;">product</th>
                                <td style="padding: 15px 0px 15px 0px;" ">Товар, за исключением подакцизного товара</td>
                            </tr>
                            <tr style=" border-bottom: 1px solid #000;">
                                <th style="padding: 15px 180px 15px 0px;">product_practical</th>
                                <td style="padding: 15px 0px 15px 0px;" ">Подакцизный товар</td>
                            </tr>
                            <tr style=" border-bottom: 1px solid #000;">
                                <th style="padding: 15px 180px 15px 0px;">work</th>
                                <td style="padding: 15px 0px 15px 0px;" ">Работа</td>
                            </tr>
                            <tr style=" border-bottom: 1px solid #000;">
                                <th style="padding: 15px 180px 15px 0px;">service</th>
                                <td style="padding: 15px 0px 15px 0px;" ">Услуга</td>
                            </tr>
                            <tr style=" border-bottom: 1px solid #000;">
                                <th style="padding: 15px 180px 15px 0px;">lottery_bet</th>
                                <td style="padding: 15px 0px 15px 0px;" ">
                                    Прием денежных средств при реализации лотерейных билетов, электронных лотерейных билетов, приеме лотерейных ставок при осуществлении деятельности по проведению лотерей
                                </td>
                            </tr>
                            <tr style=" border-bottom: 1px solid #000;">
                                <th style="padding: 15px 180px 15px 0px;">lottery_win</th>
                                <td style="padding: 15px 0px 15px 0px;" ">
                                    Выплате денежных средств в виде выигрыша при осуществлении деятельности по проведению лотерей
                                </td>
                            </tr>
                            <tr style=" border-bottom: 1px solid #000;">
                                <th style="padding: 15px 180px 15px 0px;">rid</th>
                                <td style="padding: 15px 0px 15px 0px;" ">
                                    Предоставление прав на использование результатов интеллектуальной деятельности или средств индивидуализации «ПРЕДОСТАВЛЕНИЕ РИД» или «РИД»
                                </td>
                            </tr>
                            <tr style=" border-bottom: 1px solid #000;">
                                <th style="padding: 15px 180px 15px 0px;">payment</th>
                                <td style="padding: 15px 0px 15px 0px;" ">
                                    Об авансе, задатке, предоплате, кредите, взносе в счет оплаты, пени, штрафе, вознаграждении, бонусе и ином аналогичном предмете расчета
                                </td>
                            </tr>
                            <tr style=" border-bottom: 1px solid #000;">
                                <th style="padding: 15px 180px 15px 0px;">commission</th>
                                <td style="padding: 15px 0px 15px 0px;" ">
                                    Вознаграждении пользователя, являющегося платежным агентом (субагентом), банковским платежным агентом (субагентом), комиссионером, поверенным или иным агентом
                                </td>
                            </tr>
                            <tr style=" border-bottom: 1px solid #000;">
                                <th style="padding: 15px 180px 15px 0px;">property_right</th>
                                <td style="padding: 15px 0px 15px 0px;" ">Передача имущественного права</td>
                            </tr>
                            <tr style=" border-bottom: 1px solid #000;">
                                <th style="padding: 15px 180px 15px 0px;">non_operating</th>
                                <td style="padding: 15px 0px 15px 0px;" ">Внереализационный доход</td>
                            </tr>
                            <tr style=" border-bottom: 1px solid #000;">
                                <th style="padding: 15px 180px 15px 0px;">insurance</th>
                                <td style="padding: 15px 0px 15px 0px;" ">Страховые взносы</td>
                            </tr>
                            <tr style=" border-bottom: 1px solid #000;">
                                <th style="padding: 15px 180px 15px 0px;">sales_tax</th>
                                <td style="padding: 15px 0px 15px 0px;" ">Торговый сбор</td>
                            </tr>
                            <tr style=" border-bottom: 1px solid #000;">
                                <th style="padding: 15px 180px 15px 0px;">resort_fee</th>
                                <td style="padding: 15px 0px 15px 0px;" ">Курортный сбор</td>
                            </tr>
                        </table>
                    </div>
                </details>
            </th>
        </tr>
    </table>
</div>