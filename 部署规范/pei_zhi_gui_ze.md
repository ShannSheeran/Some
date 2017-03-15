# 配置规则


** 1） 每个项目设置一个对应的用户，并设置与 Apache 相同的用户组 **  

如，创建“www”用户用于运行web项目  

    useradd –g users www

** 2） 设置项目根目录访问权限 **  

    chmod g+rwx /home/www