<?php
/**
 * The bootstrap file for the TinyMVC framework.
 *
 * @package TinyMVC
 * @copyright (c) 2012 Justin "4sak3n 0ne" Jahn
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/us Attribution-Noncommercial-Share Alike 3.0
 */

    //
    // Settings
    //
        // Debugging
        ini_set('display_errors', 'on');

        // Define the base path of the scripts
        define('BASE_PATH', realpath(dirname(__FILE__) . '/..'));

        // Include Path
        ini_set('include_path', implode(PATH_SEPARATOR, array(
            '.',
            BASE_PATH . '/lib',
            BASE_PATH . '/app/models'
        )));
    
    //
    // SESSION
    //
        if (!isset($_SESSION)) {
            session_start();
        }

    //
    // Autoloader
    //
        // Make sure we have a compatible function: stream_resolve_include_path
        if (!function_exists('stream_resolve_include_path')) {
            /**
             * A compatibility function that searches for a file within an include path.
             *
             * @param String $sFile The filename.
             * @return bool
             */
            function stream_resolve_include_path($sFile) {
                // Try the path without anything added
                if (file_exists($sFile)) return $sFile;

                // Fetch the include paths
                $aPath = explode(PATH_SEPARATOR, get_include_path());

                // Loop through and try the file in every path
                foreach ($aPath as $sPath) {
                    $sReturn = $sPath . DIRECTORY_SEPARATOR . $sFile;
                    if (file_exists($sReturn)) return $sReturn;
                }

                return false;
            }
        }

        /**
         * A simple function to check the include path for the provided class
         * and include it.
         *
         * @param String $name The class name.
         */
        function TinyAutoloader($name)
        {
            // Explode the name of the class by underscores and capitalize every
            /// word.
            $aName = explode('_', $name);
            $aName = array_map('ucfirst', $aName);

            // Implode the array by slashes
            $sName = implode('/', $aName) . '.php';

            // Try to find the class path and require it.
            $sPath = stream_resolve_include_path($sName);
            if ($sPath !== false) require_once($sPath);
        }

        spl_autoload_register('TinyAutoloader');

    //
    // TinyTemplate
    //
        $oTemplate = new TinyTemplate();
        $oTemplate->setLayoutDirectory(BASE_PATH . '/app/views/layouts')
                  ->setScriptDirectory(BASE_PATH . '/app/views/scripts')
                  ->setLayout('default.phtml');

    //
    // Bootstrap
    //
        try {
            TinyMVC::getInstance()
                ->setControllerPath(BASE_PATH . '/app/controllers')
                ->setTemplateObject($oTemplate)
                ->run();
        } catch (Exception $e) {
            if (in_array(strtolower(ini_get('display_errors')), array('on', '1', 'true'))) {
                throw $e;
            } else {
                header('HTTP/1.0 404 NOT FOUND');
                header('Status: 404 NOT FOUND');

                echo '<h1>404 NOT FOUND</h1>';

                die();
            }
        }
