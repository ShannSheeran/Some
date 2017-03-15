<?php
class my_sql {
	private $config_file = 'config/my_sql_config.php';
	private $push_file = 'class/push.php';
	private $conn;
	private $sql;
	private $result = array();
	private $database_host = '';
	private $database_user = '';
	private $database_password = '';
	private $database_database = '';
	private $database_notification = '';
	private $database_notification_details = '';
	private $database_device_user = '';
	
	private $myfile;
	private $push;

	/**
	 * 构造函数
	 */
	public function __construct() {
		$this->myfile = new myfile();
		$this->init ();
	}
	
	public function __destruct() {
		unset($this->conn);
		unset($this);
	}
	
	/**
	 * 没有参数，没有返回，把配置文件的内容录入。
	 */
	private function init() {
		if (is_file ( PUSH_ROOT . '/'.$this->push_file )) {
			$this->push = new push();
		}else{
			$this->result ['errcode'] =14001;
			$this->myfile->put(sprintf(STATUS_14001,$this->mysql_file),1);
			die();
		}
		if (is_file ( PUSH_ROOT . '/'.$this->config_file )) {
			require_once PUSH_ROOT . '/'.$this->config_file; //
			$this->database_host = DATABASE_HOST;
			$this->database_user = DATABASE_USER;
			$this->database_password = DATABASE_PASSWORD;
			$this->database_name = DATABASE_NAME;
			$this->database_notification = DATABASE_NOTIFICATION;
			$this->database_notification_details = DATABASE_NOTIFICATION_DETAILS;
			$this->database_device_user = DATABASE_HOST_DEVICE_USER;
			
			$this->connnect ();
			$this->result = array (
					'errcode' => 0,
					'success' => array(),
					'fail' => array()
			);
		} else {
			$this->myfile->put(sprintf(STATUS_12001,$this->config_file),1);
			die ();
		}
	}

	/**
	 * 没有参数，没有返回值，连接数据库。
	 */
	private function connnect() {
		$this->conn = mysql_connect ( $this->database_host, $this->database_user, $this->database_password );
		if(!$this->conn){
			$this->result['errcode'] = 18000;
			$this->myfile->put(sprintf(STATUS_18000,$this->database_user,$this->database_host,$this->database_password),1);
			die();
		}
		mysql_query ( "set names utf8" );
		mysql_select_db ( $this->database_name );
	}
	/**
	 * 主要执行方法
	 * @param number $NotificationId 默认0，没有就查找没发的
	 * @param number $type 默认0，1.IOS,2安卓,4短信
	 * @return Ambigous <$result, multitype:>
	 */
	public function action($NotificationId = 0,$type = 0){
		$Notification = $this->getNotification($NotificationId);
		$result = $this->assign($Notification,$type);
		
		if(!$this->get_num_Notification($Notification['id'])){
			$this->updateNotification($Notification['id']);
			$this->result['updateNotification'] =true;
		}else{
			$this->result['updateNotification'] =false;
		}

		if($this->result['updateNotification']){
			if(count($result['success'] )+count($result['fail']) > 0){
				$this->myfile->put(sprintf(STATUS_0,$Notification['id'],count($result['success'] )+count($result['fail']),count($result['success'] ),count($result['fail'] ) ) ,1);
			}else{
				$this->result['errcode'] = 20003;
				$this->myfile->put(sprintf(STATUS_20003,$Notification['id']) ,1);
			}
		}else{
			$this->result['errcode'] = 11001;
			$this->myfile->put(sprintf(STATUS_11001,$Notification['id'],$type,count($result['success'] )+count($result['fail']),count($result['success'] ),count($result['fail'] ) ) ,1);
		}
		return $result;
	}
	
	/**
	 * 
	 * @param number $NotificationId
	 * @param number $type
	 * @return Ambigous <$result, multitype:>
	 */
	public function actionforAll($NotificationId = 0,$type = 0){
		$Notification = $this->getNotification($NotificationId);
		$from = 0;
		while($deviceTokens = $this->getDevicebyAll($type,'id',LIMIT,$from) ){
			$result = $this->push->Send($deviceTokens, $Notification['content'],$Notification['title']);
			$this->merge_result($result);
			$from += LIMIT;
			sleep(SLEEP_TIME);
		}
		$this->updateNotification($Notification['id']);
		$this->myfile->put(sprintf(STATUS_0,$Notification['id'],count($result['success'] )+count($result['fail']),count($result['success'] ),count($result['fail'] ) ) ,1);
		return $result;
	}
	
	/**
	 * 根据类型查找数据，推送，更新
	 * @param array $Notification id,content,title
	 * @param number $type
	 * @return $result sussess,fail,updateNotification
	 */
	private function assign($Notification,$type = 0){
		// 全部或只是IOS
		if(!$type or $type == 1) {
			while($deviceTokens = $this->getDevice_tokens($Notification['id'],1,LIMIT ) ){
				$result = $this->push->SendToIOS($deviceTokens, $Notification['content']);
				$this->merge_result($result);
				$this->updateforpush($result);
				sleep(SLEEP_TIME);
			}
		}
		// 全部或只是Android
		if(!$type or $type == 2) {
			while($deviceTokens = $this->getDevice_tokens($Notification['id'],2,LIMIT ) ){
				$result = $this->push->SendToAndroid($deviceTokens, $Notification['content'], $Notification['title']);
				$this->merge_result($result);
				$this->updateforpush($result);
				sleep(SLEEP_TIME);
			}
		}
		// 全部或只是短信
		if(!$type or $type == 4) {
			while($deviceTokens = $this->getDevice_tokens($Notification['id'],4,LIMIT ) ){
				$result = $this->push->SendToAndroid($deviceTokens, $Notification['content'], $Notification['title']);
				$this->merge_result($result);
				$this->updateforpush($result);
				sleep(SLEEP_TIME);
			}
		}
		return $this->result;;
	}

	/**
	 * 把分批发送的结果整合在一齐，并且根据分批结果更新状态
	 * @param array $result
	 */
	public function merge_result($result){
		$this->result['success'] = $this->result['success']?array_merge($this->result['success'],$result['success']):$result['success'];
		$this->result['fail'] = $this->result['fail']?array_merge($this->result['fail'],$result['fail']):$result['fail'];
	}
	/**
	 * 检查有没有需要推送的消息，或者检查指定ID是否已经推送。
	 * @param number $NotificationId        	
	 * @return array $Notification 如果检测出有没推送的消息，返回数据库中整一条信息
	 */
	public function getNotification($NotificationId = 0) {
		$Notification = '';
		$where = "";
		$where .= " `delete` = 0 ";
		if ($NotificationId == 0) {
			$where .= " AND `status` = 1 ";
		}else{
			$where .= " AND `id` = $NotificationId ";
		}
		$this->sql = "SELECT id,title,content,`status` FROM $this->database_notification WHERE  $where ORDER BY id LIMIT 0,1";
		$rst = mysql_query ( $this->sql );
		if ($rst) {
			$Notification = mysql_fetch_assoc ( $rst );
			if ($Notification) {
				if($Notification['status'] == 1){
					$this->result ['errcode'] = 0;
				}else{
					$this->result ['errcode'] =20002;
					$this->myfile->put(sprintf(STATUS_20002,$Notification['id']),2);
					die();
				}
			} elseif ($NotificationId == 0) {
				$this->result ['errcode'] = 20000;
				$this->myfile->put(sprintf(STATUS_20000),2);
				die();
			} else {
				$this->result ['errcode'] = 20001;
				$this->myfile->put(sprintf(STATUS_20001,$NotificationId),2);
				die();
			}
		} else {
			$this->result ['errcode'] = 18001;
			$this->myfile->put(sprintf(STATUS_18001,$this->sql),1);
			die();
		}
		return $Notification;
	}
	
	/**
	 * 查找未发送的设备信息
	 * @param number $NotificationId	主要是消息表的id
	 * @param number $type 1.IOS 2.Android 4.SMS
	 * @param number $limit 每次搜索的数量
	 * @return array $NotificationDetails 返回未发送的设备列表
	 */
	public function getDevice_tokens($NotificationId,$type = 0,$limit = 0) {
		$NotificationDetails = array ();
		$where = '';
		$where .= ' `delete` = 0 ';
		$where .= ' AND `status` = 1 ';
		$where .= ' AND `notification_id` = ' . $NotificationId . ' ';
		if($type){
			$where .=  ' AND `device_type` = '.$type.' ' ;
		}

		$sql_limit = $limit?" LIMIT 0,".$limit:'';
		$this->sql = 'SELECT id,device_token,device_type FROM '.$this->database_notification_details .' WHERE ' . $where . ' ORDER BY id '. $sql_limit;
		$rst = mysql_query ( $this->sql );
		if ($rst) {
			while ( $NotificationDetails[] = mysql_fetch_assoc ( $rst ) ) {
			}
			if(!$NotificationDetails[count($NotificationDetails)-1]){
				unset($NotificationDetails[count($NotificationDetails)-1]);
			}
		} else {
			$this->result ['errcode'] = 18002;
			$this->myfile->put(sprintf(STATUS_18002,$this->sql),1);
			die();
		}
		return $NotificationDetails;
	}
	
	/**
	 * 查找未发送的设备信息
	 * @param int $NotificationId	主要是消息表的id
	 * @param int $type 1.IOS 2.Android 4.SMS
	 * @return array $NotificationDetails 返回未发送的设备列表
	 */

	/**
	 * 查找数据库中所有已知用户（在设备表中查找）
	 * @param string $sql 自定义查找语句
	 * @param string $order
	 * @param number $limit
	 * @param number $from
	 * @return multitype:multitype:
	 */
	public function getDevicebyAll($type,$order = 'id',$limit = 0,$from= 0) {
		$NotificationDetails = array ();
		$sql_seletc = 'SELECT id,device_token,device_type FROM '.$this->database_device_user.' WHERE ';
		$sql_where = ' `delete` = 0';
		if($type){
			$sql_where .= ' AND device_type = ' . $type . ' ';
		}
		$sql_order = ' ORDER BY '. $order;
		$sql_limit = '';
		if($from and $limit){
			$sql_limit = " LIMIT ".$from.",".$limit;
		}elseif($limit){
			$sql_limit = " LIMIT ".$limit;
		}
		$this->sql = $sql_seletc . $sql_where . $sql_order . $sql_limit;
		$rst = mysql_query ( $this->sql );
		if ($rst) {
			while ( $NotificationDetails[] = mysql_fetch_assoc ( $rst ) ) {
			}
			if(!$NotificationDetails[count($NotificationDetails)-1]){
				unset($NotificationDetails[count($NotificationDetails)-1]);
			}
		} else {
			$this->result ['errcode'] = 18002;
			$this->myfile->put(sprintf(STATUS_18002,$this->sql),1);
			die();
		}
		return $NotificationDetails;
	}
	
	/**
	 * 自定义查找信息
	 * @param string $sql 自定义查找语句
	 * @param string $order
	 * @param number $limit
	 * @return multitype:multitype:
	 */
	private function getDevicebySql($sql) {
		$NotificationDetails = array ();
		$this->sql = $sql ;
		$rst = mysql_query ( $this->sql );
		if ($rst) {
			while ( $NotificationDetails[] = mysql_fetch_assoc ( $rst ) ) {
			}
			if(!$NotificationDetails[count($NotificationDetails)-1]){
				unset($NotificationDetails[count($NotificationDetails)-1]);
			}
		} else {
			$this->result ['errcode'] = 18002;
			$this->myfile->put(sprintf(STATUS_18002,$this->sql),1);
			die();
		}
		return $NotificationDetails;
	}
	/**
	 * 标记Notification表的status状态为0
	 * @param int $NotificationId Notification表的主键
	 * @param int $time 表示在什么情况更新此数据 1为推送完毕的时候。
	 * @param int $status 想要标记的状态
	 * @return NULL
	 */
	public function updateNotification($NotificationId,$status = 0){
		$this->sql = 'UPDATE '.$this->database_notification .' SET `status` = '.$status.' WHERE id = '.$NotificationId;
		$rst = mysql_query($this->sql);
		if(!$rst){
			$this->result ['errcode'] = 18003;
			$this->myfile->put(sprintf(STATUS_18003,$this->sql),1);
		}else{
		}
	}
	/**
	 * 根据push返回来的信息，对数据库进行修改。
	 * @param Array $result 推送返回的结果，只用到$result['success'],$result['fail']
	 */
	public function updateforpush($result){
		if(!empty($result['success'])) {
			$set  = ' `status` = 0 ' ;
			$this->sql = 'UPDATE '.$this->database_notification_details .' SET '.$set.' WHERE id IN('.implode(',', $result['success']).')';
			$rst = mysql_query($this->sql);
			if(!$rst){
				$this->result ['errcode'] = 18004;
				$this->myfile->put(sprintf(STATUS_18004,$this->sql),1);
			}else{
			}
		}
		if(!empty($result['fail'])) {
			$set  = ' `status` = 3 ' ;
			$this->sql = 'UPDATE '.$this->database_notification_details .' SET '.$set.' WHERE id IN('.implode(',', $result['fail']).')';
			$rst = mysql_query($this->sql);
			if(!$rst){
				$this->result ['errcode'] = 18005;
				$this->myfile->put(sprintf(STATUS_18005,$this->sql),1);
			}else{
			}
		}
	}
	
	/**
	 * 返回没发送设备的个数
	 * @param $NotificationId 
	 * @return int 设备个数
	 */
	public function get_num_Notification($NotificationId){
		$result = $this->getDevice_tokens($NotificationId);
		return count($result);
	}
}
?>