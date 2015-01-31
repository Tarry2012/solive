<?php

require_once ("conn.php");

//用户类
class User{
	private $conn;

	//构造函数，初始化数据库
	public  function __construct(){
		$this->conn = new Solivesdb();
	}


	//通过用户登录名得到用户ID，如果用户名不存在返回false，存在返回uid
	public function findUidByUname($name){
		$sql = "select uid from user where name='$name';";
		$result = $this->conn->query($sql);
		if ($result == false){
			return false;
		}else {
			$uid = $result[0]['uid'];
			echo "uid = $uid\n";
			return $result[0]['uid'];
		}
	}

	//用户登录:参数用户登录名和密码
	//登录成功返回true，失败返回false
	public function userLogin($name, $passwd){
		$uid = $this->findUidByUname($name);
		if ($uid == false){
//			echo "该用户不存在\n";
			return false;
		}else{
			$passwd = md5($passwd);
			$sql = "select passwd from user where uid = '$uid';";
			$result = $this->conn->query($sql);
			$getPasswd = $result[0]['passwd'];
            
			if ($passwd == $getPasswd){
				return true;
			}else{
				return false;
			}
		}		
	}
	

	//增加用户，参数为存储用户信息的数组(user表中所有字段)，添加成功返回true，失败返回false
	public function addUser($arrayUser){
		$result = $this->findUidByUname($arrayUser['name']);

		if ($result){
//			echo "用户已存在\n";
			return false;
		}else{
			$name = $arrayUser['name'];
			$passwd = md5($arrayUser['passwd']);
			$nickname = $arrayUser['nickname'];
			$sex = (int)$arrayUser['sex'];
			$wechat = $arrayUser['wechat'];
		
			$sql = "insert into user(name, head, passwd, nickname, sex, wechat, email, phone, status, workplace, pwdpasswd) values('$name', null, '$passwd', '$nickname', '$sex', '$wechat', null, null, 1, null, null);";
			
			$result = $this->conn->query($sql);
			return $result;
			
		}
	}
	
	//修改用户信息，参数为存储用户原信息的数组(head, nickname, sex, email, phone, status, place, workplace, pwdpasswd)，修改成功返回true，失败返回false；
	public function modifyUser($arrayUser){
		$uid = $this->findUidByUname($arrayUser['name']);
		
		if ($uid == false){
//			echo "用户不存在\n";
			return false;
		}else {
			$head = $arrayUser['head'];
			$passwd = md5($arrayUser['passwd']);
			$nickname = $arrayUser['nickname'];
			$sex = (int)$arrayUser['sex'];
			$email = $arrayUser['email'];
			$phone = $arrayUser['phone'];
			$status = (int)$arrayUser['status'];
			$place = $arrayUser['place'];
			$workplace = $arrayUser['workplace'];
			$pwdpasswd = md5($arrayUser['pwdpasswd']);
			
			$sql = "update user set head = '$head', passwd = '$passwd', nickname = '$nickname', sex = '$sex', email = '$email', phone = '$phone', status = '$status', place = '$place', workplace = '$workplace', pwdpasswd = '$pwdpasswd' where uid = '$uid';"; 
			$result = $this->conn->query($sql);
			return $result;
		}
	}	
	
	//精确查询：根据用户登录名查询用户所有信息，返回存储用户所有信息的数组(user表中所有信息)，失败返回false
	public function queryUser($name){
			$uid = $this->findUidByUname($name);
			
			if ($uid == false){
//				echo "用户不存在\n";
				return false;
			}else{
			  	
				$sql = "select * from user where uid = '$uid'";
				$result = $this->conn->query($sql);
				
				if ($result == false){
//					 echo "查询失败！\n";
					return false;
				}else{
					$arrayUser = array();
					$arrayUser['name'] = $result[0]['name'];
					$arrayUser['head'] = $result[0]['head'];
					$arrayUser['passwd'] = $result[0]['passwd'];
					$arrayUser['nickname'] = $result[0]['nickname'];
					$arrayUser['sex'] = $result[0]['sex'];
					$arrayUser['email'] = $result[0]['email'];
					$arrayUser['phone'] = $result[0]['phone'];
					$arrayUser['status'] = $result[0]['status'];
					$arrayUser['place'] = $result[0]['place'];
					$arrayUser['workplace'] = $result[0]['workplace'];
					$arrayUser['pwdpasswd'] = $result[0]['pwdpasswd'];
					
					return $arrayUser;
				}
			}
	
	}	

	//模糊查询用户登录名，参数为检索词，返回值为与检索词模糊匹配的用户登录名的所有记录
	//返回一个二维数组，数组下标从0开始
	public function vagueQueryUname($uName)
	{
		$sql = "select * from user where name like '%$uName%'"	;
		$result = $this->conn->query($sql);

		if ($result == false){
//			echo "模糊查询不到用户登录名\n";
			return false;
		}else{
			print_r($result);
			return $result;
		}
	}	

	//模糊查询用户昵称，参数为检索词，返回值为与检索词模糊匹配的用户昵称的所有记录
	//返回一个二维数组，数组下标从0开始
	public function vagueQueryNickname($nickname)
	{
		$sql = "select * from user where nickname like '%$nickname%'";
		$result = $this->conn->query($sql);

		if ($result == false){
//			echo "模糊查询不到用户昵称\n";
			return false;
		}else{
			print_r($result);
			return $result;	
		}
	}


	//获得用户密码包密码,失败返回false，成功返回密码包密码 
	public function findPwdpassword($string){
		$sql = "select pwdpasswd from user where name = '$string';";
		$result = $this->conn->query($sql);

		if ($result == false){
	//		echo "查询密码包密码失败\n";
			return false;
		}else{
			$pwdpasswd = $result[0]['pwdpasswd'];
			if (empty($pwdpasswd)){
	//			echo "该用户没有设定密码包密码\n";
				return false;
			}else{
	//			echo "pwdpasswd = $pwdpasswd\n";
				return $pwdpasswd;
			}
		}
	}
}
?>
