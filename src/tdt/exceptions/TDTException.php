<?php
/*
 * @package tdt\exceptions
 * @copyright (C) 2011,2013 by iRail vzw/asbl, OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 * @author Pieter Colpaert   <pieter@iRail.be>
 */

namespace tdt\exceptions;

class TDTException extends \Exception{

    private $errorcode,$exceptionini;
    private $parameters;

    public function __construct($errorcode, array $parameters){
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
            Log::getInstance()->logCrit("Could not find an exception with errorcode " . $errorcode . ".");
            header("Location: " . Config::get("general","hostname") . Config::get("general","subdir") . "error/critical");
        }
        parent::__construct($this->getMsg(),$errorcode);
    }

    public function getMsg(){
        if(isset($this->exceptionini["message"]))
            return $this->exceptionini["message"];
        else
            return "-- Please set a message in your exceptions.ini for exception " . $errorcode . "--";
    }

    public function getShort(){
        if(isset($this->exceptionini["short"]))
            return $this->exceptionini["short"];
        else
            return "-- Please set a short in your exceptions.ini for exception " . $errorcode . "--";
    }
    
    public function getDocumentation(){
        if(isset($this->exceptionini["documentation"]))
            return $this->exceptionini["documentation"];
        else
            return "-- Please set documentation in your exceptions.ini for exception " . $errorcode . " --";
    }
    
    public function getParameters(){
        return explode(",",$this->parameters);
    }

    public function getURL(){
        return Config::get("general","hostname") . Config::get("general","subdir") . "error/" . $this->getCode() . "/?problem=". urlencode($this->getMsg());
    }
}