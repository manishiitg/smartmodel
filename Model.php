<?php
/**
 * Author: Manish
 * Version: v0.2.6 alpha
 */
class Model {
	protected static $_className;
	private $sql_tracking = true;
	private $sql_list;

	const MODEL_WHERE_TYPE_AND = "AND";
	const MODEL_WHERE_TYPE_OR = "OR";
	/**
	 * Constructor of the class
	 * Reads the Configuration setting for sql tracking
	 * @return unknown_type
	 */
	public function __construct(){
		$this->sql_list = array();
	}
	/**
	 * Delete An Database Model
	 * @param array $where Key Value Pair, consisting of key as Model varible and its value as value
	 * @param string $limit e.g LIMIT 1,5 the sql limit statement
	 * @return return the number of rows deleted
	 */
	public function delete($where = null,$limit=""){

		$obj = get_object_vars($this);
		$wheresql ='';
		$where_values = array();

		$array_check = $this->is_assoc($this->_fields) ? array_keys($this->_fields) : $this->_fields;
		if(isset($where)){
			foreach($where as $k=>$v){
				if(isset($v) && in_array($k, $array_check)){
					$column = $this->is_assoc($this->_fields) ? $this->_fields[$k] : $k;
					$column = empty($column) ? $k : $column;
					$wheresql .= " AND {$obj['_table']}.{$column}=?";
					$where_values[] = $v;
				}
			}
		}else{
			foreach($obj as $o=>$v){
				if(isset($v) && in_array($o, $array_check)){
					$column = $this->is_assoc($this->_fields) ? $this->_fields[$o] : $o;
					$column = empty($column) ? $o : $column;
					$wheresql .= " AND {$obj['_table']}.{$column}=?";
					$where_values[] = $v;
				}
			}
		}
		if(!empty($wheresql))
		$wheresql = substr($wheresql, 5);


		if(!empty($limit))
		$sql ="DELETE FROM `{$this->_table}` WHERE $wheresql $limit";
		else
		$sql ="DELETE FROM `{$this->_table}` WHERE $wheresql";

		$stmt = $this->query($sql,$where_values);
		return $stmt->rowCount();
	}
	/**
	 * Read a database model
	 * @param array $select A list of columns to read from the database table
	 * @param array $where A key value pair array
	 * @param array $orderby A key value pari, with column name and asc or desc order
	 * @param string $limitsql SQL limit statement
	 * @return array
	 */
	public function read($select = null,$where = null,$orderby = null,$limitsql = ""){
		$obj = get_object_vars($this);

		$wheresql ='';
		$where_values = array();

		$array_check = $this->is_assoc($this->_fields) ? array_keys($this->_fields) : $this->_fields;

		if(isset($where)){
			foreach($where as $k=>$v){
				if(isset($v) && in_array($k, $array_check)){
					if(is_array($v)){
						$column = $this->is_assoc($this->_fields) ? $this->_fields[$k] : $k;
						$column = empty($column) ? $k : $column;
						$wheresql .= " {$v[1]} {$obj['_table']}.{$column}=?";
						$where_values[] = $v[0];
					}else{
						$column = $this->is_assoc($this->_fields) ? $this->_fields[$k] : $k;
						$column = empty($column) ? $k : $column;
						$wheresql .= " AND {$obj['_table']}.{$column}=?";
						$where_values[] = $v;
					}
				}
			}
		}else{
			foreach($obj as $o=>$v){
				if(isset($v) && in_array($o, $array_check) && !empty($v)){
					$column = $this->is_assoc($this->_fields) ? $this->_fields[$o] : $o;
					$column = empty($column) ? $o : $column;
					$wheresql .= " AND {$obj['_table']}.{$column}=?";
					$where_values[] = $v;
				}
			}
		}
		if(!empty($wheresql)){
			$pos = strpos($wheresql,' ', 1);
			$wheresql = substr($wheresql, $pos);
			$wheresql = " where " . $wheresql;
		}
		$selectsql = "";
		//select
		if(isset($select)){
			foreach($select as $k){
				$column = $this->is_assoc($this->_fields) ? $this->_fields[$k] : $k;
				$column = empty($column) ? $k : $column;
				$selectsql .= "{$obj['_table']}.{$column},";
			}
			$selectsql = substr($selectsql,0,-1);
		}else{
			$selectsql ='*';
		}


		$orderbysql = "";
		if(isset($orderby['asc']) && $orderby($opt['desc']) && $orderby['asc']!='' && $orderby['desc']!=''){
			$orderbysql= 'ORDER BY '. $orderby['desc'] .' DESC, '. $orderby['asc'] . ' ASC';
		}
		else if(isset($orderby['asc'])){
			$orderbysql = 'ORDER BY ' . $orderby['asc'] . ' ASC';
		}
		else if(isset($orderby['desc'])){
			$orderbysql = 'ORDER BY ' . $orderby['desc'] . ' DESC';
		}
		$sql ="SELECT $selectsql FROM `{$this->_table}` $wheresql $orderbysql $limitsql";
		$stmt = $this->query($sql,$where_values);
		$data =  $stmt->fetchAll();
		//	 if(sizeof($data) > 0){
		//	 	$row = $data[0];
		//	 	foreach($this->_fields as $k=>$v){
		//	 		$this->$k = $row[$v];
		//	 	}
		//	 }
		return $data;
	}

	/**
	 * Insert a databae model into the table
	 * @return The insert id
	 */
	public function insert(){
		$model = $this;
		$obj = get_object_vars($this);

		$valuestr = "";
		$fieldstr = "";
		$values = array();

		$array_check = $this->is_assoc($this->_fields) ? array_keys($this->_fields) : $this->_fields;

		foreach($obj as $o=>$v){
			if(isset($v) && in_array($o, $array_check)){
				$valuestr .= "?,";
				$values[] = "$v";
				$db = $this->is_assoc($this->_fields) ? $this->_fields[$o] : $o;
				$db = empty($db) ? $o : $db;
				$fieldstr .= "`".$db .'`,';
			}
		}
		$valuestr = substr($valuestr, 0, strlen($valuestr)-1);
		$fieldstr = substr($fieldstr, 0, strlen($fieldstr)-1);

		$sql ="INSERT INTO `{$obj['_table']}`($fieldstr) VALUES ($valuestr)";
		$this->query($sql,$values);
		return $this->lastInsertId();

	}

	public function update($set = null,$where = null,$opt = null){
		$model = $this;
		$obj = get_object_vars($model);

		$field_and_value = '';

		$array_check = $this->is_assoc($this->_fields) ? array_keys($this->_fields) : $this->_fields;

		$values = array();
		if(isset($set)){
			foreach($set as $o=>$v){
				if(isset($v) && in_array($o, $array_check)){
					$db = $this->is_assoc($this->_fields) ? $this->_fields[$o] : $o;
					$db = empty($db) ? $o : $db;
					$field_and_value .= $obj['_table'].'.'.$db."=?,";
					$values[] = $v;
				}
			}
		}else{
			foreach($obj as $o=>$v){
				if(isset($v) && in_array($o,$array_check)){
					$db = $this->is_assoc($this->_fields) ? $this->_fields[$o] : $o;
					$db = empty($db) ? $o : $db;
					$field_and_value .= $obj['_table'].'.'.$db.'=?,';
					$values[] = $v;
				}
			}
		}

		$field_and_value = substr($field_and_value, 0, strlen($field_and_value)-1);
		if(isset($where)){
			$where_values = "";
			foreach($where as $o=>$v){
				if(isset($v) && in_array($o, $array_check)){
					$db = $this->is_assoc($this->_fields) ? $this->_fields[$o] : $o;
					$db = empty($db) ? $o : $db;
					$where_values .= $obj['_table'].'.'.$db.'=?,';
					$values[] = $v;
				}
			}
			$where_values = substr($where_values, 0, strlen($where_values)-1);
			$sql ="UPDATE `{$obj['_table']}` SET $field_and_value WHERE $where_values";
		}else if(isset($obj['_key']) && isset($model->{$obj['_key']})){
			$db = $this->is_assoc($this->_fields) ? $this->_fields[$obj['_key']] : $obj['_key'];
			$db = empty($db) ? $obj['_key'] : $db;
			$where = $obj['_table'].'.'.$db ."=?";
			$values[] = $model->$obj['_key'];

			$sql ="UPDATE `{$obj['_table']}` SET $field_and_value WHERE $where";
		}
		else if(isset($obj['_primarykey']) && isset($model->{$obj['_primarykey']})){

			$db = $this->is_assoc($this->_fields) ? $this->_fields[$obj['_primarykey']] : $obj['_primarykey'];
			$db = empty($db) ? $obj['_primarykey'] : $db;
			$where = $obj['_table'].'.'.$db."=?";
			$values[] = $model->$obj['_primarykey'];

			$sql ="UPDATE `{$obj['_table']}` SET $field_and_value WHERE $where";
		}else{
			$sql ="UPDATE `{$obj['_table']}` SET $field_and_value";
		}
		if(isset($opt['limit'])){
			$sql = $sql . " LIMIT " . $opt['limit'];
		}
		$stmt = $this->query($sql,$values);
		return $stmt->rowCount();
	}
	public function count($where = null){
		$model = $this;
		$obj = get_object_vars($model);
		$values = array();
		$where_values = "";

		$array_check = $this->is_assoc($this->_fields) ? array_keys($this->_fields) : $this->_fields;

		if(isset($where)){
			$where_values = "";
			foreach($where as $o=>$v){
				if(isset($v) && in_array($o, $array_check)){
					$db = $this->is_assoc($this->_fields) ? $this->_fields[$o] : $o;
					$db = empty($db) ? $o : $db;
					$where_values .= $obj['_table'].'.'.$db.'= ?,';
					$values[]= $v;
				}
			}
			$where_values = substr($where_values, 0, strlen($where_values)-1);
			$sql ="select count(*) from {$obj['_table']} WHERE $where_values";
		}else if(isset($obj['_key']) && isset($model->{$obj['_key']})){

			$db = $this->is_assoc($this->_fields) ? $this->_fields[$obj['_key']] : $obj['_key'];
			$db = empty($db) ? $obj['_key'] : $db;
			$where = $obj['_table'].'.'.$db ."=?";
			$values[] = $model->$obj['_key'];

			$sql ="select count(*) from {$obj['_table']} WHERE $where";
		}
		else if(isset($obj['_primarykey']) && isset($model->{$obj['_primarykey']})){
			$db = $this->is_assoc($this->_fields) ? $this->_fields[$obj['_primarykey']] : $obj['_primarykey'];
			$db = empty($db) ? $obj['_primarykey'] : $db;
			$where = $obj['_table'].'.'.$db."=?";
			$values[] = $model->$obj['_primarykey'];

			$sql ="select count(*) from `{$obj['_table']}` WHERE $where";
		}else{
			$wheresql = "";
			foreach($obj as $o=>$v){
				if(isset($v) && in_array($o, $array_check) && !empty($v)){
					$db = $this->is_assoc($this->_fields) ? $this->_fields[$o] : $o;
					$db = empty($db) ? $o : $db;
					$wheresql .= " AND {$obj['_table']}.".$db."=?";
					$values[] = $v;
				}
			}
			if(!empty($wheresql)){
				$pos = strpos($wheresql,' ', 1);
				$wheresql = substr($wheresql, $pos);
				$wheresql = " where " . $wheresql;
			}
			$sql ="select count(*) from `".$obj['_table']."` $wheresql";
		}
		$stmt = $this->query($sql,$values,PDO::FETCH_NUM);
		$arr = $stmt->fetch();
		return $arr[0];

	}

	public function saveOrUpdate($where= null){
		$count = $this->count($where);
		if($count == 0){
			$this->insert();
		}else{
			$this->update(null,$where);
		}
	}
	public function query($sql,$param=null,$mode = PDO::FETCH_ASSOC){
		if($this->sql_tracking===true){
			$querytrack = $sql;
			//if params used in sql, replace them into the sql string for logging
			if($param!=null){
				if(isset($param[0])){
					$querytrack = explode('?',$querytrack);
					$q = $querytrack[0];
					foreach($querytrack as $k=>$v){
						if($k===0)continue;
						$q .=  "'".$param[$k-1]."'" . $querytrack[$k];
					}
					$querytrack = $q;
				}else{
					//named param used
					foreach($param as $k=>$v)
					$querytrack = str_replace($k, "'$v'", $querytrack);
				}
			}
			$this->sql_list[] = $querytrack;
			echo "Query Exec: " . $querytrack . "<br>";
		}

		if(!class_exists('DataDB')){
			require_once 'DataDB.php';
		}
		$pdo = DataDB::connect();
		$stmt = $pdo->prepare($sql);
		$stmt->setFetchMode($mode);


		if($param==null || empty($param) ){
			try{
				$stmt->execute();
				if($this->sql_tracking){
					echo "Row Affected : " . $stmt->rowCount() ."<br>";
				}
			}catch(PDOException $e){
				die("SQL Error: " . $e->getMessage() . "<br> Query: $sql");
			}
		}
		else{
			try{
				$stmt->execute($param);
				if($this->sql_tracking){
					echo "Row Affected : " . $stmt->rowCount() ."<br>";
				}
			}catch(PDOException $e){
				die("SQL Error: " . $e->getMessage() . "<br> Query: $sql and Values ".print_r($param));
			}
		}
		return $stmt;
	}
	public function lastInsertId(){
		$id = DataDB::connect()->lastInsertId();
		if($this->sql_tracking){
			echo "Last Insert ID: " . $id ."<br>";
		}
		return $id;
	}
	public function getSQLList(){
		return $this->sql_list;
	}

	static function is_assoc($array) {
		foreach (array_keys($array) as $k => $v) {
			if ($k !== $v)
			return true;
		}
		return false;
	}

}