[![](http://vdisk.me/static/images/vi/logo/32x32.png)](#) VdiskSDK-PHP
============

请先前往 [微盘开发者中心](http://vdisk.weibo.com/developers/) 注册为微盘开发者, 并创建应用.

RESTful API文档:
[![](http://vdisk.me/static/images/vi/icon/16x16.png)](http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc)
http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc


Demo演示地址: http://vauth.appsina.com/


SDK For PHP文档地址: http://vauth.appsina.com/Doc/namespaces/Vdisk.html


关于微盘OPENAPI、SDK使用以及技术问题请联系: [@一个开发者](http://weibo.com/smcz)

[![](http://service.t.sina.com.cn/widget/qmd/1656360925/02781ba4/4.png)](http://weibo.com/smcz)

-----
Usage
=====


```php

//实例化 \Vdisk\OAuth2
$oauth2 = new \Vdisk\OAuth2('您应用的appkey', '您应用的appsecret');
$auth_url = $oauth2->getAuthorizeURL('您在开发者中心设置的跳转地址');
/*
引导用户访问授权页面: $auth_url
*/

```
