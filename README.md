<p align="center">
  <img src="https://www.kwtsms.com/images/kwtsms_logo_60.png" alt="kwtSMS Logo" height="60">
</p>

<h1 align="center">kwtSMS for PrestaShop</h1>

<p align="center">
  <a href="https://github.com/boxlinknet/kwtsms-prestashop/releases"><img src="https://img.shields.io/github/v/release/boxlinknet/kwtsms-prestashop?label=version&color=FFA200" alt="Latest Release"></a>
  <img src="https://img.shields.io/badge/PrestaShop-8.0%E2%80%939.x-5D3FD3" alt="PrestaShop 8.0-9.x">
  <img src="https://img.shields.io/badge/PHP-%3E%3D7.4-777BB4?logo=php&logoColor=white" alt="PHP >= 7.4">
  <img src="https://img.shields.io/badge/license-AFL--3.0-blue" alt="License AFL-3.0">
  <a href="https://www.kwtsms.com"><img src="https://img.shields.io/badge/kwtsms.com-FFA200?logo=data:image/svg+xml;base64,&logoColor=white" alt="kwtsms.com"></a>
</p>

<p align="center">
  Send SMS notifications to customers and admins via the <a href="https://www.kwtsms.com">kwtSMS</a> gateway.
</p>

---

## Features

- **Order confirmations**: notify customers when an order is placed
- **Status updates**: SMS on shipping, delivery, cancellation, and refund
- **New customer alerts**: admin gets notified on new registrations
- **Payment confirmations**: SMS when payment is received
- **Low stock alerts**: admin notifications when products run low
- **Custom templates**: per-status message templates with variable placeholders
- **SMS log**: full history of sent messages in the admin panel
- **Cron sync**: daily automated tasks via front controller endpoint

## Requirements

| Requirement  | Version       |
|-------------|---------------|
| PrestaShop  | 8.0.0 - 9.x  |
| PHP         | >= 7.4        |

## Installation

1. Download the latest release zip from [Releases](https://github.com/boxlinknet/kwtsms-prestashop/releases)
2. In your PrestaShop admin, go to **Modules > Module Manager**
3. Click **Upload a module** and select the zip file
4. Configure the module with your kwtSMS API credentials

## Configuration

After installation, navigate to the module configuration page:

1. Enter your **kwtSMS username** and **password**
2. Set your **Sender ID** (must be registered with kwtSMS)
3. Enable the notification types you want (order updates, new customers, etc.)
4. Customize message templates using available placeholders

## Getting a kwtSMS Account

Sign up at [kwtsms.com](https://www.kwtsms.com) to get API credentials. The service covers Kuwait mobile numbers with reliable delivery and Arabic language support.

## License

[Academic Free License 3.0 (AFL-3.0)](https://opensource.org/licenses/AFL-3.0)
