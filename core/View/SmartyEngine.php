<?php

class SmartyEngine implements ITemplateEngine {

    /**
     * Smarty Object
     * @var Smarty 
     */
    protected $template;

    /**
     * Template Config
     * @var type 
     */
    protected $config;

    /**
     * Layout used to display the views
     */
    protected $layout = null;

    public function getConfig() {
        return $this->config;
    }

    public function getLayout() {
        return $this->layout;
    }

    public function setLayout($layout) {
        $this->layout = $layout;
    }

    function __construct() {
        //Loads the template config
        $this->config = Config::read('template');
        //Instanciate a Smarty object
        $this->template = new Smarty();
        //Build the template directory
        $this->buildTemplateDir();
        //Build the layouts vars
        $this->buildLayouts();
        //Build the cache
        $this->buildCache();
    }

    public function display($view, $ext = "tpl") {
        $layout = isset($this->layout) ? $this->layout . '/' : null;
        // If the view exists...
        if (App::path("View", $layout . $view, $ext)) {
            //...display it
            return $this->template->display("file:{$layout}{$view}.{$ext}");
        } else {
            //...or throw an MissingViewException
            $errors = explode("/", $view);
            throw new MissingViewException(array("controller" => $errors[0], "action" => $errors[1]));
        }
    }

    public function set($var, $value) {
        return $this->template->assign($var, $value);
    }

    /**
     * Defines the templates dir
     * @since 0.1.2
     */
    private function buildTemplateDir() {
        if (isset($this->config["templateDir"]) && is_array($this->config["templateDir"])) {
            $this->template->setTemplateDir($this->config["templateDir"]);
        } else {
            $this->template->setTemplateDir(array("views" => App::path("View"), 'layouts' => App::path("Layout")));
        }
    }

    /**
     * Build the includes vars for the views. This makes the call more friendly.
     * @since 0.1.5
     */
    private function buildLayouts() {
        if (isset($this->config["layouts"]) && is_array($this->config["layouts"])) {
            foreach ($this->config["layouts"] as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Constroi o cache padrão para as views, caso estejam setados na configuração
     * @since 0.1.6
     */
    private function buildCache() {
        $caching = isset($this->config["cache"]) ? $this->config["cache"] : null;

        if (!is_null($caching) && isset($caching["cache"]) && $caching["cache"]) {
            if (isset($caching["cacheDir"])) {
                $this->template->setCacheDir($caching["cacheDir"]);
            }
            $this->template->setCacheLifetime(isset($caching["time"]) ? $caching["time"] : 3600);
            $this->template->setCaching(Smarty::CACHING_LIFETIME_SAVED);
        }
    }

}

?>
