/**
 * Expercash Expercash
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category    Expercash
 * @package     Expercash_Expercash
 * @copyright   Copyright (c) 2015 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
var ExpercashOpc = Class.create(Checkout, {
    initialize: function($super, accordion, urls) {
        this.addressesUrl = urls.addresses;
        this.validateAddressesUrl = urls.validateAddresses;
        this.onAddressLoad = this.fillForm.bindAsEventListener(this);
        this.shippingMethodsUrl = urls.shippingMethods;
        $super(accordion, urls);
    },
    setGuestCheckout: function() {
        $('login:guest').checked = true;
        this.setMethod();
    },
    fillForm: function(transport) {
        var billingElementValues = {};
        var shippingElementValues = {};
        var steps = {};

        if (transport && transport.responseText){
            try{
                steps = eval('(' + transport.responseText + ')');
                billingElementValues = steps.billing;
                shippingElementValues = steps.shipping;
            }
            catch (e) {
                billingElementValues = {};
                shippingElementValues = {};
            }
        }

        var arrBillingElements = billingForm.form.getElements().sortBy(function(element) {
            return element.id;
        });
        for (var elemIndex in arrBillingElements) {
            if (arrBillingElements[elemIndex].id) {
                var fieldName = arrBillingElements[elemIndex].id.replace(/^billing:/, '');

                if (arrBillingElements[elemIndex].type == 'radio') {
                    arrBillingElements[elemIndex].checked = billingElementValues[fieldName] ? billingElementValues[fieldName] : '';
                } else {
                    arrBillingElements[elemIndex].value = billingElementValues[fieldName] ? billingElementValues[fieldName] : '';
                }

                if (fieldName == 'country_id') {
                    billingRegionUpdater.update();
                }
            }
        }
        var arrShippingElements = shippingForm.form.getElements().sortBy(function(element) {
            return element.id;
        });
        for (var elemIndex in arrShippingElements) {
            if (arrShippingElements[elemIndex].id) {
                var fieldName = arrShippingElements[elemIndex].id.replace(/^shipping:/, '');
                arrShippingElements[elemIndex].value = shippingElementValues[fieldName] ? shippingElementValues[fieldName] : '';
                if (fieldName == 'country_id') {
                    shippingRegionUpdater.update();
                }
            }
        }

        // validate Address
        this.validateAddresses();
    },
    validateAddresses: function() {
        var section = 'shipping_method';
        var request = new Ajax.Request(this.validateAddressesUrl, {
            method: 'get',
            onFailure: this.ajaxFailure.bind(this),
            onSuccess: function(transport) {
                if (transport.responseJSON.error === true) {
                    section = transport.responseJSON.goto_section;

                    // call form validator so the fields get highlighted
                    // section needs to be opened prior to validation
                    if (section == 'shipping') {
                        this.gotoSection(section, false);
                        shippingForm.validator.validate();
                    } else if (section == 'billing') {
                        this.gotoSection(section, false);
                        billingForm.validator.validate();
                    }

                } else {
                    // in case everything is fine, shipping address step is skipped, reload it
                    this.reloadProgressBlock('shipping');
                    this.fetchShippingMethods();
                }

                this.gotoSection(section, true);
            }.bind(this)
        });
    },
    fetchAddresses: function() {
        var request = new Ajax.Request(this.addressesUrl, {
            method: 'get',
            onFailure: this.ajaxFailure.bind(this),
            onSuccess: this.onAddressLoad
        });
    },
    fetchShippingMethods: function() {
        var request = new Ajax.Request(this.shippingMethodsUrl, {
            method: 'get',
            onFailure: this.ajaxFailure.bind(this),
            onSuccess: shipping.onSave
        });
    }
});
