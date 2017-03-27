<?php
/**
 * Created by PhpStorm.
 * User: jsb-10
 * Date: 2017/3/21
 * Time: 13:48
 */


require_once "ImageUplode.php";

$up = new upload();
$str='abcdefghijklmnopqrstuvwxyz';
$rndstr = '';	//用来存放生成的随机字符串
for($i=0;$i<5;$i++)
{
    $rndcode=rand(0,25);
    $rndstr.=$str[$rndcode];
}
$path = dirname($_SERVER['HTTP_HOST']).'/uplode/banner/'. $rndstr.'/';
/**
 * Upload constructor.      构造函数
 * @param string $types     允许上传的文件类型
 * @param int $maxsize      允许大小
 * @param string $field     上传控件名称
 * @param string $time      自定义上传时间
 */
$up->upload();
/**
 * @param $basedir          基目录，必须为物理路径
 * @param string $filedir   自定义子目录，可用参数{y}、{m}、{d}
 * @return string           设置并创建文件具体存放的目录
 */
$up->setDir($path);
/**
 * @param 图片缩略图设置 ，如果不生成缩略图则不用设置
 * @param int $width     缩略图宽度
 * @param int $height    缩略图高度
 */
$up->setThumb(600,600,300,300,50,50);
/**
 * @param 其中：name 为文件名，上传成功时是上传到服务器上的文件名，上传失败则是本地的文件名
 *              dir  为服务器上存放该附件的物理路径，上传失败不存在该值
 *              size 为附件大小，上传失败不存在该值
 *              flag 为状态标识，1表示成功，-1表示文件类型不允许，-2表示文件大小超出
 * @return array   执行文件上传，处理完返回一个包含上传成功或失败的文件信息数组，
 */
$aa = $up->execute();

var_dump($aa);

// 删除图片文件及文件夹
$aa = $up->delImgDir('uplode/');

var_dump($aa);



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
