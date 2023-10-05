## Trinos - Postcode.nl Magento 2 module

Postcode.nl integration for Hyvä Checkout.

## Installation

1. Add the checkout repository to the Magento `composer.json`
    ```sh
    composer config repositories.trinos-nl/magento2-postcode-nl git git@github.com:trinos-nl/magento2-postcode-nl.git
    ```

2. Require the `trinos-nl/magento2-postcode-nl` packages using the `dev-main` branch version:
    ```sh
    composer require --prefer-source 'trinos-nl/magento2-postcode-nl:dev-main'
    ```

3. Run `bin/magento setup:upgrade`

## Additional steps

After installing the module, there are some additional steps you need to do.

### 1. Configure the module

Go to `Stores > Configuration > Sales > Postcode.nl Api` and configure the module.
Enable the module and fill in your API key.

### 2. Checkout configuration

Go to `Stores > Configuration > Hyvä Themes > Checkout` and configure `postcodenl_manual_mode` field.
This field is responsible for the manual mode checkbox of the postcode check.

Our recommendation is to set this fields to the following order and configuration:

| Attribute              | Enabled | Required | Length |
|------------------------|---------|----------|--------|
| country_id             | Yes     | Yes      | 100%   |
| postcode               | Yes     | Yes      | 50%    |
| street.1               | Yes     | Yes      | 25%    |
| street.2               | Yes     | No       | 25%    |
| postcodenl_manual_mode | Yes     | No       | 100%   |
| street.0               | Yes     | Yes      | 100%   |
| city                   | Yes     | Yes      | 100%   |
