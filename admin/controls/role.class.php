<?php
    
    /**
     * 后台角色管理控制器
     */
	class Role {
		function index(){
                    $role = D('role')->field('id , name , authid')->select();

                    $this->assign('role' , $role);
                    $this->display();
            	}
                //添加一个角色
                function add(){
                    $role = D('role');
                    if($_POST){
                        foreach($_POST as $k=>$v){
                            foreach($v as $vv){
                                $insert = array(
                                    'name'=>$vv
                                );
                                $row = $role->insert($insert);  
                            }
                        }
                        if($row){
                            $this->success('添加成功',1,'index');
                        }else{
                            $this->error('添加失败',1,'add');
                        }
                    }
                    $this->display();
                }

                //删除一个角色
                function del(){
                    $row = D('role')->delete($_GET["id"]);
                    if($row){
                        $this->success('删除成功',1,'index');
                    }else{
                        $this->error('删除失败',1,'index');
                    }
                }

                //更新一个角色
                function update(){
                    $model_r = D('role');
                    $model_a = D('auth');
                    $role = $model_r->where(array('id'=>$_GET["id"]))->find();
                    $auth = $model_a->field('id,name,path')->order('path asc')->select();
                    
                    $this->assign('role' , $role);
                    $this->assign('auth' , $auth);

                    if($_POST){
                        $_POST["name"] = strval(trim($_POST["name"]));
                        if(empty($_POST["auth"])){
                            $_POST["authid"] = "";
                            $_POST["ac"] = "";
                            $sql = 'update sw_role set name="'.$_POST["name"].'",authid="",ac="" where id="'.$_POST["id"].'"';
                            if($model_r->query($sql,'update')){
                                $this->success('修改成功',1,'index');
                            }else{
                                $this->error('修改失败',2,'role/update/id/"'.$_POST["id"].'"');
                            }
                        }else{
                            foreach($_POST["auth"] as $k=>$v){
                                $_POST["authid"] .=",".$v;
                            }
                            $_POST["authid"]  =  ltrim($_POST["authid"] , ",");
                            $ac = $model_a->field('id,concat(control,"-",action) as ac')->where($_POST["authid"])->select();
                            foreach($ac as $kk=>$vv){
                                $_POST["ac"] .=",".$vv["ac"];
                            }
                            $_POST["ac"] = trim($_POST["ac"] , ',-');
                            unset($_POST["auth"]);

                            $row = $model_r->update($_POST);
                            if($row){
                                $this->success('修改成功',1,'index');
                            }else{
                                $this->error('修改失败',2,'role/update/id/"'.$_POST["id"].'"');
                            }
                        }
                    }

                                       
                    $this->display(); 
                }

                //给一个角色授权
                function grant(){
                    $this->display();
                }

                //显示该角色有多少用户
                function show(){

                    if($_GET["flag"] == "user"){
                        $user = D('manager')->where(array('roleid'=>$_GET["id"]))->select();
                        $rolename = D('role')->field('id ,name')->where(array('id'=>$_GET["id"]))->find();
                    
                        $this->assign('role' , $rolename);
                        $this->assign('user' , $user);

                        unset($user,$rolename);//不释放前台会有缓存
                        $this->display('showuser'); 
                    }
                    if($_GET["flag"] == "grant"){
                        $rolename = D('role')->field('id ,name,authid')->where(array('id'=>$_GET["id"]))->find();

                        if(!empty($rolename["authid"])){
                            $grant = D('auth')->where($rolename["authid"])->order('path asc')->select();
                        }

                        $this->assign('role' , $rolename);
                        $this->assign('grant' , $grant);
                        
                        unset($rolename,$grant);//同上
                        $this->display('showgrant');
                    }
                }
               
    	}
