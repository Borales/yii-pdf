<?php

/**
 * The EYiiPdfException exception class.
 * @author Borales <bordun.alexandr@gmail.com>
 * @link https://github.com/Borales/yii-pdf
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package application.extensions.yii-pdf.EYiiPdf
 * @version 0.1
 */
class EYiiPdf extends CApplicationComponent
{
    /**
     * @var array Key-value pairs parameters
     */
    public $params = array();

    /**
     * @var mPDF|null
     */
    protected $_mPDF = null;

    /**
     * @var HTML2PDF|null
     */
    protected $_HTML2PDF = null;

    protected $_importedPaths = array();

    /**
     * @param string $library_name
     * @param array $constructorClassArgs
     */
    protected function initLibrary($library_name, $constructorClassArgs = array())
    {
        if( !isset($this->params[$library_name]) || empty($this->params[$library_name]) )
            throw new EYiiPdfException(Yii::t('yii-pdf', 'You must set parameters first'), 500);

        # Fix for HTML2PDF - class filename is "html2pdf.class.php"
        if( isset($this->params[$library_name]['classFile']) && !isset(Yii::$classMap[$library_name]) )
            Yii::$classMap[$library_name] = Yii::getPathOfAlias($this->params[$library_name]['librarySourcePath']) . DIRECTORY_SEPARATOR . $this->params[$library_name]['classFile'];

        # Reserve required constants
        $this->initConstants($library_name);

        $sourcePath = $this->params[$library_name]['librarySourcePath'];
        if( !key_exists($sourcePath, $this->_importedPaths) )
            $this->_importedPaths[$sourcePath] = Yii::import($sourcePath, true);

        # Merging params arrays (preserving params' indexes)
        $args = $constructorClassArgs + array_values($this->params[$library_name]['defaultParams']);

        $reflClass = isset($this->params[$library_name]['class']) ? $this->params[$library_name]['class'] : $library_name;

        $r = new ReflectionClass( $reflClass );
        $this->{"_" . $library_name} = $r->newInstanceArgs($args);
    }

    /**
     * Registering required constants
     * @param string $library_name
     */
    protected function initConstants($library_name)
    {
        if(!isset($this->params[$library_name]['constants']))
            return;

        foreach( (array)$this->params[$library_name]['constants']  as $constant_name => $constant_value )
            defined($constant_name) or define($constant_name, $constant_value);
    }

    /**
     * @return mPDF
     */
    public function mPDF()
    {
        $this->initLibrary(__FUNCTION__, func_get_args());
        return $this->_mPDF;
    }

    /**
     * @return HTML2PDF
     */
    public function HTML2PDF()
    {
        $this->initLibrary(__FUNCTION__, func_get_args());
        return $this->_HTML2PDF;
    }
}

/**
 * The EYiiPdfException exception class.
 * @author Borales <bordun.alexandr@gmail.com>
 * @package application.extensions.yii-pdf.EYiiPdf
 * @version 0.1
 */
class EYiiPdfException extends CException {}