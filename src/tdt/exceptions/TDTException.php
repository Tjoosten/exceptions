<?php
/*
 * @package tdt\exceptions
 * @copyright (C) 2011,2013 by iRail vzw/asbl, OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */

namespace tdt\exceptions;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class TDTException extends \Exception{

    private $errorcode,$exceptionini;
    private $parameters;
    private $config;
    /*
     * The config interprets the following keys:
     *  log_dir : The directory in which to log errors that occur in this class.      
     * url : The url to redirect the user when an exception is thrown
     *
     */
    public function __construct($errorcode, array $parameters = array(),array $config = array()) {
        $this->config = $config;
        $this->errorcode = $errorcode;
        $this->parameters = $parameters;
        $exceptions = parse_ini_file("exceptions.ini",true);
        if(isset($exceptions[$errorcode])){
            $this->exceptionini = $exceptions[$errorcode];
            //create the message of the exception by filling out the parameters, if the message exists of course
            $i = 1;
            if(isset($this->exceptionini["message"])){
                foreach($this->parameters as $param){
                    $to_replace = "$".$i;
                    if(!is_string($param)){
                        $param = print_r($param,true);
                    }
                    $this->exceptionini["message"] = str_replace($to_replace, $param ,$this->exceptionini["message"]);
                    $i++;
                }
            }
        }else{
            if(isset($config["log_dir"])) {
                $log = new Logger('error_handler');
                $log_dir = rtrim($this->config["log_dir"], "/");                
                $log->pushHandler(new StreamHandler($log_dir . "/log_". date('Y-m-d') . ".txt", Logger::CRITICAL));                
                $log->addCritical("Could not find an exception with errorcode " . $this->errorcode . ".");                
            }
            
            if(isset($config["url"])) {      
                $url = rtrim($config["url"], "/");
                header("Location: " . $url . "/critical");
            }                       
        }
        parent::__construct($this->getMsg(),$errorcode);
    }

    public function getMsg() {
        if(isset($this->exceptionini["message"]))
            return $this->exceptionini["message"];
        else
            return "-- Please set a message in your exceptions.ini for exception " . $this->errorcode . "--";
    }

    public function getShort() {
        if(isset($this->exceptionini["short"]))
            return $this->exceptionini["short"];
        else
            return "-- Please set a short in your exceptions.ini for exception " . $this->errorcode . "--";
    }
    
    public function getDocumentation() {
        if(isset($this->exceptionini["documentation"]))
            return $this->exceptionini["documentation"];
        else
            return "-- Please set documentation in your exceptions.ini for exception " . $this->errorcode . " --";
    }
    
    public function getParameters() {
        return explode(",",$this->parameters);
    }

    public function getURL(){
        if(isset($this->config["url"])) {
            $url = rtrim($this->config["url"],"/");
            return $url . "/" . $this->getCode() . "/?problem=". urlencode($this->getMsg());   
        }else{
            return "";
        }        
    }
}
