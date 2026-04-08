<p align="center">
  <img src="https://www.kwtsms.com/images/kwtsms_logo_60.png" alt="kwtSMS" height="60">
</p>

<h1 align="center">kwtSMS for PrestaShop</h1>

<p align="center">
  SMS notifications and alerts for your PrestaShop store, powered by the
  <a href="https://www.kwtsms.com">kwtSMS</a> gateway.
</p>

<p align="center">
  <a href="https://github.com/boxlinknet/kwtsms-prestashop/releases/latest"><img src="https://img.shields.io/github/v/release/boxlinknet/kwtsms-prestashop?style=flat-square&label=version&color=FFA200" alt="Version"></a>
  <img src="https://img.shields.io/badge/PrestaShop-8.0--9.x-251B5B?style=flat-square&logo=prestashop&logoColor=white" alt="PrestaShop 8.0 - 9.x">
  <img src="https://img.shields.io/badge/PHP-%3E%3D7.4-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP >= 7.4">
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-AFL--3.0-blue?style=flat-square" alt="License"></a>
  <a href="https://www.kwtsms.com"><img src="https://img.shields.io/badge/kwtsms.com-FFA200?style=flat-square" alt="kwtsms.com"></a>
</p>

---

## About kwtSMS

[kwtSMS](https://www.kwtsms.com) is a Kuwait-based SMS gateway providing reliable message delivery to Kuwaiti mobile numbers. It supports Arabic and English content, approved Sender IDs, OTP flows, and a simple HTTP API. This module connects your PrestaShop store to the kwtSMS platform so you can send automated SMS notifications without writing any code.

## Features

| Notification | Recipient | Trigger |
|---|---|---|
| Order placed | Customer + Admin | New order validated |
| Order status changed | Customer | Shipping, delivery, cancellation, refund |
| New customer | Customer + Admin | Account registration |
| Payment confirmed | Customer + Admin | Payment received |
| Low stock alert | Admin | Stock falls below threshold |

Additional capabilities:

- **Bilingual templates** with automatic language selection (English / Arabic)
- **Custom placeholders** per notification type
- **SMS log** with full send history in the back office
- **Cron endpoint** for daily automated sync tasks
- **Connection test** to verify API credentials from the admin panel

## Requirements

| Dependency | Version |
|---|---|
| PrestaShop | 8.0.0 - 9.x |
| PHP | >= 7.4 |
| kwtSMS account | [kwtsms.com](https://www.kwtsms.com) |

## Installation

1. Download `kwtsms.zip` from the [latest release](https://github.com/boxlinknet/kwtsms-prestashop/releases/latest)
2. In PrestaShop admin, go to **Modules > Module Manager**
3. Click **Upload a module** and select the zip
4. The module appears under **Advertising & Marketing**

## Configuration

1. Enter your **kwtSMS username** and **password**
2. Set your approved **Sender ID**
3. Toggle each notification type on or off
4. Edit message templates using the placeholders below

## Template Placeholders

| Placeholder | Available in | Description |
|---|---|---|
| `{customer_name}` | All templates | Customer full name |
| `{order_ref}` | Order placed, status changed, payment | Order reference number |
| `{order_total}` | Order placed, payment | Order total amount |
| `{currency}` | Order placed, payment | Currency code |
| `{order_status}` | Status changed | New status name |
| `{customer_email}` | New customer (admin) | Customer email address |
| `{product_name}` | Low stock | Product name |
| `{product_ref}` | Low stock | Product reference / SKU |
| `{stock_quantity}` | Low stock | Current stock level |
| `{shop_name}` | All templates | Inserted automatically |

## Module Structure

```
kwtsms/
  kwtsms.php                 # Main module: hooks, install, config routing
  config.xml                 # Module metadata
  composer.json              # Dependencies (kwtsms/kwtsms-php)
  classes/
    KwtsmsGateway.php        # API wrapper (connect, send, sync)
    KwtsmsSender.php         # Send pipeline with guards and batching
    KwtsmsTemplateManager.php# Template rendering and placeholder replacement
    KwtsmsInstaller.php      # Install / uninstall logic and seed data
    KwtsmsLogger.php         # SMS logging and dashboard stats
    KwtsmsCron.php           # Daily sync tasks
  controllers/
    admin/AdminKwtsmsController.php  # Back-office UI (tabs)
    front/cron.php                   # Public cron endpoint
  views/
    templates/admin/         # Smarty templates for each admin tab
    css/admin.css            # Back-office styles
    js/admin.js              # Tab interaction logic
  sql/
    install.php              # Database schema
    uninstall.php            # Cleanup queries
  translations/
    en.php                   # English strings
    ar.php                   # Arabic strings
```

## License

[Academic Free License 3.0 (AFL-3.0)](https://opensource.org/licenses/AFL-3.0)
