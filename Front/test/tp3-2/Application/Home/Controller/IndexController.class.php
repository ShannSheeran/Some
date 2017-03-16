<?php

namespace Home\Controller;

use Common\Common\Controller\BaseController;

class IndexController extends BaseController {

    public function index(){
        echo '<pre>';
        dd(
            $this->request->all(),                              // 所有请求参数（包括get、post等)
            $this->request->only(['username', 'password']),     // 只获取username和password参数
            $this->request->except(['username', 'password']),   // 获取除username和password外的参数
            $this->request->url(),                              // 当前请求url
            $this->request->root(),                             // 服务器根路径的url
            $this->request->header('token'),                    // 获取header中的token
            $this->request->decodedPath(),                      // url解码后的path
            $this->request->ip(),                               // 获取请求ip
            $this->request->hasFile()                           // 是否有文件上传
        );
    }
}