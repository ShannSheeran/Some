<?php
namespace Index\Controller;

use Zend\View\Model\ViewModel;
use Core\System\Uploadfile as Uploadfiles;
use Core\System\Image;
use Zend\Captcha\Image as imageCaptcha;
use Api\Controller\CommonController as Api;
use Admin\Controller\CommonController as AdminController;
use Admin\Controller\TableController;
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
    public function login($type = '')
    {
        if (!isset($_SESSION['index_user_id']) || !isset($_SESSION['index_name']))
        {
            session_destroy();
            return false;
        }
        else 
        {
            return true;
        }
    }

    public function clearCookie() {

        setcookie('mobile','',time() - 600);
        setcookie('name','',time() - 600);
        setcookie('index_user_id','',time() - 600);
        setcookie('code','',time() - 600);
        setcookie('mobile','',time() - 600,ROOT_PATH);
        setcookie('name','',time() - 600,ROOT_PATH);
        setcookie('index_user_id','',time() - 600,ROOT_PATH);
        setcookie('code','',time() - 600,ROOT_PATH);
        session_destroy();//9.16
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
        return $this->redirect()->toRoute('index', array('controller' => 'index', 'action' => 'index' ));
    }

    public function memoryUrl()
    {
        if(isset($_COOKIE['index_user_id']) && $_COOKIE['index_user_id'])
        {
            setcookie('thisUrl', '10',time()+10,ROOT_PATH);
        }
        else
        {
            setcookie('thisUrl', '',time()+10,ROOT_PATH);
            setcookie('thisUrl' ,$_SERVER['REQUEST_URI'],time()+3600*24*7,ROOT_PATH);
        }

    }

    public function redirectTo($url)
    {
        echo "<script>window.location.href='{$url}';</script>";
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
     * 设置模板跟菜单
     * @param $type 不传或者传s为普通layout，传其他为登陆页layout
     *
     * @param unknown $mainView
     * @param string $controller
     * @return \Zend\View\Model\ViewModel
     */
    protected function setMenu($mainView, $type = '' , $action="index")
    {
        $user_info = array();
        if(isset($_SESSION['index_user_id']))
        {
            $user_info = $this->getUserTable()->getOne(array('id' => $_SESSION['index_user_id']));
        }
        
        $list = $this->getSetupTable()->fetchAll();
        $setting = array();
        foreach ($list as $value)
        {
            $setting[$value->id] =$value;
        }
        
        $layout = new ViewModel(array(
            'user_info' => $user_info,
            'action' => $action,
            'setting' => $setting,
        ));
        if ($type == 1) {
            $layout->setTemplate('layout_index/layout_index');
            $menuView = new ViewModel(array(
                'user_info' => $user_info,
                'action' => $action,
                'setting' => $setting,
            ));
            $menuView->setTemplate('layout_index/menu');
            $menuView->addChild($mainView, 'main');
            $layout->addChild($menuView, 'content');
        }
        elseif ($type == 2) {
            $layout->setTemplate('layout_index/layout_index2');
            $menuView = new ViewModel(array(
                'action' => $action
            ));
            $menuView->setTemplate('layout_index/menu2');
            $menuView->addChild($mainView, 'main');
            $layout->addChild($menuView, 'content');
        }
        elseif ($type == 3)
        {
            $layout->setTemplate('layout_index/layout_index');
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
protected function encode($county, $city, $province)
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
	        );
	    }
	    if ($city > 1)
	    {
	        $res = $this->getRegionTable()->getOne(array(
	            'id' => $city
	        ));
	        $city_info = array(
	            "id" => $res->id,
	            "name" => $res->name,
	            "parent_id" => $res->parent_id,
	            "pinyin" => $res->pinyin
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
	            "id" => $res->id,
	            "name" => $res->name,
	            "parent_id" => $res->parent_id,
	            "pinyin" => $res->pinyin
	        );
	        $region_info[] = array(
	            "region" => $county_info
	        );
	    }
	    return $this->JSON($region_info);
	}
	
	protected function JSON($array)
	{
	    $this->arrayRecursive($array, 'urlencode', true);
	    $json = json_encode($array);
	    return urldecode($json);
	}
	
	/**
	 *
	 *
	 *
	 * 使用特定function对数组中所有元素做处理
	 *
	 * @param
	 *            string	&$array		要处理的字符串
	 * @param string $function
	 * @return boolean
	 * @access public
	 *
	 *
	 */
	protected function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
	{
	    static $recursive_counter = 0;
	    if (++ $recursive_counter > 1000)
	    {
	        die('possible deep recursion attack');
	    }
	    foreach ($array as $key => $value)
	    {
	        if (is_array($value))
	        {
	            $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
	        }
	        else
	        {
	            $array[$key] = $function($value);
	        }
	
	        if ($apply_to_keys_also && is_string($key))
	        {
	            $new_key = $function($key);
	            if ($new_key != $key)
	            {
	                $array[$new_key] = $array[$key];
	                unset($array[$key]);
	            }
	        }
	    }
	    $recursive_counter --;
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

    public function apiExit($msg)
    {
        echo json_encode($msg);
        die();
    }

    function domainRedirection($url, $time=0, $msg='')
    {
        $url        = str_replace(array("\n", "\r"), '', $url);
        if (empty($msg))
            $msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
        if (!headers_sent())
        {
            // redirect
            if (0 === $time)
            {
                header('Location: ' . $url);
            }
            else
            {
                header("refresh:{$time};url={$url}");
                echo($msg);
            }
            exit();
        }
        else
        {
            $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
            if ($time != 0)
                $str .= $msg;
            exit($str);
        }
    }

    /**
     * 这个函数用来获得客户端IP
     * @return Ambigous <string, unknown>
     * @version 2016年6月6日
     * @author liujun
     */
    public  function getClientIp()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
    
    function dump($expression)
    {
        echo '<pre>';
        var_dump($expression);
        echo '</pre>';
    }
    
    /**
     * 售后服务问题类型
     */
    public function serviceType()
    {
        return $this->getAdminController()->serviceType();
    }
    
    /**
     * 售后服务状态
     */
    public function serviceStatus()
    {
        return $this->getAdminController()->serviceStatus();
       // return array('1'=>'待处理','2'=>'已处理','3'=>'申请失败');
    }
    
    /**
     * 留言类型
     */
    public function messageType()
    {
        return $this->getAdminController()->messageType();
        //return array(1=>'商品咨询',2=>'普通留言', 3=>'app反馈');
    }
    
    /**
     * 商品状态
     * @return multitype:string
     */
    public function goodsStatus()
    {
        return $this->getAdminController()->goodsStatus();
        //return array('1'=>'待审核','2'=>'已审核','3'=>'出售中', '4'=>'已下架', '5'=>'已取消', '6'=>'审核失败');
    }
    
    /**
     * 订单状态
     */
    public function orderStatus()
    {
        return $this->getAdminController()->orderStatus();
        //return array(1=>'待付款' , 2 => '待审核' ,  3 =>'待发货', 4=>'待收货' , 5=> '已完成' ,6=> '已取消', 7=>'审核失败');
    }

}
