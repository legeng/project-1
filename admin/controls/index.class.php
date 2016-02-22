<?php
	class Index {
		function index(){
                    //根据用户id号查询角色id信息
                        $userinfo = D('manager')->where(array('id'=>$_SESSION[id]))->find();
                        $role_id = $userinfo["roleid"];
                    //根据角色信息获取权限ids信息
                        $roleinfo = D('role')->where(array('id'=>$role_id))->find();
                        $auth_ids = $roleinfo["authid"];
                    //根据$auth_ids查询全部拥有的权限信息
                        //获取顶级权限
                        $sql = "select * from sw_auth where level = 0";
                        //如何是超级管理员要实现全部的权限
                        if($_SESSION['id'] != 1){
                            $sql .= " and id in ($auth_ids)";
                        }

                        $p_info = D('auth')->query($sql,"select");
                        
                        //取得次顶级的权限
                        $sql = "select * from sw_auth where level = 1";
                        //如果是超级管理员要实现全部权限
                        if($_SESSION["id"] != 1){
                            $sql .= " and id in ($auth_ids)";
                        }
                        $s_info = D('auth')->query($sql , 'select');


                        $this->assign('pauth_info' , $p_info);
                        $this->assign('sauth_info' , $s_info);
			$this->assign("session" , $_SESSION);
                        unset($_SESSION['src']); //避免刷新页面循环，删掉标志
			$this->display();
		}
                function welcome(){
                    $this->display(); 
                }
    	}
