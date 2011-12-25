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
 * Config
 */
$GLOBALS['TL_DCA']['tl_iso_products']['config']['onsubmit_callback'][] = array('IsotopeEventHooks', 'onProductSave');
$GLOBALS['TL_DCA']['tl_iso_products']['config']['ondelete_callback'][] = array('tl_iso_products_tags', 'deleteEvents');
$GLOBALS['TL_DCA']['tl_iso_products']['config']['onload_callback'][] = array('tl_iso_products_tags', 'onCopy');




/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_iso_products']['fields']['tags'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['MSC']['tags'],
	'inputType'               => 'tag',
	'eval'                    => array('tl_class'=>'clr long'),
	'attributes'			  => array('legend'=>'general_legend', 'fe_search'=>true),
);



class tl_iso_products_tags extends tl_iso_products
{
	public function deleteEvents($dc)
	{
		$this->Database->prepare("DELETE FROM tl_tag WHERE from_table=? AND id= ")
			->execute($dc->table, $dc->id);
	}
	
	public function onCopy($dc)
	{
		if (is_array($this->Session->get('tl_iso_products_copy')))
		{
			foreach ($this->Session->get('tl_iso_products_copy') as $data)
			{
				$this->Database->prepare("INSERT INTO tl_tag (id, tag, from_table) VALUES (?, ?, ?)")
					->execute($dc->id, $data['tag'], $data['table']);
			}
		}
		
		$this->Session->set('tl_iso_products_copy', null);
		
		if ($this->Input->get('act') != 'copy')
		{
			return;
		}
		
		$objTags = $this->Database->prepare("SELECT * FROM tl_tag WHERE id = ? AND from_table = ?")
			->execute($this->Input->get('id'), $dc->table);
		
		$tags = array();
		
		while ($objTags->next())
		{
			array_push($tags, array("table" => $dc->table, "tag" => $objTags->tag));
		}
		
		$this->Session->set("tl_iso_products_copy", $tags);
	}
}

?>