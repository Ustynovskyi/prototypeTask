<?php

/**
 * This class is used to render HTML templates and handling JSONS
 */

namespace App;


class View {

    private static $_instance;

    /**
     * @var String
     */
    private $output;

    /**
     * @var array
     */
    private $_vars=array();


    public function _construct(){

    }

    public static function getInstance() {
        if (null === self :: $_instance) {
            self :: $_instance = new self();
        }
        return self :: $_instance;
    }


    /**
     * renders html output or json responce
     */
    public function render()
    {
        echo $this->output;
    }


    /**
     * @param String $template
     */
    public function parsePage($template){
        $this->output .= $this->parse($template, 0);
    }

    /**
     * This function parses template pages and applies variables passed by controller
     * @param String $template
     * @param Int $position
     * @return boolean
     */
    protected function parse($template, $position)
    {

        extract($this->_vars, EXTR_OVERWRITE);
        if ($position)	 extract($this->_vars[0], EXTR_OVERWRITE);
        extract($_SERVER, EXTR_OVERWRITE);
        $path = getcwd().'/templates/'.$template.'.tpl.php';

        if (file_exists($path)) {
            ob_start();
            include($path);
            $contents = ob_get_contents();
            ob_end_clean();
        }

        if ($position)	unset($this->_vars[$position]);
        return isset($contents) ? $contents : '';
    }

    /**
     * Pass variable value to template handling or json encoding
     * @param String $varname
     * @param $varval
     * @param int $position
     */
    public function assign($varname, $varval, $position = 0)
    {
        $this->_vars[$position][$varname] = $varval;
    }

    /**
     * Encode and output json
     */
    public function sendJSON()
    {
        extract($this->_vars[0], EXTR_OVERWRITE);
        if(!isset($responce)) $responce=array();
        $this->output = json_encode($responce);
        header('Content-Type: application/json');
    }


    /**
     * Render template inside other template
     * @param string $template
     * @param array $data
     */
    public function renderPartial($template, $data=array())
    {
        extract($data, EXTR_OVERWRITE);

        $contents='';
        $path = getcwd().'/templates/'.$template.'.tpl.php';

        if (file_exists($path)) {
            ob_start();
            include($path);
            $contents = ob_get_contents();
            ob_end_clean();
        }


        echo $contents;
    }

}