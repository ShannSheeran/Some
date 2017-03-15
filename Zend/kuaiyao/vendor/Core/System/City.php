<?php
namespace Core\System;

class City{
	public $str;
	public $ids;
	
	public function w_cache($c_name=null){
				$this->str=file_get_contents(APP_PATH.'/Cache/CityCache/cache.php');
				$this->ids=substr($this->str,0,strrpos($this->str,','));
				$result=explode(',',$this->ids);
				if($c_name && !in_array($c_name,$result)){
					file_put_contents(APP_PATH.'/Cache/CityCache/cache.php',$c_name.",",FILE_APPEND);
				}
				return $result;
			}
	
}
	
?>