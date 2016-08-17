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
		$this->autoload = require base_path().'/vendor/autoload.php';

	}

    private function getFileLines($start = 0, $length = null, $file){
        if (null !== ($contents = file_get_contents($file))) {
            $lines = explode("\n", $contents);

            if ($length !== null) {
                $start  = (int) $start;
                $length = (int) $length;
                if ($start < 0) {
                    $start = 0;
                }
                $lines = array_slice($lines, $start, $length, true);
            }
            return $lines;
        }
    }

    private function getFileFromClassName($class){
    	return $this->autoload->findFile($class);
    }

    private function mainExceptionAsTrace($e){
    	$Trace = [];
    	$Trace['file'] = $e->getFile();
    	$Trace['line'] = $e->getLine();
    	$Trace['function'] = null;
    	$Trace['class'] = get_class($e);
    	$Trace['type'] = null;
    	$Trace['args'] = null;
    	$range = $this->getFileLines($Trace['line'] - 8, 10, $Trace['file']);
		if($range){
			$range = array_map(function($line){
                return empty($line) ? ' ' : $line;
            }, $range);

            $start = key($range) + 1;
            $code  = join("\n", $range);
        
            $Trace['file_contents'] = $code;
		}
		return $Trace;
    }

    private function processTrace($e){
    	$Stack = [];

    	$Stack[] = $this->mainExceptionAsTrace($e);

    	foreach($e->getTrace() as $Trace){
    		if(isset($Trace['class'])){
	    		$Trace['file'] = $this->getFileFromClassName($Trace['class']);
	    		if(isset($Trace['line'])){
		    		$range = $this->getFileLines($Trace['line'] - 8, 10, $Trace['file']);

		    		if($range){
						$range = array_map(function($line){
		                    return empty($line) ? ' ' : $line;
		                }, $range);

		                $start = key($range) + 1;
		                $code  = join("\n", $range);
		            
		                $Trace['file_contents'] = $code;
		    		}
		    		$Trace['args'] = null;
		    		$Stack[] = $Trace;
		    	}
		    }
	    }

	    return $Stack;
    }


	public function exception($e, $found_by=null){

		$Trace = $this->processTrace($e);

		$this->Data = [
			"project_id" => $this->Project,
			"exception" => [
				"class" => get_class($e),
				"message" => $e->getMessage() ? $e->getMessage() : get_class($e),
				"file"	  => $e->getFile(),
				"line"	  => $e->getLine(),
				"code"	  => $e->getCode(),
				"trace" => $Trace,
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