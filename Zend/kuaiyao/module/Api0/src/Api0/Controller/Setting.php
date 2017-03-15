<?php
namespace Api0\Controller;

/**
 * 系统设置
 */
class Setting extends CommonController
{
    const SINGLE_SETTINGS = '1,2,5';
    
    const GROUP_SETTINGS = '3';

    public function index()
    {
        $response = $this->getAiiResponse();
        $keys = explode(',', self::SINGLE_SETTINGS);
        foreach ($keys as $id)
        {
            $data = $this->getSettingTable()->getOne(array('id' => $id));
            $key = $this->uppercase($data['key'], '_', 1);
            $response->$key = $data['value'];
        }
        $keys = explode(',', self::GROUP_SETTINGS);
        foreach ($keys as $id)
        {
            $data = $this->getSettingTable()->getOne(array('id' => $id));
            $key = $this->uppercase($data['key'], '_', 1);
            $array_key = $key . 's';
            $response->$array_key = $this->getSettingItems($id, $key);
        }
        return $response;
    }
    
    private function getSettingItems($id, $key)
    {
        $where = array('setting_group_id' => $id, 'delete' => DELETE_FALSE);
        $order = array('id' => 'asc');
        $data = $this->getSettingItemTable()->fetchAll($where, $order);
        $list = array();
        foreach ($data as $value)
        {
            $item = array(
                'id' => $value['id'],
                'name' => $value['name'],
                'value' => $value['value']
            );
            $list[][$key] = $item;
        }
        return $list;
    }
    
    /**
     * 
     * @param unknown $name
     * @param unknown $type
     * @return string
     * @version 2015-2-9 WZ
     */
    function uppercase($string, $delimiter, $type)
    {
        $array = explode($delimiter, $string);
        foreach ($array as $key => $value)
        {
            if (1 == $type && 0 == $key)
            {
                continue;
            }
            $array[$key] = ucfirst($value);
        }
        $result = implode('', $array);
        return $result;
    }
}
