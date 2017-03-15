<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Core\System\UploadfileApi;
use Core\System\WxApi\WxApi;
use Core\System\WxApi\WxJsApi;
use Zend\Db\Sql\Where;

class UserController extends CommonController
{

    /**
     * 用户列表
     *
     * @return multitype:
     * @version
     *          2014-12-29
     *          liujun
     */
    public function indexAction()
    {
       /* $this->checkLogin('user_index');
        $this->table = $this->getUserTable();
        $this->seach = array(
            'mobile',
            'id',
            'name'
        );
        $this->template = array(
            'user/index',
            'user'
        );
        $status = $this->params('cid', 1);
        $this->where = array(
            'status' => $status
        );
        $this->breadcrumb = array(
            array(
                'url' => '#',
                'title' => '用户'
            ),
            array(
                'url' => '',
                'title' => '用户列表'
            )
        );
        
        return $this->getList();*/


        $page = $this->params()->fromRoute('page');
        $cid=$this->params()->fromRoute('cid');
        $where = new where();
        if($cid==1)
        {
            $where->equalTo('status',1);
        }else{
            $where->equalTo('status',2);
        }

        $like = null;
        $this->seach = array(
            'name',
            'mobile',
            'id'

        );


        $keyword = $this->params()->fromRoute('keyword') ? trim($this->params()->fromRoute('keyword')) : '';
        if ((isset($_POST['submit']) && $_POST['keyword'] != '') || $keyword) {
            $keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : $keyword;
            if ($keyword && is_array($this->seach)) {
                foreach ($this->seach as $v) {
                    $like[$v] = $keyword;
                }
            }
        }
        $where->equalTo('delete',0);
        $list = $this->getUserTable()->getAll($where,null,array('id'=>'DESC'),true,$page,10,$like);
        foreach($list['list'] as $v)
        {
            $info = $this->getCompanyTable()->getAll(array('delete'=>0,'user_id'=>$v['id']));
            foreach($info['list'] as $val)
            {
                $v['company'][] = $val['name'];
                $v['company_id'][] = $val['id'];
            }
        }
       /* echo "<pre>";
        print_r($list['list']);die;*/
        $view = new ViewModel(array(
            'list'=> $list,
            'paginator' => $list['paginator'],
            'condition' => array(
                'action' => $this->action,
                'cid' => $cid,
                'page' => $page,
                'keyword' => $keyword,
                'where' => $where
            ),
            'cid' => $cid,
            'page' => $page,
            'keyword' => $keyword,
            'where' => $where
        ));
        $view->setTemplate('admin/user/index');
        return $this->setMenu($view,1);
    }

    /**
     * 新增用户/编辑用户
     */
    public function detailsAction()
    {
        $this->checkLogin('user_index');
        $id = (int) $this->params('id');
        $info = $this->getUserTable()->getOne(array(
            'id' => $id
        ));
        $this->breadcrumb = array(
            array(
                'url' => '#',
                'title' => '用户'
            ),
            array(
                'url' => $this->plugin('url')->fromRoute('admin-user', array(
                    'action' => 'index'
                )),
                'title' => '用户列表'
            ),
            array(
                'url' => '',
                'title' => '用户详细'
            )
        );
        $view = new ViewModel(array(
            'info' => $info
        ));
        $view->setTemplate('admin/user/userDetails');
        return $this->setMenu($view, 'operateUser');
    }


    // 修改用户状态
    public function statusAction()
    {
        $this->checkLogin('updateUserStatus');
        $status = (int) $this->params()->fromRoute('page');
        $id = (int) $this->params()->fromRoute('id');
        // var_dump($_SERVER);exit;
        $url = $_SERVER['HTTP_REFERER'];
        if ($status == 1 || $status == 2) {
            $status = $status > 1 ? 1 : 2;
            if ($this->getUserTable()->update(array(
                'status' => $status
            ), array(
                'id' => $id
            ))) {
                echo "<script>window.location.href='$url'</script>";
            }
        }
        die();
    }
    
    /**
     * 前端Ajax查询
     *
     * @version
     *          2015-6-25
     * @author
     *         liujun
     */
    public function checkAction()
    {
        $this->checkLogin();
        if ($_POST['mobile']) {
            $where = array(
                'mobile' => $_POST['mobile']
            );
        }
        $info = $this->getUserTable()->getOne($where);
        
        if ($info) {
            echo 2;
        } else {
            echo 1;
        }
        die();
    }

    /**
     * 新增用户/编辑用户
     */
    public function addAction()
    {
        $data_array = array(
            'head_icon',
            'name',
            'signature',
            'mobile',
            'telephone',
            'qq',
            'email',
            'weixin_number',
            'weibo',
            'company_logo',
            'company_name',
            'en_company',
            'industry',
            'street',
            'web_address',
            'description',
            'company_album',
            'project',
            'project_album',
            'tianmao_shop_url',
            'jingdong_shop_url',
            'taobao_shop_url',
            'wx_code',
            'position'
            
        );

        $info = '';
        $images= array();

        if (isset($_POST['is_post']) && $_POST['is_post'])
        {//提交保存数据
            $verification_item = array(
                'name',
                'user_id'
            );
            
            foreach ($verification_item as $val)
            {
                if(!isset($_POST[$val]) || !$_POST[$val])
                {
                    return $this->showMessage("提交数据不完整");
                }
            }
            
            if(isset($_POST['wxjs_toke']) && $_POST['wxjs_toke'])
            {
                if(isset($_POST['head_icon']) &&  $_POST['head_icon'])
                {
                    if(!is_numeric($_POST['head_icon']))
                    {
                        $head_image = $this->generatePictures($_POST['wxjs_toke'], $_POST['head_icon']);
                        $_POST['head_icon'] = isset($head_image['id']) ? $head_image['id'] : 0;
                    }
                }
            
                if(isset($_POST['wx_code']) &&  $_POST['wx_code'])
                {
                    if(!is_numeric( $_POST['wx_code']))
                    {
                        $wx_image = $this->generatePictures($_POST['wxjs_toke'], $_POST['wx_code']);
          
                        if(isset($wx_image['id']) && $wx_image['id'])
                        {
                            $wx_image_path = HTTP . ROOT_PATH.UPLOAD_PATH.$wx_image['path'];
                            $url = "http://api.wwei.cn/dewwei.html?data=" . $wx_image_path . "&apikey=20141110217674";//读取二维码内容
            				$json_data = file_get_contents($url);
            				$data = json_decode($json_data);
                        
            				if (isset($data->status) && $data->status == 1)
            				{
            				    $text = $data->data->raw_text;
            				    $imgInfo = $this->generationQRcode($text);
            				    $_POST['wx_code']  = $imgInfo['id'];
            				}
                        }
                    }
                }
            }
            $user = $this->getUserTable()->getOne(array('id'=>$_POST['user_id']));
            $_POST['mobile'] = $user->mobile;
            $carte_data = array(
                'name' => $_POST['name'],
                'position' => $_POST['position'],
                'mobile' => $_POST['mobile'],
                'telephone' => $_POST['telephone'],
                'qq' => $_POST['qq'],
                'email' => $_POST['email'],
                'head_icon' => $_POST['head_icon'],
                'wx_code' => $_POST['wx_code'],
                'company' => $_POST['company'],
                'web_address' => $_POST['web_address']
            );
            
            $page_data = array(
                'title' => $_POST['name'],
                'user_id' => $_POST['user_id']
            );
            
            if($user->page_id)
            {
                $page = $this->getPageTable()->getOne(array('id'=>$user->page_id));
                
                if($page->carte_id)
                {
                    $carte_id = $page->carte_id;
                    $this->getCarteTable()->update($carte_data,array('id'=>$page->carte_id));    
                }
                else
                {
                    $carte_data['timestamp'] = $this->getTime();
                    $carte_id = $this->getCarteTable()->insertData($carte_data);
                }
                $page_data['carte_id'] = $carte_id;
                $page_data['timestamp'] = $this->getTime();
                $this->getPageTable()->updateData($page_data,array('id'=>$user->page_id));
            }
            else
            {
                $carte_data['timestamp'] = $this->getTime();
                $carte_id = $this->getCarteTable()->insertData($carte_data);
                $page_data['timestamp'] = $this->getTime();
                $page_data['carte_id'] = $carte_id;
                $page_id = $this->getPageTable()->insertData($page_data);
                $this->getUserTable()->update(array('page_id'=>$page_id),array('id'=>$_POST['user_id']));
                
            }
           
            $data = array();
            foreach ($data_array as $k)
            {
                $data[$k] = isset($_POST[$k]) && $_POST[$k] ? $this->HtmlFilter($_POST[$k]) : '';
            }
            
            $this->getUserApplicationTable()->insertData($data);
            
            
            return $this->redirect ()->toRoute ( 'admin-user', array (
                'action' => 'success'
            ) );

        }
        else
        {
            $wxJsApi = new WxJsApi();
            $openid = $wxJsApi->GetOpenid();

            $_SESSION['openid'] = $openid;
            
            if($_SESSION['openid'])
            {
                $user_partner = $this->getUserPartnerTable()->getOne(array('open_id'=>$_SESSION['openid']));

                $info = $this->getViewUserPageTable()->getOne(array('id'=>$user_partner->user_id));

                $image_id = array();
                if(isset($info->wx_code) && $info->wx_code)
                {
                    $image_id[] = $info->wx_code;
                }
                if(isset($info->head_icon) && $info->head_icon)
                {
                    $image_id[] = $info->head_icon;
                }
            
                if($image_id)
                {
                    $images = $this->getImageTable()->getImages($image_id);
                }
            }    
        }
        
        
        
        $template = 'admin/user/add';
        $view = new ViewModel(array('info'=>$info,'images'=>$images));
        $view->setTemplate($template);
        return $view;
    }
    
    /**
     * 前端临时存储数据
     * @version 2015年7月30日 
     * @author liujun
     */
    public function temporaryDataAction()
    {
        $data_array = array(
                'head_icon',
                'name',
                'signature',
                'mobile',
                'telephone',
                'qq',
                'email',
                'weixin_number',
                'weibo',
                'company_logo',
                'company_name',
                'en_company',
                'industry',
                'street',
                'web_address',
                'description',
                'company_album',
                'project',
                'project_album',
                'tianmao_shop_url',
                'jingdong_shop_url',
                'taobao_shop_url',
                'wx_code',
                'position'
            );
        if(isset($_POST['submit']) && $_POST['submit'])
        {
            
            if(isset($_POST['company_album']) && $_POST['company_album'])
            {
                $_POST['company_album'] = implode(',',$_POST['company_album']);
            }
            
            if(isset($_POST['project_album']) && $_POST['project_album'])
            {
                $_POST['project_album'] = implode(',',$_POST['project_album']);
            }
            
            foreach ($data_array as $k)
            {
                if(isset($_POST[$k]))
                {
                    $_SESSION[$k] = $_POST[$k];
                  
                }
            }

           return $this->redirect ()->toRoute ( 'admin-user', array (
                'action' => 'add'
            ) );
            die();
        }
        die();
    }
    
    /**
     * 申请成功提示页面
     * 
     * @version 2015年7月30日 
     * @author liujun
     */
    public function successAction()
    {
        $view = new ViewModel();
        $view->setTemplate('admin/user/success');
        return $view;
    }
    
    
    /**
     * 用户申请列表
     *
     * @return multitype:
     * @version
     *          2014-12-29
     *          liujun
     */
    public function applicationListAction()
    {
        $this->checkLogin('user_index');
        $this->table = $this->getUserApplicationTable();
        $this->action = 'applicationList';
        $this->seach = array(
            'mobile',
            'id',
            'name'
        );
        $this->screening =  'status';
        $this->template = array(
            'user/applicationList',
            'user'
        );
        $this->breadcrumb = array(
            array(
                'url' => '#',
                'title' => '站点信息'
            ),
            array(
                'url' => '',
                'title' => '用户申请列表'
            )
        )
        ;
        return $this->getList();
    }

    /**
     *获取JSSDK
     */
     public function getJssdkAction()
    {
       $jssdk = $this->getJssdk($_SERVER['HTTP_REFERER']);
	   echo json_encode($jssdk);
	   die();
        
    }
    /**
     * 申请详细
     * @version 2015年7月30日 
     * @author liujun
     */
    public function applyDetailsAction()
    {
            $this->checkLogin('user_index');
            $id = $this->params('id');
            if($id)
            {
                $info = $this->getUserApplicationTable()->getOne(array('id'=>$id));
                
            }
            
            $image_ids = array();
            if($info->head_icon)
            {
                $image_ids[] = $info->head_icon;
            }
            
            if($info->company_logo)
            {
                $image_ids[] = $info->company_logo;
            }
            
            
            if($info->wx_code)
            {
                $image_ids[] = $info->wx_code;
            }
            
            if($info->company_album)
            {
                $comany_album = explode(',', $info->company_album);
                $image_ids = array_merge($comany_album,$image_ids);
            }
            
            if($info->project_album)
            {
                $project_album = explode(',', $info->project_album);
                $image_ids = array_merge($project_album,$image_ids);
            }
            $images = array();
            if($image_ids)
            {
                $images = $this->getImageTable()->getAdminImages($image_ids);
            }
            
            $this->breadcrumb = array(
                array(
                    'url' => '#',
                    'title' => '站点信息'
                ),
                array(
                    'url' =>  $this->plugin('url')->fromRoute('admin-user', array('action' => 'applicationList','cid'=>1)),
                    'title' => '申请列表'),
                array(
                    'url' => '',
                    'title' => '申请详细'
                )
            );
            $view = new ViewModel(array(
                'info' => $info,
                'images' => $images
            ));
            
            $view->setTemplate("admin/user/applyDetails");
           
            return  $this->setMenu($view,'user');
    }
    
    /**
     * 删除用户申请
     * 
     * @version 2015年7月30日 
     * @author liujun
     */
    public function deleteApplyAction()
    {
        $this->checkLogin('user_index');
        $id = $this->params('id');
        if($id)
        {
            $this->table = $this->getUserApplicationTable();
           return  $this->deleteDate();
        }
        die();
    }
    
    /**
     *更新用户审核资料状态
     * @version 2015年7月30日 
     * @author liujun
     */
    public function updateStatusApplyAction()
    {
        $id = $this->params('id');
        if($id)
        {
            $this->getUserApplicationTable()->updateData(array('status'=>3), array('id'=>$id));
            $this->showMessage('操作成功！');
        }
        die();
    }
    
    
    /**
     * 内容过滤
     *
     */
    protected function HtmlFilter($str){
        return addslashes(htmlspecialchars(trim($str), ENT_QUOTES));
    }
    
    /**
     * 获取微信临时素材图片
     * @version 2015年8月8日 
     * @author liujun
     */
    protected function generatePictures($access_token,$media_id)
    {
        if(!$access_token || !$media_id)
        {
            return false;
        }
        $file = new UploadfileApi();
        $file->setPath(APP_PATH.'/public/'.UPLOAD_PATH);
        $path = $file->path;
        $wx_api = new WxApi();
        $access_token = $wx_api->wxAccessToken();
        $file_name = date("His").'_'.mt_rand(1000, 9999).'.jpg';
        $wx_media_url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=$media_id";

        $res = file_put_contents($path.$file_name, file_get_contents($wx_media_url));

        if($res)
        {
            $set = array("filename"=>$file_name,'path'=>date("Ym/"),"timestamp"=>$this->getTime());
            $id = $this->getImageTable()->insertData($set);
            $image = array("id"=>$id,"path"=>date("Ym/").$file_name);
            return $image;
        }
        return false; 
    }


    /*
     * 公司查看
     *
     * */

    public function showAction()
    {
        $id = $this->params()->fromRoute('id');
        $user_info = $this->getUserTable()->getOne(array('id'=>$id));
        $info = $this->getCompanyTable()->getAll(array('user_id'=>$id,'delete'=>0));

        foreach($info['list'] as $v)
        {
            $logo = $this->getImageTable()->getOne(array('id'=>$v['image']));
            $city = $this->getRegionTable()->getOne(array('id'=>$v['region_id']));
            $v['city'] = $city['name'];
            $v['path'] = $logo['path'].$logo['filename'];
        }

        $this->breadcrumb = array(
            array(
                'url' =>  $this->plugin('url')->fromRoute('admin-user', array('action' => 'index')),
                'title' => '用户列表'),
            array(
                'url' => '',
                'title' => '公司列表'
            )
        );
        $view = new ViewModel(array(
            'info' => $info,
            'category' => $this->category(),
            'scale' => $this->scale(),
            'user_id' => $id,
            'user_info' => $user_info
        ));
        $view->setTemplate('admin/user/show');
        return $this->setMenu($view ,1);
    }


    /*
     * 添加公司
     * */
    public function joinAction()
    {
        $company = $_POST['company'];
        $id = $_POST['id'];
        $info = $this->getCompanyTable()->getOne(array('name'=>$company));
        if($info)
        {
            $back = $this->getCompanyTable()->updateData(array('user_id'=>$id),array('id'=>$info['id']));
            if($back)
            {
                echo 1;

            }else
            {
                echo 2;

            }
        }else{
            echo 3;
        }
        exit;
    }

    /*
     *
     * 解绑*/

    public function unblindAction()
    {
        $id = $_POST['id'];
        if($id)
        {
            $back = $this->getCompanyTable()->updateData(array('user_id'=>0),array('id'=>$id));
            if($back)
            {
                echo 1;

            }else
            {
                echo 2;

            }
        }else{
            echo 3;
        }
        exit;
    }



}