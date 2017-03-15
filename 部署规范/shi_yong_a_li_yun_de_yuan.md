# 使用阿里云的源


    cd /etc/yum.repos.d 

** 1）下载替换 repo **  

    mv CentOS-Base.repo CentOS-Base.repo.bak  
    wget -O CentOS-Base.repo http://mirrors.aliyun.com/repo/Centos-6.repo

若使用阿里云服务器，可以通过内网地址：  

<http://mirrors.aliyuncs.com/repo/Centos-6.repo>  

不占用公网流量。  


** 2）重建 cache **  

    yum clean all
    yum makecache