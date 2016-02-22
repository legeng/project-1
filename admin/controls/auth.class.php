<?php
    
    /**
     * 权限管理控制器
     */
	class Auth {
		function index(){
                    $auth = D('auth');
                    $page = new Page($auth->total(),20);
                    $auths = D('auth')->field('id,name,pid,path')->order('path asc')->limit($page->limit)->select();
                    
                    $this->assign('auth',$auths);
                    $this->assign("src",$_SESSION["src"]);//增删改权限时，让iframe的父窗体也更新的标志
                    $this->assign('fpage' , $page->fpage());
                    $this->display();
            	}
                //添加权限
                function add(){
                    $model = D('auth');
                    $auth = $model->order('path asc')->select();
                    $this->assign('auth' , $auth);
                    $tmpname = array();
                    $tmpauthid = array();
                    $tmpcontrol = array();
                    $tmpaction = array();
                    if($_POST){
                        foreach($_POST["name"] as $k=>$v){
                            $tmpname[] = $v;
                        } 
                        foreach($_POST["authid"] as $kk=>$vv){
                            $tmpauthid[] = $vv;
                        }
                        foreach($_POST["control"] as $kkk=>$vvv){
                            $tmpcontrol[] = $vvv;
                        }
                        foreach($_POST["action"] as $kkkk=>$vvvv){
                            $tmpaction[] = $vvvv;
                        }

                        $length = count($tmpname);
            
                        for($i = 0 ; $i < $length ; $i++){
                            if($tmpauthid[$i] == 0){
                                $level = 0;
                            }else{
                                $path = $model->field('id,path')->where(array('id'=>$tmpauthid[$i]))->find();
                                $level = 1;
                            }
                            $insert = array(
                                'name'=>$tmpname[$i],
                                'pid'=>$tmpauthid[$i],
                                'control'=>$tmpcontrol[$i],
                                'action'=>$tmpaction[$i],
                                'level'=>$level
                            );
                            $row = $model->insert($insert);
                            if($row){
                                $insert['id'] = $row;
                                $insert["pid"] = $tmpauthid[$i];
                                $insert['path'] = ($tmpauthid[$i]) ? ($path["path"]."-".$row):$row;
                                $result = $model->update($insert);
                            }else{
                                $this->error('添加权限失败',2,'add');
                            }
                        }
                        if($result){
                                unset($_SESSION['src']);
                                $_SESSION["src"] = $GLOBALS["url"];
                                $this->success('添加权限成功',1,'index');
                        }else{
                                $this->error('添加权限失败',2,'add');   
                        }
                    }
                    $this->display();
                }

                //删除权限
                function del(){
                    $model = D('auth');
                    $model_r = D('role');
                    
                    $rows = $model->field('id,concat(control,"-",action) as ac')->where(array('path'=>"%'".$_GET['id']."'%"))->select();

                    if(count($rows) == 1){
                        $row = $model->field('id,concat(control,"-",action) as ac')->where(array('path'=>"%'".$_GET['id']."'%"))->find();
                        $ids = $row["id"];
                        $ac = $row["ac"];
                        $sql = 'update sw_role set authid=replace(authid,"'.$ids.'",""),ac=replace(ac,"'.$ac.'","")';

                        $model_r->beginTransaction();
                        if($model->delete($_GET["id"])){
                            if($model_r->query($sql)){
                                $model_r->commit();
                                unset($_SESSION['src']);
                                $_SESSION["src"] = $GLOBALS["url"];
                                $this->success('删除成功',1,'index');
                            }else{
                                $model_r->rollback();
                                $this->error('删除失败',2,'index');
                            }
                        }else{
                            $this->error('删除失败',2,'index');
                        }
                    }else{
                        $this->error('删除失败,该栏目下有子栏目！',2,'index');
                    }                   
                
                }

                //更新权限
                function update(){
                    $model = D('auth');
                    $auth = $model->where(array('id'=>$_GET["id"]))->find();
                    $pauth = $model->field('id,name,pid,path')->order('path asc')->select();

                    if($_POST){
                        $_POST["name"] = strval(trim($_POST["name"]));
                        /***********************************************
                          1、修改权限时，看修改权限下面是否有子权限 path
                          2、如果有子权限，将子权限也一并修改path
                          使用MySQL的replace(path,"本path","修改后的path")

                         ************************************************/
                        $pinfo = $model->where(array('id'=>$_POST["pid"]))->find();//查询父级权限的path信息
                        $sinfo = $model->where(array('id'=>$_POST["id"]))->find();//查询本级权限的path信息
                        
                        $model->update(array('id'=>$_POST[id],'name'=>$_POST['name']),1);

                        $tmp = $model->where(array('path' =>"%'".$sinfo['path']."'%" ))->total();//查询本权限下是否有子权限
                        $ids = "" ;
                        if($tmp != 1){
                            $id = $model->where(array('path' =>"%'".$sinfo['path']."'%" ))->select();
                            foreach($id as $k=>$v){
                                if(substr_count($v['path'],"-") > 0){
                                    $ids = $ids.",".$v['id'];
                                }
                            }
                        }
                        $ids = trim($ids,',');
                        $_POST["path"] = $pinfo["path"]."-".$sinfo['path'];  //组合要修改的path信息
                        $patharr = explode("-",$_POST['path']); //分割path信息
                        $patharr = array_unique($patharr);            //去除数组中重复的值
                        $_POST["path"] = trim(implode("-",$patharr),"-");   //将path信息从新组合
                        $sql = ($tmp==1) ? 'update sw_auth set path=replace(path , "'.$sinfo['path'].'" , "'.$_POST["path"].'") where id="'.$_POST['id'].'"' : 'update sw_auth set path=replace(path , "'.$sinfo['path'].'" , "'.$_POST["path"].'"),level=1 where id in("'.$ids.'")';
                        $model->query($sql);
                        //if($model->query($sql)){
                            unset($_SESSION['src']);
                            $_SESSION["src"] = $GLOBALS["url"];
                            $this->success('修改成功',1,'index');
                        /*}else{
                            $this->error('修改失败',2,'auth/update/id/"'.$_POST["id"].'"');                        
                        }*/

                }
                $this->assign('auth' , $auth);
                $this->assign('pauth' , $pauth);
                $this->display();
            }
        }
