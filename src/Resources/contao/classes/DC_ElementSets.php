<?php
namespace Contao;

use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\InternalServerErrorException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Picker\PickerInterface;
use Patchwork\Utf8;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use leycommediasolutions\contao_elementsets\Resources\contao\classes;

/**
 * Class DC_ElementSets
 * 
 */

class DC_ElementSets extends \DC_Table
{
    public function __construct($strTable, $arrModule=array())
    {
        parent::__construct($strTable, $arrModule=array());
    }
    
    /**
     * Diese Methode hat die Parameter aus der Klasse DC_Table -> Methode create Zeile 623
     */   
	public function elementset_add($set=array())
	{
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'])
		{
			throw new InternalServerErrorException('Table "' . $this->strTable . '" is not creatable.');
        }
        $this->set['type']='elementset';

		// Get the new position
		$this->getNewPosition('new', (\strlen(\Input::get('pid')) ? \Input::get('pid') : null), (\Input::get('mode') == '2' ? true : false));

		// Dynamically set the parent table of tl_content
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'])
		{
			$this->set['ptable'] = $this->ptable;
		}

		/** @var SessionInterface $objSession */
		$objSession = \System::getContainer()->get('session');

		// Empty the clipboard
		$arrClipboard = $objSession->get('CLIPBOARD');
		$arrClipboard[$this->strTable] = array();
		$objSession->set('CLIPBOARD', $arrClipboard);

		// Insert the record if the table is not closed and switch to edit mode
	    if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'])
		{
			$this->set['tstamp'] = 0;

			$objInsertStmt = $this->Database->prepare("INSERT INTO " . $this->strTable . " %s")
											->set($this->set)
                                            ->execute();

			if ($objInsertStmt->affectedRows)
			{
				$insertID = $objInsertStmt->insertId;

				/** @var AttributeBagInterface $objSessionBag */
				$objSessionBag = $objSession->getBag('contao_backend');

				// Save new record in the session
                $new_records = $objSessionBag->get('new_records');
				$new_records[$this->strTable][] = $insertID;
				$objSessionBag->set('new_records', $new_records);

                // Add a log entry
				$this->log('A new Elementset will be create in "'.$this->strTable.'".'.$this->getParentEntries($this->strTable, $insertID), __METHOD__, TL_GENERAL);
				$this->redirect($this->switchToElements($insertID));
			}
		}

		$this->redirect($this->getReferer());
    }

    /**
     * Diese Methode hat die Parameter aus der Klasse DC_Table -> Methode edit Zeile 1774
     * Diese Methode besitzt zusaetzlich die Methode copy aus DC_Table
     */
    public function elementset_edit($intId=null, $ajaxId=null, $blnDoNotRedirect=false)
    {
        if(!(\Input::get('elementset_id') == ''))
        {
            $this->createNewElements($intId, $ajaxId, $blnDoNotRedirect);
        }
        if(\Input::get('elementset_id') == '')
        {
           return $this->showTheElements($intId, $ajaxId, $blnDoNotRedirect);
        }
        return false;
    }
    
    /**
     * Diese Methode erstellt den neuen Datensatz 
     */
    public function createNewElements($intId, $ajaxId, $blnDoNotRedirect)
    {
        $element_sort = 1;
        $id_set = array();
        $rand = rand(); // Generieren der Zufallszahl
        $rand_ok = 0;

        //Ausgeben der id um zu ueberpruefen ob die Zahl schon existiert
        do{
            $result_rand = $this->Database->prepare("SELECT elementset_id_all FROM tl_content")->execute();
            while($result_rand->next())
            {
                foreach ($result_rand->row() as $k=>$v)
                {
                    if($v == $rand)
                    {
                        $rand = rand();
                    }
                    else
                    {
                        $rand_ok = 1;
                    }
                }
            }
        }while($rand_ok != 1);

        $result_newPosition = $this->Database->prepare("SELECT * FROM tl_content WHERE pid=? AND ptable=?")->execute(\Input::get('elementset_id'), 'tl_elementsets');
        if ($result_newPosition->numRows)
        {

            // Werte fuer die Datenbank werden erstellt.
            $this->set['type'] = 'elementset_start';
            $this->set['tstamp'] = time();
            $this->set['headline'] = 'a:2:{s:4:"unit";s:2:"h2";s:5:"value";s:0:"";}';
            $this->set['sortOrder'] = 'ascending';
            $this->set['cssID'] = 'a:2:{i:0;s:0:"";i:1;s:0:"";}';
            $this->set['elementset_id'] = \Input::get('elementset_id');
            $this->set['elementset_sort'] = $element_sort;
            $this->set['elementset_id_all'] = $rand;

              

            // Dynamically set the parent table of tl_content
            if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'])
            {
                $this->set['ptable'] = $this->ptable;
            }

            // Die neue Position wird errechnet und verwendet
            $this->getNewPosition('copy', (\strlen(\Input::get('pid')) ? \Input::get('pid') : null), (\Input::get('mode') == '2' ? true : false));

            // Die Werte werden in die Tabelle eingefuegt
            $objInsertStmt = $this->Database->prepare("INSERT INTO " . $this->strTable . " %s")
                                            ->set($this->set)
                                            ->execute();

            // Um die richtige Reihenfolge einzuhalten wird die ID des neuen Datensatzes herrausgenommen und als neue ID(PID) verwendet
            $newid = $this->Database->prepare("SELECT id FROM tl_content WHERE elementset_sort=? ")
                                            ->execute($element_sort);

            // Die ID wird zwischengespeichert
            foreach($newid->row()  as $k=>$v)
            {
                $id_set[$element_sort]= $v;
            }                                   
            $element_sort++;
            // Das Array wird durchlaufen und die Werte werden hinzugefuegt
            while($result_newPosition->next())
            {
                /*Contao Anfang*/
                // Das Array wird verwendet
                foreach ($result_newPosition->row() as $k=>$v)
                {
                    if (array_key_exists($k, $GLOBALS['TL_DCA'][$this->strTable]['fields']))
                    {
                        // Never copy passwords
                        if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['inputType'] == 'password')
                        {
                            $v = \Widget::getEmptyValueByFieldType($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['sql']);
                        }
    
                        // Empty unique fields or add a unique identifier in copyAll mode
                        elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['eval']['unique'])
                        {
                            $v = (\Input::get('act') == 'copyAll' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['eval']['doNotCopy']) ? $v .'-'. substr(md5(uniqid(mt_rand(), true)), 0, 8) : \Widget::getEmptyValueByFieldType($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['sql']);
                        }
    
                        // Reset doNotCopy and fallback fields to their default value
                        elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['eval']['doNotCopy'] || $GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['eval']['fallback'])
                        {
                            $v = \Widget::getEmptyValueByFieldType($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['sql']);
    
                            // Use array_key_exists to allow NULL (see #5252)
                            if (array_key_exists('default', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]))
                            {
                                $v = \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['default']) ? serialize($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['default']) : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['default'];
                            }
    
                            // Encrypt the default value (see #3740)
                            if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$k]['eval']['encrypt'])
                            {
                                $v = \Encryption::encrypt($v);
                            }
                        }
    
                        $this->set[$k] = $v;
                    }
                }
                // HOOK: style sheet category
                if ($this->strTable == 'tl_style')
                {
                    $filter = $objSessionBag->get('filter');
                    $category = $filter['tl_style_' . CURRENT_ID]['category'];
    
                    if ($category != '')
                    {
                        $this->set['category'] = $category;
                    }
                }
                /*Contao Ende*/
                // Die neue ID wird verwendet um die Reihenfolge einhalten zu koennen
                $pidold = $id_set[$element_sort-1];
                $this->getNewPosition('copy', (\strlen($pidold) ? $pidold : null) , false);

                // Dynamically set the parent table of tl_content
                if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'])
                {
                    $this->set['ptable'] = $this->ptable;
                }
                // Remove the ID field from the data array
                unset($this->set['id']);
                $this->set['elementset_sort'] = $element_sort;            
                $this->set['elementset_id'] = \Input::get('elementset_id');
                $this->set['elementset_id_all'] = $rand;
                $objInsertStmt = $this->Database->prepare("INSERT INTO " . $this->strTable . " %s")
                                                ->set($this->set)
                                                ->execute();
                $newid = $this->Database->prepare("SELECT id FROM tl_content WHERE elementset_sort=? ")
                                                ->execute($element_sort);

                foreach($newid->row()  as $k=>$v)
                {
                    $id_set[$element_sort]= $v;
                }                                   
                $element_sort++;
            
            }
            // Das Array [set] wird geleert um die Daten von dem vorherigen Datensatz nicht zu uebernehmen 
            $this->set = array();

            // ElementSets Ende wird generiert
            $this->set['type'] = 'elementset_start';
            $this->set['tstamp'] = time();
            $this->set['headline'] = 'a:2:{s:4:"unit";s:2:"h2";s:5:"value";s:0:"";}';
            $this->set['sortOrder'] = 'ascending';
            $this->set['cssID'] = 'a:2:{i:0;s:0:"";i:1;s:0:"";}';
            $this->set['elementset_id'] = \Input::get('elementset_id');
            $this->set['elementset_sort'] = $element_sort;
            $this->set['elementset_id_all'] = $rand;

            // Dynamically set the parent table of tl_content
            if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'])
            {
                $this->set['ptable'] = $this->ptable;
            }

            // Die neue ID wird verwendet
            $pidold = $id_set[$element_sort-1];
            $this->getNewPosition('copy', (\strlen($pidold) ? $pidold : null) , false);
            
            $objInsertStmt = $this->Database->prepare("INSERT INTO " . $this->strTable . " %s")
                                            ->set($this->set)
                                            ->execute();
            $newid = $this->Database->prepare("SELECT id FROM tl_content WHERE elementset_sort=? ")
                                            ->execute($element_sort);

            foreach($newid->row()  as $k=>$v)
            {
                $id_set[$element_sort]= $v;
            }

            $element_sort++;

            for ($ix=1; $ix<$element_sort; $ix++)
            {
                $this->Database->prepare("UPDATE tl_content SET elementset_sort=0 WHERE id=? ")
                                        ->execute($id_set[$ix]);
            }
        } 
        $this->redirect($this->getReferer());
        //return false
    }

    /**
     * Diese Methode erstellt die Uebersicht
     */
    public function showTheElements($intId, $ajaxId, $blnDoNotRedirect)
    {
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'])
		{
			throw new InternalServerErrorException('Table "' . $this->strTable . '" is not editable.');
		}

		if ($intId != '')
		{
			$this->intId = $intId;
		}

		// Get the current record
		$objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
								 ->limit(1)
								 ->execute($this->intId);

		// Redirect if there is no record with the given ID
		if ($objRow->numRows < 1)
		{
			throw new AccessDeniedException('Cannot load record "' . $this->strTable . '.id=' . $this->intId . '".');
		}

		$this->objActiveRecord = $objRow;

		$return = '';
		$this->values[] = $this->intId;
        $this->procedure[] = 'id=?';

        /**
         * Neue Elemente werden angezeigt
         */

        //Filter wird wieder geloescht
        if($_POST['filter_reset'] == 1)
        {
            $_POST['category'] = '';
        }

        // Die Kategorien werden in eine Variable gespeichert
        $result_category= $this->Database->prepare("SELECT id, category FROM tl_elementsets_category")
                                         ->execute();           
        while($result_category->next())
        {
            $id_category[] = $result_category->id;
            $category_name[] = $result_category->category;
            $cat .= $result_category->category . ","; // Um die Form_Fields zu fuellen
        }

        // Die Daten werden aus der Datenbank genommen
        $result = $this->Database->prepare("SELECT id, title, preview_image, category FROM tl_elementsets")
                                ->execute();
        // Das Bild wird generiert
        $objFile = \FilesModel::findByPk($objPage->flexibleheader_image)->path; /*echo $objFile*/
        while($result->next())
        {
            $id[] = $result->id;
            $title[] = $result->title;
            $objFile_picture = \FilesModel::findByUuid($result->preview_image);
            $picture[] = Image::getHtml(\System::getContainer()->get('contao.image.image_factory')->create(TL_ROOT . '/' . $objFile_picture->path)->getUrl(TL_ROOT), '', 'class="elementsets_preview"');
            $category[] = $result->category;
        }
        for($ix=0; $ix < count($category_name); $ix++)
        {
            // Es duerfen nur Kategorien angezeigt werden die auch einen Inhalt haben
            if(in_array($category_name[$ix], $category))
            {
                // Ueberpruefen ob die  $_POST Variable gesetzt ist
                if($_POST['category'] != '')
                {
                    // Ueberpruefung ob die Kategorie mit der Filterregel uebereinstimmt
                    if($category[$ix] == $_POST['category']) 
                    {
                        $vartemp = array_search($category[$ix],$category_name);

                        $return .= '<fieldset' .' id="pal_'.$id_category[$vartemp].'"'. ' class="tl_tbox tl_formbody_edit_elementsets">';
                        $return .= '<legend onclick="AjaxRequest.toggleFieldset(this,\'' . $id_category[$vartemp] . '\',\'' . $this->strTable . '\')">' . $category_name[$vartemp] . '</legend>';
        
                            // Eine Schleife wird durchlaufen um die Kategorien abzuarbeiten
                            for($iy=0; $iy <count($category); $iy++)
                            {
                                // Nur die passenden Daten werden herausgegeben
                                if($category[$iy] == $category_name[$vartemp])
                                {
                                    $return .= '<div class="w50 autoheight widget">';
                                    $return .= '<div class="tl_box_elementsets">';
                                    $return .= '<div class="inside_elementsets">';
                                    $return .= $picture[$iy];
                                    $return .= '<h2>'.$title[$iy].'</h2>';
                                    $return .= '<a class="insert_elementsets" href="'.ampersand(\Environment::get('request'), true).'&amp;elementset_id='.$id[$iy].'">'.$GLOBALS['TL_LANG']['tl_content']['insert_elementsets'].'</a>';
                                    $return .= '</div>';
                                    $return .= '</div>';
                                    $return .= '</div>';
                                }
                            }
                        $return .= '</fieldset>';
                    }
                }
                else
                {
                    $return .= '<fieldset' .' id="pal_'.$id_category[$ix].'"'. ' class="tl_tbox tl_formbody_edit_elementsets">';
                    $return .= '<legend onclick="AjaxRequest.toggleFieldset(this,\'' . $id_category[$ix] . '\',\'' . $this->strTable . '\')">' . $category_name[$ix] . '</legend>';
    
                        // Eine Schleife wird durchlaufen um die Kategorien abzuarbeiten
                        for($iy=0; $iy <count($category); $iy++)
                        {
                            // Nur die passenden Daten werden herausgegeben
                            if($category[$iy] == $category_name[$ix])
                            {
                                $return .= '<div class="w50 autoheight widget">';
                                $return .= '<div class="tl_box_elementsets">';
                                $return .= '<div class="inside_elementsets">';
                                $return .= $picture[$iy];
                                $return .= '<h2>'.$title[$iy].'</h2>';
                                $return .= '<a class="insert_elementsets" href="'.ampersand(\Environment::get('request'), true).'&amp;elementset_id='.$id[$iy].'">'.$GLOBALS['TL_LANG']['tl_content']['insert_elementsets'].'</a>';
                                $return .= '</div>';
                                $return .= '</div>';
                                $return .= '</div>';
                            }
                        }
                    $return .= '</fieldset>';
                }
            }
        }
        
		$return .= '
            </div>
            </form>';

        $result_options = $this->Database->prepare("SELECT category FROM tl_elementsets_category")
                                ->execute();

        while($result_options->next())
        {
            foreach ($result_options->row() as $k=>$v)
            {
                $options_elementsets .= '<option value="'.$v.'">'.$v.'</option>';
            }
        }
             
        $submit = '
        <div class="tl_submit_panel tl_subpanel">
          <button name="filter" id="filter" class="tl_img_submit filter_apply" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['applyTitle']) . '">' . $GLOBALS['TL_LANG']['MSC']['apply'] . '</button>
          <button name="filter_reset" id="filter_reset" value="1" class="tl_img_submit filter_reset" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['resetTitle']) . '">' . $GLOBALS['TL_LANG']['MSC']['reset'] . '</button>
        </div>';
        
        $return1 .= '
            <div class="tl_panel cf">
            ' . $submit . ' 
                <div class="tl_filter tl_subpanel">
                    <strong>Filter</strong>
                    <select name="category" id="category" class="tl_select">
                    '.$options_elementsets.'
                    </select>
                </div>
            </div>
        ';
        
        $return1 = '
            <form action="'.ampersand(\Environment::get('request'), true).'" class="tl_form" method="post" aria-label="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['searchAndFilter']).'">
            <div class="tl_formbody">
            <input type="hidden" name="FORM_SUBMIT" value="tl_filters">
            <input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
            '.$return1.'
           
            </form>';

		// Begin the form (-> DO NOT CHANGE THIS ORDER -> this way the onsubmit attribute of the form can be changed by a field)
        $return = $return1. '
            <div id="tl_buttons">' . (\Input::get('nb') ? '&nbsp;' : '
            <a href="'.$this->getReferer(true).'" class="header_back" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>') . '
            </div>
            <form action="'.ampersand(\Environment::get('request'), true).'" id="'.$this->strTable.'" class="tl_form tl_edit_form" method="post" enctype="' . ($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded') . '"'.(!empty($this->onsubmit) ? ' onsubmit="'.implode(' ', $this->onsubmit).'"' : '').'>
            
            <input type="hidden" name="FORM_SUBMIT" value="'.$this->strTable.'">
            <input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
            <input type="hidden" name="FORM_FIELDS[]" value="'.$cat.'">' . $return;



		// Reload the page to prevent _POST variables from being sent twice
		if (\Input::post('FORM_SUBMIT') == $this->strTable && !$this->noReload)
		{
			$arrValues = $this->values;
			array_unshift($arrValues, time());

			// Trigger the onsubmit_callback
			if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback']))
			{
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback)
				{
					if (\is_array($callback))
					{
						$this->import($callback[0]);
						$this->{$callback[0]}->{$callback[1]}($this);
					}
					elseif (\is_callable($callback))
					{
						$callback($this);
					}
				}
			}

			// Set the current timestamp (-> DO NOT CHANGE THE ORDER version - timestamp)
			if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'])
			{
				$this->Database->prepare("UPDATE " . $this->strTable . " SET ptable=?, tstamp=? WHERE id=?")
							   ->execute($this->ptable, time(), $this->intId);
			}
			else
			{
				$this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=? WHERE id=?")
							   ->execute(time(), $this->intId);
			}

			// Invalidate cache tags
			$this->invalidateCacheTags($this->getCacheTags($this->table, array($this->id), $this->ptable, $this->activeRecord->pid));

			$this->reload();
		}

		// Set the focus if there is an error
		if ($this->noReload)
		{
			$return .= '
            <script>
            window.addEvent(\'domready\', function() {
                Backend.vScrollTo(($(\'' . $this->strTable . '\').getElement(\'label.error\').getPosition().y - 20));
            });
            </script>';
		}
        return $return;		
    }
    /**
     * Zum loeschen des gesamten Sets
     */
    public function elementset_delete()
    {
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable'])
		{
			throw new InternalServerErrorException('Table "' . $this->strTable . '" is not deletable.');
		}

		if (!$this->intId)
		{
			$this->redirect($this->getReferer());
        }

        /* START */

        if($this->strTable == 'tl_content')
        {
            $result_delete = $this->Database->prepare("SELECT type, elementset_id, elementset_id_all FROM tl_content WHERE id=?" )
                                            ->limit(1)
                                            ->execute($this->intId);
            if ($result_delete->numRows)
            {
                $result_delete->next();
                $data_delete = $result_delete->fetchAllAssoc();

                if($data_delete[0]['type'] == 'elementset_start')
                {
                    $result_delete_all = $this->Database->prepare("SELECT id FROM tl_content WHERE elementset_id =? AND elementset_id_all=?" )
                                                        ->execute($data_delete[0]['elementset_id'], $data_delete[0]['elementset_id_all']);
                    while($result_delete_all->next())
                    {
                        foreach ($result_delete_all->row() as $k=>$v)
                        {
                            $this->intId = $v;
                            $this->delete(true);
                        }
                    }
                }
            }
        }
        
        /* END */
        $this->redirect($this->getReferer());
    }

	/** 
     * Die Parent Methode ist in Datacontainer.php line 724
	 * Return a query string that switches into edit mode
     * 
     * Beschreibung: Diese Klasse generiert den den URL. Contao verwendet diese URL um die naechste Aktion abzuarbeiten. In dieser URL wurde aus
     * act=edit => act=elementset_edit. In dieser Klasse werden die neuen Elementsets abgearbeitet.
	 *
	 * @param integer $id
	 *
	 * @return string
	 */
	public function switchToElements($id)
	{
		$arrKeys = array();
		$arrUnset = array('act', 'id', 'table');

		foreach (array_keys($_GET) as $strKey)
		{
			if (!\in_array($strKey, $arrUnset))
			{
				$arrKeys[$strKey] = $strKey . '=' . \Input::get($strKey);
			}
		}

		$strUrl = TL_SCRIPT . '?' . implode('&', $arrKeys);

        return $strUrl . (!empty($arrKeys) ? '&' : '') . (\Input::get('table') ? 'table='.\Input::get('table').'&amp;' : '').'act=elementset_edit&amp;id='.rawurlencode($id);
    }
}