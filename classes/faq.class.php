<?php
class FAQ {
	
	private $g;
	
	public function __construct() {
		global $g;
		$this->g = $g;
	}
	
	public function get($dep) {
		
		$qry = $this->g->_query("select faq_question as q, faq_answer as a from #_faq where faq_section = ? order by faq_order asc", $dep);
		return ($this->g->_count($qry) == 0 ? array() : $this->g->_array($qry, 1));
	}
	
	public function save($save = array(), $sql = array()) {
		
		$coll = $this->g->collect();
		foreach($coll as $k => $v) {
			$t = explode('_', $k);
			$key = sprintf("%s_%s", $t[0], $t[1]);
			$out[$t[2]][$t[3]][$key] = $v;
		}
		
		$i = 0;
		// Loop to set values
		foreach($out as $ki => $vi) {
			foreach($vi as $k => $v) {
				foreach($v as $key => $value) {
					$save[$i][$key] = $value;
				}
				$save[$i]['faq_order'] = $k;
				$save[$i]['faq_section'] = $ki;
			$i++;
			}
		}
		// Clear out empty ones
		$i = 0;
		foreach($save as $v) {
			if(!empty($v['faq_question']) && !empty($v['faq_answer']))
				$sql[] = $this->g->impl('values', $v);
			$i++;
		}
		
		$this->g->_exec("delete from #_faq");
		$this->g->_exec("insert into #_faq :fields values :params",
							array(':fields' => $this->g->impl('params', $save[0]), ':params' => implode(', ', $sql)));
		
		$this->cookie = 'Frågor och svar har blivit uppdaterade!';
		$this->destruct();
	}
	
	private function destruct() {
		
		if(!empty($this->cookie))
			cookie('success', $this->cookie);
			
		$this->g->send($this->g->href('admin', $this->g->v1));
	}
}
?>