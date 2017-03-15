<?php

namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use Core\System\UploadfileApi;
use Core\System\WxApi\WxApi;

class PageController extends CommonController
{

    /**
     * 用户列表
     *
     * @return multitype:
     * @version 2014-12-29 liujun
     */
    public function indexAction()
    {
        $this->checkLogin();
        $this->table = $this->getPageTable();
        $this->template = array(
            'page/index',
            'page'
        );

        $this->breadcrumb = array(
            array(
                'url' => '#',
                'title' => '页面'
            ),
            array(
                'url' => '#',
                'title' => '页面列表'
            )
        );

        return $this->getList();
    }

    /**
     * 页面表与微信端同布
     * @version 2015-6-2
     * @author liujun
     */
    public function updatePageListAction()
    {

        $this->checkLogin();
        $this->interfaceType= 2;
        $this->updateList();
        $this->showMessage('数据与微信更新成功！');
    }

    /**
     * 新增页面&&编辑页面
     * @version 2015-6-5
     * @author chenzy
     */
    public function pageAction()
    {
        $this->checkLogin();
        $edit_page = array();
      /*   $usersInfo = $this->getUserTable()->fetchAll(array('delete'=>0));

        //请求查询个体用户信息
        if(isset($_POST['request']) && $_POST['request'])
        {
        	$json = $_POST['request'];
            $userInfo =  $this->getUserTable()->getOne(array('id'=>$json));
            exit(json_encode($userInfo));
        } */

        if(isset($_POST['submit']))
        {
            $data = array(
                'title'=>trim($_POST['title']),
                'description'=>trim($_POST['description']),
                'comment'=>trim($_POST['comment']),
                'page_url'=>trim($_POST['page_url']),
                'icon_url'=>trim($_POST['icon_url']),
            );

            $wxApi = new WxApi();
            if(isset($_POST['id']) && $id = $_POST['id'])
            {
                $edit_page = $this->getPageTable()->getOne(array('id'=>$id));
                $data['page_id'] = (int)$edit_page['page_id'];
                $res = $wxApi->wxPageUpdate(json_encode($data));
                if($res['errcode'] == 0)
                {
                    $this->getPageTable()->update($data,array('id'=>$id));
                    return $this->redirect()->toRoute('admin-page');
                }
            }
            else
            {
                $res = $wxApi->wxPageAdd(json_encode($data));
                if($res['errcode'] == 0)
                {
                    $data['page_id'] = $res['data']['page_id'];
                    $this->getPageTable()->insert($data);
                    return $this->redirect()->toRoute('admin-page');
                }
            }

        }
        $userInfo = '';
        if($id = $this->params()->fromRoute('id'))
        {
            $edit_page = $this->getPageTable()->getOne(array('id'=>$id));
            if($edit_page->user_id){
            	$userInfo = $this->getUserTable()->getOne(array($edit_page->user_id));
            }

            $view = new ViewModel(array(
                'userInfo'=>$userInfo,
                'edit_page'=>$edit_page,
            ));
        }
        else
        {
            $view = new ViewModel(array(
                'usersInfo'=>$userInfo,
            ));
        }

        $this->breadcrumb = array(
            array(
                'url' => '#',
                'title' => '页面'
            ),
            array(
                'url' => $this->plugin ('url')->fromRoute ('admin-page',array(
                    'action' => 'index'
                )),
                'title' => '页面列表'
            ),
            array(
                'url' => '#',
                'title' => '页面详情'
            ),
        );

        $view->setTemplate('admin/page/details');
        return $this->setMenu($view);

    }

    /**
     * 删除页面
     * @version 2015-6-5
     * @author chenzy
     */
    public function delPageAction()
    {
        $this->checkLogin();
        $id = (int)$this->params()->fromRoute('id');
        if($id)
        {
            $page_id['page_ids'][] = $id;
            $jsonData = json_encode($page_id);
            $wxApi = new WxApi();
            $res = $wxApi->wxPageDelete($jsonData);
            if($res['errcode'] == 0)
            {
                $this->getPageTable()->update(array('delete'=>1),array('page_id'=>$page_id['page_ids']));
                $this->showMessage('页面删除成功！');
            }
            else
            {
                $msg = array('9001029'=>'错误信息：页面已应用在设备中，请先解除应用关系再删除!' );
                $this->showMessage('页面删除失败，错误代码:'.$res['errcode'].($res['errcode'] == 9001029 ? $msg[$res['errcode']] : '').'！');
            }
        }
        exit;
    }

}