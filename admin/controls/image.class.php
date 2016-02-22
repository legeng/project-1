<?php
    
    /**
     * 后台相册管理控制器
     */
	class Image {
		function index(){
                    //所有相册下的图片
                    $album = user_scanDir('./public/uploads/images') ;
                    $total_img = array();

                    foreach($album as $k=>$v){
                        if(is_array($v)){
                            foreach($v as $kk=>$vv){
                                if("." !==$vv && '..' !== $vv){
                                    $total_img[] = "/public/uploads/images/{$k}/{$vv}";  
                                }
                            }
                        }else{
                            $total_img[] = substr($v , 1);
                        }
                    }

                    $this->assign('total_img' , $total_img);
                    $this->assign('album' , $album);
                    $this->display();
            	}

		function add(){
                    $album = user_scanDir('./public/uploads/images') ;
                    $this->assign('album' , $album);
                    $this->display();
            	}

                function del(){
                   $fileName = strval($_POST['name']);
                   unlink('.'.$fileName);
                   echo json_encode(array('code'=>'0000' , 'message'=>'success')); 
                }

                function search(){
                    $name = strval($_POST['name']);
                    $time = strval($_POST['time']); 
                    $data = array();
                    
                    if($name && empty($time)){
                         $files = user_scanDir('./public/uploads/images/'.$name);
                         foreach($files as $k=>$v){
                            $data[] = "<img src='".substr($v,1)."' alt='' class='picture' style='float:left;width:190px;height:185px;border: 5px solid #ddd;background:#fff;cursor:hand;' />";
                         }
                    }
                    if($time && empty($name)){
                        $files = user_scandir('./public/uploads/images');
                        foreach($files as $key=>$val){
                            if(is_array($val)){
                                foreach($val as $k=>$v){
                                    if('.' !== $v && '..' !== $v){
                                        if(substr_count($v,$time)){
                                            $data[] = "<img src='/public/uploads/images/".$key.'/'.$v."' alt='' class='picture' style='float:left;width:190px;height:185px;border: 5px solid #ddd;background:#fff;cursor:hand;' />";
                                        } 
                                    }
                                }
                            }else{
                                if(substr_count($val , $time)){
                                    $data[] = "<img src='".substr($val,1)."' alt='' class='picture' style='float:left;width:190px;height:185px;border: 5px solid #ddd;background:#fff;cursor:hand;' />";
                                }
                            }
                        }
                    
                    }
                    if($name && $time){
                         $files = user_scanDir('./public/uploads/images');
                         foreach($files as $k=>$v){
                            if(is_array($v) && ($k==$name)){
                                foreach($v as $kk=>$vv){
                                    if('.' !== $vv && '..' !== $vv){
                                        if(substr_count($vv,$time)){
                                            $data[] = "<img src='/public/uploads/images/".$k.'/'.$vv."' alt='' class='picture' style='float:left;width:190px;height:185px;border: 5px solid #ddd;background:#fff;cursor:hand;' />";
                                        }
                                    }
                                }
                            }else{
                                if(substr_count($v , $time)){
                                    $data[] = "<img src='".substr($v,1)."' alt='' class='picture' style='float:left;width:190px;height:185px;border: 5px solid #ddd;background:#fff;cursor:hand;' />";
                                }
                            }
                         }
                    
                    
                    }
                    if(!$time && !$name){
                        $album = user_scanDir('./public/uploads/images') ;

                        foreach($album as $k=>$v){
                            if(is_array($v)){
                                foreach($v as $kk=>$vv){
                                    if("." !==$vv && '..' !== $vv){
                                        $data[] = "<img src=/public/uploads/images/".$k.'/'.$vv." alt='' class='picture' style='float:left;width:190px;height:185px;border: 5px solid #ddd;background:#fff;cursor:hand;' />";
                                    }
                                }
                            }else{
                                $data[] = "<img src='".substr($v,1)."' alt='' class='picture' style='float:left;width:190px;height:185px;border: 5px solid #ddd;background:#fff;cursor:hand;' />";
                            }
                        }
                    }

                    echo json_encode(array('code'=>'0000' , 'message'=>'success' , 'data'=>$data));
                }

                function save(){
                    $name = strval($_POST['name']);
                    if($name){
                        $imgpath = './public/uploads/images/'.$name;
                        if(!is_dir($imgpath)){
                            @mkdir($imgpath);
                        }
                        
                        $files = user_scandir('./public/uploads/images/');
                        $date = date('Y-m-d');
                        foreach($files as $key=>$val){
                            if(is_file($val) && substr_count($val,$date)){
                                copy($val,$imgpath."/".substr($val,strrpos($val,'/')));
                                unlink($val);
                            } 
                        }

                        $this->success('新建相册成功',3,'index');
                    }else{
                        $this->error('请输入相册名称',3);
                    }
                
                }

		function saveimg(){
                    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                    header("Cache-Control: no-store, no-cache, must-revalidate");
                    header("Cache-Control: post-check=0, pre-check=0", false);
                    header("Pragma: no-cache");


                    // Support CORS
                    // header("Access-Control-Allow-Origin: *");
                    // other CORS headers if any...
                    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                        exit; // finish preflight CORS requests here
                    }


                    if ( !empty($_REQUEST[ 'debug' ]) ) {
                        $random = rand(0, intval($_REQUEST[ 'debug' ]) );
                        if ( $random === 0 ) {
                            header("HTTP/1.0 500 Internal Server Error");
                            exit;
                        }
                    }

                    // header("HTTP/1.0 500 Internal Server Error");
                    // exit;


                    // 5 minutes execution time
                    @set_time_limit(5 * 60);

                    // Uncomment this one to fake upload time
                    usleep(5000);

                    // Settings
                    // $targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
                    $targetDir = './public/uploads/tmp';
                    $uploadDir = './public/uploads/images';

                    $cleanupTargetDir = true; // Remove old files
                    $maxFileAge = 5 * 3600; // Temp file age in seconds


                    // Create target dir
                    if (!file_exists($targetDir)) {
                        @mkdir($targetDir);
                    }

                    // Create target dir
                    if (!file_exists($uploadDir)) {
                        @mkdir($uploadDir);
                    }

                    // Get a file name
                    if (isset($_REQUEST["name"])) {
                        $fileName = $_REQUEST["name"];
                    } elseif (!empty($_FILES)) {
                        $fileName = $_FILES["file"]["name"];
                    } else {
                        $fileName = uniqid("file_");
                    }
                    //文件类型
                    $fileType = strrchr($fileName , ".");
                    //随机文件名
                    $fileName = date('Y-m-d').'-'.md5($fileName.time()).$fileType;

                    $md5File = @file('md5list.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    $md5File = $md5File ? $md5File : array();

                    if (isset($_REQUEST["md5"]) && array_search($_REQUEST["md5"], $md5File ) !== FALSE ) {
                        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id", "exist": 1}');
                    }

                    $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
                    $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

                    // Chunking might be enabled
                    $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
                    $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;

                    // Remove old temp files
                    if ($cleanupTargetDir) {
                        if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                            die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
                        }

                        while (($file = readdir($dir)) !== false) {
                            $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                            // If temp file is current file proceed to the next
                            if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
                                continue;
                            }

                            // Remove temp file if it is older than the max age and is not the current file
                            if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
                                @unlink($tmpfilePath);
                            }
                        }
                        closedir($dir);
                    }

                    
                    // Open temp file
                    if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
                        die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
                    }

                    if (!empty($_FILES)) {
                        if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                            die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
                        }

                        // Read binary input stream and append it to temp file
                        if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                            die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                        }
                    } else {
                        if (!$in = @fopen("php://input", "rb")) {
                            die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                        }
                    }

                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }

                    @fclose($out);
                    @fclose($in);

                    rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");

                    $index = 0;
                    $done = true;
                    for( $index = 0; $index < $chunks; $index++ ) {
                        if ( !file_exists("{$filePath}_{$index}.part") ) {
                            $done = false;
                            break;
                        }
                    }
                    if ( $done ) {
                        if (!$out = @fopen($uploadPath, "wb")) {
                            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
                        }

                        if ( flock($out, LOCK_EX) ) {
                            for( $index = 0; $index < $chunks; $index++ ) {
                                if (!$in = @fopen("{$filePath}_{$index}.part", "rb")) {
                                    break;
                                }

                                while ($buff = fread($in, 4096)) {
                                    fwrite($out, $buff);
                                }

                                @fclose($in);
                                @unlink("{$filePath}_{$index}.part");
                            }

                            flock($out, LOCK_UN);
                        }
                        @fclose($out);
                    }

                    // Return Success JSON-RPC response
                    die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
            	}
               
    	}
