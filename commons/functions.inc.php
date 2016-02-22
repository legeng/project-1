<?php
	//全局可以使用的通用函数声明在这个文件中.

	function un_escape($str){
 		$ret = '';
 		$len = strlen($str);
 		for ($i = 0; $i < $len; $i++){
 			if ($str[$i] == '%' && $str[$i+1] == 'u'){
 				$val = hexdec(substr($str, $i+2, 4));
				if ($val < 0x7f) 
					$ret .= chr($val);
				else if($val < 0x800) 
					$ret .= chr(0xc0|($val>>6)).chr(0x80|($val&0x3f));
				else 
					$ret .= chr(0xe0|($val>>12)).chr(0x80|(($val>>6)&0x3f)).chr(0x80|($val&0x3f));
				
				$i += 5;
			
			}
			
			else if ($str[$i] == '%'){
				
				$ret .= urldecode(substr($str, $i, 3));
				
				$i += 2;
			
			}
			
			else $ret .= $str[$i];
		
		}
		
		return $ret;
	
	}

	//删除目录下所有文件和子目录
	function deldir($directory){
		if(is_dir($directory)) {      
			if($dir_handle=@opendir($directory)) {	
				while(false!==($filename=readdir($dir_handle))) {  
					$file=$directory."/".$filename;
					if($filename!="." && $filename!="..") {   
						if(is_dir($file)) {
							deldir($file);
						} else {
							unlink($file);
						}           
					}
				}
				closedir($dir_handle);                      
				                     
			}
			rmdir($directory);     
		}
	}
         /**
         *检查用户角色权限，根据此权限是否允许用户操作当前的模块和方法，防止用户在地址栏直接输入
         *@param       $userid     当前登录用户的id号
         *@param       $ac         当前登录用户操作的控制器和方法
         *@param       bool    $falg  
         */
        function checkUserAuth($userid , $a_c = null){
            if(is_null($a_c)){
                $a_c = $_GET;
            }
            if($userid != 1){//如果是超级管理员则不限制
                $m = $a_c['m'];
                $a = $a_c['a'];
                $ma = $m."-".$a;
                $info = D('role')->where(array('id'=>$userid))->find();
                $ac = explode(',' , $info["ac"]);
                array_push($ac,"index-index");

                if(in_array($ma , $ac)){
                    return true;
                }else{
                    return false;
                }
            }else{
                return true;
            }
        }


        /*********************以下是我自己添加的一些小功能函数2014-8-14******************************/


         /*此函数得到的是字符串的总个数，不管是gbk | utf8
                     *如果对于单字节字编码符串，可以考虑使用strlen()
                     *preg_split()：通过一个正则表达式分割字符串
                     */
                    function user_getStrLen($str){

                           //自定义处理，不使用php内置函数
                           return count(preg_split("//u" , $str , -1 , PREG_SPLIT_NO_EMPTY));

                           //下面是使用php内置函数处理字符个数
                           //return (mb_strlen($str , $code));
                    }
                    
                    /*实现中文字符串截取无乱码的方法，视当前文档编码来截取
                     *也可以使用php内置函数mb_substr()来截取
                     *array_slice()从数组中取出一段
                     */
                    function user_getSubStr($str , $start , $length = null){

                            return join("",array_slice(preg_split("//u" , $str , -1 , PREG_SPLIT_NO_EMPTY),
                            $start , $length));
                    }

                    /*获取3个数中的最大值，用最少的代码
                     *技巧：使用条件表达式 表达式1 ？表达式2 ：表达式3
                     */
                    function user_maxValue($a , $b , $c){
                            
                            return ($a > $b ? ($a > $c ? $a : $c) : ($b > $c ? $b : $c));
                    }

                    /*求两个日期的差数，日期格式：2014-05-12 | 2014-5-12 均可
                     *strtotime()函数将日期转换为时间戳,time()函数获取当前时间的时间戳
                     */
                    function user_date_days($date1 , $date2){
                            
                            //首先设置默认时区
                            date_default_timezone_set("Asia/Shanghai");

                            return (strtotime($date1) > strtotime($date2) ? 
                            ((strtotime($date1)-strtotime($date2))/(3600*24)) : 
                            ((strtotime($date2)-strtotime($date1))/(3600*24)) );
                    }
                    
                    /*不适用第3个变量交换两个变量的值
                     *技巧一：使用php内置函数list();
                     *技巧二：使用编码解码也可以
                     */
                    function user_valChange(&$value1 , &$value2){

                            //list()函数将数组中的值赋给一些变量
                            list($value1 , $value2) = array($value2 , $value1);
                    }

                    function user_valChange1(&$value1 , &$value2){
                            
                            //使用编码，在解码
                            $value1 = base64_encode($value1);
                            $value2 = base64_encode($value2);
                            $value1 = $value1."&".$value2;
                            //用 & 分割放入数组
                            $value2 = explode("&" , $value1);
                            //解码
                            $value1 = base64_decode($value2[1]);
                            $value2 = base64_decode($value2[0]);
                            
                    }

                    /*实现字符串的翻转 ：abc->cba
                     *技巧：使用正则表达式和数组实现
                     *join()==implode();将一个数组里的元素组合成一个以什么字符连接的字符串
                     *array_reverse()函数，返回一个顺序与之前数组相反的数组
                     *补充：strrev()函数，返回一个与之前顺序相反的字符串
                     *补充：chunk_split()函数,将一个字符串分割成以某个字符连接的多个小块
                     */
                    function user_strRollback($str){

                            return (join("" , array_reverse(preg_split("//u" , $str))));
                    }

                    /*根据某列对二维数组进行排序
                     *技巧：ksort();对数组按照键名进行正向排序
                     *技巧：krsort();对数组按照键名逆向排序
                     */
                    function user_arraySort($array , $row , $style = "asc"){

                            $arr_temp = array();

                            foreach ($array as $val){
                                    //将排序依据列作为数组的键名
                                    $arr_temp[$val[$row]] = $val;
                            }

                            //排序
                            if($style == "asc"){
                                ksort($arr_temp);
                            }else if($style == "desc"){
                                krsort($arr_temp);
                            }else{
                                echo "你应该输入 asc | desc";
                            }

                            return $arr_temp;
                    }

                    /*
                     *
                     */
                   function user_getFileExten($path){
                            
                            $path = str_replace("\\" , "/" , $path);
                            

                            //方法一：查找指定字符在字符串中的最后一次出现 : strrchr()函数,返回的是从指定位置开始之后的字符串
                            //return (strrchr($path , "."));
                            //方法二：strrpos():计算指定字符串在目标字符串中最后一次出现的位置
                            //return (substr($path , strrpos($path , ".")));
                            //方法三：pathinfo()；返回文件路径信息
                            //$path_parts = pathinfo($path);
                            //return ($path_parts['extension']);
                            //方法四：explode()：使用一个字符串分割另一个字符串
                            // $array = explode("." , $path);
                            // return ($array[count($array)-1]);
                            //方法五：preg_replace()：正则表达式替换,basename()返回文件中的文件名部分
                            $patten = "/^[^\.]+\.([\w]+)$/";
                            return (preg_replace($patten , '${1}' , basename($path)));

                    }

                    /*创建多级目录
                     *技巧：使用mkdir()函数 ，0777为权限
                     */
                    function user_createMoreDir($path , $mode = '0777'){

                            if(is_dir($path)){
                                //code .....
                            }else{
                                if(mkdir($path , $mode , true)){
                                    //success....
                                }else{
                                    //faild....
                                }
                            }
                    }

                    /*从一个URL中提取出文件的扩展名
                     *技巧一：使用parse_url()函数，解析一个url,返回一个数组是url的组成部分
                     */
                    function user_getUrlExten($url){
                            
                            //方法一：
                            $arr = parse_url($url);
                            $file = basename($arr['path']);
                            $ext = explode("." , $file);
                            return ($ext[count($ext)-1]);
            
                            //方法二：
                            /*$url = basename($url);
                            $point1 = strpos($url , ".");
                            $point2 = strpos($url , "?");

                            if(strstr($url , "?")){
                                return (substr($url , $point1+1 , $point2-$point1-1));
                            }else{
                                return (substr($url , $point1));
                            }*/
                    }

                    /*遍历文件夹下的所有文件和子文件
                     *技巧：递归调用；
                     *使用的php函数：opendir();打开文件夹; readdir()，读取打开文件夹下的文件
                     */
                    function user_scanDir($dir){
                            
                            $dir = str_replace("\\" , "/" , $dir);
                            $files = array();

                            if(is_dir($dir)){
                                if($handle = opendir($dir)){
                                    while(($file = readdir($handle)) !== false){
                                        if($file != "." && $file !=".."){
                                            if(is_dir($dir."/".$file)){
                                                $files[$file] = scanDir($dir."/".$file);
                                            }else if(is_file($dir."/".$file)){
                                                $files[] = $dir."/".$file;
                                            }else{
                                                //your code .....
                                            }
                                        }
                                    }
                                    closedir($handle);
                                }
                            }
                            return $files;
                    }
                    

                    /*递归遍历，实现无限分类原理*/
                    function user_classTree($array , $pid = 0 , $level = 0){

                            static  $list = array();
                            foreach ($array as $val){

                                //如果是顶级分类，则将其保存到$list中
                                //并以此节点作为根节点，遍历找其子节点
                                //@ parent_id 是数据库表中的类别父ID; cat_id是数据库表中的类别ID
                                if($val['parent_id'] == $pid){
                                    $val['level'] = $level;
                                    $list[] = $val;
                                    classTree($array , $val['cat_id'] , $level+1);
                                }
                            }
                            return $list;
                    }

                    /*实现两个文件的相对路径；
                     *array_fill():用给定的值填充数组
                     *array_merge():合并一个或者多个数组
                     */
                    function user_relativePath($path1 , $path2){

                            $path1 = str_replace("\\" , "/" , $path1);   
                            $path2 = str_replace("\\" , "/" , $path2);   
                            $arr1 = explode("/" , dirname($path1));
                            $arr2 = explode("/" , dirname($path2));
                            for ($i = 0 , $len = count($arr2) ; $i < $len ; $i++){
                                if($arr1[$i] != $arr2[$i])
                                    break;
                            }
                            //不在同一个根目录下
                            if($i == 1){
                                $return_path = array();
                            }

                            #在同一个根目录下
                            if($i != 1 && $i < $len){
                                $return_path = array_fill(0 , $len - $i , "..");
                            }

                            #在同一个目录下
                            if($i == $len){
                                $return_path = array('./');
                            }

                            $return_path = array_merge($return_path , array_slice($arr1 , $i));

                            return (implode("/" , $return_path));
                    }


                    /*
                     *冒泡排序法，给数组排序，数组传递在php中默认是值传递
                     */
                    function user_bubbleSort(&$array){

                            $len = count($array);

                            for($i = 0 ; $i < $len ; $i++){
                                for($j = 1 ; $j < $len - $i ; $j++){
                                    if($array[$j - 1] > $array[$j]){
                                        $temp = $array[$j - 1];
                                        $array[$j - 1] = $array[$j];
                                        $array[$j] = $temp;
                                    }
                                }
                            }

                    }

                    /*
                     *将一个二进制串转换为10进制数；使用strrev()反转字符串函数和pow()指数表达式函数
                     */
                    function user_bin2dec($str){

                            $str = strrev($str);
                            $len = strlen($str);
                            $result = 0;
                            for($i = 0 ; $i < $len ; $i++){
                                
                                    $result += pow(2 , $i) * $str[$i];
                            }
                            return $result;
                    }

                /**************************完毕***************************************/

