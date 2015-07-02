<?

trait parts_mails {


  function show_alias_list($domain ){
    $list = $this->lnk->redirectedEmailList($this->sid, $domain);
    $list = $this->sstruct($list);
    $list = array_extract($list, 'local');
    sort($list);
    cli::box("Alias list for $domain", $list);
  }

  private function alias_update($domain, $alias, $old_target, $target){
    $this->lnk->redirectedEmailModify($this->sid, $domain, $alias, $old_target, $target, "");
  }


  function alias_add($domain, $dest_pop, $alias){
    $this->lnk->redirectedEmailAdd($this->sid, $domain, $alias, $dest_pop, "", false);
    $this->show_alias_list($domain);
  }


}