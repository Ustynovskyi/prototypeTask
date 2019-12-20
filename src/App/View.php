<?php

namespace App;


class View {

    private static $_instance;

    private $output;
    private $request;
    private $_vars=array();


    public function _construct(){

     $this->processRequest();
    }

    public static function getInstance() {
        if (null === self :: $_instance) {
            self :: $_instance = new self();
        }
        return self :: $_instance;
    }


    private function processRequest()
    {

        $this->request=array();
    }

    public function render()
    {
        echo $this->output;
    }


    public function parsePage($template){
        $this->output .= $this->parse($template, 0);
    }

    protected function parse($template, $position)
    {

        //extract($this->_vars, EXTR_OVERWRITE);
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

    public function assign($varname, $varval, $position = 0)
    {
        $this->_vars[$position][$varname] = $varval;
    }

    public function sendJSON()
    {
        extract($this->_vars, EXTR_OVERWRITE);
        if(!isset($responce)) $responce=array();
        $this->output = json_encode($responce);
    }


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