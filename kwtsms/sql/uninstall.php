<?php
/**
 * kwtSMS - SQL Uninstall
 *
 * Drops all database tables created by the module.
 * Called from KwtsmsInstaller::uninstallDatabase()
 *
 * @author    kwtSMS <support@kwtsms.com>
 * @copyright kwtSMS
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 */

$sql = array();

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'kwtsms_log`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'kwtsms_template`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'kwtsms_integration`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'kwtsms_otp_attempt`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'kwtsms_cache`';
