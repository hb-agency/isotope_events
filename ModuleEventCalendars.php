<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
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
 * @copyright  Leo Feyer 2005-2011
 * @author     Leo Feyer <http://www.contao.org>
 * @package    Calendar
 * @license    LGPL
 * @filesource
 */


/**
 * Class ModuleCalendar
 *
 * Front end module "calendar".
 * @copyright  Leo Feyer 2005-2011
 * @author     Leo Feyer <http://www.contao.org>
 * @package    Controller
 */
class ModuleEventCalendars extends Events
{

	/**
	 * Current date object
	 * @var integer
	 */
	protected $Date;

	/**
	 * Redirect URL
	 * @var string
	 */
	protected $strLink;

	/**
	 * Template
	 * @var string
	 */

	protected $EventCart;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_event';

	/**
	 * Form ID
	 * @var string
	 */
	protected $strFormId = 'iso_mod_iso_event';

	public function __construct($objModule, $strColumn='main')
	{
		parent::__construct($objModule, $strColumn);
		
		$this->import('Database');
		
		$this->strUrl = preg_replace('/\?.*$/i', '', $this->Environment->request);

		// Get current "jumpTo" page
		$objPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
								  ->limit(1)
								  ->execute($this->jumpTo);

		if ($objPage->numRows)
		{
			$this->strLink = $this->generateFrontendUrl($objPage->row());
		}
		else
		{
			$this->strLink = $this->strUrl;
		}
		
		$this->iso_arrEventIDs = deserialize($this->iso_Event);
		$this->cal_calendar = $this->sortOutProtected(deserialize($this->cal_calendar, true));	
		
	}
	

	/**
	 * Do not show the module if no calendar has been selected
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### EVENT CALENDAR ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		return parent::generate();
	}


	/**
	 * Generate module
	 */
	protected function compile()
	{
		// Respond to month
		if ($this->Input->get('month'))
		{
			$this->Date = new Date($this->Input->get('month'), 'Ym');
		}

		// Respond to day
		elseif ($this->Input->get('day'))
		{
			$this->Date = new Date($this->Input->get('day'), 'Ymd');
		}

		// Fallback to today
		else
		{
			$this->Date = new Date();
		}

		$intYear = date('Y', $this->Date->tstamp);
		$intMonth = date('m', $this->Date->tstamp);

		$objTemplate = new FrontendTemplate(($this->cal_ctemplate ? $this->cal_ctemplate : 'cal_default'));

		$objTemplate->intYear = $intYear;
		$objTemplate->intMonth = $intMonth;

		// Previous month
		$prevMonth = ($intMonth == 1) ? 12 : ($intMonth - 1);
		$prevYear = ($intMonth == 1) ? ($intYear - 1) : $intYear;
		$lblPrevious = $GLOBALS['TL_LANG']['MONTHS'][($prevMonth - 1)] . ' ' . $prevYear;

		$objTemplate->prevHref = $this->strUrl . ($GLOBALS['TL_CONFIG']['disableAlias'] ? '?id=' . $this->Input->get('id') . '&amp;' : '?') . 'month=' . $prevYear . str_pad($prevMonth, 2, 0, STR_PAD_LEFT);
		$objTemplate->prevTitle = specialchars($lblPrevious);
		$objTemplate->prevLink = $GLOBALS['TL_LANG']['MSC']['cal_previous'] . ' ' . $lblPrevious;
		$objTemplate->prevLabel = $GLOBALS['TL_LANG']['MSC']['cal_previous'];

		// Current month
		$objTemplate->current = $GLOBALS['TL_LANG']['MONTHS'][(date('m', $this->Date->tstamp) - 1)] .  ' ' . date('Y', $this->Date->tstamp);

		// Next month
		$nextMonth = ($intMonth == 12) ? 1 : ($intMonth + 1);
		$nextYear = ($intMonth == 12) ? ($intYear + 1) : $intYear;
		$lblNext = $GLOBALS['TL_LANG']['MONTHS'][($nextMonth - 1)] . ' ' . $nextYear;

		$objTemplate->nextHref = $this->strUrl . ($GLOBALS['TL_CONFIG']['disableAlias'] ? '?id=' . $this->Input->get('id') . '&amp;' : '?') . 'month=' . $nextYear . str_pad($nextMonth, 2, 0, STR_PAD_LEFT);
		$objTemplate->nextTitle = specialchars($lblNext);
		$objTemplate->nextLink = $lblNext . ' ' . $GLOBALS['TL_LANG']['MSC']['cal_next'];
		$objTemplate->nextLabel = $GLOBALS['TL_LANG']['MSC']['cal_next'];

		// Set week start day
		if (!$this->cal_startDay)
		{
			$this->cal_startDay = 0;
		}

		$objTemplate->days = $this->compileDays();
		$objTemplate->weeks = $this->compileWeeks();
		$objTemplate->substr = $GLOBALS['TL_LANG']['MSC']['dayShortLength'];

		$this->Template->calendar = $objTemplate->parse();
	}


	/**
	 * Return the week days and labels as array
	 * @return array
	 */
	protected function compileDays()
	{
		$arrDays = array();

		for ($i=0; $i<7; $i++)
		{
			$intCurrentDay = ($i + $this->cal_startDay) % 7;
			$arrDays[$intCurrentDay] = $GLOBALS['TL_LANG']['DAYS'][$intCurrentDay];
		}

		return $arrDays;
	}


	/**
	 * Return all weeks of the current month as array
	 * @return array
	 */
	protected function compileWeeks()
	{
		$intDaysInMonth = date('t', $this->Date->monthBegin);
		$intFirstDayOffset = date('w', $this->Date->monthBegin) - $this->cal_startDay;

		if ($intFirstDayOffset < 0)
		{
			$intFirstDayOffset += 7;
		}

		$intColumnCount = -1;
		$intNumberOfRows = ceil(($intDaysInMonth + $intFirstDayOffset) / 7);
		$arrAllEvents = $this->getAllEvents($this->iso_arrEventIDs, $this->cal_calendar, $this->Date->monthBegin, $this->Date->monthEnd);
		
		$arrDays = array();

		// Compile days
		for ($i=1; $i<=($intNumberOfRows * 7); $i++)
		{
			$intWeek = floor(++$intColumnCount / 7);
			$intDay = $i - $intFirstDayOffset;
			$intCurrentDay = ($i + $this->cal_startDay) % 7;

			$strWeekClass = 'week_' . $intWeek;
			$strWeekClass .= ($intWeek == 0) ? ' first' : '';
			$strWeekClass .= ($intWeek == ($intNumberOfRows - 1)) ? ' last' : '';

			$strClass = ($intCurrentDay < 2) ? ' weekend' : '';
			$strClass .= ($i == 1 || $i == 8 || $i == 15 || $i == 22 || $i == 29 || $i == 36) ? ' col_first' : '';
			$strClass .= ($i == 7 || $i == 14 || $i == 21 || $i == 28 || $i == 35 || $i == 42) ? ' col_last' : '';

			// Empty cell
			if ($intDay < 1 || $intDay > $intDaysInMonth)
			{
				$arrDays[$strWeekClass][$i]['label'] = '&nbsp;';
				$arrDays[$strWeekClass][$i]['class'] = 'days empty' . $strClass ;
				$arrDays[$strWeekClass][$i]['events'] = array();

				continue;
			}

			$intKey = date('Ym', $this->Date->tstamp) . ((strlen($intDay) < 2) ? '0' . $intDay : $intDay);
			$strClass .= ($intKey == date('Ymd')) ? ' today' : '';

			// Mark the selected day (see #1784)
			if ($intKey == $this->Input->get('day'))
			{
				$strClass .= ' selected';
			}

			// Inactive days
			if (empty($intKey) || !isset($arrAllEvents[$intKey]))
			{
				$arrDays[$strWeekClass][$i]['label'] = $intDay;
				$arrDays[$strWeekClass][$i]['class'] = 'days' . $strClass;
				$arrDays[$strWeekClass][$i]['events'] = array();

				continue;
			}

			$arrEvents = array();

			// Get all events of a day
			foreach ($arrAllEvents[$intKey] as $v)
			{
				foreach ($v as $vv)
				{
					$arrEvents[] = $vv;
				}
			}

			$arrDays[$strWeekClass][$i]['label'] = $intDay;
			$arrDays[$strWeekClass][$i]['class'] = 'days active' . $strClass;
			
			$arrDays[$strWeekClass][$i]['href'] = $this->strUrl . ($GLOBALS['TL_CONFIG']['disableAlias'] ? '?id=' . $this->Input->get('id') . '&amp;' : '?') . 'day=' . $intKey;
			
			$arrDays[$strWeekClass][$i]['title'] = sprintf(specialchars($GLOBALS['TL_LANG']['MSC']['cal_events']), count($arrEvents));
			$arrDays[$strWeekClass][$i]['events'] = $arrEvents;
		}

		return $arrDays;
	}
	
		/**
	 * Add an event to the array of active events
	 * @param object
	 * @param integer
	 * @param integer
	 * @param string
	 * @param integer
	 * @param integer
	 * @param integer
	 */
	protected function addisoEvent(Database_Result $objEvents, $intStart, $intEnd, $strUrl, $intBegin, $intLimit, $intCalendar)
	{
		global $objPage;

		$intDate = $intStart;
		$intKey = date('Ymd', $intStart);
		$span = Calendar::calculateSpan($intStart, $intEnd);
		$strDate = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $intStart);
		$strDay = $GLOBALS['TL_LANG']['DAYS'][date('w', $intStart)];
		$strMonth = $GLOBALS['TL_LANG']['MONTHS'][(date('n', $intStart)-1)];

		if ($span > 0)
		{
			$strDate = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $intStart) . ' - ' . $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $intEnd);
			$strDay = '';
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
			
			if ($span > 0)
			{
				$strTime .= ', daily';
			}
		}

		// Store raw data
		$arrEvent = $objEvents->row();
		
		//Get tags
		$arrEvent['tags'] = $this->Database->prepare("SELECT tag FROM tl_tag WHERE from_table='tl_iso_products' AND id=?")
								  ->execute($objEvents->id)->fetchEach('tag');

		// Overwrite some settings
		$arrEvent['time'] = $strTime;
		$arrEvent['date'] = $strDate;
		$arrEvent['day'] = $strDay;
		$arrEvent['month'] = $strMonth;
		$arrEvent['parent'] = $intCalendar;
		$arrEvent['link'] = $objEvents->name;
		$arrEvent['target'] = '';
		$arrEvent['title'] = specialchars($objEvents->name, true);

		$arrEvent['href'] = $this->generateEventUrl($objEvents, $strUrl);

		$arrEvent['class'] = ($objEvents->cssClass != '') ? ' ' . $objEvents->cssClass : '';
		$arrEvent['details'] = $this->String->encodeEmail($objEvents->description);
		$arrEvent['start'] = $intStart;
		$arrEvent['end'] = $intEnd;

		// Override the link target
		if ($objEvents->source == 'external' && $objEvents->target)
		{
			$arrEvent['target'] = ($objPage->outputFormat == 'xhtml') ? ' onclick="window.open(this.href); return false;"' : ' target="_blank"';
		}

		// Clean the RTE output
		if ($arrEvent['teaser'] != '')
		{
			if ($objPage->outputFormat == 'xhtml')
			{
				$arrEvent['teaser'] = $this->String->toXhtml($arrEvent['teaser']);
			}
			else
			{
				$arrEvent['teaser'] = $this->String->toHtml5($arrEvent['teaser']);
			}
		}

		// Display the "read more" button for external/article links
		if (($objEvents->source == 'external' || $objEvents->source == 'article') && $objEvents->details == '')
		{
			$arrEvent['details'] = true;
		}

		// Clean the RTE output
		else
		{
			if ($objPage->outputFormat == 'xhtml')
			{
				$arrEvent['details'] = $this->String->toXhtml($arrEvent['details']);
			}
			else
			{
				$arrEvent['details'] = $this->String->toHtml5($arrEvent['details']);
			}
		}

		$this->arrEvents[$intKey][$intStart][] = $arrEvent;

		// Multi-day event
		for ($i=1; $i<=$span && $intDate<=$intLimit; $i++)
		{
			// Only show first occurrence
			if ($this->cal_noSpan && $intDate >= $intBegin)
			{
				break;
			}

			$intDate = strtotime('+ 1 day', $intDate);
			$intNextKey = date('Ymd', $intDate);

			$this->arrEvents[$intNextKey][$intDate][] = $arrEvent;
		}
	}

	protected function addcalEvent(Database_Result $objEvents, $intStart, $intEnd, $strUrl, $intBegin, $intLimit, $intCalendar)
	{
		global $objPage;

		$intDate = $intStart;
		$intKey = date('Ymd', $intStart);
		$span = Calendar::calculateSpan($intStart, $intEnd);
		$strDate = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $intStart);
		$strDay = $GLOBALS['TL_LANG']['DAYS'][date('w', $intStart)];
		$strMonth = $GLOBALS['TL_LANG']['MONTHS'][(date('n', $intStart)-1)];

		if ($span > 0)
		{
			$strDate = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $intStart) . ' - ' . $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $intEnd);
			$strDay = '';
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
			
			if ($span > 0)
			{
				$strTime .= ', daily';
			}
		}

		// Store raw data
		$arrEvent = $objEvents->row();
		
		//Get tags
		$arrEvent['tags'] = $this->Database->prepare("SELECT tag FROM tl_tag WHERE from_table='tl_calendar_events' AND id=?")
								  ->execute($objEvents->id)->fetchEach('tag');
		

		// Overwrite some settings
		$arrEvent['time'] = $strTime;
		$arrEvent['date'] = $strDate;
		$arrEvent['day'] = $strDay;
		$arrEvent['month'] = $strMonth;
		$arrEvent['parent'] = $intCalendar;
		$arrEvent['link'] = $objEvents->title;
		$arrEvent['target'] = '';
		$arrEvent['title'] = specialchars($objEvents->title, true);
		$arrEvent['href'] = $this->generateEventUrl($objEvents, $strUrl);
		

		
		$arrEvent['class'] = ($objEvents->cssClass != '') ? ' ' . $objEvents->cssClass : '';
		$arrEvent['details'] = $this->String->encodeEmail($objEvents->details);
		$arrEvent['start'] = $intStart;
		$arrEvent['end'] = $intEnd;


		// Override the link target
		if ($objEvents->source == 'external' && $objEvents->target)
		{
			$arrEvent['target'] = ($objPage->outputFormat == 'xhtml') ? ' onclick="window.open(this.href); return false;"' : ' target="_blank"';
		}

		// Clean the RTE output
		if ($arrEvent['teaser'] != '')
		{
			if ($objPage->outputFormat == 'xhtml')
			{
				$arrEvent['teaser'] = $this->String->toXhtml($arrEvent['teaser']);
			}
			else
			{
				$arrEvent['teaser'] = $this->String->toHtml5($arrEvent['teaser']);
			}
		}

		// Display the "read more" button for external/article links
		if (($objEvents->source == 'external' || $objEvents->source == 'article') && $objEvents->details == '')
		{
			$arrEvent['details'] = true;
		}

		// Clean the RTE output
		else
		{
			if ($objPage->outputFormat == 'xhtml')
			{
				$arrEvent['details'] = $this->String->toXhtml($arrEvent['details']);
			}
			else
			{
				$arrEvent['details'] = $this->String->toHtml5($arrEvent['details']);
			}
		}

		$this->arrEvents[$intKey][$intStart][] = $arrEvent;

		// Multi-day event
		for ($i=1; $i<=$span && $intDate<=$intLimit; $i++)
		{
			// Only show first occurrence
			if ($this->cal_noSpan && $intDate >= $intBegin)
			{
				break;
			}

			$intDate = strtotime('+ 1 day', $intDate);
			$intNextKey = date('Ymd', $intDate);

			$this->arrEvents[$intNextKey][$intDate][] = $arrEvent;
		}
	}

	protected function getAllEvents($arrisoEvents, $arrCalendars, $intStart, $intEnd)
	{
		if (!is_array($arrisoEvents))
		{
			$arrisoEvents = array();
		}
		if (!is_array($arrCalendars))
		{
			$arrCalendars = array();
		}
		
		$this->strUrl = preg_replace('/\?.*$/i', '', $this->Environment->request);
		
		
		if(sizeof($arrCalendars)+sizeof($arrisoEvents)==0){
			return array();
		}
		$arrFilterIds = array();
		if (strlen($this->tag_filter))
		{
			$tags = preg_split("/,/", $this->tag_filter);
			$arrFilterIds = $this->Database->execute("SELECT id FROM tl_tag WHERE tag IN (" . join($tags, ',') . ") AND from_table='tl_iso_products' ORDER BY tag ASC")
				->fetchEach('id');
		}

		$this->import('String');

		$time = time();
		$this->arrEvents = array();

		foreach ($arrisoEvents as $id)
		{
			// Get current "jumpTo" page
			$objPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
									  ->limit(1)
									  ->execute($this->jumpTo);

			if ($objPage->numRows)
			{
				$strUrl = $this->generateFrontendUrl($objPage->row(), '/product/%s');
			}

			
			// Get events of the current period
			$objEvents = $this->Database->prepare("SELECT * FROM tl_iso_products_events e1 INNER JOIN tl_iso_products p1 ON p1.id=e1.pid WHERE type=? AND ((startDate>=? AND startDate<=?) OR (endDate>=? AND endDate<=?) OR (startDate<=? AND endDate>=?))" . (count($arrFilterIds) ? " AND p1.id IN(".implode(',', $arrFilterIds).")" : "") . (!BE_USER_LOGGED_IN ? " AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND published=1" : "") . " ORDER BY startTime")
				->execute($id, $intStart, $intEnd, $intStart, $intEnd, $intStart, $intEnd, $intStart, $intEnd);

			if ($objEvents->numRows < 1)
			{
				continue;
			}

			while ($objEvents->next())
			{

				$this->addisoEvent($objEvents, $objEvents->startDate, $objEvents->endDate, $strUrl, $intStart, $intEnd, $id);

				// Recurring events
				if ($objEvents->recurring)
				{
					$count = 0;

					$arrRepeat = deserialize($objEvents->repeatEach);
					$strtotime = '+ ' . $arrRepeat['value'] . ' ' . $arrRepeat['unit'];

					if ($arrRepeat['value'] < 1)
					{
						continue;
					}

					while ($objEvents->endTime < $intEnd)
					{
						if ($objEvents->recurrences > 0 && $count++ >= $objEvents->recurrences)
						{
							break;
						}

						$objEvents->startTime = strtotime($strtotime, $objEvents->startTime);
						$objEvents->endTime = strtotime($strtotime, $objEvents->endTime);

						// Skip events outside the scope
						if ($objEvents->endTime < $intStart || $objEvents->startTime > $intEnd)
						{
							continue;
						}

						$this->addisoEvent($objEvents, $objEvents->startTime, $objEvents->endTime, $strUrl, $intStart, $intEnd, $id);
					}
				}
			}
		}


		foreach ($arrCalendars as $id)
		{
			$strUrl = $this->strUrl;

			// Get current "jumpTo" page
			$objPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
									  ->limit(1)
									  ->execute($this->jumpTo);

			if ($objPage->numRows)
			{
				$strUrl = $this->generateFrontendUrl($objPage->row(), '/events/%s');
			}

			// Get events of the current period
			$objEvents = $this->Database->prepare("SELECT *, (SELECT title FROM tl_calendar WHERE id=?) AS calendar, (SELECT name FROM tl_user WHERE id=author) author FROM tl_calendar_events WHERE pid=? AND ((startTime>=? AND startTime<=?) OR (endTime>=? AND endTime<=?) OR (startTime<=? AND endTime>=?) OR (recurring=1 AND (recurrences=0 OR repeatEnd>=?) AND startTime<=?))" . (!BE_USER_LOGGED_IN ? " AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND published=1" : "") . " ORDER BY startTime")
										->execute($id, $id, $intStart, $intEnd, $intStart, $intEnd, $intStart, $intEnd, $intStart, $intEnd);

			if ($objEvents->numRows < 1)
			{
				continue;
			}

			while ($objEvents->next())
			{
				$this->addcalEvent($objEvents, $objEvents->startTime, $objEvents->endTime, $strUrl, $intStart, $intEnd, $id);

				// Recurring events
				if ($objEvents->recurring)
				{
					$count = 0;

					$arrRepeat = deserialize($objEvents->repeatEach);
					$strtotime = '+ ' . $arrRepeat['value'] . ' ' . $arrRepeat['unit'];

					if ($arrRepeat['value'] < 1)
					{
						continue;
					}

					while ($objEvents->endTime < $intEnd)
					{
						if ($objEvents->recurrences > 0 && $count++ >= $objEvents->recurrences)
						{
							break;
						}

						$objEvents->startTime = strtotime($strtotime, $objEvents->startTime);
						$objEvents->endTime = strtotime($strtotime, $objEvents->endTime);

						// Skip events outside the scope
						if ($objEvents->endTime < $intStart || $objEvents->startTime > $intEnd)
						{
							continue;
						}

						$this->addcalEvent($objEvents, $objEvents->startTime, $objEvents->endTime, $strUrl, $intStart, $intEnd, $id);
					}
				}
			}
		}

		// Sort the array
		foreach (array_keys($this->arrEvents) as $key)
		{
			ksort($this->arrEvents[$key]);
		}

		// HOOK: modify the result set
		if (isset($GLOBALS['TL_HOOKS']['getAllEvents']) && is_array($GLOBALS['TL_HOOKS']['getAllEvents']))
		{
			foreach ($GLOBALS['TL_HOOKS']['getAllEvents'] as $callback)
			{
				$this->import($callback[0]);
				$this->arrEvents = $this->$callback[0]->$callback[1]($this->arrEvents, $arrCalendars, $intStart, $intEnd, $this);
			}
		}

		return $this->arrEvents;
	}
	

}

?>