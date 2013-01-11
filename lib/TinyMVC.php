<?php
/**
 * A minimalistic approach to MVC.
 *
 * @package TinyMVC
 * @copyright (c) 2012 Justin "4sak3n 0ne" Jahn
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/us Attribution-Noncommercial-Share Alike 3.0
 */

/**
 * A minimalistic approach to the Model-View-Controller architecture.
 *
 * File Structure:
 *  /app
 *      /controllers
 *          IndexController.php
 *      /models
 *          ...
 *      /views
 *          /layouts
 *              default.phtml
 *          /scripts
 *              /index
 *                  index.phtml
 *
 * Conventions:
 *  # Controller class names, as well as file names should be CamelCased.
 *  # Controllers must have the suffix 'Controller'.
 *  # Actions must have the suffix 'Action'.
 *  # Spaces are converted to dashes; 'my action' becomes my-actionAction.
 *  # Models may follow any format you prefer; there is no clear definition of them.
 *  # After an action is run a view corresponding to {controller}/{action}.phtml is rendered.
 *      * View folders and filenames are always lowercase.
 *      * Controller names for views do not contain the 'Controller' suffix.
 *  # Actions that return false do not have views automatically rendered.
 *
 * @package TinyMVC
 * @copyright (c) 2012 Justin "4sak3n 0ne" Jahn
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/us Attribution-Noncommercial-Share Alike 3.0
 */
class TinyMVC
{
    /**
     * The singleton instance of this class.
     *
     * @var TinyMVC
     */
    protected static $_instance;

    /**
     * The location of the Controller files.
     *
     * @var string
     */
    protected $_controllerPath;

    /**
     * The current Controller.
     *
     * @var string
     */
    protected $_controller;

    /**
     * The current Action.
     *
     * @var string
     */
    protected $_action;

    /**
     * The template object.
     *
     * @var TinyTemplate
     */
    protected $_template;

    /**
     * Prevent copying the class as it is a singleton.
     *
     * @return void
     */
    protected function __clone() {}

    /**
     * Prevent creating new instances of this object.
     *
     * @return void
     */
    protected function __construct()
    {
        $this->_controller     = 'index';
        $this->_action         = 'index';
        $this->_controllerPath = null;
        $this->_template       = null;
    }

    /**
     * Remove all special characters and convert spaces to dashes to form a valid
     * method name.
     *
     * @param string $sQuery The query string to parse.
     * @return string Fully parsed string.
     */
    protected function _filter($sQuery)
    {
        // Trim whitespace and remove special characters
        $sQuery = urldecode($sQuery);
        $sQuery = trim($sQuery);
        $sQuery = preg_replace('/[^a-z0-9\s]/i', '', $sQuery);

        // Convert one or more spaces to a dash
        $sQuery = preg_replace('/\s+/i', '-', $sQuery);

        // Make everything lowercase
        $sQuery = strtolower($sQuery);

        return $sQuery;
    }

    /**
     * Get the singleton instance of this class.
     *
     * @return TinyMVC
     */
    public static function getInstance()
    {
        // Create a new instance if we don't have one
        if (self::$_instance === null) self::$_instance = new self();
        return self::$_instance;
    }

    /**
     * Set the template object.
     *
     * @param TinyTemplate $oTemplate The TinyTemplate object to utilize.
     * @return TinyMVC Allows method chaining.
     */
    public function setTemplateObject(TinyTemplate $oTemplate)
    {
        $this->_template = $oTemplate;
        return $this;
    }

    /**
     * Fetch the template object.
     *
     * @return TinyTemplate
     */
    public function getTemplateObject()
    {
        return $this->_template;
    }

    /**
     * Set the Controller class name and file path.
     *
     * @param string $sPath The full path to the controller.
     * @return TinyMVC Allows method chaining.
     */
    public function setControllerPath($sPath)
    {
        $this->_controllerPath = $sPath;
        return $this;
    }

    /**
     * Fetch the full path to the controller file.
     *
     * @return string
     */
    public function getControllerPath()
    {
        return $this->_controllerPath;
    }

    /**
     * Get the short name of the current controller.
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->_controller;
    }
    
    /**
     * Get the short name of the current action.
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->_action;
    }

    /**
     * Call controller and action specified.
     *
     * @throws Exception
     * @param string $controller The name of the controller minus the suffix 'Controller'.
     * @param string $action     The name of the action minus the suffic 'Action'.
     * @return void
     */
    public function call($controller, $action)
    {
        // Format the controller and action
        $sAction     = strtolower($action) . 'Action';
        $sController = ucfirst($controller) . 'Controller';

        // Generate the path to the controller file.
        $sPath = $this->_controllerPath . '/' . $sController . '.php';

        // Verify the file exists and is readable.
        if (stream_resolve_include_path($sPath) === false) {
            throw new Exception(sprintf(
                'The controller, %s was not found in the path %s.',
                $sController,
                $sPath
            ));
        }

        // Include the class file.  Note that the controllers shouldn't be in
        /// the include path as they are typically only included once per
        /// request.
        require_once($sPath);

        // Make sure the class exists
        if (!class_exists($sController)) {
            throw new Exception(sprintf(
                'The class name, %s does not match the class contained within %s.',
                $sController,
                $sPath
            ));
        }

        // Make sure the action requested exists
        if (!method_exists($sController, $sAction)) {
            throw new Exception(sprintf(
                'The action %s does not exist in %s.',
                $sAction,
                $sController
            ));
        }

        // Create the class instance and call the method
        $oController = new $sController($this->_template);

        try {
            $bResult = $oController->{$sAction}();
        } catch (Exception $e) {
            throw $e;
        }

        // If the result was not false, then we will automatically render.
        if ($bResult !== false) {
            $sPath = sprintf('%s/%s.phtml',
                             strtolower($controller),
                             strtolower($action));

            $this->_template->render($sPath);
        }
    }

    /**
     * Process the current request.
     *
     * @throws Exception
     * @return void
     */
    public function run()
    {
        // Make sure they gave us a controller path
        if ($this->_controllerPath == null) {
            throw new Exception('A controller path was not provided.');
        }

        // Make sure they provided us a template object.
        if ($this->_template == null) {
            throw new Exception('A template object was not provided.');
        }

        // We support two different types of request handling.  GET variables,
        /// and reading the REQUEST_URI directly.  This block normalizes the
        /// to, and provides the rest of the code the MVC request logic only.
        if (!isset($_GET['q'])) {
            // Remove the query string from the URI
            $iPosition = strpos($_SERVER['REQUEST_URI'], '?');

            // If we found a ?, then this begins the query string and we should
            /// exclude it from the request string.
            if ($iPosition !== false) {
                $sRequest = urldecode(substr($_SERVER['REQUEST_URI'], 0, $iPosition));
            } else {
                // We did not find a query string.
                $sRequest = urldecode($_SERVER['REQUEST_URI']);
            }
        } else {
            // The GET method makes things easier, but isn't as clean.
            $sRequest = urldecode($_GET['q']);
        }

        // Parse the query string.  The first slash will produce an empty
        /// first array element every time.
        $aQuery = explode('/', $sRequest);
        array_shift($aQuery);

        // If we only have one element in our array, it will act as an action.
        if (count($aQuery) == 1) {
            // Make sure that the element isn't empty before continuing
            if ($aQuery[0]) {
                $this->_action = $this->_filter($aQuery[0]);
            }

            // Remove the only element of the array for later processing
            $aQuery = array();
        } else {
            // There was a controller and an action specified
            $this->_controller = $this->_filter($aQuery[0]);
            $this->_action     = $this->_filter($aQuery[1]);

            // Pop the first two elements off for later processing
            array_shift($aQuery);
            array_shift($aQuery);
        }

        // Loop through each additional request element and add it as a request
        /// flag.
        foreach($aQuery as $sFlag) {
            if (empty($sFlag)) continue;
            $_REQUEST[$sFlag] = true;
        }
        
        // Set the default title of the current page
        $this->_template->title = sprintf('%s/%s', $this->_controller, $this->_action);

        // Call the requested controller and action
        $this->call($this->_controller, $this->_action);
    }
}
