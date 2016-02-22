<?php
//验证码类
class Authcode {
    private $charset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';    //随机因子
    private $code;                            //验证码
    private $codelen;                    //验证码长度
    private $width;                    //宽度
    private $height;                    //高度
    private $img;                                //图形资源句柄
    private $font;                                //指定的字体
    private $fontsize = 20;                //指定字体大小
    private $fontcolor;                        //指定字体颜色

    //构造方法初始化
    public function __construct($width = 115 , $height = 33 , $codelen = 4) {
        $this->width = $width;
        $this->height = $height;
        $this->codelen = $codelen;
        $this->font = PROJECT_PATH.'public/font/elephant.ttf';
        $this->createCode();
    }

    public function __toString(){
        
        $_SESSION['code']  = strtoupper($this->code);
        $this->doimg();
        return '';   
    }

    //生成随机码
    private function createCode() {
        $len = strlen($this->charset)-1;
        for ($i=0;$i<$this->codelen;$i++) {
            $this->code .= $this->charset[mt_rand(0,$len)];
        }
    }

    //生成背景
    private function createBg() {
        $this->img = imagecreatetruecolor($this->width, $this->height);
        $color = imagecolorallocate($this->img, mt_rand(157,255), mt_rand(157,255), mt_rand(157,255));
        imagefilledrectangle($this->img,0,$this->height,$this->width,0,$color);
    }

    //生成文字
    private function createFont() {    
        $_x = $this->width / $this->codelen;
        for ($i=0;$i<$this->codelen;$i++) {
            $this->fontcolor = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
            imagettftext($this->img,$this->fontsize,mt_rand(-30,30),$_x*$i+mt_rand(1,5),$this->height / 1.4,$this->fontcolor,$this->font,$this->code[$i]);
        }
    }

    //生成线条、雪花
    private function createLine() {
        for ($i=0;$i<6;$i++) {
            $color = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
            imageline($this->img,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$color);
        }
        for ($i=0;$i<100;$i++) {
            $color = imagecolorallocate($this->img,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255));
            imagestring($this->img,mt_rand(1,5),mt_rand(0,$this->width),mt_rand(0,$this->height),'*',$color);
        }
    }

    //输出
    private function outPut() {
            //自动检测GD支持的图像类型，并输出图像
		if(imagetypes() & IMG_GIF){          //判断生成GIF格式图像的函数是否存在
		    	header("Content-type: image/gif");  //发送标头信息设置MIME类型为image/gif
		    	imagegif($this->img);           //以GIF格式将图像输出到浏览器
	    	}elseif(imagetypes() & IMG_JPG){      //判断生成JPG格式图像的函数是否存在
		    	header("Content-type: image/jpeg"); //发送标头信息设置MIME类型为image/jpeg
		    	imagejpeg($this->img, "", 0.5);   //以JPEN格式将图像输出到浏览器
	    	}elseif(imagetypes() & IMG_PNG){     //判断生成PNG格式图像的函数是否存在
		    	header("Content-type: image/png");  //发送标头信息设置MIME类型为image/png
		    	imagepng($this->img);          //以PNG格式将图像输出到浏览器
		}elseif(imagetypes() & IMG_WBMP){   //判断生成WBMP格式图像的函数是否存在
		    	header("Content-type: image/vnd.wap.wbmp");   //发送标头为image/wbmp
		    	imagewbmp($this->img);       //以WBMP格式将图像输出到浏览器
		}else{                              //如果没有支持的图像类型
			die("PHP不支持图像创建！");    //不输出图像，输出一错误消息，并退出程序
		}	
 }

    //对外生成并输出图像
    public function doimg() {
        $this->createBg();
        $this->createLine();
        $this->createFont();
        $this->outPut();
    }

    //释放资源
    public function __destruct(){
        imagedestroy($this->img);
    }
}
