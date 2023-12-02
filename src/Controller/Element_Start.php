<?php
namespace leycommediasolutions\contao_elementsets\Controller;

use Contao\ContentElement;

class Element_Start extends ContentElement
{
    protected $strTemplate = "ce_elementset_start"; 

    public function generate()
	{   
        $resultelementset_id = $this->Database->prepare("SELECT elementset_id FROM tl_content WHERE id=? ")
                                              ->limit(1)
                                              ->execute($this->id);

        if($resultelementset_id->numRows > 0){                                        
            foreach($resultelementset_id->row()  as $k=>$v)
            {
                $resultelementset_id_new = $this->Database->prepare("SELECT customTplStart FROM tl_elementsets WHERE id=? AND addWrapper=1")
                                                          ->limit(1)
                                                          ->execute($v);

                if($resultelementset_id_new->numRows > 0){ 

                    $result_array = $resultelementset_id_new->row();
                    if($result_array["customTplStart"]){
                        $this->strTemplate = $result_array["customTplStart"];
                        if($this->customTpl){
                            $this->strTemplate = $this->customTpl;
                        }
                    }
                }
            }
        }        
        return parent::generate();
    }

    protected function compile()
    {
        if (TL_MODE == 'BE') {
            $this->genBeOutput();
        } else {
            $this->genFeOutput();
        } 
    }
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
    private function genFeOutput()
    {
        $this->Template->elementset_class = "ce_elementset";
        $this->Template->addWrapper = "";

        $resultelementset_id = $this->Database->prepare("SELECT elementset_id FROM tl_content WHERE id=? ")
                                              ->limit(1)
                                              ->execute($this->id);

        if($resultelementset_id->numRows > 0){                                        
            foreach($resultelementset_id->row()  as $k=>$v)
            {
                $resultelementset_id_new = $this->Database->prepare("SELECT elementset_class, addWrapper FROM tl_elementsets WHERE id=? ")
                                                          ->limit(1)
                                                          ->execute($v);

                if($resultelementset_id_new->numRows > 0){ 

                    $result_array = $resultelementset_id_new->row();
                    if($result_array["elementset_class"] && $result_array["addWrapper"]){
                        $this->Template->elementset_class = 'ce_elementset '. $result_array["elementset_class"];
                    }
                    if($result_array["addWrapper"]){
                        $this->Template->addWrapper = $result_array["addWrapper"];
                    }
                }
            }
        }
    }    
}
class_alias(Element_Start::class, 'Element_Start');