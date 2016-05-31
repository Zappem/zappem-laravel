<?php namespace Zappem\ZappemLaravel;

class Zappem{
	
	public $URL;
	public $Project;
	public $User;
	public $Data;

	public function __construct($URL, $Project, $User=null){

		$this->URL = $URL;
		$this->Project = $Project;
		$this->User = $User;

	}

	public function exception($e, $found_by=null){


		$this->Data = [
			"project_id" => $this->Project,
			"exception" => [
				"class" => get_class($e),
				"message" => $e->getMessage() ? $e->getMessage() : get_class($e),
				"file"	  => $e->getFile(),
				"line"	  => $e->getLine(),
				"code"	  => $e->getCode(),
				"trace" => $e->getTrace(),
				"get"	=> $_GET,
				"post"	=> $_POST,
				"server" => $_SERVER,
				"req"	=> $_REQUEST,
				"env"	=> $_ENV,
				"cookie" => $_COOKIE,
				"env"	=> $_ENV
			]
		];

		return $this;

	}

	public function user($ID, $Username, $Email){
		$this->Data["found_by"] = [
			"user_id" => $ID,
			"user" => $Username,
			"email" => $Email
		];
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