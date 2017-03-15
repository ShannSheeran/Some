<?php
namespace Api0\Controller;

use Api0\Controller\Request\CardListWhereRequest;
use Zend\Db\Sql\Where;
use Api0\Controller\Request\CardSubmitRequest;

/**
 * 名片提交
 */
class CardSubmit extends CommonController
{

    public function __construct()
    {
        $this->myRequest = new CardSubmitRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        
        $where = array();
        $list = array();
        $where['id'] = $request->card->id;
        if ($where['id']) {
            $data = $this->getPageTable()->getOne($where);
        }
        
        $list = array(
            'head_icon' => $request->card->id_image,
            'signature' => $request->card->signature,
            'englist_name' => $request->card->english_name,
            'qq' => is_array($request->card->qq) ? implode(",", $request->card->qq) : "",
            'email' => is_array($request->card->email) ? implode(",", $request->card->email) : "",
            'telephone' => is_array($request->card->telephone) ? implode(",", $request->card->telephone) : "",
            'weixin_number' => $request->card->wechat,
            'weibo' => $request->card->weibo,
            'company' => $request->card->company,
            'position' => $request->card->job,
            'show' => $request->card->show,
            'address' => $request->card->address->street,
            'timestamp'=>$this->getTime()
        );
        
        $pagelist = array();
        $pagelist = array(
            'description' => $request->card->job,
            'user_id' => $this->getUserId(),
            'last_edit_time' => $this->getTime()
        )
        ;
        if ($where['id'] && $data) {
            $card = $this->getCarteTable()->updateData($list, array('id'=>$data['carte_id']));
            $card = $this->getPageTable()->updateData($pagelist, array(
                'id' => $data['id']
            ));
            $id = $where['id'];
        } else {
            $list['name'] = $request->card->name;
            $list['mobile'] = is_array($request->card->mobile) ? implode(",", $request->card->mobile) : "";
            $id = $this->getCarteTable()->insertData($list);
            $pagelist['carte_id'] = $id;
            $pagelist['title']=$request->card->name;
            $pagelist['timestamp'] = $this->getTime();
            $pageId = $this->getPageTable()->insertData($pagelist);
            $u_id = $this->getUserId();
            $u_data = $this->getUserTable()->getOne(array('id'=>$u_id));
            if (!$u_data['page_id'])
            {
                $this->getUserTable()->updateData(array('page_id'=>$pageId,'name'=>$request->card->name), array('id'=>$u_id));
            }
            if (!$u_data['name'])
            {
                $this->getUserTable()->updateData(array('name'=>$request->card->name), array('id'=>$u_id));
            }
        }
        $response->id = $id;
        return $response;
    }
}







