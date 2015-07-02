<?

class ovh {
  use parts_conf;
  use parts_dns;
  use parts_mails;
  use parts_pcc;

  const OVH_SOAPI_WSDL = "http://www.ovh.com/soapi/soapi-re-1.58.wsdl";

  const OVH_PCC_WSDL = "https://ws.ovh.com/privateCloud/r1/soap.wsdl";
  const OVH_PCC_DISPATCHER = "https://ws.ovh.com/privateCloud/r1/soap.dispatcher";

  const OVH_SESSIONHANDLER_WSDL = "https://ws.ovh.com/sessionHandler/r2/soap.wsdl";
  const OVH_SESSIONHANDLER_DISPATCHER = "https://ws.ovh.com/sessionHandler/r2/soap.dispatcher";
  const OVH_MAGIC_PREFIX = 'ovh_';

  private $sid;
  private $login;
  private $password;

  function __construct($login, $password){
    $this->login    = $login;
    $this->password = $password;
    $this->auth();
  }

  private function get_lnk(){

    $options = array(
      'features' => SOAP_SINGLE_ELEMENT_ARRAYS | SOAP_USE_XSI_ARRAY_TYPE,
    );

    $this->lnk = new SoapClient($this->load_wsdl(self::OVH_SOAPI_WSDL), $options);

    $tmp = $this->lnk->__getFunctions ();
    $this->valid_func = ['lnk' => []];
    foreach($tmp as $func)
      $this->valid_func['lnk'][] = end(explode(' ',strtok($func, "(")));
    sort($this->valid_func['lnk']);

    return $this->lnk;
  }

  private function auth(){
    $this->sid  = $this->lnk->login($this->login, $this->password, "fr", false);
    rbx::ok("Login successfull with session {$this->sid}");
  }


  function __call($func, $args){

    if(starts_with($func, "CurrentPcc_")) 
      return $this->__pcc_call(
        strip_start($func, "CurrentPcc_"),
        $args,
        array('pccId' => $this->pcc_id)
      );

    if(starts_with($func, "Pcc_")) 
      return $this->__pcc_call(strip_start($func, "Pcc_"), $args);

/*    if(!starts_with($func, self::OVH_MAGIC_PREFIX))
      return false;
    $func = strip_start($func, self::OVH_MAGIC_PREFIX); */

    $callable = in_array($func, $this->valid_func['lnk']);
    if(!$callable)
      throw new Exception("Unsupported ovh func $func");
    array_unshift($args, $this->sid);
    return call_user_func_array(array($this->lnk, $func), $args);
  }

  function __get($key){

    if(method_exists($this, $getter = "get_$key"))
      return $this->$getter();
  }


  function sstruct($data){
    if(is_object($data)) 
      $data = (array)$data;
    elseif(!is_array($data)) return $data ;
    foreach($data  as &$d)
      $d = $this->sstruct($d);
    return $data;
  }



  private function load_wsdl($url){ //wsdl_cache=file is not enough
    $local_wsdl = sys_get_temp_dir().DIRECTORY_SEPARATOR.basename($url);
    if(!is_file($local_wsdl))
      if(!copy($url, $local_wsdl))
        throw new Exception("Invalid wsdl $url");
    return $local_wsdl;
  }



}