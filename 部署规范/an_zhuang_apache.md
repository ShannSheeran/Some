# 安装Apache


    yum install httpd –y

### 设置 Apache 开机自动启动
    chkconfig httpd on
### 修改 Apache 的用户组
    vim /etc/httpd/conf/httpd.conf

修改为“users”用户组，如下图  

![](QQ图片20160526112151.png)