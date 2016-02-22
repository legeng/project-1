<?php
    /**
     * 系统管理员控制
     */
	class Manage {
		function index(){
                    $manager = D('role')->field('id,name as Rname')
                    ->r_select(array('manager' , 'id as Mid, name as Mname , time , roleid , disable' , 'roleid'));

                    
                    $this->assign("manager" , $manager);
                    
                    $this->display();
            	}
                //添加管理员用户
                function add(){
                    $role = D('role')->field('id,name')->select();
                    $this->assign('role' , $role);
                    
                    $arrtmp = array();
                    $time = time();
                    $manager = D('manager');
                    if($_POST){
                        $length = count($_POST["name"]);
                        if(count($_POST["name"]) == 1){
                            $_POST["name"] = $_POST["name"][0];
                            $_POST["pwd"] = md5($_POST["pwd"][0]);
                            $_POST["roleid"] = $_POST["roleid"][0];
                            $_POST["time"] = $time;
                            $row = $manager->insert($_POST);
                        }else{
                            foreach($_POST as $k=>$v){
                                foreach($v as $vv){
                                    $arrtmp[] = strval(trim($vv));
                                }
                            }
                            for($i = 0 ; $i < (count($arrtmp)/count($_POST)) ; $i++){
                                 $insert = array(
                                     'name'=>$arrtmp[$i],
                                     'pwd'=>md5($arrtmp[$i+$length]),
                                     'roleid'=>$arrtmp[$i+2*$length],
                                     'time'=>$time
                                 );
                                
                                $row = $manager->insert($insert);
                            }
                        }
                        if($row){
                            $this->success("添加管理员成功",1,'index');
                        }else{
                            $this->error("添加管理员失败",1,'add');
                        }

                    }
                        $this->display();
                }

                //删除管理员用户
                function del(){
                    $row = D('manager')->delete($_GET["id"]);
                    if($row){
                        $this->success("删除成功",1,'index');
                    }else{
                        $this->error('删除失败',1,'index');
                    }
                }

                //更新管理员用户
                function update(){
                                         
                    $manager = D('manager')->field('id , name , roleid , disable')->where(array('id'=>$_GET["id"]))
                    ->find();
                    $roleinfo = D('role')->field('id , name')->select();

                

                    if($_POST){
                        $_POST["name"] = strval(trim($_POST["name"]));
                        $_POST["pwd"] = strval(trim($_POST["pwd"]));

                        if(!empty($_POST["pwd"])){
                            $_POST["pwd"] = md5($_POST["pwd"]);
                        
                        }else{
                            unset($_POST["pwd"]);
                        }
                        $row = D('manager')->update();
                        if($row)
                            $this->success("修改成功",1,'index');
                        else
                            $this->error("修改失败",1,'manage/update/id/"'.$_POST['id'].'"');
                    }

                    $this->assign("manager" , $manager);
                    $this->assign("roleinfo", $roleinfo);
                    $this->display();
                }
               
    	}
