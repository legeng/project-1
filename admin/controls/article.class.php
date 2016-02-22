<?php
    
    /**
     * 后台文章管理控制器
     */
	class Article {
		function index(){
/*                  $m_sort = D('article_sort');
                    $m_article = D('article');
                    $data = $m_sort->field('id ,article_sort')->r_select(array('article','id as aid,title,picture,origin,create_time','sort_id'));
                    p($data);
*/                  //p($_POST['sort_id']);
                    //p(empty($_POST));
                    $m_article = D('article');
                    $m_sort = D('article_sort');
                    $sorts = $m_sort->field('id,article_sort')->order('id asc')->select();
                    $sort_id = intval($_POST['sort_id']);
                    if($sort_id != 0 && $sort_id !=null){
                       $_SESSION['sort_id'] = $_POST['sort_id'] ? $_POST['sort_id'] : $_SESSION['sort_id']; 
                       $total =  $m_article->where(array('sort_id'=>intval($_SESSION['sort_id'])))->total();
                       $page = new Page($total,20);
                       $article = $m_article->field('id,title,sort_id,create_time')->where(array('sort_id'=>$_SESSION['sort_id']))->order('id desc')->limit($page->limit)->select();
                    }else{
                        $total =  $m_article->total();
                        $page = new Page($total,20);
                        $article = $m_article->field('id,title,sort_id,create_time')->order('id desc')->limit($page->limit)->select();
                        unset($_SESSION['sort_id']);
                    }

                    $this->assign('article',$article);
                    $this->assign('sorts',$sorts);
                    $this->assign('sort_id',$_SESSION['sort_id']);
                    $this->assign('fpage' , $page->fpage());
                    $this->display();
            	}
                //添加文章调用模板
                function add(){
                    $m_sort = D('article_sort');
                    $sorts = $m_sort->field('id,article_sort')->order('id asc')->select();
                    $this->assign('sorts',$sorts);
                    $this->assign("date",date("Y-m-d"));
                    $this->assign("ck", Form::editor("content", "full", 500, "#FAFAFA",""));

                    $this->display();
                }

                //添加或修改文章执行操作
                function insert() {
                    
                    $article = D("article");
                    
                    $content=$_POST['content'];
                    $_POST['create_time'] = strtotime($_POST['create_time']);
                    $_POST['origin'] = strval(trim($_POST['origin']));
                    $_POST['sort_id'] = intval($_POST['sort_id']);
                    $_POST['create_person_id'] = $_SESSION['id'];
                    
                    unset($_POST['content']);

                    $content= str_replace(array("\"", "'"), array("&quot;", "&#039"), stripslashes($content));

                    $lastid=$article->insert($_POST, 1);
                    

                    if($lastid) {
                        if(!empty($_SESSION['article'])) {
                            $srcpath="./public/uploads/tmp/";
                            $article_path="./public/uploads/article/";
                            $path = "./public/uploads/article/{$lastid}/";
                            if(!file_exists($article_path)) {
                                 if(mkdir($article_path, 0755)){
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

                            foreach($_SESSION['article'] as $filename) {
                                $content = str_replace("tmp/".$filename, "article/".$lastid."/".$filename, $content);

                                rename($srcpath.$filename, $path.$filename);
                            }


                            $_SESSION['article']=array();
                        }

                        $article->update(array("id"=>$lastid, "content"=>$content,"picture"=>$picture));
                        D("article_sort")->where(array('id'=>$_POST['sort_id']))->update("article_count=article_count+1");//使类别表中的 文章数增加1

                        $this->success('添加文章成功', 1, "index");
                    }else {
                        $this->error("添加文章失败！", 2, "add");
                    }
                }

                
                //ckeditor异步上传文章的图片,上传成功后会显示在窗体中（必须的）
                function upimage() {

                    $up = new FileUpload('./public/uploads/tmp'); 

                    $up->set("allowtype", array('jpg', 'png', 'gif'));//允许上传图片类型
                    $up->set('thumb', array('width'=>300, 'height'=>300));//生成缩略图

                    if($up->upload('upload')) {
                        $filename = $up->getFileName();
                        $_SESSION['article'][]=$filename;
                        $path = B_PUBLIC."/uploads/tmp/".$filename;
                        die('<script>window.parent.CKEDITOR.tools.callFunction('.$_GET['CKEditorFuncNum'].', "'.$path.'", "");</script>');
                    } else {
                        $mess = strip_tags($up->getErrorMsg());

                        die('<script>window.parent.CKEDITOR.tools.callFunction('.$_GET['CKEditorFuncNum'].', "", "'.$mess.'");</script>');
                    }

                }


                //删除一条文章
                function del(){
                    $path = "./public/uploads/article/{$_GET['id']}" ;//文章图片所在路径，删除文章一起把图片删除掉
                    $row = D('article')->find($_GET['id']);
                    $sort_id = $row['sort_id'];
                    $article_id = $row['id'];
                    $row = D('article')->delete($article_id);
                    $row = D('article_sort')->where(array('id'=>$sort_id))->update("article_count=article_count-1");
                    if($row){
                        if(file_exists($path)){
                            deldir($path);
                        }
                        unset($sort_id,$article_id,$row);
                        $this->success('删除成功',1,'index');
                    }else{
                        unset($sort_id,$article_id,$row);
                        $this->error('删除失败',1,'index');
                    }
                }

                //更新一条文章调用模板
                function update(){
                    $article = D('article')->find($_GET["id"]);
                    $sort = D('article_sort')->field('id,article_sort')->order("id asc")->select();
                    $article['picture'] = "http://".$_SERVER['HTTP_HOST'].$article['picture'];
                    $content = $article['content'];
                    $this->assign("article",$article);
                    $this->assign("sort",$sort);
                    $this->assign("ck", Form::editor("content","full", 400, "#FAFAFA",$content));
                    $this->display(); 
                }

                //更新一条文章执行操作
                function modify(){
                    $article = D("article");

                    $content=$_POST['content'];
                    $_POST['create_time'] = strtotime($_POST['create_time']);
                    $_POST['origin'] = strval(trim($_POST['origin']));
                    $_POST['sort_id'] = intval($_POST['sort_id']);
                    $_POST['create_person_id'] = $_SESSION['id'];
                    $old_sort_id = $_POST['sortid'];
                    $flag = ($old_sort_id==$_POST['sort_id']) ? 0 : 1;//判断类别是否变化
                    unset($_POST['content'],$_POST['sortid']);

                    $content= str_replace(array("\"", "'"), array("&quot;", "&#039"), stripslashes($content));

                    $article->update($_POST, 1);

                    if($flag){
                        D('article_sort')->where(array('id'=>intval($_POST['sort_id'])))->update("article_count=article_count+1");//文章数加1
                        D('article_sort')->where(array('id'=>$old_sort_id))->update("article_count=article_count-1");//文章数减1
                    }
                    $srcpath="./public/uploads/tmp/"; //临时存放图片路径
                    $article_path="./public/uploads/article/";//文章图片路径
                    $path = "./public/uploads/article/{$_POST['id']}/";//每一条文章相关的图片
                    
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
                    if(!empty($_SESSION['article'])) {
                        foreach($_SESSION['article'] as $filename) {
                            $content = str_replace("tmp/".$filename, "article/".$_POST['id']."/".$filename, $content);

                            rename($srcpath.$filename, $path.$filename);
                        }


                        $_SESSION['article']=array();
                    }
                    
                    if($flag){
                         $article->update(array("id"=>$_POST['id'], "content"=>$content,"picture"=>$picture), array('content','picture'));
                    }else{
                         $article->update(array("id"=>$_POST['id'], "content"=>$content), array('content'));
                    }
                    
                    $this->success('修改文章成功', 1, "article/update/id/{$_POST['id']}");
                
                }

                //文章类别
                function sort(){
                    $sort = D('article_sort')->select();
                    $this->assign('sort' , $sort);
                    $this->display();
                }

                //添加文章类别
                function addsort(){
                    if($_POST){
                        $_POST['article_sort'] = strval(trim($_POST['article_sort']));
                        $_POST['detail'] = strval(trim($_POST['detail']));   
                        if(D('article_sort')->insert($_POST))
                        {   
                            $this->success("添加文章类别成功",1,"sort");
                        }else{
                            $this->error("添加文章类别失败",2,"addsort");
                        }
                    }

                    $this->display();
                }

                //更新文章类别
                function updatesort(){
                    $sort = D('article_sort')->find($_GET['id']);
                    $this->assign('sort',$sort);

                    if($_POST){
                        $_POST['article_sort'] = strval(trim($_POST['article_sort']));
                        $_POST['detail'] = strval(trim($_POST['detail']));   
                        if(D('article_sort')->update($_POST))
                        {   
                            $this->success("更新文章类别成功",1,"article/updatesort/id/{$_POST['id']}");
                        }else{
                            $this->error("更新文章类别失败",2,"article/updatesort/id/{$_POST['id']}");
                        }
                    }
                    $this->display();
                }

                //删除文章类别
                function delsort(){
                   if(D('article_sort')->where(array('id'=>$_GET['id'],"article_count"=>0))->delete()){
                        $this->success('删除类别成功',1,sort);     
                   }else{
                        $this->error("类别下面有文章，删除失败", 2,"sort");
                   }
                }

}
