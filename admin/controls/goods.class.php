<?php
    
    /**
     * 后台商品管理控制器
     */
	class Goods {
		function index(){
                    $goods = D('good')->field('id ,url,name,rank')->order("rank asc")->select();
                    $this->assign('goods' , $goods);
                    $this->display();
            	}
                //添加一个商品
                function addgood(){
                    $good = D('good');

                    if($_POST){
                        $insertid = $good->insert($_POST);  
                        if($insertid){
                            if($_FILES) {
                                $srcpath="./public/uploads/tmp/";
                                $good_path="./public/uploads/goods/";
                                $new_path="./public/uploads/goods/{$insertid}/";
                                if(!file_exists($good_path)) {
                                    if(mkdir($good_path,0755)){
                                        mkdir($new_path,0755);
                                    }
                                }

                                $up = new FileUpload($new_path); 

                                $up->set("allowtype", array('jpg', 'png', 'gif'));//允许上传图片类型
                                $up->set('thumb', array('width'=>110, 'height'=>120));//生成缩略图

                                if($_FILES['picture']['name']){
                                    if($up->upload('picture')) {
                                        $_POST['picture'] = $new_path.$up->getFileName();
                                    }
                                    if($_FILES['picture1']['name']){
                                        if($up->upload('picture1')) {
                                            $_POST['picture1'] = $new_path.$up->getFileName();
                                        }
                                        if($_FILES['picture2']['name']){
                                            if($up->upload('picture2')) {
                                                $_POST['picture2'] = $new_path.$up->getFileName();
                                            }
                                            if($_FILES['picture3']['name']){
                                                if($up->upload('picture3')) {
                                                    $_POST['picture3'] = $new_path.$up->getFileName();
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            
                                $update = array(
                                        'id'=>$insertid,
                                        'picture'=>$_POST['picture'],
                                        'picture1'=>$_POST['picture1'],
                                        'picture2'=>$_POST['picture2'],
                                        'picture3'=>$_POST['picture3']
                                    );
                            if($good->update($update)){
                                $this->success('添加成功',1,'index');
                            }else{
                            
                            } 
                        }else{
                            $this->error('添加失败',1,'add');
                        }
                    }
                    $this->display();
                }

                //删除一个商品(需要完善，只要删除了商品，和商品有关的店铺和评论，详情和促销活动全部一起删除)
                function delgood(){
                    $path = "./public/uploads/goods/{$_GET['id']}" ;//商品图片所在路径，删除文章一起把图片删除掉
                    $row = D('good_store')->where(array('good_id'=>$_GET['id']))->delete();
                    $row = D('good_comment')->where(array('good_id'=>$_GET['id']))->delete();
                    $row = D('good_detail')->where(array('good_id'=>$_GET['id']))->delete();
                    $row = D('good_promotion')->where(array('good_id'=>$_GET['id']))->delete();
                    $row = D('good')->delete($_GET["id"]);
                    if($row){
                        if(file_exists($path)){
                            deldir($path);
                        }
                        $this->success('删除成功',1,'index');
                    }else{
                        $this->error('删除失败',1,'index');
                    }
                }

                //更新一个商品
                function updategood(){
                    $good = D('good');
                    $result = $good->where(array('id'=>$_GET["id"]))->find();
                    $result['picture'] = $result['picture'] ? "http://".$_SERVER['HTTP_HOST'].$result['picture'] : ''; 
                    $result['picture1'] = $result['picture1'] ? "http://".$_SERVER['HTTP_HOST'].$result['picture1'] : ''; 
                    $result['picture2'] = $result['picture2'] ? "http://".$_SERVER['HTTP_HOST'].$result['picture2'] : ''; 
                    $result['picture3'] = $result['picture3'] ? "http://".$_SERVER['HTTP_HOST'].$result['picture3'] : ''; 
                    $this->assign('good' , $result);

                    if($_POST){
                        $new_path="./public/uploads/goods/{$result['id']}/";
                        $up = new FileUpload($new_path); 

                        $up->set("allowtype", array('jpg', 'png', 'gif'));//允许上传图片类型
                        //$up->set('thumb', array('width'=>110, 'height'=>120));//生成缩略图

                        if($_FILES['picture']['name']){
                            if($up->upload('picture')) {
                                $_POST['picture'] = $new_path.$up->getFileName();
                            }
                        }
                        if($_FILES['picture1']['name']){
                            if($up->upload('picture1')) {
                                $_POST['picture1'] = $new_path.$up->getFileName();
                            }
                        }
                        if($_FILES['picture2']['name']){
                            if($up->upload('picture2')) {
                                $_POST['picture2'] = $new_path.$up->getFileName();
                            }
                        }
                        if($_FILES['picture3']['name']){
                            if($up->upload('picture3')) {
                                $_POST['picture3'] = $new_path.$up->getFileName();
                            }
                        }
                    }

                    if($good->update($_POST)){
                        $this->success("更新成功",1,"index"); 
                    }
                                      
                    $this->display(); 
                }
        
        //商品所属店铺，如果有则显示店铺信息，如果没有则显示添加页面
        function goodstore(){
            $m_store = D('good_store');
            $good_id = $_GET["id"];
            $store = $m_store->where(array('good_id'=>$good_id))->find();
            if($store){
                $store['trust'] = $store['trust'] ? "http://".$_SERVER['HTTP_HOST'].$store['trust'] : ''; 
                $this->assign("store",$store); 
                $this->display();   
            }else{
                $this->assign("good_id",$good_id); 
                $this->display("addstore");
            }
        }
        
        //增加一个店铺
        function addstore(){
             $store = D('good_store');
             if($_POST){
                 $insertid = $store->insert($_POST);  
                 if($insertid){
                     $path="./public/uploads/goods/{$_POST['good_id']}/";
                     $up = new FileUpload($path); 
                     $up->set("allowtype", array('jpg', 'png', 'gif'));//允许上传图片类型
                     //$up->set('thumb', array('width'=>110, 'height'=>120));//生成缩略图

                     if($_FILES['trust']['name']){
                         if($up->upload('trust')) {
                             $_POST['trust'] = $path.$up->getFileName();
                         }
                     }

                     $update = array(
                             'id'=>$insertid,
                             'trust'=>$_POST['trust']
                             );
                     if($store->update($update)){
                         $this->success('添加成功',1,'index');
                     }else{

                     } 
                 }else{
                     $this->error('添加失败',1,'add');
                 }
             }
             $this->display();
        }

        //更新一个店铺
        function updatestore(){
            $m_store = D('good_store');
            $id = $_GET["id"];
            $store = $m_store->where(array('id'=>$id))->find();
            $store['trust'] = $store['trust'] ? "http://".$_SERVER['HTTP_HOST'].$store['trust'] : ''; 
            $this->assign("store",$store);
            
            if($_POST){
                $path="./public/uploads/goods/{$_POST['good_id']}/";
                $up = new FileUpload($path); 
                $up->set("allowtype", array('jpg', 'png', 'gif'));//允许上传图片类型
                //$up->set('thumb', array('width'=>110, 'height'=>120));//生成缩略图

                if($_FILES['trust']['name']){
                    if($up->upload('trust')) {
                        $_POST['trust'] = $path.$up->getFileName();
                    }
                }

                if($m_store->update($_POST)){
                    $this->success('更新成功',1,"goods/goodstore/id/{$_POST['good_id']}");
                }else{
                    $this->error('更新失败',1,"goods/updatestore/id/{$_POST['id']}");
                } 
            }

            $this->display();
        }

        //查看该商品的评论
        function commentlist(){
            $_SESSION['good_id'] = $_GET["id"] ? $_GET["id"] : $_SESSION["good_id"];
            $m_comment = D('good_comment');
            $total =  $m_comment->where(array('good_id'=>$_SESSION["good_id"]))->total();
            $page = new Page($total,5);
            $comment = D('good_comment')->field('id,who,comments,create_time')->where(array('good_id'=>$_SESSION["good_id"]))->order('id desc')->limit($page->limit)->select();
        

            $this->assign('good_id' , $_SESSION["good_id"]);
            $this->assign('comment' , $comment);
            $this->assign('fpage' , $page->fpage());

            $this->display();
        }

        //为商品添加评论
        function addcomment(){
            $good_id = $_GET["id"];
            $this->assign('good_id',$good_id);
            $comment = D('good_comment');
            $arrtmp = array();
            if($_POST){
                $_POST["good_id"] = $good_id;
                $length = count($_POST["who"]);
                if(count($_POST["who"]) == 1){
                    $_POST["who"] = $_POST["who"][0];
                    $_POST["comments"] = $_POST["comments"][0];
                    $_POST["create_time"] = $_POST["create_time"][0];
                    $row = $comment->insert($_POST);
                }else{
                    foreach($_POST as $k=>$v){
                        foreach($v as $vv){
                            $arrtmp[] = strval(trim($vv));
                        }
                    }
                    for($i = 0 ; $i < (count($arrtmp)/count($_POST)) ; $i++){
                        $insert = array(
                                'good_id'=>$_POST['good_id'],
                                'who'=>$arrtmp[$i],
                                'comments'=>$arrtmp[$i+$length],
                                'create_time'=>$arrtmp[$i+2*$length],
                                );

                        $row = $comment->insert($insert);
                    }
                }
                if($row){
                    $this->success("添加商品评论成功",1,"goods/commentlist/id/{$_POST['good_id']}");
                }else{
                    $this->error("添加商品评论失败",1,"goods/addcomment/id/{$_POST['good_id']}");
                }

            }
            $this->display();
        }

        //删除商品的评论
        function delcomment(){
            $row = D('good_comment')->delete($_GET["id"]);
            if($row){
                if(file_exists($path)){
                    deldir($path);
                }
                $this->success('删除成功',1,'index');
            }else{
                $this->error('删除失败',1,'index');
            } 
        }

        //商品的详情，有就显示，没有就添加
        function gooddetail(){
            $m_detail = D('good_detail');
            $good_id = $_GET["id"];
            $detail = $m_detail->where(array('good_id'=>$good_id))->find();
            $content = $detail["good_detail"];
            if($detail){
                $this->assign("ck", Form::editor("content", "full", 500, "#FAFAFA",$content));
                $this->assign("detail",$detail); 
                $this->display();   
            }else{
                $this->assign("good_id",$good_id); 
                $this->assign("ck", Form::editor("content", "full", 500, "#FAFAFA",""));
                $this->display("adddetail");
            }
        }

        //添加商品详情
        function adddetail(){
                    $detail = D("good_detail");

                    $content=$_POST['content'];

                    unset($_POST['content']);

                   // $content= str_replace(array("\"", "'"), array("&quot;", "&#039"), stripslashes($content));

                    $lastid=$detail->insert($_POST, 1);
                    

                    if($lastid) {

                        if(!empty($_SESSION['gooddetail'])) {
                            $srcpath="./public/uploads/tmp/";
                            $new_path="./public/uploads/goods/";
                            $path = "./public/uploads/goods/{$_POST['good_id']}/";


                            foreach($_SESSION['gooddetail'] as $filename) {
                                $content = str_replace("tmp/".$filename, "goods/".$_POST['good_id']."/".$filename, $content);

                                rename($srcpath.$filename, $path.$filename);
                            }


                            $_SESSION['gooddetail']=array();
                        }

                        $detail->update(array("id"=>$lastid, "good_detail"=>$content), array('content'));

                        $this->success('添加商品详情成功', 1, "goods/gooddetail/id/{$_POST['good_id']}");
                    }
        }

        //ckeditor异步上传文章的图片,上传成功后会显示在窗体中（必须的）
        function upimage() {

            $up = new FileUpload('./public/uploads/tmp'); 

            $up->set("allowtype", array('jpg', 'png', 'gif'));//允许上传图片类型
            //$up->set('thumb', array('width'=>300, 'height'=>300));//生成缩略图

            if($up->upload('upload')) {
                $filename = $up->getFileName();
                $_SESSION['gooddetail'][]=$filename;
                $path = B_PUBLIC."/uploads/tmp/".$filename;
                die('<script>window.parent.CKEDITOR.tools.callFunction('.$_GET['CKEditorFuncNum'].', "'.$path.'", "");</script>');
            } else {
                $mess = strip_tags($up->getErrorMsg());

                die('<script>window.parent.CKEDITOR.tools.callFunction('.$_GET['CKEditorFuncNum'].', "", "'.$mess.'");</script>');
            }

        }

        //修改商品详情
        function updatedetail(){
            $detail = D('good_detail')->where(array('good_id'=>$_GET['id']))->find();
            $content = $detail["good_detail"];
            $this->assign('detail',$detail);
            $this->assign("ck", Form::editor("content", "full", 500, "#FAFAFA",$content));
            $this->display();
        }

        //修改商品详情执行操作
        function updatedetailaction(){
            $detail = D("good_detail");

            $content=$_POST['content'];

            unset($_POST['content']);

            if(!empty($_SESSION['gooddetail'])) {
                $srcpath="./public/uploads/tmp/";
                $new_path="./public/uploads/goods/";
                $path = "./public/uploads/goods/{$_POST['good_id']}/";


                foreach($_SESSION['gooddetail'] as $filename) {
                    $content = str_replace("tmp/".$filename, "goods/".$_POST['good_id']."/".$filename, $content);

                    rename($srcpath.$filename, $path.$filename);
                }


                $_SESSION['gooddetail']=array();
            }

            $detail->update(array("id"=>$_POST['id'], "good_detail"=>$content), array('content'));

            $this->success('修改商品详情成功', 1, "goods/gooddetail/id/{$_POST['good_id']}");

        }

        //商品的促销活动，有就显示，没有就添加
        function goodpromotion(){
            $m_promotion = D('good_promotion');
            $good_id = $_GET["id"];
            $promotion = $m_promotion->where(array('good_id'=>$good_id))->find();
            $content = $promotion["good_promotion"];
            if($promotion){
                $this->assign("ck", Form::editor("content", "full", 500, "#FAFAFA",$content));
                $this->assign("promotion",$promotion); 
                $this->display();   
            }else{
                $this->assign("good_id",$good_id); 
                $this->assign("ck", Form::editor("content", "full", 500, "#FAFAFA",""));
                $this->display("addpromotion");
            }
        }

        //添加商品的促销活动
        function addpromotion(){
            $promotion = D("good_promotion");

            $content=$_POST['content'];

            unset($_POST['content']);

            // $content= str_replace(array("\"", "'"), array("&quot;", "&#039"), stripslashes($content));

            $lastid=$promotion->insert($_POST, 1);


            if($lastid) {

                if(!empty($_SESSION['gooddetail'])) {
                    $srcpath="./public/uploads/tmp/";
                    $new_path="./public/uploads/goods/";
                    $path = "./public/uploads/goods/{$_POST['good_id']}/";


                    foreach($_SESSION['gooddetail'] as $filename) {
                        $content = str_replace("tmp/".$filename, "goods/".$_POST['good_id']."/".$filename, $content);

                        rename($srcpath.$filename, $path.$filename);
                    }


                    $_SESSION['gooddetail']=array();
                }

                $promotion->update(array("id"=>$lastid, "good_promotion"=>$content), array('content'));

                $this->success('添加商品促销活动成功', 1, "goods/goodpromotion/id/{$_POST['good_id']}");
            }
        }

        //修改商品的促销活动
        function updatepromotion(){
            $promotion = D('good_promotion')->where(array('good_id'=>$_GET['id']))->find();
            $content = $promotion["good_promotion"];
            $this->assign('detail',$promotion);
            $this->assign("ck", Form::editor("content", "full", 500, "#FAFAFA",$content));
            $this->display();
    
        }
        //修改商品的促销活动执行
        function updatepromotionaction(){
           $promotion = D("good_promotion");

            $content=$_POST['content'];

            unset($_POST['content']);

            if(!empty($_SESSION['gooddetail'])) {
                $srcpath="./public/uploads/tmp/";
                $new_path="./public/uploads/goods/";
                $path = "./public/uploads/goods/{$_POST['good_id']}/";


                foreach($_SESSION['gooddetail'] as $filename) {
                    $content = str_replace("tmp/".$filename, "goods/".$_POST['good_id']."/".$filename, $content);

                    rename($srcpath.$filename, $path.$filename);
                }


                $_SESSION['gooddetail']=array();
            }

            $detail->update(array("id"=>$_POST['id'], "good_promotion"=>$content), array('content'));

            $this->success('修改商品促销活动成功', 1, "goods/goodpromotion/id/{$_POST['good_id']}");
 
        }
}
