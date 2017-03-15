<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use Core\System\UploadfileApi;
use Core\System\WxApi\WxApi;

class CardController extends CommonController
{

    /**
     * 名片列表
     *
     * @return multitype:
     * @version 2014-12-29   liujun
     */
    public function indexAction()
    {
        $this->checkLogin('user_index');
        $carte_head_icon_ids = array();
        $carte_head_icon = array();
        $carte_ids = array();
        $device_arr = array();
        $where = array();
        $page = $this->params()->fromRoute('page');
        $alert=$this->params()->fromRoute('other');
        $cid = $this->params()->fromRoute('cid');
        if($_POST && @$_POST['submit'])
        {
            $cid=$_POST['cid'];
        }
        $_SESSION['cid']=$cid;
        $keyword = $this->params()->fromRoute('keyword') ? trim($this->params()->fromRoute('keyword')) : '';
        $like = null;
        $this->seach = array(
            'name',
            'company',
            'mobile',
            'id'

        );



        if ((isset($_POST['submit']) && $_POST['keyword'] != '') || $keyword) {
            $keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : $keyword;
            if ($keyword && is_array($this->seach)) {
                foreach ($this->seach as $v) {
                    $like[$v] = $keyword;
                }
            }
        }


        $where = new Where();
        $where->equalTo('delete',0);
        $where->equalTo('preview',0);//不显示预览
        /*if($_POST)
        {
            $where->equalTo('name',$_POST['keyword']);
        }*/

       /* if($_POST['submit'] && $_POST['blind'])
        {
            if($_POST['blind']!==0)
            {
                $where->notEqualTo('status', 0);
            }
            else
            {
                $where->equalTo('status', 0);
            }
        }*/

        if ($_SESSION['cid'] == 0) {
            $where->equalTo('status', 0);
        } else {
            $where->notEqualTo('status', 0); // 未绑定的设备
        }
        if($alert){
            $where->equalTo('id',$alert);
        }
        $carte_info = $this->getCarteTable()->getAll($where, null, array(
            'id' => 'DESC'
        ), true, $page, PAGE_NUMBER, $like);
        foreach($carte_info['list'] as $v)
        {
            $page_info=$this->getViewPageCarteTable()->getOne(array('c_id'=>$v['id']));
            if($page_info)
            {
                $company_info = $this->getCompanyTable()->getOne(array('user_id'=>$page_info['user_id'],'delete'=>0));
                $v['company1'] = $company_info['name'];
            }

        }
        /*echo "<pre>";
        print_r($carte_info);die;*/
        if ($carte_info) {
            foreach ($carte_info['list'] as $v) {
                $carte_head_icon_ids[$v['id']] = $v['head_icon'];
                $device_id[] = $v['status'];
            }
            $device_info = array();
            if ($device_id)
            {
                $where = new Where();
                $where->in('device_id',$device_id );
                $device_info = $this->getDeviceTable()->fetchAll($where);
            }

            foreach ($device_info as $vv)
             {
                $device_arr[$vv['device_id']] = $vv['id'];
            }

            if (! empty($carte_head_icon_ids)) {
                $carte_head_icon = $this->getImageTable()->getAdminImages($carte_head_icon_ids);
            }
        }
        $this->breadcrumb = array(
            array(
                'url' => '#',
                'title' => '名片'
            ),
            array(
                'url' => '',
                'title' => '名片列表'
            )
        );


        $view = new ViewModel(array(
            'paginator' => $carte_info['paginator'],
            'condition' => array(
                'action' => $this->action,
                'cid' => $cid,
                'page' => $page,
                'keyword' => $keyword,
                'where' => $where
            ),
            'carte_info' => $carte_info['list'],
            'carte_head_icon' => $carte_head_icon,
            'device_arr' => $device_arr,
            'cid' => $cid,
            'page' => $page,
            'keyword' => $keyword,
            'where' => $where
        ));
        $view->setTemplate('admin/card/index');
        return $this->setMenu($view, 'user');
    }

    /**
     * 新增名片/编辑名片
     */
    public function addUserAction()
    {        
        $this->checkLogin('user_index');
        if (isset($_POST['submit'])) {
            $id = $_POST['carte_id'];
         
            //增加
            if($id){
                if($_POST['company'])
                {
                    $audit=$this->getCompanyTable()->getOne(array('name'=>$_POST['company'],'delete'=>0));
                    $car_id=$this->getCarteTable()->getOne(array('id'=>$id));
                    
                    if($audit['id'] == $car_id['company_id'] )
                    {  
                        if($car_id['company_status'] != 3){
                            $num =  $audit['stat_audit'] + 1;
                            $this->getCarteTable()->updateData(array('company_status' =>2),array('id'=>$id)); 
                            $this->getCompanyTable()->updateData(array('stat_audit' =>$num),array('id'=>$audit['id']));
                        }
                       
                    }else{
                        $num =  $audit['stat_audit'] + 1;
                        $this->getCarteTable()->updateData(array('company_status' =>2),array('id'=>$id));
                        $this->getCompanyTable()->updateData(array('stat_audit' =>$num),array('id'=>$audit['id']));
                    }
            
                }
            }else{
                if($_POST['company'])
                {
                  
                    $audit=$this->getCompanyTable()->getOne(array('name'=>$_POST['company'],'delete'=>0));
                    if($audit)
                    {
                        $num =  $audit['stat_audit'] + 1;
                        $this->getCompanyTable()->updateData(array('stat_audit' =>$num),array('id'=>$audit['id']));
                    }
            
                }
            }
            
            
            $province_id = $_POST['province_id'];
            $city_id = $_POST['city_id'];
            $county = isset($_POST['county']) ? $_POST['county'] : $city_id;
            
            $region_info = $this->encode($county, $city_id, $province_id);
            
            $region_info_arr = array();
            $street = $address = isset($_POST['street']) ? trim($_POST['street']) : '';
            
            if ($region_info) {
                $region_info_arr = $this->decode($region_info);
                $address = $this->getProvinceCityCountryName($region_info) . $street;
            }
            $company_album = isset($_POST['company_album']) ? implode(',',$_POST['company_album']) : '';
            $project_album = isset($_POST['project_album']) ? implode(',',$_POST['project_album']) : '';
            
         

            $data = array(
                'name' => trim($_POST['name']),
                'birthday' => $_POST['birthday'],
                'citizenship' => trim($_POST['citizenship']),
                'signature' => trim($_POST['signature']),
                'profile' => trim($_POST['profile']),
                'company' => isset($_POST['company']) ? $_POST['company'] : '',
                'company_id' => isset($audit['id']) ? $audit['id'] : '',
                'en_company' => trim($_POST['en_company']),
                'description' => trim($_POST['description']),
                'weixin_number' => $_POST['weixin'],
                'telephone' => $_POST['telephone'],
                'template' => $_POST['template'],
                'web_address' => $_POST['web_address'],
                'project' => trim($_POST['project']),
                'others' => $_POST['others'],
                'hobby' => implode(",", $_POST['hobby']),
                'position' => implode(",", $_POST['position']),
                'mobile' => implode(",", $_POST['mobile']),
                'wx_code' => $_POST['wx_code'],
                /* 'company_status'=>2, */
                //'erweima' => $_POST['wx_code'],
                'fax' => implode(",", $_POST['fax']),
                'qq' => implode(",", $_POST['qq']),
                'email' => implode(",", $_POST['email']),
                'weibo' => implode(",", $_POST['weibo']),
                'tianmao_shop_url' => isset($_POST['tianmao_shop_url']) ? $_POST['tianmao_shop_url'] : '',
                'jingdong_shop_url' => isset($_POST['jingdong_shop_url']) ? $_POST['jingdong_shop_url'] : '',
                'taobao_shop_url' => isset($_POST['taobao_shop_url']) ? $_POST['taobao_shop_url'] : '',
                'company_logo' => isset($_POST['company_logo']) ? $_POST['company_logo'] : '',
                'industry' => isset($_POST['industry']) ? $_POST['industry'] : '',
                'project_album' => $project_album,
                'company_album' => $company_album,
                'address' => $address,
                'region_id' => $county,
                'region_info' => $region_info,
                'street' => $street,
                'longitude' => isset($_POST['longitude']) ? $_POST['longitude'] : "",
                'latitude' => isset($_POST['latitude']) ? $_POST['latitude'] : "",
                'category_id' => isset($_POST['category']) ? $_POST['category'] : 1,
                'timestamp' => date('Y-m-d H:i:s', time())
            );
          
            $pattern_arr = array(
                'mobile' => '/^(1[\d]{10})$/',
                'qq' => '/^([\d]{5,10})$/',
                'email' => '/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/'
            );

            // 验证输入信息
            if (mb_strlen($data['name'], 'utf-8') < 2) {
                $this->showMessage("请输入姓名不能少于2个字符！");
            }

            /*if ($data['longitude'] == 0 && $data['latitude'] == 0) {
                $this->showMessage("请输入坐标！");
            }*/

            foreach (explode(",", $data['mobile']) as $v_mobile) {
                if (! preg_match($pattern_arr['mobile'], $v_mobile)) {
                    $this->showMessage("请输入一个有效移动电话！");
                }
            }

            if (isset($_POST['image']) && $_POST['image']) {

                if (isset($_POST['id']) && $_POST['id']) { // 编辑查询名片信息
                    $carte_info = $this->getCarteTable()->getOne(array(
                        'id' => $_POST['id']
                    ));
                }

                $page_info = $this->getPageTable()->getOne(array("carte_id"=>$_POST['id']));

                if ((isset($carte_info->head_icon) && ($carte_info->head_icon != $_POST['image'])) || ! $_POST['id'] || !$page_info['page_id']) { // 新增，或编辑时ID有变化头像ID提交到微信
                    $data['head_icon'] = $_POST['image'];
                    $image = $this->getImageTable()->getOne(array(
                        'id' => $data['head_icon']
                    ));
                    file_get_contents(HTTP.ROOT_PATH.UPLOAD_PATH.'thumb/200X200X4/'.$image->path.$image->filename);

                    //9.7

                    $wxApi = new wxApi();
                    $wxImgUrl = $wxApi->wxMaterialAdd(('thumb/200X200X4/'.$image->path . $image->filename));

                    if ($wxImgUrl['errcode'] == 0) {
                        $data['wx_img_url'] = $wxImgUrl['data']['pic_url'];
                    }
                    else
                    {
                        $this->showMessage('用户头像上传失败：错误代码'.$wxImgUrl['errcode']);
                    }
                }
                else
                {
                    $data['wx_img_url'] = $page_info['icon_url'];
                }
            }
            else
            {
                $this->showMessage('用户头像不能为空！');
            }

            // 调用参数=====================================================================
            $position = explode(",", $data['position']);

            // 保存文件路径-----------------------------------------------------------------
            $write_path = SAVE_TEMPLATE_PATH;

            if (! is_dir(SAVE_TEMPLATE_PATH)) {
                mkdir(SAVE_TEMPLATE_PATH, 0775, true);
            }
            // ----------------------------------------------------------------------------

            // vcf资料数组------------------------------------------------------------------
            $vard_data = array(
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'email' => $data['email'],
                'web_address' => $data['web_address'],
                'company' => $data['company'],
                'position' => $data['position'],
                'address' => $data['street'],
                'province' => isset($region_info_arr['province']['name']) ? $region_info_arr['province']['name'] : '',
                'city' => isset($region_info_arr['city']['name']) ? $region_info_arr['city']['name'] : '',
                'county' => isset($region_info_arr['county']['name']) ? $region_info_arr['county']['name'] : ''
            );

            // ----------------------------------------------------------------------------

            if (isset($_POST['id']) && $_POST['id'])
            { // 如果是更新数据，用回原来生成的静态文件
                $id = $_POST['id'];
                $this->getCarteTable()->update($data, array(
                    'id' => $id
                ));
                $carte_info = $this->getCarteTable()->getOne(array(
                    'id' => $id
                ));
                $html_name = $carte_info['html'] ? $carte_info['html'] : time() . mt_rand(100, 999);
                
                $file_name = $html_name . '.html';
               
                // 修改原生成vcf
                $vcf_name = $carte_info['vcard'];

                if($vcf_name && $carte_info['qr_code']){
                    $vcf_info = $this->generationVcf($vard_data, $vcf_name);


                    // 修改原vcf二维码
                    // $qr_code_info
                    // =
                    // json_decode($carte_info['qr_code']);
                    $vcf_qr_code_id = $carte_info['qr_code'];
                    $vcf_qr_code_img = $this->getImageTable()->getOne(array(
                        'id' => $vcf_qr_code_id
                    ));
                    $vcf_qr_code_path = QRCODE_PATH . mb_substr($vcf_qr_code_img['path'], 7) . $vcf_qr_code_img['filename'];
                    $vcf = $this->generationQRcode($vcf_info['str'], $vcf_qr_code_path);
                }
                else
                {
                    $vcf_info = $this->generationVcf($vard_data); // 生成vcf名片
                    $vcf = $this->generationQRcode($vcf_info['str']);
                    $vcf_qr_code_id = isset($vcf['id']) ? $vcf['id'] : 0;
                }
                // 查询page_id
                $page_info = $this->getPageTable()->getOne(array(
                    'carte_id' => $id
                ));
               
                $wx_page_url = HTTP . $this->plugin('url')->fromRoute('index', array('controller' => 'user', 'action' => 'pageDetails', 'id' => $page_info['id']));
               
                $wxPageData = array(
                    'title' => mb_substr($data['name'], 0, 6, 'utf-8'),
                    'description' => "快摇名片",
                    // 'comment'=>mb_substr($data['company'],0,15,'utf-8'),
//                     'page_url' => HTTP . ROOT_PATH . 'static_html/' . $file_name,
                    'page_url' => $wx_page_url,
                    'icon_url' => $data['wx_img_url'],
                    'page_id' => (int) $page_info['page_id']
                );
                
                $wxPage = new WxApi();

                if($page_info['page_id'])
                {
                    $res = $wxPage->wxPageUpdate(json_encode($wxPageData));
                }
                else
                {
                    $res = $wxPage->wxPageAdd(json_encode($wxPageData));

                    if($res['errcode'] == 0)
                     {
                        $wxPageData['page_id']  =  $res['data']['page_id'];
                    }
                }

//                 if ($res['errcode'] == 0)
//                 {
                    $this->getPageTable()->update($wxPageData, array(
                        'carte_id' => $id
                    ));
                    $data['html'] = $html_name;
                    $data['qr_code'] = $vcf_qr_code_id;
                    $data['vcard'] = $vcf_info['vcf_fname'];
                    $this->getCarteTable()->update($data,array('id'=>$id)); // 更新名片数据
//                 }
            } else {

                $main_mobile = isset($_POST['main_mobile']) ? $_POST['main_mobile'] : '';
                if ($main_mobile) { // 有主帐号手机
                    if (! preg_match($pattern_arr['mobile'], $main_mobile)) {
                        $this->showMessage("请输入一个有效移动电话！");
                    }
                    $where = array(
                        'mobile' => $main_mobile
                    );
                } else {
                    $where = array(
                        'mobile' => $_POST['mobile'][0]
                    );
                }

                $main_user_info = $this->getUserTable()->getOne($where); // 查询是否有些主帐号
                
                if ($main_user_info) {
                    $user_id = $main_user_info->id;
                }
                else
                {//新增用户
                    $user_id = $this->getUserTable()->insertData(array(
                        'mobile' => $where['mobile'],
                        'timestamp' => $this->getTime(),
                        'name' => $data['name'],
                        'password' => md5('kuaiyao'),
                    ));
                    $this->statOporation(1,1);
                    /* $setup_info = $this->getSetupTable()->getOne(array('id'=>1));

                    if($setup_info->value>0)
                    {
                        for ($i=0;$i<$setup_info->value;$i++)
                        {
                            $code = $this->getApiController()->makeCode(6, 6);//生成6位随机字母加数字为推荐码
                            $this->getInvitationCodeTable()->insert(array('user_id'=>$user_id,'timestamp'=>$this->getTime(),'code'=>$code));//生成推荐码
                        }
                    } */

                  }
                /* $page_info = $this->getPageTable()->getOne(array(
                    'carte_id' => $id
                )); */
                
                $html_name = time() . mt_rand(100, 999);
                $file_name = $html_name . '.html';
                $vcf_info = $this->generationVcf($vard_data); // 生成vcf名片
                $vcf = $this->generationQRcode($vcf_info['str']); // ***'http://'.HTTP.ROOT_PATH.UPLOAD_PATH.VCARD_DIR.$vcf_path
               
                
               /*  $wx_page_url = HTTP . $this->plugin('url')->fromRoute('index', array('controller' => 'user', 'action' => 'pageDetails', 'id' => $page_info['id']));
                
                $wxPageData = array(
                    'title' => mb_substr($data['name'], 0, 6, 'utf-8'),
                    'description' => "快摇名片",
                    // 'comment'=>mb_substr($data['company'],0,15,'utf-8'),
//                     'page_url' => HTTP . ROOT_PATH . 'static_html/' . $file_name,
                    'page_url' => $wx_page_url,
                    'icon_url' => isset($data['wx_img_url'])?$data['wx_img_url']:'0',
                );
               
                $wxPage = new WxApi();
                $res = $wxPage->wxPageAdd(json_encode($wxPageData)); */

                $data['html'] = $html_name;
                $data['qr_code'] = $vcf['id'];
                $data['vcard'] = $vcf_info['vcf_fname'];
                $data['timestamp'] = $this->getTime();
                $id = $this->getCarteTable()->insertData($data); // 插入数据后返回名片ID
                /*if($id)
                {
                    $this->getCompanyTable()->updateKey();
                }*/

                $wxPageData['carte_id'] = $id;
                $wxPageData['user_id'] = $user_id;

                $page_id = $this->getPageTable()->insertData($wxPageData);
                
                $wx_page_url_update = HTTP . $this->plugin('url')->fromRoute('index', array('controller' => 'user', 'action' => 'pageDetails', 'id' => $page_id));
                
                $wxPageData = array(
                    'title' => mb_substr($data['name'], 0, 6, 'utf-8'),
                    'description' => "快摇名片",
                    // 'comment'=>mb_substr($data['company'],0,15,'utf-8'),
                //                     'page_url' => HTTP . ROOT_PATH . 'static_html/' . $file_name,
                    'page_url' => $wx_page_url_update,
                    'icon_url' => isset($data['wx_img_url'])?$data['wx_img_url']:'0',
                );
               
                /*var_dump(json_encode($wxPageData));die;*/
                $wxPage = new WxApi();
                $res = $wxPage->wxPageAdd(json_encode($wxPageData));
                
                $wxPageData = array(
                    'page_id'=> $res['data']['page_id'],
                    'title' => mb_substr($data['name'], 0, 6, 'utf-8'),
                    'description' => "快摇名片",
                    // 'comment'=>mb_substr($data['company'],0,15,'utf-8'),
                    //                     'page_url' => HTTP . ROOT_PATH . 'static_html/' . $file_name,
                    'page_url' => $wx_page_url_update,
                    'icon_url' => isset($data['wx_img_url'])?$data['wx_img_url']:'0',
                );
                
                $update_id = $this->getPageTable()->updateData($wxPageData, array('id'=>$page_id)); 
                
                if(!$main_user_info)
                {//新增用户则更新主面片
                    $this->getUserTable()->updateData(array('page_id'=>$page_id), array('id'=>$user_id));//更新主名片
                }
               

                if ($res['errcode'] != 0)
                {
                    $this->showMessage('名片生成失败：错误代码'.$res['errcode']);
                }

            }

            if(isset($_POST['apply_id']) && $_POST['apply_id'])
            {//如果是用户审请提交过来的更新申请表为已审核通过状态
                $this->getUserApplicationTable()->updateData(array('status'=>2), array('id'=>$_POST['apply_id']));
            }

            // 暂时屏蔽生成静态
//             $read_url = $this->plugin('url')->fromRoute('admin-card', array(
//                 'action' => 'preview',
//                 'id' => $id
//             ));
//             $read_data = file_get_contents(HTTP . $read_url);

//             file_put_contents($write_path . $file_name, $read_data);

 /*
            // 绑定设备-----------------------------------------------------------------------------------------------------
            $device_id = isset($_POST['device_id']) ? trim($_POST['device_id']) : '';
            $device_info = $this->getDeviceTable()->getOne(array(
                'device_id' => $device_id
            ));
            if ($device_id && ! empty($device_info) && $wxPageData['page_id'])
             {
                $bindDeviceData = json_encode(array(
                    'device_identifier' => array(
                        'device_id' => (int) $device_info->device_id,
                        'uuid' => $device_info->uuid,
                        'major' => $device_info->major,
                        'minor' => $device_info->minor
                    ),
                    'page_ids' => array(
                        (int) $wxPageData['page_id']
                    ),
                    'bind' => 1, // 0解除
                                 // 1关联
                    'append' => 0
                )); // 0覆盖
                    // 1新增

                $wxBindDevice = new WxApi();
                $res = $wxBindDevice->wxDeviceBindPage($bindDeviceData);
                if ($res['errcode'] == 0) {
                    if ($device_info['page_ids']) {
                        $page_ids_arr = explode(",", $device_info['page_ids']);
                        $page_ids_arr[] = $wxPageData['page_id'];
                    } else {
                        $page_ids_arr = $wxPageData['page_id'];
                    }

                    if ($device_info['carte_ids']) {
                        $carte_ids_arr = explode(",", $device_info['carte_ids']);
                        $carte_ids_arr[] = $id;
                    } else {
                        $carte_ids_arr = $id;
                    }

                    $device_update = array(
                        'page_ids' => $page_ids_arr,
                        'carte_ids' => $carte_ids_arr
                    );
                    $this->getCarteTable()->update(array(
                        'status' => $device_id
                    ), array(
                        'id' => $id
                    )); // 更新名片绑定状态
                    $this->getDeviceTable()->update($device_update, array(
                        'device_id' => $device_id
                    ));
                }
            } */
            return $this->redirect()->toRoute('admin-card');
        }

        // 判断是否进入编辑页面             看编辑卡片操作 9.7
        $id = $this->params()->fromRoute('id');
        if ($id) {

            $info = $this->getCarteTable()->getOne(array(
                'id' => $id
            ));

            $page_id=$this->getViewPageCarteTable()->getOne(array('mobile'=>$info['mobile']));
//             print_r($info);die;
           /*  if ($info->region_info) {
                $region_info_arr = $this->decode($info->region_info);
                $info->province_id = $region_info_arr['province']['id'];
                $info->city_id = $region_info_arr['city']['id'];
                $info->county = $region_info_arr['county']['id'];
            } */

            $image_ids = array();
            $image_ids[] = $info['head_icon'];
            $image_ids[] = $info['wx_code'];
            $image_ids[] = $info['company_logo'];

            if($info['project_album'])
            {
                $image_ids = array_merge($image_ids,explode(',', $info['project_album']));
            }
            if($info['company_album'])
            {
                $image_ids = array_merge($image_ids,explode(',', $info['company_album']));
            }



            $images = $this->getImageTable()->getAdminImages($image_ids);
            $city= $this->getRegionTable()->getOne(array('id' => $info['region_id']));
            $info->province_id = $city['parent_id'];
            $info->city_id = $city['id'];
           
            $view = new ViewModel(array(
                'info' => $info,
                'category' => $this->category(),
                'images' => $images,
                'id'=>isset($page_id['id']) ? $page_id['id'] : '',
            ));
        } else {
            $view = new ViewModel();
            $view = new ViewModel(array(
                'category' => $this->category(),
            ));
        }
        $this->breadcrumb = array(
            array(
                'url' => '#',
                'title' => '名片'
            ),
            array(
                'url' => $this->plugin('url')->fromRoute('admin-card', array(
                    'action' => 'index'
                )),
                'title' => '名片列表'
            ),
            array(
                'url' => '',
                'title' => '名片详细'
            )
        );

        $view->setTemplate('admin/card/userDetails');
        return $this->setMenu($view, 'card');
    }

    /**
     * 加载名片动态数据页面
     */
    public function previewAction()
    {
        $id = $this->params()->fromRoute('id');
        $info = $this->getCarteTable()->getOne(array(
            'id' => $id
        ));
        // $qr_code_info
        // =
        // json_decode($info['qr_code']);

        $head_icon = $this->getImageTable()->getAdminImages(array(
            $info['head_icon'],
            $info['qr_code'],
            $info['wx_code']
        ));

        $view = new ViewModel(array(
            'info' => $info,
            'head_icon' => $head_icon,
            'vcf_id' => $info['qr_code']
        ));
        $view->setTemplate("admin/mobileTemplate/index");
        return $view;
    }

    /**
     * 浏览静态
     */
    public function staticAction()
    {
        $id = $this->params()->fromRoute('id');

        $view = new ViewModel();
        $view->setTemplate("admin/static_html/" . $id);
        return $view;
    }

    /**
     * 删除一个名片
     *
     * @version 2014-12-29  liujun
     */
    public function delUserAction()
    {
        $this->checkLogin('user_delete');
        $id = (int) $this->params()->fromRoute('id');

        $page_info = $this->getPageTable()->update(array(
            'delete' => 1
        ), array(
            'carte_id' => $id
        ));

        $this->getCarteTable()->update(array(
            'delete' => 1
        ), array(
            'id' => $id
        ));
        /*
         * if
         * ($carte_info->status
         * !=
         * 0)
         * {
         * $this->showMessage('请解除绑定后再删除');
         * exit();
         * }
         * else
         * {
         * $page_info
         * =
         * $this->getPageTable()->getOne(array(
         * 'carte_id'
         * =>
         * $id
         * ));
         * $page_id['page_ids'][]
         * =
         * (int)
         * $page_info->page_id;
         * $jsonData
         * =
         * json_encode($page_id);
         * $wxApi
         * =
         * new
         * WxApi();
         * $res
         * =
         * $wxApi->wxPageDelete($jsonData);
         * if
         * ($res['errcode']
         * ==
         * 0)
         * {
         * $this->getPageTable()->update(array(
         * 'delete'
         * =>
         * 1
         * ),
         * array(
         * 'page_id'
         * =>
         * $page_id['page_ids']
         * ));
         * $this->getCarteTable()->update(array(
         * 'delete'
         * =>
         * 1
         * ),
         * array(
         * 'id'
         * =>
         * $id
         * ));
         * }
         */
        $this->showMessage('页面删除成功！');
        return $this->redirect()->toRoute('admin-card');
        // }
    }

    /**
     * 获取微信页面分享密钥参数
     *
     * @version
     *          2015-6-15
     *
     *
     *          chenzy
     */
    function getJssdkAction()
    {
        $data = $this->getJssdk();
        if ($data) {
            exit(json_encode($data));
        } else {
            exit(0);
        }
    }

    /**
     * 名片列表绑定/解绑设备
     *
     * @version 2015-6-17 chenzy
     */
    public function carteListBindDeviceAction()
    {
        //print_r($_POST);die;Array ( [id] => 3002 [carte_id] => 243 [bind] => 0 [submit] => 解除绑定 )
        if (isset($_POST['submit']) && isset($_POST['id']) && isset($_POST['carte_id'])) {
            $page_info = $this->getPageTable()->getOne(array(
                'carte_id' => $_POST['carte_id']
            ));

            $device_info = $this->getDeviceTable()->getOne(array(
                'id' => $_POST['id']
            ));

            if (empty($device_info)) {
                $this->showMessage('无此设备!!');
                exit();
            }

            if (! $page_info) {
                $this->showMessage("当前页面未生成！");
            }

            if ($device_info->user_id)
            { // 如果已有用户绑定检查是当前用户是否跟前一个用户相同
                if ($device_info->user_id != $page_info->user_id) {
                    return $this->showMessage('绑定失败，该设备已被其它用户绑定！');
                }
            }

            $jsonData = json_encode(array(
                'device_identifier' => array(
                    'device_id' => (int) $device_info['device_id']
                ),
                'page_ids' => array(
                    (int) $page_info['page_id']
                ),
                'bind' => (int) $_POST['bind'], // 0解除
                                                // 1关联
                'append' => 1
            )); // 0覆盖
                // 1新增

            $wxApi = new WxApi();
            $res = $wxApi->wxDeviceBindPage($jsonData);

            //print_r($_POST['carte_id']);die;

            if ($res['errcode'] == 0) {
                if ($device_info['carte_ids']) {
                    $carte_ids = explode(',', $device_info['carte_ids']);
                    if (! in_array($_POST['carte_id'], $carte_ids)) { // 如果没有绑定追加ID在后面
                        $carte_ids[] = $_POST['carte_id'];
                    } else {
                        if ($_POST['bind'] == 0) {
                            foreach ($carte_ids as $k => $v) {
                                if ($v == $_POST['carte_id']) {
                                    unset($carte_ids[$k]); // 解绑则删除对应的id
                                    break;
                                }
                            }
                        }
                    }
                } else {
                    $carte_ids = array(
                        $_POST['carte_id']
                    );
                }
                if ($device_info['page_ids']) {

                    $page_ids = explode(',', $device_info['page_ids']);
                    if (! in_array($page_info['page_id'], $page_ids)) { // 如果没有绑定追加ID在后面
                        $page_ids[] = $page_info['page_id'];
                    } else {
                        if ($_POST['bind'] == 0) {
                            foreach ($page_ids as $k => $v) {
                                if ($v == $page_info['page_id']) {
                                    unset($page_ids[$k]); // 解绑则删除对应的id
                                    break;
                                }
                            }
                        }
                    }
                } else {
                    $page_ids = array(
                        $page_info['page_id']
                    );

/*                     $code = $this->getInvitationCodeTable()->getOne(array(
                        'device_id' => $device_info['id']
                    ));
                    if ($code && ! $code->user_id) { // 此设备没有绑定用户才送推荐码
                        $this->getInvitationCodeTable()->update(array(
                            'user_id' => $page_info['user_id']
                        ), array(
                            'device_id' => $device_info['id']
                        ));
                    } */
                }

                $this->getDeviceTable()->update(array(
                    'carte_ids' => implode(',', $carte_ids),
                    'page_ids' => implode(',', $page_ids),
                    'user_id' => $page_info['user_id']
                ), array(
                    'id' => $_POST['id']
                ));
                if ($_POST['bind'] == 0) {
                    $this->getCarteTable()->update(array(
                        'status' => 0
                    ), array(
                        'id' => $_POST['carte_id']
                    ));
                    $device = $this->getDeviceTable()->getOne(array('id'=>$_POST['id']));
                    if(!$device->page_id)
                    {//页面没有pageID则删除些用户绑定的设备
                        $this->getDeviceTable()->updateData(array('user_id'=>0), array('id'=>$_POST['id']));
                    }
                } else {
                    $this->getCarteTable()->update(array(
                        'status' => $device_info['device_id']
                    ), array(
                        'id' => $_POST['carte_id']
                    ));
                }
            } else {
                $this->showMessage("绑定设备失败!错误代码({$res['errcode']})");
            }
            return $this->redirect()->toRoute('admin-card', array(
                'cid' => 1
            ));
        }
        $this->showMessage('参数错误');
        exit();
    }

    /*
     * 撤销管理
     *
     * */
    /*public function cancelAction()
    {*/
       /* $id = $_POST['id'];
        $company = $_POST['company'];
        $page_info = $this->getPageTable()->getOne(array('carte_id' => $id));
        $company_info=$this->getCompanyTable()->getOne(array('name'=>trim($company)));
        print_r($company_info);die;
        if($id && $company_info)
        {
            $set = array('user_id'=>0);
            $where = array('id'=>$company_info['id']);
            $back=$this->getCompanyTable()->updateData($set,$where);
            if($back){
                echo 1;
            }
        }else{
            echo 0;
        }*/
       /* $id = $_POST['id'];*/
        /*$company = $_POST['company'];*/
       /* $page_info = $this->getPageTable()->getOne(array('carte_id' => $id));


        var_dump($page_info);
        $company_info=$this->getCompanyTable()->getOne(array('name'=>trim($company)));
        var_dump($company_info);*/
       /* if ($company) {
            $back = $this->getCompanyTable()->updateData(array('user_id'=>0),array('name'=>$company));
            if($back)
            {
                echo 1;
            }
            else
            {
                echo 0;
            }
        }

        exit;
    }*/
    /*
     *
     * 设置管理人
     * */
   /* public function setupAction()
    {
        $id = $_POST['id'];
        $company = $_POST['company'];
        $page_info = $this->getPageTable()->getOne(array('carte_id' => $id));
        $company_info=$this->getCompanyTable()->getOne(array('name'=>trim($company)));
        $company_id = $this->getCompanyTable()->getOne(array('user_id'=>$page_info['user_id']));
        if ($company_info && $page_info && $page_info['user_id']) {
            if(!$company_id){
                $back = $this->getCompanyTable()->updateData(array('user_id'=>$page_info['user_id']),array('id'=>$company_info['id']));
            }

            if($back)
            {
                echo 1;
            }
            else
            {
                echo 0;
            }
        }*/
        
//         $info = $this->getCompanyTable()->getOne(array('name'=>trim($company)));
//         if($info)
//         {
//             $back=$this->getCarteTable()->updateData(array('company_id'=>$info['id']),array('id'=>$id));
//             if($back)
//             {
//                 echo 1;
//             }
//             else
//             {
//                 echo 0;
//                 exit;
//             }
//         }
//         else
//         {
//             echo 0;
//         }
    /*    exit;

    }*/


}