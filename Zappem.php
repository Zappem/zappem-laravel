<?php namespace Zappem\ZappemLaravel;

class Zappem{
	
	public $Endpoint;
	public $Project;
	public $User;
	public $Data;

	public function __construct($Endpoint, $Project, $User=null){

		$this->Endpoint = $Endpoint;
		$this->Project = $Project;
		$this->User = $User;

	}

	public function exception($e, $found_by=null){

		$this->Data = [
			"project_id" => $this->Project,
			"exception" => [
				"message" => $e->getMessage(),
				"file"	  => $e->getFile(),
				"line"	  => $e->getLine(),
				"code"	  => $e->getCode(),
				"trace" => $e->getTrace()
				]
		];

		return $this;

	}

	public function send(){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL            => "http://localhost:3000/api/v1/exception",
            CURLOPT_HTTPGET        => 0,
            CURLOPT_POST           => count($this->Data),
            CURLOPT_POSTFIELDS     => json_encode($this->Data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ));
        $resp = curl_exec($curl);

        dd($resp);
        
        curl_close($curl);


	}
}