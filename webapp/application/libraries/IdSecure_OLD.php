<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

Class IdSecure
{

   private $id;
   private $ip;
   private $port;
   private $user;
   private $password;
   private $session;
   private $CI;
   private $protocol;

   public function __construct($arrConstruct)
   {
       $this->CI = &get_instance(); 

 //Carrega o model de equipamentos
       $this->CI->load->model('ti_biometria/biometria_registro_model');

       $this->id = isset($arrConstruct['id']) ? $arrConstruct['id'] : null;
       $this->protocol = isset($arrConstruct['protocol']) ? $arrConstruct['protocol'] : 'https';
       $this->port = isset($arrConstruct['port']) ? $arrConstruct['port'] : '';
       $this->ip = $arrConstruct['ip'];
       $this->user = $arrConstruct['user'];
       $this->password = $arrConstruct['password'];
   }

   public function setSession($session){
       $this->session = $session;
   }

   public function getSession(){
       return $this->session;
   }

   public function authenticate(){ 
       $isOk = true;

       $userLogin = new \StdClass();
       $userLogin->username = $this->user;
       $userLogin->password = $this->password;
       $userLogin->passwordCustom = null;

       if($result = $this->callRequest(true,'api/login/',$userLogin)){
           $returnObj = json_decode($result);
           $returnObj->accessToken ? $this->setSession($returnObj->accessToken) 
           : $isOk = false; 
       }else{
           $isOk = 'false';
       }

       return $isOk;
   } 

    public function removeGeral($ref){
        switch ($ref) {
            case 1:
                $grupoName = 'Refeição - 12H';
                $grupoCode = 1005;
                break;
            case 2:
                $grupoName = 'Refeição - 19H';
                $grupoCode = 1006;
                break;
            case 3:
                $grupoName = 'Refeição - 22H';
                $grupoCode = 1007;
                break;
            case 4:
                $grupoName = 'Refeição - 00H';
                $grupoCode = 1008;
                break;
        }
        $ref = new StdClass();
        $ref->contingency = false;
        $ref->disableADE = false;
        $ref->id = $grupoCode;
        $ref->id2 = null;
        $ref->idType = 1;
        $ref->maxTimeInside = null;
        $ref->nPeople = 0;
        $ref->nUsers = 0;
        $ref->nVisitors = 0;
        $ref->name = $grupoName;
        $ref->users = [];
        $ref->parkingSpots = null;
        $ref->parkingSpotsList = null;
        if($result = $this->callRequestPut('api/group/',$ref, true)){
           return $result = $this->callRequestPut('api/group/',$ref, true);
        }else{
            return $result = $this->callRequestPut('api/group/',$ref, true);
        }
    }

    
    public function TESTE($userID){
        $grupoName = 'TESTE';
        $grupoCode = 1042;
        $ref = new StdClass();
        $ref->contingency = false;
        $ref->disableADE = false;
        $ref->id = $grupoCode;
        $ref->id2 = null;
        $ref->idType = 1;
        $ref->maxTimeInside = null;
        $ref->nPeople = 0;
        $ref->nUsers = 0;
        $ref->nVisitors = 0;
        $ref->name = $grupoName;
        $ref->users = $userID;
        $ref->parkingSpots = null;
        $ref->parkingSpotsList = null;
        print_r($ref);
        
        if($result = $this->callRequestPut('api/group/',$ref, true)){

           return $result = $this->callRequestPut('api/group/',$ref, true);
        }else{
            return $result = $this->callRequestPut('api/group/',$ref, true);
        }
    }

   public function callRequest($post,$method,$postData,$useSession = false){
       $ch = curl_init();

       if($useSession){
           $header = array("content-type: application/json", "Authorization: Bearer ".$this->getSession());
       }else{
           $header = array("content-type: application/json");
       }

 //Monta a Url de Requisição
       $url = $this->protocol.'://'.$this->ip.':'.$this->port.'/'.$method;

 //Transforma em Json o objeto
       if($post){
           $jsonString = json_encode($postData); 
       }else{
 //Query Param
           $url = $url.'/'.$postData; 
       }

       curl_setopt($ch,CURLOPT_URL,$url);
       if($post){
           curl_setopt($ch,CURLOPT_POST,1);
           curl_setopt($ch,CURLOPT_POSTFIELDS,$jsonString); 
       } 
       curl_setopt($ch,CURLOPT_HEADER,false);
       curl_setopt($ch,CURLOPT_TIMEOUT,5); 
       curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 0);
       curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
       curl_setopt($ch,CURLOPT_HTTPHEADER, $header);

 //Executa a requisição
       $postResult = curl_exec($ch);

       $error = curl_error ($ch);

 //Fecha a conexão
       curl_close($ch); 

       return $postResult;
   }

   public function callRequestPut($method,$postData,$useSession = false){
       $ch = curl_init();

       if($useSession){
           $header = array("content-type: application/json", "Authorization: Bearer ".$this->getSession());
       }else{
           $header = array("content-type: application/json");
       }

 //Monta a Url de Requisição
       $url = $this->protocol.'://'.$this->ip.':'.$this->port.'/'.$method;

       $jsonString = json_encode($postData); 

 //curl_setopt($ch, CURLOPT_HTTPHEADER, $cabecalho);

       curl_setopt($ch,CURLOPT_URL,$url); 
       curl_setopt($ch,CURLOPT_HEADER,false);
       curl_setopt($ch,CURLOPT_POST,1); 
       curl_setopt($ch,CURLOPT_POSTFIELDS,$jsonString); 
       curl_setopt($ch,CURLOPT_TIMEOUT,5); 
       curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); 
       curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 0);
       curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
       curl_setopt($ch,CURLOPT_HTTPHEADER, $header);

 //Executa a requisição
       $postResult = curl_exec($ch);

       $error = curl_error ($ch);

 //Fecha a conexão
       curl_close($ch); 

       return $postResult;
   }

   public function searchUser($searchValue){
       $queryParam = "?idType=0&draw=2&columns%5B0%5D%5Bdata%5D=&columns%5B0%5D%5Bname%5D=&columns%5B0%5D%5Bsearchable%5D=true&columns%5B0%5D%5Borderable%5D=false&columns%5B0%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B0%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B1%5D%5Bdata%5D=id&columns%5B1%5D%5Bname%5D=&columns%5B1%5D%5Bsearchable%5D=true&columns%5B1%5D%5Borderable%5D=true&columns%5B1%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B1%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B2%5D%5Bdata%5D=name&columns%5B2%5D%5Bname%5D=&columns%5B2%5D%5Bsearchable%5D=true&columns%5B2%5D%5Borderable%5D=true&columns%5B2%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B2%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B3%5D%5Bdata%5D=registration&columns%5B3%5D%5Bname%5D=&columns%5B3%5D%5Bsearchable%5D=true&columns%5B3%5D%5Borderable%5D=true&columns%5B3%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B3%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B4%5D%5Bdata%5D=rg&columns%5B4%5D%5Bname%5D=&columns%5B4%5D%5Bsearchable%5D=true&columns%5B4%5D%5Borderable%5D=true&columns%5B4%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B4%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B5%5D%5Bdata%5D=cpf&columns%5B5%5D%5Bname%5D=&columns%5B5%5D%5Bsearchable%5D=true&columns%5B5%5D%5Borderable%5D=true&columns%5B5%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B5%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B6%5D%5Bdata%5D=phone&columns%5B6%5D%5Bname%5D=&columns%5B6%5D%5Bsearchable%5D=true&columns%5B6%5D%5Borderable%5D=true&columns%5B6%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B6%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B7%5D%5Bdata%5D=cargo&columns%5B7%5D%5Bname%5D=&columns%5B7%5D%5Bsearchable%5D=true&columns%5B7%5D%5Borderable%5D=true&columns%5B7%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B7%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B8%5D%5Bdata%5D=inativo&columns%5B8%5D%5Bname%5D=&columns%5B8%5D%5Bsearchable%5D=true&columns%5B8%5D%5Borderable%5D=true&columns%5B8%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B8%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B9%5D%5Bdata%5D=blackList&columns%5B9%5D%5Bname%5D=&columns%5B9%5D%5Bsearchable%5D=true&columns%5B9%5D%5Borderable%5D=true&columns%5B9%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B9%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B10%5D%5Bdata%5D=&columns%5B10%5D%5Bname%5D=&columns%5B10%5D%5Bsearchable%5D=true&columns%5B10%5D%5Borderable%5D=false&columns%5B10%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B10%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B11%5D%5Bdata%5D=&columns%5B11%5D%5Bname%5D=&columns%5B11%5D%5Bsearchable%5D=true&columns%5B11%5D%5Borderable%5D=false&columns%5B11%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B11%5D%5Bsearch%5D%5Bregex%5D=false&order%5B0%5D%5Bcolumn%5D=2&order%5B0%5D%5Bdir%5D=asc&start=0&length=10&search%5Bvalue%5D={$searchValue}&search%5Bregex%5D=false&inactive=0&inactive=1";
       $user = null;
       $result = $this->callRequest(false,'api/user',$queryParam, true);
       if($result){ 
           if(json_decode($result)->data){ 
               $users = json_decode($result)->data[0]; 
               if($resultuser = $this->callRequest(false,'api/user', $users->id, true)){
                   $user = json_decode($resultuser);
               }else{
               }
           }
       }
       return $user;
   }

   public function getGroups($id = null){
       $group = [];
       if($result = $this->callRequest(false,'api/group','', true)){
           if(json_decode($result)->data){ 
               $groups = json_decode($result)->data; 
               if($id){
                   foreach ($groups as $key => $value) {
                       if($value->id == $id){
                           $group = $value;
                       }
                   }
               }
           }
       }
       return $group;
   }

   public function getGroupID($name = null){ 
       $group = [];
       if($result = $this->callRequest(false,'api/group','', true)){
           if(json_decode($result)->data){ 
               $groups = json_decode($result)->data; 
               if($name){
                   foreach ($groups as $key => $value) { 
                       if(trim($value->name) == trim($name) && $value->idType == 0){
                           $group = $value;
                       }
                   }
               }
           }
       }
       return $group;
   }

   public function setUserGroup($user){
       if($result = $this->callRequestPut('api/user/',$user, true)){
           return $result = $this->callRequestPut('api/user/',$user, true);
       }else{
           return $result = $this->callRequestPut('api/user/',$user, true);
       }
   }

   public function reportDepartment($day,$month,$year){
       $users = [];

       $queryParam = "?draw=1&columns%5B0%5D%5Bdata%5D=user_id&columns%5B0%5D%5Bname%5D=&columns%5B0%5D%5Bsearchable%5D=true&columns%5B0%5D%5Borderable%5D=true&columns%5B0%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B0%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B1%5D%5Bdata%5D=time&columns%5B1%5D%5Bname%5D=&columns%5B1%5D%5Bsearchable%5D=true&columns%5B1%5D%5Borderable%5D=true&columns%5B1%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B1%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B2%5D%5Bdata%5D=user_name&columns%5B2%5D%5Bname%5D=&columns%5B2%5D%5Bsearchable%5D=true&columns%5B2%5D%5Borderable%5D=true&columns%5B2%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B2%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B3%5D%5Bdata%5D=event&columns%5B3%5D%5Bname%5D=&columns%5B3%5D%5Bsearchable%5D=true&columns%5B3%5D%5Borderable%5D=true&columns%5B3%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B3%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B4%5D%5Bdata%5D=user_registration&columns%5B4%5D%5Bname%5D=&columns%5B4%5D%5Bsearchable%5D=true&columns%5B4%5D%5Borderable%5D=true&columns%5B4%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B4%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B5%5D%5Bdata%5D=device_name&columns%5B5%5D%5Bname%5D=&columns%5B5%5D%5Bsearchable%5D=true&columns%5B5%5D%5Borderable%5D=true&columns%5B5%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B5%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B6%5D%5Bdata%5D=area&columns%5B6%5D%5Bname%5D=&columns%5B6%5D%5Bsearchable%5D=true&columns%5B6%5D%5Borderable%5D=true&columns%5B6%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B6%5D%5Bsearch%5D%5Bregex%5D=false&order%5B0%5D%5Bcolumn%5D=1&order%5B0%5D%5Bdir%5D=desc&start=0&length=99999999&search%5Bvalue%5D=&search%5Bregex%5D=false";
       $api = "api/report/logs/department".$queryParam;
       $timestampStart = strtotime($day.'-'.$month.'-'.$year.' 00:00:00');
       $timestampEnd = strtotime($day.'-'.$month.'-'.$year.' 23:59:00');
       $postData = new StdClass();
       $postData->cameras = [];
       $postData->accessLogs = [];
       $postData->areas = [];
       $postData->devices = [4];
       $postData->end = $timestampEnd;
       $postData->groups = [1005,1006,1007,1008];
       $postData->schedules = [];
       $postData->start = $timestampStart;
       $postData->users = [];
       if($result = $this->callRequest(true, $api,$postData, true)){
           $users = json_decode($result);
       }
       return $users;
   }

   public function getDepartments(){
       $group = [];
       if($result = $this->callRequest(false,'api/group','', true)){
           if(json_decode($result)->data){ 
               $groups = json_decode($result)->data; 
               foreach ($groups as $key => $value) {
                   if($value->idType == 0){
                       array_push($group, $value);
                   }
               } 
           }
       }
       return $group;
   }


   public function getUsers(){
       $querParam = "?draw=1&columns%5B0%5D%5Bdata%5D=&columns%5B0%5D%5Bname%5D=&columns%5B0%5D%5Bsearchable%5D=true&columns%5B0%5D%5Borderable%5D=false&columns%5B0%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B0%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B1%5D%5Bdata%5D=id&columns%5B1%5D%5Bname%5D=&columns%5B1%5D%5Bsearchable%5D=true&columns%5B1%5D%5Borderable%5D=true&columns%5B1%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B1%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B2%5D%5Bdata%5D=name&columns%5B2%5D%5Bname%5D=&columns%5B2%5D%5Bsearchable%5D=true&columns%5B2%5D%5Borderable%5D=true&columns%5B2%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B2%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B3%5D%5Bdata%5D=registration&columns%5B3%5D%5Bname%5D=&columns%5B3%5D%5Bsearchable%5D=true&columns%5B3%5D%5Borderable%5D=true&columns%5B3%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B3%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B4%5D%5Bdata%5D=rg&columns%5B4%5D%5Bname%5D=&columns%5B4%5D%5Bsearchable%5D=true&columns%5B4%5D%5Borderable%5D=true&columns%5B4%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B4%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B5%5D%5Bdata%5D=cpf&columns%5B5%5D%5Bname%5D=&columns%5B5%5D%5Bsearchable%5D=true&columns%5B5%5D%5Borderable%5D=true&columns%5B5%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B5%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B6%5D%5Bdata%5D=phone&columns%5B6%5D%5Bname%5D=&columns%5B6%5D%5Bsearchable%5D=true&columns%5B6%5D%5Borderable%5D=true&columns%5B6%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B6%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B7%5D%5Bdata%5D=cargo&columns%5B7%5D%5Bname%5D=&columns%5B7%5D%5Bsearchable%5D=true&columns%5B7%5D%5Borderable%5D=true&columns%5B7%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B7%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B8%5D%5Bdata%5D=inativo&columns%5B8%5D%5Bname%5D=&columns%5B8%5D%5Bsearchable%5D=true&columns%5B8%5D%5Borderable%5D=true&columns%5B8%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B8%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B9%5D%5Bdata%5D=blackList&columns%5B9%5D%5Bname%5D=&columns%5B9%5D%5Bsearchable%5D=true&columns%5B9%5D%5Borderable%5D=true&columns%5B9%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B9%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B10%5D%5Bdata%5D=&columns%5B10%5D%5Bname%5D=&columns%5B10%5D%5Bsearchable%5D=true&columns%5B10%5D%5Borderable%5D=false&columns%5B10%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B10%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B11%5D%5Bdata%5D=&columns%5B11%5D%5Bname%5D=&columns%5B11%5D%5Bsearchable%5D=true&columns%5B11%5D%5Borderable%5D=false&columns%5B11%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B11%5D%5Bsearch%5D%5Bregex%5D=false&order%5B0%5D%5Bcolumn%5D=2&order%5B0%5D%5Bdir%5D=asc&start=0&length=0&search%5Bvalue%5D=&search%5Bregex%5D=false&inactive=0";
       if($result = $this->callRequest(false,'api/user',$querParam, true)){
           $users = json_decode($result); 
 /* foreach($users->data as $user){
 //echo json_encode($user);
 }*/
 return $this->getUserInfo($users);
}
}

public function getUserInfo($users){
   set_time_limit(2500);
   $t = true;
   $ret = [];
   foreach($users->data as $user){
       if($result = $this->callRequest(false,'api/user',$user->id, true)){
           $arrTemplates = json_decode($result);
           $postData = new StdClass();

           foreach ($arrTemplates->templates as $key => $value) {
               if(substr($value, -1) == '|'){
                   $arrTemplates->templates[$key] = substr($value, 0, -1); 
               }
           }
           $postData->name = trim(substr($arrTemplates->name, strpos($arrTemplates->name, '-')+1));
           $postData->pis = $arrTemplates->pis;
           $postData->templates = $arrTemplates->templates;
           if($arrTemplates->pis){
               array_push($ret, $postData);
           }
 /*$postData = new StdClass();
 $postData->templates = $arrTemplates->templates;*/



 /*foreach($arrTemplates->templates as $template){
 array_push($motorista->templates,$template);
}**/
}
} 
return $ret;
}

public function integrationUserIdSecure(){
   $motoristas = [];

   $querParam = "?draw=1&columns%5B0%5D%5Bdata%5D=&columns%5B0%5D%5Bname%5D=&columns%5B0%5D%5Bsearchable%5D=true&columns%5B0%5D%5Borderable%5D=false&columns%5B0%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B0%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B1%5D%5Bdata%5D=id&columns%5B1%5D%5Bname%5D=&columns%5B1%5D%5Bsearchable%5D=true&columns%5B1%5D%5Borderable%5D=true&columns%5B1%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B1%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B2%5D%5Bdata%5D=name&columns%5B2%5D%5Bname%5D=&columns%5B2%5D%5Bsearchable%5D=true&columns%5B2%5D%5Borderable%5D=true&columns%5B2%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B2%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B3%5D%5Bdata%5D=registration&columns%5B3%5D%5Bname%5D=&columns%5B3%5D%5Bsearchable%5D=true&columns%5B3%5D%5Borderable%5D=true&columns%5B3%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B3%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B4%5D%5Bdata%5D=rg&columns%5B4%5D%5Bname%5D=&columns%5B4%5D%5Bsearchable%5D=true&columns%5B4%5D%5Borderable%5D=true&columns%5B4%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B4%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B5%5D%5Bdata%5D=cpf&columns%5B5%5D%5Bname%5D=&columns%5B5%5D%5Bsearchable%5D=true&columns%5B5%5D%5Borderable%5D=true&columns%5B5%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B5%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B6%5D%5Bdata%5D=phone&columns%5B6%5D%5Bname%5D=&columns%5B6%5D%5Bsearchable%5D=true&columns%5B6%5D%5Borderable%5D=true&columns%5B6%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B6%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B7%5D%5Bdata%5D=cargo&columns%5B7%5D%5Bname%5D=&columns%5B7%5D%5Bsearchable%5D=true&columns%5B7%5D%5Borderable%5D=true&columns%5B7%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B7%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B8%5D%5Bdata%5D=inativo&columns%5B8%5D%5Bname%5D=&columns%5B8%5D%5Bsearchable%5D=true&columns%5B8%5D%5Borderable%5D=true&columns%5B8%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B8%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B9%5D%5Bdata%5D=blackList&columns%5B9%5D%5Bname%5D=&columns%5B9%5D%5Bsearchable%5D=true&columns%5B9%5D%5Borderable%5D=true&columns%5B9%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B9%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B10%5D%5Bdata%5D=&columns%5B10%5D%5Bname%5D=&columns%5B10%5D%5Bsearchable%5D=true&columns%5B10%5D%5Borderable%5D=false&columns%5B10%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B10%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B11%5D%5Bdata%5D=&columns%5B11%5D%5Bname%5D=&columns%5B11%5D%5Bsearchable%5D=true&columns%5B11%5D%5Borderable%5D=false&columns%5B11%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B11%5D%5Bsearch%5D%5Bregex%5D=false&order%5B0%5D%5Bcolumn%5D=2&order%5B0%5D%5Bdir%5D=asc&start=0&length=0&search%5Bvalue%5D=&search%5Bregex%5D=false&inactive=0";

   if($result = $this->callRequest(false,'api/user',$querParam, true)){

       $users = json_decode($result);

       foreach($users->data as $user){
 // print_r($user);

           if($user = $this->returnValidateUser($user)){

               $index = false;

 //Percorre o array para ver se existe um motorista com este CPF, caso tenha atribui o indice dele no array para a variavel
               foreach($motoristas as $chaveMot => $motorista){

                   if($motorista->cpf == $user->cpf){
                       $index = $chaveMot; 
                   }

               }


 //Caso exista, e o id for menor que o usuário atual apaga o usuário atual que está no arrray e adiciona o novo
               if($index){

                   if(intval($motoristas[$index]->id) < intval($user->id)){
                       unset($motoristas[$index]);
                       array_push($motoristas,$user);
                   }

               }else{
                   array_push($motoristas,$user);
               }

           }

       } 

       $motoristas = $this->addTemplateToUsers($motoristas);
       $this->insertUsersDb($motoristas); 

   }

}

public function addUser($user, $isRH = false){
   $this->insertUsersDb($user, $isRH); 
   $user = $user[0];
   if($user->tipo == 1 or $user->tipo == 2){
       $data = new StdClass(); 
       $data->inativo = false ;
       $data->blackList = false;
       $data->contingency = false;
       $data->cards = [] ;
       $data->groups = [];
       $data->groupsList = [];
       foreach ($user->departamento as $key => $value) {
           if($grupoInfo = $this->getGroups(intval($value))){
               array_push($data->groups,intval($value));
               array_push($data->groupsList,$grupoInfo);
           } 
       } 
       $data->shelfLifeDate = "";
       $data->shelfEndLifeDate = "";
       $data->name = $user->nome;
       $data->id = $user->pis * -1;
       $data->rg = $user->rg;
       $data->registration = $user->registration;
       $data->cpf = $user->cpf;
       $data->templates = $user->templates;
       $data->templatesImages = [null] ;
       $data->pis = $user->pis * 1;
       $data->shelfLife = null;
       $data->shelfEndLife = null;
       $data->foto = null;
       $data->fotoDoc = null;

       if($result = $this->callRequestPut('api/user/',$data, true)){
           return true;
       }else{
           return false;
       } 
   }else{
       return true;
   } 
}

public function addUserImport($user){
   if($result = $this->callRequestPut('api/user/',$user, true)){
       return $result;
   }else{
       return $result;
   } 
}

private function insertUsersDb($arrUsers, $isRH = false){

   $this->CI->load->model('ti_biometria/biometria_usuario_digital_model');
   $this->CI->load->model('ti_biometria/biometria_usuario_model');
   $this->CI->load->model('ti_biometria/biometria_fila_model');
   $this->CI->load->model('ti_biometria/biometria_usuario_equipamento_model');
   $this->CI->load->model('ti_biometria/biometria_equipamento_model');

   $ArrnewUsers = [];

   foreach ($arrUsers as $motorista){ 
       if(strlen($motorista->cpf) == 11){$motorista->cpf = "000".$motorista->cpf;} 
       $newUser = $this->CI->db->query("SELECT * FROM BIOMETRIA_USUARIO WHERE CPF = '$motorista->cpf' ")->num_rows() <= 0 ? true : false;

       if($newUser){
           if($isRH){
               $userInsert = ["CPF" => $motorista->cpf, "DSCOMENTARIO" => "INTEGRADO VIA WEB", "TIPO" => $motorista->tipo];
           }else{
               $userInsert = ["CPF" => $motorista->cpf, "DSCOMENTARIO" => "INTEGRADO VIA WEB", "TIPO" => 9];
           }

           $this->CI->biometria_usuario_model->insert($userInsert);

           foreach($motorista->templates as $finger){
               if(substr($finger, -1) == '|'){
                   $finger = substr($finger, 0, -1);
               }
               $fingerInsert = $this->dividirTemplate($finger);
               $fingerInsert['USUARIOCPF'] = $motorista->cpf;
               $this->CI->biometria_usuario_digital_model->insert($fingerInsert);
           }
           array_push($ArrnewUsers,$motorista->cpf);
       }
       $motorista->cpf = substr($motorista->cpf, 3, 11);
   }

   if(count($ArrnewUsers) && 
       $isRH && 
       $this->returnValidateUser($motorista)){
       $arrEquipamentos = $this->CI->biometria_equipamento_model->get_all(["INTEGRADO <>" => 0]); 
   foreach($arrEquipamentos as $equipamento){ 
       foreach($ArrnewUsers as $cpf){
           $this->CI->biometria_fila_model->insert(["EQUIPAMENTOID" => $equipamento->ID, "USUARIOCPF" => $cpf, "OPERACAO" => "CADASTRO"]);
       }
   }
}

if(count($ArrnewUsers) > 0 && !$isRH){
   $arrEquipamentos = $this->CI->biometria_equipamento_model->get_all(["INTEGRADO <>" => 0]); 
   foreach($arrEquipamentos as $equipamento){ 
       foreach($ArrnewUsers as $cpf){
           $this->CI->biometria_fila_model->insert(["EQUIPAMENTOID" => $equipamento->ID, "USUARIOCPF" => $cpf, "OPERACAO" => "CADASTRO"]);
       }
   } 
}
}

private function addTemplateToUsers($motoristas){

   foreach($motoristas as $motorista){ 
       $motorista->templates = [];

       if($result = $this->callRequest(false,'api/user',$motorista->id, true)){

           $arrTemplates = json_decode($result);

           foreach($arrTemplates->templates as $template){
               array_push($motorista->templates,$template);
           }
       }
   } 
   return $motoristas;
}

private function returnValidateUser($user){ 
   if($user->registration){ 
 //Verifica se o cadastro é de um freteiro ou motorista da casa
       if(strlen($user->registration) == 11){ 
 //Concatena com 3 zeros para fazer a consula no SOFtran
           $cpfUser = '000'.$user->registration;
 //Prepara query de consulta
           $queryFrete = $this->CI->db->query("SELECT DSNOME,CDFRETEIRO FROM SOFTRAN_MAGNA.GTCFRETE WHERE CDFRETEIRO = '$cpfUser' AND ININATIVO = 0"); 
 //Verifica se existe um freteiro para este cpf
           if($queryFrete->num_rows() > 0){
 //Adiciona o nome para o motorista
               $user->name = $queryFrete->result()[0]->DSNOME;
               $user->cpf = $queryFrete->result()[0]->CDFRETEIRO;
               return $user;
           } 
       }else{
           return $this->existsMotoristaDb($user); 
       } 
   }else{
       return $this->existsMotoristaDb($user,false);
   } 
}

private function existsMotoristaDb($user,$numSearch = true){
   if($numSearch){
 //Remove caracter '\t' que vem alguns registros
       $user->registration = str_replace("\t", '', $user->registration); 
       if(is_numeric($user->registration)){ 
           $queryFunc = $this->CI->db
           ->query("SELECT A.DSNOME as DSNOME, A.NRCPF
               FROM SOFTRAN_MAGNA.SISFUN A
               LEFT JOIN SOFTRAN_MAGNA.FPGCBO B ON B.CDCBO = A.CDCBO2002 
               AND B.CDSEQUENCIALCBO = A.CDSEQUENCIALCBO2002
               WHERE UPPER(B.DSDESCRICAO) LIKE '%MOTORISTA%' 
               AND A.FGDEMITIDO = 0
               AND A.CDMATRICULA = $user->registration");
           $queryMatricula = $this->CI->db
           ->query("SELECT A.DSNOME as DSNOME, A.NRCPF
               FROM SOFTRAN_MAGNA.SISFUN A
               LEFT JOIN SOFTRAN_MAGNA.FPGCBO B ON B.CDCBO = A.CDCBO2002 
               AND B.CDSEQUENCIALCBO = A.CDSEQUENCIALCBO2002
               WHERE UPPER(B.DSDESCRICAO) LIKE '%MOTORISTA%' 
               AND A.FGDEMITIDO = 0
               AND A.CDFUNCIONARIO = $user->registration");
           if($queryFunc->num_rows() > 0){
               $user->name = $queryFunc->result()[0]->DSNOME;
               $user->cpf = '000'.$queryFunc->result()[0]->NRCPF;
               return $user;
           }else if($queryMatricula->num_rows() > 0){
               $user->name = $queryMatricula->result()[0]->DSNOME;
               $user->cpf = '000'.$queryMatricula->result()[0]->NRCPF;
               return $user;
           }else{
               return false;
           }
       }
   }else{
       if(substr($user->name,0,6) == "MOT - "){
           $user->name = strtoupper(substr($user->name,6));
           $queryName = $this->CI->db
           ->query("SELECT UPPER(A.DSNOME) as DSNOME, A.NRCPF
               FROM SOFTRAN_MAGNA.SISFUN A
               LEFT JOIN SOFTRAN_MAGNA.FPGCBO B ON B.CDCBO = A.CDCBO2002 
               AND B.CDSEQUENCIALCBO = A.CDSEQUENCIALCBO2002
               WHERE B.DSDESCRICAO LIKE '%Motorista%' 
               AND A.FGDEMITIDO = 0
               AND UPPER(A.DSNOME) like '%$user->name%' ");
           if($queryName->num_rows() > 0){
               $user->name = $queryName->result()[0]->DSNOME;
               $user->cpf = '000'.$queryName->result()[0]->NRCPF;
               return $user;
           }else{
               return false;
           }
       }else{
           $user->name = strtoupper($user->name);
           $queryName = $this->CI->db
           ->query("SELECT UPPER(A.DSNOME) as DSNOME, A.NRCPF
               FROM SOFTRAN_MAGNA.SISFUN A
               LEFT JOIN SOFTRAN_MAGNA.FPGCBO B ON B.CDCBO = A.CDCBO2002 
               AND B.CDSEQUENCIALCBO = A.CDSEQUENCIALCBO2002
               WHERE B.DSDESCRICAO LIKE '%Motorista%'
               AND A.FGDEMITIDO = 0
               AND UPPER(A.DSNOME) like '%$user->name%' ");
           if($queryName->num_rows() > 0){
               $user->name = $queryName->result()[0]->DSNOME;
               $user->cpf = '000'.$queryName->result()[0]->NRCPF;
               return $user;
           }else{
               return false;
           }
       }
   }
}

public function dividirTemplate($digital){ 

   $usuario_template = [];

   $usuario_template['TEMPLATE1'] = substr($digital, 0, 4000);

   if (strlen($digital) > 4000){
       $usuario_template['TEMPLATE2'] = substr($digital, 4000, 8000);
   }

   if (strlen($digital) > 8000){
       $usuario_template['TEMPLATE3'] = substr($digital, 8000);
   }

   return $usuario_template;

}

}