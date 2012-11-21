<?php

/**
 * EasyPHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Easy Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2005-2012, Easy Software Foundation, Inc. (http://cakefoundation.org)
 * @link http://cakephp.org EasyPHP(tm) Project
 * @since EasyPHP(tm) v 2.2
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Mvc\Routing\Filter;

use Easy\Core\Config;
use Easy\Event\Event;
use Easy\Mvc\Routing\DispatcherFilter;
use Easy\Mvc\View\View;
use Easy\Utility\Inflector;

/**
 * This filter will check wheter the response was previously cached in the file system
 * and served it back to the client if appropriate.
 */
class CacheDispatcher extends DispatcherFilter
{

    /**
     * Default priority for all methods in this filter
     * This filter should run before the request gets parsed by router
     *
     * @var int
     */
    public $priority = 9;

    /**
     * Checks whether the response was cached and set the body accordingly.
     *
     * @param Event $event containing the request and response object
     * @return Response with cached content if found, null otherwise
     */
    public function beforeDispatch($event)
    {
        if (Config::read('Cache.check') !== true) {
            return;
        }

        $path = $event->data['request']->here();
        if ($path == '/') {
            $path = 'home';
        }
        $path = strtolower(Inflector::slug($path));

        $filename = CACHE . 'views/' . $path . '.php';

        if (!file_exists($filename)) {
            $filename = CACHE . 'views/' . $path . '_index.php';
        }
        if (file_exists($filename)) {
            $controller = null;
            $view = new View($controller);
            $result = $view->renderCache($filename, microtime(true));
            if ($result !== false) {
                $event->stopPropagation();
                $event->data['response']->body($result);
                return $event->data['response'];
            }
        }
    }

}