<?php
$GLOBALS['TL_DCA']['tl_elementsets'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ctable'                      => array('tl_content'),
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		'markAsCopy'                  => 'title',
		'onload_callback' => array
		(
            array('tl_elementsets', 'checkPermission'),
            /*array('Import_ElementSets', ''),*/
		),
		'oncreate_callback' => array
		(
			array('tl_elementsets', 'adjustPermissions')
		),
		'oncopy_callback' => array
		(
			array('tl_elementsets', 'adjustPermissions')
		),
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary'
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('title'),
			'flag'                    => 1,
			'panelLayout'             => 'filter;search,limit',
			'headerFields'            => array('title', 'headline', 'author', 'inColumn', 'tstamp', 'showTeaser', 'published', 'start', 'stop'),
		),
		'label' => array
		(
			'fields'                  => array('title'),
			'format'                  => '%s',
			'label_callback'          => array('tl_elementsets', 'addPreviewImage')
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ),
			'newcategory' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_elementsets']['newcategory'],
				'href'                => 'table=tl_elementsets_category',
				'class'               => 'header_new',
				'attributes'          => 'onclick="Backend.getScrollOffset()"'
            )
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_elementsets']['edit'],
				'href'                => 'table=tl_content',
				'icon'                => 'edit.svg'
			),
			'editheader' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_elementsets']['editheader'],
				'href'                => 'act=edit',
				'icon'                => 'header.svg',
				'button_callback'     => array('tl_elementsets', 'editHeader')
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_elementsets']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.svg',
				'button_callback'     => array('tl_elementsets', 'copyCategory')
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_elementsets']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.svg',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']  . '\'))return false;Backend.getScrollOffset()"',
				'button_callback'     => array('tl_elementsets', 'deleteCategory')
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_elementsets']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.svg'
            ),           
		)
	),
	// Palettes
	'palettes' => array
	(
        '__selector__'                => array('addWrapper'),
        'default'                     => '{title_legend},title,category,preview_image,addWrapper',
    ),

    	// Subpalettes
	'subpalettes' => array
	(
		'addWrapper'                  => 'elementset_class,customTplStart,customTplEnde',
	),
    
	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_elementsets']['title'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'preview_image' => array
        (
			'label'                   => &$GLOBALS['TL_LANG']['tl_elementsets']['preview_image'],
			'exclude'                 => true,
            'inputType'               => 'fileTree',
			'eval'                    => array('filesOnly'=>true, 'fieldType'=>'radio', 'mandatory'=>true, 'tl_class'=>'clr'),
			'sql'                     => "binary(16) NULL"
        ),
		'elementset_class' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_elementsets']['elementset_class'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'category' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_elementsets']['category'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'options_callback' => array('tl_elementsets', 'getElementSetsOptions'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_elementsets'],
            'eval'             => array
            (
              'mandatory'       => true,
              'multiple'        => false,
              'size'            => 10,
              'tl_class'        => 'w50 w50h autoheight',
              'chosen'          => true
            ),
            'sql'                     => "text NULL"
        ),
        'addWrapper' => array
	(
			'label'                   => &$GLOBALS['TL_LANG']['tl_elementsets']['addWrapper'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true),
			'sql'                     => "char(1) NOT NULL default '1'"
        ),
        'customTplStart' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_elementsets']['customTplStart'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback' => static function (\Contao\DataContainer $dc)
			{
				return \Contao\Controller::getTemplateGroup('ce_elementset_start_');
			},
			'eval'                    => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50 clr'),
			'sql'                     => "varchar(64) NOT NULL default ''"
        ),
        'customTplEnde' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_elementsets']['customTplEnde'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback' => static function (\Contao\DataContainer $dc)
			{
				return \Contao\Controller::getTemplateGroup('ce_elementset_end_');
			},
			'eval'                    => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),       
	)
);
class tl_elementsets extends Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Check permissions to edit table tl_news_archive
     *
     * @throws Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function checkPermission()
    {
        if ($this->User->isAdmin)
        {
            return;
        }

        // Set root IDs
        if (empty($this->User->elementsets_s) || !\is_array($this->User->elementsets_s))
        {
            $root = array(0);
        }
        else
        {
            $root = $this->User->elementsets_s;
        }

        $GLOBALS['TL_DCA']['tl_elementsets']['list']['sorting']['root'] = $root;

        // Check permissions to add Elementsets categories
        if (!$this->User->hasAccess('create', 'elementsets_p'))
        {
            $GLOBALS['TL_DCA']['tl_elementsets']['config']['closed'] = true;
            $GLOBALS['TL_DCA']['tl_elementsets']['config']['notCreatable'] = true;
            $GLOBALS['TL_DCA']['tl_elementsets']['config']['notCopyable'] = true;
        }

        // Check permissions to delete Elementsets categories
        if (!$this->User->hasAccess('delete', 'elementsets_p'))
        {
            $GLOBALS['TL_DCA']['tl_elementsets']['config']['notDeletable'] = true;
        }

        /** @var Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
        $objSession = System::getContainer()->get('session');

        // Check current action
        switch (Input::get('act'))
        {
            case 'select':
                // Allow
                break;

            case 'create':
                if (!$this->User->hasAccess('create', 'elementsets_p'))
                {
                    throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to create Elementsets.');
                }
                break;

            case 'edit':
            case 'copy':
            case 'delete':
            case 'show':
                if (!\in_array(Input::get('id'), $root) || (Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'elementsets_p')))
                {
                    throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' Elementset ID ' . Input::get('id') . '.');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'copyAll':
                $session = $objSession->all();
                if (Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'elementsets_p'))
                {
                    $session['CURRENT']['IDS'] = array();
                }
                else
                {
                    $session['CURRENT']['IDS'] = array_intersect((array) $session['CURRENT']['IDS'], $root);
                }
                $objSession->replace($session);
                break;

            default:
                if (\strlen(Input::get('act')))
                {
                    throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' Elementsets.');
                }
                break;
        }
    }

    /**
     * Add the new Elementsets to the permissions
     *
     * @param $insertId
     */
    public function adjustPermissions($insertId)
    {
        // The oncreate_callback passes $insertId as second argument
        if (\func_num_args() == 4)
        {
            $insertId = func_get_arg(1);
        }

        if ($this->User->isAdmin)
        {
            return;
        }

        // Set root IDs
        if (empty($this->User->forms) || !\is_array($this->User->forms))
        {
            $root = array(0);
        }
        else
        {
            $root = $this->User->forms;
        }

        // The Elementset is enabled already
        if (\in_array($insertId, $root))
        {
            return;
        }

        /** @var Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface $objSessionBag */
        $objSessionBag = System::getContainer()->get('session')->getBag('contao_backend');

        $arrNew = $objSessionBag->get('new_records');

        if (\is_array($arrNew['tl_elementsets']) && \in_array($insertId, $arrNew['tl_elementsets']))
        {
            // Add the permissions on group level
            if ($this->User->inherit != 'custom')
            {
                $objGroup = $this->Database->execute("SELECT id, elementsets_s, elementsets_p FROM tl_user_group WHERE id IN(" . implode(',', array_map('\intval', $this->User->groups)) . ")");

                while ($objGroup->next())
                {
                    $arrFaqp = StringUtil::deserialize($objGroup->elementsets_p);

                    if (\is_array($arrFaqp) && \in_array('create', $arrFaqp))
                    {
                        $arrFaqs = StringUtil::deserialize($objGroup->elementsets_s, true);
                        $arrFaqs[] = $insertId;

                        $this->Database->prepare("UPDATE tl_user_group SET elementsets_s=? WHERE id=?")
                                        ->execute(serialize($arrFaqs), $objGroup->id);
                    }
                }
            }

            // Add the permissions on user level
            if ($this->User->inherit != 'group')
            {
                $objUser = $this->Database->prepare("SELECT elementsets_s, elementsets_p FROM tl_user WHERE id=?")
                                            ->limit(1)
                                            ->execute($this->User->id);

                $arrFaqp = StringUtil::deserialize($objUser->elementsets_p);

                if (\is_array($arrFaqp) && \in_array('create', $arrFaqp))
                {
                    $arrFaqs = StringUtil::deserialize($objUser->elementsets_s, true);
                    $arrFaqs[] = $insertId;

                    $this->Database->prepare("UPDATE tl_user SET elementsets_s=? WHERE id=?")
                                    ->execute(serialize($arrFaqs), $this->User->id);
                }
            }

            // Add the new element to the user object
            $root[] = $insertId;
            $this->User->elementsets_s = $root;
        }
    }
    /**
     * Die Optionen werden aus tl_elementsets_category herausgenommen
     */
    public function getElementSetsOptions(DataContainer $dc)
    {
        $category = array();
        $cat = 0;
        $result = $this->Database->prepare("SELECT category FROM tl_elementsets_category")
                                ->execute();

        while($result->next())
        {
            foreach ($result->row() as $k=>$v)
            {
                $category[$cat] = $v;
            }
            $cat++;
        }            
        return $category;
    }
    /**
     * Return the edit header button
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
    public function editHeader($row, $href, $label, $title, $icon, $attributes)
    {
        return $this->User->canEditFieldsOf('tl_elementsets') ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    /**
     * Return the copy category button
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
    public function copyCategory($row, $href, $label, $title, $icon, $attributes)
    {
        return $this->User->hasAccess('create', 'elementsets_p') ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    /**
     * Return the delete category button
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
    public function deleteCategory($row, $href, $label, $title, $icon, $attributes)
    {
        return $this->User->hasAccess('delete', 'elementsets_p') ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }
    	/**
	 * Add an image to each record
	 *
	 * @param array  $row
	 * @param string $label
	 *
	 * @return string
	 */
	public function addPreviewImage($row, $label)
	{
		if ($row['preview_image'] != '')
		{
			$objFile = FilesModel::findByUuid($row['preview_image']);

			if ($objFile !== null)
			{
				$label = Image::getHtml(\System::getContainer()->get('contao.image.image_factory')->create(TL_ROOT . '/' . $objFile->path, array(100, 75, 'center_top'))->getUrl(TL_ROOT), '', 'class="theme_preview titel_elementsets"') . ' ' . $label; // Andere Klasse einbinden!
			}
		}

		return $label;
    }
    public function import_ElementSets($dc)
    {

    }
}
