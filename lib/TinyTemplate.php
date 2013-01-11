<?php
/**
 * A very small template class, that allows for layouts and partial rendering.
 *
 * @package TinyMVC
 * @copyright (c) 2012 Justin "4sak3n 0ne" Jahn
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/us Attribution-Noncommercial-Share Alike 3.0
 */

/**
 * A simple template renderer that supports partial rendering and master layouts.
 *
 * NOTE: $this->content used within a layout file will render the script.  It is
 * not recommended to modify this variable directly.
 *
 * @package TinyMVC
 * @copyright (c) 2012 Justin "4sak3n 0ne" Jahn
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/us Attribution-Noncommercial-Share Alike 3.0
 */
class TinyTemplate
{
    /**
     * An associative array of variables to be passed on to the rendering.
     *
     * @var array
     */
    protected $_variables = array();

    /**
     * The directory location of the layouts.  This can be set via setLayout and getLayout.
     *
     * @var string
     */
    protected $_layouts = 'views/layouts';

    /**
     * The directory location of the scripts.  This can be set via setScript and getScript.
     *
     * @var string
     */
    protected $_scripts = 'views/scripts';
    
    /**
     * The base url of the web site.  This can be set via setBaseUrl and getBaseUrl.
     *
     * @var string
     */
    protected $_baseUrl = '';

    /**
     * The layout file to use when rendering.
     *
     * @var string
     */
    protected $_template;

    /**
     * Set the class up, and prepare for the impending render.
     *
     * @param string $strLayout The layout file relative to the layout path.
     * @return void
     */
    public function __construct($strLayout='default.phtml')
    {
        // If the layout file isn't null, set it
        if (!is_null($strLayout)) {
            // Normalize the pathname
            $this->_normalizeSlashes($strLayout);

            // Set it
            $this->_template = $strLayout;
        }
    }

    /**
     * The magic method that allows templates to use $this and grab variables.
     *
     * @return mixed The variable or null if not found.
     */
    public function __get($strName)
    {
        if (array_key_exists($strName, $this->_variables)) {
            return $this->_variables[$strName];
        } else {
            return null;
        }
    }

    /**
     * The magic method that allows us to set variables via $this.
     *
     * @return void
     */
    public function __set($strName, $mxdValue)
    {
        // Just set it, we don't need to chech anything.
        $this->_variables[$strName] = $mxdValue;
    }

    /**
     * The magic method that allows us to check if something is set via $this.
     *
     * @return bool
     */
    public function __isset($strName)
    {
        return isset($this->_variables[$strName]);
    }

    /**
     * The magic method that allows us to unset a view variable.
     *
     * @return void
     */
    public function __unset($strName)
    {
        unset($this->_variables[$strName]);
    }

    /**
     * Set the layout file to use.  Relative to the layout directory.
     *
     * @param string|null $strFile The file to use.  This can be null to disable layouts.
     * @return TinyTemplate
     */
    public function setLayout($strFile)
    {
        // If it is null, don't bother doing anything else
        if (is_null($strFile)) {
            $this->_template = null;
            return $this;
        }

        // Normalize slashes
        $this->_normalizeSlashes($strFile);

        // Check to make sure it exists
        if (!file_exists($this->_layouts . $strFile) || !is_readable($this->_layouts . $strFile))
        {
            // The file doesn't exist, throw an error
            throw new Exception('The layout file you wish to use does not exist or is not readable:' . $this->_layouts . $strFile);
        }

        // It exists, or is null, set it
        $this->_template = $strFile;

        // Allow method chaining
        return $this;
    }

    /**
     * Get the layout file to use.
     *
     * @return string
     */
    public function getLayout()
    {
        // Don't give them a slash
        return substr($this->_template, 1);
    }

    /**
     * Set the location of the layouts, this must be a valid directory.
     *
     * @param string $strDirectory The directory where layouts are held.
     * @return TinyTemplate
     */
    public function setLayoutDirectory($strDirectory)
    {
        // Make sure that we have a directory and that it is readable.
        if (is_dir($strDirectory) && is_readable($strDirectory))
            $this->_layouts = $strDirectory;
        else
            throw new Exception('Directory not found or not readable: ' . $strDirectory);

        // Allow method chaining
        return $this;
    }

    /**
     * Set the location of the scripts, this must be a valid directory.
     *
     * @param string $strDirectory The directory where layouts are held.
     * @return TinyTemplate
     */
    public function setScriptDirectory($strDirectory)
    {
        // Make sure that we have a directory and that it is readable.
        if (is_dir($strDirectory) && is_readable($strDirectory))
            $this->_scripts = $strDirectory;
        else
            throw new Exception('Directory not found or not readable: ' . $strDirectory);

        // Allow method chaining
        return $this;
    }

    /**
     * Get the location of the script directory.
     *
     * @return string
     */
    public function getScriptDirectory()
    {
        // Don't give them a slash
        return substr($this->_scripts, 1);
    }

    /**
     * Get the location of the layout directory.
     *
     * @return string
     */
    public function getLayoutDirectory()
    {
        // Don't give them a slash
        return substr($this->_layouts, 1);
    }

    /**
     * Set an array of variables to give to the view scripts.
     *
     * @param array $arrVariables An associative array of variables.
     * @return TinyTemplate
     */
    public function setArray(array $arrVariables)
    {
        // Just merge it
        $this->_variables = array_merge_recursive($this->_variables, $arrVariables);

        // Allow method chaining
        return $this;
    }

    /**
     * Sets the base URL of this web site.
     *
     * @param string $strBaseUrl The base url of the web site.
     * @return TinyTemplate 
     */
    public function setBaseUrl($strBaseUrl)
    {
        $this->_normalizeSlashes($strBaseUrl);
        $this->_baseUrl = $strBaseUrl;
    }
    
    /**
     * Gets the base URL of this web site and optionally append a path.
     *
     * @param string $url The url to be appended.
     * @return string
     */
    public function getBaseUrl($url = '')
    {
        $this->_normalizeSlashes($url);
        $url = $this->_baseUrl . $url;
        
        // If the url is empty, then give it a slash
        if ($url == '') $url = '/';

        return $url;
    }

    /**
     * Get the array of variables to be given to the view scripts.
     *
     * @return array An associative array of variables.
     */
    public function toArray()
    {
        return $this->_variables;
    }

    /**
     * Clear the variables to give to the view scripts.
     *
     * @return TinyTemplate
     */
    public function clear()
    {
        $this->_variables = array();

        // Allow method chaining
        return $this;
    }

    /**
     * Render a layout.
     *
     * @param string $strFilename The name of the file to render.
     * @param bool   $boolOutput  If the render should be immediately output to the browser.
     * @return bool|string If $boolOutput is set to false, string is returned.
     */
    public function render($strFilename, $boolOutput=true)
    {
        // Make sure the layout file exists if there is one
        if (!is_null($this->_template) && !is_file($this->_layouts . $this->_template)) {
            // Warn them, but don't throw a fatal error
            trigger_error('The layout specified does not exist: ' . $this->_layouts . $this->_template, E_USER_WARNING);

            // Set the template file to null
            $this->_template = null;
        }

        // Normalize the pathname
        $this->_normalizeSlashes($strFilename);

        // Make sure the file exists
        if (!file_exists($this->_scripts . $strFilename))
            throw new Exception('The script you are trying to render does not exist: ' . $this->_scripts . $strFilename);

        // Get the script as a string, no matter what they choose.
        $this->_variables['content'] = $this->_getInclude($this->_scripts . $strFilename);

        // We must do things a little bit differently if we have a layout to render
        if (!is_null($this->_template)) {
            // Either print out the template or return the string.
            if ($boolOutput) return include $this->_layouts . $this->_template;
            else return $this->_getInclude($this->_layouts . $this->_template);
        } else {
            // There was no template file, print or return the partial
            if ($boolOutput) {
                echo $this->_variables['content'];
                return 1;
            } else {
                return $this->_variables['content'];
            }
        }
    }

    /**
     * Render a partial layout.  This is relative to the scripts folder.
     *
     * @param string $strFilename  The name of the file to render relative to the scripts folder.
     * @param array  $arrVariables An associative array of variables to pass to the new file.
     * @param bool   $boolOutput   If the render should be immediately output to the browser.
     * @return bool|string if $boolOutput is set to false, string is returned.
     */
    protected function Partial($strFilename, array $arrVariables=array(), $boolOutput=false)
    {
        // This was an interesting decision.  Basically, I had 2 decent choices:
        // Make a new instance of this class, and set the variables as to not dirty
        // the original variable space, or temporarilly move the old ones.  I felt
        // that temporarilly moving them might be better in most circumstances.

        // Normalize the pathname
        $this->_normalizeSlashes($strFilename);

        // Make sure the file exists
        if (!file_exists($this->_scripts . $strFilename))
            throw new Exception('The script you are trying to render does not exist: ' . $this->_scripts . $strFilename);

        // Store our real variables somewhere else for now.
        $arrOldVariables = $this->_variables;

        // Set the new variables
        $this->_variables = $arrVariables;

        // Either print out the template or return the string
        $strReturn = ($boolOutput) ? include $this->_scripts . $strFilename
                                   : $this->_getInclude($this->_scripts . $strFilename);

        // Restore the variables
        $this->_variables = $arrOldVariables;

        // Return the string/success code
        return $strReturn;
    }

    /**
     * Capture the output of an include into a string.
     *
     * @param string $strFilename The file to capture output of.
     * @return string
     */
    protected function _getInclude($strFilename)
    {
        // Make sure the given file is in fact a file
        if(is_file($strFilename)) {
            // Start the output buffering
            ob_start();

            // Include the file
            include $strFilename;

            // Get the output of the include
            $strReturn = ob_get_contents();
            ob_end_clean();

            // Return the contents
            return $strReturn;
        }

        // We've made it here, meaning we don't have a valid file.
        return null;
    }

    /**
     * Make sure that there is a slash at the beginning of the filepath.
     *
     * @param string &$strPath The pathname to normalize
     * @param bool   $boolFirst If the slash at the beginning should be removed and the slash at the end should be added.
     * @return void
     */
    protected function _normalizeSlashes(&$strPath, $boolFirst=true)
    {
        // An array of slashes to check for
        $arrSlashes = array('./', '/', '\\', '//', '\\\\');

        if ($boolFirst) {
            // Make sure there is slash at the beginning of the string
            if (!in_array(substr($strPath, 0, 1), $arrSlashes) && !in_array(substr($strPath, 0, 2), $arrSlashes))
                $strPath = '/' . $strPath;

            // Make sure that there is not a / or \ at the end of the string.
            if (in_array(substr($strPath, -1, 1), $arrSlashes))
                $strPath = substr($strPath, 0, -1);
        } else {
            // Make sure there is not a slash at the beginning
            if (in_array(substr($strPath, 0, 1), $arrSlashes))
                $strPath = substr($strPath, 1);

            // Make sure there is not a double slash at the beginning either
            if (in_array(substr($strPath, 0, 2), $arrSlashes))
                $strPath = substr($strPath, 2);

            // Make sure that there is a slash at the end
            if (!in_array(substr($strPath, -1, 1), $arrSlashes))
                $strPath = $strPath . '/';
        }
    }
}
