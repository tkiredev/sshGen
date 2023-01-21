<?php
/*
** Author: kireDev
** Property of https://github.com/tkiredev
*/
class sshGen
{
	private $proxy = "";
	public $urid = "bW9kdmlwLXZwcy50aw";
	public $_token;
  public $selectList = 16;
  
  public function __construct(){
   $this->urid = $this->_dcode($this->urid);
  }

	function _c($prx = false,$user = false){
    
     curl_setopt_array($c = curl_init(), [
      CURLOPT_URL => is_array($user) ? "http://{$this->urid}/create-server?{$this->selectList}=" : "http://{$this->urid}/create-server/{$this->selectList}",
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_NOBODY => 0,
      CURLOPT_CUSTOMREQUEST => is_array($user) ? "POST" : "GET",
      CURLOPT_PROXY => $prx == "proxy" ? $this->proxy: false,
      CURLOPT_POSTFIELDS => is_array($user) ? http_build_query($user) : false,
      CURLOPT_COOKIEFILE => "Auth.txt",
      CURLOPT_HTTPHEADER => [
       "Host:{$this->urid}",
       "Origin: http://{$this->urid}",
       "Referer: http://{$this->urid}/create-server/{$this->selectList}",
       "Content-Type:application/x-www-form-urlencoded",
       "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:94.0) Gecko/20100101 Firefox/94.0"
      ]
     ]);

     $x = curl_exec($c);
     $data_ssh = [];

     if (!is_array($user)) return $this->matchToken($x);

     if ( ($status_http = curl_getinfo($c)["http_code"]) === 200) {
        preg_match_all('/<li.*?>(.*?)<\/[\s]*li>/',$x,$matches);
        foreach ($matches[1] as $key => $value) {
            $value = strip_tags($value);
            $noPush = ["Registrarse","Politicas","Admninistración"];
            if ($noPush[0] != $value && $noPush[1] != $value && $noPush[2] != $value) {
             array_push($data_ssh,$value);
            }
        }
        return $data_ssh;
     }

     return ["code_status"=> $status_http];
	}

  public function _r($urid,$crd = false){
      curl_setopt_array($c = curl_init(),[
       CURLOPT_URL => $urid."/login",
       CURLOPT_PROXY => $this->proxy != false ? $this->proxy : false,
       CURLOPT_CUSTOMREQUEST => !$crd ? "GET":"POST",
       CURLOPT_POSTFIELDS => !$crd ? false: http_build_query($crd),
       CURLOPT_COOKIEJAR => "Auth.txt",
       CURLOPT_COOKIEFILE => !$crd ? false:"Auth.txt",
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_HTTPHEADER => [
        "Host: {$urid}",
        "Origin: http://{$urid}",
        "Referer: http://{$urid}/login",
        "Content-Type: application/x-www-form-urlencoded",
        "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:94.0) Gecko/20100101 Firefox/94.0"
       ]
    ]);
    $rs = curl_exec($c);
    if (!is_array($crd)) return $this->matchToken($rs);
    if (curl_getinfo($c)['redirect_url'] === "http://{$urid}/") return true;
    return false;
  }

	public function auth($crd){
    return $this->_r($this->urid,[
        "_token"=> $this->_r($this->urid),
        "email"=> $crd["email"],
        "password"=> $crd['password'],
    ]);
	}

    public function matchToken($subject){
     if (empty($subject)) return !1;
      $pattern = '~<\s*meta\b.*\bname="csrf-token"\s.*\bcontent="([^"]*)~i';
      $m = preg_match_all($pattern, $subject, $matches);
      return $matches[1][0];
    }

    public function randomUsers($token){
     $uniqid = uniqid();
     return ["_token"=> $token, "user" => $uniqid,"passwd" => $uniqid];
    }

    private function _dcode($format){
      return base64_decode($format);
    }
}

error_reporting(0);
system("clear");

//init class / object
$sshGen = new sshGen();

$randomUsers = true;
/*
* Enter your credentials Here
*/
$login = [
  "email" => "", 
  "password" => ""
];

/*
 Status code of server:
 302 > Acount exist.
 200 > Acount Create successfully
 419 > Token expired, or Cookie is bad.
*/

 while(true){

  if ($sshGen->auth($login)) {
   $token = $sshGen->_c("proxy");
   $ssh = $sshGen->_c("proxy",($randomUsers ? $sshGen->randomUsers($token):[
    "_token" => $token,
    "user"=> "¡¡change Here!",
    "passwd" => "¡¡change Here!",
]));

  if ($ssh["code_status"] == 419){
   print "Unknow List > ".$sshGen->selectList;
   return !1;
  }
  
  if ($ssh["code_status"] != 302 && is_array($ssh) && count($ssh) > 0) {
    print "Account has been successfully created\n";
    print "┏××××××××××××××××××××┓\n";
    foreach ($ssh as $key => $value) {
      echo "┊•⊰❂⊱•{$value}\n";
    }
    print "┗××××××××××××××××××××┛";

    return !0;
  } else print "Account Exist!!\n";
   break;
  }
 }

?>
