<?php
/**
 *
 * @property string $fileName
 */
abstract class xlConfigFileWriter extends xlBaseConfigWriter
{
	//*************************************************************************
	//* Private Members
	//*************************************************************************

    /**
     * @var string
     */
    protected $_fileName = null;

	//*************************************************************************
	//* Public Methods
	//*************************************************************************

	/**
	 * Writes configuration to a file
	 * @param string $fileName
	 * @param xlConfig $config
	 * @return bool
	 */
    public function write( $fileName = null, xlConfig $config = null )
    {
		if ( null !== $fileName )
			$this->_fileName = $fileName;

		if ( null !== $config )
			$this->_config = $config;

        if ( null === $this->_fileName )
			throw new xlException( 'Must set the "fileName" property before calling the "write" method.' );

        if ( null === $this->_config )
			throw new xlException( 'Cowardly refusing to write empty configuration to file.' );

        $_output = $this->render();

        if ( false === ( $_result = @file_put_contents( $this->_fileName, $_output ) ) )
			throw new xlException( 'Error saving configuration to file "' . $this->_fileName . '"' );

		return $_result;
    }

	/**
	 * @param string $fileName
	 * @return void
	 */
	public function setFileName( $fileName )
	{
		$this->_fileName = $fileName;
	}

	/**
	 * @return string
	 */
	public function getFileName()
	{
		return $this->_fileName;
	}
}
