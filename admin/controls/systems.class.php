<?php
        /**
         *系统设置控制类
         */
	class Systems {
                function index(){
                    $define = get_defined_constants(true);
                    if(!empty($define['user']['FRIEND'])){
                        $friend = explode('=' , $define['user']['FRIEND']);
                        foreach($friend as $k=>$v){
                            $friends[] = explode('&' , $v);
                        }
                        $this->assign('friends' , $friends);
                    }
                    $this->assign('define' , $define['user']);
                    $this->display();
		}
		function update(){
                    $friend = '';
                    if($_POST){
                        if($_FILES['PICTURE']){
                            $up = new FileUpload('./public/uploads/images/'); 

                            $up->set("allowtype", array('jpg', 'png', 'gif'));//允许上传图片类型
                            //$up->set('thumb', array('width'=>110, 'height'=>120));//生成缩略图

                            if($up->upload('PICTURE')) {
                                $filename = $up->getFileName();
                                $_POST['PICTURE'] = '/public/uploads/images/'.$filename;
                            }
                        }
                        if(count($_POST['FRIENDURL'])>1){
                            foreach($_POST['FRIENDURL'] as $k=>$v){
                                if(!empty($v))
                                    $friend .= $v.'&'.$_POST['FRIENDNAME'][$k].'=';
                            }

                            $_POST['FRIEND'] = rtrim($friend,'=');
                            unset($friend , $_POST['FRIENDURL'] , $_POST['FRIENDNAME']);
                        }

                        //进行替换
                        //$pattern = define("KEYWORD", "linux,php,java,xsphp,cms");
                        foreach($_POST as $k=>$v){
                            $pattern[] = '/define\(\"'.$k.'\",\s*.+\);/';
                            $replace[] = 'define("'.$k.'","'.$v.'");';
                        }

                        $config = file_get_contents('./config.inc.php');
                        $config = preg_replace($pattern , $replace , $config);

                        if(file_put_contents('./config.inc.php' , $config)){
                            $this->success('保存成功',1);
                        }else{
                            $this->error('保存失败',1);
                        }
                    }
		}
    	}
