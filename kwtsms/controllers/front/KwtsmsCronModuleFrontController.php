<?php
/**
 * kwtSMS - Standalone Cron Endpoint
 *
 * Front controller that runs the daily sync when called with a valid token.
 * URL: /module/kwtsms/cron?token={KWTSMS_CRON_TOKEN}
 * Can be added to system crontab, cPanel, or any external scheduler.
 *
 * Related files:
 * - classes/KwtsmsCron.php: actual sync logic
 *
 * @author    kwtSMS <support@kwtsms.com>
 * @copyright kwtSMS
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 */

class KwtsmsCronModuleFrontController extends ModuleFrontController
{
    /** @var bool */
    public $auth = false;

    /** @var bool */
    public $ajax = true;

    public function initContent()
    {
        $token = Tools::getValue('token');
        $expectedToken = Configuration::get('KWTSMS_CRON_TOKEN');

        if (empty($token) || $token !== $expectedToken) {
            header('HTTP/1.1 403 Forbidden');
            die('Forbidden');
        }

        require_once _PS_MODULE_DIR_ . 'kwtsms/classes/KwtsmsGateway.php';
        require_once _PS_MODULE_DIR_ . 'kwtsms/classes/KwtsmsLogger.php';
        require_once _PS_MODULE_DIR_ . 'kwtsms/classes/KwtsmsCron.php';

        $results = KwtsmsCron::run();

        header('Content-Type: application/json');
        die(json_encode(array(
            'success' => true,
            'results' => $results,
            'timestamp' => date('Y-m-d H:i:s'),
        )));
    }
}
