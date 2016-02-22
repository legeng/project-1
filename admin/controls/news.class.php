<?php
    
    /**
     * 后台新闻管理控制器
     */
	class News {
		function index(){
                    $m_news = D('news');
                    $total =  $m_news->total();
                    $page = new Page($total,20);
                    $news = $m_news->field('id,title,create_time')->order('id desc')->limit($page->limit)->select();
                    $this->assign('news' , $news);
                    $this->assign('fpage' , $page->fpage());
                    $this->display();

            	}
                //添加新闻调用模板
                function add(){
                    $this->assign("date",date("Y-m-d"));
                    $this->assign("ck", Form::editor("content", "full", 500, "#FAFAFA",""));

                    $this->display();
                }

                //添加或修改新闻执行操作
                function insert() {
                    
                    $new = D("news");

                    $content=$_POST['content'];
                    $_POST['create_time'] = strtotime($_POST['create_time']);
                    $_POST['create_person_id'] = $_SESSION['id'];

                    unset($_POST['content']);

                    $content= str_replace(array("\"", "'"), array("&quot;", "&#039"), stripslashes($content));

                    $lastid=$new->insert($_POST, 1);
                    

                    if($lastid) {

                        $srcpath="./public/uploads/tmp/";
                        $new_path="./public/uploads/news/";
                        $path = "./public/uploads/news/{$lastid}/";
                        if(!file_exists($new_path)) {
                            if(mkdir($new_path, 0755)){
                                mkdir($path,0755);
                            }
                        }else{
                            mkdir($path,0755);
                        }

                        $up = new FileUpload($path); 

                        $up->set("allowtype", array('jpg', 'png', 'gif'));//允许上传图片类型
                        $up->set('thumb', array('width'=>110, 'height'=>120));//生成缩略图

                        if($up->upload('picture')) {
                            $filename = $up->getFileName();
                            $picture = $path.$filename;
                        }
                        if(!empty($_SESSION['news'])) {

                            foreach($_SESSION['news'] as $filename) {
                                $content = str_replace("tmp/".$filename, "news/".$lastid."/".$filename, $content);

                                rename($srcpath.$filename, $path.$filename);
                            }


                            $_SESSION['news']=array();
                        }
                        
                        $new->update(array("id"=>$lastid, "content"=>$content,"picture"=>$picture), array('content','picture'));

                        $this->success('添加新闻成功', 1, "index");
                    }else {
                        $this->error("添加新闻失败！", 2, "add");
                    }
                }

                
                //ckeditor异步上传文章的图片,上传成功后会显示在窗体中（必须的）
                function upimage() {

                    $up = new FileUpload('./public/uploads/tmp'); 

                    $up->set("allowtype", array('jpg', 'png', 'gif'));//允许上传图片类型
                    $up->set('thumb', array('width'=>300, 'height'=>300));//生成缩略图

                    if($up->upload('upload')) {
                        $filename = $up->getFileName();
                        $_SESSION['news'][]=$filename;
                        $path = B_PUBLIC."/uploads/tmp/".$filename;
                        die('<script>window.parent.CKEDITOR.tools.callFunction('.$_GET['CKEditorFuncNum'].', "'.$path.'", "");</script>');
                    } else {
                        $mess = strip_tags($up->getErrorMsg());

                        die('<script>window.parent.CKEDITOR.tools.callFunction('.$_GET['CKEditorFuncNum'].', "", "'.$mess.'");</script>');
                    }

                }


                //删除一条新闻
                function del(){
                    $path = "./public/uploads/news/{$_GET['id']}" ;//文章图片所在路径，删除文章一起把图片删除掉
                    $row = D('news')->delete($_GET["id"]);
                    if($row){
                        if(file_exists($path)){
                            deldir($path);
                        }
                        $this->success('删除成功',1,'index');
                    }else{
                        $this->error('删除失败',1,'index');
                    }
                }

                //更新一条新闻调用模板
                function update(){
                    $new = D('news')->find($_GET["id"]);
                    $new['picture'] = "http://".$_SERVER['HTTP_HOST'].$new['picture'];
                    $content = $new['content'];
                    $this->assign("new",$new);
                    $this->assign("ck", Form::editor("content","full", 500, "#FAFAFA",$content));
                    $this->display(); 
                }

                //更新一条新闻执行操作
                function modify(){
                    $new = D("news");

                    $content=$_POST['content'];
                    $_POST['create_time'] = strtotime($_POST['create_time']);
                    $_POST['create_person_id'] = $_SESSION['id'];

                    unset($_POST['content']);

                    $content= str_replace(array("\"", "'"), array("&quot;", "&#039"), stripslashes($content));

                    $new->update($_POST, 1);
                    
                    $srcpath="./public/uploads/tmp/"; //临时存放图片路径
                    $new_path="./public/uploads/news/";//新闻图片路径
                    $path = "./public/uploads/news/{$_POST['id']}/";//每一条新闻相关的图片
                    
                    if($_FILES['picture']['name']){
                        $flag = true;
                        $up = new FileUpload($path); 

                        $up->set("allowtype", array('jpg', 'png', 'gif'));//允许上传图片类型
                        $up->set('thumb', array('width'=>110, 'height'=>120));//生成缩略图

                        if($up->upload('picture')) {
                            $filename = $up->getFileName();
                            $picture = $path.$filename;
                        }
                    }
                    if(!empty($_SESSION['news'])) {
                        foreach($_SESSION['news'] as $filename) {
                            $content = str_replace("tmp/".$filename, "news/".$_POST['id']."/".$filename, $content);

                            rename($srcpath.$filename, $path.$filename);
                        }


                        $_SESSION['news']=array();
                    }
                    
                    if($flag){
                         $new->update(array("id"=>$_POST['id'], "content"=>$content,"picture"=>$picture), array('content','picture'));
                    }else{
                         $new->update(array("id"=>$_POST['id'], "content"=>$content), array('content'));
                    }
                    
                    $this->success('修改新闻成功', 1, "news/update/id/{$_POST['id']}");
                
                }

               
    	}
