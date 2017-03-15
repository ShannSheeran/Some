<?php

namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Core\System\Uploadfile as Uploadfiles;
use Core\System\Image;
use Zend\Captcha\Image as imageCaptcha;
use Core\System\WxApi\WxApi;
use Zend\Db\Sql\Where;
use Core\System\WxApi\JSSDK;
use Core\System\phpqrcode\QRcode;
use Core\System\File;
use Core\System\UploadfileApi;
use Api\Controller\CommonController as api;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;
use Admin\Controller\FinancialController;

class CommonController extends TableController
{
	protected $table; // 表对象str
	
	/**
	 * 后台列表专用
	 * 后台列表条件array
	 * 
	 * @var array
	 */
	protected $where;
	
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
	 * 字段条件筛选 字段
	 *
	 * @var string
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
	 * 面包屑导航设置
	 *
	 * @var array(array('url'=>"","title"=>),array('url'=>'','title'=>''))
	 */
	protected $breadcrumb;
	
	/**
	 * 微信接口
	 * 
	 * @var integer 1设备列表接口 2 页面接口
	 */
	protected $interfaceType;
	
	/**
	 * 上传图片时的key
	 */
	public $file_key;
	
	/**
	 */
	public $head_icon;
	
	/**
	 * 登录判断
	 * 
	 * @param string $action_list
	 *        	权限列表
	 * @return boolean
	 */
	protected function checkLogin($action_list = '')
	{
		if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_name']))
		{
			if (! $this->admin_priv($action_list))
            {
                echo "<script>alert('对不起，你无此操作权限！！');history.back(-1);</script>";
                exit();
            }
            return true;
		}
		else
		{
			$this->redirect()->toRoute('admin-index', array(
					'action' => 'login' 
			));
		}
	}
	/**
	 * 用户登出
	 * 
	 * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
	 */
	protected function quit()
	{
		session_destroy();
		return $this->redirect()->toRoute('admin-index', array(
				'action' => 'login' 
		));
	}
	
	/**
	 * 设置模板跟菜单
	 * 
	 * @param unknown $mainView        	
	 * @param string $controller        	
	 * @return \Zend\View\Model\ViewModel
	 */
	protected function setMenu($mainView, $controller = '')
	{
		if (! $this->breadcrumb)
		{
			// /throw new \DomainException('未定义面包屑导航数组！');
			$this->breadcrumb = array(
					array(
							'url' => '',
							'title' => '' 
					),
					array(
							'url' => '',
							'title' => '' 
					) 
			);
		}

		/* $where1 = new Where();
		$where1->in('audit_status',array('0'=>0,'1' => 1));
		$where1->equalTo('delete', 0);
		$order = $this->getViewOrderTable()->countData(array('status'=>2, 'delete' => 0));
		$company = $this->getCompanyTable()->countData($where1);
		$total = $order+$company;
		$count = array(
				'AuditsCount' => array(),
				'TaskReportCount' => array(),
				'FinanacialCount' => array(),
				'messageCount' => array(),
				'wbReportCount' => array(),
				'CommentReportCount' => array() 
		); */
		
		$admin_info = $this->getAdminTable()->getOne(array('id' => $_SESSION['admin_id'] , 'delete' => DELETE_FALSE));
		if( $admin_info->admin_category_id == 1)
		{
		    $action_list = 'all';
		}
		else
		{
		    $action_list = $this->getAdminCategoryTable()->getOne(array('id' => $admin_info->admin_category_id , 'delete' => DELETE_FALSE));
		    $action_list = $action_list->action_list;
		}
		
		$menuView = new ViewModel(array(
				'controller' => $controller,
				'count' => 0,
				'breadcrumb' => $this->breadcrumb,
				'order1' => 0,
				'company1' => 0,
				'total_num' => 0,
		        'action_list' => explode(',', $action_list),
		));
		$menuView->setTemplate('layout/menu');
		$menuView->addChild($mainView, 'main');
		
		$layout = new ViewModel(array(

		));
		$layout->setTemplate('layout/layout');
		$layout->addChild($menuView, 'content');
		return $layout;
	}
	
	/**
	 * 后台列表
	 * 调用此方法实现列表查询,调用此方法前需向属性table传入表对像 如$this->table = $this->getUserTable();
	 * $this->where 属性为外部输入条件
	 * $this->action 分页用的访问方法名 如果是index 可不设置
	 * $this->template (0=>模板目录/模板,1=>菜单对应高亮的变量) 必设置
	 * $this->screening 条件筛选字段 字段1
	 * $this->seach 关键字要搜索的字段 array('字段1','字段2')
	 * $this->other 其它要传入模板的数据 array('模板变量'=>值)
	 * $this->delet 是否要查已删除的字段 true 不查(默认)
	 * $this->order 排序字段
	 *
	 * @return array $list 列表数据
	 */
	public function getList()
	{
		$this->checkLogin();
		if (! $this->table)
		{
			throw new \DomainException('未定义表对像！');
		}
		$this->isList = true;
		$page = $this->params()->fromRoute('page', 1);
		$cid = $this->params()->fromRoute('cid', 0);
		$keyword = $this->params()->fromRoute('keyword') ? trim($this->params()->fromRoute('keyword')) : '';
		$like = null;
		
		if ((isset($_POST['submit']) && $_POST['keyword'] != '') || $keyword)
		{
			$keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : $keyword;
			if ($keyword && is_array($this->seach))
			{
				foreach ( $this->seach as $v )
				{
					$like[$v] = $keyword;
				}
			}
		}
		
		$where = null;
		
		if (($cid && $this->screening) || ($cid == 0 && $this->screening))
		{
			$where[$this->screening] = $cid; // 要筛选的字段
		}
		
		if ($this->delete && ! is_object($this->where))
		{
			$where['delete'] = 0;
		}
		
		if ($where && is_array($this->where))
		{
			$where = array_merge($where, $this->where);
		}
		elseif (is_object($this->where))
		{
			$where = $this->where;
		}
		else if (! $where && is_array($this->where))
		{
			$where = $this->where;
		}
		
		$list = $this->table->getAll($where, null, $this->order, true, $page, 10, $like);

		$date = array();
		
		if ($this->other && is_array($this->other))
		{
			foreach ( $this->other as $k => $v )
			{
				$date[$k] = $v;
			}
		}
		
		$date_two = array(
				'paginator' => $list['paginator'],
				'list' => $list['list'],
				'condition' => array(
						'action' => $this->action,
						'cid' => $cid,
						'page' => $page,
						'where' => $where 
				) // 提交的where参数用get参数传递
,
				'where' => $where,
				'cid' => $cid,
				'page' => $page,
				'keyword' => $keyword 
		);
		if($keyword)
		{
			$date_two['condition']['keyword'] = $keyword;
		}
		if (! $this->action || $this->action == 'index')
		{
			unset($date_two['condition']['action']);
		}
		
		$view_date = array_merge($date, $date_two);
		$view = new ViewModel($view_date);
		$view->setTemplate('admin/' . $this->template[0]);
		return $this->setMenu($view, $this->template[0]);
	}
	
	/**
	 * 删除一条记录 需指定表对像 $this->table
	 */
	public function deleteDate()
	{
		$this->checkLogin();
		if (! $this->table)
		{
			throw new \DomainException('未定义表对像！');
		}
		$id = ( int ) $this->params()->fromRoute('id');
		$url = $_SERVER['HTTP_REFERER'];

		if ($id)
		{

			if ($this->table->update(array('delete' => 1), array('id' => $id)))
			{
				echo "<script>window.location.href='".$url."'</script>";
			}
		}
	}
	
	/**
	 * 判断管理员对某一个操作是否有权限。
	 *
	 * 根据当前对应的action_code，然后再和用户session里面的action_list做匹配，以此来决定是否可以继续执行。
	 * 
	 * @param string $priv_str
	 *        	操作对应的priv_str
	 * @return true/false
	 */
	protected function admin_priv($priv_str)
	{
		if ($_SESSION['action_list'] == 'all')
		{
			return true;
		}
		
		if (strpos(',' . $_SESSION['action_list'] . ',', ',' . $priv_str . ',') === false)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * 2014/08/08
	 * 
	 * @author liujun
	 *         获到当前时间用于插入数据库里的timestamp字段
	 * @return string
	 */
	protected function getTime()
	{
		return date("Y-m-d H:i:s");
	}

	/**
	 * 格式化 region_info 字段（转为JSON，用于插入数据库）
	 *
	 * @author liujun
	 * @param integer $county
	 *            区域ID
	 * @param integer $city
	 *            城市ID
	 * @param integer $province
	 *            省份直辖市ID
	 * @return string region_info JSON数据
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
	
	/**
	 * 将数组转换为JSON字符串（兼容中文）
	 *
	 * @param array $array
	 * @return string
	 * @access public
	 */
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
	
	/**
	 * 解析 region_info 字段（转为数组，用于模板数据）
	 *
	 * @author liujun
	 * @param string $result
	 *            数据库region_info JSON数据
	 * @return array array('province'=>省信息数组,'city'=>市信息数组，'county'=>区信息数组)
	 * @version 2015/04/14 WZ 大改
	 */
	protected function decode($result)
	{
	    $result_info = array();
	    $result = json_decode($result,true);
	    if (is_array($result)) {
	        foreach ($result as $key => $value)
	        {
	            $value = $value['region'];
	            if (isset($value['parent_id'])) {
	                $value['parentId'] = $value['parent_id'];
	                unset($value['parent_id']);
	            }
	            $result_info[]['region'] = $value;
	        }
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
        $result = '';
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
		$captcha->setImgDir('public/' . UPLOAD_PATH . 'captcha/'); // 验证码图片放置路径
		$captcha->setImgUrl(ROOT_PATH . 'uploadfiles/captcha/');
		$captcha->setWordlen(5);
		$captcha->setFontSize(30);
		$captcha->setLineNoiseLevel(5); // 随机线
		$captcha->setDotNoiseLevel(50); // 随机点
		$captcha->setExpiration(10); // 图片回收有效时间
		$captcha->generate(); // 生成验证码
		$_SESSION['captcha'] = $captcha->getWord();
		echo $captcha->getImgUrl() . $captcha->getId() . $captcha->getSuffix(); // 图片路径
		die();
	}
	
	/**
	 * 错误提示信息输出
	 *
	 * @param string $message
	 *        	提示信息
	 * @param integer $type
	 *        	是否要后退一页
	 */
	public function showMessage($message, $type = true)
	{
		$location = $type ? "history.back(-1);" : '';
		echo "<script type='text/javascript'>alert('{$message}');{$location}</script>";
		die();
	}

	/**
	 * 前端接收表单文件域传过来文件 用于上传文件处理 4:3
	 * 
	 * @author liujun
	 * @return string 用于模板页面JS处理
	 */
	public function getAdminFileAction()
	{
		if (isset($_FILES) && $_FILES['Filedata']['error'] == 0 && $this->check_file_type($_FILES['Filedata']['tmp_name']))
		{
			/* $ima_info = getimagesize($_FILES['Filedata']['tmp_name']);
			if ($ima_info[0] != $ima_info[1])
			{
				$path = '';
				$imgid = '';
				$error = '图片比例不正确，请上传200X200以下像素的正方形图片！';
			}
			else
			{ */
				$data = $this->uploadImageForController('Filedata');
				$path = ROOT_PATH . UPLOAD_PATH . $data['files'][0]['Filedata']['path'] . $data['files'][0]['Filedata']['filename'];
				$imgid = $data['files'][0]['Filedata']['id'];
				if (! $imgid)
				{
					$error = '上传失败，未知错误！';
				}
				else
				{
					$error = '';
				}
			/* } */
			
			$return = array('error' => $error, 'path' => $path, 'imgid' => $imgid);
			echo json_encode($return);
			// echo "{";
			// echo "error: '" . $error . "',\n";
			// echo "path: '" . $path . "',\n";
			// echo "imgid: '" . $imgid . "'\n";
			// echo "}";
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
		// die();
	}
	
	
	/**
	 * 前端接收表单文件域传过来文件 用于上传文件处理 4:3
	 *
	 * @author liujun
	 * @return string 用于模板页面JS处理
	 */
	public function getFileAction()
	{
	    if (isset($_FILES) && $_FILES['file']['error'] == 0 && $this->check_file_type($_FILES['file']['tmp_name']))
	    {
	        
            $data = $this->uploadImageForController('file');
            $path = ROOT_PATH . UPLOAD_PATH . $data['files'][0]['file']['path'] . $data['files'][0]['file']['filename'];
            $imgid = $data['files'][0]['file']['id'];
            if (! $imgid)
            {
                $error = '上传失败，未知错误！';
            }
            else
            {
                $error = '';
            }
	         die("{\"jsonrpc\" : \"2.0\", \"result\" : null, \"id\" : \"$imgid\", \"exist\": 1}");
	    }
	    else
	    {
	        $error = '文件类型不正确，或未选择上传图片！';
	        die("{\"jsonrpc\" : \"2.0\", \"error\" : {\"code\": 100, \"message\": \"$error\"}, \"id\" : \"0\"}");
	    }
	      die("{\"jsonrpc\" : \"2.0\", \"error\" : {\"code\": 100, \"message\": \"未选择文件\"}, \"id\" : \"0\"}");
	}
	
	/**
	 * 前端接收表单文件域传过来文件 用于上传文件处理 4:3
	 * 
	 * @author liujun
	 * @return string 用于模板页面JS处理
	 */
	public function getAdminFileTwoAction()
	{

		if (isset($_FILES) && $_FILES['Filedata']['error'] == 0 /*&& $this->check_file_type($_FILES['File']['tmp_name'])*/)
		{
			//$ima_info = getimagesize($_FILES['File']['tmp_name']);
			
			$data = $this->uploadImageForController('Filedata');
			$path = ROOT_PATH . UPLOAD_PATH . $data['files'][0]['Filedata']['path'] . $data['files'][0]['Filedata']['filename'];
			$imgid = $data['files'][0]['Filedata']['id'];
			if (! $imgid)
			{
				$error = '上传失败，未知错误！';
			}
			else
			{
				$error = '';
				$url = "http://api.wwei.cn/dewwei.html?data=" . "http://www.kuaiyao.name" . $path . "&apikey=20160118172115";
				$json_data = file_get_contents($url);
				$data = json_decode($json_data);
				if (isset($data->status) && $data->status == 1)
				{
					$text = $data->data->raw_text;
					$imgInfo = $this->generationQRcode($text);
					$imgid = $imgInfo['id'];
					$path = $imgInfo['path'];
				}
				else
				{
					$error = '二维码识别失败!';
				}
			}
			
			echo "{";
			echo "error: '" . $error . "',\n";
			echo "path: '" . $path . "',\n";
			echo "imgid: '" . $imgid . "'\n";
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
		// die();
	}


	public function checkSize($size)
	{
		if($size>1*1024*1024)
		{
			$this->showMessage("只能上传小于1M的图片");
		}
	}
	/**
	 * 检查图片文件类型
	 *
	 * @access public
	 * @param
	 *        	string filename 文件名（地址）
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
	 * 上传文件总入口
	 *
	 * @param $_FILES $file        	
	 * @param string $file_key
	 *        	post过来的key
	 * @return Ambigous <\Api\Controller\multitype:multitype:multitype:multitype:unknown, multitype:multitype:multitype:multitype:unknown multitype:string unknown >
	 * @version 2014-12-6 WZ
	 */
	public function uploadImageForController($file_key)
	{
		$this->file_key = $file_key;
		$data = array();
		if (! isset($_FILES[$this->file_key]))
		{
			return array(
					'ids' => array(),
					'files' => array() 
			);
		}
		if (is_array($_FILES[$this->file_key]['name']))
		{
			foreach ( $_FILES[$this->file_key]['name'] as $key => $value )
			{
				if (! $_FILES[$this->file_key]['error'][$key])
				{
					$source_file = array(
							$this->file_key => array(
									'name' => array(
											$_FILES[$this->file_key]['name'][$key] 
									),
									'type' => array(
											$_FILES[$this->file_key]['type'][$key] 
									),
									'tmp_name' => array(
											$_FILES[$this->file_key]['tmp_name'][$key] 
									),
									'error' => array(
											$_FILES[$this->file_key]['error'][$key] 
									),
									'size' => array(
											$_FILES[$this->file_key]['size'][$key] 
									) 
							) 
					);
					$data[] = $this->checkFileMd5($source_file);
				}
			}
		}
		else
		{
			if (! $_FILES[$this->file_key]['error'])
			{
				$source_file = array(
						$this->file_key => array(
								'name' => array(
										$_FILES[$this->file_key]['name'] 
								),
								'type' => array(
										$_FILES[$this->file_key]['type'] 
								),
								'tmp_name' => array(
										$_FILES[$this->file_key]['tmp_name'] 
								),
								'error' => array(
										$_FILES[$this->file_key]['error'] 
								),
								'size' => array(
										$_FILES[$this->file_key]['size'] 
								) 
						) 
				);
				$data[] = $this->checkFileMd5($source_file);
			}
		}
		
		$files = $this->saveFileInfo($data);
		return $files;
	}
	
	/**
	 * 通过对图片的md5验证，查看图片是否存在，<br />
	 * 如果存在返回数据库中的图片信息，<br />
	 * 如果不存在，上传新图片，再返回图片信息<br />
	 *
	 * @param array $source_file        	
	 * @return array|Ambigous number string >
	 * @version 2014-12-6 WZ
	 */
	public function checkFileMd5($source_file)
	{
		if (is_array($source_file[$this->file_key]['tmp_name']))
		{
			if (isset($source_file[$this->file_key]['data'][0]))
			{
				$content = $source_file[$this->file_key]['data'][0];
			}
			else
			{
				$content = $this->getUrlImage($source_file[$this->file_key]['tmp_name'][0]);
				$source_file[$this->file_key]['data'][0] = $content;
			}
		}
		else
		{
			if (isset($source_file[$this->file_key]['data']))
			{
				$content = $source_file[$this->file_key]['data'];
			}
			else
			{
				$content = $this->getUrlImage($source_file[$this->file_key]['tmp_name']);
				$source_file[$this->file_key]['data'] = $content;
			}
		}
		$md5 = md5($content);
		$data = $this->getImageTable()->getOne(array(
				'md5' => $md5 
		));
		if ($data)
		{
			return ( array ) $data;
		}
		else
		{
			$data = $this->Uploadfile(LOCAL_SAVEPATH, true, 1, 2048, $source_file);
			return $data[0];
		}
	}
	
	/**
	 * 获取图片内容
	 * 
	 * @param unknown $path        	
	 * @return mixed
	 * @version 2014-12-16 WZ
	 */
	public function getUrlImage($path)
	{
		if (preg_match('/http\:\/\//i', $path))
		{
			$cookie_file = tempnam('./temp', 'cookie');
			$url = $path;
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
			$content = curl_exec($ch);
		}
		else
		{
			$content = file_get_contents($path);
		}
		
		return $content;
	}
	
	/**
	 * 结果保存到数据库
	 * 
	 * @param unknown $data        	
	 * @return multitype:multitype:multitype:multitype:unknown multitype:string unknown
	 * @version 2014-12-6 WZ
	 */
	public function saveFileInfo($data)
	{
		$ids = array();
		$files = array();
		foreach ( $data as $key => $value )
		{
			if (! isset($value['id']) && isset($value['filename']) && isset($value['path']) && $value['filename'] && $value['path'])
			{
				$value['timestamp'] = $this->getTime();
				$id = $this->getImageTable()->insertData($value);
				$ids[] = $id;
				$files[] = array(
						$this->file_key => array(
								'id' => $id,
								'path' => $value['path'],
								'filename' => $value['filename'] 
						) 
				);
			}
			else
			{
				$this->getImageTable()->updateKey($value['id'], 1, 'count', 1);
				$ids[] = $value['id'];
				$files[] = array(
						$this->file_key => array(
								'id' => $value['id'],
								'path' => $value['path'],
								'filename' => $value['filename'] 
						) 
				);
			}
		}
		
		return array(
				'ids' => $ids,
				'files' => $files 
		);
	}
	
	/**
	 * 上传文件处理
	 *
	 * @author liujun
	 * @param string $pash
	 *        	要上传到的文件夹 默认为public 下的uploadfiles/年月命名的文件夹（此文件夹为大图文件夹）
	 * @param boolean $is_thumb
	 *        	是否生成缩略图 默认为否false，true为是
	 * @param integer $filetype
	 *        	1,为图片类;2,swf类;3,音频类;4,文本文件类;5,可执行文件类; 默认为 1图片类
	 * @param integer $size
	 *        	设置上传最大文件的大小（与PHP配置文件有关）此项默认为：2M
	 * @return array $array array('filename','path','size','mime','extension')
	 */
	public function Uploadfile($path = LOCAL_SAVEPATH, $is_thumb = true, $filetype = 1, $size = 2048, $source_file = array())
	{
		set_time_limit(0);
		$upload = new UploadfileApi($path, $size, $filetype, 'Ym/d');
		if ($source_file)
		{
			$upload->setFiles($source_file);
		}
		$upload->uploadfile();
		$filename = $upload->getUploadFileInfo();
		$path = $upload->imgPath;
		// $extension = substr($name, (strrpos($name, '.') + 1));
		
		$results = array();
		
		$thumb = new Image();
		
		if (! is_array($filename[$this->file_key]['new_name']))
		{
			foreach ( $filename[$this->file_key] as $f_key => $f_value )
			{
				$filename[$this->file_key][$f_key] = array(
						$f_value 
				);
			}
		}
		foreach ( $filename[$this->file_key]['new_name'] as $key => $value )
		{
			$name = substr($filename[$this->file_key]['new_name'][$key], strrpos($filename[$this->file_key]['new_name'][$key], '/') + 1);
			
			if ($filename[$this->file_key]['size'][$key] > 0)
			{
				$results[] = array(
						'filename' => $name,
						'path' => $path,
						'md5' => $filename[$this->file_key]['md5'][$key],
						'width' => $filename[$this->file_key]['width'][$key],
						'height' => $filename[$this->file_key]['height'][$key],
						'count' => 1 
				);
			}
        }

        return $results;
    }

    
    
    /**
     * 获取API CommonControler控制器
     * @version 2015-6-26
     * @author liujun
     */
    public function getApiController()
    {
    	return $obj = new api();
    }
    
    public function getAdminFinancialController()
    {
    	return $obj = new FinancialController();
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
    
    public function filterWords($words)
    {
        return addslashes(trim($words));
    }
    
    public function dump($expression)
    {
        echo '<pre>';
        var_dump($expression);
        echo '</pre>';
    }
    
    /**
     * 企业类型
     * @return multitype:string
     */
    protected function enterprisType()
    {
        return array(1=>'经销商',2=>'供应商');
    }
    
    /**
     * 用户等级
     * @return multitype:string
     */
    protected function userLevel()
    {
        return array(1=>'普通会员',2=>'VIP会员');
    }
    
    /**
     * 学历
     */
    public function  education()
    {
        return array(1=>'中专及以下',2=>'大专',3=>'本科',4=>'硕士',5=>'博士');
    }
    
    /**
     * 工作年限
     */
    public function yearsOfWorking()
    {
        return array(1=>'应届毕业生',2=>'1-2年',3=>'2-3年',4=>'3-5年',5=>'5-10年',6=>'10年');
    }
    
    /**
     * 商品分类类型
     * @return multitype:string 
     */
    public function categoryType()
    {
        return array(1=>'盆栽',2=>'资材');
    }
    
    /**
     * 商品上架方式
     * @return multitype:string
     */
    public function onSaleType()
    {
        return array('0'=>'现货','1'=>'预售');
    }
    
    /**
     * 商品标签
     * @return multitype:string
     */
    public function referrerType()
    {
        return array('0'=>'普通','1'=>'新品上市','2'=>'促销商品','3'=>'推荐购买');
    }
    
    /**
     * 商品状态
     * @return multitype:string
     */
    public function goodsStatus()
    {
        return array('1'=>'待审核','2'=>'已审核','3'=>'出售中', '4'=>'已下架', '5'=>'已取消', '6'=>'审核失败');
    }
    
    /**
     * 会员审核状态
     */
    public function userAuditStatus()
    {
        return array(1=> '待审核' , 2 =>'临时保存' , 3 => '审核通过',4=>'审核不通过' ,);
    }
    
    /**
     * 订单状态
     */
    public function orderStatus()
    {
        return array(1=>'待付款' , 2 => '待审核' ,  3 =>'待发货', 4=>'待收货' , 5=> '已完成' ,6=> '已取消', 7=>'审核失败');
    }
    
    /**
     * 支付类型
     */
    public function payType()
    {
        return array(1 =>'余款支付',2=>'转账支付');
    }
    
    /**
     * 财务类型
     */
    public function financialType()
    {
        return array( 1 => '充值', 2=>'优惠',  3=>'账户扣款', 4 => '账户红冲', 5 =>'订单扣款', 6=>'订单退款');
    }
    
    /**
     * 售后服务问题类型
     */
    public function serviceType()
    {
        return array(1=>'商品质量',2=>'物流问题',3=>'其他问题');
    }
    
    /**
     * 售后服务状态
     */
    public function serviceStatus()
    {
        return array('1'=>'待处理','2'=>'已处理','3'=>'申请失败');
    }
    
    /**
     * 留言类型
     */
    public function messageType()
    {
        return array(1=>'商品咨询',2=>'普通留言', 3=>'app反馈');
    }
    
    /**
     * 留言是否已回复
     */
    public function messageIsRead()
    {
        return array('1'=>'未回复','2'=>'已回复');
    }
    
    /**
     * 市场反馈状态
     */
    public function marketFeedbackStatus()
    {
        return array('1'=>'未回复','2'=>'已回复');
    }
     
}
