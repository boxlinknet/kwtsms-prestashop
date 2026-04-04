<?php
/**
 * kwtSMS - Module Installer
 *
 * Handles installation, uninstallation, database schema, configuration defaults,
 * admin tab registration, and seed data for templates and integrations.
 *
 * Related files:
 * - sql/install.php: CREATE TABLE statements
 * - sql/uninstall.php: DROP TABLE statements
 * - kwtsms.php: calls install()/uninstall() from Module::install()/uninstall()
 *
 * @author    kwtSMS <support@kwtsms.com>
 * @copyright kwtSMS
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 */

class KwtsmsInstaller
{
    /** @var Module */
    private $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * Full installation: DB tables, config, admin tab, seed data.
     *
     * @return bool
     */
    public function install()
    {
        return $this->installDatabase()
            && $this->installConfiguration()
            && $this->installTab()
            && $this->seedTemplates()
            && $this->seedIntegrations();
    }

    /**
     * Full uninstallation: drop tables, remove config, remove tab.
     *
     * @return bool
     */
    public function uninstall()
    {
        return $this->uninstallDatabase()
            && $this->uninstallConfiguration()
            && $this->uninstallTab();
    }

    /**
     * Register all hooks used by the module.
     *
     * @return bool
     */
    public function registerHooks()
    {
        return $this->module->registerHook('actionValidateOrder')
            && $this->module->registerHook('actionOrderStatusPostUpdate')
            && $this->module->registerHook('actionCustomerAccountAdd')
            && $this->module->registerHook('actionPaymentConfirmation')
            && $this->module->registerHook('actionUpdateQuantity')
            && $this->module->registerHook('actionCronJob');
    }

    /**
     * Execute all CREATE TABLE statements from sql/install.php.
     *
     * @return bool
     */
    private function installDatabase()
    {
        $sql = array();
        include dirname(__FILE__) . '/../sql/install.php';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Execute all DROP TABLE statements from sql/uninstall.php.
     *
     * @return bool
     */
    private function uninstallDatabase()
    {
        $sql = array();
        include dirname(__FILE__) . '/../sql/uninstall.php';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Set all default KWTSMS_* configuration values.
     *
     * @return bool
     */
    private function installConfiguration()
    {
        $cronToken = bin2hex(random_bytes(16));

        return Configuration::updateValue('KWTSMS_USERNAME', '')
            && Configuration::updateValue('KWTSMS_PASSWORD', '')
            && Configuration::updateValue('KWTSMS_SENDER_ID', 'KWT-SMS')
            && Configuration::updateValue('KWTSMS_DEFAULT_COUNTRY_CODE', '965')
            && Configuration::updateValue('KWTSMS_GATEWAY_CONNECTED', false)
            && Configuration::updateValue('KWTSMS_GATEWAY_ENABLED', false)
            && Configuration::updateValue('KWTSMS_TEST_MODE', true)
            && Configuration::updateValue('KWTSMS_DEBUG_MODE', false)
            && Configuration::updateValue('KWTSMS_ADMIN_PHONES', '')
            && Configuration::updateValue('KWTSMS_CRON_TOKEN', $cronToken);
    }

    /**
     * Delete all KWTSMS_* configuration values.
     *
     * @return bool
     */
    private function uninstallConfiguration()
    {
        $keys = array(
            'KWTSMS_USERNAME',
            'KWTSMS_PASSWORD',
            'KWTSMS_SENDER_ID',
            'KWTSMS_DEFAULT_COUNTRY_CODE',
            'KWTSMS_GATEWAY_CONNECTED',
            'KWTSMS_GATEWAY_ENABLED',
            'KWTSMS_TEST_MODE',
            'KWTSMS_DEBUG_MODE',
            'KWTSMS_ADMIN_PHONES',
            'KWTSMS_CRON_TOKEN',
        );

        $result = true;
        foreach ($keys as $key) {
            $result = $result && Configuration::deleteByName($key);
        }

        return $result;
    }

    /**
     * Install admin tab under Modules menu.
     *
     * @return bool
     */
    private function installTab()
    {
        $tabId = (int) Tab::getIdFromClassName('AdminKwtsms');
        if ($tabId) {
            return true;
        }

        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminKwtsms';
        $tab->module = $this->module->name;

        $parentClass = 'AdminModulesSf';
        $parentId = (int) Tab::getIdFromClassName($parentClass);
        if (!$parentId) {
            $parentId = (int) Tab::getIdFromClassName('AdminModules');
        }
        $tab->id_parent = $parentId;

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'kwtSMS';
        }

        return $tab->save();
    }

    /**
     * Uninstall admin tab.
     *
     * @return bool
     */
    private function uninstallTab()
    {
        $tabId = (int) Tab::getIdFromClassName('AdminKwtsms');
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }

    /**
     * Seed default SMS templates.
     *
     * @return bool
     */
    private function seedTemplates()
    {
        $now = date('Y-m-d H:i:s');
        $templates = array(
            array(
                'template_key' => 'order_placed_customer',
                'recipient_type' => 'customer',
                'content_en' => 'Hi {customer_name}, your order {order_ref} for {order_total} {currency} has been placed. Thank you! - {shop_name}',
                'content_ar' => '{customer_name} :مرحبا، تم استلام طلبك {order_ref} بقيمة {order_total} {currency}. شكرا لك! - {shop_name}',
            ),
            array(
                'template_key' => 'order_placed_admin',
                'recipient_type' => 'admin',
                'content_en' => 'New order {order_ref} for {order_total} {currency} from {customer_name}. - {shop_name}',
                'content_ar' => 'طلب جديد {order_ref} بقيمة {order_total} {currency} من {customer_name}. - {shop_name}',
            ),
            array(
                'template_key' => 'order_status_changed_customer',
                'recipient_type' => 'customer',
                'content_en' => 'Hi {customer_name}, your order {order_ref} status is now: {order_status}. - {shop_name}',
                'content_ar' => '{customer_name} :مرحبا، حالة طلبك {order_ref} الان: {order_status}. - {shop_name}',
            ),
            array(
                'template_key' => 'new_customer_customer',
                'recipient_type' => 'customer',
                'content_en' => 'Welcome {customer_name}! Your account has been created. - {shop_name}',
                'content_ar' => '{customer_name} :مرحبا! تم انشاء حسابك بنجاح. - {shop_name}',
            ),
            array(
                'template_key' => 'new_customer_admin',
                'recipient_type' => 'admin',
                'content_en' => 'New customer registered: {customer_name} ({customer_email}). - {shop_name}',
                'content_ar' => 'عميل جديد: {customer_name} ({customer_email}). - {shop_name}',
            ),
            array(
                'template_key' => 'payment_confirmed_customer',
                'recipient_type' => 'customer',
                'content_en' => 'Hi {customer_name}, payment confirmed for order {order_ref} ({order_total} {currency}). Thank you! - {shop_name}',
                'content_ar' => '{customer_name} :مرحبا، تم تاكيد الدفع لطلبك {order_ref} ({order_total} {currency}). شكرا لك! - {shop_name}',
            ),
            array(
                'template_key' => 'payment_confirmed_admin',
                'recipient_type' => 'admin',
                'content_en' => 'Payment confirmed for order {order_ref} ({order_total} {currency}) from {customer_name}. - {shop_name}',
                'content_ar' => 'تم تاكيد الدفع لطلب {order_ref} ({order_total} {currency}) من {customer_name}. - {shop_name}',
            ),
            array(
                'template_key' => 'low_stock_admin',
                'recipient_type' => 'admin',
                'content_en' => 'Low stock alert: {product_name} (ref: {product_ref}) has {stock_quantity} units left. - {shop_name}',
                'content_ar' => 'تنبيه مخزون منخفض: {product_name} (المرجع: {product_ref}) متبقي {stock_quantity} وحدة. - {shop_name}',
            ),
        );

        foreach ($templates as $tpl) {
            $exists = Db::getInstance()->getValue(
                'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'kwtsms_template` WHERE `template_key` = "' . pSQL($tpl['template_key']) . '"'
            );
            if ($exists) {
                continue;
            }

            $result = Db::getInstance()->insert('kwtsms_template', array(
                'template_key' => pSQL($tpl['template_key']),
                'recipient_type' => pSQL($tpl['recipient_type']),
                'content_en' => pSQL($tpl['content_en']),
                'content_ar' => pSQL($tpl['content_ar']),
                'date_add' => $now,
                'date_upd' => $now,
            ));

            if (!$result) {
                return false;
            }
        }

        return true;
    }

    /**
     * Seed default integrations (all disabled).
     *
     * @return bool
     */
    private function seedIntegrations()
    {
        $now = date('Y-m-d H:i:s');

        $integrations = array(
            array(
                'integration_key' => 'order_placed',
                'recipient_type' => 'both',
                'customer_template' => 'order_placed_customer',
                'admin_template' => 'order_placed_admin',
                'settings' => null,
            ),
            array(
                'integration_key' => 'order_status_changed',
                'recipient_type' => 'customer',
                'customer_template' => 'order_status_changed_customer',
                'admin_template' => null,
                'settings' => null,
            ),
            array(
                'integration_key' => 'new_customer',
                'recipient_type' => 'both',
                'customer_template' => 'new_customer_customer',
                'admin_template' => 'new_customer_admin',
                'settings' => null,
            ),
            array(
                'integration_key' => 'payment_confirmed',
                'recipient_type' => 'both',
                'customer_template' => 'payment_confirmed_customer',
                'admin_template' => 'payment_confirmed_admin',
                'settings' => null,
            ),
            array(
                'integration_key' => 'low_stock',
                'recipient_type' => 'admin',
                'customer_template' => null,
                'admin_template' => 'low_stock_admin',
                'settings' => json_encode(array('threshold' => 5)),
            ),
        );

        foreach ($integrations as $integ) {
            $exists = Db::getInstance()->getValue(
                'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'kwtsms_integration` WHERE `integration_key` = "' . pSQL($integ['integration_key']) . '"'
            );
            if ($exists) {
                continue;
            }

            $customerTemplateId = 0;
            if ($integ['customer_template']) {
                $customerTemplateId = (int) Db::getInstance()->getValue(
                    'SELECT `id_kwtsms_template` FROM `' . _DB_PREFIX_ . 'kwtsms_template` WHERE `template_key` = "' . pSQL($integ['customer_template']) . '"'
                );
            }

            $adminTemplateId = null;
            if ($integ['admin_template']) {
                $adminTemplateId = (int) Db::getInstance()->getValue(
                    'SELECT `id_kwtsms_template` FROM `' . _DB_PREFIX_ . 'kwtsms_template` WHERE `template_key` = "' . pSQL($integ['admin_template']) . '"'
                );
            }

            $result = Db::getInstance()->insert('kwtsms_integration', array(
                'integration_key' => pSQL($integ['integration_key']),
                'active' => 0,
                'recipient_type' => pSQL($integ['recipient_type']),
                'settings' => $integ['settings'] ? pSQL($integ['settings']) : null,
                'id_kwtsms_template' => (int) $customerTemplateId,
                'id_kwtsms_template_admin' => $adminTemplateId ? (int) $adminTemplateId : null,
                'date_upd' => $now,
            ));

            if (!$result) {
                return false;
            }
        }

        return true;
    }
}
