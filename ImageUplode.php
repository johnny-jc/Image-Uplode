<?php

/**
 * @auth  Johnny  2017-02-21
 * @param 上传图片公告类
 * @param 功能介绍 ：             方法 setThumb(图片宽度|高度)
 * @param 图片上传地址 ：         方法 setDir
 * @param 图片水印 ：             方法 setWatermark
 * @param 图片对应文件及图片删除  方法 delImgDir
 */
class Upload
{
    public $dir;            // 附件存放物理目录
    public $time;           // 自定义文件上传时间
    public $allow_types;    // 允许上传附件类型
    public $field;          // 上传控件名称
    public $maxsize;        // 最大允许文件大小，单位为KB

    public $big_width;      // 大图缩略图宽度
    public $big_height;     // 大图缩略图高度
    public $middle_width;   // 中图缩略图宽度
    public $middle_height;  // 中图缩略图高度
    public $small_width;    // 小图缩略图宽度
    public $small_height;   // 小图缩略图高度

    public $watermark_file; // 水印图片地址
    public $watermark_pos;  // 水印位置
    public $watermark_trans;// 水印透明度

    /**
     * Upload constructor.      构造函数
     * @param string $types     允许上传的文件类型
     * @param int $maxsize      允许大小
     * @param string $field     上传控件名称
     * @param string $time      自定义上传时间
     */
    public function upload($types = 'jpg|png', $maxsize = 1024, $field = 'file', $time = '')
    {
        $this->allow_types = explode('|', $types);
        $this->maxsize = $maxsize * 1024;
        $this->field = $field;
        $this->time = $time ? $time : time();
    }

    /**
     * @param $basedir          基目录，必须为物理路径
     * @param string $filedir   自定义子目录，可用参数{y}、{m}、{d}
     * @return string           设置并创建文件具体存放的目录
     */
    public function setDir($basedir, $filedir = '')
    {
        $dir = $basedir;
        !is_dir($dir) && @mkdir($dir, 0777);
        if (!empty($filedir)) {
            $filedir = str_replace(array('{y}', '{m}', '{d}'), array(date('Y', $this->time), date('m', $this->time), date('d', $this->time)), strtolower($filedir));//用string_replace把{y} {m} {d}几个标签进行替换
            $dirs = explode('/', $filedir);
            foreach ($dirs as $d) {
                !empty($d) && $dir .= $d . '/';
                !is_dir($dir) && @mkdir($dir, 0777);
            }
        }
        $this->dir = $dir;
        return $dir;
    }

    /**
     * @param 图片缩略图设置 ，如果不生成缩略图则不用设置
     * @param int $width     缩略图宽度
     * @param int $height    缩略图高度
     */
    public function setThumb($big_width = 0, $big_height = 0, $middle_width = 0, $middle_height = 0, $small_width = 0, $small_height = 0)
    {
        $this->big_width = $big_width;
        $this->big_height = $big_height;
        $this->middle_width = $middle_width;
        $this->middle_height = $middle_height;
        $this->small_width = $small_width;
        $this->small_height = $small_height;
    }

    /**
     * @param               图片水印设置 ，如果不生成添加水印则不用设置
     * @param $file         水印图片
     * @param int $pos      水印位置
     * @param int $trans    水印透明度
     */
    public function setWatermark($file, $pos = 6, $trans = 80)
    {
        $this->watermark_file = $file;
        $this->watermark_pos = $pos;
        $this->watermark_trans = $trans;
    }

    /**
     * @param 其中：name 为文件名，上传成功时是上传到服务器上的文件名，上传失败则是本地的文件名
     *              dir  为服务器上存放该附件的物理路径，上传失败不存在该值
     *              size 为附件大小，上传失败不存在该值
     *              flag 为状态标识，1表示成功，-1表示文件类型不允许，-2表示文件大小超出
     * @return array   执行文件上传，处理完返回一个包含上传成功或失败的文件信息数组，
     */
    public function execute()
    {
        $files = array(); //成功上传的文件信息
        $field = $this->field;
        $keys = $_FILES[$field];
        foreach ($keys as $key) {
            if (!$_FILES[$field]['name'] == $key) continue;
            $fileext = $this->fileext($_FILES[$field]['name']); //获取文件扩展名
            $filename = date('Ymdhis', $this->time) . mt_rand(10, 99) . '.' . $fileext; //生成文件名
            $filedir = $this->dir;  //附件实际存放目录
            $filesize = $_FILES[$field]['size']; //文件大小

            //文件类型不允许
            if (!in_array($fileext, $this->allow_types)) {
                $files['name'] = $_FILES[$field]['name'];
                $files['flag'] = -1;
                continue;
            }

            //文件大小超出
            if ($filesize > $this->maxsize) {
                $files['name'] = $_FILES[$field]['name'];
                $files['name'] = $filesize;
                $files['flag'] = -2;
                continue;
            }

            $files['name'] = $filename;
            $files['dir'] = $filedir;
            $files['size'] = $filesize;

            //保存上传文件并删除临时文件
            if (is_uploaded_file($_FILES[$field]['tmp_name'])) {
                move_uploaded_file($_FILES[$field]['tmp_name'], $filedir . $filename);
                @unlink($_FILES[$field]['tmp_name']);
                $files['flag'] = 1;

                //对图片进行加水印和生成缩略图,这里演示只支持jpg和png(gif生成的话会没了帧的)
                if (in_array($fileext, array('jpg', 'png'))) {
                    if ($this->big_width) {
                        if ($this->createThumb($filedir . $filename, $filedir . 'big_' . $filename,$this->big_width,$this->big_height)) {
                            $files['big'] = 'big_' . $filename;  // 大缩略图文件名
                        }
                    }

                    if ($this->middle_width) {
                        if ($this->createThumb($filedir . $filename, $filedir . 'middle_' . $filename,$this->middle_width,$this->middle_height)) {
                            $files['middle'] = 'middle_' . $filename;  // 中缩略图文件名
                        }
                    }
                    if ($this->small_width) {
                        if ($this->createThumb($filedir . $filename, $filedir . 'small_' . $filename,$this->small_width,$this->small_height)) {
                            $files['small'] = 'small_' . $filename;  // 小缩略图文件名
                        }
                    }

                    $this->createWatermark($filedir . $filename);
                }
            }
        }
        return $files;
    }

    /**
     * @param 创建缩略图,以相同的扩展名生成缩略图
     * @param $src_file     来源图像路径
     * @param $big_file   缩略图路径
     * @return bool
     */
    public function createThumb($src_file, $thumb_file,$t_width,$t_height)
    {
        if (!file_exists($src_file)) return false;

        $src_info = getImageSize($src_file);
        //如果来源图像小于或等于缩略图则拷贝源图像作为缩略图,免去操作
        if ($src_info[0] <= $t_width && $src_info[1] <= $t_height) {
            if (!copy($src_file, $thumb_file)) {
                return false;
            }
            return true;
        }

        //按比例计算缩略图大小
        if (($src_info[0] - $t_width) > ($src_info[1] - $t_width)) {
            $t_height = ($t_width / $src_info[0]) * $src_info[1];
        } else {
            $t_width = ($t_width / $src_info[1]) * $src_info[0];
        }

        //取得文件扩展名
        $fileext = $this->fileext($src_file);

        switch ($fileext) {
            case 'jpg' :
                $src_img = ImageCreateFromJPEG($src_file);
                break;
            case 'png' :
                $src_img = ImageCreateFromPNG($src_file);
                break;
            case 'gif' :
                $src_img = ImageCreateFromGIF($src_file);
                break;
        }

        //创建一个真彩色的缩略图像
        $thumb_img = @ImageCreateTrueColor($t_width, $t_width);

        //ImageCopyResampled函数拷贝的图像平滑度较好，优先考虑
        if (function_exists('imagecopyresampled')) {
            @ImageCopyResampled($thumb_img, $src_img, 0, 0, 0, 0, $t_width, $t_width, $src_info[0], $src_info[1]);
        } else {
            @ImageCopyResized($thumb_img, $src_img, 0, 0, 0, 0, $t_width, $t_width, $src_info[0], $src_info[1]);
        }

        //生成缩略图
        switch ($fileext) {
            case 'jpg' :
                ImageJPEG($thumb_img, $thumb_file);
                break;
            case 'gif' :
                ImageGIF($thumb_img, $thumb_file);
                break;
            case 'png' :
                ImagePNG($thumb_img, $thumb_file);
                break;
        }

        //销毁临时图像
        @ImageDestroy($src_img);
        @ImageDestroy($thumb_img);

        return true;
    }

    /**
     * @param 为图片添加水印
     * @param $file  要添加水印的文件
     */
    public function createWatermark($file)
    {
        //文件不存在则返回
        if (!file_exists($this->watermark_file) || !file_exists($file)) return;
        if (!function_exists('getImageSize')) return;

        //检查GD支持的文件类型
        $gd_allow_types = array();
        if (function_exists('ImageCreateFromGIF')) $gd_allow_types['image/gif'] = 'ImageCreateFromGIF';
        if (function_exists('ImageCreateFromPNG')) $gd_allow_types['image/png'] = 'ImageCreateFromPNG';
        if (function_exists('ImageCreateFromJPEG')) $gd_allow_types['image/jpeg'] = 'ImageCreateFromJPEG';

        //获取文件信息
        $fileinfo = getImageSize($file);

        $wminfo = getImageSize($this->watermark_file);

        if ($fileinfo[0] < $wminfo[0] || $fileinfo[1] < $wminfo[1]) return;

        if (array_key_exists($fileinfo['mime'], $gd_allow_types)) {
            if (array_key_exists($wminfo['mime'], $gd_allow_types)) {

                //从文件创建图像
                $temp = $gd_allow_types[$fileinfo['mime']]($file);
                $temp_wm = $gd_allow_types[$wminfo['mime']]($this->watermark_file);

                //水印位置
                switch ($this->watermark_pos) {
                    case 1 :  //顶部居左
                        $dst_x = 0;
                        $dst_y = 0;
                        break;
                    case 2 :    //顶部居中
                        $dst_x = ($fileinfo[0] - $wminfo[0]) / 2;
                        $dst_y = 0;
                        break;
                    case 3 :  //顶部居右
                        $dst_x = $fileinfo[0];
                        $dst_y = 0;
                        break;
                    case 4 :  //底部居左
                        $dst_x = 0;
                        $dst_y = $fileinfo[1];
                        break;
                    case 5 :  //底部居中
                        $dst_x = ($fileinfo[0] - $wminfo[0]) / 2;
                        $dst_y = $fileinfo[1];
                        break;
                    case 6 :  //底部居右
                        $dst_x = $fileinfo[0] - $wminfo[0];
                        $dst_y = $fileinfo[1] - $wminfo[1];
                        break;
                    default : //随机
                        $dst_x = mt_rand(0, $fileinfo[0] - $wminfo[0]);
                        $dst_y = mt_rand(0, $fileinfo[1] - $wminfo[1]);
                }

                if (function_exists('ImageAlphaBlending')) ImageAlphaBlending($temp_wm, True); //设定图像的混色模式
                if (function_exists('ImageSaveAlpha')) ImageSaveAlpha($temp_wm, True); //保存完整的 alpha 通道信息

                //为图像添加水印
                if (function_exists('imageCopyMerge')) {
                    ImageCopyMerge($temp, $temp_wm, $dst_x, $dst_y, 0, 0, $wminfo[0], $wminfo[1], $this->watermark_trans);
                } else {
                    ImageCopyMerge($temp, $temp_wm, $dst_x, $dst_y, 0, 0, $wminfo[0], $wminfo[1]);
                }

                //保存图片
                switch ($fileinfo['mime']) {
                    case 'image/jpeg' :
                        @imageJPEG($temp, $file);
                        break;
                    case 'image/png' :
                        @imagePNG($temp, $file);
                        break;
                    case 'image/gif' :
                        @imageGIF($temp, $file);
                        break;
                }
                //销毁零时图像
                @imageDestroy($temp);
                @imageDestroy($temp_wm);
            }
        }
    }

    /**
     * @param 获取文件扩展名
     * @return string
     */
    public function fileext($filename)
    {
        return strtolower(substr(strrchr($filename, '.'), 1, 10));
    }

    /**
     * @param $filepath   删除目录文件及图片
     */
    public function delImgDir($filepath)
    {
        if (is_dir($filepath)){
            if ($handle = opendir($filepath)) {
                while (false !== ($item = readdir($handle))) {
                    if ($item != '.' && $item != '..') {
                        if (is_dir($filepath.'/'.$item)) {
                            delImgDir($filepath.'/'.$item);
                        } else {
                            if (unlink($filepath.'/'.$item));
                            @rmdir($filepath);
                        }
                    }
                }
                closedir($handle);
            }
        }
    }
}