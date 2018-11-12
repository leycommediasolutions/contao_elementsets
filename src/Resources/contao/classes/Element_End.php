<?php
namespace leycommediasolutions\contao_elementsets\Resources\contao\classes;
class Element_End extends \ContentElement
{
    /**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_elementset_end';
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
            $this->Template->wildcard   = "### ". $GLOBALS['TL_LANG']['FFL']['elementset_end'][0] .$v ." ###";
        }
    }
    /**
     * @return string
     */
    private function genFeOutput()
    {
        $this->Template;
    }
}
class_alias(Element_End::class, 'Element_End');