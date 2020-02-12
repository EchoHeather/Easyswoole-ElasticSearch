环境
    centos7.2 swoole4.5 easyswoole2.1.2 ealsticsearch7.4 php7.2 redis nginx 
项目
    高性能小视频服务系统
	前后端分离，通过Nginx请求转发到swoole服务器
	视频上传本地和阿里云视频点播服务底层类库封装
	Redis消息队列读取信息
	Yaconf重构底层配置信息
	3种页面静态化方式，根据Yaconf配置选择
		1.定时将页面信息存入.json文件中
		2.定时将页面信息存入Redis中
		3.定时将页面信息存入swoole内存table中
	ElasticSearch搜索视频信息