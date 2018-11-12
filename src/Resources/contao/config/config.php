<?php
array_insert($GLOBALS['BE_MOD']['content'], 8, array
(
    'elementsets' => array
	(
        'tables'      => array('tl_elementsets', 'tl_content', 'tl_elementsets_category'),
	)
));

$GLOBALS['TL_CTE']['elementset']['elementset_start'] = 'leycommediasolutions\contao_elementsets\Resources\contao\classes\Element_Start';
$GLOBALS['TL_CTE']['elementset']['elementset_end'] = 'leycommediasolutions\contao_elementsets\Resources\contao\classes\Element_End';


$GLOBALS['TL_PERMISSIONS'][] = 'elementsets_s';
$GLOBALS['TL_PERMISSIONS'][] = 'elementsets_p';

$GLOBALS['TL_WRAPPERS']['start'][] = 'elementset_start'; 
$GLOBALS['TL_WRAPPERS']['stop'][] = 'elementset_end';

if (TL_MODE == 'BE') {
    $GLOBALS['TL_CSS'][] = 'bundles/contao_elementsets/css/ElementSet.css';
}
