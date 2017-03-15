<?php
//	以下代码请保留
define('PUSH_ROOT',__DIR__);

function myAutoload($class)
{
    $file = PUSH_ROOT . '/../../' . $class . '.php';
    $file = strtr($file, array('\\' => '/'));
    try
    {
        if (is_file($file))
        {
            include_once $file;
        }
        else
        {
            throw new Exception('CAN NOT FIND THE CLASS: ' . $class);
        }
    }
    catch (Exception $e)
    {
        echo $e->getMessage();
    }
}

spl_autoload_register('myAutoload');
