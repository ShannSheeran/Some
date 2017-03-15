<?php
namespace Api5\Controller;

use Zend\Form\Annotation\Object;

/**
 * 赞列表
 */
class PraiseUserList extends CommonController
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $where = array();
        $where['chat_id'] = $request->id;
        $chat = $this->getChatTable()->getOne(array(
            'id' => $request->id,
            'delete' => 0
        ));
        if ($request->id && $chat) {
            $data = $this->getChatPraiseTable()->getAll($where);
            $list = array();
            if ($data['list']) {
                $relation = $this->getUserRelation($this->getUserId());
                foreach ($data['list'] as $val) {
                    $page = $this->getViewUserPageTable()->getOne(array(
                        'id' => $val['user_id']
                    ));
                    $company_data = $this->getCompanyTable()->getAll(array(
                        'id' => $page['company_id']
                    ));
                    $company = array();
                    foreach ($company_data['list'] as $values) {
                        $company['id'] = $values['id'] ? $values['id'] : 0;
                        if ($values['audit_status'] == 2) {
                            $company['name'] = $values['name'];
                        }
                    }
                    if ($page) {
                        $relationship = 0;
                        if (array_key_exists($page['id'], $relation['deep1'])) {
                            $relationship = 1;
                        } elseif (array_key_exists($page['id'], $relation['deep2'])) {
                            $relationship = 2;
                        }
                        $img = $this->getImageTable()->getOne(array(
                            'id' => $page['head_icon']
                        ));
                        $list[]['card'] = array(
                            'id' => $page['page_id'],
                            'name' => $page['name'],
                            'imagePath' => $img['path'] . $img['filename'],
                            "job" => $page['position'],
                            'user' => array(
                                'id' => $page['id'],
                                'relationship' => $relationship
                            ),
                            'company' => (object) $company
                        );
                    } else {
                        $list[]['card'] = array(
                            'id' => '',
                            'name' => '',
                            'imagePath' => ''
                        );
                    }
                }
            }
            $response->total = $data['total'];
            $response->cards = $list;
            return $response;
        } else {
            return $response;
        }
    }
}







