<?php
namespace Api3\Controller;

use Zend\Db\Sql\Where;
/**
 * 信息、信息列表
 */
class NotificationList extends CommonController
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $action = $request->action;
        $list = array();
        $action_array = array(1,2);
        if (!in_array($action, $action_array))
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        if ($action == 1) {
            $this->checkLogin();
            $where = array();
            $where['user_id'] = $this->getUserId();
            $data = $this->getAll($this->getNotificationRecordsTable(), $where);
            
            if ($data['list']) {
                foreach ($data['list'] as $val) {
                    $list[]['message'] = array(
                        'id' => $val['id'],
                        'title' => $val['title'],
                        'content' => $val['content'],
                        'imagePath' => '',
                        'timestamp' => $val['timestamp']
                    );
                }
            }
        }
        if ($action == 2) {
            $login_info = $this->getLoginTable()->getOne(array('session_id' => $this->getSessionId()));
            $where = new Where();
            $where->equalTo('delete',0);
            $where->equalTo('device_type', 0)->or->equalTo('device_type', $login_info['device_type']);
            $data = $this->getAll($this->getNotificationTable(), $where);
            if ($data['list']) {
                foreach ($data['list'] as $val) {
                    $img = $this->getImageTable()->getOne(array(
                        'id' => $val['image_id']
                    ));
                    $list[]['message'] = array(
                        'id' => $val['id'],
                        'title' => $val['title'],
                        'content' => $val['content'],
                        'imagePath' => $img['path'] . $img['filename'],
                        'timestamp' => $val['timestamp']
                    );
                }
            }
        }
        $response->total = $data['total'];
        $response->messages = $list;
        return $response;
    }
}
