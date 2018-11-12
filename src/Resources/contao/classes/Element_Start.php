<?php
namespace leycommediasolutions\contao_elementsets\Resources\contao\classes;
class Element_Start extends \ContentElement
{
    /**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_elementset_start';
	/**
     * Compile the content element
     */
    protected function compile()
    {
        if (TL_MODE == 'BE') {
            $this->genBeOutput();
        } else {
            $this->genFeOutput();
        } 
    }
    /**
     * @return string
     */
    private function genBeOutput()
    {
        $resultelementset_id = $this->Database->prepare("SELECT elementset_id FROM tl_content WHERE id=? ")
                                        ->execute($this->id);

        foreach($resultelementset_id->row()  as $k=>$v)
        {
            $this->strTemplate          = 'be_wildcard';
            $this->Template             = new \BackendTemplate($this->strTemplate);
            $this->Template->wildcard   = "### ". $GLOBALS['TL_LANG']['FFL']['elementset_start'][0] . $v. " ###";
        }
    }
    /**
     * @return string
     */
    private function genFeOutput()
    {
        $resultelementset_id = $this->Database->prepare("SELECT elementset_id FROM tl_content WHERE id=? ")
                                        ->execute($this->id);
        foreach($resultelementset_id->row()  as $k=>$v)
        {
            $resultelementset_id_new = $this->Database->prepare("SELECT elementset_class FROM tl_elementsets WHERE id=? ")
                                        ->execute($v);
            foreach($resultelementset_id_new->row()  as $kk=>$vv)
            {
                if ($v != '') {
                    $this->Template->elementset_class = 'ce_elementset '. $vv;
                }
                else
                {
                    $this->Template->elementset_class = 'ce_elementset';
                }
            }
        }
    }
}
class_alias(Element_Start::class, 'Element_Start');