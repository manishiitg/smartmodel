<?php
/**
 *
 * @author Manish <manish@excellencetechnoloiges.in>
 * @version v0.2
 * @package SmartModel
 * @copyright Copyright (c) 2009, Excellence Technologies
 *
 */
require_once dirname(__FILE__).'/DataDB.php';
/**
 * This is the code ORM class. This has all the database related operation functions.
 *
 */
class Model {

	/**
	 * This is used to enabled SQL Tracking.
	 * If this is set as true, then SQL Queries being executed will be printed on screen
	 * If set to false, no sql queries will be printed
	 * @var boolean
	 */
	public $sql_tracking = false;

	/**
	 * This array is used to store a list of all SQL statement executed.
	 * Any output generated when $sql_tracking {@link sql_tracking} is true will be stored in this array.
	 * @var array
	 */
	private $sql_list;

	/**
	 *
	 * @staticvar This is used in read() {@link read()} function. This is used defined the AND OR in SQL where clause
	 */
	const MODEL_WHERE_TYPE_AND = " AND ";
	const MODEL_WHERE_TYPE_OR = " OR  ";
	const MODEL_WHERE_TYPE_EQ = " = ";
	const MODEL_WHERE_TYPE_NOTEQ = " != ";
	const MODEL_WHERE_TYPE_GT = " > ";
	const MODEL_WHERE_TYPE_GTEQ = " >= ";
	const MODEL_WHERE_TYPE_LT = " < ";
	const MODEL_WHERE_TYPE_LTWQ = " <= ";
	const MODEL_WHERE_TYPE_LIKE = " LIKE ";
	const MODEL_WHERE_TYPE_NOTLIKE = " NOTLIKE ";
	const MODEL_WHERE_TYPE_IN = " IN ";
	const MODEL_WHERE_TYPE_ISNULL = " IS NULL ";
	const MODEL_WHERE_TYPE_ISNOTNULL = " IS NOT NULL ";
	/**
	 * Constructor of the class
	 * Used for basic initialization
	 */

	private $escapeArr = array();
	public function __construct(){
		$this->sql_list = array();
		$this->sql_tracking = false;
		$this->escapeArr[DataDB::DB_TYPE_MYSQL]['OPEN'] = '`';
		$this->escapeArr[DataDB::DB_TYPE_MYSQL]['CLOSE'] = '`';
		$this->escapeArr[DataDB::DB_TYPE_MSSQL]['OPEN'] = '[';
		$this->escapeArr[DataDB::DB_TYPE_MSSQL]['CLOSE'] = ']';
		$this->escapeArr[DataDB::DB_TYPE_WORDPRESS]['OPEN'] = '`';
		$this->escapeArr[DataDB::DB_TYPE_WORDPRESS]['CLOSE'] = '`';

	}
	public function escOp(){
		return $this->escapeArr[DataDB::DB_TYPE]['OPEN'];
	}
	public function escCl(){
		return $this->escapeArr[DataDB::DB_TYPE]['CLOSE'];
	}
	/**
	 * Delete An Database Model
	 * @param array $where
	 * @param string $limit
	 * @return integer
	 */




	public function delete($where = null,$limit=""){

		$obj = get_object_vars($this);
		$wheresql ='';
		$where_values = array();

		$array_check = $this->getKeys($this->_fields);
		if(isset($where)){
			foreach($where as $k=>$v){
				$this->generateWhere($v,$k,$array_check,$obj['_table'],$wheresql,$where_values);
			}
		}else{
			foreach($obj as $o=>$v){
				if(isset($v) && in_array($o, $array_check)){
					$column = $this->getColumnName($o);
					$wheresql .= " AND {$obj['_table']}.". $this->escOp() . "{$column}". $this->escCl() . " = ?";
					$where_values[] = $v;
				}
			}
		}
		if(!empty($wheresql)){
			$wheresql = substr($wheresql, 5);
			$wheresql = " WHERE " . $wheresql ;
		}


		if(!empty($limit))
		$sql ="DELETE FROM ". $this->escOp() . "{$this->_table}". $this->escCl() . "   $wheresql $limit";
		else
		$sql ="DELETE FROM  ". $this->escOp() . "{$this->_table}". $this->escCl() . "   $wheresql";


		$obj = $this->query($sql,$where_values,'delete');
		return $this->getRowCount($obj);
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

		$array_check = $this->getKeys($this->_fields);
		if(isset($where)){
			foreach($where as $k=>$v){
				$this->generateWhere($v,$k,$array_check,$obj['_table'],$wheresql,$where_values);
			}
		}else{
			foreach($obj as $o=>$v){
				if(isset($v) && in_array($o, $array_check) && (!empty($v) || $v ==0) ){
					$column = $this->getColumnName($o);
					$wheresql .= " AND {$obj['_table']}.". $this->escOp() . "{$column}". $this->escCl() . "=?";
					$where_values[] = $v;
				}
			}
		}
		if(!empty($wheresql)){
			$wheresql = substr($wheresql, 5);
			$wheresql = " where " . $wheresql;
		}
		$selectsql = "";
		//select
		if(isset($select)){
			foreach($select as $k){
				$column = $this->getColumnName($k);
				$selectsql .= "{$obj['_table']}.{$column},";
			}
			$selectsql = substr($selectsql,0,-1);
		}else{
			$selectsql ='*';
		}


		$orderbysql = "";
		if(isset($orderby['asc']) && isset($orderby['desc']) && $orderby['asc']!='' && $orderby['desc']!=''){
			$orderbysql= 'ORDER BY '. $obj['_table'].'.'.$this->escOp().$this->getColumnName($orderby['desc']).$this->escCl().' DESC, '.$this->escOp(). $obj['_table'].$this->escCl().'.'.$this->escOp().$this->getColumnName($orderby['asc']).$this->escCl().  ' ASC';
		}
		else if(isset($orderby['asc'])){
			$orderbysql = 'ORDER BY ' . $obj['_table'].'.'.$this->escOp().$this->getColumnName($orderby['asc']) .$this->escCl(). ' ASC';
		}
		else if(isset($orderby['desc'])){
			$orderbysql = 'ORDER BY ' . $obj['_table'].'.'.$this->escOp().$this->getColumnName($orderby['desc']).$this->escCl(). ' DESC';
		}
		if(DataDB::DB_TYPE == DataDB::DB_TYPE_MSSQL && !empty($limitsql)){
			if(strpos($limitsql,',') === false){ // off the form LIMIT 5
				$limitsql = str_replace("LIMIT","TOP",$limitsql);
				$sql ="SELECT $limitsql $selectsql FROM  ". $this->escOp() . "{$this->_table}". $this->escCl() . "  $wheresql $orderbysql";
			}else{ // off the form LIMIT 5,10
				$limitsql = trim($limitsql);
				$pos = strpos($limitsql,',');

				$end = substr($limitsql,$pos+1,strlen($limitsql)-$pos);
				$start = substr($limitsql,strpos($limitsql,' '),$pos-strpos($limitsql,' '));
				$start = trim($start);
				if($start == 0){
					$limitsql = "TOP $end";
					$sql ="SELECT $limitsql $selectsql FROM  ". $this->escOp() . "{$this->_table}". $this->escCl() . "  $wheresql $orderbysql";
				}else{
					$key = $this->getModelKey();
					$end = (int)$start + (int)$end -1;
					if(empty($orderbysql)){
						$orderbysql = "ORDER BY $key";
					}
					$sql = "SELECT * FROM ( SELECT row_number() OVER ($orderbysql) AS rownum, $selectsql FROM ". $this->escOp() . "{$this->_table}". $this->escCl() . " $wheresql ) AS A WHERE A.rownum BETWEEN ($start) AND ($end)";
					echo $sql;
				}

			}
		}else{
			$sql ="SELECT $selectsql FROM  ". $this->escOp() . "{$this->_table}". $this->escCl() . "  $wheresql $orderbysql $limitsql";
		}
		$obj = $this->query($sql,$where_values,'read');
		return $this->getData($obj);
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

		$array_check = $this->getKeys($this->_fields);


		foreach($obj as $o=>$v){
			if(isset($v) && in_array($o, $array_check)){
				$valuestr .= "?,";
				$values[] = "$v";
				$column = $this->getColumnName($o);
				$fieldstr .= $this->escOp() .$column .  $this->escCl() . ',';
			}
		}
		$valuestr = substr($valuestr, 0, strlen($valuestr)-1);
		$fieldstr = substr($fieldstr, 0, strlen($fieldstr)-1);

		$sql ="INSERT INTO  ". $this->escOp() . "{$obj['_table']}". $this->escCl() . " ($fieldstr) VALUES ($valuestr)";

		if(DataDB::DB_TYPE != DataDB::DB_TYPE_MSSQL){
			$ret = $this->query($sql,$values,'insert');
			if(DataDB::DB_TYPE == DataDB::DB_TYPE_WORDPRESS){
				return $ret;
			}
			return $this->lastInsertId();
		}else{
			$this->query($sql,$values,'insert');
			return $this->lastInsertId();
			//				$sql .= "; SELECT @@IDENTITY AS mixLastId;";
			//				$objSth = $this->query($sql,$values);
			//				$mixRc = false;
			//				$mixRc = (is_object($objSth) and $objSth->errorCode() == '00000');
			//
			//				if ($mixRc === false) return false;
			//
			//				// The compound command delivers a multi-rowset statement handle
			//				// Move past the first (invalid) rowset from the INSERT command
			//				//								$objSth->nextRowset();
			//				// Pick up the first row of the rowset from "SELECT @@IDENTITY"
			//				$rowTd = $objSth->fetch();
			//				if (!is_array($rowTd)) {
			//
			//					return false;
			//				}
			//				$objSth->closeCursor();
			//				$strLastRowId = trim($rowTd[0]); // trim() for trailing Nullbyte
			//				// Integers are returned stringified, format depends on locale
			//				// Generally ends with ",00" or ".00" - trim that off
			//				$strLastRowId = preg_replace('/[,.]0+$/', '', $strLastRowId);
			//				// Remove any remaining "." or "," for thousands
			//				$strLastRowId = preg_replace('/[,.]/', '', $strLastRowId);
			//				// A GUID, which contains no "," or ".", will be left unchanged
			//				$this->lastInsertId = $strLastRowId;
			//				return $strLastRowId;
		}


	}
	public function countJoin($join = null,$whereArr = null){
		return $this->join($join,$whereArr,null,null,"",true);
	}
	const JOIN_LEFT = "LEFT JOIN";
	const JOIN_RIGHT = "RIGHT JOIN";
	const JOIN_EQUI = "JOIN";

	static $i = 0;
	private static function updateJoinMappingTable($table,&$join_map){
		$keyword = "Join";
		if(!in_array($table,array_keys($join_map))){
			$join_map[$table] = $keyword.self::$i++;
		}
	}
	public function join($join = null,$whereArr = null,$selectArr = null,$orderby = null,$limit = "",$count = false){
		$obj = get_object_vars($this);
		$select = "*";
		$where = "";
		$joins = "";
		$models = array();
		$array_join_map = array();
		$i=0;

		self::updateJoinMappingTable($this->_table,$array_join_map,$i);
		$joins .= " {$this->_table} AS {$array_join_map[$this->_table]} ";
		$models[] = get_class($this);

		$array_check = $this->getKeys($this->_fields);
		if(isset($join)){
			foreach($join as $k => $v){
				$join_type = self::JOIN_LEFT;
				$left_table = $this->_table;
				$left_var = $k;
				$right_table = $this->_table;
				$left_var = $this->getColumnName($left_var);
				$right_var = "";

				$right_var = $this->getColumnName($right_var);
				$v_arr = array();
				if(is_array($v)){
					$v_arr = $v;
				}else{
					$v_arr[] = $v;
				}
				foreach($v_arr as $v){
					if(strpos($k,'.') !== false){
						$left_table = substr($k,0,strpos($k,'.'));
						$left_var = substr($k,strpos($k,'.')+1);
						if(!isset($models[$left_table])){
							$models[] = $left_table;
						}
						if($left_table != get_class($this)){
							$obj = new $left_table;
							$left_table = $obj->_table;
							$left_var = $obj->getColumnName($left_var);
							self::updateJoinMappingTable($left_table,$array_join_map);
						}else{
							$left_var = $this->getColumnName($left_var);
						}
					}
					if(strpos($v,'.') !== false){
						$right_table = substr($v,0,strpos($v,'.'));
						$right_var = substr($v,strpos($v,'.')+1);
						if(!isset($models[$right_table])){
							$models[] = $right_table;
						}
						if($right_table != get_class($this)){
							$obj = new $right_table;
							$right_table = $obj->_table;
							$right_var = $obj->getColumnName($right_var);
							self::updateJoinMappingTable($right_table,$array_join_map);
						}else{
							$right_var = $this->getColumnName($right_var);
						}
					}
					$joins .= $join_type . " $right_table AS {$array_join_map[$right_table]} ON {$array_join_map[$left_table]}.$left_var = {$array_join_map[$right_table]}.$right_var ";
				}
			}
		}
		$wherevalues = array();
		if(isset($whereArr)){
			$where = "";
			foreach($whereArr as $k => $v){

				$class = get_class($this);
				$var = $k;
				if(strpos($k,'.') !== false){
					$class = substr($k,0,strpos($k,'.'));
					$var = 	substr($k,strpos($k,'.')+1);
				}
				$obj_r = new $class;
				$var = $obj_r->getColumnName($var);

				$table = $array_join_map[$obj_r->_table];
				$this->generateWhere($v,$var,false,$table,$where,$wherevalues);
			}
		}else{
			$where = "";
			$obj = get_object_vars($this);
			$array_check = $this->getKeys($this->_fields);
			foreach($obj as $o=>$v){
				if(isset($v) && in_array($o, $array_check) && (!empty($v) || $v ==0) ){
					$column = $this->getColumnName($o);
					$where .= " AND {$array_join_map[$obj['_table']]}.{$column}=? ";
					$wherevalues[] = $v;
				}
			}
		}
		if(!empty($where)){
			$where = " WHERE " . substr($where,5);
		}else{
			$where = "";
		}

		$select = "";
		if(!$count){
			if(isset($selectArr)){
				foreach($selectArr as $k){
					$class = get_class($this);
					$var = $k;
					if(strpos($k,'.') !== false){
						$class = substr($k,0,strpos($k,'.'));
						$var = substr($k,strpos($k,'.')+1);
					}
					if(get_class($this) != $class){
						$obj_r = new $class;
						$var_c = $obj_r->getColumnName($var);
					}else{
						$var_c = $this->getColumnName($var);
					}
					if($class == get_class($this)){
						$select .= " {$array_join_map[$this->_table]}.$var_c as " . $this->escOp() . "$var" . $this->escCl() . " ,";
					}else{
						$select .= " {$array_join_map[$this->_table]}.$var_c as " . $this->escOp() . "$class.$var" . $this->escCl() . " ,";
					}
				}
				$select = substr($select,0,-1);
			}else{
				foreach($models as $model){
					$obj = new $model;
					foreach($obj->getKeys($obj->_fields) as $k){
						if($model == get_class($this)){
							$select .= " {$array_join_map[$obj->_table]}.{$obj->getColumnName($k)} as " . $this->escOp() . "{$obj->getColumnName($k)}" . $this->escCl() . " ,";
						}else{
							$table = get_class($obj);
							$select .= " {$array_join_map[$obj->_table]}.{$obj->getColumnName($k)} as " . $this->escOp() . "{$table}.{$obj->getColumnName($k)}" . $this->escCl() . " ,";
						}
					}
				}
				$select = substr($select,0,-1);
			}
		}else{
			$select = " count(*) as " . $this->escOp() . "count" . $this->escOp() . "";
		}
		$orderbysql = "";

		if(isset($orderby['asc']) && isset($orderby['desc']) && $orderby['asc']!='' && $orderby['desc']!=''){
			$k = $orderby['desc'];
			if(strpos($k,'.')!==false){
				$class = substr($k,0,strpos($k,'.'));
				$var = substr($k,strpos($k,'.')+1);
				$obj_r = new $class;
				$orderby['desc'] = $array_join_map[$obj_r->_table].'.'.$obj_r->getColumnName($var);
			}else{
				$orderby['desc'] = $array_join_map[$this->_table].'.'.$this->getColumnName($k);
			}
			$k = $orderby['asc'];
			if(strpos($k,'.')!==false){
				$class = substr($k,0,strpos($k,'.'));
				$var = substr($k,strpos($k,'.')+1);
				$obj_r = new $class;
				$orderby['asc'] = $array_join_map[$obj_r->_table].'.'.$obj_r->getColumnName($var);
			}else{
				$orderby['asc'] = $array_join_map[$this->_table].'.'.$this->getColumnName($k);
			}
			$orderbysql= 'ORDER BY '. $orderby['desc'] .' DESC, '. $orderby['asc'] . ' ASC';
		}
		else if(isset($orderby['asc'])){
			$k = $orderby['asc'];
			if(strpos($k,'.')!==false){
				$class = substr($k,0,strpos($k,'.'));
				$var = substr($k,strpos($k,'.')+1);
				$obj_r = new $class;
				$orderby['asc'] = $array_join_map[$obj_r->_table].'.'.$obj_r->getColumnName($var);
			}else{
				$orderby['asc'] = $array_join_map[$this->_table].'.'.$this->getColumnName($k);
			}
			$orderbysql = 'ORDER BY ' . $orderby['asc'] . ' ASC';
		}
		else if(isset($orderby['desc'])){
			$k = $orderby['desc'];
			if(strpos($k,'.')!==false){
				$class = substr($k,0,strpos($k,'.'));
				$var = substr($k,strpos($k,'.')+1);
				$obj_r = new $class;

				$orderby['desc'] = $array_join_map[$obj_r->_table].'.'.$obj_r->getColumnName($var);
			}else{
				$orderby['desc'] = $array_join_map[$this->_table].'.'.$this->getColumnName($k);
			}
			$orderbysql = 'ORDER BY ' . $orderby['desc'] . ' DESC';
		}

		if(DataDB::DB_TYPE == DataDB::DB_TYPE_MSSQL && !empty($limitsql)){
			if(strpos($limitsql,',') === false){ // off the form LIMIT 5
				$limitsql = str_replace("LIMIT","TOP",$limitsql);
				$sql ="SELECT $limitsql $selectsql FROM  ". $this->escOp() . "{$this->_table}". $this->escCl() . "  $wheresql $orderbysql";
			}else{ // off the form LIMIT 5,10
				$limitsql = trim($limitsql);
				$pos = strpos($limitsql,',');

				$end = substr($limitsql,$pos+1,strlen($limitsql)-$pos);
				$start = substr($limitsql,strpos($limitsql,' '),$pos-strpos($limitsql,' '));
				$start = trim($start);
				if($start == 0){
					$limitsql = "TOP $end";
					$sql ="SELECT $limitsql $selectsql FROM  ". $this->escOp() . "{$this->_table}". $this->escCl() . "  $wheresql $orderbysql";
				}else{
					$key = $this->getModelKey();
					$end = (int)$start + (int)$end -1;
					if(empty($orderbysql)){
						$orderbysql = "ORDER BY $key";
					}
					$sql = "SELECT * FROM ( SELECT row_number() OVER ($orderbysql) AS rownum, $selectsql FROM ". $this->escOp() . "{$this->_table}". $this->escCl() . " $wheresql ) AS A WHERE A.rownum BETWEEN ($start) AND ($end)";
					echo $sql;
				}

			}
		}else{
			$sql = "SELECT {$select} FROM {$joins} {$where} {$orderbysql} {$limit} ";
		}
		$data = array();
		$obj = $this->query($sql,$wherevalues,'join');
		if(!$count){
			$data =  $this->getData($obj);
			return $data;
		}else{
			$arr = $this->getRow($obj);
			return $arr['count'];
		}

	}

	public function update($set = null,$where = null,$opt = null){
		$model = $this;
		$obj = get_object_vars($model);

		$field_and_value = '';

		$array_check = $this->getKeys($this->_fields);

		$values = array();
		if(isset($set)){
			foreach($set as $o=>$v){
				$where_condition = '=';
				if(is_array($v)){
					$where_condition = $v[1];
					$v = $v[0];
				}
				if(isset($v) && in_array($o, $array_check)){
					$column = $this->getColumnName($o);
					$field_and_value .= $obj['_table'].'.'.$this->escOp().$column.$this->escCl()."{$where_condition} ?,";
					$values[] = $v;
				}
			}
		}else{
			foreach($obj as $o=>$v){
				if(isset($v) && in_array($o,$array_check)){
					$column = $this->getColumnName($o);
					$field_and_value .= $obj['_table'].'.'.$this->escOp().$column.$this->escCl().'=?,';
					$values[] = $v;
				}
			}
		}
		$field_and_value = substr($field_and_value, 0, strlen($field_and_value)-1);
		if(isset($where)){
			$where_values = "";
			foreach($where as $o=>$v){
				$this->generateWhere($v,$o,$array_check,$obj['_table'],$where_values,$values);
			}
			$where_values = substr($where_values,5);
			$sql ="UPDATE  ". $this->escOp() . "{$obj['_table']}". $this->escCl() . "  SET $field_and_value WHERE $where_values";
		}else if(isset($obj['_key']) && isset($model->{$obj['_key']})){
			$column = $this->getColumnName('_key');
			$where = $obj['_table'].'.'.$column ."=?";
			$values[] = $model->$obj['_key'];

			$sql ="UPDATE  ". $this->escOp() . "{$obj['_table']}". $this->escCl() . "  SET $field_and_value WHERE $where";
		}
		else if(isset($obj['_primarykey']) && isset($model->{$obj['_primarykey']})){
			$column = $this->getColumnName('_primarykey');
			$where = $obj['_table'].'.'.$this->escOp().$column.$this->escCl()."=?";
			$values[] = $model->$obj['_primarykey'];

			$sql ="UPDATE  ". $this->escOp() . "{$obj['_table']}". $this->escCl() . "  SET $field_and_value WHERE $where";
		}else{
			$sql ="UPDATE  ". $this->escOp() . "{$obj['_table']}". $this->escCl() . "  SET $field_and_value";
		}
		if(isset($opt['limit'])){
			$sql = $sql . " LIMIT " . $opt['limit'];
		}
		$obj = $this->query($sql,$values,'update');
		return $this->getRowCount($obj);

	}
	public function count($where = null){
		$model = $this;
		$obj = get_object_vars($model);
		$values = array();
		$where_values = "";

		$array_check = $this->getKeys($this->_fields);

		if(isset($where)){
			$where_values = "";
			foreach($where as $o=>$v){
				$this->generateWhere($v,$o,$array_check,$obj['_table'],$where_values,$values);
			}
			$where_values = substr($where_values, 5);
			$sql ="select count(*) as c from {$obj['_table']} WHERE $where_values";
		}else if(isset($obj['_key']) && isset($model->{$obj['_key']})){
			$column = $this->getColumnName('_key');
			$where = $obj['_table'].'.'.$this->escOp().$column.$this->escCl()."=?";
			$values[] = $model->$obj['_key'];

			$sql ="select count(*) as c from {$obj['_table']} WHERE $where";
		}else if(isset($obj['_primarykey']) && isset($model->{$obj['_primarykey']})){
			$column = $this->getColumnName('_primarykey');
			$where = $obj['_table'].'.'.$this->escOp().$column.$this->escCl()."=?";
			$values[] = $model->$obj['_primarykey'];

			$sql ="select count(*) as c from  ". $this->escOp() . "{$obj['_table']}". $this->escCl() . "  WHERE $where";
		}else{
			$wheresql = "";
			foreach($obj as $o=>$v){
				if(isset($v) && in_array($o, $array_check)){
					$column = $this->getColumnName($o);
					$wheresql .= " AND {$obj['_table']}.".$this->escOp().$column.$this->escCl()."=?";
					$values[] = $v;
				}
			}
			if(!empty($wheresql)){
				$pos = strpos($wheresql,' ', 1);
				$wheresql = substr($wheresql, $pos);
				$wheresql = " where " . $wheresql;
			}
			$sql ="select count(*) as c from  ". $this->escOp() .$obj['_table']. $this->escCl() . "  $wheresql";
		}

		$obj = $this->query($sql,$values,'count',PDO::FETCH_NUM);
		$arr = $this->getRow($obj);
		if(isset($arr['c'])){
			return $arr['c'];
		}else{
			return $arr[0];
		}
	}

	public function saveOrUpdate($where= null){
		$count = $this->count($where);
		if($count == 0){
			$this->insert();
		}else{
			$this->update(null,$where);
		}
	}

	/**
	 * Called only when USE_PDO is false
	 * @return unknown_type
	 */
	public function queryMYSQL($sql,$param=null){
		die('This Method Is not used any more. Using query() function itself');
	}
	public function query($sql,$param=null,$func = '',$mode = PDO::FETCH_ASSOC){

		$querytrack = $sql;
		//if params used in sql, replace them into the sql string for logging
		if($param!=null){
			if(isset($param[0])){
				$querytrack = explode('?',$querytrack);
				$q = $querytrack[0];
				foreach($querytrack as $k=>$v){
					if($k===0)continue;
					$regex = "/\(.*\)/";
					if(preg_match($regex,$param[$k-1])>0 && in_array($param[$k-1],$this->checkQuotes)){
						$q .=  $param[$k-1] . $querytrack[$k];
					} else{
						$q .=  "'". mysql_escape_string($param[$k-1])."'" . $querytrack[$k];
					}
				}
				$querytrack = $q;
			}else{
				//named param used
				foreach($param as $k=>$v)
				$querytrack = str_replace($k, "'$v'", $querytrack);
			}
		}
		if($this->sql_tracking===true){
			$this->sql_list[] = $querytrack;
			echo "Query Exec: " . $querytrack . "<br>";
		}
		if(!DataDB::USE_PDO){
			$db = mysql_connect(Configuration::host,Configuration::user,Configuration::pass);
			mysql_selectdb(Configuration::db);
			$result = mysql_query($querytrack,$db);
			if(!$result){
				die('Error In SQL'. $querytrack . ':'.mysql_error());
			}
			return $result;
		}else{
			$pdo = DataDB::connect();


			if(DataDB::DB_TYPE == DataDB::DB_TYPE_WORDPRESS){
				$wpdb = $pdo;
				global  $wpdb;
				if($this->sql_tracking){
					$wpdb->show_errors(true);
				}
				switch($func){
					case 'delete' :
						$wpdb->query($querytrack);
						return $wpdb->num_rows;
						break;
					case 'read' :
						return $wpdb->get_results($querytrack,ARRAY_A);
						break;
					case 'insert' :
						$wpdb->query($querytrack);
						return $wpdb->insert_id;
						break;
					case 'join' :
						return $wpdb->get_results($querytrack,ARRAY_A);
						break;
					case 'update' :
						$wpdb->query($querytrack);
						return $wpdb->num_rows;
						break;
					case 'count' :
						return $wpdb->get_results($querytrack,ARRAY_A);
						break;
					default:
						$wpdb->query($querytrack);
						return $wpdb;
						break;
				}


			}else{
				$stmt = $pdo->prepare($querytrack);
				$stmt->setFetchMode($mode);


				try{
					$stmt->execute();
					if($this->sql_tracking){
						echo "Row Affected : " . $stmt->rowCount() ."<br>";
					}
				}catch(PDOException $e){
					die("SQL Error: " . $e->getMessage() . "<br> Query: $sql");
				}

				return $stmt;

			}
		}
	}
	public function getRow($obj){

		if(DataDB::DB_TYPE ==  DataDB::DB_TYPE_WORDPRESS){
			return $obj[0];
		}else{
			if(DataDB::USE_PDO){
				return $obj->fetch();
			}else{
				return mysql_fetch_assoc($obj);
			}
		}

	}
	public function getData($obj){
		$data = array();
		if( DataDB::DB_TYPE ==  DataDB::DB_TYPE_WORDPRESS){
			return $obj;
		}else{
			if(DataDB::USE_PDO){
				$data =  $obj->fetchAll();
				$newData = array();
				foreach($data as $row){
					$newRow = array();
					foreach($row as $k => $v){
						$newRow[$k] = stripslashes($v);
					}
					$newData[] = $newRow;
				}
				return $newData;
			}else{
				if(mysql_num_rows($obj) > 0)
				while($row = mysql_fetch_assoc($obj)){
					$newRow = array();
					foreach($row as $k => $v){
						$newRow[$k] = stripslashes($v);
					}
					array_push($data,$newRow);
				}
				return $data;
			}
		}
	}
	public function getRowCount($obj){
		if(DataDB::DB_TYPE == DataDB::DB_TYPE_WORDPRESS){
			return $obj;
		}
		if(DataDB::USE_PDO){
			return $obj->rowCount();
		}else{
			return mysql_affected_rows();
		}
	}
	public function lastInsertId(){
		if(DataDB::DB_TYPE == DataDB::DB_TYPE_WORDPRESS){
			return;
		}
		if(!DataDB::USE_PDO){
			return mysql_insert_id();
		}else{
			if(DataDB::DB_TYPE == DataDB::DB_TYPE_MSSQL){
				return -1;
				if($this->sql_tracking){
					echo "Last Insert ID Is not supported in MSSQL";
				}
			}else{
				$id = DataDB::connect()->lastInsertId();
				if($this->sql_tracking){
					echo "Last Insert ID: " . $id ."<br>";
				}
				return $id;
			}
		}
	}
	public function getSQLList(){
		return $this->sql_list;
	}
	public function getModelKeyCol(){
		$obj = get_object_vars($this);
		if(key_exists("_pk",$obj)){
			return "_pk";
		}else if(key_exists("id",$obj)){
			return "id";
		}else if(key_exists("ID",$obj)){
			return "ID";
		}else{
			$array_check = $this->getKeys();
			return $array_check[0];
		}
	}
	public function getModelKey(){
		$obj = get_object_vars($this);
		$key = "";

		if(isset($obj['_pk'])){
			$key = $obj['_pk'];
		}else if(isset($obj['id'])){
			$key = $obj['id'];
		}else if(isset($obj['ID'])){
			$key = $obj['ID'];
		}else{
			$array_check = $this->getKeys();
			$key = $obj[$array_check[0]];
		}
		return $key;
	}
	/**
	 * This will return all the object variables from $_fields array
	 * @param array $_fields array is required here
	 * @return array
	 */
	public function getKeys($array = false){
		if(!$array){
			$array = $this->_fields;
		}
		if($this->is_assoc($array)){
			$keys = array();
			foreach($array as $k => $v){
				if(!is_integer($k)){
					$keys[] = $k;
				}else{
					if(isset($v)){
						$keys[] = $v;
					}else{
						$keys[] = $k;
					}
				}
			}
		}else{
			$keys = $array;
		}
		return $keys;
	}

	/**
	 * This returns the database column name from the object variable name
	 * @param string
	 * @return string
	 */
	public function getColumnName($field){
		if($this->is_assoc($this->_fields)){
			if(in_array($field,array_keys($this->_fields),true)){
				$column = $this->_fields[$field];
			}else{
				$column = $field;
			}
		}else{
			$column = $field;
		}
		$column = empty($column) ? $field : $column;
		return $column;
	}

	/**
	 * Convert the db column to object fields name
	 * @param unknown_type $field
	 * @return unknown_type
	 */
	public function getFieldName($field){
		$field = trim($field);


		if($this->is_assoc($this->_fields)){
			$array_check = $this->getKeys($this->_fields);
			if(in_array($field,$array_check)){
				$column = $field;
			}else{
				foreach($this->_fields as $k => $v){
					if($v == $field){
						$column = $k;
						break;
					}
				}
			}
		}else{
			$column = "";
		}
		$column = empty($column) ? $field : $column;
		return $column;
	}
	public static function is_assoc($array) {
		foreach (array_keys($array) as $k => $v) {
			if ($k !== $v)
			return true;
		}
		return false;
	}
	private function generateWhere($v,$k,$array_check,$tablename,&$wheresql,&$where_values){
		$where_condition = self::MODEL_WHERE_TYPE_EQ;
		$where_combination = self::MODEL_WHERE_TYPE_AND;
		if(is_array($v) && is_array($v[0])){
			foreach($v as $w){
				$this->generateWhere($w,$k,$array_check,$tablename,$wheresql,$where_values);
			}
		}else{
			if(is_array($v)){
				$where_condition = $v[1];
				if(isset($v[2])){
					$where_combination = $v[2];
				}
				$v = $v[0];
			}
			if(isset($v)){
				if( (isset($array_check) && is_array($array_check) && in_array($k, $array_check)) || !$array_check){
					if($tablename == $this->_table){
						$column = $this->getColumnName($k);
					}else{
						$column = $k;
					}
					if($where_condition == self::MODEL_WHERE_TYPE_IN){
						$this->checkQuotes[] = $v;
					}
					$wheresql .= " {$where_combination} {$tablename}.". $this->escOp() . "{$column}". $this->escCl() . "{$where_condition}?";
					$where_values[] = $v;
				}

			}
		}
	}
	private $checkQuotes = array();
}