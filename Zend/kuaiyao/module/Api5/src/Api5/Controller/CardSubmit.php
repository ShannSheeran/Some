<?php
namespace Api5\Controller;


use Zend\Db\Sql\Where;
use Api5\Controller\Request\CardSubmitRequest;
use Core\System\WxApi\WxApi;
use Zend\XmlRpc\Value\String;

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
        $action = $request->action ? $request->action : 1;
        $action_array = array(
            1,//修改、添加
            3
        );

        if (! in_array($action, $action_array)) {
            return STATUS_UNKNOWN;
        }
        
        if ($action == 1) {
            $data = $this->getViewPageCarteTable()->getOne(array(
                'id' => $request->card->id
            ));
            $card = $this->getCarteTable()->getOne(array(
                'id' => $data['c_id']
            ));
           
            $company_data = $this->getCompanyTable()->getOne(array('id'=>$request->card->company->id));
         
            if($card['company_id'] == $request->card->company->id){
                $num =  $company_data['stat_audit'];
                $this->getCompanyTable()->update(array('stat_audit' => $num),array('id'=>$request->card->company->id));
                /* $this->getViewPageCarteTable()->update(array('card_status' => 2),array('id' => $request->card->id)); */
            }else{
                $num =  $company_data['stat_audit']+1 ;
                $this->getCompanyTable()->update(array('stat_audit' => $num),array('id'=>$request->card->company->id));
                /* $this->getViewPageCarteTable()->update(array('card_status' => 2),array('id' => $request->card->id)); */
            }
            $company_status = 2;
            if ($card['company_id'] == $request->card->company->id) {
                $company_status = $card['company_status'];
            } else {
                $company_status = 2;
            }
            $style_id = $card['style_id'];
            $bg_image = $card['bg_image'];
        }
        if ($action == 3) {
            
            $card_data = $this->getViewPageCarteTable()->getOne(array(
                'id' => $request->card->id
            ));
            if ($card_data) {
               $style_id  = $request->card->style->id ? $request->card->style->id : 0;
               $bg_image  = $request->card->style->image->id ? $request->card->style->image->id : 0;
                $list = array(
                    'bg_image' => $bg_image ? $bg_image : 0,
                    'style_id' => $style_id ? $style_id : 0
                );
                $card = $this->getCarteTable()->updateData($list, array(
                    'id' => $card_data['c_id'],
                ));
            } else {
                return STATUS_NODATA;
            }
            return $response;
        }

       $mobiles = $request->card->mobile? implode(',' , $request->card->mobile) : ''; 
       $region_info = $this->getRegionInfoArray($request->card->address->regionId);
        $list = array(
           'carte_name' => $request->card->cardName,
               'mobile' => $mobiles,
         'englist_name' => $request->card->englishName?$request->card->englishName:'',
            'head_icon' => $request->card->image->id ? $request->card->image->id:'',
              'wx_code' => $request->card->wxImage->id?$request->card->wxImage->id:'',
            'signature' => $request->card->signature,
                   'qq' => is_array($request->card->qq) ? implode(",", $request->card->qq) : "",
                'email' => is_array($request->card->email) ? implode(",", $request->card->email) : "",
             'position' => $request->card->job,
           'company_id' => $request->card->company->id?$request->card->company->id:'',
              'company' => $request->card->company->name,
            'region_id'  => $request->card->address->regionId,
            'region_info' =>  $region_info['region_info'],
            'category_id' => $request->card->categoryId,
            'company_status' => $company_status,
            'timestamp' => $this->getTime(),
        ); 
        $pagelist = array(
          'description' => $request->card->job?$request->card->job:'',
              'user_id' => $this->getUserId(),
       'last_edit_time' => $this->getTime()
        );
        if(!$list['head_icon'])
        {
            $response->description = '请选择头像后在提交!';
            $response->status = '9000';
            return $response;
        }
        
         if((isset($data->head_icon) && $data->head_icon != $list['head_icon']) || !isset($data->data->head_icon))
        {//新增，或编辑改变头像更新微信API端头像信息
            $image = $this->getImageTable()->getOne(array(
                'id' => $list['head_icon']
            )); 

            file_get_contents(HTTP.ROOT_PATH.UPLOAD_PATH.'thumb/200X200X4/'.$image->path.$image->filename);          
            $wxApi = new WxApi();
            $wxImgUrl = $wxApi->wxMaterialAdd(('thumb/200X200X4/'.$image->path . $image->filename));

            if ($wxImgUrl['errcode'] == 0) {
                $list['wx_img_url'] = $wxImgUrl['data']['pic_url'];
                $pagelist['icon_url'] = $list['wx_img_url']; 
            }
            else
            {
                return STATUS_UNKNOWN;
            }
        }
         
        
         
        if($data){   
            $wx_page_url = WX_PAGE_URL.'user/pageDetails/'.$data['id'].'.html';
            $page_info = $this->getPageTable()->getOne(array('id'=>$data['id']));
            $wxPageData = array(
                    'title' => mb_substr($data['name'], 0, 6, 'utf-8'),
                    'description' => "快摇名片",
                    'page_url' => $wx_page_url,
                     'icon_url' => $list['wx_img_url'], 
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
                        $pagelist['page_id'] = $res['data']['page_id'];
                    }
                }
            
            $card = $this->getCarteTable()->updateData($list, array('id'=>$data['c_id']));
            $card = $this->getPageTable()->updateData($pagelist, array('id' => $data['id']));
            $pageId = $data['id'];
        }
        elseif(!$data)
        {
            $list['name'] = $request->card->name;
            $list['mobile'] = $mobiles;
            $id = $this->getCarteTable()->insertData($list);
          /*   $id = $this->getCompanyTable()->insertData($category_id); */
            $pagelist['carte_id'] = $id;
            $pagelist['timestamp'] = $this->getTime();
            $pageId = $this->getPageTable()->insertData($pagelist);
            $wx_page_url = WX_PAGE_URL.'user/pageDetails/'.$pageId.'.html';
            $wxPageData = array(
                'title' => mb_substr($list['name'], 0, 6, 'utf-8'),
                'description' => "快摇名片",
                'page_url' => $wx_page_url,
                 'icon_url' => $list['wx_img_url'] 
            );
            
            $wxPage = new WxApi();
            $res = $wxPage->wxPageAdd(json_encode($wxPageData));
            
             $this->getPageTable()->updateData(array('page_id'=>$res['data']['page_id'],'page_url'=>$wx_page_url), array('id'=>$pageId));
          

            $u_id = $this->getUserId();
            $u_data = $this->getUserTable()->getOne(array('id'=>$u_id));
            if (!$u_data['page_id']){
                $this->getUserTable()->updateData(array('page_id'=>$pageId,'name'=>$request->card->name), array('id'=>$u_id));
			}
			if (!$u_data['name']){
			    $this->getUserTable()->updateData(array('name'=>$request->card->name), array('id'=>$u_id));
			}
        }else{
            return STATUS_UNKNOWN;
        }

        $response->id = $pageId;
        return $response;
    }
}







