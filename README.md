# Magento Payment Plugin

The repository provides a credit card payment plugin for Magento v2.4.

Additionally, a white-label script is provided for rebranding the plugin.

The plugin supports credit card payments and the following features:

- Perform payment on the storefront (hosted payment page and PaymentJS)
- Perform payments from the admin area (PaymentJS)
- If activated, support for COF TX by storing tokenized credit card data on a customer account (Magento Vault support)
- Configure whether an authorize or debit transaction shall be performed (authorization require manual captures from the store admin)
- Support for capturing, voiding and refunding transactions
- 3DS support


## System Requirements

The plugin targets Magento v2.4 Open Source.

The plugin itself requires the PHP `ext-curl` to be installed, which is already
a system dependency for
[Magento](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements.html).


## Installation

> Note: unless specified otherwise, all file paths are **relative** from
> Magento's root installation.


### Plugin installation

The plugin's, source code must be copied (unzipped) in the `app/code` directory.
Please ensure, the proper file permissions and ownership, according to your
server's setup.

```bash
bin/magento module:enable Pgc_Pgc # Replacing Pgc_Pgc with your whitelabel name
bin/magento setup:upgrade
bin/magento setup:di:compile
```


### Plugin configuration

There are 2 options for configuring the plugin.
After changing the configuration, clearing Magento's config cache is required.


#### Via Magento's admin webinterface (simple)

Goto: `Stores -> Configuration -> Sales -> Payment Methods`


#### Via Magento's CLI (advanced)

Refer to the `docker/configure.sh` script.
For encrypted parameters, the [n98-magerun2 utility](https://github.com/netz98/n98-magerun2) is required.
Config paths may be deduced from `etc/config.xml`.


## Translations

The plugin allows translating certain text blocks.
If you like to provide translations for a certain language, you'll have to
provide dictionary files - see `i18n` directory.

For more details on how Magento handles translations, refer to Magento's
documentation
([see "Translations overview"](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/translations/xlate.html)
and
["Use translation dictionary to customize strings"](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/translations/theme_dictionary.html)).

> Note: the plugin makes no guarantees for about backwards compatibility of
> translation keys for future releases.


## White-labeling

For white-labeling the code base, simply run.

```bash
./whitelabelbuild \
    --vendor-name="Acme Payments" \
    --package-name="Payment Plugin" \
    --production-host="gateway.acme.com" \
    --sandbox-host="acme.paymentsandbox.cloud" \
    --vault-host="secure.acme.com"
```

The script will generate a white-labeled zip file in the `dist/` directory.
The plugin's identifier for the sample above is `AcmePayments_PaymentPlugin`.


## Debugging, reporting and support

> Note: the plugin's source code is provided for free.
> For support, please contact your customer success manager or sales@ixopay.com

The plugin has a debug mode, which generates verbose logs. The debug mode may be
enabled from plugins configuration page.

When reporting issues with the plugin, please provide the following information:

1) The shop's system information:

- Information about the operation system (output of `uname -a` and `lsb_release -a`).
- Information about the PHP system (output of `php -i`)
- Information about the Magento shop (output of `bin/magento --version`)


2) the relevant logfiles (entire files are not needed - just the relevant sections)

> By default, Magento stores logs in `var/log` directory. This may be changed by
> the shop admin, though.

- debug.log
- exception.log
- payment.log
- system.log


3) Additional context information

- date/time when the bug occurred
- step-by-step instructions for reproduction
- revision (commit hash), used for building/white-labeling the plugin.
- if processing-related, IXOPAY's TX ID, connector/merchant GUID, etc.
- Screen shots from the Magento shop and browser's console logs (if applicable or UI related)
- etc...
