<?php
    
    /**
     * 后台留言管理控制器
     */
	class Message {
		function index(){
                    $message = D('message');
                    $total =  $message->total();
                    $page = new Page($total,20);
                    $messages = $message->field('id,title,create_time')->order('id desc')->limit($page->limit)->select();
                    $this->assign('message' , $messages);
                    $this->assign('fpage' , $page->fpage());
                    $this->display();

            	}
                //添加留言调用模板
                function add(){
                    $message = D('message');
                    if($_POST){
                        $_POST['create_time'] = strtotime($_POST['create_time']);
                        if($message ->insert($_POST)){
                            $this->success('留言添加成功',1,'index'); 
                        }else{
                            $this->error('留言添加失败',3); 
                        }
                    }
                    $this->display();
                }



                //删除一条留言
                function del(){
                    $row = D('message')->delete($_GET["id"]);
                    if($row){
                        $this->success('删除成功',1,'index');
                    }else{
                        $this->error('删除失败',1,'index');
                    }
                }

                //更新一条新闻调用模板
                function update(){
                    $message = D('message')->find($_GET["id"]);
                    $this->assign("message",$message);
                    if($_POST){
                        $_POST['create_time'] = strtotime($_POST['create_time']);
                        if(D('message')->update($_POST)){
                            $this->success('更新成功',1,'index');
                        }else{
                            $this->error('更新失败',1);
                        }
                    }
                    $this->display(); 
                }

    	}
