<?php
/**
 * kwtSMS - Admin Controller
 *
 * Back-office controller with 6 tabs: Dashboard, Gateway, Settings,
 * Templates, Logs, Help. Uses Smarty templates for rendering.
 *
 * Related files:
 * - views/templates/admin/*.tpl: tab templates
 * - views/css/admin.css: styles
 * - views/js/admin.js: client-side logic
 * - classes/*: business logic classes
 *
 * @author    kwtSMS <support@kwtsms.com>
 * @copyright kwtSMS
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 */

require_once _PS_MODULE_DIR_ . 'kwtsms/classes/KwtsmsGateway.php';
require_once _PS_MODULE_DIR_ . 'kwtsms/classes/KwtsmsSender.php';
require_once _PS_MODULE_DIR_ . 'kwtsms/classes/KwtsmsTemplateManager.php';
require_once _PS_MODULE_DIR_ . 'kwtsms/classes/KwtsmsLogger.php';
require_once _PS_MODULE_DIR_ . 'kwtsms/classes/KwtsmsCron.php';

class AdminKwtsmsController extends ModuleAdminController
{
    /** @var array Tab definitions */
    private $tabs = array(
        'dashboard' => 'Dashboard',
        'gateway'   => 'Gateway',
        'settings'  => 'Settings',
        'templates' => 'Templates',
        'logs'      => 'Logs',
        'help'      => 'Help',
    );

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent()
    {
        parent::initContent();

        $currentTab = Tools::getValue('tab', 'dashboard');
        if (!array_key_exists($currentTab, $this->tabs)) {
            $currentTab = 'dashboard';
        }

        // Handle POST actions
        $this->processActions($currentTab);

        // Build tab content
        $tabContent = $this->renderTab($currentTab);

        // Assign common variables
        $this->context->smarty->assign(array(
            'tabs'        => $this->tabs,
            'current_tab' => $currentTab,
            'tab_content' => $tabContent,
            'admin_link'  => $this->context->link->getAdminLink('AdminKwtsms'),
            'module_dir'  => _PS_MODULE_DIR_ . 'kwtsms/',
        ));

        // Add CSS/JS
        $this->addCSS(_PS_MODULE_DIR_ . 'kwtsms/views/css/admin.css');
        $this->addJS(_PS_MODULE_DIR_ . 'kwtsms/views/js/admin.js');

        $this->setTemplate('dashboard.tpl');
    }

    /**
     * Route to the correct tab renderer.
     *
     * @param string $tab
     *
     * @return string Rendered HTML
     */
    private function renderTab($tab)
    {
        switch ($tab) {
            case 'dashboard':
                return $this->renderDashboard();
            case 'gateway':
                return $this->renderGateway();
            case 'settings':
                return $this->renderSettings();
            case 'templates':
                return $this->renderTemplates();
            case 'logs':
                return $this->renderLogs();
            case 'help':
                return $this->renderHelp();
            default:
                return $this->renderDashboard();
        }
    }

    /**
     * Handle POST form submissions per tab.
     *
     * @param string $tab
     */
    private function processActions($tab)
    {
        if (!Tools::isSubmit('submitKwtsms')) {
            return;
        }

        switch ($tab) {
            case 'gateway':
                $this->processGateway();
                break;
            case 'settings':
                $this->processSettings();
                break;
            case 'templates':
                $this->processTemplates();
                break;
            case 'logs':
                $this->processLogs();
                break;
        }
    }

    // =========================================================================
    // Dashboard Tab
    // =========================================================================

    private function renderDashboard()
    {
        $gateway = new KwtsmsGateway();
        $stats = KwtsmsLogger::getStats();

        $this->context->smarty->assign(array(
            'balance'           => $gateway->getCachedBalance(),
            'balance_updated'   => KwtsmsGateway::getCacheUpdatedAt('balance'),
            'sender_id'         => Configuration::get('KWTSMS_SENDER_ID'),
            'default_country'   => Configuration::get('KWTSMS_DEFAULT_COUNTRY_CODE'),
            'gateway_connected' => (bool) Configuration::get('KWTSMS_GATEWAY_CONNECTED'),
            'gateway_enabled'   => (bool) Configuration::get('KWTSMS_GATEWAY_ENABLED'),
            'test_mode'         => (bool) Configuration::get('KWTSMS_TEST_MODE'),
            'sent_today'        => $stats['sent_today'],
            'sent_month'        => $stats['sent_month'],
            'failed_month'      => $stats['failed_month'],
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'kwtsms/views/templates/admin/dashboard.tpl'
        );
    }

    // =========================================================================
    // Gateway Tab (placeholder - implemented in Task 10)
    // =========================================================================

    private function renderGateway()
    {
        return '<div class="alert alert-info">Gateway tab - coming next.</div>';
    }

    private function processGateway()
    {
        // Implemented in Task 10
    }

    // =========================================================================
    // Settings Tab (placeholder - implemented in Task 11)
    // =========================================================================

    private function renderSettings()
    {
        return '<div class="alert alert-info">Settings tab - coming next.</div>';
    }

    private function processSettings()
    {
        // Implemented in Task 11
    }

    // =========================================================================
    // Templates Tab (placeholder - implemented in Task 12)
    // =========================================================================

    private function renderTemplates()
    {
        return '<div class="alert alert-info">Templates tab - coming next.</div>';
    }

    private function processTemplates()
    {
        // Implemented in Task 12
    }

    // =========================================================================
    // Logs Tab (placeholder - implemented in Task 13)
    // =========================================================================

    private function renderLogs()
    {
        return '<div class="alert alert-info">Logs tab - coming next.</div>';
    }

    private function processLogs()
    {
        // Implemented in Task 13
    }

    // =========================================================================
    // Help Tab (placeholder - implemented in Task 14)
    // =========================================================================

    private function renderHelp()
    {
        return '<div class="alert alert-info">Help tab - coming next.</div>';
    }
}
