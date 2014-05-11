<?php
//appkey
$app_key_online		＝ ‘’;
$app_key_test		＝ ‘’;
//secretkey
$secret_key_online 	＝ ‘’;
$secret_key_test 	＝ ‘’;

$connect_timeout 	＝ ‘’;
$read_timeout    	＝ ‘’;

/** 是否打开入参check**/
$check_request 		= true;
//是否是模式
$testmode 			= false;
//日志目录
$data_dir 			= './';

//API提交网关地址
$gateway_url_online = "http://gw.api.taobao.com/router/rest";
$gateway_url_test   = "http://gw.api.tbsandbox.com/router/rest?";

//认证授权地址
$auth_url_online	= "http://container.api.taobao.com/container?appkey=";
$auth_url_test		= "http://container.api.tbsandbox.com/container?appkey=";