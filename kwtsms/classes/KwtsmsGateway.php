<?php
/**
 * kwtSMS - Gateway API Wrapper
 *
 * Wraps the kwtsms/kwtsms-php library and integrates with PrestaShop
 * Configuration and kwtsms_cache tables. All API calls go through this class.
 *
 * Related files:
 * - vendor/kwtsms/kwtsms-php: underlying API client
 * - classes/KwtsmsLogger.php: debug logging
 * - classes/KwtsmsSender.php: uses this for send()
 * - classes/KwtsmsCron.php: uses this for sync
 *
 * @author    kwtSMS <support@kwtsms.com>
 * @copyright kwtSMS
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 */

require_once dirname(__FILE__) . '/../vendor/autoload.php';

use KwtSMS\KwtSMS;

class KwtsmsGateway
{
    /** @var KwtSMS|null */
    private $client;

    /**
     * Get or create the KwtSMS client instance using stored credentials.
     *
     * @return KwtSMS|null Null if credentials not configured
     */
    public function getClient()
    {
        if ($this->client) {
            return $this->client;
        }

        $username = Configuration::get('KWTSMS_USERNAME');
        $password = Configuration::get('KWTSMS_PASSWORD');
        $senderId = Configuration::get('KWTSMS_SENDER_ID');
        $testMode = (bool) Configuration::get('KWTSMS_TEST_MODE');

        if (empty($username) || empty($password)) {
            return null;
        }

        $this->client = new KwtSMS($username, $password, $senderId, $testMode);

        return $this->client;
    }

    /**
     * Test connection by calling /balance/. Used for the "Connect" button.
     * On success: saves credentials, fetches senderids + coverage, caches all.
     *
     * @param string $username API username
     * @param string $password API password
     *
     * @return array ['success' => bool, 'balance' => float|null, 'error' => string|null]
     */
    public function connect($username, $password)
    {
        $senderId = Configuration::get('KWTSMS_SENDER_ID');
        $testMode = (bool) Configuration::get('KWTSMS_TEST_MODE');

        $tempClient = new KwtSMS($username, $password, $senderId, $testMode);

        try {
            $balanceResult = $tempClient->balance();
        } catch (\Exception $e) {
            KwtsmsLogger::debug('gateway_connect', 'Exception: ' . $e->getMessage());
            return array('success' => false, 'balance' => null, 'error' => 'Connection failed. Check your credentials.');
        }

        if (!isset($balanceResult['result']) || $balanceResult['result'] !== 'OK') {
            $errorMsg = isset($balanceResult['description']) ? $balanceResult['description'] : 'Connection failed. Check your credentials.';
            KwtsmsLogger::debug('gateway_connect', 'Balance check failed: ' . $errorMsg);
            return array('success' => false, 'balance' => null, 'error' => $errorMsg);
        }

        Configuration::updateValue('KWTSMS_USERNAME', $username);
        Configuration::updateValue('KWTSMS_PASSWORD', $password);
        Configuration::updateValue('KWTSMS_GATEWAY_CONNECTED', true);

        $balance = isset($balanceResult['available']) ? (float) $balanceResult['available'] : 0;
        self::setCache('balance', json_encode($balanceResult));

        $this->client = $tempClient;
        $this->syncSenderIds();
        $this->syncCoverage();

        KwtsmsLogger::debug('gateway_connect', 'Connected successfully. Balance: ' . $balance);

        return array('success' => true, 'balance' => $balance, 'error' => null);
    }

    /**
     * Get cached balance value.
     *
     * @return float
     */
    public function getCachedBalance()
    {
        $cached = self::getCache('balance');
        if ($cached) {
            $data = json_decode($cached, true);
            return isset($data['available']) ? (float) $data['available'] : 0;
        }
        return 0;
    }

    /**
     * Fetch and cache balance from API.
     *
     * @return array|null API response or null on failure
     */
    public function syncBalance()
    {
        $client = $this->getClient();
        if (!$client) {
            return null;
        }

        try {
            $result = $client->balance();
        } catch (\Exception $e) {
            KwtsmsLogger::debug('sync_balance', 'Exception: ' . $e->getMessage());
            return null;
        }

        if (isset($result['result']) && $result['result'] === 'OK') {
            self::setCache('balance', json_encode($result));
            KwtsmsLogger::debug('sync_balance', 'Balance synced: ' . json_encode($result));
        }

        return $result;
    }

    /**
     * Fetch and cache sender IDs from API.
     *
     * @return array|null API response or null on failure
     */
    public function syncSenderIds()
    {
        $client = $this->getClient();
        if (!$client) {
            return null;
        }

        try {
            $result = $client->senderids();
        } catch (\Exception $e) {
            KwtsmsLogger::debug('sync_senderids', 'Exception: ' . $e->getMessage());
            return null;
        }

        if (isset($result['result']) && $result['result'] === 'OK') {
            self::setCache('senderids', json_encode($result));
            KwtsmsLogger::debug('sync_senderids', 'Sender IDs synced: ' . json_encode($result));
        }

        return $result;
    }

    /**
     * Fetch and cache coverage from API.
     *
     * @return array|null API response or null on failure
     */
    public function syncCoverage()
    {
        $client = $this->getClient();
        if (!$client) {
            return null;
        }

        try {
            $result = $client->coverage();
        } catch (\Exception $e) {
            KwtsmsLogger::debug('sync_coverage', 'Exception: ' . $e->getMessage());
            return null;
        }

        if (isset($result['result']) && $result['result'] === 'OK') {
            self::setCache('coverage', json_encode($result));
            KwtsmsLogger::debug('sync_coverage', 'Coverage synced: ' . json_encode($result));
        }

        return $result;
    }

    /**
     * Get cached sender IDs as array.
     *
     * @return array List of sender ID strings
     */
    public function getCachedSenderIds()
    {
        $cached = self::getCache('senderids');
        if ($cached) {
            $data = json_decode($cached, true);
            return isset($data['senderid']) ? $data['senderid'] : array();
        }
        return array();
    }

    /**
     * Get cached coverage country codes as array.
     *
     * @return array List of country code strings
     */
    public function getCachedCoverage()
    {
        $cached = self::getCache('coverage');
        if ($cached) {
            $data = json_decode($cached, true);
            if (is_array($data) && isset($data['result']) && $data['result'] === 'OK') {
                unset($data['result']);
                return array_keys($data);
            }
        }
        return array();
    }

    /**
     * Send SMS via the API. Low-level call, use KwtsmsSender for the full pipeline.
     *
     * @param string|array $mobile Phone number(s)
     * @param string       $message SMS content
     *
     * @return array API response
     */
    public function send($mobile, $message)
    {
        $client = $this->getClient();
        if (!$client) {
            return array('result' => 'ERROR', 'code' => 'LOCAL', 'description' => 'Gateway not configured');
        }

        try {
            $result = $client->send($mobile, $message);
        } catch (\Exception $e) {
            KwtsmsLogger::debug('gateway_send', 'Exception: ' . $e->getMessage());
            return array('result' => 'ERROR', 'code' => 'EXCEPTION', 'description' => $e->getMessage());
        }

        return $result;
    }

    /**
     * Get a value from the kwtsms_cache table.
     *
     * @param string $key Cache key
     *
     * @return string|null Cached value or null
     */
    public static function getCache($key)
    {
        return Db::getInstance()->getValue(
            'SELECT `cache_value` FROM `' . _DB_PREFIX_ . 'kwtsms_cache` WHERE `cache_key` = "' . pSQL($key) . '"'
        );
    }

    /**
     * Set a value in the kwtsms_cache table (insert or update).
     *
     * @param string $key   Cache key
     * @param string $value Cache value (JSON string)
     *
     * @return bool
     */
    public static function setCache($key, $value)
    {
        $now = date('Y-m-d H:i:s');
        $exists = Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'kwtsms_cache` WHERE `cache_key` = "' . pSQL($key) . '"'
        );

        if ($exists) {
            return Db::getInstance()->update('kwtsms_cache', array(
                'cache_value' => pSQL($value),
                'date_upd' => $now,
            ), '`cache_key` = "' . pSQL($key) . '"');
        }

        return Db::getInstance()->insert('kwtsms_cache', array(
            'cache_key' => pSQL($key),
            'cache_value' => pSQL($value),
            'date_upd' => $now,
        ));
    }

    /**
     * Get cache last updated timestamp.
     *
     * @param string $key Cache key
     *
     * @return string|null Datetime string or null
     */
    public static function getCacheUpdatedAt($key)
    {
        return Db::getInstance()->getValue(
            'SELECT `date_upd` FROM `' . _DB_PREFIX_ . 'kwtsms_cache` WHERE `cache_key` = "' . pSQL($key) . '"'
        );
    }
}
