<?php
namespace Api3\Controller;

/**
 * 系统设置
 */
class Setting extends CommonController
{

    public function index()
    {
        $response = $this->getAiiResponse();
        $settting = $this->getSetting();
        foreach ($settting as $key => $value)
        {
            $response->$key = $value;
        }
        return $response;
    }
}
