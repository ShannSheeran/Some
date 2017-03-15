<?php
namespace Api\Controller;

/**
 * 删除对象
 */
class DeleteAction extends CommonController
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $user_id = $this->getUserId();
        $action = $request->action;//a;1购物车 2通讯地址；
        $id = $request->id;
        if (!$id || !in_array($action,array(1,2))) {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        if ($action == 1)
        {//删除购物车商品
            $info  = $this->getCartTable()->getOne(array('id'=>$id,'user_id'=>$user_id));
            if (!$info)
            {
                return STATUS_NODATA;//1011
            }
            $this->getCartTable()->delete(array('id' => $id));
        }
        if ($action == 2)
        {//删除收货地址
            $this->getContactsTable()->updateData(array(
                'delete' => 1
            ), array(
                'id' => $id,
                'user_id' => $this->getUserId()
            ));
        }
        return STATUS_SUCCESS;
    }
}
