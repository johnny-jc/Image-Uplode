<?php
/**
 * Created by PhpStorm.
 * User: jsb-10
 * Date: 2017/3/21
 * Time: 13:48
 */


require_once "ImageUplode.php";

// Yii2 调用
$str='abcdefghijklmnopqrstuvwxyz';
$rndstr = '';	//用来存放生成的随机字符串
for($i=0;$i<5;$i++)
{
    $rndcode=rand(0,25);
    $rndstr.=$str[$rndcode];
}

// 调用图片资源存储
$pathLog = Yii::$app->params['UPLODE_IMG_LOG'].$rndstr.'/';
$pathBanner = Yii::$app->params['UPLODE_IMG_BANNER'].$rndstr.'/';

// 调用图片类
$uplodeImg = new UplodeImg();

// 控件名称
$fieldLog = 'log';
$fieldBanner = 'banner';

// 获取资源
$log[$fieldLog] = $_FILES[$fieldLog];
$banner[$fieldBanner] = $_FILES[$fieldBanner];

// 生成图片
$logImg = $uplodeImg->uplodeImgExecute($fieldLog,$pathLog,$log,500, 500, 300, 300, 100, 100);
$bannerImg = $uplodeImg->uplodeImgExecute($fieldBanner,$pathBanner,$log,500, 500, 300, 300, 100, 100);

var_dump($logImg['name']);
var_dump($bannerImg['name']);
