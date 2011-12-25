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
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]			= 'iso_eventcheckout_method';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_eventcheckout']				= '{title_legend},name,headline,type;{config_legend},form,iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions;{redirect_legend},iso_forward_review,orderCompleteJumpTo;{template_legend},iso_includeMessages,iso_mail_customer,iso_mail_admin,iso_sales_email,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_eventcheckoutmember']		= '{title_legend},name,headline,type;{config_legend},form,iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions,iso_addToAddressbook;{redirect_legend},iso_forward_review,orderCompleteJumpTo,iso_login_jumpTo;{template_legend},iso_includeMessages,iso_mail_customer,iso_mail_admin,iso_sales_email,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_eventcheckoutguest']		= '{title_legend},name,headline,type;{config_legend},form,iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions;{redirect_legend},iso_forward_review,orderCompleteJumpTo;{template_legend},iso_includeMessages,iso_mail_customer,iso_mail_admin,iso_sales_email,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_eventcheckoutboth']			= '{title_legend},name,headline,type;{config_legend},form,iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions,iso_addToAddressbook;{redirect_legend},iso_forward_review,orderCompleteJumpTo;{template_legend},iso_includeMessages,iso_login_jumpTo,iso_mail_customer,iso_mail_admin,iso_sales_email,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_eventcalendar']    = '{title_legend},name,headline,type;{config_legend},iso_Event,cal_calendar, cal_noSpan,cal_startDay;{redirect_legend},jumpTo;{template_legend:hide},cal_ctemplate;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_eventcart']					= '{title_legend},name,headline,type;{redirect_legend},iso_cart_jumpTo,iso_checkout_jumpTo;{template_legend},iso_cart_layout,iso_continueShopping,iso_includeMessages,iso_emptyMessage;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space'; 
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_eventreader']		= '{title_legend},name,headline,type;{config_legend},iso_use_quantity;{redirect_legend},iso_addProductJumpTo;{template_legend:hide},iso_includeMessages,iso_reader_layout,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space'; 
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_eventlister']   = '{title_legend},name,headline,type;{config_legend},iso_Event,cal_calendar,cal_noSpan,cal_ignoreDynamic,cal_format,cal_order,cal_limit,perPage;{redirect_legend},jumpTo;{template_legend:hide},cal_template,imgSize,tag_filter;{showtags_legend},tag_ignore;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_event']				= '{title_legend},name,headline,type;{config_legend},iso_Event,form,iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions;{redirect_legend},orderCompleteJumpTo;{template_legend},iso_includeMessages,iso_mail_customer,iso_mail_admin,iso_sales_email,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_eventmember']		= '{title_legend},name,headline,type;{config_legend},iso_Event,form,iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions,iso_addToAddressbook;{redirect_legend},orderCompleteJumpTo,iso_login_jumpTo;{template_legend},iso_includeMessages,iso_mail_customer,iso_mail_admin,iso_sales_email,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_eventguest']		= '{title_legend},name,headline,type;{config_legend},iso_Event,form,iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions;{redirect_legend},orderCompleteJumpTo;{template_legend},iso_includeMessages,iso_mail_customer,iso_mail_admin,iso_sales_email,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_eventboth']			= '{title_legend},name,headline,type;{config_legend},iso_Event,form,iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions,iso_addToAddressbook;{redirect_legend},orderCompleteJumpTo;{template_legend},iso_includeMessages,iso_login_jumpTo,iso_mail_customer,iso_mail_admin,iso_sales_email,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


$GLOBALS['TL_DCA']['tl_module']['fields']['iso_Event'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_module']['iso_Event'],
	'exclude'					=> true,
	'inputType'					=> 'select',
	'foreignKey' 				=> 'tl_iso_producttypes.name',
	'eval'						=> array('mandatory'=>true, 'multiple'=>true)
);
