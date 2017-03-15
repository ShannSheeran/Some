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
	 * 实例化微信API
	 * 
	 * @return \Core\System\WxApi\WxApi
	 * @version 2015-5-28
	 * @author liujun
	 */
	protected function getWxApi()
	{
		return $wxApi = new WxApi();
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
		
		$count = array(
				'AuditsCount' => array(),
				'TaskReportCount' => array(),
				'FinanacialCount' => array(),
				'messageCount' => array(),
				'wbReportCount' => array(),
				'CommentReportCount' => array() 
		);
		$menuView = new ViewModel(array(
				'controller' => $controller,
				'count' => $count,
				'breadcrumb' => $this->breadcrumb 
		));
		$menuView->setTemplate('layout/menu');
		$menuView->addChild($mainView, 'main');
		
		$layout = new ViewModel();
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
		return $this->setMenu($view, $this->template[1]);
	}
	
	/**
	 * (本地数据库更新) 列表数据与微信同布
	 *
	 * @param $begin =
	 *        	0;//起始索引值
	 * @param $count =
	 *        	50;//查询条数
	 * @version 2015-6-2
	 * @author liujun
	 */
	public function updateList()
	{
		$type = $this->interfaceType;
		$postJson = json_encode(array(
				'begin' => 0,
				'count' => 1
		));
		$wxApi = new WxApi();
		switch ($type)
		{
			case 1 : // 设备列表
				$data = $wxApi->wxDeviceSearch($postJson);
				$end = $data['data']['total_count'];
				$this->table = $this->getDeviceTable();
				for($i=0; $i<=$end; $i =$i+50)
				{
					$this->setDdeviceData($i);
				}
				break;
			case 2 : // 页面列表
				$data = $wxApi->wxPageSearch($postJson);
				
				$this->table = $this->getPageTable();
				$param = array(
						'page_id',
						'title',
						'description',
						'page_url',
						'comment',
						'icon_url' 
				);
				$localData = $this->table->getAll(null, $param); // 本地数据
				
				if ($data['errcode'] == 0 && $data['data']['total_count'])
				{
					$localDataArray = array();
					$page_id_array = array();
					foreach ( $localData['list'] as $v )
					{
						$localDataArray[$v->page_id] = ( array ) $v;
						$page_id_array[$v->page_id] = $v->page_id;
					}
					
					foreach ( $data['data']['pages'] as $v )
					{
						if (array_key_exists($v['page_id'], $page_id_array))
						{ // 数据库存在更新
							
							if (array_diff_assoc($localDataArray[$v['page_id']], $v))
							{ // 与数据库数据不同,执行更新操作
								$this->table->updateData($v, array(
										'page_id' => $v['page_id'] 
								));
							}
						}
						else
						{ // 不存在插入数据
							foreach ( $param as $val )
							{
								$set[$val] = $v[$val] ? $v[$val] : '';
							}
							$set['timestamp'] = $this->getTime();
							$this->table->insert($set);
						}
					}
					/* if ($data['data']['total_count'] > $begin)
					{
						$begin = $begin + $count;
						$this->updateList($begin, $count);
					} */
				}
				break;
		}
	}
	
	/**
	 * 设置数据处理
	 * @version 2015年8月19日 
	 * @author liujun
	 */
	public function setDdeviceData($begin = 0)
	{
	    $postJson = json_encode(array(
	        'begin' => $begin,
	        'count' => 50
	    ));
	    $wxApi = new WxApi();
	    $data = $wxApi->wxDeviceSearch($postJson);
	    $param = array(
	        'comment',
	        'device_id',
	        'major',
	        'minor',
	        'page_ids',
	        'status',
	        'poi_id',
	        'uuid'
	    );
	    $localData = $this->table->getAll(null, $param); // 本地数据
	    
	    if ($data['errcode'] == 0 && $data['data']['total_count'])
	    {
	        $localDataArray = array();
	        $device_id_array = array();
	        foreach ( $localData['list'] as $v )
	        {
	            $localDataArray[$v->device_id] = ( array ) $v;
	            $device_id_array[$v->device_id] = $v->device_id;
	        }
	        	
	        foreach ( $data['data']['devices'] as $v )
	        {
	            if (array_key_exists($v['device_id'], $device_id_array))
	            { // 数据库存在更新
	                	
	                if (array_diff_assoc($localDataArray[$v['device_id']], $v))
	                { // 与数据库数据不同,执行更新操作
	                    unset($v['last_active_time']);
	                    $this->table->updateData($v, array(
	                        'device_id' => $v['device_id']
	                    ));
	                }
	            }
	            else
	            { // 不存在插入数据
	                foreach ( $param as $val )
	                {
	                    $set[$val] = isset($v[$val]) && $v[$val] ? $v[$val] : '';
	                }
	                $set['timestamp'] = $this->getTime();
	                $this->table->insert($set);
	            }
	        }
	        unset($localData,$data,$localDataArray);
	    }
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
			if ($this->table->update(array(
					'delete' => 1 
			), array(
					'id' => $id 
			)))
			{
				echo "<script>window.location.href='$url'</script>";
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
	 * 格式化
	 * region_info
	 * 字段（转为JOSN，用于插入数据库）
	 *
	 * @author liujun
	 * @param integer $county
	 *        	区域ID
	 * @param integer $city
	 *        	城市ID
	 * @param integer $province
	 *        	省份直辖市ID
	 * @return string region_info
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
	
	/**
	 * 解析 region_info 字段（转为数组，用于模板数据）
	 *
	 * @author liujun
	 * @param string $result
	 *        	数据库region_info JSON数据
	 * @return array array('region'=>省信息数组,'region'=>市信息数组，'region'=>区信息数组)
	 */
	public function decode($result)
	{
		$result_info = array();
		$result = json_decode($result);
		if (isset($result[0]->region->id))
		{
			$province = array(
					'id' => $result[0]->region->id,
					'name' => $result[0]->region->name,
					'parentId' => '1',
					'pinyin' => $result[0]->region->pinyin 
			);
			$result_info['province'] = $province;
		}
		
		if (isset($result[1]->region->id))
		{
			$city = array(
					'id' => $result[1]->region->id,
					'name' => $result[1]->region->name,
					'parentId' => $result[1]->region->parent_id,
					'pinyin' => $result[1]->region->pinyin 
			);
			$result_info['city'] = $city;
		}
		
		if (isset($result[2]->region->id))
		{
			$county = array(
					'id' => $result[2]->region->id,
					'name' => $result[2]->region->name,
					'parentId' => $result[2]->region->parent_id,
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
	 * 生成二维码图片
	 * 
	 * @param unknown $data
	 *        	要生成的数据
	 * @param string $level
	 *        	生成复杂程度
	 * @param string $size
	 *        	尺寸
	 * @param string $margin
	 *        	边距
	 * @throws \DomainException
	 * @return multitype:string unknown |boolean 生成成功返回图片数组array('id'=>'生成在图片表ID','path'=>'访问路径')
	 * @version 2015-5-29
	 * @author liujun
	 */
	public function generationQRcode($data, $path = null, $level = 'L', $size = '15', $margin = '1')
	{

		if ($data)
		{
			include_once APP_PATH . '/vendor/Core/System/phpqrcode/phpqrcode.php';
			$dir = date('Ymd') . '/';
			$file_path = QRCODE_PATH . $dir;
			$file = date("His") . rand(000, 999) . '.png';
			if (! is_dir($file_path))
			{
				mkdir($file_path, 0775, true);
			}
			/*print_r($path);die;*/
			if ($path)
			{
				return QRcode::png($data, $path, $level, $size, $margin);
			}
			else
			{
				$result = QRcode::png($data, $file_path . $file, $level, $size, $margin);
			}

			if (! $result)
			{
				$set = array(
						'path' => QRCODE_DIR . $dir,
						'filename' => $file,
						'timestamp' => $this->getTime() 
				);
				$id = $this->getImageTable()->insertData($set);
				if ($id)
				{
					$image_array = array(
							'id' => $id,
							'path' => ROOT_PATH.UPLOAD_PATH.QRCODE_DIR . $dir . $file 
					);
					return $image_array;
				}
			}
			
			return false;
		}
		else
		{
			throw new \DomainException('二维码数据不能为空！');
		}
	}
	
	/**
	 * 生成用户Vcf文件
	 * 
	 * @param string $data
	 *        	生成的数据
	 * @version 2015-5-29
	 * @author chenzy
	 */
	public function generationVcf($data, $filename = null)
	{
		$name = $data['name'] ? $data['name'] : '';
		$mobile = $data['mobile'] ? explode(",", $data['mobile']) : '';
		$email = $data['email'] ? $data['email'] : '';
		$web_address = $data['web_address'] ? $data['web_address'] : '';
		$company = $data['company'] ? $data['company'] : '';
		$position = isset($data['position']) ? explode(",", $data['position']) : '';
		$address = $data['address'] ? $data['address'] : '';
		if ($name && $mobile)
		{
			$str = 'BEGIN:VCARD' . "\r\n";
			$str .= 'VERSION:3.0' . "\r\n";
			$str .= 'N:' . '' . ';' . $name . "\r\n";
			$str .= 'TEL;WORK;VOICE:' . $mobile[0] . "\r\n";
			if($web_address)
			{
			     $str .= 'URL:' . $web_address . "\r\n";
			}
			$str .= 'ORG:' . $company . "\r\n";
			if($email)
			{
			     $str .= 'EMAIL;PREF;INTERNET:'. $email ."\r\n";
			}
			$str .= 'TITLE:' . isset($position[0]) ? $position[0] : ""  . "\r\n";
			$str .= 'ADR;WORK:;;' .$data['county']. $address . ';'.$data['city'].';'.$data['province'].";510000;中国\r\n";
			$str .= 'END:VCARD';
			
			if ($filename)
			{
				file_put_contents(VCARD_PATH . $filename, $str);
				$vcf_fname = $filename;
			}
			else
			{
			    $dir = date("Ymd").'/';
			    $vcf_fname = date("His") . mt_rand(100, 999) . '.vcf';
			    $vcf_save_path_add = VCARD_PATH.$dir;//新增路径
			    
			    if (! is_dir($vcf_save_path_add))
			    {
			        mkdir($vcf_save_path_add, 0775, true);
			    }
			    
				file_put_contents($vcf_save_path_add . $vcf_fname, $str);
				$vcf_fname = $dir.$vcf_fname;
			}
			
			return array(
					'vcf_fname' => $vcf_fname,
					'str' => $str 
			);
		}
		
		return false;
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
				$url = "http://api.wwei.cn/dewwei.html?data=" . HTTP . $path . "&apikey=20160118172115";
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

    public function getJssdk($http="")
    {
        $jssdk = new \Core\System\WxApi\jssdk(WEIXIN_APP_ID, WEIXIN_APP_SECRET,$http);
        $signPackage = $jssdk->GetSignPackage();
        return $signPackage;
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
    
    /**
     * 
     * 前端生成临时二维码
     * @version 2015年6月30日 
     * @author liujun
     */
    public function generateCodeAction()
    {
        $uuid = $_GET['uuid'];
        $major = $_GET['major'];
        $minor = $_GET['minor'];
        
        $srt =" https://zb.weixin.qq.com/nearby/device/v3/clipboard.xhtml?uuid=$uuid&major=$major&minor=$minor";
        
        $url = "http://api.wwei.cn/wwei.html?data=" . urlencode($srt) . "&version=1.0&apikey=20141110217674";
        $json_data = file_get_contents($url);
        	
        $data = json_decode($json_data);
        if (isset($data->status) && $data->status == 1)
        {
            $img_url = $data->data->qr_filepath;
        }
        else
        {
            $img_url = '';
        }
        
        echo $img_url;
        die();
    }
    
    /**
     *
     * 后台行业类型
     * @version 2015年10月29日
     * @author HY
     */
    public function category(){
        $category_arr = array(
            '1' => 'IT互联网',
            '2' => '金融',
            '3' => '文化传媒',
            '4' => '服务业',
            '5' => '教育培训', 
            '6' => '通讯',
            '7' => '房产建筑',
            '8' => '轻工贸易',
            '9' => '医疗生物',
            '10' => '电子电气',
            '11' => '物联网',
            '12' => '机械重工',
            '13' => '司法法律',
            '14' => '政府科研NGO',
            '15' => '化工环保',
            '16' => '光电新能源',
            '17' => '农林牧渔',
            '18' => '其他',
        );
        return $category_arr;
    }
    
    /**
     *
     * 后台公司规模
     * @version 2015年10月29日
     * @author HY
     */
    public function scale(){
        $scale_arr = array(
            '1' => '≤50人',
            '2' => '50-150人',
            '3' => '150-1000人',
            '4' => '1000-5000人',
            '5' => '5000-10000人',
        );
        return $scale_arr;
    }



	/*
	 * 更新统计数据
	 *@param  int   $type (1新用户；2摇一摇；3入驻公司；4设备销量；5app访问公司；6摇一摇/网页访问公司；)
	 *@param  int   $value  (增长量)
	 *@param  int   $foreign_id ($type=5或6时 company_id)
	 *@version 2015年12月25日
	 *@author Sheeran
	 * */
	public function statOporation($type,$value,$foreign_id=null,$activity_id=null)
	{
		$data=array();
		
		$date=date('Y-m-d',strtotime(date('Y-m-d',time())));
		
		if($type && $value)
		{
			/*print_r($activity_id);die;*/
			if($foreign_id && ($type==5 || $type==6))
			{
				$data['foreign_id']=$foreign_id;
			}else if($activity_id && $type==7)
			{
				$data['activity_id']=$activity_id;
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
				$this->getStatisticsDayTable()->updateKeys($date, 1, 'value', $value,array('type'=>$data['type'],'date'=>$date,'foreign_id'=>$foreign_id,'activity_id'=>$activity_id));
			}
		}
		else
		{
			return false;
		}
	}
	/*
	 * 输入年月，得到当月的第一天和最后一天
	 * @return array
	 * @param  $date like 2015-12
	 * */
	public function getTheMonth($i,$date)
	{
	    
		$dates = $date."0201";
		$firstday = date("Y-$i-01", strtotime($dates));
		$lastday = date('Y-m-d', strtotime("$firstday + 1month -1day"));
		return array('first'=>$firstday, 'last'=>$lastday);
	}

	public function getTheMonths($date)
	{
		$dates = $date."01";
		$firstday = date("Y-m-01", strtotime($dates));
		$lastday = date('Y-m-d', strtotime("$firstday + 1month -1day"));
		return array('first'=>$firstday, 'last'=>$lastday);
	}


    public function getFirstLastTime($date){
		$dates = $date."0201";
        $firstday = date("Y-2-01", strtotime($dates));
        $lastday = date('Y-12-31', strtotime($dates));
        return array('first'=>$firstday, 'last'=>$lastday);
    }

	public function getStasticsData($colum,$i,$type, $date,$company_id)
	{

		$time = $this->getTheMonth($i, $date);
		$where = new where();
		$where->equalTo('type', $type);
		$where->equalTo($colum,$company_id);
		$where->greaterThanOrEqualTo('date', $time['first']);
		$where->lessThanOrEqualTo('date', $time['last']);
		$data = $this->getStatisticsDayTable()->getAll($where);


		$num = 0;
		foreach ($data['list'] as $val) {
			$num += $val['value'];
		}
		return $num;
	}
	//获取年数据
	 public function getYearData($colum,$type,$year,$company_id){
		$date=$year."0101";
		$firstday = date("Y-m-01", strtotime($date));
		$lastday = date('Y-m-d', strtotime("$firstday + 1year -1day"));
		$where = new where();
		 $where->equalTo('type', $type);
		$where->equalTo($colum,$company_id);
		$where->greaterThan('date',$firstday);
		$where->lessThan('date', $lastday);
		$info = $this->getStatisticsDayTable()->getAll($where);
		 $num=0;
		 foreach($info['list'] as $v){
			 $num+=$v['value'];

		 }
		return $num;
	}


}
