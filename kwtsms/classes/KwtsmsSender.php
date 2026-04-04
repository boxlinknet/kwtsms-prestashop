<?php
/**
 * kwtSMS - SMS Send Pipeline
 *
 * Orchestrates the full SMS sending flow: guard checks, phone normalization,
 * verification, message cleaning, deduplication, batching, sending, and logging.
 * All SMS in the module must go through KwtsmsSender::send().
 *
 * Related files:
 * - classes/KwtsmsGateway.php: low-level API calls
 * - classes/KwtsmsLogger.php: logging every step
 * - vendor/kwtsms/kwtsms-php: phone/message utilities
 *
 * @author    kwtSMS <support@kwtsms.com>
 * @copyright kwtSMS
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 */

require_once dirname(__FILE__) . '/../vendor/autoload.php';

use KwtSMS\PhoneUtils;
use KwtSMS\MessageUtils;

class KwtsmsSender
{
    /** @var int Max recipients per API call */
    const BATCH_SIZE = 200;

    /** @var float Delay between batches in seconds */
    const BATCH_DELAY = 0.2;

    /**
     * Send SMS through the full pipeline.
     *
     * @param string|array $phones  One phone number or array of numbers
     * @param string       $message SMS content (may contain HTML/emoji, will be cleaned)
     * @param string       $eventType Integration key (e.g. 'order_placed', 'gateway_test')
     * @param array        $context Extra context for logging:
     *   - id_customer (int|null)
     *   - id_order (int|null)
     *   - recipient_type (string): 'customer' or 'admin'
     *
     * @return array ['success' => bool, 'msg_id' => string|null, 'error' => string|null,
     *                'numbers_sent' => int, 'numbers_skipped' => int]
     */
    public static function send($phones, $message, $eventType, array $context = array())
    {
        $result = array(
            'success' => false,
            'msg_id' => null,
            'error' => null,
            'numbers_sent' => 0,
            'numbers_skipped' => 0,
        );

        $recipientType = isset($context['recipient_type']) ? $context['recipient_type'] : 'customer';
        $idCustomer = isset($context['id_customer']) ? (int) $context['id_customer'] : null;
        $idOrder = isset($context['id_order']) ? (int) $context['id_order'] : null;

        // 1. Global guard checks
        $guardError = self::checkGuards();
        if ($guardError) {
            $result['error'] = $guardError;
            KwtsmsLogger::logSms(array(
                'recipient' => is_array($phones) ? implode(',', array_slice($phones, 0, 3)) : $phones,
                'recipient_type' => $recipientType,
                'id_customer' => $idCustomer,
                'id_order' => $idOrder,
                'sender_id' => Configuration::get('KWTSMS_SENDER_ID'),
                'message' => $message,
                'event_type' => $eventType,
                'status' => 'skipped',
                'error_message' => $guardError,
                'test_mode' => (int) Configuration::get('KWTSMS_TEST_MODE'),
            ));
            return $result;
        }

        // 2. Normalize phones to array
        if (!is_array($phones)) {
            $phones = array($phones);
        }

        $defaultCountry = Configuration::get('KWTSMS_DEFAULT_COUNTRY_CODE');
        $coverage = self::getCoverageList();

        $validPhones = array();
        $skipped = 0;

        foreach ($phones as $phone) {
            // Normalize
            $normalized = PhoneUtils::normalize($phone);
            if (empty($normalized)) {
                KwtsmsLogger::debug('normalize', 'Empty after normalization: ' . $phone);
                $skipped++;
                continue;
            }

            // Prepend default country code if no recognized prefix
            if ($defaultCountry && !self::hasCountryPrefix($normalized, $coverage)) {
                $normalized = $defaultCountry . ltrim($normalized, '0');
                KwtsmsLogger::debug('normalize', 'Prepended default country: ' . $phone . ' -> ' . $normalized);
            }

            // 3. Verify: country-specific length + coverage check
            if (!PhoneUtils::validate($normalized)) {
                KwtsmsLogger::debug('verify', 'Invalid phone format: ' . $normalized);
                $skipped++;
                continue;
            }

            $phoneCountry = PhoneUtils::country_code($normalized);
            if (!empty($coverage) && $phoneCountry && !in_array($phoneCountry, $coverage)) {
                KwtsmsLogger::debug('verify', 'Country not in coverage: ' . $normalized . ' (prefix: ' . $phoneCountry . ')');
                $skipped++;
                continue;
            }

            $validPhones[] = $normalized;
        }

        // Deduplicate
        $validPhones = array_unique($validPhones);
        $result['numbers_skipped'] = $skipped;

        // 5. Validate: any numbers left?
        if (empty($validPhones)) {
            $result['error'] = 'No valid phone numbers';
            KwtsmsLogger::logSms(array(
                'recipient' => '',
                'recipient_type' => $recipientType,
                'id_customer' => $idCustomer,
                'id_order' => $idOrder,
                'sender_id' => Configuration::get('KWTSMS_SENDER_ID'),
                'message' => $message,
                'event_type' => $eventType,
                'status' => 'skipped',
                'error_message' => 'No valid phone numbers after normalization',
                'test_mode' => (int) Configuration::get('KWTSMS_TEST_MODE'),
            ));
            return $result;
        }

        // 4. Clean message
        $cleanedMessage = MessageUtils::clean_message($message);

        if (empty(trim($cleanedMessage))) {
            $result['error'] = 'Empty message after cleaning';
            KwtsmsLogger::logSms(array(
                'recipient' => implode(',', array_slice($validPhones, 0, 3)),
                'recipient_type' => $recipientType,
                'id_customer' => $idCustomer,
                'id_order' => $idOrder,
                'sender_id' => Configuration::get('KWTSMS_SENDER_ID'),
                'message' => $message,
                'event_type' => $eventType,
                'status' => 'skipped',
                'error_message' => 'Empty message after cleaning',
                'test_mode' => (int) Configuration::get('KWTSMS_TEST_MODE'),
            ));
            return $result;
        }

        KwtsmsLogger::debug('clean', 'Message cleaned. Length: ' . mb_strlen($cleanedMessage));

        // 6. Send
        $gateway = new KwtsmsGateway();
        $senderId = Configuration::get('KWTSMS_SENDER_ID');
        $testMode = (int) Configuration::get('KWTSMS_TEST_MODE');
        $batchId = null;

        if (count($validPhones) > self::BATCH_SIZE) {
            $batchId = self::generateUuid();
            $result = self::bulkSend($gateway, $validPhones, $cleanedMessage, $eventType, $context, $senderId, $testMode, $batchId);
        } else {
            $phoneString = count($validPhones) === 1 ? $validPhones[0] : implode(',', $validPhones);

            if (count($validPhones) > 1) {
                $batchId = self::generateUuid();
            }

            $apiResult = $gateway->send($phoneString, $cleanedMessage);

            // 7. Handle response - log per recipient
            foreach ($validPhones as $singlePhone) {
                if (isset($apiResult['result']) && $apiResult['result'] === 'OK') {
                    KwtsmsLogger::logSms(array(
                        'recipient' => $singlePhone,
                        'recipient_type' => $recipientType,
                        'id_customer' => $idCustomer,
                        'id_order' => $idOrder,
                        'sender_id' => $senderId,
                        'message' => $cleanedMessage,
                        'event_type' => $eventType,
                        'status' => 'sent',
                        'api_response' => json_encode($apiResult),
                        'msg_id' => isset($apiResult['msg-id']) ? $apiResult['msg-id'] : null,
                        'points_charged' => isset($apiResult['points-charged']) ? (int) $apiResult['points-charged'] : 0,
                        'test_mode' => $testMode,
                        'batch_id' => $batchId,
                    ));
                } else {
                    KwtsmsLogger::logSms(array(
                        'recipient' => $singlePhone,
                        'recipient_type' => $recipientType,
                        'id_customer' => $idCustomer,
                        'id_order' => $idOrder,
                        'sender_id' => $senderId,
                        'message' => $cleanedMessage,
                        'event_type' => $eventType,
                        'status' => 'failed',
                        'error_code' => isset($apiResult['code']) ? $apiResult['code'] : null,
                        'error_message' => isset($apiResult['description']) ? $apiResult['description'] : 'Unknown error',
                        'api_response' => json_encode($apiResult),
                        'test_mode' => $testMode,
                        'batch_id' => $batchId,
                    ));
                }
            }

            if (isset($apiResult['result']) && $apiResult['result'] === 'OK') {
                $result['success'] = true;
                $result['msg_id'] = isset($apiResult['msg-id']) ? $apiResult['msg-id'] : null;
                $result['numbers_sent'] = count($validPhones);

                // Update cached balance
                if (isset($apiResult['balance-after'])) {
                    KwtsmsGateway::setCache('balance', json_encode(array(
                        'result' => 'OK',
                        'available' => $apiResult['balance-after'],
                    )));
                }
            } else {
                $result['error'] = isset($apiResult['description']) ? $apiResult['description'] : 'Send failed';
            }
        }

        $result['numbers_skipped'] = $skipped;

        return $result;
    }

    /**
     * Send to 200+ numbers in batches.
     *
     * @param KwtsmsGateway $gateway
     * @param array         $phones
     * @param string        $message
     * @param string        $eventType
     * @param array         $context
     * @param string        $senderId
     * @param int           $testMode
     * @param string        $batchId
     *
     * @return array Result array
     */
    private static function bulkSend($gateway, $phones, $message, $eventType, $context, $senderId, $testMode, $batchId)
    {
        $result = array(
            'success' => true,
            'msg_id' => null,
            'error' => null,
            'numbers_sent' => 0,
            'numbers_skipped' => 0,
        );

        $recipientType = isset($context['recipient_type']) ? $context['recipient_type'] : 'customer';
        $idCustomer = isset($context['id_customer']) ? (int) $context['id_customer'] : null;
        $idOrder = isset($context['id_order']) ? (int) $context['id_order'] : null;

        $chunks = array_chunk($phones, self::BATCH_SIZE);

        foreach ($chunks as $i => $chunk) {
            if ($i > 0) {
                usleep((int) (self::BATCH_DELAY * 1000000));
            }

            $phoneString = implode(',', $chunk);
            $apiResult = $gateway->send($phoneString, $message);

            foreach ($chunk as $singlePhone) {
                if (isset($apiResult['result']) && $apiResult['result'] === 'OK') {
                    KwtsmsLogger::logSms(array(
                        'recipient' => $singlePhone,
                        'recipient_type' => $recipientType,
                        'id_customer' => $idCustomer,
                        'id_order' => $idOrder,
                        'sender_id' => $senderId,
                        'message' => $message,
                        'event_type' => $eventType,
                        'status' => 'sent',
                        'api_response' => json_encode($apiResult),
                        'msg_id' => isset($apiResult['msg-id']) ? $apiResult['msg-id'] : null,
                        'points_charged' => 0,
                        'test_mode' => $testMode,
                        'batch_id' => $batchId,
                    ));
                    $result['numbers_sent']++;
                } else {
                    KwtsmsLogger::logSms(array(
                        'recipient' => $singlePhone,
                        'recipient_type' => $recipientType,
                        'id_customer' => $idCustomer,
                        'id_order' => $idOrder,
                        'sender_id' => $senderId,
                        'message' => $message,
                        'event_type' => $eventType,
                        'status' => 'failed',
                        'error_code' => isset($apiResult['code']) ? $apiResult['code'] : null,
                        'error_message' => isset($apiResult['description']) ? $apiResult['description'] : 'Unknown error',
                        'api_response' => json_encode($apiResult),
                        'test_mode' => $testMode,
                        'batch_id' => $batchId,
                    ));
                }
            }

            if (isset($apiResult['balance-after'])) {
                KwtsmsGateway::setCache('balance', json_encode(array(
                    'result' => 'OK',
                    'available' => $apiResult['balance-after'],
                )));
            }
        }

        return $result;
    }

    /**
     * Check global guards: connected, enabled, balance.
     *
     * @return string|null Error message or null if all guards pass
     */
    private static function checkGuards()
    {
        if (!Configuration::get('KWTSMS_GATEWAY_CONNECTED')) {
            return 'Gateway not connected';
        }

        if (!Configuration::get('KWTSMS_GATEWAY_ENABLED')) {
            return 'Gateway disabled';
        }

        $gateway = new KwtsmsGateway();
        if ($gateway->getCachedBalance() <= 0) {
            return 'Zero balance';
        }

        return null;
    }

    /**
     * Check if a phone number starts with any known country prefix.
     *
     * @param string $phone    Normalized phone
     * @param array  $coverage List of country codes
     *
     * @return bool
     */
    private static function hasCountryPrefix($phone, array $coverage)
    {
        foreach ($coverage as $prefix) {
            if (strpos($phone, (string) $prefix) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get coverage list from cache.
     *
     * @return array
     */
    private static function getCoverageList()
    {
        $gateway = new KwtsmsGateway();
        return $gateway->getCachedCoverage();
    }

    /**
     * Generate a UUID v4 for batch_id.
     *
     * @return string
     */
    private static function generateUuid()
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
