<?php
	class Index {

                private $template = './home/views/default/index/';
                private $preview = '';

                function __construct(){
                    $this->preview = $_SESSION['preview'];
                    unset($_SESSION['preview']);
                }
                function __destruct(){
                    unlink($this->template.$this->preview.'.'.TPLPREFIX); 
                }
        
                function index(){
                    $this->display($this->preview);
                }
        
                
        }