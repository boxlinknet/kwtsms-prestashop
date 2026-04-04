<?php
/**
 * kwtSMS - Cron Sync
 *
 * Daily sync job that refreshes cached balance, sender IDs, and coverage
 * from the kwtSMS API. Called by either the CronJobs module hook or
 * the standalone front controller endpoint.
 *
 * Related files:
 * - classes/KwtsmsGateway.php: API calls for sync
 * - controllers/front/KwtsmsCronModuleFrontController.php: standalone endpoint
 * - kwtsms.php: hookActionCronJob() calls this
 *
 * @author    kwtSMS <support@kwtsms.com>
 * @copyright kwtSMS
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 */

class KwtsmsCron
{
    /**
     * Run the daily sync. Each API call is independent; if one fails,
     * the others still run.
     *
     * @return array ['balance' => bool, 'senderids' => bool, 'coverage' => bool]
     */
    public static function run()
    {
        $results = array(
            'balance' => false,
            'senderids' => false,
            'coverage' => false,
        );

        if (!Configuration::get('KWTSMS_GATEWAY_CONNECTED')) {
            KwtsmsLogger::debug('cron', 'Skipped: gateway not connected');
            return $results;
        }

        $gateway = new KwtsmsGateway();

        // Sync balance
        $balanceResult = $gateway->syncBalance();
        if ($balanceResult && isset($balanceResult['available'])) {
            $results['balance'] = true;
        } else {
            KwtsmsLogger::debug('cron', 'Balance sync failed: ' . json_encode($balanceResult));
        }

        // Sync sender IDs
        $senderResult = $gateway->syncSenderIds();
        if ($senderResult && isset($senderResult['result']) && $senderResult['result'] === 'OK') {
            $results['senderids'] = true;
        } else {
            KwtsmsLogger::debug('cron', 'SenderID sync failed: ' . json_encode($senderResult));
        }

        // Sync coverage
        $coverageResult = $gateway->syncCoverage();
        if ($coverageResult && isset($coverageResult['result']) && $coverageResult['result'] === 'OK') {
            $results['coverage'] = true;
        } else {
            KwtsmsLogger::debug('cron', 'Coverage sync failed: ' . json_encode($coverageResult));
        }

        KwtsmsLogger::debug('cron', 'Sync complete: ' . json_encode($results));

        return $results;
    }
}
