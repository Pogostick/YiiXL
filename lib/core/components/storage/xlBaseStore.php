<?php
class xlBaseStore extends xlComponent
{
	//*************************************************************************
	//* Private Members
	//*************************************************************************

	protected $_
	


    /**
     * @return string
     */
    public function store()
    {
        $data        = $this->_config->toArray();
        $sectionName = $this->_config->getSectionName();

        if (is_string($sectionName)) {
            $data = array($sectionName => $data);
        }

        $arrayString = "<?php\n"
                     . "return " . var_export($data, true) . ";\n";

        return $arrayString;
    }
}
