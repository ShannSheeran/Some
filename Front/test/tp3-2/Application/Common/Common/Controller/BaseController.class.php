<?php

namespace Common\Common\Controller;

use Common\Common\ErrorCode;
use Common\Common\Service\AuthService;
use Illuminate\Http\Request;
use Library\Crypt\AuthCode;
use Think\Controller;

class BaseController extends Controller
{
    /**
     * @var Request $request
     */
    protected $request;

    public function __construct()
    {

        parent::__construct();

        include dirname($_SERVER['SCRIPT_FILENAME']) . '/vendor/autoload.php';

        $this->request = Request::capture();

        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers:Origin, X-Requested-With, Content-Type, Accept, Authorization");
        if ('OPTIONS' == REQUEST_METHOD) {
            header("HTTP/1.1 200 OK");
            exit();
        }

        if (strtolower(REQUEST_METHOD) == 'post') {
            $_POST = (array)I('post.') + (array)json_decode(file_get_contents('php://input'), true);
        }
    }

    /**
     * 接口必须要登录时使用，
     * 用户已登录则返回用户实例，否则返回错误信息
     * @return mixed
     */
    protected function requireAuth()
    {
        $userId = $this->checkAuth();

        if (!$userId) {
            $this->fail(ErrorCode::SYS_USER_VERIFY_FAIL);
        }

        return $userId;
    }

    /**
     * 检查用户是否登录
     * @return int
     */
    protected function checkAuth()
    {
        $user_id = AuthService::getAuth()->id;
//        $user_id = UserService::user()->id;
        if ($user_id) {
            return $user_id;
        } else {
            $token = $this->getToken();
            $toke_json = AuthCode::decrypt($token, C('TOKEN_CRYPT_CODE'));
            $token_data = json_decode($toke_json, true);

            if ($token_data) {
                // TODO 修改认证的model
                //AuthService::create('user')->loadData($token_data['user_id']);
//                UserService::init($token_data['user_id']);
                return $token_data;
            } else {
                return 0;
            }
        }
    }

    protected function getToken()
    {
        return $this->request->header('token') ? $this->request->header('token') : I('get.token');
    }

    /**
     * 按分页返回
     * @param $list
     * @param $number
     */
    protected function paginate($list, $number)
    {
        $this->response(
            [
                'page_no'  => I('page_no',   1,  'intval'),
                'page_num' => I('page_size', 10, 'intval'),
                'count' => intval($number),
                'data_list' => $list,
            ]
        );
    }

    /**
     * 返回不需要分页时的列表
     * @param $list
     */
    public function responseList($list)
    {
        $this->response(['data_list' => $list]);
    }

    /**
     * 返回分页设置
     * @param null $page_no
     * @param null $page_num
     * @return string
     */
    protected function page($page_no = NULL, $page_num = NULL)
    {
        empty($page_no) && $page_no = I('page_no', 1, 'intval');
        empty($page_num) && $page_num = I('page_size', 10, 'intval');
        $offset = ($page_no - 1) * $page_num;
        $offset = max(0, $offset);
        $page_num = max(0, $page_num);
        return "$offset,$page_num";
    }

    /**
     * 检查参数是否有空值(未设置该字段或为''),有则返回错误提示
     * @param $params
     */
    protected function checkEmpty($params)
    {
        foreach ($params as $param) {
            (!isset($param) || $param === '') && $this->fail(ErrorCode::SYS_REQUEST_PARAMS_MISSING);
        }
    }

    /**
     * 输出错误提示,
     * 若未传$error_code则默认会返回系统错误提示
     * @param $error_code
     * @param string $error_msg
     */
    protected function fail($error_code = ErrorCode::SYS_SYSTEM_ERROR, $error_msg = '')
    {
        $error_msg_list = ErrorCode::getAllErrorMessage();
        if (!isset($error_msg_list[$error_code])){
            $error_code = ErrorCode::SYS_SYSTEM_ERROR;
            $error_msg = APP_DEBUG && !empty($error_msg) ? $error_msg : ErrorCode::getMessage($error_code);
        } else {
            $error_msg = APP_DEBUG && !empty($error_msg) ? $error_msg : ErrorCode::getMessage($error_code);
        }
        $this->returnData($error_code, $error_msg);
    }

    /**
     * 输出成功状态并返回数据
     * @param $response_data
     */
    protected function response($response_data = '')
    {
        $response_data && $this->checkResponseData($response_data);
        $this->returnData(ErrorCode::SUCCESS, ErrorCode::getMessage(ErrorCode::SUCCESS), $response_data);
    }

    /**
     * 输出数据
     * @param int $error_code
     * @param string $error_msg
     * @param array $response_data
     */
    protected function returnData($error_code, $error_msg, $response_data = array())
    {
        $this->beforeResponse($error_code, $error_msg, $response_data);
        $data = [
            'error_code' => $error_code,
            'error_msg' => $error_msg,
            'data' => $response_data,
        ];
        $this->ajaxReturn($data, 'JSON');
    }

    /**
     * 接口返回前的动作
     * @param $error_code
     * @param $error_msg
     * @param $response_data
     */
    protected function beforeResponse($error_code, $error_msg, $response_data)
    {

    }

    /**
     * 将$data中$from的值转换为$to的值，如果$rec为true则会递归转换；
     * @param $data
     * @param $from
     * @param $to
     * @param bool $rec
     */
    protected function checkResponseData(&$data, $from = [], $to = null, $rec = false)
    {
        if ($data === $from) {
            $data = $to;
        } else if (is_array($data)) {
            foreach ($data as &$row) {
                if ($row === $from) {
                    $row = $to;
                } else if (is_array($row) && $rec) {
                    $this->checkResponseData($row, $from, $to, $rec);
                }
            }
//            function convertTmp($from, $to) {
//                return function (&$item) use ($from, $to) {
//                    $item === $from && $item = $to;
//                };
//            }
//            $rec ? array_walk_recursive($data, convertTmp($from, $to)) : array_walk($data, convertTmp($from, $to));
        }
    }

    /**
     * 根据异常信息输出错误信息
     * @param \Exception $e
     */
    protected function getExceptionError(\Exception $e)
    {
        $code = $e->getCode();
        // 如果异常未设置错误代码则默认为系统错误
        $this->fail($code ? $code : ErrorCode::SYS_SYSTEM_ERROR, $e->getMessage());
    }

    /**
     * 调用方法不存在则输出错误提示
     */
    public function _empty()
    {
        $this->fail(ErrorCode::SYS_INTERFACE_NOT_EXIST);
    }

}