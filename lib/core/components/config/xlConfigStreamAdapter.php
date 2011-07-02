<?php
/**
 * @property xlConfig $config
 *
 */
abstract class xlConfigStreamAdapter extends CViewRenderer  implements xlIStreamAdapter
{
	//*************************************************************************
	//* Private Members
	//*************************************************************************

	/**
	 * @var xlConfig
	 */
	protected $_config;

	//*************************************************************************
	//* Public Methods
	//*************************************************************************

    /**
     * @param xlConfig $config
     * @return xlBaseConfigWriter
     */
    public function setConfig( xlConfig $config )
    {
        $this->_config = $config;
        return $this;
    }

    /**
     * @return xlConfig
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
	 * Creates a representation of the config suitable for storage
     * @return mixed
     */
    abstract public function render();

}