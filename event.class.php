<?php
	require_once("conn.php");
	date_default_timezone_set('Asia/Shanghai');	

	class Event{
		private $conn;

		//构造函数，连接数据库
		public function __construct(){
			$this->conn = new Solivesdb();
		}

		//添加事件：参数事件数组(uname, content, happentime,place, attr),分类数组(一个事件的多个分类)
		//返回值：失败返回false，成功返回true；
		public function addEvent($arrayEvent, $arrayCname){
			//先通过uname找到uid
			$uname = $arrayEvent['uname'];
			$userObj = new user();		
			$uid = $userObj->findUidByUname($uname);

			if ($uid == false){
			//	echo "找不到该用户\n";
				return false;
			}else {
				//如果能找到则插入，事件内容，事件的发生时间，记录时间，地点和attr字段
				$content = $arrayEvent['content'];			
				$happentime = $arrayEvent['happentime'];
				$writetime = date('y-m-d H:i:s', time());
			
				//首先添加event表
				$sql = "insert into event(uid, content, writetime, happentime, place, attr) values ( '$uid', '$content', '$writetime', '$happentime', null, null);";
				$result = $this->conn->query($sql);

				//判断是否插入成功
				if ($result == false){
				//	echo "插入event表失败\n";
					return false;
				}else{
					//若成功，则获得插入的eid
					$sql = "select last_insert_id();";
					$result = $this->conn->query($sql);
					$eid = $result[0]['last_insert_id()'];
				//	echo "eid = $eid\n";
					//得到分类数组的大小
					$cnameCount = count($arrayCname);
				//	echo "cnameCount = $cnameCount\n";
					//将分类循环插入
					for ($i = 0; $i < $cnameCount; $i++){
						//首先查询在classify表中是否有cname和uid的组合，如果有则不再插入
						$sql = "select * from classify where cname = '$arrayCname[$i]' and uid = '$uid';";
						$result = $this->conn->query($sql);
						//没有则插入，建立新的分类
						if ($result == false){
							$sql = "insert into classify(cname, uid) values('$arrayCname[$i]', '$uid');";
							$result = $this->conn->query($sql);
							if ($result == false){
							//	echo "插入classify失败\n";
								return false;
							}
						//得到新分类的cid
						$sql = "select last_insert_id();";
						$result = $this->conn->query($sql);
						$cid = $result[0]['last_insert_id()'];
		//				echo "cid = $cid\n";
						//将相应的eid和cid插入到classifyEvent表中
						$sql = "insert into classifyEvent(eid, cid) values('$eid', '$cid');";
						$result = $this->conn->query($sql);
						if ($result == false){
							//echo "插入classifyEvent失败\n";
							return $result;
							}
						}else{
							$cid = $result[0]['cid'];	
							$sql = "insert into classifyEvent(eid, cid) values('$eid', '$cid');";
							$result = $this->conn->query($sql);
							return $result;
						}
					}
				}
			}
	}

		//判断事件能否修改，如果事件时间为过去的时间则不能修改，否则可以修改
		//参数为事件eid，
		//如果可以修改返回true，否则返回false；
		public function canModify($eid){
			$sql = "select happentime from event where eid = '$eid';";
			$result = $this->conn->query($sql);
			if ($result == false){
				echo "找不到事件\n";
				return false;
			}else{
				print_r ($result);
				$happentime = strtotime($result[0]['happentime']);
				$nowtime = date('y-m-d H:i:s', time());
				$nowtime = strtotime($nowtime);
				echo $happentime;
				echo $nowtime;
				if ($happentime <= $nowtime){
					echo "不能插入\n";
					return false;
				}else{
					echo "可以插入\n";
					return true;
				}
			}
		
		}

		//模糊查询事件内容
		public function vagueQueryContent($content){
			$sql = "select event.eid, content, happentime, writetime, user.name, cname from event, classify, classifyEvent, user where classifyEvent.eid = event.eid and event.uid = user.uid and classify.cid = classifyEvent.cid and event.content like '%$content%';";
			$result = $this->conn->query($sql);

			if ($result == false){
//				echo "找不到相关模糊事件内容\n";
				return null;
			}else{
//				print_r ($result);
				return $result;
			}
		}

		//模糊查询发生时间
		public function vagueQueryHappentime($happentime){
			$sql = "select event.eid, content, happentime, writetime, user.name, cname from event, classify, classifyEvent, user where classifyEvent.eid = event.eid and event.uid = user.uid and classify.cid = classifyEvent.cid and event.happentime like '%$happentime%';";

			$result = $this->conn->query($sql);

			if ($result == false){
//				echo "找不到相关模糊事件发生时间\n";
				return null;
			}else{
//				print_r($result);
				return $result;
			}
		}

		//模糊查询写入时间
		public function vagueQueryWritetime($writetime){
			$sql = "select event.eid, content, happentime, writetime, user.name, cname from event, classify, classifyEvent, user where classifyEvent.eid = event.eid and event.uid = user.uid and classify.cid = classifyEvent.cid and event.writetime like '%$writetime%';";

			$result = $this->conn->query($sql);

			if ($result == false){
//				echo "找不到相关模糊事件写入时间\n";
				return null;
			}else{
//				print_r($result);
				return $result;
			}
		}

		//模糊查询分类
		public function vagueQueryCname($cname){			
			$sql = "select event.eid, content, happentime, writetime, user.name, cname from event, classify, classifyEvent, user where classifyEvent.eid = event.eid and event.uid = user.uid and classify.cid = classifyEvent.cid and classify.cname like '%$cname%';";
		
			$result = $this->conn->query($sql);

			if ($result == false){
//				echo "找不到相关模糊分类\n";
				return null;
			}else{
//				print_r ($result);
				return $result;
			}
		}

		//模糊查询昵称
		public function vagueQueryNickname($nickname){
			$sql = "select event.eid, content, happentime, writetime, user.name, cname from event, classify, classifyEvent, user where classifyEvent.eid = event.eid and event.uid = user.uid and classify.cid = classifyEvent.cid and user.nickname like '%$nickname%';";
		
			$result = $this->conn->query($sql);

			if ($result == false){
//				echo "找不到相关模糊昵称\n";
				return null;
			}else{
//				print_r($result);
				return $result;
			}
		}

		//对事件进行查询，查询的内容有uname,content,happentime,writetime，cname
		//查询类型为模糊查询
		//查询返回为一个二维数据，需要得到eid,content,happentime,writetime, uname, cname
		public function findEvent($string){
			$resultContent = $this-> vagueQueryContent($string); 
			$resultHappentime = $this->vagueQueryHappentime($string);
			$resultWritetime = $this->vagueQueryWritetime($string);
			$resultCname = $this->vagueQueryCname($string);
			$resultNickname = $this->vagueQueryNickname($string);

			return array("Content" => $resultContent, "Happentime" => $resultHappentime, "Writetime"=>$resultWritetime, "Cname" => $resultCname, "Nickname"=>$resultNickname);
	
		}

		//删除事件，涉及event和classifyEvent表
		public function deleteEvent($eid){
			$sql = "delete from event where event.eid = '$eid';";

			$result = $this->conn->query($sql);

			if ($result == false){
			//	echo "删除事件失败\n";
				return false;
			}else{
				$sql = "select * from classifyEvent where eid = '$eid';";
				$result = $this->conn->query($sql);
				$length = count($result);

				for ($i = 0; $i < $length; $i++){
					$cid = $result[$i]['cid'];
					$sql = "delete from classifyEvent where eid = '$eid' and cid = '$cid';";

					$result = $this->conn->query($sql);
					if ($result == false){
						//echo "删除失败\n";
					}else{
					//	echo "成功\n";
					}
					return $result;
				}
			
			}
		}

		//修改事件，涉及event表
		public function modifyEvent($arrayEvent){
			$eid = $arrayEvent['eid'];
			$content = $arrayEvent['content'];
			$happentime = $arrayEvent['happentime'];
			
			$result = $this->canModify($eid);
			if ($result == false){
		//		echo "不能修改\n";
			}else{
				$writetime = date('y-m-d H:i:s', time());
				$sql = "update event set content = '$content' , happentime = '$happentime' , writetime = '$writetime' where eid = '$eid';";
				$reuslt = $this->conn->query($sql);
				if ($result == false){
		//			echo "修改失败\n";
				}else{
		//			echo "成功\n";
				}
				return $result;
		}
	}
}
?>
