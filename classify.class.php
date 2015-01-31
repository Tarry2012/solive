<?php
require_once("conn.php");

class Classify{
	private $conn;
	private $uid;

	//创建数据库对象
	public function __construct() {
		$this->conn = new Solivesdb();
	}

	//根据name获取uid
	public function getUid($name) {

		$selectSql = "select uid from user where name = '$name';";
		$result = $this->conn->query($selectSql);
		if ($result == FALSE)
			return FALSE;
		else {
			return $result;
		}
	}

	//从classify中根据cname获取cid
	public function getCidFromC($cname) {

		$selectSql = "select cid from classify where uid = $this->uid && cname = '$cname';";
		$result = $this->conn->query($selectSql);
		if ($result == FALSE) {
			echo "该用户的此分组不存在\n";
			return FALSE;
		} else {
			echo "该用户的此分组已存在\n";
			return $result;
		}
	}

	//寻找该uid用户的other分类的cid
	public function getOtherCid() {

		$selectSql = "select cid from classify where uid = $this->uid && cname = 'other';";
		$result = $this->conn->query($selectSql);
		if ($result == FALSE) {
			echo "没有找到该用户的other分类\n";
			return FALSE;
		} else {
			return $result;
		}
	}

	//添加分类
	public function addType($array) {
		//获取用户uid
		$result = $this->getUid($array[0]);
		if ($result == FALSE)
			return FALSE;
		else {
			$this->uid = $result[0]['uid'];
			//从classify表中获取cid
			$result = $this->getCidFromC($array[1]);
			if ($result == FALSE ) {//如果分组不存在，就直接插入新分组
				$insertSql = "insert into classify(uid, cname) values($this->uid, '$array[1]');";
				$result = $this->conn->query($insertSql);
				if ($result == FALSE)
					return FALSE;
				else 
					return TRUE;
			} else {//分组已存在，则返回错误
				return FALSE;
			}
		}
	}

	//删除分类
	public function delType($array) {

		$result = $this->getUid($array[0]);
		if ($result == FALSE)
			return FALSE;
		else {
			$this->uid = $result[0]['uid'];
			$result = $this->getCidFromC($array[1]);
			if ($result == FALSE ) //若分组不存在，则返回错误
				return FALSE;
			else { //若分组存在，则获取该分组的cid，删除
				$cid = $result[0]['cid'];
				//从classify表中删除该分组
				$deleteSql = "delete from classify where cid = $cid;";
				$result = $this->conn->query($deleteSql);
				if ($result == FALSE)
					return FALSE;
				else {
					//获取该用户的other分组的cid
					$result = $this->getOtherCid();
					if ($result == FALSE)
						return FALSE;
					else {
						$cidOther = $result[0]['cid'];
						//将被删除cid下的所有eid的cid置为other的cid
						$updateSql = "update classifyEvent set cid = $cidOther where cid = $cid;" ;
						$result = $this->conn->query($updateSql);
						if ($result == FALSE)
							return FALSE;
						else 
							return TRUE;
					}
				} 
			}
		}
	}


	//修改分类
	public function modifyType($array1){
		//array1:name.oldname,newname
		if ($array1[2] == 'other') {
			echo "重命名不可为other\n";
			return FALSE;
		}
		$result = $this->getUid($array1[0]);
		if ($result == FALSE )
			return FALSE;
		else {
			$this->uid = $result[0]['uid'];
			$result = $this->getCidFromC($array1[1]);
			if ($result == FALSE)
				return FALSE;
			else {
				$cid = $result[0]['cid'];
				$updateSql = "update classify set cname = '$array1[2]' where cid = $cid ;";
				$result = $this->conn->query($updateSql);
				if ($result == FALSE)
					return FALSE;
				else 
					return TRUE;
			}
		}
	}
}


?>
