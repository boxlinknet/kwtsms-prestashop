<?php
/**
 * kwtSMS - Logger
 *
 * Handles all logging to the kwtsms_log database table.
 * Supports debug mode (verbose) and normal mode (send results only).
 * All SMS attempts are logged regardless of debug mode.
 *
 * Related files:
 * - sql/install.php: kwtsms_log table definition
 * - classes/KwtsmsSender.php: calls logger for every send attempt
 *
 * @author    kwtSMS <support@kwtsms.com>
 * @copyright kwtSMS
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 */

class KwtsmsLogger
{
    /**
     * Log an SMS send attempt to the kwtsms_log table.
     *
     * @param array $data Log entry fields:
     *   - recipient (string): phone number
     *   - recipient_type (string): 'customer' or 'admin'
     *   - id_customer (int|null): customer ID
     *   - id_order (int|null): order ID
     *   - sender_id (string): sender ID used
     *   - message (string): SMS content
     *   - event_type (string): integration key
     *   - status (string): 'sent', 'failed', 'skipped'
     *   - error_code (string|null): API error code
     *   - error_message (string|null): error description
     *   - api_response (string|null): raw JSON response
     *   - msg_id (string|null): API message ID
     *   - points_charged (int): credits used
     *   - test_mode (int): 1 if test mode
     *   - batch_id (string|null): UUID for bulk sends
     *
     * @return int|false Inserted row ID or false on failure
     */
    public static function logSms(array $data)
    {
        $defaults = array(
            'recipient' => '',
            'recipient_type' => 'customer',
            'id_customer' => null,
            'id_order' => null,
            'sender_id' => '',
            'message' => '',
            'event_type' => '',
            'status' => 'pending',
            'error_code' => null,
            'error_message' => null,
            'api_response' => null,
            'msg_id' => null,
            'points_charged' => 0,
            'test_mode' => 0,
            'batch_id' => null,
            'date_add' => date('Y-m-d H:i:s'),
        );

        $entry = array_merge($defaults, $data);

        $insert = array(
            'recipient' => pSQL($entry['recipient']),
            'recipient_type' => pSQL($entry['recipient_type']),
            'id_customer' => $entry['id_customer'] ? (int) $entry['id_customer'] : null,
            'id_order' => $entry['id_order'] ? (int) $entry['id_order'] : null,
            'sender_id' => pSQL($entry['sender_id']),
            'message' => pSQL($entry['message']),
            'event_type' => pSQL($entry['event_type']),
            'status' => pSQL($entry['status']),
            'error_code' => $entry['error_code'] ? pSQL($entry['error_code']) : null,
            'error_message' => $entry['error_message'] ? pSQL($entry['error_message']) : null,
            'api_response' => $entry['api_response'] ? pSQL($entry['api_response']) : null,
            'msg_id' => $entry['msg_id'] ? pSQL($entry['msg_id']) : null,
            'points_charged' => (int) $entry['points_charged'],
            'test_mode' => (int) $entry['test_mode'],
            'batch_id' => $entry['batch_id'] ? pSQL($entry['batch_id']) : null,
            'date_add' => pSQL($entry['date_add']),
        );

        $result = Db::getInstance()->insert('kwtsms_log', $insert);

        if ($result) {
            return (int) Db::getInstance()->Insert_ID();
        }

        return false;
    }

    /**
     * Log a debug message. Only writes when KWTSMS_DEBUG_MODE is on.
     * Debug entries are logged as status='debug' in kwtsms_log for visibility.
     *
     * @param string $eventType Context (e.g. 'normalize', 'verify', 'clean')
     * @param string $message Debug message
     * @param array  $context Optional extra context (id_customer, id_order, etc.)
     *
     * @return void
     */
    public static function debug($eventType, $message, array $context = array())
    {
        if (!Configuration::get('KWTSMS_DEBUG_MODE')) {
            return;
        }

        self::logSms(array_merge(array(
            'event_type' => pSQL($eventType),
            'message' => pSQL($message),
            'status' => 'debug',
            'sender_id' => '',
            'recipient' => isset($context['recipient']) ? $context['recipient'] : '',
        ), $context));
    }

    /**
     * Get log entries with optional filters and pagination.
     *
     * @param array $filters Optional filters:
     *   - status (string): 'sent', 'failed', 'skipped'
     *   - event_type (string): integration key
     *   - date_from (string): 'Y-m-d' start date
     *   - date_to (string): 'Y-m-d' end date
     *   - recipient (string): phone number search
     * @param int $page Page number (1-based)
     * @param int $perPage Results per page
     *
     * @return array ['rows' => array, 'total' => int]
     */
    public static function getLogs(array $filters = array(), $page = 1, $perPage = 50)
    {
        $where = array('1=1');

        if (!empty($filters['status']) && $filters['status'] !== 'debug') {
            $where[] = '`status` = "' . pSQL($filters['status']) . '"';
        }

        if (empty($filters['status'])) {
            $where[] = '`status` != "debug"';
        }

        if (!empty($filters['event_type'])) {
            $where[] = '`event_type` = "' . pSQL($filters['event_type']) . '"';
        }

        if (!empty($filters['date_from'])) {
            $where[] = '`date_add` >= "' . pSQL($filters['date_from']) . ' 00:00:00"';
        }

        if (!empty($filters['date_to'])) {
            $where[] = '`date_add` <= "' . pSQL($filters['date_to']) . ' 23:59:59"';
        }

        if (!empty($filters['recipient'])) {
            $where[] = '`recipient` LIKE "%' . pSQL($filters['recipient']) . '%"';
        }

        $whereClause = implode(' AND ', $where);
        $offset = ((int) $page - 1) * (int) $perPage;

        $total = (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'kwtsms_log` WHERE ' . $whereClause
        );

        $rows = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'kwtsms_log`
             WHERE ' . $whereClause . '
             ORDER BY `date_add` DESC
             LIMIT ' . (int) $offset . ', ' . (int) $perPage
        );

        return array(
            'rows' => $rows ? $rows : array(),
            'total' => $total,
        );
    }

    /**
     * Get summary stats for the dashboard.
     *
     * @return array ['sent_today' => int, 'sent_month' => int, 'failed_month' => int]
     */
    public static function getStats()
    {
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');

        $sentToday = (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'kwtsms_log`
             WHERE `status` = "sent" AND `date_add` >= "' . pSQL($today) . ' 00:00:00"'
        );

        $sentMonth = (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'kwtsms_log`
             WHERE `status` = "sent" AND `date_add` >= "' . pSQL($monthStart) . ' 00:00:00"'
        );

        $failedMonth = (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'kwtsms_log`
             WHERE `status` = "failed" AND `date_add` >= "' . pSQL($monthStart) . ' 00:00:00"'
        );

        return array(
            'sent_today' => $sentToday,
            'sent_month' => $sentMonth,
            'failed_month' => $failedMonth,
        );
    }

    /**
     * Delete all log entries.
     *
     * @return bool
     */
    public static function clearAll()
    {
        return Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'kwtsms_log`'
        );
    }
}
