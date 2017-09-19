# КОМТЕТ Касса для WooCommerce

Данное решение позволяет подключить Ваш интернет-магазин к облачному сервису КОМТЕТ Касса с целью соответствия требованиям 54-ФЗ для регистрации расчетов с использованием электронного средства платежа в сети Интернет.

Возможности плагина

  - автоматическая фискализация платежей;
  - фискализация в ручном режиме.

Описание работы

Плагин реагирует на событие статуса заказа, которое возникает, когда клиент совершает оплату через один из подключенных плагинов приема платежей, например, PayPal или Robokassa.

По событию «Заказ оплачен» статус заказа изменяется на «Обработка» после чего информации о заказе формируется и отправляется запрос на фискализацию платежа в систему КОМТЕТ Касса. Вы сможете увидеть список всех запросов на фискализацию в личном кабинете на сайте КОМТЕТ Касса в разделе «История».

Как только данные по заказу появляются в системе КОМТЕТ Касса, формируется чек, который записывается на фискальный накопитель кассового аппарата и он же отправляется в ОФД (Оператор Фискальных Данных). Если указано в настройках, аппарат может распечатать бланк чека.

Важно! 54-ФЗ обязует выдать электронный чек клиенту, для того чтобы электронный чек был выслан клиенту на электронную почту необходимо сделать обязательным поле email на форме оформления заказа.

Так же статус заказа «Обработка» возникает, когда в разделе администратора магазина для заказа вы устанвливате статус «Обработка» и нажимаете кнопкк «Обновить». Это позволит провести фискализацию вручную.

Таким образом вы можете использовать разделе администратора магазина для печати чека по любому заказу.

### Установка
Для установки плагина нужно выполнить слудующие действия:
1. Войти в админ панель WordPress и открыть вкладку Плагины — Добавить новый (Plugins tab — Add New):
2. Вверху нажать кнопку Загрузить плагин (Upload plugin).
3. Кликнуть Обзор (Browse) и выбрать .zip архив плагина. Когда вы нашли необходимый архив плагина, нужно нажать кнопку Открыть (Open), а затем кликнуть Установить сейчас (Install Now).
4. Когда плагин будет загружен, нужно активировать его, кликнув по ссылке Активировать плагин (Activate Plugin):

Также можно установить плагин через админ панель, используя форму поиска плагинов.
1. Откройте вкладку Плагины (Plugins), кликните Добавить новый (Add New), в строке поиска введите название плагина, КОМТЕТ Касса, и нажмите кнопку Enter на клавиатуре:
2. Вы можете видеть плагин КОМТЕТ Касса в списке результатов поиска. Чтобы установить данный плагин, нажмите кнопку Установить сейчас (Install now).
3. Когда плагин будет установлен, кликните ссылку Активировать плагин (Activate Plugin).

### Настройка плагина

Прежде чем приступить к настройке плагина вам потребуется зарегистрировать в [личном кабинете на сайте КОМТЕТ Касса](https://kassa.komtet.ru/signup).

Для настройки плагин нужно открыть вкладку КОМТЕТ Касса - Настройки

В настройках плагина необходимо указать:
1. URL по которому система КОМТЕТ Касса принимает данные для фискализации. Актуальный адрес указан по умолчанию.
2. Идентификатор магазина. В личном кабинете на сайте КОМТЕТ Касса зайдите в меню «Магазины» (слева), далее выберете нужный магазин и зайдите в его настройки, там вы и найдете необходимое значение (ShopId).
3. Секретный ключ магазина. Аналогично предыдущему (Secret).
4. Включить или отключить печать бумажного чека.
5. Указать систему налогообложения вашей компании. Данные о системе налогообложения будут использованы при формировании чека.
6. Выбрать статус заказа, при при котором формируется и отправляется запрос на фискализацию платежа в систему КОМТЕТ Касса.
7. Скопировать в настройках плагина данные из полей Success url (http://your_domain/?komtet-kassa=success)  и Failure url (http://your_domain/?komtet-kassa=fail), и записать их в соответствующие поля в настройках магазина в личном кабинете КОМТЕТ Касса

### Будет сделано

 - Возврат чека
 - Локализация
 - Логирование

License
----

MIT

