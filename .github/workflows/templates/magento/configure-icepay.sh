#!/bin/bash

set -e

if [ -z "$ICEPAY_MERCHANT_ID" ]; then
    echo "Variable \$ICEPAY_MERCHANT_ID is not set"
    exit 1
fi

if [ -z "$ICEPAY_MERCHANT_SECRET" ]; then
    echo "Variable \$ICEPAY_MERCHANT_SECRET is not set"
    exit 1
fi

bin/magento config:set payment/icepay/active 1
bin/magento config:set payment/icepay/merchant_id "$ICEPAY_MERCHANT_ID"
magerun2 config:store:set payment/icepay/merchant_secret "$ICEPAY_MERCHANT_SECRET" --encrypt
bin/magento config:set payment/icepay/debug 1

# The methods ship disabled; the specs cover every method the merchant has
# enabled at Icepay, so turn them all on and let the availability gate decide.
for METHOD in bancontact creditcard eps ideal onlineueberweisen paypal sofort; do
    bin/magento config:set "payment/icepay/$METHOD/active" 1
done

# The specs select the country by its Dutch label ("Nederland"), which only
# appears under a Dutch locale. It also renames "Vatican City" to
# "Vaticaanstad", which is what keeps the City field locator unambiguous.
bin/magento config:set general/locale/code nl_NL
bin/magento config:set general/country/default NL

# Icepay settles in euro; the sample data ships a dollar store.
bin/magento config:set currency/options/base EUR
bin/magento config:set currency/options/default EUR
bin/magento config:set currency/options/allow EUR

bin/magento cache:flush config
