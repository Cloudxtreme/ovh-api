<?

trait parts_dns {

  function zoneExport($domain){
    $list = $this->lnk->zoneEntryList($this->sid, $domain);
    $list = $this->sstruct($list);
    $list = array_msort($list, array('subdomain' => SORT_ASC, 'fieldtype' => SORT_DESC)) ;
    $maxlen = max(array_map('strlen', array_extract($list, 'subdomain')));

    $entries = "";
    foreach($list  as $entry) 
      $entries .= str_pad($entry['subdomain'],  $maxlen + 2, " ") . str_pad($entry['fieldtype'], 9, " ").$entry['target'].CRLF;

    return $entries;
  }


  function get_records($domain, $type = 'A'){
    $list = $this->lnk->zoneEntryList($this->sid, $domain);
    $list = $this->sstruct($list);
    $records = array();
    foreach($list as $record) {
      if($record['fieldtype'] != $type) continue;
      $records[$record['subdomain']] = $record['target'];
    }
    ksort($records);
    return $records;
  }


  function dns_add($domain, $type, $sub, $target ){
    $this->lnk->zoneEntryAdd($this->sid, $domain, $sub, $type, $target, false);

    return $this->get_records($domain);
  }

  function dns_del($domain, $type, $sub){
    $this->lnk->zoneEntryDel($this->sid, $domain, $sub, $type, null);

    return $this->dns_list($domain);
  }


}