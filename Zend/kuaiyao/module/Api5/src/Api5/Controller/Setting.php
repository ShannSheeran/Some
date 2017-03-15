<?php
namespace Api5\Controller;

/**
 * 系统设置
 */
class Setting extends CommonController
{

    public function index()
    {
        $response = $this->getAiiResponse();
        $settting = $this->getSetting();
        $array = array();
        foreach ($settting as $key => $value) {
            $array[$key] = $value;
        }
        $response->goodsPrice = $array['goodsPrice'];
        $response->goodsDiscountPrice = $array['goodsDiscountPrice'];
        $data = $this->getSetupTable()->fetchAll();
        
        foreach ($data as $val) {
            $value = json_decode($val['value']);
            $ids = (array)$value;
            $image_data = $this->getImageTable()->getOne(array(
                'id' => $ids['image'],
            ));
            $group = array(
                'imagePath' => $image_data['path'] . $image_data['filename'],
                'title' => $array['title'],
                'content' => $array['content'],
                'timestampUpdate' => $value->timestampUpdate
            );
        }
        $response->group = $group;
        return $response;
    }
}
