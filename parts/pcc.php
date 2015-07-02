<?

trait parts_pcc {


/**
* @interactive_runner hide
*/
  function load_pcc($pcc_name){
    $res = $this->Pcc_listPcc();

    $pccStruct = $res->value->pcc;
    if($pccStruct->name != $pcc_name)
      throw new Exception("Could not resolve pcc id");

    $this->pcc_id = $pccStruct->id; 
    rbx::ok("Found pccStruct : {$this->pcc_id}");
  }

  private function get_pcc_lnk(){
    $this->pcc_lnk = new SoapClient(self::OVH_PCC_WSDL, array( 'location' => self::OVH_PCC_DISPATCHER, 'trace' => 1 ) );
    $client = new SoapClient(self::OVH_SESSIONHANDLER_WSDL , array( 'location' => self::OVH_SESSIONHANDLER_DISPATCHER, 'trace' => 1) );
    $result = $client->login( array(
            'login' => $this->login,
            'password' => $this->password,
            'language' => 'fr',
            'multisession' => 'true'
    ));
    $this->pcc_sid = $result->value->id;
    rbx::ok("Open pcc lnk with session {$this->pcc_sid}");
    return $this->pcc_lnk ;
  }


  function reverse($ip, $target){
    $result = $this->CurrentPcc_updateIpReverse( array(
                'ip'        => $ip,
                'newName'   => $target,
    ));
    var_dump($result);
  }



  function __pcc_call($name, $args, $extras = array()){
    $callback = array($this->pcc_lnk, $name);
    $args = array_merge(array(
              'sessionId'  => $this->pcc_sid,
      ), $extras, $args[0] ? $args[0] : array());

    $response = call_user_func($callback, $args);

    return $response;
  }



}
