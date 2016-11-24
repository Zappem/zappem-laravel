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

    public function getSource($file, $number, $radius = 2){
        if (!is_file($file) or !is_readable($file)) {
            return [];
        }

        $before = $after = $radius;
        $start  = ($number - 1) - $before;

        if ($start <= 0) {
            $start  = 1;
            $before = 1;
        }

        $duration = $before + 1 + $after;
        $size     = $start + $duration;
        $lines    = [];

        $file_handle = fopen($file, 'r');

        for ($l = 1; $l < $size; $l++) {
            $line = fgets($file_handle);

            if ($l < $start) {
                continue;
            }

            $lines["$l"] = $this->trimLine($line);
        }

        return $lines;
    }

    private function trimLine($line){
        $trimmed = trim($line, "\n\r\0\x0B");

        return preg_replace(
            [
                '/\s*$/D',
                '/\t/'
            ],
            [
                '',
                '    ',
            ],
            $trimmed
        );
    }

    private function trimFileName($file){
    	return substr($file, strlen(base_path()), strlen($file));
    }

    private function getTrace($Trace){
    	foreach($Trace as $Key=>$Bit){
    		if(isset($Bit['file'])) $Trace[$Key]['file'] = $this->trimFileName($Bit['file']);
    	}
    	return $Trace;
    }

	public function exception($e, $found_by=null){

		$Trace = $e->getTrace();
		if(count($Trace)){
			$Source = $this->getSource($Trace[0]['file'], $Trace[0]['line']);
			$Trace = $this->getTrace($Trace);
			$File = $this->trimFileName($Trace[0]['file']);
			$Line = $Trace[0]['line'];
		}else{
			$Source = $this->getSource($e->getFile(), $e->getLine());
			$File = $this->trimFileName($e->getFile());
			$Line = $e->getLine();
		}

		

		$this->Data = [
			"project" => $this->Project,
			"method" => $_SERVER['REQUEST_METHOD'],
			"url" => $_SERVER['REQUEST_URI'],
			"ip" => $_SERVER['REMOTE_ADDR'],
			"useragent" => $_SERVER['HTTP_USER_AGENT'],
			"env" => $_ENV,
			"cookies" => $_COOKIE,
			"data" => ($_SERVER['REQUEST_METHOD'] == "post" ? $_POST : $_GET),
			"message" => $e->getMessage() ? $e->getMessage() : get_class($e),
			"class" => get_class($e),
			"file" => $File,
			"line" => $Line,
			"trace" => $Trace,
			"source" => $Source
		];

		return $this;

	}

	public function user($UserID, $UserName){
		$this->Data["user"] = [
			"id" => $UserID,
			"name" => $UserName
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