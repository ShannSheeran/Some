<?php
namespace Api\Controller;

use Zend\Db\Sql\Where;
/**
 * 查询个人信息
 */
class UserDetails extends User
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $where = array();
        $where['id'] = $this->getUserId();
        $data = $this->getUserTable()->getOne($where);
        $adminInfo = array();
        if ($data['admin_id'])
        {
            $adminInfo = $this->getAdminTable()->getOne(array('id'=>$data['admin_id']));
        }
        $cartInfo = $this->getCartTable()->fetchAll(array('user_id'=>$data['id']),array('id','number'));
        $carNumber =0;
        foreach ($cartInfo as $v)
        {
            $carNumber +=$v['number'];
        }
        //订单处理时间
        $set = new Where();
        $set->equalTo('delete', DELETE_FALSE);
        $set->greaterThan('deadline', date('y-m-d h:m:s'));
        $set->lessThan('start_time', date('y-m-d h:m:s'));
        $orderTime = $this->getTimeNodeTable()->getOne($set);
        $item = array(
            'id' => $data['id'],
            'labelId' => $data['label_id'],
            'companyName' => $data['company_name'],
            'contactsName' => $data['contacts_name'],
            'type' => $data['type'],
            'mobile' => $data['mobile'],
            'fax' => $data['fax'],
            'qq' => $data['qq'],
            'email' => $data['email'],
            'description' => $data['description'],
            'adminId' => $data['admin_id'],
            'adminQQ' => $adminInfo ? $adminInfo['qq'] : '',
            'timeNode' => $orderTime?$orderTime['node']:'16:30',
            'regionId' => $data['region_id'],
            'regionInfo' => $data->region_info &&  json_decode($data->region_info) ? json_decode($data->region_info) : array(),
            'street' => $data['street'],
            'address' => $data['address'],
            'cartGoodsNumber' => $carNumber,
            'timestamp' => $data['timestamp']
        );
        $response->user = $item;
        return $response;
    }
}
