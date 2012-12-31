<?php
/**
 * The controller superclass for TinyMVC.
 *
 * @package TinyMVC
 * @copyright (c) 2008 Justin "4sak3n 0ne" Jahn
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/us Attribution-Noncommercial-Share Alike 3.0
 */

/**
 * A superclass used to create controllers for TinyMVC.
 *
 * Every controller used by the TinyMVC framework should inherit from this class.
 *
 * @package Template
 * @copyright (c) 2008 Justin "4sak3n 0ne" Jahn
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/us Attribution-Noncommercial-Share Alike 3.0
 */
class TinyController
{
    /**
     * The view renderer class instance.
     *
     * @var TinyTemplate
     */
    protected $view;

    /**
     * Set up the controller functionality and place the TinyTemplate instance
     * in an easier to use variable.
     *
     * @param TinyTemplate $view The view object to use when rendering.
     */
    public function __construct($view)
    {
        $this->view = $view;
    }
}
