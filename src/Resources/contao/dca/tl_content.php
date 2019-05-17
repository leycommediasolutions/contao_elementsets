<?php
$GLOBALS['TL_DCA']['tl_content']['fields']['elementset_sort'] = array
(
	'eval' => array('doNotCopy' => true),
    'sql'                     => "int(10) NULL"
);
$GLOBALS['TL_DCA']['tl_content']['fields']['elementset_id'] = array
(
	'eval' => array('doNotCopy' => true),
    'sql'                     => "int(10) NULL"
);
$GLOBALS['TL_DCA']['tl_content']['fields']['elementset_id_all'] = array
(
	'eval' => array('doNotCopy' => true),
    'sql'                     => "int(10) NULL"
);


$GLOBALS['TL_DCA']['tl_content']['config']['dataContainer'] = 'ElementSets';
$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array('tl_content_elementsets', 'ElementsetOnload');


$GLOBALS['TL_DCA']['tl_content']['palettes']['elementset_start'] = '{type_legend},type;{template_legend},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes']['elementset_end'] = '{type_legend},type;{template_legend},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';


// Dynamically add the permission check and parent table
if (Input::get('do') == 'elementsets')
{
	$GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = 'tl_elementsets';
	$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array('tl_content_elementsets', 'checkPermission');
	$GLOBALS['TL_DCA']['tl_content']['list']['operations']['toggle']['button_callback'] = array('tl_content_elementsets', 'toggleIcon');
}

class tl_content_elementsets extends Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
    }
	public function ElementsetOnload($dc)
	{
        if(!(Input::get('do') == 'elementsets'))
        {
            array_insert($GLOBALS['TL_DCA']['tl_content']['list']['global_operations'], 0, array
            (
                'elementsets' => array
                (
                    'label'               => &$GLOBALS['TL_LANG']['tl_content']['header_elementsets'],
                    'href'                => 'act=paste&mode=elementset_add',
                    'icon'                => 'bundles/elementsets/icon/new_elementsets.svg',
                    'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="4"'
                )
            ));
            
            // NUR TYPE=myelementset_start
            array_insert($GLOBALS['TL_DCA']['tl_content']['list']['operations'], 4, array
            (
                'elementset_delete' => array
                (
                    'label'               => &$GLOBALS['TL_LANG']['tl_content']['elementset_delete'],
                    'href'                => 'act=elementset_delete',
                    'icon'                => 'bundles/elementsets/icon/delete_elementsets.svg',
                    'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
                    'button_callback'     => array('tl_content_elementsets', 'deleteElement_Elementset')
                )
            ));
            $GLOBALS['TL_DCA']['tl_content']['list']['operations']['copy'] = array(
                'label'               => &$GLOBALS['TL_LANG']['tl_content']['copy'],
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.svg',
                'attributes'          => 'onclick="Backend.getScrollOffset()"',
                'button_callback'     => array('tl_content_elementsets', 'copyElement_Elementset')
            ); 

            $GLOBALS['TL_DCA']['tl_content']['list']['operations']['cut'] = array(
                'label'               => &$GLOBALS['TL_LANG']['tl_content']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.svg',
                'attributes'          => 'onclick="Backend.getScrollOffset()"',
                'button_callback'     => array('tl_content_elementsets', 'cutElement_Elementset')
            ); 
        }
    }
    	/**
	 * Return the delete content element button
	 *
	 * @param array  $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 *
	 * @return string
	 */
	public function deleteElement_Elementset($row, $href, $label, $title, $icon, $attributes)
	{
		$objElement = $this->Database->prepare("SELECT id FROM tl_content WHERE cteAlias=? AND type='alias'")
									 ->limit(1)
                                     ->execute($row['id']);
                                     
        $result = $this->Database->prepare("SELECT type FROM tl_content WHERE id=?")
                                 ->execute($row['id']);
        if ($result->numRows)
        {
            while($result->next())
            {
                foreach ($result->row() as $k=>$v)
                {
                    if($v == 'elementset_start')
                    {
                        return $objElement->numRows ? Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ' : '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a>';
                    }
                }
            }
        }    
    }
    public function copyElement_Elementset($row, $href, $label, $title, $icon, $attributes)
	{
		$objElement = $this->Database->prepare("SELECT id FROM tl_content WHERE cteAlias=? AND type='alias'")
									 ->limit(1)
                                     ->execute($row['id']);
                                     
        $result = $this->Database->prepare("SELECT type FROM tl_content WHERE id=?")
                                 ->execute($row['id']);
        if ($result->numRows)
        {
            while($result->next())
            {
                foreach ($result->row() as $k=>$v)
                {
                    if($v == 'elementset_start')
                    {

                        $label = $GLOBALS['TL_LANG']['tl_content']['elementset_copy'][0]; 
                        $title = $GLOBALS['TL_LANG']['tl_content']['elementset_copy'][1];
                        $href = "act=paste&mode=elementset_copy";

                        return $objElement->numRows ? Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ' : '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a>';
                    }
                    else{
                        return $objElement->numRows ? Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ' : '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a>';
                    }
                }
            }
        }    
    }
    public function cutElement_Elementset($row, $href, $label, $title, $icon, $attributes)
	{
		$objElement = $this->Database->prepare("SELECT id FROM tl_content WHERE cteAlias=? AND type='alias'")
									 ->limit(1)
                                     ->execute($row['id']);
                                     
        $result = $this->Database->prepare("SELECT type FROM tl_content WHERE id=?")
                                 ->execute($row['id']);
        if ($result->numRows)
        {
            while($result->next())
            {
                foreach ($result->row() as $k=>$v)
                {
                    if($v == 'elementset_start')
                    {
                        $label = $GLOBALS['TL_LANG']['tl_content']['elementset_cut'][0]; 
                        $title = $GLOBALS['TL_LANG']['tl_content']['elementset_cut'][1];
                        $href = "act=paste&mode=elementset_cut";

                        return $objElement->numRows ? Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ' : '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a>';
                    }
                    else{
                        return $objElement->numRows ? Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ' : '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a>';
                    }
                }
            }
        }    
    }
	/**
	 * Check permissions to edit table tl_content
	 */
	public function checkPermission()
	{
		if ($this->User->isAdmin)
		{
			return;
		}

		// Set the root IDs
		if (empty($this->User->elementsets_s) || !\is_array($this->User->elementsets_s))
		{
			$root = array(0);
		}
		else
		{
			$root = $this->User->elementsets_s;
		}

		// Check the current action
		switch (Input::get('act'))
		{
			case 'paste':
				// Allow
				break;

			case '': // empty
			case 'create':
			case 'select':
				// Check access to the news item
				$this->checkAccessToElement(CURRENT_ID, $root, true);
				break;

			case 'editAll':
			case 'deleteAll':
			case 'overrideAll':
			case 'cutAll':
			case 'copyAll':
				// Check access to the parent element if a content element is moved
				if (Input::get('act') == 'cutAll' || Input::get('act') == 'copyAll')
				{
					$this->checkAccessToElement(Input::get('pid'), $root, (Input::get('mode') == 2));
				}

				$objCes = $this->Database->prepare("SELECT id FROM tl_content WHERE ptable='tl_elementsets' AND pid=?")
										 ->execute(CURRENT_ID);

				/** @var Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
				$objSession = System::getContainer()->get('session');

				$session = $objSession->all();
				$session['CURRENT']['IDS'] = array_intersect((array) $session['CURRENT']['IDS'], $objCes->fetchEach('id'));
				$objSession->replace($session);
				break;

			case 'cut':
			case 'copy':
				// Check access to the parent element if a content element is moved
				$this->checkAccessToElement(Input::get('pid'), $root, (Input::get('mode') == 2));
				// no break;

			default:
				// Check access to the content element
				$this->checkAccessToElement(Input::get('id'), $root);
				break;
		}
	}

	/**
	 * Check access to a particular content element
	 *
	 * @param integer $id
	 * @param array   $root
	 * @param boolean $blnIsPid
	 *
	 * @throws Contao\CoreBundle\Exception\AccessDeniedException
	 */
	protected function checkAccessToElement($id, $root, $blnIsPid=false)
	{
		if ($blnIsPid)
		{
			$objArchive = $this->Database->prepare("SELECT n.id, a.id AS nid FROM tl_content n, tl_elementsets a WHERE a.id=? AND n.pid=a.id")
										 ->limit(1)
                                         ->execute($id);
            
                                         
		}
		else
		{
			$objArchive = $this->Database->prepare("SELECT a.id, n.id AS nid FROM tl_content n, tl_elementsets a WHERE n.id=? AND n.pid=a.id")
										 ->limit(1)
										 ->execute($id);
		}

		// Invalid ID
		if ($objArchive->numRows < 1)
		{
			throw new Contao\CoreBundle\Exception\AccessDeniedException('Invalid elementset content element ID ' . $id . '.');
		}
	}

	/**
	 * Return the "toggle visibility" button
	 *
	 * @param array  $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 *
	 * @return string
	 */
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		if (\strlen(Input::get('cid')))
		{
			$this->toggleVisibility(Input::get('cid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
			$this->redirect($this->getReferer());
		}

		// Check permissions AFTER checking the cid, so hacking attempts are logged
		if (!$this->User->hasAccess('tl_content::invisible', 'alexf'))
		{
			return '';
		}

		$href .= '&amp;id='.Input::get('id').'&amp;cid='.$row['id'].'&amp;state='.$row['invisible'];

		if ($row['invisible'])
		{
			$icon = 'invisible.svg';
		}

		return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'" data-tid="cid"'.$attributes.'>'.Image::getHtml($icon, $label, 'data-state="' . ($row['invisible'] ? 0 : 1) . '"').'</a> ';
	}

	/**
	 * Toggle the visibility of an element
	 *
	 * @param integer       $intId
	 * @param boolean       $blnVisible
	 * @param DataContainer $dc
	 */
	public function toggleVisibility($intId, $blnVisible, DataContainer $dc=null)
	{
		// Set the ID and action
		Input::setGet('id', $intId);
		Input::setGet('act', 'toggle');

		if ($dc)
		{
			$dc->id = $intId; // see #8043
		}

		// Trigger the onload_callback
		if (\is_array($GLOBALS['TL_DCA']['tl_content']['config']['onload_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'] as $callback)
			{
				if (\is_array($callback))
				{
					$this->import($callback[0]);
					$this->{$callback[0]}->{$callback[1]}($dc);
				}
				elseif (\is_callable($callback))
				{
					$callback($dc);
				}
			}
		}

		// Check the field access
		if (!$this->User->hasAccess('tl_content::invisible', 'alexf'))
		{
			throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish content element ID ' . $intId . '.');
		}

		// Set the current record
		if ($dc)
		{
			$objRow = $this->Database->prepare("SELECT * FROM tl_content WHERE id=?")
									 ->limit(1)
									 ->execute($intId);

			if ($objRow->numRows)
			{
				$dc->activeRecord = $objRow;
			}
		}

		$objVersions = new Versions('tl_content', $intId);
		$objVersions->initialize();

		// Reverse the logic (elements have invisible=1)
		$blnVisible = !$blnVisible;

		// Trigger the save_callback
		if (\is_array($GLOBALS['TL_DCA']['tl_content']['fields']['invisible']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_content']['fields']['invisible']['save_callback'] as $callback)
			{
				if (\is_array($callback))
				{
					$this->import($callback[0]);
					$blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
				}
				elseif (\is_callable($callback))
				{
					$blnVisible = $callback($blnVisible, $dc);
				}
			}
		}

		$time = time();

		// Update the database
		$this->Database->prepare("UPDATE tl_content SET tstamp=$time, invisible='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
					   ->execute($intId);

		if ($dc)
		{
			$dc->activeRecord->tstamp = $time;
			$dc->activeRecord->invisible = ($blnVisible ? '1' : '');
		}

		// Trigger the onsubmit_callback
		if (\is_array($GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'] as $callback)
			{
				if (\is_array($callback))
				{
					$this->import($callback[0]);
					$this->{$callback[0]}->{$callback[1]}($dc);
				}
				elseif (\is_callable($callback))
				{
					$callback($dc);
				}
			}
		}

		$objVersions->create();
    }
}
