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

namespace Easy\View\Engine;

use Easy\Core\Config;
use Easy\IO\Folder;
use Easy\Network\Request;
use Easy\Utility\Hash;
use Easy\Utility\Inflector;
use Easy\View\Engine\ITemplateEngine;
use Smarty;

/**
 * This class handles the smarty engine 
 * @since 0.1
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class SmartyEngine implements ITemplateEngine
{

    /**
     * @var Smarty Smarty Object
     */
    protected $smarty;
    protected $options;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        //Set the options, loaded from the config file
        $this->options = Config::read('View.options');
        //Instanciate a Smarty object
        $this->smarty = new Smarty();
        /*
         * This is to mute all expected erros on Smarty and pass to error handler 
         * TODO: Try to get a better implementation 
         */
        Smarty::muteExpectedErrors();
        //Build the template directory
        $this->loadOptions();
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function display($layout, $view, $ext = null, $output = true)
    {
        list(, $view) = namespaceSplit($view);
        $ext = empty($ext) ? "tpl" : $ext;
        // If the view not exists...
//        if (!App::path("View", $view, $ext)) {
//            $errors = explode("/", $view);
//            throw new Error\MissingViewException(array(
//                "view" => $errors[1] . "." . $ext,
//            ));
//        }
        // ...display it
        if (!empty($layout)) {
//            if (!App::path("Layout", $layout, $ext)) {
//                throw new Error\MissingLayoutException(array(
//                    "layout" => $layout . $ext,
//                ));
//            }
            return $this->smarty->fetch("extends:{$layout}.{$ext}|{$view}.{$ext}", null, null, null, $output);
        } else {
            return $this->smarty->fetch("file:{$view}.{$ext}", null, null, null, $output);
        }
    }

    public function set($var, $value)
    {
        return $this->smarty->assign($var, $value);
    }

    /**
     * Defines the templates dir
     */
    private function loadOptions()
    {
        $area = Inflector::camelize($this->request->prefix);
        $defaults = array(
            "template_dir" => array(
                'views' => APP_PATH . "View" . DS . "Pages",
                'layouts' => APP_PATH . "View" . DS . "Layouts",
                'elements' => APP_PATH . "View" . DS . "Elements"
            ),
            "areas_template_dir" => array(
                'views' => APP_PATH . "Areas" . DS . $area . DS . "View" . DS . "Pages",
                'layouts' => APP_PATH . "Areas" . DS . $area . DS . "View" . DS . "Layouts",
                'elements' => APP_PATH . "Areas" . DS . $area . DS . "View" . DS . "Elements"
            ),
            "compile_dir" => TMP . DS . "views" . DS,
            "cache_dir" => CACHE . DS . "views" . DS,
            "cache" => false
        );
        $this->options = Hash::merge($defaults, $this->options);

        $this->smarty->addTemplateDir($this->options["areas_template_dir"]);
        $this->smarty->addTemplateDir($this->options["template_dir"]);

        $this->checkDir($this->options["compile_dir"]);
        $this->smarty->setCompileDir($this->options["compile_dir"]);

        $this->checkDir($this->options["cache_dir"]);
        $this->smarty->setCacheDir($this->options["cache_dir"]);

        if ($this->options['cache']) {
            $this->smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
            $this->smarty->setCacheLifetime($this->options['cache']['lifetime']);
        }
    }

    private function checkDir($dir)
    {
        return new Folder($dir, true);
    }

}
