<?php 
class Settings{
	private $_db,
			$data;

	public function __construct(){
		$this->_db = DB::getInstance();
		$this->data = $this->_db->get('settings', array('1', '=', '1'))->results();
	}

	public function companyName(){
		return !empty($this->data) ? $this->data[0]->company_name : null;
	}

	public function companyLogo(){
		return !empty($this->data) ? $this->data[0]->company_logo : null;
	}

	public function timeZone(){
		return !empty($this->data) ? $this->data[0]->time_zone : null;
	}

	public function startTime(){
		return !empty($this->data) ? $this->data[0]->start_time : null;
	}

	public function closeTime(){
		return !empty($this->data) ? $this->data[0]->close_time : null;
	}

	public function weekend(){
		return !empty($this->data) ? explode(',', $this->data[0]->wholiday) : null;
	}

	public function maintMode(){
		return !empty($this->data[0]->maint_mode) ? $this->data[0]->maint_mode : null;		
	}

	public function underMaintenance(){
		return !empty($this->data[0]->maint_msg) ? $this->data[0]->maint_msg : null;
	}

}