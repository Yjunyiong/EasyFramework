<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.easyframework.net>.
 */

namespace Easy\Mvc\Controller\Component;

use Easy\Mvc\Controller\Component;
use Easy\Mvc\Controller\Controller;
use Easy\Mvc\Controller\Event\InitializeEvent;
use Easy\Network\AcceptHeader;
use Easy\Network\Request;
use Easy\Network\Response;

/**
 * Request object for handling alternative HTTP requests
 *
 * Alternative HTTP requests can come from wireless units like mobile phones, palmtop computers,
 * and the like. These units have no use for Ajax requests, and this Component can tell how EasyFw
 * should respond to the different needs of a handheld computer and a desktop machine.
 *
 * @since 1.4
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class RequestHandler extends Component {

    /**
     * Holds the reference to Controller::$request
     *
     * @var Request
     */
    public $request;

    /**
     * Holds the reference to Controller::$response
     *
     * @var Response
     */
    public $response;

    /**
     * Contains the file extension parsed out by the Router
     *
     * @var string
     * @see Router::parseExtensions()
     */
    public $ext = null;

    /**
     * Checks to see if a file extension has been parsed by the Router, or if the
     * HTTP_ACCEPT_TYPE has matches only one content type with the supported extensions.
     * If there is only one matching type between the supported content types & extensions,
     * and the requested mime-types, RequestHandler::$ext is set to that value.
     *
     * @param Controller $controller A reference to the controller
     * @param array $settings Array of settings to _set().
     * @return void
     * @see Router::parseExtensions()
     */
    public function initialize(InitializeEvent $event) {
        $this->controller = $event->getController();
        $this->request = $this->controller->getRequest();
        $this->response = new Response();

        if (isset($this->request->params['ext'])) {
            $this->ext = $this->request->params['ext'];
        }
        $this->params = $this->controller->request->params;
    }

    /**
     * Returns true if the current HTTP request is Ajax, false otherwise
     *
     * @return boolean True if call is Ajax
     * @deprecated use `$this->request->is('ajax')` instead.
     */
    public function isAjax() {
        return $this->request->is('ajax');
    }

    /**
     * Returns true if the current HTTP request is coming from a Flash-based client
     *
     * @return boolean True if call is from Flash
     * @deprecated use `$this->request->is('flash')` instead.
     */
    public function isFlash() {
        return $this->request->is('flash');
    }

    /**
     * Returns true if the current request is over HTTPS, false otherwise.
     *
     * @return boolean True if call is over HTTPS
     * @deprecated use `$this->request->is('ssl')` instead.
     */
    public function isSSL() {
        return $this->request->isSecure();
    }

    /**
     * Returns true if the current call accepts an XML response, false otherwise
     *
     * @return boolean True if client accepts an XML response
     */
    public function isXml() {
        return $this->prefers('xml');
    }

    /**
     * Returns true if the current call accepts an JSON response, false otherwise
     *
     * @return boolean True if client accepts an JSON response
     */
    public function isJson() {
        return $this->prefers('json');
    }

    /**
     * Returns true if the current call accepts an RSS response, false otherwise
     *
     * @return boolean True if client accepts an RSS response
     */
    public function isRss() {
        return $this->prefers('rss');
    }

    /**
     * Returns true if the current call accepts an Atom response, false otherwise
     *
     * @return boolean True if client accepts an RSS response
     */
    public function isAtom() {
        return $this->prefers('atom');
    }

    /**
     * Returns true if user agent string matches a mobile web browser, or if the
     * client accepts WAP content.
     *
     * @return boolean True if user agent is a mobile web browser
     */
    public function isMobile() {
        return $this->request->is('mobile') || $this->accepts('wap');
    }

    /**
     * Returns true if the client accepts WAP content
     *
     * @return boolean
     */
    public function isWap() {
        return $this->prefers('wap');
    }

    /**
     * Returns true if the current call a POST request
     *
     * @return boolean True if call is a POST
     * @deprecated Use $this->request->is('post'); from your controller.
     */
    public function isPost() {
        return $this->request->isMethod('post');
    }

    /**
     * Returns true if the current call a PUT request
     *
     * @return boolean True if call is a PUT
     * @deprecated Use $this->request->is('put'); from your controller.
     */
    public function isPut() {
        return $this->request->isMethod('put');
    }

    /**
     * Returns true if the current call a GET request
     *
     * @return boolean True if call is a GET
     * @deprecated Use $this->request->is('get'); from your controller.
     */
    public function isGet() {
        return $this->request->isMethod('get');
    }

    /**
     * Returns true if the current call a DELETE request
     *
     * @return boolean True if call is a DELETE
     * @deprecated Use $this->request->is('delete'); from your controller.
     */
    public function isDelete() {
        return $this->request->isMethod('delete');
    }

    /**
     * Adds/sets the Content-type(s) for the given name.  This method allows
     * content-types to be mapped to friendly aliases (or extensions), which allows
     * RequestHandler to automatically respond to requests of that type in the
     * startup method.
     *
     * @param string $name The name of the Content-type, i.e. "html", "xml", "css"
     * @param mixed $type The Content-type or array of Content-types assigned to the name,
     *    i.e. "text/html", or "application/xml"
     * @return void
     * @deprecated use `$this->response->type()` instead.
     */
    public function setContent($name, $type = null) {
        $this->response->type(array($name => $type));
    }

    /**
     * Gets the server name from which this request was referred
     *
     * @return string Server address
     * @deprecated use $this->request->referer() from your controller instead
     */
    public function getReferer() {
        return $this->request->referer(false);
    }

    /**
     * Gets remote client IP
     *
     * @param boolean $safe
     * @return string Client IP address
     * @deprecated use $this->request->clientIp() from your,  controller instead.
     */
    public function getClientIP($safe = true) {
        return $this->request->getClientIp($safe);
    }

    /**
     * Determines which content types the client accepts.  Acceptance is based on
     * the file extension parsed by the Router (if present), and by the HTTP_ACCEPT
     * header. Unlike Request::accepts() this method deals entirely with mapped content types.
     *
     * Usage:
     *
     * `$this->RequestHandler->accepts(array('xml', 'html', 'json'));`
     *
     * Returns true if the client accepts any of the supplied types.
     *
     * `$this->RequestHandler->accepts('xml');`
     *
     * Returns true if the client accepts xml.
     *
     * @param mixed $type Can be null (or no parameter), a string type name, or an
     *   array of types
     * @return mixed If null or no parameter is passed, returns an array of content
     *   types the client accepts.  If a string is passed, returns true
     *   if the client accepts it.  If an array is passed, returns true
     *   if the client accepts one or more elements in the array.
     * @see RequestHandlerComponent::setContent()
     */
    public function accepts($type = null) {
        return $this->request->accepts($type);
    }

    /**
     * Determines which content-types the client prefers.  If no parameters are given,
     * the single content-type that the client most likely prefers is returned.  If $type is
     * an array, the first item in the array that the client accepts is returned.
     * Preference is determined primarily by the file extension parsed by the Router
     * if provided, and secondarily by the list of content-types provided in
     * HTTP_ACCEPT.
     *
     * @param mixed $type An optional array of 'friendly' content-type names, i.e.
     *   'html', 'xml', 'js', etc.
     * @return mixed If $type is null or not provided, the first content-type in the
     *    list, based on preference, is returned.  If a single type is provided
     *    a boolean will be returned if that type is preferred.
     *    If an array of types are provided then the first preferred type is returned.
     *    If no type is provided the first preferred type is returned.
     * @see RequestHandlerComponent::setContent()
     */
    public function prefers($type = null) {
        $acceptRaw = AcceptHeader::fromString($this->request->header->getItem("Accept"))->all();

        if (empty($acceptRaw)) {
            return $this->ext;
        }

        $accepts = array_shift($acceptRaw);
        $accepts = $this->mapType($accepts);

        if ($type == null) {
            if (empty($this->ext) && !empty($accepts)) {
                return $accepts[0];
            }
            return $this->ext;
        }

        $types = (array) $type;

        if (count($types) === 1) {
            if (!empty($this->ext)) {
                return in_array($this->ext, $types);
            }
            return in_array($types[0], $accepts);
        }

        $intersect = array_values(array_intersect($accepts, $types));
        if (empty($intersect)) {
            return false;
        }
        return $intersect[0];
    }

    /**
     * Sets the response header based on type map index name.  This wraps several methods
     * available on Response. It also allows you to use Content-Type aliases.
     *
     * @param mixed $type Friendly type name, i.e. 'html' or 'xml', or a full content-type,
     *    like 'application/x-shockwave'.
     * @param array $options If $type is a friendly type name that is associated with
     *    more than one type of content, $index is used to select which content-type to use.
     * @return boolean Returns false if the friendly type name given in $type does
     *    not exist in the type map, or if the Content-type header has
     *    already been set by this method.
     * @see RequestHandlerComponent::setContent()
     */
    public function respondAs($type, $options = array()) {
        $defaults = array('index' => null, 'charset' => null, 'attachment' => false);
        $options = $options + $defaults;

        if (strpos($type, '/') === false) {
            $cType = $this->response->getMimeType($type);
            if ($cType === false) {
                return false;
            }
            if (is_array($cType) && isset($cType[$options['index']])) {
                $cType = $cType[$options['index']];
            }
            if (is_array($cType)) {
                if ($this->prefers($cType)) {
                    $cType = $this->prefers($cType);
                } else {
                    $cType = $cType[0];
                }
            }
        } else {
            $cType = $type;
        }

        if ($cType != null) {
            if (empty($this->request->params['requested'])) {
                $this->response->type($cType);
            }

            if (!empty($options['charset'])) {
                $this->response->setCharset($options['charset']);
            }
            if (!empty($options['attachment'])) {
                $this->response->download($options['attachment']);
            }
            return true;
        }
        return false;
    }

    /**
     * Returns the current response type (Content-type header), or null if not alias exists
     *
     * @return mixed A string content type alias, or raw content type if no alias map exists,
     * 	otherwise null
     */
    public function responseType() {
        return $this->mapType($this->response->type());
    }

    /**
     * Maps a content-type back to an alias
     *
     * @param mixed $cType Either a string content type to map, or an array of types.
     * @return mixed Aliases for the types provided.
     * @deprecated Use $this->response->mapType() in your controller instead.
     */
    public function mapType($cType) {
        return $this->response->mapType($cType);
    }

}
