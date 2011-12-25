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


class IsotopeEventHooks extends Frontend
{

   /**
   	* add callbacks to the DCA to display frontend and backend configuration
   	*/
	public function dcaConfig($strField, $arrData){
		if (TL_MODE == 'FE'){
			$arrData['options_callback']=array('IsotopeEventHooks','setProductOptions');
		}
		else{
			unset($arrData['attributes']['customer_defined']);
		}

		return $arrData;
	}
	
  /**
   	* constructs the FE options selection
   	*/
	public function setProductOptions($objItem){
		if (TL_MODE == 'FE')
		{
			$this->import('Database');
			$arrOptions = array();
	
			$objEvents = $this->Database->prepare("SELECT * FROM tl_iso_products_events WHERE pid=? ORDER BY startDate")->execute($objItem->id);
			
			while($objEvents->next()){
				if ($objEvents->startDate==$objEvents->endDate){
					$strDate = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objEvents->startDate);
				}
				else{
					$strDate = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objEvents->startDate) . ' - ' . $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objEvents->endDate);
				}
		
				$strTime = '';
				
				if ($objEvents->addTime)
				{
					if ($objEvents->startTime == $objEvents->endTime)
					{
						$strTime = $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objEvents->startTime);
					}
					else
					{
						$strTime = $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objEvents->startTime) . ' - ' . $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objEvents->endTime);
					}
				}
				if ($objEvents->startDate!=$objEvents->endDate){
					$strTime .= ', daily';
				}
				$label = $strDate . ', ' . $strTime;
				$arrOptions[] = $label;		
			}
			return $arrOptions;
		}
	}
	
  	/**
   	* product save callback
   	*/
	public function onProductSave($dc){
		$this->import('Database');
		$strType = $dc->activeRecord->type;
		
		$objType = $this->Database->execute("SELECT * FROM tl_iso_producttypes WHERE id = $strType");
		
		if ($objType->class == 'event')
		{
			$objAttr = $this->Database->execute("SELECT * FROM tl_iso_attributes WHERE type = 'dateTimePicker'");
			
			$arrEvents = array();

			while($objAttr->next()){
			
				$strField = $objAttr->field_name;
				
				$arrData = deserialize($dc->activeRecord->$strField);
				
				if($arrData == NULL){
					continue;
				}

				foreach ($arrData as $event){
					$arrEvents[] = $event;
				}
			}
			
			
			$arrTimes = array();
			
			foreach ($arrEvents as $event){	
				$dates = array();
				$times = array();
				foreach ($event as $varInput){	
					$objDate = new Date();	
					if (preg_match('~^'. $objDate->getRegexp($GLOBALS['TL_CONFIG']['dateFormat']) .'$~i', $varInput))
					{
						$dates[] = $varInput;
					}
										
					if (preg_match('~^'. $objDate->getRegexp($GLOBALS['TL_CONFIG']['timeFormat']) .'$~i', $varInput))
					{
						$times[] = $varInput;
					}
				}
				if (sizeof($dates)+sizeof($times) > 0){
					$arrTimes[] = array('dates'=>$dates, 'times'=>$times);
				}
			}
						
			foreach ($arrTimes as $event){
				$addTime = true;
				$dates = $event['dates'];
				$times = $event['times'];
			
				if(sizeof($dates)==2){
					$startDate = strtotime($dates[0]);
					$endDate = strtotime($dates[1]); 
				}
				elseif(sizeof($dates)==1){
					$startDate = strtotime($dates[0]);
					$endDate = strtotime($dates[0]); 
				}
				else{
					$_SESSION['TL_ERROR'][] = $GLOBALS['TL_LANG']['EVENT']['no_dates'];
					continue;
				}
				
				if(sizeof($times)==2){
					$startTime = strtotime(date('Y-m-d', $startDate) . ' ' . $times[0]);
					$endTime = strtotime(date('Y-m-d', $endDate) . ' ' . $times[1]);
				}
				elseif(sizeof($times)==1){
					$startTime = strtotime(date('m-d-Y', $startDate) . ' ' . $times[0]);
					$endTime = strtotime(date('m-d-Y', $endDate) . ' ' . $times[0]); 
				}
				else{
					$startTime = '';
					$endTime = ''; 
					$addTime = '';
				}
				
				if($startDate==$endDate && $endTime < $startTime){
					$_SESSION['TL_ERROR'][] = $GLOBALS['TL_LANG']['EVENT']['end_time_prior'];
					continue;
				}
				if($startDate > $endDate){
					$_SESSION['TL_ERROR'][] = $GLOBALS['TL_LANG']['EVENT']['end_date_prior'];
					continue;
				}
				
				$pid = $dc->activeRecord->id;

				//Check for existing
				$objCheck = $this->Database->execute("SELECT * FROM tl_iso_products_events WHERE pid=$pid");
				
				$arrSet = array
				(
					'startDate'	=> $startDate,
					'endDate'	=> $endDate,
					'startTime'	=> $startTime,
					'endTime'	=> $endTime,
					'addTime'	=> $addTime,
					'pid'		=> $pid
				);
				
				if($objCheck->numRows)
				{
					$objAttr = $this->Database->prepare("UPDATE tl_iso_products_events %s WHERE pid=?")
									->set($arrSet)
									->execute($pid);
				}
				else
				{
					$objAttr = $this->Database->prepare("INSERT INTO tl_iso_products_events %s")
									->set($arrSet)
									->execute();
				}
			}
		}
	}
	
	/**
	 * Callback for isoButton Hook
	 * @param array
	 * @return array
	 */
	public function eventButtons($arrButtons)
	{
		$arrButtons['add_to_eventcart'] = array('label'=>$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_eventcart'], 'callback'=>array('IsotopeEventHooks', 'addToEventCart'));
		return $arrButtons;
	}
	
	/**
	 * Callback for add_to_cart button
	 * @param object
	 * @param object
	 */
	public function addToEventCart($objProduct, $objModule=null)
	{
		$this->import('Isotope');
		$intQuantity = ($objModule->iso_use_quantity && intval($this->Input->post('quantity_requested')) > 0) ? intval($this->Input->post('quantity_requested')) : 1;

		if ($this->Isotope->EventCart->addProduct($objProduct, $intQuantity) !== false)
		{
			$_SESSION['ISO_CONFIRM'][] = $GLOBALS['TL_LANG']['MSC']['addedToCart'];
			$this->jumpToOrReload($objModule->iso_addProductJumpTo);
		}
	}
	
}