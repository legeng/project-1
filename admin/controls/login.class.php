<?php

	/**
	 * 后台用户登录控制器
	 */
	class Login extends Action{
		function index(){
			$this->display();
		}

		function login(){
			$this->validate();
                         

			$user=D("manager")->field('id,name,time,roleid,disable')
				->where(array("name"=>$_POST["username"], "pwd"=>md5($_POST["userpwd"])))
				->find();
			if($user){
                                        if($user["disable"] != 1){
                                            $this->error("对不起,您已经被禁止登陆!", 3 ,"index");
                                        }else{
					    $_SESSION=$user;
                                            $_SESSION["time"] = date('Y-m-d' , $_SESSION["time"]);
					    $_SESSION["isLogin"] = 1;

                                            $currTime = time();
                                            $update = array(
                                                'id'=>$_SESSION["id"],
                                                'time'=>$currTime
                                            );

                                            D("manager")->update($update);

					    $this->redirect("index/index");
					}
			
			}else{
				$this->error("用户名或密码错误,请重新登录！", 3, "index");
			}
		}

		function logout(){
			$username=$_SESSION["name"];
			
			$_SESSION=array();
			
			if(isset($_COOKIE[session_name()])) {
				setCookie(session_name(), '', time()-3600, '/');
			}

			session_destroy();

			
			$this->success("再见{$username}, 退出成功!", 1, "index");
		}

		private function validate(){

                        $_POST["username"] = strval(trim($_POST["username"]));
                        $_POST["userpwd"] = strval(trim($_POST["userpwd"]));
                    
			$stats=false;
			$errormess="";
			if(!preg_match('/^\S+$/i', $_POST["username"])){
				$errormess.="用户名不能为空!<br>";
				$stats=true;	
			}
			if(!preg_match('/^\S+$/i', $_POST["userpwd"])){
				$errormess.="用户密码不能为空!<br>";
				$stats=true;	
			}
			if(strtoupper($_POST["code"])!=$_SESSION["code"]){
				$errormess.="验证码错误!<br>";
				$stats=true;	
			}
			if($stats){
				$this->error($errormess, 3, "index");
			}
		}

		function code(){
			echo new Authcode();
		}

                function checkcode(){
                    if(strtoupper($_GET["code"]) != $_SESSION["code"]){
				$errormess="error";
				echo $errormess;	
		    }
                }
	}
