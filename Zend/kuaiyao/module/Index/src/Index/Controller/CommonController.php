<?php
namespace Index\Controller;

use Zend\View\Model\ViewModel;
use Core\System\Uploadfile as Uploadfiles;
use Core\System\Image;
use Zend\Db\Sql\Where;
use Zend\Captcha\Image as imageCaptcha;
use Admin\Controller\Financial;
use Api3\Controller\CommonController as Api;
use Admin\Controller\CommonController as AdminController;
use Core\System\imageCache;
use Core\System\IpSearch\Ip;
use Core\System\WxPayApi\AiiWxPay;
use Api3\Controller\Item\OrderItem;
use Api3\Controller\SMSCode;

class CommonController extends TableController
{

    private $api_controller;

    protected $table; // 表对象

    /**
     * 后台列表专用
     * where
     * 条件数组格式
     *
     * @var array
     */
    protected $Where;

    /**
     * 后台列表专用
     * 分页路由用的方法名,如果是index可不设置此参数
     *
     * @var str
     */
    protected $action;

    /**
     * 后台列表专用
     *
     * @var array (0=>模板目录/模板,1=>菜单对应高亮的变量)
     */
    protected $template;

    /**
     * 后台列表专用
     * 字段条件筛选 array('字段1','字段2')
     *
     * @var array
     */
    protected $screening;

    /**
     * 后台列表专用
     * 搜索字段 array('字段1','字段2')
     *
     * @var array
     */
    protected $seach;

    /**
     * 后台列表专用
     * 其它要传入模板的数据 array('模板变量'=>值)
     *
     * @var array
     */
    protected $other;

    /**
     * 后台列表专用
     * 是否要查已删除的字段 true 不查（默认）
     *
     * @var bool
     */
    protected $delete = true;

    /**
     * 后台列表专用
     * 默认ID 倒序
     *
     * @var array
     */
    protected $order = array(
        'id desc'
    );

    /**
     * 错误提示
     *
     * @var str
     */
    protected $error_message = null;

    /**
     *获取访问IP地址所在地区
     */
    protected $ip_region = '';

    public function __construct()
    {
        //if($this->is_mobile())
//        {
//            header("location:http://wap.mwvip.com");
//            die();
//        }
        parent::__construct();
    }

    //判断是否属手机
   public function is_mobile() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","hp-tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
        $is_mobile = false;
        foreach ($mobile_agents as $device) {
           if (stristr($user_agent, $device)) {
               $is_mobile = true;
               break;
           }
       }
        return $is_mobile;
    }


    /**
     * 登录判断
     *
     * @param string $action_list
     *            权限列表
     * @return boolean
     */
    public function login()
    {
        //var_dump(!isset($_COOKIE['mobile']) , !isset($_COOKIE['index_user_id']) , !isset($_COOKIE['name']));exit;
        $user_info = $this->getUserTable()->getOne(array('id'=>$_COOKIE['index_user_id'])); //9.18加密算法更新
        $code = md5($_COOKIE['index_user_id'] . $_COOKIE['mobile'].$user_info['status']);
        if (!isset($_COOKIE['mobile']) || !isset($_COOKIE['code']) || $code != $_COOKIE['code'])
        {
            $this->clearCookie();
            $this->redirect()->toRoute('index', array(
                'controller' => 'index',
                'action' => 'index',
            ));
        }else
        {
            setcookie('index_user_id',$_COOKIE['index_user_id'],time()+3600*24*30,ROOT_PATH);
            setcookie('mobile',$_COOKIE['mobile'],time()+3600*24*30,ROOT_PATH);
            if (isset($_COOKIE['name'])){
                setcookie('name',$_COOKIE['name'],time()+3600*24*30,ROOT_PATH);
            }
            return true;
        }
    }

    /*
     * 微信登录判断
     *
     */

    public function wxlogin()
    {
        //$user_info = $this->getUserTable()->getOne(array('id'=>$_COOKIE['wx_user_id'])); //9.18加密算法更新
        /*$code = md5($_COOKIE['wx_user_id'] . $_COOKIE['wx_mobile'].$user_info['status']);*/
        if (!isset($_COOKIE['wx_mobile']) || !isset($_COOKIE['wx_user_id']) )
        {
            $this->clearCookie();
            $this->redirect()->toRoute('index', array(
                'controller' => 'commodity',
                'action' => 'wxlogin',
            ));
        }else
        {
            setcookie('wx_user_id',$_COOKIE['wx_user_id'],time()+3600,ROOT_PATH);
            setcookie('wx_mobile',$_COOKIE['wx_mobile'],time()+3600,ROOT_PATH);
            /*if (isset($_COOKIE['name'])){
                setcookie('name',$_COOKIE['name'],time()+3600*24*30,ROOT_PATH);
            }*/
            return true;
        }
    }



    public function clearCookie() {
        //         setcookie('mobile','',-1,ROOT_PATH);
        //         setcookie('name','',-1,ROOT_PATH);
        //         setcookie('index_user_id','',-1,ROOT_PATH);
        setcookie('mobile','',time() - 600);
        setcookie('name','',time() - 600);
        setcookie('index_user_id','',time() - 600);
        setcookie('code','',time() - 600);
        setcookie('mobile','',time() - 600,ROOT_PATH);
        setcookie('name','',time() - 600,ROOT_PATH);
        setcookie('index_user_id','',time() - 600,ROOT_PATH);
        setcookie('code','',time() - 600,ROOT_PATH);
        session_destroy();//9.16
        //   unset($_COOKIE['index_user_id'],$_COOKIE['name'],$_COOKIE['mobile']);
    }

    /**
     * 用户登出
     *
     * @return Ambigous <\Zend\Http\Response,
     *         \Zend\Stdlib\ResponseInterface>
     */
    protected function quit()
    {
        session_destroy();
        return $this->redirect()->toRoute('index', array(
            'controller' => 'index',
            'action' => 'index',
        ));
    }
    /**
     * 内容过滤
     *
     */
    protected function HtmlFilter($str){
		return addslashes(htmlspecialchars(trim($str), ENT_QUOTES));
	}
    /**
     * 内容过滤
     *
     */
    protected function back($str='',$url=array()){
        $view = new ViewModel(array(
            "str"=>$str,
            "url"=>$url,
        ));
        $view->setTemplate('index/index/back');
        return $this->setMenu($view);
	}

    /**
     * 2014/09/10
     * 订单号财务流水号生成
     *
     * @author liujun
     * @return integer $order_id
     */
    public function generate()
    {
        $code = date('YmdHis') . mt_rand(10, 99);

        return $code;
    }


    /**
     * 2014/09/10 未测试
     * 更新商家星级（用户评价商品）
     *
     * @author liujun
     * @param int $user_id
     *            商家ID
     * @return number 星级
     */
    public function updateStars($user_id)
    {
        $res = $this->getEvaluateTable()->getUserStars($user_id);
        $stars = 1; // 默认一星
        if ($res->count > 0)
        {
            $stars = round($res->stars / $res->count);
        }
        $this->getMerchantTable()->updateEvaluationNumber($user_id);
        $this->getMerchantTable()->update(array(
            'stars' => $stars
        ), array(
            'id' => $user_id
        ));
        return true;
    }

    /**
     * 2014/09/10 商家类型已测试通过(用户未测试)
     * 更新推荐人推荐数(并插入推荐关系表)
     *
     * @author liujun
     * @param int $id
     *            推荐人(用户OR商家)ID
     * @param int $been_recommended_id
     *            被推荐人ID
     * @param int $type
     *            1用户 2商家
     */
    public function updateRecommendedNumber($id, $been_recommended_id, $type)
    {
        if ($type == 1)
        {
            // 用户
            $user = $this->getUserTable()->getOne(array(
                'id' => $id
            ));
            if (isset($user->id) && $user->id)
            {
                $this->getUserTable()->updateRecommendedNumber($id);
                $this->getUserRecommendTable()->insert(array(
                    'recommend_id' => $id,
                    'been_recommended_id' => $been_recommended_id
                ));
                $this->getUserTable()->update(array(
                    'referrer_id' => $id
                ), array(
                    'id' => $been_recommended_id
                ));
                $silver_cat = $this->getSetupTable()->getOne(array(
                    'id' => 20
                )); // 查询配置文件送多少银猫
                $param = array(
                    'silver_cat' => $silver_cat->value,
                    'transfer_way' => 6,
                    'user_id' => $id
                );
                $this->getFinancialObject($param);
            }
            return true;
        }
        elseif ($type == 2)
        {
            // 商家
            $user = $this->getMerchantTable()->getOne(array(
                'id' => $id
            ));
            if (isset($user->id) && $user->id)
            {
                $been_recommended = $this->getMerchantTable()->getOne(array(
                    'id' => $been_recommended_id
                )); // 被推荐人信息
                $this->getMerchantTable()->updateRecommendedNumber($id);
                $date = array(
                    'recommend_id' => $id,
                    'been_recommended_id' => $been_recommended_id,
                    'type_one' => $user->type,
                    'type_two' => $been_recommended->type
                );
                $this->getMerchantRecommendTable()->insert($date);
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 2014/09/18
     * 商家冲值推荐人返利入库
     *
     * @author liujun
     * @param int $user_id
     *            商家ID
     * @param float $golden_cat
     *            金猫
     * @param float $silver_cat
     *            银猫
     * @param int $type
     *            0充值调用 1财务审核通过调用
     */
    public function merchantRechargeRebate($merchant_id, $golden_cat, $silver_cat, $type = 0)
    {
        if (! $merchant_id)
        {
            die('商家ID不能为空');
        }

        if ($type == 0)
        {
            // 充值未审核
            $data['recharge_index_id'] = $_SESSION['index_id'];
            $data['recharge_index_name'] = $_SESSION['index_name'];
            $data['golden_cat'] = $golden_cat;
            $data['silver_cat'] = $silver_cat;
            $data['transfer_way'] = 14;
            $data['merchant_id'] = $merchant_id;
            return $this->error_message = $this->getFinancialObject($data); // 写入财务表 更新前商家金猫数/银猫
        }
        elseif ($type == 1)
        {
            // 财务审核通过
            $recommend = $this->getMerchantRecommendTable()->getOne(array(
                'been_recommended_id' => $merchant_id
            )); // 当前商家的首级推荐人

            $this->getMerchantTable()->updateMoney($merchant_id, 1, 'golden_cat', $golden_cat); // 更新当前用户金猫
            $this->getMerchantTable()->updateMoney($merchant_id, 1, 'silver_cat', $silver_cat); // 更新当前用户银猫

            if ($recommend)
            {
                // 如果有推荐人
                $where = array(
                    'been_recommended_id' => $recommend->recommend_id
                );
                $secondary_recommend = $this->getMerchantRecommendTable()->getOne($where); // 商家的次级推荐人

                $data['transfer_way'] = 7; // 公共字段 交易方式： 返利
                if ($recommend->type_one == 3)
                {
                    // 首级商家返利
                    $data['golden_cat'] = $this->rebatesCalculation(14, $golden_cat);
                    $data['silver_cat'] = $this->rebatesCalculation(14, $silver_cat);
                    $data['merchant_id'] = $recommend->recommend_id;
                    $this->error_message = $this->getFinancialObject($data); // 写入财务表 更新首级商家推荐人金猫数/银猫
                }
                else
                {
                    // 首级运营商，城门合伙人返利
                    $data['golden_cat'] = $this->rebatesCalculation(15, $golden_cat);
                    $data['silver_cat'] = $this->rebatesCalculation(15, $silver_cat);
                    $data['merchant_id'] = $recommend->recommend_id;
                    $this->error_message = $this->getFinancialObject($data); // 写入财务表 更新首级商家推荐人金猫数/银猫
                }

                if ($secondary_recommend)
                {
                    // 如果有次级推荐人
                    $data['golden_cat'] = $this->rebatesCalculation(16, $golden_cat);
                    $data['silver_cat'] = $this->rebatesCalculation(16, $silver_cat);
                    $data['merchant_id'] = $secondary_recommend->recommend_id;
                    $this->error_message = $this->getFinancialObject($data); // 写入财务表 更新次级推荐人金猫数/银猫
                }
            }

            return $this->error_message;
        }
    }

    /**
     * 获取财务类
     *
     * @return number 状态码
     */
    public function getFinancialObject($param)
    {
        $serviceLocator = $this->getServiceLocator();
        $financial = new Financial($param);
        $financial->setServiceLocator($serviceLocator);
        return $financial->transferCollection();
    }


    /**
     * 2014/10/16
     * 根据数据配置信息计算返利金额
     *
     * @author liujun
     * @param unknown $id
     *            数据配置ID
     * @param unknown $amount
     *            金额 金猫/银猫
     */
    public function rebatesCalculation($id, $amount)
    {
        $where = new Where();
        $where->equalTo('id', $id);
        $setup = $this->getSetupTable()->getOne($where);
        if (! $setup)
        {
            return false;
        }
        $rebate = round($amount * ($setup->value / 100), 2);
        return $rebate;
    }

    /**
     * 2014/09/10
     * 获到当前时间用于插入数据库里的timestamp字段
     *
     * @author liujun
     * @return string
     */
    protected function getTime()
    {
        return date("Y-m-d H:i:s");
    }

    /**
     * 2014/09/10
     * 用户状态输出
     *
     * @author liujun
     * @return Ambigous <string>|string
     */
    public function getUserStatus()
    {
        $status_array = array(
            '1' => '正常',
            '2' => '停用'
        );
        return $status_array;
    }

    /**
     * 2014/09/10
     * 用户认证状态输出
     *
     * @author liujun
     * @return Ambigous <string>|string
     */
    public function getUserAuthStatus()
    {
        $auth_array = array(
            '0' => '未认证',
            '2' => '已认证',
            '3' => '未通过'
        );
        return $auth_array;
    }

    /**
     * 2014/09/10
     * 产品状态输出
     *
     * @author liujun
     * @return Ambigous <string>|string
     */
    public function getGoodsStatus()
    {
        $goods_status = array(
            '1' => '正常',
            '2' => '下架'
        );
        return $goods_status;
    }

    /**
     * 2014/09/10
     * 订单状态输出
     *
     * @author liujun
     * @return Ambigous <string>|string
     */
    public function getOrderStatus()
    {
        $order_status = array(
            '1' => '待发货',
            '2' => '已发货',
            '3' => '已收货'
        );
        return $order_status;
    }

    /**
     * 2014/09/16
     * 财务交易方式输出
     *
     * @author liujun
     * @return Ambigous <string>|string
     */
    public function getTransferWay()
    {
        $TransferWay = array(
            1 => '充值',
            2 => '商家产品',
            3 => '精品广场',
            4 => '秒杀',
            5 => '抽奖',
            6 => '推荐',
            7 => '返利',
            8 => '兑换',
            9 => '摇钱树',
            10 => '商家赠送',
            11 => '签到',
            12 => '淘银猫',
            13 => '提现',
            14 => '商家充值',
            15 => '退款'
        );
        return $TransferWay;
    }

    /**
     * 2014/09/16
     * 提现类型输出
     *
     * @author liujun
     * @return Ambigous <string>|string
     */
    public function getWithdrawalsType()
    {
        $WithdrawalsType = array(
            1 => '收入提现',
            2 => '余额提现',
            3 => '返利提现'
        );
        return $WithdrawalsType;
    }

    /**
     * 设置模板跟菜单
     * @param $type 不传或者传s为普通layout，传其他为登陆页layout
     *
     * @param unknown $mainView
     * @param string $controller
     * @return \Zend\View\Model\ViewModel
     */
    protected function setMenu($mainView, $type = '')
    {
        $layout = new ViewModel(array(
            "menu" => $type
        ));
        if ($type == 1) {
            $layout->setTemplate('layout_index/layout');
            $menuView = new ViewModel(array());
            $menuView->setTemplate('layout_index/menu');
            $menuView->addChild($mainView, 'main');
            $layout->addChild($menuView, 'content');
        }
        elseif ($type == 2) {
            $layout->setTemplate('layout_index/layout2');
            $layout->addChild($mainView, 'content');
        }

        return $layout;
    }
    /**
     * 设置模板跟菜单
     *
     * @param unknown $mainView
     * @param string $controller
     * @return \Zend\View\Model\ViewModel
     */
    protected function UserMenuTemplate($mainView, $type = '')
    {
        $ads_15 = $this->getImageTable()->getAdsImages(15);

        $UserMenu = new ViewModel(array(
            'type' =>$type,
            'ads_15' =>$ads_15,
        ));
        $UserMenu->setTemplate('layout_index/usermenu');
        $mainView->addChild($UserMenu, 'menu');
        return $mainView;
    }

    /**
     * 后台列表
     * 调用此方法实现列表查询,调用此方法前需向属性Table传入表对像 如$this->table = $this->getUserTable();
     * $this->Where 属性为外部输入条件
     * $this->action 分页用的访问方法名 如果是index 可不设置
     * $this->template (0=>模板目录/模板,1=>菜单对应高亮的变量) 必设置
     * $this->screening 条件筛选字段 array('字段1','字段2')
     * $this->seach 关键字要搜索的字段 array('字段1','字段2')
     * $this->other 其它要传入模板的数据 array('模板变量'=>值)
     * $this->delet 是否要查已删除的字段 true 不查(默认)
     * $this->order 排序字段
     *
     * @return array $list 列表数据
     */
    public function getList()
    {
        if (! $this->table)
        {
            die('表对像不能为空');
        }
        $page = $this->params()->fromRoute('page');
        $type = $this->params()->fromRoute('id');
        $keyword = $this->params()->fromRoute('keyword') ? trim($this->params()->fromRoute('keyword')) : '';
        $like = null;

        if (isset($_POST['submit']) && $_POST['keyword'] != '' || $keyword)
        {
            $keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : $keyword;
            if ($keyword && is_array($this->seach))
            {
                foreach ($this->seach as $v)
                {
                    $like[$v] = $keyword;
                }
            }
        }

        $where = null;

        if ($type)
        {
            if (is_array($this->screening))
            {
                foreach ($this->screening as $v)
                {
                    $where[$v] = $type;
                }
            }
        }

        if ($this->delete)
        {
            $where['delete'] = 0;
        }

        if ($where && $this->Where && is_array($this->Where))
        {
            $where = array_merge($where, $this->Where);
        }
        elseif (! $where && $this->Where)
        {
            $where = $this->Where;
        }

        $list = $this->table->getAll($where, null, $this->order, true, $page, 0, $like);

        $date = array();

        if ($this->other && is_array($this->other))
        {
            foreach ($this->other as $k => $v)
            {
                $date[$k] = $v;
            }
        }

        $date_two = array(
            'paginator' => $list['paginator'],
            'list' => $list['list'],
            'condition' => array(
                'action' => $this->action,
                'id' => $type,
                'page' => $page,
                'where' => array()
            ),

            'type' => $type,
            'page' => $page,
            'keyword' => $keyword
        );
        if (! $this->action || $this->action == 'index')
        {
            unset($date_two['condition']['action']);
        }
        $view_date = array_merge($date, $date_two);
        $view = new ViewModel($view_date);
        $view->setTemplate('index/' . $this->template[0]);
        return $this->setMenu($view, 1);
    }

    /**
     * 删除一条记录
     * 需指定表对像
     * $this->table
     */
    public function deleteDate()
    {
        $this->checkLogin();
        if (! $this->table)
        {
            die('表对像不能为空');
        }
        $id = (int) $this->params()->fromRoute('id');
        $url = $_SERVER['HTTP_REFERER'];
        if ($id)
        {
            if ($this->table->deleteData($id))
            {
                echo "<script>window.location.href='$url'</script>";
            }
        }
    }

    /**
     * 前端接收表单文件域传过来文件
     * 用于上传文件处理
     * 4:3
     *
     * @author liujun
     * @return string 用于模板页面JS处理
     */
    public function getIndexFileAction()
    {
        if (isset($_FILES) && $_FILES['Filedata']['error'] == 0 && $this->check_file_type($_FILES['Filedata']['tmp_name']))
        {
            $file = $this->Uploadfile(THUMB_WIDTH, THUMB_HEIGHT);
            $path = 'http://' . HTTP . ROOT_PATH . 'uploadfiles/' . $file['path'] . $file['filename'];
            $image_id = $this->getImageTable()->insertData($file);
            if (! $file)
            {
                $error = '上传失败，未知错误！';
            }
            else
            {
                $error = '';
            }
            echo $path . "," . $image_id;
            die();
        }
        else
        {
            $error = '文件类型不正确，或未选择上传图片！';
            $path = '';
            $image_id = '';
            echo $path . "," . $image_id;
            die();
        }
        die();
    }

    /**
     * 接收表单文件域传过来文件 用于上传文件处理 5:3
     *
     * @author liujun
     * @return string 用于模板页面JS处理
     */
    public function getInputFileTwoAction()
    {
        if (isset($_FILES) && $_FILES['Filedata']['error'] == 0 && $this->check_file_type($_FILES['Filedata']['tmp_name']))
        {
            $file = $this->Uploadfile(THUMB_WIDTH_2, THUMB_HEIGHT_2);
            $path = 'http://' . HTTP . ROOT_PATH . 'uploadfiles/' . $file['path'] . $file['filename'];
            $image_id = $this->getImageTable()->insertData($file);
            if (! $file)
            {
                $error = '上传失败，未知错误！';
            }
            else
            {
                $error = '';
            }
            echo "{";
            echo "error: '" . $error . "',\n";
            echo "path: '" . $path . "',\n";
            echo "imgid: '" . $image_id . "'\n";
            echo "}";
            die();
        }
        else
        {
            $error = '文件类型不正确，或未选择上传图片！';
            $path = '';
            $image_id = '';
            echo "{";
            echo "error: '" . $error . "',\n";
            echo "path: '" . $path . "',\n";
            echo "imgid: '" . $image_id . "'\n";
            echo "}";
            die();
        }
        die();
    }

    /**
     * 检查图片文件类型
     *
     * @access public
     * @param
     *            string filename 文件名（地址）
     * @return string 空为验证失败
     */
    public function check_file_type($filename)
    {
        $limit_ext_types = '|GIF|JPG|JEPG|PNG|';
        if ($filename)
        {
            $extname = strtolower(substr($filename, strrpos($filename, '.') + 1));
        }
        else
        {
            return '';
        }

        $str = $format = '';

        $file = @fopen($filename, 'rb');
        if ($file)
        {
            $str = @fread($file, 0x400); // 读取前 1024 个字节
            @fclose($file);
        }
        else
        {

            return '';
        }

        if ($format == '' && strlen($str) >= 2)
        {

            if (substr($str, 0, 3) == "\xFF\xD8\xFF")
            {
                $format = 'jpg';
            }
            elseif (substr($str, 0, 4) == 'GIF8' && $extname != 'txt')
            {
                $format = 'gif';
            }
            elseif (substr($str, 0, 8) == "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A")
            {
                $format = 'png';
            }
        }

        if ($limit_ext_types && stristr($limit_ext_types, '|' . $format . '|') === false)
        {
            $format = '';
        }

        return $format;
    }

    /**
     * 上传文件处理
     *
     * @author liujun
     * @param string $pash
     *            要上传到的文件夹
     *            默认为public
     *            下的uploadfiles/年月命名的文件夹（此文件夹为大图文件夹）
     * @param boolean $is_thumb
     *            是否生成缩略图
     *            默认为是false，true为是
     * @param string $width
     *            缩略图宽度(PX)（自动用作缩略图路径
     *            默认100）
     * @param string $height
     *            缩略图高度(PX)（自动用作缩略图路径
     *            默认
     *            100）
     * @param integer $filetype
     *            1,为图片类;2,swf类;3,音频类;4,文本文件类;5,可执行文件类;
     *            默认为
     *            1图片类
     * @param integer $size
     *            设置上传最大文件的大小（与PHP配置文件有关）此项默认为：2M
     * @return array $array
     *         array('filename','path','size','mime','extension')
     */
    protected function Uploadfile($width = THUMB_WIDTH, $height = THUMB_HEIGHT, $pash = UPLOAD_PATH, $is_thumb = true, $filetype = 1, $size = 2048)
    {
        set_time_limit(0);
        $upload = new Uploadfiles($pash, $size, $filetype, 'Ymd');
        $upload->uploadfile();
        $filename = $upload->getUploadFileInfo();
        $name = $upload->newfilename;
        $path = $upload->imgPath;
        if ($filetype == 1 && $is_thumb == true)
        {
            $thumb = new Image();
            $thumb->nameRule = substr($name, 0, strrpos($name, '.'));
            @$thumb_name = $thumb->makeThumb($filename['Filedata']['new_name'], $width, $height, THUMB_IMAGE_PATH . $width . 'X' . $height);
        }
        $array = array(
            'filename' => $name,
            'path' => $path,
            'timestamp' => date("Y-m-d H:i:s")
        );

        return $array;
    }

    /**
     * 格式化
     * region_info
     * 字段（转为JOSN，用于插入数据库）
     *
     * @author liujun
     * @param integer $county
     *            区域ID
     * @param integer $city
     *            城市ID
     * @param integer $province
     *            省份直辖市ID
     * @return string region_info
     *
     *         JSON数据
     *
     */
    protected function encode($province, $city, $county)
    {
        $region_info = array();
        if ($province > 1)
        {
            $res = $this->getRegionTable()->getOne(array(
                'id' => $province
            ));

            $province_info = array(
                "id" => $res->id,
                "name" => $res->name,
                "parent_id" => 1,
                "pinyin" => $res->pinyin
            );
            $region_info[] = array(
                "region" => $province_info
            ); // $province_info;
        }

        if ($city > 1)
        {
            $res = $this->getRegionTable()->getOne(array(
                'id' => $city
            ));
            $city_info = array(
                'id' => $res->id,
                'name' => $res->name,
                'parent_id' => $res->parent_id,
                'pinyin' => $res->pinyin
            );
            $region_info[] = array(
                "region" => $city_info
            );
        }

        if ($county > 1)
        {
            $res = $this->getRegionTable()->getOne(array(
                'id' => $county
            ));
            $county_info = array(
                'id' => $res->id,
                'name' => $res->name,
                'parent_id' => $res->parent_id,
                'pinyin' => $res->pinyin
            );
            $region_info[] = array(
                "region" => $county_info
            );
        }

        $json = json_encode($region_info);
        return $json;
    }

    public function getAllRegionInfo($county){
        if ($county)
        {
            $county = $this->getRegionTable()->getOne(array('id' => $county));
            $city = $this->getRegionTable()->getOne(array('id' => $county['parent_id']));
            $province = $this->getRegionTable()->getOne(array('id' => $city['parent_id']));
            $region_info = array(
                'county_id' => $county->id,
                'city_id' => $city->id,
                'province_id' => $province->id,
                'county' => $county->name,
                'city' => $city->name,
                'province' => $province->name,
            );
        }
        return $region_info;
    }

    /**
     * 解析 region_info 字段（转为数组，用于模板数据）
     *
     * @author liujun
     * @param string $result
     *            数据库region_info JSON数据
     * @return array array('province'=>省信息数组,'city'=>市信息数组，'county'=>区信息数组)
     *
     */
    protected function decode($result)
    {
        $result_info = array();

        $result = json_decode($result);
        if (isset($result[0]->region->id))
        {
            $province = array(
                'id' => $result[0]->region->id,
                'name' => $result[0]->region->name,
                'parent_id' => 1,
                'pinyin' => $result[0]->region->pinyin
            );
            $result_info['province'] = $province;
        }

        if (isset($result[1]->region->id))
        {
            $city = array(
                'id' => $result[1]->region->id,
                'name' => $result[1]->region->name,
                'parent_id' => $result[1]->region->parent_id,
                'pinyin' => $result[1]->region->pinyin
            );
            $result_info['city'] = $city;
        }

        if (isset($result[2]->region->id))
        {
            $county = array(
                'id' => $result[2]->region->id,
                'name' => $result[2]->region->name,
                'parent_id' => $result[2]->region->parent_id,
                'pinyin' => $result[2]->region->pinyin
            );
            $result_info['county'] = $county;
        }

        return $result_info;
    }

    /**
     * 2014/09/11
     * 解析
     * region_info
     * 字段
     *
     * @author liujn
     *
     * @param string $result
     *            数据库region_info
     *            JSON数据
     * @return string 省市区名字
     *
     */
    public function getProvinceCityCountryName($region_info)
    {
        $region = json_decode($region_info);
        $result = array();
        if (isset($region[0]->region->id))
        {
            $result = $region[0]->region->name;
            $result .= ' ';
        }
        if (isset($region[1]->region->id))
        {
            $result .= $region[1]->region->name;
            $result .= ' ';
        }
        if (isset($region[2]->region->id))
        {
            $result .= $region[2]->region->name;
            $result .= ' ';
        }
        return $result;
    }

    /**
     * 验证码生成
     *
     * @author liujun
     */
    public function generateCaptchaAction()
    {
        $captcha = new imageCaptcha();
        $number = rand(1, 6);
        $language = __DIR__ . "/../../../language/$number.ttf";
        $captcha->setFont($language); // 字体路径
        $captcha->setImgDir(UPLOAD_PATH . 'captcha/'); // 验证码图片放置路径
        $captcha->setImgUrl(ROOT_PATH . 'uploadfiles/captcha/');
        $captcha->setWordlen(5);
        $captcha->setFontSize(30);
        $captcha->setLineNoiseLevel(4); // 随机线
        $captcha->setDotNoiseLevel(40); // 随机点
        $captcha->setExpiration(10); // 图片回收有效时间
        $captcha->generate(); // 生成验证码
        $_SESSION['captcha'] = $captcha->getWord();
        echo $captcha->getImgUrl() . $captcha->getId() . $captcha->getSuffix(); // 图片路径
        die();
    }

    /**
     * 排序地区字段
     *
     * @param 地区数组 $array
     * @return array
     */
    public function typeArray($array)
    {
        $con = null;
        $na = array();
        foreach ($array as $k => $v)
        {
            $code = array();
            $code['name'] = $v['parent_id'] != 1 ? $na[$v['parent_id']]['name'] . $k . "|" : $k . "|";
            $code['status'] = $v['status'];
            $code['timestamp_update'] = $v['timestamp_update'];
            $code['parent_id'] = $v['parent_id'];
            $na[$k] = $code;
        }
        asort($na); // 排序
        foreach ($na as $k => $v)
        {
            $s = substr_count($v['name'], "|");
            if ($s < 3)
            {
                $region = array();
                $region['id'] = $k;
                $region['name'] = str_repeat("&nbsp&nbsp&nbsp&nbsp└", ($s - 1)) . $array[$k]['name'] . "\n";
                $region['status'] = $v['status'];
                $region['timestamp_update'] = $v['timestamp_update'];
                $region['parent_id'] = $v['parent_id'];
                $con[] = $region;
            }
        }
        return $con;
    }
    /**
     * 错误提示信息输出
     *
     * @param string $message
     *            提示信息
     * @param integer $type
     *            是否要后退一页
     */
    public function showMessage($message, $type = true)
    {
        $location = $type ? "history.back(-1);" : '';
        echo "<script type='text/javascript'>alert('{$message}');{$location}</script>";
        die();
    }
    public function showNext($location = '')
    {
        $location = $location ? "window.location.href = '" . $location . "'" : 'history.back(-1);';
        echo "<script type='text/javascript'>{$location}</script>";
        exit();

    }
    public function showTu($message,$location = '')
    {
        $location = $location ? "window.location.href = '" . $location . "'" : 'history.back(-1);';
        echo "<script type='text/javascript'>alert('{$message}');{$location}</script>";
        exit();

    }
    /**
     * 取得后台控制器的方法
     *
     * @return \Admin\Controller\CommonController
     */
    public function getAdminController()
    {
        if(! isset($this->admin_controller))
         {
        	$serviceLocator = $this->getServiceLocator();
            $this->admin_controller = new AdminController();
            $this->admin_controller->setServiceLocator($serviceLocator);
        }
        return $this->admin_controller;
    }
    /**
     * 取得协议控制器的方法
     *
     * @return \Api\Controller\CommonController
     */
    public function getApiController()
    {
        if(! isset($this->api_controller)) {
            $this->api_controller = new Api();
        }
        return $this->api_controller;
    }
    /**
     * 地区选择
     * @params $type 商品类型
     */
    public function selectRegion($type)
    {
        $merchant_id = array();

//        if(isset($_COOKIE['city_id']) && $_COOKIE['city_id'] != 0){
//            $merchant = $this->getMerchantTable()->fetchAll(array('city_id'=>$_COOKIE['city_id'],'delete'=>0,'status'=>1));
//        }else{
            $merchant = $this->getMerchantTable()->fetchAll(array('delete'=>0,'status'=>1));
//        }

        foreach($merchant as $v){
            $merchant_id[] = $v['id'];
        }

        if(!empty($merchant_id)) {
            $where = array('type'=>$type,'delete'=>0,'status'=>1,'user_id'=>$merchant_id);
        }else{
            $where = array('type'=>$type,'delete'=>0,'status'=>1,'user_id'=>0);
        }

        return $where;
    }

    /**
     * 银行卡列表
     *
     * @return multitype:string
     */
    public function bankList()
    {
        return  array(
            1 =>'工商银行',
            2 =>'农业银行',
            3 =>'招商银行',
            4 =>'建设银行',
            5 =>'中国银行',
            6 =>'浦发银行',
            7 =>'广发银行',
            8 =>'民生银行',
            9 =>'平安银行',
            10 =>'光大银行',
            11 =>'兴业银行',
            12 =>'中信银行',
            13 =>'交通银行',
            14 =>'上海银行',
            15 =>'上海农商',
            16 =>'南粤银行',
            17 =>'宁波银行',
            18 =>'华润银行',
            19 =>'华夏银行',
            20 =>'北京银行',
            21 =>'江苏银行',
            22 =>'南京银行',
        );
    }


    /**
     * 个性名片的图片
     *
     * @return multitype:string
     */
    public function styleImageList()
    {
       
        $img=new imageCache();
        $filename =  'img/img_id';
        $Ids = $img->getCache($filename, 1);
        $list = array(
            array(
                'id' => 1,
                'name' => '名片1',
                'image' => $Ids['0'] ?$Ids['0']:1094,
            ),
            array(
                'id' => 2,
                'name' => '名片2',
                'image' => $Ids['1'] ?$Ids['1']:1095,
            ),
            array(
                'id' => 3,
                'name' => '名片3',
                'image' => $Ids['2'] ?$Ids['2']:1096,
            ),
            array(
                'id' => 4,
                'name' => '名片4',
                'image' => $Ids['3'] ?$Ids['3']:1098,
            ),
            array(
                'id' => 5,
                'name' => '名片5',
                'image' => $Ids['4'] ?$Ids['4']:1099,
            ),
            array(
                'id' => 6,
                'name' => '名片6',
                'image' => $Ids['5'] ?$Ids['5']:1100,
            ),
            array(
                'id' => 7,
                'name' => '名片7',
                'image' => $Ids['6'] ?$Ids['6']:1101,
            ),
            array(
                'id' => 8,
                'name' => '名片8',
                'image' => $Ids['7'] ?$Ids['7']:1102,
            ),
            array(
                'id' => 9,
                'name' => '名片9',
                'image' => $Ids['8'] ?$Ids['8']:1103,
            ),
            
        );
        return $list;
    }

    /**
     * 统一下单
     *
     * @param OrderItem $order
     * @return multitype:string |multitype:number unknown Ambigous <number, string> NULL Ambigous <number, string, unknown, NULL> string Ambigous <>
     * @version 2015-11-24 WZ
     */
    public function orderSubmit($user_id ,$order) {
        // 检查收货地址
        $order->number = $order->number ? $order->number : 1;
        $setting = $this->getApiController()->getSetting();
        $diff = abs($setting['goodsPrice'] - $setting['goodsDiscountPrice']);
        $address_info = $this->getUserAddressTable()->getOne(array('user_id' => $user_id));

        if (! $address_info) {
            return array('status' => STATUS_UNKNOWN, 'description' => '未选择收货地址');
        }

        $codes = $order->codes;
        $result = array(
            'status' => 0, // 正常
            'msg' => '下单成功', // 描述
            'price' => $setting['goodsPrice'], // 正常价
            'order_sn' => $this->makeSN(), // 订单号
            'page_id' => 0,
        );
        $total = $order->number * $result['price'];

        // 处理优惠码
        $code_info = null;
        $code_id = "";
        if ($codes && is_array($codes)) {
            $code_id = array();
            $codes = array_unique($codes);
            foreach ($codes as $code) {
                $code_where = array('code' => $code, 'status' => 0);
                $code_info = $this->getInvitationCodeTable()->getOne($code_where);

                if (! $code_info) {
                    return array('status' => STATUS_UNKNOWN, 'description' => '优惠码无效');
                }

                if ($code_info['user_id'] == $user_id) {
                    return array('status' => STATUS_UNKNOWN, 'description' => '用户您自己的优惠码只能分享给朋友使用');
                }

                $total -= $diff;
                $code_id[] = $code_info['id'];
                //             $this->getInvitationCodeTable()->updateData(array('status' => 1,'timestamp_update' => $this->getTime(), 'recommended_user_id' => $user_id), $code_where);
            }

            $code_id = implode(',', $code_id);
            if (strlen($code_id) > 255) {
                return array('status' => STATUS_UNKNOWN, 'description' => '一次使用优惠码过多');
            }
        }

        $status = 1;
        if ($order->payment && 0) {
            // 如果有余额支付，在这里判断
            $status = 2;
        }

        $order_data = array(
            'order_sn' => $result['order_sn'],
            'code_id' => $code_id,
            'price' => $result['price'],
            'number' => $order->number,
            'total' => $total,
            'status' => $status,
            'payment' => $order->payment ? $order->payment : 0,
            'invoice_status' => $order->invoice->status ? $order->invoice->status : 0,
            'invoice_type' => $order->invoice->type ? $order->invoice->type : 0,
            'invoice_name' => $order->invoice->name ? $order->invoice->name : '',
            'address_id' => $address_info['id'],
            'address_name' => $address_info['name'],
            'address_telephone' => $address_info['telephone'],
            'address_region_info' => $address_info['region_info'],
            'address_street' => $address_info['street'],
            'user_id' => $user_id,
            'timestamp' => $this->getTime(),
        );

//         $order_where = array(
//             'status' => 1,
//             'user_id' => $user_id,/.f
//         );
//         $order_info = $this->getOrderTable()->getOne($order_where);

//         if ($order_info) {
//             // 更新，只保留一条没有购买的订单防止冗余数据，除非订单列表以后展示未支付订单
//             $this->getOrderTable()->updateData($order_data, array('id'=>$order_info['id']));
//             $order_data['id'] = $order_info['id'];
//         }
//         else {
            $order_data['id'] = $this->getOrderTable()->insertData($order_data);
//         }
        return array('status' => STATUS_SUCCESS, 'info' => $order_data);
    }

    /**
     * 把订单标记为完成并且记录财务，并且发送短信通知，并且赠送优惠码
     * @param unknown $order_sn
     * @version 2015-11-24 WZ
     */
    public function orderUpdateStatus($order_sn, $payment_type) {
        $order_where = array(
            'order_sn' => $order_sn,
            'status' => 1 // 待支付
        );
        $order_info = $this->getOrderTable()->getOne($order_where);
        if (! $order_info) {
            exit();
        }
        if($order_info){
            $this->statOporation(4, $order_info['number']);
        }
        // 订单更新
        $this->getOrderTable()->updateData(array(
            'status' => 2 // 待处理
        ), $order_where);
        // 财务记录
        $fin_order = array(
            'type' => 1, // 订单
            'amount' => $order_info['total'],
            'payment_type' => $payment_type+1,
            'user_id' => $order_info['user_id'],
            'order_id' => $order_info['id']
        );
        $this->saveFinancial($fin_order);

        // 短信通知购买用户
        $order_user_info = $this->getUserTable()->getOne(array(
            'id' => $order_info['user_id']
        ));
        $smscode = new SMSCode();
        $content = sprintf(TEMPLATE_SMS_BUY, 8);
        $smscode->smsPush($content, array(
            $order_user_info['mobile']
        ));

        // 赠送八个优惠码
        for ($i = 1; $i <= 8; $i ++) {
            $code = $this->getApiController()->makeCode(6, 6); // 生成6位随机字母加数字为推荐码
            $set = array(
                'code' => $code,
                'user_id' => $order_info['user_id'],
                'timestamp' => $this->getTime()
            );
            $this->getInvitationCodeTable()->insert($set);
        }

        // 如果有用优惠码，对优惠码用户进行处理
        $user_number = array();
        if ($order_info['code_id']) {
            $codes = explode(',', $order_info['code_id']);
            foreach ($codes as $code) {
                $code_where = array(
                    'id' => $code
                );
                $code_info = $this->getInvitationCodeTable()->getOne($code_where);
                if ($code_info && 0 == $code_info['status']) {
                    // 邀请码状态改变
                    $this->getInvitationCodeTable()->updateData(array(
                        'status' => 1,
                        'timestamp_update' => $this->getTime(),
                        'recommended_user_id' => $order_info['user_id']
                    ), $code_where);
                    if (! isset($user_number[$code_info['user_id']])) {
                        $user_number[$code_info['user_id']] = 0;
                    }
                    $user_number[$code_info['user_id']]++;
                }
            }
            if ($user_number){
                foreach ($user_number as $user_id => $number) {
                    $amount = 30; // 返现金额

                    // 返现财务记录
                    $fin_code = array(
                        'type' => 2, // 返利
                        'amount' => $amount * $number, // 固定,
                        'payment_type' => 1, // 用户余额
                        'user_id' => $user_id,
                        'order_id' => $order_info['id']
                    );
                    $this->saveFinancial($fin_code, true);

                    $code_user_info = $this->getUserTable()->getOne(array(
                        'id' => $user_id
                    ));
                    if ($code_user_info) {
                        $this->getUserTable()->updateData(array(
                            'recommend_stat' => $code_user_info['recommend_stat'] + 1,
                            'recommend_bonus' => $code_user_info['recommend_bonus'] + $amount,
                            'money' => $code_user_info['money'] + $amount,
                            'is_buy' => 1
                        ), array(
                            'id' => $code_user_info['id']
                        ));

                        $content = sprintf(TEMPLATE_SMS_INVITATION, $number, $amount * $number);
                        $smscode->smsPush($content, array(
                            $code_user_info['mobile']
                        ));
                    }
                }
            }
        }
    }

    /**
     * 生成财务号或订单号
     * 规则为年月日时分秒+两位随机数
     *
     * @return string
     * @version 2015-8-11 WZ
     */
    public function makeSN() {
        $sn = date('YmdHis') . mt_rand(10, 99);
        return (string) $sn;
    }

    /**
     * 保存到财务表
     *
     * @param array $data
     * @param string $update
     * @return boolean
     * @version 2015-8-11 WZ
     */
    function saveFinancial(array $data) {
        $income_add = array(1);
        $income_sub = array(2,3);
        if (in_array($data['type'], $income_add) ) {
            $data['income'] = 1;
        }
        elseif (in_array($data['type'], $income_sub)) {
            $data['income'] = 2;
        }
        else {
            $data['income'] = 0;
        }
        $data['status'] = 1;
        $data['transfer_no'] = $this->makeSN();
        $data['timestamp'] = $this->getTime();
        $this->getFinancialTable()->insertData($data);

        return true;
    }
    public function statOporation($type,$value,$foreign_id=null)
    {
        $data=array();
    
        $date=date('Y-m-d',strtotime(date('Y-m-d',time())));
    
        if($type && $value)
        {
            if($foreign_id && ($type==5 || $type==6))
            {
                $data['foreign_id']=$foreign_id;
            }
            $data['type']=$type;
             
            $data['date']=$date;
             
            $stat=$this->getStatisticsDayTable()->getOne($data);
            if(!$stat)
            {
                $data['value']=$value;
                $data['timestamps']=$this->getTime();
                $this->getStatisticsDayTable()->insertData($data);
            }
            else
            {
                $this->getStatisticsDayTable()->updateKeys($date, 1, 'value', $value,array('type'=>$data['type'],'date'=>$date));
            }
        }
        else
        {
            return false;
        }
    }

}
