<?

trait ovh_parts_sms {

  private function internationalized($dest, $country = "fr"){
    switch($country){
      case 'fr':
        $str = preg_replace("#^[0-9]#","", $dest);
        $str = substr($str,-9);
        if(strlen($str) != 9)
          throw new Exception("Unsupported short phone syntax $dest");
        return "+33$str";
      break;
      default:
      throw new Exception("Unknow phone format syntax for $country");
    }
  }

  public function send($sms_dest, $sms_message){
    $sms_dest = $this->internationalized($sms_dest);
    //the maximum time -in minute(s)- before the message is dropped, defaut is 48 hours
    $sms_expiracy = 48 * 60;
    //the sms class: flash(0),phone display(1),SIM(2),toolkit(3)
    $sms_class    = 1;
    //the time -in minute(s)- to wait before sending the message, default is 0
    $sms_deferred = 0;
    //the priority of the message (0 to 3), default is 3
    $sms_priority = 3;
    //the sms coding : 1 for 7 bit or 2 for unicode, default is 1
    $sms_encoding = 2;
    //an optional tag 
    $sms_tag      = "";

    try {
      $result = $this->ovh_telephonySmsSend(
              OVH_SMS_ACCOUNT, OVH_SMS_FROM,
              $sms_dest, $sms_message,
              $sms_expiracy, $sms_class, $sms_deferred, $sms_priority, $sms_encoding, $sms_tag
      );
      rbx::ok("SMS successfully sent $sms_message");
    } catch(SoapFault $fault) {
      rbx::error("Could not send sms : $fault");
    }
  }

}