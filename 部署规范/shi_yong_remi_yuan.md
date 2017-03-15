# 使用remi源

** 1） 安装 remi 源 **  

    cd /etc/yum.repos.d
    yum install http://rpms.famillecollet.com/enterprise/remi-release-6.rpm

** 2） 使 remi 源生效 **  

    vim remi.repo

分别找到“[remi]”及“[remi-php55]”部分，修改  

    enabled=1

保存并退出。