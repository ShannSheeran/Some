<?php
namespace Api5\Controller;

use Api5\Controller\Request\CardListWhereRequest;
use Zend\Db\Sql\Where;

/**
 * 人脉圈列表
 */
class MessageList extends CommonController
{

    public $user_list;

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $user_id=$request->id;

        //传id
       if($user_id)
        {
            $chat_where=new where();
            $chat_where->equalTo('delete', "0");
            $chat_where->equalTo('user_id',$user_id);
            $chat = $this->getAll($this->getChatTable(), $chat_where);  // 人脉圈数据
            /* $chat = $this->getChatTable()->fetchAll($chat_where); */
            $chat_list = array();
            $chat_ima = array();
            $array = array();
            foreach ($chat['list'] as $val) {
                $chat_comment_where = new Where();
                $chat_comment_where->equalTo('chat_id', $val['id'])->equalTo('delete', DELETE_FALSE)/* ->equalTo('user_id', $user_id) */;
                $comment = $this->getViewChatCommentTable()->getAll($chat_comment_where, null, array(
                    'id' => 'asc'
                ), true, 1, 9999); // 评论数据
                $chat_peaiser_where = new Where();
                $chat_peaiser_where->equalTo('chat_id', $val['id'])/*  ->equalTo('user_id', $user_id)  */;
                $peaiser = $this->getViewChatPraiseTable()->getAll($chat_peaiser_where, null, array(
                    'id' => 'asc'
                ), true, 1, 10); // 点赞数据             
                $images = array();
                $chatPraiser = array();
                $chatComment = array();
                if ($val['images']) {
                    $chat_ima = explode(',', $val['images']);
                    foreach ($chat_ima as $ima_id) {
                        $ima = $this->getImageTable()->getOne(array('id' => $ima_id)); // 图片的shuju
                        $images[]['image'] = array(
                            'id' => $ima['id'],
                            'path' => $ima['path'] . $ima['filename']
                        );
                    }
                }
                $card = $this->getCardDetails($val['user_id']);
                foreach ($peaiser['list'] as $valpea) {
                    $name = $this->getPageTable()->getOne(array(
                        'id' => $valpea['page_id']
                    ));

                    $user = $this->getUserTable()->getOne(array('id'=>$name['user_id']));
                     $chatPraiser[]['user'] = array(
                        'id' => $valpea['user_id'],
                        'name' => $user['name'],

                    ); 
                }
                //评论列表信息

                foreach ($comment['list'] as $valcom) {


                    $name = $this->getPageTable()->getOne(array(
                        'id' => $valcom['page_id']
                    ));

                    $user = $this->getUserTable()->getOne(array('id'=>$name['user_id']));
                    //自己
                    $carte=$this->getCarteTable()->getOne(array('id'=>$name['carte_id']));
                    $img=$this->getImageTable()->getOne(array('id'=>$carte['head_icon']));

                    $to_name = $this->getPageTable()->getOne(array(
                        'id' => $valcom['to_page_id']
                    ));
                    $to_carte=$this->getCarteTable()->getOne(array('id'=>$to_name['carte_id']));
                    $to_img=$this->getImageTable()->getOne(array('id'=>$to_carte['head_icon']));
                    $chatComment[]['comment'] = array(
                        'id' => $valcom['id'],
                        'content' => $valcom['content'],
                        'user' => array(
                            'id' => $valcom['user_id'],
                            'name' => $user['name'],
                            'imagePath'=>$img['path'].$img['filename']
                        ),
                        'userTo' => array(
                            'id' => $valcom['user_id_to'],
                            'name' => $valcom['to_name'],
                            'imagePath'=>$to_img['path'].$to_img['filename']
                        )
                    );
                }
                $chat_pea_where = new Where();
                $chat_pea_where->equalTo('chat_id', $val['id']);
                $chat_pea_where->equalTo('user_id', $this->getUserId());

                $chatP = $this->getChatPraiseTable()->getOne($chat_pea_where);

                $array[]['message'] = array(
                    "id" => $val['id'],
                    "type" => $val['type'],
                    "content" => $val['content'],
                    'statComment'=>$val['stat_comment'],
                    'statPraise'=>$val['stat_praise'],
                    "images" => $images,
                    "card" => $card,
                    "users" => $chatPraiser,
                    "comments" => $chatComment,
                    "timestamp" => $val['timestamp'],
                    "isPraise" => $chatP ? 1 : 0
                );
        }
            $response->messages = $array;
            $response->total = $chat['total'];
            return $response;

        }

        //不传id
        $where = array();
        $where['user_id_1'] = $this->getUserId();
        
        $where['attention'] = 3;
        $relation = $this->getUserRelationTable()->fetchAll($where); // 全部好友数据
//         echo '<pre>';
//         print_r($relation);die();
//         echo '</pre>';

        $user_list = array();
        foreach ($relation as $value) {
            $user_list[] = $value['user_id_2'];
        }
        $where_shield = array();
        $where_shield['user_id_1'] = $this->getUserId();
//         $where_shield['shield'] = 2;//v1.0不屏蔽的好友
        $where_shield['shield'] = 1;    //  屏蔽的好友  后加的 下个版本会删掉
        $where_shield['attention'] = 3; 
        $shield = $this->getUserRelationTable()->fetchAll($where_shield); // 不屏蔽的好友人脉圈数据
        
        $shield_list  =array();
        foreach ($shield as $val_sh) {
            $shield_list[] = $val_sh['user_id_2'];
        }

        $user_list[] = $this->getUserId();
       
//        $shield_list[] = $this->getUserId();//v1.0
        $chat_where = new Where();
//         $chat_where->in('user_id', $shield_list);  //v1.0
            if ($shield_list) //屏蔽的好友  后加的 下个版本会删掉
            {
                foreach ($shield_list as $shield_list_val){
                $chat_where->notEqualTo('user_id', $shield_list_val); //  屏蔽的好友  后加的 下个版本会删掉
                }
            }
        
        $chat_where->equalTo('delete', "0");
        
        $chat = $this->getAll($this->getChatTable(), $chat_where); // 人脉圈数据

        //var_dump($chat);
        $chat_list = array();
        $chat_ima = array();
        $array = array();
        foreach ($chat['list'] as $val) {
            $statComment = $val['stat_comment']; 
            $statPraise = $val['stat_praise'];
            $chat_comment_where = new Where();
            $chat_comment_where->equalTo('chat_id', $val['id'])->equalTo('delete', DELETE_FALSE)/* ->in('user_id', $user_list) */;

            $comment = $this->getViewChatCommentTable()->getAll($chat_comment_where, null, array('id' => 'asc'), true, 1, 9999); // 评论数据
            $chat_peaiser_where = new Where();
            $chat_peaiser_where->equalTo('chat_id', $val['id']);
            
            $peaiser = $this->getViewChatPraiseTable()->getAll($chat_peaiser_where, null, array(
                'id' => 'asc'
            ), true, 1, 10); // 点赞数据
            $images = array();
            $chatPraiser = array();
            $chatComment = array();
            if ($val['images']) {
                $chat_ima = explode(',', $val['images']);
//                 $chat_ima_where = new Where();
//                 $chat_ima_where->in('id', $chat_ima);
//                 $ima = $this->getImageTable()->fetchAll($chat_ima_where); // 图片的shuju
//                 foreach ($ima as $valima) {
//                     $images[]['image'] = array(
//                         'id' => $valima['id'],
//                         'path' => $valima['path'] . $valima['filename']
//                     );
//                 }
                
                foreach ($chat_ima as $ima_id) {
                    $ima = $this->getImageTable()->getOne(array('id' => $ima_id)); // 图片的shuju
                    $images[]['image'] = array(
                        'id' => $ima['id'],
                        'path' => $ima['path'] . $ima['filename']
                    );
                }
            }
            // $user_id_where
            // =
            // new
            // Where();
            // $user_id_where->in('id',
            // $user_list);
            // $userId
            // =
            // $this->getUserTable()->getAll($user_id_where);
            
            $card = $this->getCardDetails($val['user_id']);
            // 赞列表信息
            foreach ($peaiser['list'] as $valpea) {
                $name = $this->getPageTable()->getOne(array(
                    'id' => $valpea['page_id']
                ));
                $user = $this->getUserTable()->getOne(array('id'=>$name['user_id']));
               

                $chatPraiser[]['user'] = array(
                    'id' => $valpea['user_id'],
                    'name' => $user['name']
                );
            }
            //评论列表信息
            foreach ($comment['list'] as $valcom) {
                
                $name = $this->getPageTable()->getOne(array(
                    'id' => $valcom['page_id']
                ));
                
                $user = $this->getUserTable()->getOne(array('id'=>$name['user_id']));
                
                $to_name = $this->getPageTable()->getOne(array(
                    'id' => $valcom['to_page_id']
                ));
                $chatComment[]['comment'] = array(
                    'id' => $valcom['id'],
                    'content' => $valcom['content'],
                    'user' => array(
                        'id' => $valcom['user_id'],
                        'name' => $user['name']
                    ),
                    'userTo' => array(
                        'id' => $valcom['user_id_to'],
                        'name' => $valcom['to_name']
                    )
                );
              
               
            }
           
          
            $chat_pea_where = new Where();
            $chat_pea_where->equalTo('chat_id', $val['id']);
            $chat_pea_where->equalTo('user_id', $this->getUserId());
            
            $chatP = $this->getChatPraiseTable()->getOne($chat_pea_where);
           
            $array[]['message'] = array(
                "id" => $val['id'],
                "type" => $val['type'],
                "content" => $val['content'],
                'statComment' => count($chatComment),
                'statPraise' =>  $statPraise,
                "images" => $images,
                "card" => $card,
                "users" => $chatPraiser,
                "comments" => $chatComment,
                "timestamp" => $val['timestamp'],
                "isPraise" => $chatP ? 1 : 0
            );
        }
       
      
        /* echo "<pre>";
        var_dump($array);exit; */
        $response->messages = $array;
        $response->total = $chat['total'];
        return $response;
    }
   
    function getCardDetails($user_id)
    {
        if (isset($this->user_list[$user_id])) {
            return $this->user_list[$user_id];
        }
        $pageId_where = array(
            'id' => $user_id
        );
        $page = $this->getViewUserPageTable()->getOne($pageId_where); // 查询用户数据

        $show = explode(',', $page['show']);
        if (in_array('job', $show)) {
            
            $job = $page['position'];
        } 
        else {
            $job = "";
        }
        
            $user_id_1 = $this->getUserId();
            $user_id_2 = $user_id;
            
            $data1 = $this->getUserRelationTable()->getOne(array(
                'user_id_1' => $user_id_1,
                'user_id_2' => $user_id_2,
                /* 'attention' => 3, */
            ));
            
            $data2 = $this->getUserRelationTable()->getOne(array(
                'user_id_1' => $user_id_2,
                'user_id_2' => $user_id_1,
               /*  'attention' => 3, */
            ));
            
          /*   if($data1 || $data2){
                $attention = '3';
            }else{
                $attention = '0';
            } */
          
        //$company = $this->getCompanyTable()->getOne(array('user_id'=>$user_id));
        $company=$this->getViewPageCarteTable()->getOne(array('id'=>$page['page_id']));

        $user_ids = $this->getUserId();
        $array = $this->getUserRelation($user_ids);
        
        $relationship = 0;
        if (array_key_exists($user_id, $array['deep1'])) {
            $relationship = 1;
        }
        elseif (array_key_exists($user_id, $array['deep2'])) {
            $relationship = 2;
        }
        $vip_data = $this->getDeviceTable()->getOne(array(
            'user_id' => $page['id']
        ));
        if($vip_data){
            $vip = 1;
        }else{
            $vip = 0;
        }
        $card = array(
            'id' => $page['page_id'],
            'name' => $company['name'],
            'job' => $job,
            'imagePath' => $page['path'] . $page['filename'],
            'user' => array(
                'id' => $page['id'],
                'vip' => $vip,
                'relationship' => $relationship,
                'attention' => $data1['attention'] ? (int)$data1['attention'] : 0,
            ),
            'company' => array(
                  'id' => $company['company_id'],
                  'name' => $company['company'] ? $company['company']:$page['company'],           
            )
        );
        
        $this->user_list[$user_id] = $card;
        return $card;
    }
}







