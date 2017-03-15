<?php
namespace Api3\Controller;

/**
 * 上传用户头像
 * 
 * @author WZ
 *        
 */
class UserUploadImage extends User
{
    /**
     * 
     * @return string
     */
    public function index()
    {
        $this->checkLogin();
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $id = (float)$request->id;
        
        $set = array(
            'image' => $id
        );
        $where = array(
        	'id' => $this->getUserId()
        );
        $this->getUserTable()->update($set, $where);
        
        return STATUS_SUCCESS;
    }
}