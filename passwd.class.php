<?php

	require_once("conn.php");
	require_once("user.class.php");

	class Passwd{
		private $conn;

		public function checkPwdpassword($arrayPwd){
			$name = $arrayPwd['name'];
			$userObj = new User();
			$uid = $userObj->findUidByUname($name);
			$pwdPasswd = $arrayPwd['pwdpasswd'];

			if ($uid == false){
				echo "该用户不存在\n";
				return false;
			}else{
				$pwdpasswd = md5($pwdpasswd);
				$getPasswd = $userObj->findPwdpassword($name);
				
				if ($getPasswd == false){
					return false;
				}else{
					if ($pwdpasswd == $getPasswd){
						return true;
					}else{
						return false;
					}
				}
			}

		}

		public function addPasswd($arrayPwd){
			$name = $arrayPwd['name'];
			$userObj = new User();
			$uid = $userObj->findUidByUname($name);
			$pname = $arrayPwd['pname'];
			$passwd = md5($array['pwdpasswd']);
			
			if ($uid == false){
				echo "找不到该用户\n";
				return false;
			}else{
				$canAdd = $this->conn->checkPwdPassword($arrayPwd);
				if ($canAdd == false){
					return false;
				}else{
					$sql = "insert into passwd(uid, pname, passwd) values('$uid', '$pname', '$passwd');";
					$result = $this->conn->query($sql);
					return $result;
				}
			}
		}
		public function delPasswd($arrayPwd){
				$name = $arrayPwd['name'];
				$pname = $arrayPwd['pname'];
				$userObj = new User();
				$uid = $userObj->findUidByUname($name);
			
				if ($uid == false){
					echo "找不到该用户\n";
					return false;
				}else{
					$canDel = $this->conn->checkPwdPassword($arrayPwd);
					if ($canDel == false){
						return false;
					}else{
						$sql = "delete from passwd where uid = '$uid' and pname = '$pname';";
						$result = $this->conn->query($sql);
						return $result;
					}
				}
			}
		
		public function modifyPasswd($arrayPwd){
			$name = $arrayPwd['name'];
			$userObj = new User();
			$uid = $userObj->findUidByUname($name);
			$pwdpasswd = md5($arrayPwd['pwdpasswd']) ;
			$pname = $arrayPwd['pname'];

			if ($uid == false){
				echo "找不到该用户\n";
				return false;
			}else{
				$canMod = $this->conn->checkPwdPassword($arrayPwd);
				if ($canMod == false){
					return false;
				}else{
					$sql = "update passwd set pname = '$pname' and passwd = '$passwd' where uid = '$uid';";
					$result = $this->conn->query($sql);
					return $result;
				}
			}
		}
}

?>
