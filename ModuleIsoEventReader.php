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
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class ModuleIsotopeProductReader
 * Front end module Isotope "product reader".
 */
class ModuleIsoEventReader extends ModuleIsotope
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_eventreader';

   /**
   	*Initialize cart
   	*/
	public function __construct(Database_Result $objModule, $strColumn='main')
	{
		$this->import('Isotope');
		
		global $objPage;
		$this->iso_reader_jumpTo = $objPage->id;
		
		$this->Isotope->EventCart = new EventCart();
		$this->Isotope->EventCart->initializeCart((int)$this->Isotope->Config->id, (int)$this->Isotope->Config->store_id);

		parent::__construct($objModule, $strColumn);
		
		
	}
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '###  EVENT READER ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// Return if no product has been specified
		if ($this->Input->get('product') == '' && $this->Input->get('events') == '')
		{
			return '';
		}

		global $objPage;
		$this->iso_reader_jumpTo = $objPage->id;

		return parent::generate();
	}


	/**
	 * Generate AJAX scripts
	 * @return string
	 */
	public function generateAjax()
	{
		$objProduct = IsotopeFrontend::getProduct($this->Input->get('product'), $this->iso_reader_jumpTo, false);

		if ($objProduct)
		{
			return $objProduct->generateAjax($this);
		}

		return '';
	}


	/**
	 * Generate module
	 * @return void
	 */
	protected function compile()
	{
		if($this->Input->get('product') !== '' && $this->Input->get('product') !== null){		
			$objProduct = IsotopeFrontend::getProductByAlias($this->Input->get('product'), $this->iso_reader_jumpTo);

			if (!$objProduct)
			{
				$this->Template = new FrontendTemplate('mod_message');
				$this->Template->type = 'empty';
				$this->Template->message = $GLOBALS['TL_LANG']['MSC']['invalidProductInformation'];
				return;
			}
	
			$this->Template->product = $objProduct->generate((strlen($this->iso_reader_layout) ? $this->iso_reader_layout : $objProduct->reader_template), $this);
			$this->Template->referer = 'javascript:history.go(-1)';
			$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];

			global $objPage;

			$objPage->pageTitle = strip_insert_tags($objProduct->name);
			$objPage->description = $this->prepareMetaDescription($objProduct->description_meta);

			$GLOBALS['TL_KEYWORDS'] .= (strlen($GLOBALS['TL_KEYWORDS']) ? ', ' : '') . $objProduct->keywords_meta;
		}
		
		
		if($this->Input->get('events') !== '' && $this->Input->get('events') !== null)
		{
		
			global $objPage;

			$this->Template->event = '';
			$this->Template->referer = 'javascript:history.go(-1)';
			$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
	
			$time = time();
	
			// Get current event
		$objEvent = $this->Database->prepare("SELECT * FROM tl_calendar_events WHERE alias=? AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND published=1")
								   ->limit(1)
								   ->execute($this->Input->get('events'));
	
			if ($objEvent->numRows < 1)
			{
				$this->Template->event = '<p class="error">' . sprintf($GLOBALS['TL_LANG']['MSC']['invalidPage'], $this->Input->get('events')) . '</p>';
	
				// Do not index the page
				$objPage->noSearch = 1;
				$objPage->cache = 0;
	
				// Send 404 header
				header('HTTP/1.1 404 Not Found');
				return;
			}
	
			// Overwrite the page title
			if ($objEvent->title != '')
			{
				$objPage->pageTitle = strip_insert_tags($objEvent->title);
			}
	
			// Overwrite the page description
			if ($objEvent->teaser != '')
			{
				$objPage->description = $this->prepareMetaDescription($objEvent->teaser);
			}
	
			$span = Calendar::calculateSpan($objEvent->startTime, $objEvent->endTime);
	
			if ($objPage->outputFormat == 'xhtml')
			{
				$strTimeStart = '';
				$strTimeEnd = '';
				$strTimeClose = '';
			}
			else
			{
				$strTimeStart = '<time datetime="' . date('Y-m-d\TH:i:sP', $objEvent->startTime) . '">';
				$strTimeEnd = '<time datetime="' . date('Y-m-d\TH:i:sP', $objEvent->endTime) . '">';
				$strTimeClose = '</time>';
			}
	
			// Get date
			if ($span > 0)
			{
				$date = $strTimeStart . $this->parseDate($GLOBALS['TL_CONFIG'][($objEvent->addTime ? 'datimFormat' : 'dateFormat')], $objEvent->startTime) . $strTimeClose . ' - ' . $strTimeEnd . $this->parseDate($GLOBALS['TL_CONFIG'][($objEvent->addTime ? 'datimFormat' : 'dateFormat')], $objEvent->endTime) . $strTimeClose;
			}
			elseif ($objEvent->startTime == $objEvent->endTime)
			{
				$date = $strTimeStart . $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objEvent->startTime) . ($objEvent->addTime ? ' (' . $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objEvent->startTime) . ')' : '') . $strTimeClose;
			}
			else
			{
				$date = $strTimeStart . $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objEvent->startTime) . ($objEvent->addTime ? ' (' . $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objEvent->startTime) . $strTimeClose . ' - ' . $strTimeEnd . $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objEvent->endTime) . ')' : '') . $strTimeClose;
			}
	
			$until = '';
			$recurring = '';
	
			// Recurring event
			if ($objEvent->recurring)
			{
				$arrRange = deserialize($objEvent->repeatEach);
				$strKey = 'cal_' . $arrRange['unit'];
				$recurring = sprintf($GLOBALS['TL_LANG']['MSC'][$strKey], $arrRange['value']);
	
				if ($objEvent->recurrences > 0)
				{
					$until = sprintf($GLOBALS['TL_LANG']['MSC']['cal_until'], $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objEvent->repeatEnd));
				}
			}
	
			// Override the default image size
			if ($this->imgSize != '')
			{
				$size = deserialize($this->imgSize);
	
				if ($size[0] > 0 || $size[1] > 0)
				{
					$objEvent->size = $this->imgSize;
				}
			}
	
			$objTemplate = new FrontendTemplate($this->cal_template);
			$objTemplate->setData($objEvent->row());
	
			$objTemplate->date = $date;
			$objTemplate->start = $objEvent->startTime;
			$objTemplate->end = $objEvent->endTime;
			$objTemplate->class = strlen($objEvent->cssClass) ? ' ' . $objEvent->cssClass : '';
			$objTemplate->recurring = $recurring;
			$objTemplate->until = $until;
	
			$this->import('String');
	
			// Clean the RTE output
			if ($objPage->outputFormat == 'xhtml')
			{
				$objEvent->details = $this->String->toXhtml($objEvent->details);
			}
			else
			{
				$objEvent->details = $this->String->toHtml5($objEvent->details);
			}
	
			$objTemplate->details = $this->String->encodeEmail($objEvent->details);
			$objTemplate->addImage = false;
	
			// Add image
			if ($objEvent->addImage && is_file(TL_ROOT . '/' . $objEvent->singleSRC))
			{
				$this->addImageToTemplate($objTemplate, $objEvent->row());
			}
	
			$objTemplate->enclosure = array();
	
			// Add enclosures
			if ($objEvent->addEnclosure)
			{
				$this->addEnclosuresToTemplate($objTemplate, $objEvent->row());
			}
	
			$this->Template->event = $objTemplate->parse();
	
			// HOOK: comments extension required
			if ($objEvent->noComments || !in_array('comments', $this->Config->getActiveModules()))
			{
				$this->Template->allowComments = false;
				return;
			}
	
			// Check whether comments are allowed
			$objCalendar = $this->Database->prepare("SELECT * FROM tl_calendar WHERE id=?")
										  ->limit(1)
										  ->execute($objEvent->pid);
	
			if ($objCalendar->numRows < 1 || !$objCalendar->allowComments)
			{
				$this->Template->allowComments = false;
				return;
			}
	
			$this->Template->allowComments = true;
	
			// Adjust the comments headline level
			$intHl = min(intval(str_replace('h', '', $this->hl)), 5);
			$this->Template->hlc = 'h' . ($intHl + 1);
	
			$this->import('Comments');
			$arrNotifies = array();
	
			// Notify system administrator
			if ($objCalendar->notify != 'notify_author')
			{
				$arrNotifies[] = $GLOBALS['TL_ADMIN_EMAIL'];
			}
	
			// Notify author
			if ($objCalendar->notify != 'notify_admin')
			{
				$objAuthor = $this->Database->prepare("SELECT email FROM tl_user WHERE id=?")
											->limit(1)
											->execute($objEvent->authorId);
	
				if ($objAuthor->numRows)
				{
					$arrNotifies[] = $objAuthor->email;
				}
			}
	
			$objConfig = new stdClass();
	
			$objConfig->perPage = $objCalendar->perPage;
			$objConfig->order = $objCalendar->sortOrder;
			$objConfig->template = $this->com_template;
			$objConfig->requireLogin = $objCalendar->requireLogin;
			$objConfig->disableCaptcha = $objCalendar->disableCaptcha;
			$objConfig->bbcode = $objCalendar->bbcode;
			$objConfig->moderate = $objCalendar->moderate;
	
			$this->Comments->addCommentsToTemplate($this->Template, $objConfig, 'tl_calendar_events', $objEvent->id, $arrNotifies);

		}

	}
	
	protected function sortOutProtected($arrCalendars)
	{
		if (BE_USER_LOGGED_IN || !is_array($arrCalendars) || count($arrCalendars) < 1)
		{
			return $arrCalendars;
		}

		$this->import('FrontendUser', 'User');
		$objCalendar = $this->Database->execute("SELECT id, protected, groups FROM tl_calendar WHERE id IN(" . implode(',', array_map('intval', $arrCalendars)) . ")");
		$arrCalendars = array();

		while ($objCalendar->next())
		{
			if ($objCalendar->protected)
			{
				if (!FE_USER_LOGGED_IN)
				{
					continue;
				}

				$groups = deserialize($objCalendar->groups);

				if (!is_array($groups) || count($groups) < 1 || count(array_intersect($groups, $this->User->groups)) < 1)
				{
					continue;
				}
			}

			$arrCalendars[] = $objCalendar->id;
		}

		return $arrCalendars;
	}
}

