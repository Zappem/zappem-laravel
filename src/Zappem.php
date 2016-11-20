<?php namespace Zappem\ZappemLaravel;

class Zappem{
	
	public $URL;
	public $Project;
	public $User;
	public $Data;

	private $autoload;

	public function __construct($URL, $Project, $User=null){
		$this->URL = $URL;
		$this->Project = $Project;
		$this->User = $User;
	}

	public function exception($e, $found_by=null){

		$Trace = $this->processTrace($e);

		$this->Data = [
			"project" => $this->Project,
			"method" => $_SERVER['REQUEST_METHOD'],
			"url" => $_SERVER['REQUEST_URI'],
			"ip" => $_SERVER['REMOTE_ADDR'],
			"useragent" => $_SERVER['HTTP_USER_AGENT'],
			"message" => $e->getMessage() ? $e->getMessage() : get_class($e),
			"class" => get_class($e),
			"file" => $e->getFile(),
			"line" => $e->getLine(),
			"trace" => $e->getTrace()
		];

		return $this;

	}

	public function user($User){
		$this->Data["user"] = $User;
		return $this;
	}

	public function send(){

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL            => $this->URL."/api/v1/exception",
            CURLOPT_HTTPGET        => 0,
            CURLOPT_POST           => count($this->Data),
            CURLOPT_POSTFIELDS     => json_encode($this->Data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ));

        $resp = curl_exec($curl);
        curl_close($curl);
        
        return json_decode($resp);


	}
}