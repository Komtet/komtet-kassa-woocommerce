<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<h2>Настройки</h2>
<div>
	<form method="post" name="settings_form">
		<table class="form-table">
        <tr>
            <th>
                <label>API Url:</label>
            </th>
            <td>
                <input type="text" name="komtetkassa_server_url" value="<?php echo get_option("komtetkassa_server_url") ?>" />
            </td>
        </tr>
        <tr>
            <th>
                <label>Идентификатор магазина:</label>
            </th>
            <td>
                <input type="text" name="komtetkassa_shop_id" value="<?php echo get_option("komtetkassa_shop_id") ?>" />
            </td>
        </tr>
        <tr>
            <th>
                <label>Секретный ключ:</label>
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
                <label>Идентификатор очереди:</label>
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
                    <?php foreach(Komtet_Kassa()->taxSystems() as $val => $name): ?>
                    <option value="<?php echo $val ?>" <?php echo get_option("komtetkassa_tax_system") == $val ? "selected" : ""  ?>><?php echo $name ?></option>
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
