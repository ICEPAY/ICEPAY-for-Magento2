# ICEPAY module for Magento 2

This module is a payment gateway for Magento 2 webshops. It allows you to integrate the ICEPAY payment gateway into your Magento 2 webshop. Currently, the following methods are supported:

- Bancontact
- Credit Cards
- EPS
- iDEAL
- Online Überweisen
- PayPal
- SOFORT

## Installation

To install the module, you can use composer. Run the following command in your Magento 2 root directory:

```bash
composer require icepay/magento2
php bin/magento module:enable Icepay_Payment
```

## Configuration

All configuration can be found under Stores -> Configuration -> Sales -> Payment -> ICEPAY. Under this group you can enable and disable each method individually.

## Support

Bugs, feature requests and questions can be reported by sending an email to info@icepay.com.

## Developed by Control Alt Delete

This extension has been developed by Control Alt Delete. For more information, please visit [our website](https://www.controlaltdelete.dev/?utm_source=github&utm_medium=readme&utm_campaign=icepay).
