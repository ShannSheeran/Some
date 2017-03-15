<?php
namespace Api0\Controller;

/**
 * 用户，更新个人信息
 *
 * @author
 *         WZ
 *        
 */
class UserUpdate extends User
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $Where = array();
        $Where['id'] = $this->getUserId();
        if ($request->action == 1) {
            $id = $request->id;
            if (! $id) {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            $page = $this->getPageTable()->getOne(array(
                'id' => $id,
                'user_id' => $Where['id']
            ));
           if (!$page){
               return STATUS_NODATA;
           }
            $this->getUserTable()->updateData(array(
                "page_id" => $id,
                'name' => $page['title']
            ), $Where);
        }
        return STATUS_SUCCESS;
    }
}