<?php
/**
 * kwtSMS - Template Manager
 *
 * Loads SMS templates from kwtsms_template table, selects the correct language
 * (Arabic or English fallback), and replaces placeholders with actual values.
 *
 * Related files:
 * - sql/install.php: kwtsms_template table
 * - classes/KwtsmsInstaller.php: seeds default templates
 * - kwtsms.php: hook callbacks use this to render messages
 *
 * @author    kwtSMS <support@kwtsms.com>
 * @copyright kwtSMS
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 */

class KwtsmsTemplateManager
{
    /**
     * Load a template by key and render it with placeholders.
     *
     * @param string   $templateKey Template key (e.g. 'order_placed_customer')
     * @param array    $placeholders Key-value pairs for replacement:
     *   {customer_name}, {order_ref}, {order_total}, {currency},
     *   {order_status}, {customer_email}, {product_name}, {product_ref},
     *   {stock_quantity}, {shop_name}
     * @param int|null $idLang Language ID for the customer. If Arabic, uses content_ar.
     *
     * @return string|null Rendered message or null if template not found
     */
    public static function render($templateKey, array $placeholders = array(), $idLang = null)
    {
        $template = self::getTemplate($templateKey);

        if (!$template) {
            KwtsmsLogger::debug('template', 'Template not found: ' . $templateKey);
            return null;
        }

        $content = self::selectLanguage($template, $idLang);

        if (empty($content)) {
            KwtsmsLogger::debug('template', 'Template content empty: ' . $templateKey);
            return null;
        }

        // Add {shop_name} automatically if not already in placeholders
        if (!isset($placeholders['{shop_name}'])) {
            $placeholders['{shop_name}'] = Configuration::get('PS_SHOP_NAME');
        }

        // Replace placeholders
        $message = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $content
        );

        return $message;
    }

    /**
     * Get a template row from the database.
     *
     * @param string $templateKey
     *
     * @return array|null
     */
    public static function getTemplate($templateKey)
    {
        $row = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'kwtsms_template`
             WHERE `template_key` = "' . pSQL($templateKey) . '"'
        );

        return $row ? $row : null;
    }

    /**
     * Get all templates.
     *
     * @return array
     */
    public static function getAllTemplates()
    {
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'kwtsms_template` ORDER BY `template_key` ASC'
        );

        return $rows ? $rows : array();
    }

    /**
     * Update a template's content.
     *
     * @param int    $idTemplate Template ID
     * @param string $contentEn  English content
     * @param string $contentAr  Arabic content (nullable)
     *
     * @return bool
     */
    public static function updateTemplate($idTemplate, $contentEn, $contentAr = null)
    {
        return Db::getInstance()->update('kwtsms_template', array(
            'content_en' => pSQL($contentEn),
            'content_ar' => $contentAr !== null ? pSQL($contentAr) : null,
            'date_upd' => date('Y-m-d H:i:s'),
        ), '`id_kwtsms_template` = ' . (int) $idTemplate);
    }

    /**
     * Select the correct language content from a template row.
     * If customer language is Arabic and content_ar is not empty, use Arabic.
     * Otherwise fall back to content_en.
     *
     * @param array    $template Template row from DB
     * @param int|null $idLang   Language ID
     *
     * @return string
     */
    private static function selectLanguage(array $template, $idLang = null)
    {
        if ($idLang) {
            $langIso = Language::getIsoById($idLang);
            if ($langIso === 'ar' && !empty($template['content_ar'])) {
                return $template['content_ar'];
            }
        }

        return $template['content_en'];
    }

    /**
     * Get available placeholders for a given template key.
     * Used to display reference in the Templates admin tab.
     *
     * @param string $templateKey
     *
     * @return array List of placeholder strings
     */
    public static function getPlaceholders($templateKey)
    {
        $map = array(
            'order_placed_customer' => array('{customer_name}', '{order_ref}', '{order_total}', '{currency}', '{shop_name}'),
            'order_placed_admin' => array('{customer_name}', '{order_ref}', '{order_total}', '{currency}', '{shop_name}'),
            'order_status_changed_customer' => array('{customer_name}', '{order_ref}', '{order_status}', '{shop_name}'),
            'new_customer_customer' => array('{customer_name}', '{shop_name}'),
            'new_customer_admin' => array('{customer_name}', '{customer_email}', '{shop_name}'),
            'payment_confirmed_customer' => array('{customer_name}', '{order_ref}', '{order_total}', '{currency}', '{shop_name}'),
            'payment_confirmed_admin' => array('{customer_name}', '{order_ref}', '{order_total}', '{currency}', '{shop_name}'),
            'low_stock_admin' => array('{product_name}', '{product_ref}', '{stock_quantity}', '{shop_name}'),
        );

        return isset($map[$templateKey]) ? $map[$templateKey] : array('{shop_name}');
    }
}
