<?php
namespace Api\Controller;

use Api\Controller\Request\CardListWhereRequest;
use Zend\Db\Sql\Where;
use Api\Controller\Request\CardSubmitRequest;

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
        
        $action = $request->action?$request->action:1;
        $action_array = array(
            1,//修改、添加
            2,//预览
        );

        if (! in_array($action, $action_array)) {
            return STATUS_UNKNOWN;
        }
        
        if($action == 1){
            $preview = 0;
            $data = $this->getViewPageCarteTable()->getOne(array('id'=>$request->card->id,'preview'=>$preview));
        }else if($action == 2){
            $preview = 1;
            $data = $this->getViewPageCarteTable()->getOne(array('user_id'=>$this->getUserId(),'preview'=>$preview));
        }
        // 9.29补丁
        $show = explode(',',$request->card->show);
        //print_r($show);die;
        $show1 = $show;
           foreach($show as $k => $v){
                if($k == array_search('operatingItems', $show)){
                    unset($show[$k]);
                }
                
                if($k == array_search('project', $show)){
                    unset($show[$k]);
                }
                
                if($v == 'companyIntroduction'){
                    unset($show[$k]);    
                }
                
                if($v == 'description'){
                    unset($show[$k]);
                }
           }
           
        if(in_array('operatingItems', $show1) || in_array('project', $show1)){
             $show[] = 'project';
        }
        
        if(in_array('companyIntroduction', $show1) || in_array('description', $show1)){
             $show[] = 'description';
        }

        $show = implode(',', $show);
        //echo $show;die;
        //新版本要删除
        
        $list = array(
            'head_icon' => $request->card->id_image,
           'carte_name' => $request->card->cardName,
            'signature' => $request->card->signature,
         'englist_name' => $request->card->english_name?$request->card->english_name:'',
                   'qq' => is_array($request->card->qq) ? implode(",", $request->card->qq) : "",
                'email' => is_array($request->card->email) ? implode(",", $request->card->email) : "",
            'telephone' => is_array($request->card->telephone) ? implode(",", $request->card->telephone) : "",
           'carte_name' => $request->card->cardName,
              'preview' => $preview,
              'company' => $request->card->company,
                 'show' => $show,
           'en_company' => $request->card->enCompanyName,
             'position' => $request->card->job,
              'address' => $request->card->address->street,
            'timestamp' => $this->getTime(),
              'wx_code' => $request->card->weixinImage?$request->card->weixinImage:'',
        );  
        
        $pagelist = array(
          'description' => $request->card->job?$request->card->job:'',
              'user_id' => $this->getUserId(),
       'last_edit_time' => $this->getTime()
        );
        
        if ($action == 2 || ! $data) {
            $list['name'] = $request->card->name;
            $list['mobile'] = is_array($request->card->mobile) ? implode(",", $request->card->mobile) : "";
            $pagelist['title'] = $request->card->name?$request->card->name:'';
        }
        
        if($data){
            $card = $this->getCarteTable()->updateData($list, array('id'=>$data['c_id']));
            $card = $this->getPageTable()->updateData($pagelist, array('id' => $data['id']));
            $pageId = $data['id'];
        }else if(!$data){
            $id = $this->getCarteTable()->insertData($list);
            $pagelist['carte_id'] = $id;
            $pagelist['timestamp'] = $this->getTime();
            $pageId = $this->getPageTable()->insertData($pagelist);
            $u_id = $this->getUserId();
            $u_data = $this->getUserTable()->getOne(array('id'=>$u_id));
            if (!$u_data['page_id'] && $preview==0){
                $this->getUserTable()->updateData(array('page_id'=>$pageId,'name'=>$request->card->name), array('id'=>$u_id));
			}
			if (!$u_data['name'] && $preview==0){
			    $this->getUserTable()->updateData(array('name'=>$request->card->name), array('id'=>$u_id));
			}
        }else{
            return STATUS_UNKNOWN;
        }

        $response->id = $pageId;
        return $response;
    }
}







