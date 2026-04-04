<?php
/**
 * kwtSMS - SQL Install
 *
 * Creates all database tables required by the module.
 * Called from KwtsmsInstaller::installDatabase()
 *
 * @author    kwtSMS <support@kwtsms.com>
 * @copyright kwtSMS
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'kwtsms_log` (
    `id_kwtsms_log` int(11) NOT NULL AUTO_INCREMENT,
    `recipient` varchar(20) NOT NULL,
    `recipient_type` enum("customer","admin") NOT NULL DEFAULT "customer",
    `id_customer` int(11) DEFAULT NULL,
    `id_order` int(11) DEFAULT NULL,
    `sender_id` varchar(20) NOT NULL DEFAULT "",
    `message` text NOT NULL,
    `event_type` varchar(64) NOT NULL DEFAULT "",
    `status` varchar(20) NOT NULL DEFAULT "pending",
    `error_code` varchar(10) DEFAULT NULL,
    `error_message` varchar(255) DEFAULT NULL,
    `api_response` text DEFAULT NULL,
    `msg_id` varchar(64) DEFAULT NULL,
    `points_charged` int(11) NOT NULL DEFAULT 0,
    `test_mode` tinyint(1) NOT NULL DEFAULT 0,
    `batch_id` varchar(36) DEFAULT NULL,
    `date_add` datetime NOT NULL,
    PRIMARY KEY (`id_kwtsms_log`),
    KEY `idx_recipient` (`recipient`),
    KEY `idx_id_customer` (`id_customer`),
    KEY `idx_id_order` (`id_order`),
    KEY `idx_event_type` (`event_type`),
    KEY `idx_status` (`status`),
    KEY `idx_batch_id` (`batch_id`),
    KEY `idx_date_add` (`date_add`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'kwtsms_template` (
    `id_kwtsms_template` int(11) NOT NULL AUTO_INCREMENT,
    `template_key` varchar(64) NOT NULL,
    `recipient_type` enum("customer","admin") NOT NULL DEFAULT "customer",
    `content_en` text NOT NULL,
    `content_ar` text DEFAULT NULL,
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY (`id_kwtsms_template`),
    UNIQUE KEY `idx_template_key` (`template_key`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'kwtsms_integration` (
    `id_kwtsms_integration` int(11) NOT NULL AUTO_INCREMENT,
    `integration_key` varchar(64) NOT NULL,
    `active` tinyint(1) NOT NULL DEFAULT 0,
    `recipient_type` enum("customer","admin","both") NOT NULL DEFAULT "customer",
    `settings` text DEFAULT NULL,
    `id_kwtsms_template` int(11) NOT NULL DEFAULT 0,
    `id_kwtsms_template_admin` int(11) DEFAULT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY (`id_kwtsms_integration`),
    UNIQUE KEY `idx_integration_key` (`integration_key`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'kwtsms_otp_attempt` (
    `id_kwtsms_otp_attempt` int(11) NOT NULL AUTO_INCREMENT,
    `phone` varchar(20) NOT NULL,
    `otp_code` varchar(10) NOT NULL,
    `ip_address` varchar(45) NOT NULL,
    `id_customer` int(11) DEFAULT NULL,
    `purpose` varchar(32) NOT NULL DEFAULT "login",
    `status` enum("pending","verified","expired","blocked") NOT NULL DEFAULT "pending",
    `attempts` int(11) NOT NULL DEFAULT 0,
    `max_attempts` int(11) NOT NULL DEFAULT 3,
    `date_add` datetime NOT NULL,
    `date_expire` datetime NOT NULL,
    `date_verified` datetime DEFAULT NULL,
    PRIMARY KEY (`id_kwtsms_otp_attempt`),
    KEY `idx_phone` (`phone`),
    KEY `idx_ip_address` (`ip_address`),
    KEY `idx_status` (`status`),
    KEY `idx_date_add` (`date_add`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'kwtsms_cache` (
    `id_kwtsms_cache` int(11) NOT NULL AUTO_INCREMENT,
    `cache_key` varchar(64) NOT NULL,
    `cache_value` text NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY (`id_kwtsms_cache`),
    UNIQUE KEY `idx_cache_key` (`cache_key`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';
