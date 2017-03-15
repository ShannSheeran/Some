<?php
namespace Vendor;
class loader{

	static  function autoload($class)
	{
		 $file = BASEDIR."/".str_replace("\\", "/", $class).".php";
		 include $file;
	}
}
?>