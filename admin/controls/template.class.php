<?php
    
    /**
     * 后台模板管理控制器
     */
	class Template {
                private $template_dir = './home/views/default/index';
		function index(){
                    $templates = user_scandir($this->template_dir);
                    $this->assign('templates' , $templates);
                    $this->display();
                }

                function lists(){
                    $templates = user_scandir($this->template_dir);
                    $temparr = array();

                    foreach($templates as $k=>$v){
                        $name = substr($v , strrpos($v , '/')+1 , strrpos($v , '.')-strrpos($v , '/')-1);
                        if('_preview' !== $name){
                            $temparr[$k]['name'] = $name;
                            $temparr[$k]['path'] = substr($v , 0 , strrpos($v , '.'));
                            $temparr[$k]['ac'] = 'http://'.$_SERVER['HTTP_HOST'].'/index.php/index/'.$temparr[$k]['name'];
                        }
                    }
                    $this->assign('templates' , $temparr);
                    $this->display();
                
                }

                function add(){
                    if($_POST){
                        $name = strval(trim($_POST['name']));
                        $content = strval(trim($_POST['content']));
                        if(!empty($name) && !empty($content)){
                            //添加函数
                            $class = file_get_contents('./home/controls/index.class.php');
                            $replace = <<<EOF

                function $name(){
                    \$this->display(\$this->preview);
                }
        }
EOF;
                            $index = strripos($class , '}');
                            $nclass = substr_replace($class , $replace , $index);
                            //写入文件 
                            if(file_put_contents('./home/controls/index.class.php' , $nclass)){
                                if(file_put_contents($this->template_dir.'/'.$name.'.'.TPLPREFIX , $content)){
                                    $this->success($name.'.html 模板添加成功' , 1 , 'lists'); 
                                }else{
                                    file_put_contents('./home/controls/index.class.php' , $class);
                                    $this->assign('name' , $name);
                                    $this->assign('content' , $content);
                                    $this->error($name.'.html 模板添加失败' , 2 , 'add'); 
                                }
                            }else{
                                $this->assign('name' , $name);
                                $this->assign('content' , $content);
                                $this->error($name.'.html 模板添加失败' , 2 , 'add'); 
                            }
                        }else{
                            $this->assign('name' , $name);
                            $this->assign('content' , $content);
                            $this->error('模板名称或内容不能为空' , 2 , 'add'); 
                        }
                    }
                    $this->display();    
                }

                function check(){
                    $name = strval(trim($_POST['name']));
                    $templates = user_scandir($this->template_dir);
                    $flag = false;
                    $pattern = '/\b((a(bstract|nd|rray|s))|(c(a(llable|se|tch)|l(ass|one)|on(st|tinue)))|(d(e(clare|fault)|ie|o))|(e(cho|lse(if)?|mpty|nd(declare|for(each)?|if|switch|while)|val|x(it|tends)))|(f(inal|or(each)?|unction))|(g(lobal|oto))|(i(f|mplements|n(clude(_once)?|st(anceof|eadof)|terface)|sset))|(n(amespace|ew))|(p(r(i(nt|vate)|otected)|ublic))|(re(quire(_once)?|turn))|(s(tatic|witch))|(t(hrow|r(ait|y)))|(u(nset|se))|(__halt_compiler|break|list|(x)?or|var|while))\b/';
                    foreach($templates as $v){
                        if($name.'.'.TPLPREFIX === substr($v ,  strrpos($v , '/')+1)){
                            $flag = true;
                        }else{
                            $count = preg_match($pattern , $name);
                            if($count){
                                $flag = true;
                            }
                        }
                    }
                    if($flag){
                        echo json_encode(array('code'=>'0000' , 'message'=>'success' , 'data'=>'1')); 
                    }else{
                        echo json_encode(array('code'=>'0000' , 'message'=>'success' , 'data'=>'0')); 
                    }
                }

                function update(){
                    $template_name = strval($_POST['template']);
                    $content = strval($_POST['content']); 
                    //先保存源文件，在写入原文件
                    $path = './home/views/default/restore';
                    if(!file_exists($path)){
                        @mkdir($path , 0775);
                    }
                    if(copy($this->template_dir.'/'.$template_name , $path.'/'.$template_name)){
                        if(file_put_contents($this->template_dir.'/'.$template_name , $content)){
                            $this->success($template_name.' 模板修改成功',1);
                        }else{
                            $this->error($template_name.' 模板修改失败',3);
                        }
                    }
                
                }

                function loadtemplate(){
                    $template_name = $_POST['template'];
                    if(!empty($_POST['flag']) && 'restore' === strval(trim($_POST['flag']))){

                        $content = file_get_contents('./home/views/default/restore/'.$template_name);
                        echo json_encode(array('code'=>'0000' , 'message'=>'success' , 'data'=>$content)); 

                    }else{

                        $content = file_get_contents($this->template_dir.'/'.$template_name);
                        echo json_encode(array('code'=>'0000' , 'message'=>'success' , 'data'=>$content)); 
                    }
                }

                function preview(){
                   $name = strval($_POST['name']);
                   $content = strval($_POST['content']);
                   $_SESSION['preview'] = "_preview";
                   $file = "_preview".'.'.TPLPREFIX;
                   file_put_contents('./home/views/default/index/'.$file,$content);
                   $href = 'http://'.$_SERVER['HTTP_HOST'].'/index.php/index/'.substr($name , 0 , strpos($name , '.')); 
                   echo json_encode(array('code'=>'0000' , 'message'=>'success' , 'href'=>$href)); 
                }

                function del(){
                    $name = strval(trim($_GET['id']));
                    $pattern = "/function\s+$name\(.*\)\{\s+.*\s+\}/";
                    $class = file_get_contents('./home/controls/index.class.php');
                    $nclass = preg_replace($pattern , '' , $class);

                    if(file_put_contents('./home/controls/index.class.php' , $nclass)){
                        $name = $name.'.'.TPLPREFIX;
                        if(unlink($this->template_dir.'/'.$name)){
                            unlink('./home/views/default/restore/'.$name);
                            $this->success($name.' 模板删除成功' , 1 , 'lists');     
                        }else{
                            file_put_contents('./home/controls/index.class.php' , $class);
                            $this->error($name.' 模板删除失败' , 2 , 'lists');
                        }
                    }else{
                        $this->error($name.' 模板删除失败' , 2 , 'lists');
                    }
                }

                function renames(){
                    $name = strval(trim($_GET['id']));//原始模板名称
                    $nname = strval(trim($_GET['nid'])); //修改的模板名称
                    $pattern = "/function\s+$name\(/";
                    $replace = "function $nname(";
                    $class = file_get_contents('./home/controls/index.class.php');
                    $nclass = preg_replace($pattern , $replace , $class);
                    
                    if(file_put_contents('./home/controls/index.class.php' , $nclass)){
                        $name = $name.'.'.TPLPREFIX;
                        $nname = $nname.'.'.TPLPREFIX;
                        if(rename($this->template_dir.'/'.$name , $this->template_dir.'/'.$nname)){
                            rename('./home/views/default/restore/'.$name , './home/views/default/restore/'.$nname);
                            $this->success($name.' 模板重命名成功' , 1 , 'lists');     
                        }else{
                            file_put_contents('./home/controls/index.class.php' , $class);
                            $this->error($name.' 模板重命名失败' , 2 , 'lists');
                        }
                    }else{
                        $this->error($name.' 模板重命名失败' , 2 , 'lists');
                    }
                }

                function loadfile(){
                    $fileField = 'uploadfile';
                    $tmpfile = $_FILES[$fileField]['tmp_name'];
                    $originName = substr($_FILES[$fileField]['name'] , 0 , strrpos($_FILES[$fileField]['name'] , '.'));
                    $content = file_get_contents($tmpfile);
                    echo json_encode(array('code'=>'0000' , 'message'=>'success' , 'data'=>array('content'=>$content , 'name'=>$originName))); 
                }

                function loadUrl(){
                    $url = strval(trim($_POST['url']));
                    //解析url获取主机名
                    $urlinfo = parse_url($url);
                    $content = file_get_contents($url);
                    //url替换拼接
                    $css = '/<\s*link\s+[^>]*?href\s*=\s*(?:\'|\")(.*?)(?:\'|\")[^>]*?\/?\s*>/i';
                    $js  = '/<\s*script\s+[^>]*?src\s*=\s*(?:\'|\")(.*?)(?:\'|\")[^>]*?\/?\s*>\s*<\/script>/i';
                    $img = '/<\s*img\s+[^>]*?src\s*=\s*(?:\'|\")(.*?)(?:\'|\")[^>]*?\/?\s*>/i';

                    preg_match_all($css , $content , $match_1);
                    preg_match_all($js , $content , $match_2);
                    preg_match_all($img , $content , $match_3);

                    if($match_1[1]){
                        foreach($match_1[1] as $k=>$v){
                            if(!(preg_match('/^(?:http|https|\/\/)/' , $v))){
                                $pattern[] = $v;
                                $replace[] = $urlinfo['scheme'].'://'.str_replace('./' , '/' , $urlinfo['host'].'/'.$v);
                            }
                        }
                    }

                    if($match_2[1]){
                        foreach($match_2[1] as $k=>$v){
                            if(!(preg_match('/^(?:http|https|\/\/)/' , $v))){
                                $pattern[] = $v;
                                $replace[] = $urlinfo['scheme'].'://'.str_replace('./' , '/' , $urlinfo['host'].'/'.$v);
                            }
                        }
                    }

                    if($match_3[1]){
                        foreach($match_3[1] as $k=>$v){
                            if(!(preg_match('/^(?:http|https|\/\/)/' , $v))){
                                $pattern[] = $v;
                                $replace[] = $urlinfo['scheme'].'://'.str_replace('./' , '/' , $urlinfo['host'].'/'.$v);
                            }
                        }
                    }
                    $content = str_replace($pattern , $replace , $content);
                    echo json_encode(array('code'=>'0000' , 'message'=>'success' , 'data'=>$content)); 
                }

                function resource(){
                    $path = './home/views/default/resource/user';
                    if($_FILES){
                        if(!file_exists($path)){
                           mkdir($path , 0775);
                        } 
                        $up = new FileUpload($path); 

                        $up->set("allowtype", array('css', 'js'));//允许上传图片类型
                        $up->set("israndname", false);//是否允许随机文件名

                        if(!($up->upload('resource'))) {
                            $this->error($up->getErrorMsg(),3,'resource');
                        }else{
                            $this->success('模板资源上传成功',1,'resource');
                        }
                    }else{
                        $resource = user_scandir($path);
                        $temparr = array();

                        foreach($resource as $k=>$v){
                            $name = substr($v , strrpos($v , '/')+1);
                            $temparr[$k]['name'] = $name;
                            $temparr[$k]['ac'] = 'http://'.$_SERVER['HTTP_HOST'].'/home/views/default/resource/user/'.$temparr[$k]['name'];
                        }
                        $this->assign('resource' , $temparr);
                    }
                    $this->display(); 
                }

                function delres(){
                    $name = strval(trim($_GET['id']));
                    if(unlink('./home/views/default/resource/user/'.$name)){
                        $this->success('模板资源删除成功',1,'resource');
                    }else{
                        $this->error('模板资源删除失败',2,'resource');
                    } 
                }
               
    	}
