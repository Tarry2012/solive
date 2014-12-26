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
				echo "找不到该用户\n";
				return false;
			}else {
				//如果能找到则插入，事件内容，事件的发生时间，记录时间，地点和attr字段
				$content = $arrayEvent['content'];			
				$happentime = $arrayEvent['happentime'];
				$writetime = date('y-m-d H:i:s', time());
			//	$place = $arrayEvent['place'];
			//	$attr = $arrayEvent['attr'];
			
				//首先添加event表
				//$sql = "insert into event(uid, content, writetime, happentime, place, attr) values ( '$uid', '$content', '$writetime', '$happentime', '$place', '$attr');";
				$sql = "insert into event(uid, content, writetime, happentime, place, attr) values ( '$uid', '$content', '$writetime', '$happentime');";
				$result = $this->conn->query($sql);

				//判断是否插入成功
				if ($result == false){
					echo "插入event表失败\n";
					return false;
				}else{
					//若成功，则获得插入的eid
					$sql = "select last_insert_id();";
					$result = $this->conn->query($sql);
				//	print_r ($result);
					$eid = $result[0]['last_insert_id()'];
					echo "eid = $eid\n";

					//得到分类数组的大小
					$cnameCount = count($arrayCname);
					echo "cnameCount = $cnameCount\n";
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
								echo "插入classify失败\n";
								return false;
							}
						}
						//得到新分类的cid
						$sql = "select last_insert_id();";
						$result = $this->conn->query($sql);
						$cid = $result[0]['last_insert_id()'];
						echo "cid = $cid\n";
						//将相应的eid和cid插入到classify-event表中
						$sql = "insert into classifyEvent(eid, cid) values('$eid', '$cid');";
						$result = $this->conn->query($sql);
						if ($result == false){
							echo "插入class-event失败\n";
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
			$happentime = $arrayEvent['happentime'];

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
	//			echo $happentime;
	//			echo $nowtime;
				if ($happentime <= $nowtime){
					echo "不能插入\n";
					return false;
				}else{
					return true;
				}
			}
		
		}


		public function vagueQueryContent($content){
			$sql = "select event.eid, content, happentime, writetime, cname from event, classify where event.content like '%$content%' and event.eid = classify.eid and event.uid = classify.uid;";

			$result = $this->conn->query($sql);

			if ($result == false){
				echo "找不到相关模糊事件内容\n";
				return false;
			}else{
				print_r ($result);
			}
		}

		public function vagueQueryHappentime($happentime){
			$sql = "select event.eid, content, happentime, writetime, cname from event, classify where event.happentime like '%$happentime%' and event.eid  = classify.eid and event.uid = classify.uid;";

			$result = $this->conn->query($sql);

			if ($result == false){
				echo "找不到相关模糊事件发生时间\n";
				return false;
			}else{
				print_r($result);
			}
		}

		public function vagueQueryWritetime($writetime){
			$sql = "select event.eid, content, happentime, writetime, cname from event, classify where event.writetime like '%$writetime%' and event.eid = classify.eid and event.uid = classify.uid;";

			$result = $this->conn->query($sql);

			if ($result == false){
				echo "找不到相关模糊事件写入时间\n";
				return false;
			}else{
				print_r($result);
			}
		}

		public function vagueQueryCname($cname){
			$sql = "select event.eid, content, happentime, writetime, cname from event, classify where classify.cname like '%$cname%' and event.eid = classify.eid and event.uid = classify.uid;";

			$result = $this->conn->query($sql);

			if ($result == false){
				echo "找不到相关模糊分类\n";
				return false;
			}else{
				print_r ($result);
			}
		}
		
		//对事件进行查询，查询的内容有uName,content,happentime,writetime，cname
		//查询类型为模糊查询
		//查询返回为一个二维数据，需要得到eid,content,happentime,writetime.cname
		public function findEvent($arrayEvent){
			$uname = $arrayEvent['uname'];
			$nickname = $arrayEvent['nickname'];
			$content = $arrayEvent['content'];
			$happentime = $arrayEvent['happentime'];	
			$writetime = $arrayEvent['writetime'];
			$cname = $arrayEvent['cname'];
			$returnArray = array();

			//用来模糊查找用户
			$findUnameObj = new user();
			
			


		}

		public function deleteEvent($eid){
			$sql = "delete from event where event.eid = '$eid';";

			$result = $this->conn->query($sql);

			if ($result == false){
				echo "删除事件失败\n";
				return false;
			}else{
				$sql = "delete from classifyEvent where eid = '$eid' and cid = '$cid';";
				$result = $this->conn->query($sql);
				if ($result == false){
					echo "从classifyEvent删除失败\n";
					return false;
				}else{
					return true;
				}
			}


		}

		public function modifyEvent($arrayEvent){
			//找到eid
		}
	}
?>
