# 项目目录结构

	|-Project
	    |–node_modules      插件包目录
	    |–dist          **发布环境**（编译自动生成的）
	        |–css           样式文件(style.css style.min.css)
	        |–img           图片文件(压缩图片\合并后的图片)
	        |–js            js文件(main.js main.min.js)
	        |-tpl				 视图文件(**.html)
	        |-vendor        第三方库(**.js)
	        |–index.html    静态页面文件(压缩html)
	    |–src           **开发环境**
	        |–css          sass文件
	        |–img           图片文件
	        |–js            js文件
	        |-tpl           视图文件(**.html)
	        |-vendor        第三方库(**.js)
	        |–index.html    静态文件
	    |–gulpfile.js       gulp配置文件
	    |–package.json      依赖模块json文件,在项目目录下执行 npm install 会安装项目所有的依赖模块，方便自动化构建方案在项目之间的的快速复用
	    
# 准备工作
### 1、请先安装 nodejs（自带 npm）环境。

### 2、全局安装 gulp
	npm install gulp -g
### 3、项目中安装插件
	npm install
### 4、项目运行
	gulp
	
### gulp文件配置好了，执行gulp命令将运行默认任务，项目会自动生成一个dist文件夹、浏览器会通过本地服务自动打开网页、当修改src内的文件保存后、页面会执行browser-sync来自动刷新页面

# js目录结构
	|-js
		|-controllers       控制器
		|-directives        指令
		|-model             模型
		|-services          服务
		app.js              管理模块注入、配置
		config.router.js    路由
		main.js             index.html(主视图的控制器)
		
# tpl目录结构
	|-tpl
		|-blocks            板块页面(nav.html、header.html)
		|-popup             弹窗页面
		**.html
		
# css目录结构
	|-css
		|-less
		**.css