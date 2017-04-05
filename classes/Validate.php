<?php
class Validate{
	private $_passed = false,
			$_errors = array(),
			$_db = null;

	public function __construct(){
		$this->_db = DB::getInstance();
	}

	public function check($source, $items = array()){
		foreach($items as $key => $rules){

			foreach ($rules as $rule => $rule_value) {
				$value = trim($source[$key]);

				if(strpos($key, '_')){
					$item = ucfirst(str_replace('_', ' ', $key));
				}else{
					$item = ucfirst($key);
				}

				if($rule === 'required' && empty($value)){
					$this->addError([$item => "{$item} is required"]);

				} else if(!empty($value)){
					switch ($rule) {
						case 'min':
							if(strlen($value) < $rule_value){
								$this->addError([$item => "{$item} must be a minimum of {$rule_value} characters."]);
							}
						break;
						case 'max':
							if(strlen($value) > $rule_value){
								$this->addError([$item => "{$item} must be a maximum of {$rule_value} characters."]);
							}							
						break;
						case 'matches':
							if($value != $source[$rule_value]){
								$rule_value = ucfirst($rule_value);
								$this->addError([$item => "{$rule_value} must match {$item}"]);
							}
						break;
						case 'unique':
							$check = $this->_db->get($rule_value, array($key, '=', $value));
								if($check->count()){
									$this->addError([$item => "{$item} already exists."]);
								}							
						break;
						case 'email':
							if(!filter_var($value, FILTER_VALIDATE_EMAIL) === $rule_value){
								$this->addError([$item => "{$item} must be a valid email address."]);
							}
						break;
						
					}
				}
			}
		}

		if(empty($this->_errors)){
			$this->_passed = true;
		}
		return $this;
	}

	public function checkFile($name, $required = null, $types = array(), $size = null){
		$file = basename($_FILES[$name]['name']);
		$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		$fileSize = $_FILES[$name]['size'];
		if(strpos($name, '_')){
			$name = ucfirst(str_replace('_', ' ', $name));
		}else{
			$name = ucfirst($name);
		}		


		if($required === 'required' && empty($file)){
			$this->addError([$name=> "{$name} is required!"]);
		}elseif(!empty($file)){
			$types = array_map('strtolower', $types);
			if(!in_array($ext, $types)){
				$errorType =  "{$name} must be an " . implode(', ', $types) . " image.";
			}
			
			if($fileSize > ($size * 1024)){
				$errorSize = "{$name} size must be less than {$size} kb.";
			}

			$tsError = '';
			if(!empty($errorType) || !empty($errorSize)){
				if(!empty($errorType)) $tsError .= $errorType;
				if(!empty($errorSize)) $tsError .= $errorSize; 
				$this->addError([$name => $tsError]);
			}
		}	
		if(empty($this->_errors)){
			$this->_passed = true;
		}
		return $this;
	}

	private function addError($error = []){
		$this->_errors[] = $error;
	}

	public function errors(){
		return $this->_errors;
	}

	public function passed(){
		return $this->_passed;
	}


}