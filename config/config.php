<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['isotope']['iso_eventcheckout'] = 'ModuleEventCheckout';
$GLOBALS['FE_MOD']['isotope']['iso_eventcart'] = 'ModuleEventCart';
$GLOBALS['FE_MOD']['isotope']['iso_eventcalendar'] = 'ModuleEventCalendars';
$GLOBALS['FE_MOD']['isotope']['iso_eventreader'] = 'ModuleIsoEventReader';
$GLOBALS['FE_MOD']['isotope']['iso_eventlister'] = 'ModuleEventLister';
/**
 * Hooks
 */

$GLOBALS['ISO_PRODUCT']['event'] = array('class'	=> 'IsotopeEventProduct');
$GLOBALS['ISO_HOOKS']['buttons'][] = array('IsotopeEventHooks', 'eventButtons');
$GLOBALS['ISO_ATTR']['dateTimePicker'] = array( 'sql'		=> "blob NULL",
												'backend'	=> 'datetimeWizard',
												'frontend'  => 'select',
												'callback'	=> array(array('IsotopeEventHooks', 'dcaConfig')));



/**
 * Step callbacks for checkout module
 */
$GLOBALS['EVENT_CHECKOUT_STEPS'] = array
(
	'form' => array
	(
		array('ModuleEventCheckout', 'getFormInterface'),
	),
	'address' => array
	(
		array('ModuleEventCheckout', 'getBillingAddressInterface'),
		array('ModuleEventCheckout', 'getShippingAddressInterface'),
	),
	'shipping' => array
	(
		array('ModuleEventCheckout', 'getShippingModulesInterface'),
	),
	'payment' => array
	(
		array('ModuleEventCheckout', 'getPaymentModulesInterface'),
	),
	'review' => array
	(
		array('ModuleEventCheckout', 'getOrderReviewInterface'),
		array('ModuleEventCheckout', 'getOrderConditionsInterface')
	),
);

/**
 * Checkout surcharge calculation callbacks
 */
$GLOBALS['EVENT_HOOKS']['checkoutSurcharge'][] = array('EventCart', 'getShippingSurcharge');
$GLOBALS['EVENT_HOOKS']['checkoutSurcharge'][] = array('EventCart', 'getPaymentSurcharge');


?>