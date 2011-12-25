<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
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
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */



/**
 * Table tl_iso_attributes
 */

$GLOBALS['TL_DCA']['tl_iso_attributes']['palettes']['dateTimePicker'] = '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{config_legend},dateWizardStart,dateWizardEnd,timeWizardStart,timeWizardEnd,mandatory,multiple,size;{search_filters_legend},fe_filter,fe_sorting,be_filter';

$GLOBALS['TL_DCA']['tl_iso_attributes']['palettes']['dateTimePickervariant_option'] ='{attribute_legend},name,field_name,type,legend,variant_option;{description_legend:hide},description;{search_filters_legend},fe_filter,fe_sorting,be_filter';

$GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['dateWizardStart'] = array
															(
																'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['dateWizardStart'],
																'exclude'				=> true,
																'inputType'				=> 'checkbox',
																'eval'					=> array('tl_class'=>'w50'),
															);
															
															
$GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['timeWizardStart'] = array
															(
																'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['timeWizardStart'],
																'exclude'				=> true,
																'inputType'				=> 'checkbox',
																'eval'					=> array('tl_class'=>'w50'),
															);		
															
$GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['dateWizardEnd'] = array
															(
																'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['dateWizardEnd'],
																'exclude'				=> true,
																'inputType'				=> 'checkbox',
																'eval'					=> array('tl_class'=>'w50'),
															);
															
															
$GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['timeWizardEnd'] = array
															(
																'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['timeWizardEnd'],
																'exclude'				=> true,
																'inputType'				=> 'checkbox',
																'eval'					=> array('tl_class'=>'w50'),
															);																										