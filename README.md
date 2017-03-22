# Image-Uplode
PHP 图片上传DEMO

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
$resault = $up->execute();

var_dump($resault);

// 删除图片文件及文件夹
$delImage = $up->delImgDir('uplode/');

var_dump($delImage);
