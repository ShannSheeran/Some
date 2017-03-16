# 关闭空主机头

为了防止域名解析恶意指向，需要禁止apache默认的空主机头  

编辑配置文件  

    vim /etc/httpd/conf/httpd.conf

在你的站点配置之前增加一个站点  

    # 关闭空主机头  
    
    <VirtualHost *:80>
      ServerAdmin mail@example.com
      DocumentRoot /var/www/error/
      ServerName abc.com
    </VirtualHost>
