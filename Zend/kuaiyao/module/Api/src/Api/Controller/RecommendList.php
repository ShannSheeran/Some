<?php
namespace Api\Controller;
use Api\Controller\Request\UserRequest;

/**
 * 推荐码列表
 *
 * @author 
 *
 */
class RecommendList extends CommonController
{

    /**
     * 
     * @return string|\Api21\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest(); // 获取请求参数
        $response = $this->getAiiResponse(); // 初始化返回参数
        $this->checkLogin(); // 验证登录
        $where = array();
        $where['user_id'] = $this->getUserId(); // 获取用户id
        if ($request->action) {
            $where['status'] = ($request->action == 1 ? 1 : 0);
        }
        $data = $this->getAll($this->getInvitationCodeTable(), $where);//查询数据库
        
        $list = $this->setList($data['list']);
        
        $response->total = $data['total'];
        $response->items = $list;
        return $response;
        
    }
    
    /**
     * 格式化列表
     * 
     * @param unknown $data
     * @version 2015-6-24 WZ
     */
    public function setList ($data) {
        $list = array();
        if ($data) {
            foreach ($data as $value) {
                $list []['item'] = array(
                    'id' => $value['id'],
                    'code' => $value['code'],
                    'status' =>  ($value['status']== 1 ? 1 : 2)
                );
            }
        }
        return $list;
    }
}