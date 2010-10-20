<?php

class cDb {
	
	public $dbserver;
	private $dbase;
	private $dbuser;
	private $dbpassword;
	protected static $connection = null;
	private $lastid;
	private $aErrors = array();
	private $role;
	private static $logs = null;
	private static $logClass = "_dbLog";
	
	/**
	 * create an instance of cDb and make the connection
	 * @param string $db[optional] - name of database to connect;
	 * 								default value is set in core/includes/core_defines.inc.php
	 */
	function __construct($db=null) {
		if(is_null(cDb::$logs)){
			cDb::$logs = new cLogsDb("cDb");
		}
		//$this->logs->on();
		$this->dbase = $db;
		if(is_null($this->dbase)){
			$this->dbserver = DB_SERVER;
			$this->dbase = DB_DATABASE;
			$this->dbuser = DB_ADMIN_USER;
    		$this->dbpassword = DB_ADMIN_PASSWORD;
		}
	}

	/**
	 * connect to DB
	 * @return boolean
	 */
	public function connect() {	  	
	  	if(!is_null(self::$connection)) {
	  		self::closeConnection("pervious connection");
	  	}
	  	try{
	  		if(!is_object($this) || get_class($this) != 'cDb'){
	  			throw new cException("Connection must be called from object of cDb class!");
	  		}	
	  	}catch (cException $e){
	  		cDb::$logs->addLog($e->getDbMessageError("cDb: "._METHOD_), "connection",cDb::$logClass);
	  		return false;
	  	}
	  
	  	self::$connection = @mysql_connect($this->dbserver, $this->dbuser, $this->dbpassword);
		try{
	    	if (!self::$connection){	
	    		$msg = "Server wasn't found or incorrect name or password of database user!";
	    		throw new cException($msg);
	    	}
	    	if (!@mysql_select_db ($this->dbase)) {
	    		$msg = 'No connection to database!';    			    		
	    		throw new cException($msg);
	    	}
	    	cDb::$logs->addLog("Successful connection to DB: ".$this->dbase, "connection",cDb::$logClass);
	    	cLogsDb::addFileLog("Successful connection to DB: ".$this->dbase);
	    	return true;	
	  	}catch (cException $e) {
	    	$err = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')');
	  		cDb::$logs->addLog($err, "connection",cDb::$logClass);
	    	cLogsDb::addFileLog($err);
	    	self::$connection = null;
	  		if(is_object($this) && get_class($this) == 'cDb'){
	    		$this->aErrors[] = $msg;
	    	}	    	
	    	return false;
	  	}  		  
	}

	/**
	 * close the existing connection to DB
	 * @param string $msg - message to Logs
	 */
	private function closeConnection($msg="") {
	  if(self::$connection) {
	    mysql_close(self::$connection);
	    $msg = ($msg == "" ? $this->dbase : $msg);
	    cDb::$logs->addLog("Connection closed to DB: $msg", "connection",cDb::$logClass);
	  	cLogsDb::addFileLog("Connection closed: $msg");
	  	//cDb::$logs->addLog()
	  }
	  self::$connection = null;
	}

	/**
	 * returns mysql id of last inserted row
	 * @return string 
	 */
	public function getLastId() {
		if(is_object($this) && get_class($this) == 'cDb') {
			return $this->lastid;
		}else{
			return mysql_insert_id();
		}
	}
	
	public function getAffectedRows() {
		return mysql_affected_rows();
	}
	
	/**
	 * make a select query
	 * @param string/array $aTable - name of table for single table or array for join:
	 * 				array('tab1',array('col','typeOfJoin(LEFT)'),'tab2') = tab1 left join tab2 using(col) or 
	 * 				array('tab1',array('tab1.col','=','tab2.col','typeOfJoin'),'tab2',array(tab2.col, '=','tab3.col', typeOfJoin), 'tab3',...)
	 * @param string/array $aCols - col or array(col,col,...); null means * 
	 * @param array $aWhere - array(col,operator[=,LIKE,..],value) or array(array,operator[AND,OR],array)
	 * @param array $aOrder - array(col,col,...); it is possible to use 'DESC col'
	 * @param int $limit
	 * @return array/boolean - if success returns array of result of mysql select query otherwise false 
	 */ 
	public function select($aTable, $aCols=null, $aWhere=null, $aOrder=null, $limit=null) {
	 	//$conn = dbConnect();
	  
	  	$result = array();
	  	
	  	$table = self::db_getTable($aTable);
	  	$aCols = (is_null($aCols)) ? '*' : $aCols;
	  	$where = "";
	  	$order = "";
	  
	  	if($aWhere) {
	    	$where = "WHERE ".self::db_getWhere($aWhere,true);
	  	}
	  	if(is_string($aCols)){
	  		$cols = $aCols;
	  	} else if(is_array($aCols)){
	    	$cols = implode(",",$aCols);//db_getCols($aCols);
	  	} else {	  	
	  		$msg = __CLASS__.": ".__FUNCTION__.": unsupported format of \$aCols";
	  		if(is_object($this)){
	  			$msg = get_class($this).": ".$msg;
	  		}
	  		cDb::$logs->addLog($query,$msg,cDb::$logClass." _error");
	  		return false;
	  	}
	  
	  	if(is_array($aOrder) && count($aOrder)){
	    	$order = db_getOrder($aOrder);
	  	}else if(!is_null($aOrder)){
	    	$order = "ORDER by $aOrder";
	  	}
	  
	  	$query = "SELECT $cols FROM $table $where $order";
	  	if(!is_null($limit) && $limit > 0) {
	    	$query .= " LIMIT $limit"; 
	  	}
	  	$class = cDb::$logClass;
	  	$msg = __CLASS__.": ".__FUNCTION__;
		if(is_object($this)){
		  	$msg = get_class($this).": ".$msg;
		}
		
	  	try{
	    	if(!$res = mysql_query($query)){
	    		throw new cException();
	    	}
	    	while($data = mysql_fetch_assoc($res)){
	      		$result[] = $data;
	    	}
	  		cLogsDb::addFileLog($query);
		  	if(is_object($this) && is_object($this->logs) && get_class($this) != 'cDb'){
				$this->logs->addLog($query,$msg,$class);
			}else{
				cDb::$logs->addLog($query,$msg,$class);
			}
			
	    	mysql_free_result($res);
	    	return $result;
	  	}catch (cException $e) {
	    	$query = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')',$query);
	    	cLogsDb::addFileLog($query);
	    	cDb::$logs->addLog($query,$msg,$class." _error");	    	
	    	return false;
	  	}
	}

/**
	 * make a select query and returns result as an objects contains one row
	 * @param string/array $aTable - name of table for single table or array for join:
	 * 				array('tab1',array('col','typeOfJoin(LEFT)'),'tab2') = tab1 left join tab2 using(col) or 
	 * 				array('tab1',array('tab1.col','=','tab2.col','typeOfJoin'),'tab2',array(tab2.col, '=','tab3.col', typeOfJoin), 'tab3',...)
	 * @param string/array $aCols - col or array(col,col,...); null means * 
	 * @param array $aWhere - array(col,operator[=,LIKE,..],value) or array(array,operator[AND,OR],array)
	 * @param array $aOrder - array(col,col,...); it is possible to use 'DESC col'
	 * @return object/boolean - if success returns object of result of mysql select query otherwise false 
	 */ 
	public function selectOne($aTable, $aCols=null, $aWhere=null, $aOrder=null) {
	 	//$conn = dbConnect();
	  
	  	$table = self::db_getTable($aTable);
	  	$aCols = (is_null($aCols)) ? '*' : $aCols;
	  	$where = "";
	  	$order = "";
	  
	  	if($aWhere) {
	    	$where = "WHERE ".self::db_getWhere($aWhere,true);
	  	}
	  	if(is_string($aCols)){
	  		$cols = $aCols;
	  	} else if(is_array($aCols)){
	    	$cols = implode(",",$aCols);//db_getCols($aCols);
	  	} else {	  	
	  		$msg = __CLASS__.": ".__FUNCTION__.": unsupported format of \$aCols";
	  		if(is_object($this)){
	  			$msg = get_class($this).": ".$msg;
	  		}
	  		cDb::$logs->addLog($query,$msg,cDb::$logClass." _error");
	  		return false;
	  	}
	  
	  	if(is_array($aOrder) && count($aOrder)){
	    	$order = db_getOrder($aOrder);
	  	}else if(!is_null($aOrder)){
	    	$order = "ORDER by $aOrder";
	  	}
	  
	  	$query = "SELECT $cols FROM $table $where $order LIMIT 1";	  	
	    	
	  	$class = cDb::$logClass;
	  	$msg = __CLASS__.": ".__FUNCTION__;
		if(is_object($this)){
		  	$msg = get_class($this).": ".$msg;
		}
		
	  	try{
	    	if(!$res = mysql_query($query)){
	    		throw new cException();
	    	}
	    	$result = mysql_fetch_object($res);
	      		
	  		cLogsDb::addFileLog($query);
		  	if(is_object($this) && is_object($this->logs) && get_class($this) != 'cDb'){
				$this->logs->addLog($query,$msg,$class);
			}else{
				cDb::$logs->addLog($query,$msg,$class);
			}			
	    	mysql_free_result($res);
	    	return $result;	    	
	  	}catch (cException $e) {
	    	$query = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')',$query);
	    	cLogsDb::addFileLog($query);
	    	cDb::$logs->addLog($query,$msg,$class." _error");	    	
	    	return false;
	  	}	  	
	}
	
/**
	 * make a select query
	 * @param string/array $aTable - name of table for single table or array for join:
	 * 				array('tab1',array('col','typeOfJoin(LEFT)'),'tab2') = tab1 left join tab2 using(col) or 
	 * 				array('tab1',array('tab1.col','=','tab2.col','typeOfJoin'),'tab2',array(tab2.col, '=','tab3.col', typeOfJoin), 'tab3',...)
	 * @param string/array $aCols - col or array(col,col,...); null means * 
	 * @param array $aWhere - array(col,operator[=,LIKE,..],value) or array(array,operator[AND,OR],array)
	 * @param array $aOrder - array(col,col,...); it is possible to use 'DESC col'
	 * @param int $limit
	 * @return array/boolean - if success returns array of objects results 
	 */ 
	public function selectAsObjects($aTable, $aCols=null, $aWhere=null, $aOrder=null, $limit=null) {
	 	//$conn = dbConnect();
	  
	  	$result = array();
	  	
	  	$table = self::db_getTable($aTable);
	  	$aCols = (is_null($aCols)) ? '*' : $aCols;
	  	$where = "";
	  	$order = "";
	  
	  	if($aWhere) {
	    	$where = "WHERE ".self::db_getWhere($aWhere,true);
	  	}
	  	if(is_string($aCols)){
	  		$cols = $aCols;
	  	} else if(is_array($aCols)){
	    	$cols = implode(",",$aCols);//db_getCols($aCols);
	  	} else {	  	
	  		$msg = __CLASS__.": ".__FUNCTION__.": unsupported format of \$aCols";
	  		if(is_object($this)){
	  			$msg = get_class($this).": ".$msg;
	  		}
	  		cDb::$logs->addLog($query,$msg,cDb::$logClass." _error");
	  		return false;
	  	}
	  
	  	if(is_array($aOrder) && count($aOrder)){
	    	$order = db_getOrder($aOrder);
	  	}else if(!is_null($aOrder)){
	    	$order = "order by $aOrder";
	  	}
	  
	  	$query = "SELECT $cols FROM $table $where $order";
	  	if(!is_null($limit) && $limit > 0) {
	    	$query .= " LIMIT $limit"; 
	  	}
	  	$class = cDb::$logClass;
	  	$msg = __CLASS__.": ".__FUNCTION__;
		if(is_object($this)){
		  	$msg = get_class($this).": ".$msg;
		}
		
	  	try{
	    	if(!$res = mysql_query($query)){
	    		throw new cException();
	    	}
	    	while($data = mysql_fetch_object($res)){
	      		$result[] = $data;
	    	}
	  		cLogsDb::addFileLog($query);
		  	if(is_object($this) && is_object($this->logs) && get_class($this) != 'cDb'){
				$this->logs->addLog($query,$msg,$class);
			}else{
				cDb::$logs->addLog($query,$msg,$class);
			}
			
	    	mysql_free_result($res);
	    	return $result;
	  	}catch (cException $e) {
	    	$query = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')',$query);
	    	cLogsDb::addFileLog($query);
	    	cDb::$logs->addLog($query,$msg,$class." _error");	    	
	    	return false;
	  	}
	}
	
	/**
	 * delete records from the table
	 * @param string $table
	 * @param array $aWhere - array(col,operator[=,LIKE,..],value) or array(array,operator[AND,OR],array)
	 * @return boolean/null - null for 0 affected rows; false for bad query; true for 1 affected row at least  
	 */
	public function delete($table, $aWhere=null) {
	  	$result = null;
	  
	  	$where = "";
	  
	  	if($aWhere){
	    	$where = "WHERE ".self::db_getWhere($aWhere,true);
	  	}
	  	$query = "DELETE FROM $table $where";
	  	
	  	$class = cDb::$logClass;
	  	$msg = __CLASS__.": ".__FUNCTION__;
		if(is_object($this)){
		  	$msg = get_class($this).": ".$msg;
		}
	  	  
	  	try{
	    	if(!mysql_query($query)){
	    		throw new cException();
	    	}
	    	if(!mysql_affected_rows()){	    		
	    		$result = true;
	    	}
		  	cLogsDb::addFileLog($query);
	  		if(is_object($this) && is_object($this->logs) && get_class($this) != 'cDb'){
				$this->logs->addLog($query,$msg,$class);
			}else{
				cDb::$logs->addLog($query,$msg,$class);
			}
			
			return $result;	    	
	  	}catch (cException $e) {
	    	$query = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')',$query);
	    	cLogsDb::addFileLog($query);
	    	cDb::$logs->addLog($query,$msg,$class." _error");	    	
	    	return false;
	  	}
	}

	/**
	 * insert record(s) into one table
	 * @param string $table - name of table to insert data
	 * @param array $aValues - array(col[string],value[mixed],string[boolean][optional][default true]) <br>
	 * or array(array,array,...)
	 * @return boolean
	 */
	public function insert($table, $aValues=null) {
		$result = false;
	  
	  	if($aValues){
	    	$aColVal = self::db_parseDataToDbInsert($aValues);
	  	}	  
	  	$query = "INSERT INTO $table ($aColVal[0]) VALUES ($aColVal[1])";
		
	  	$class = cDb::$logClass;
	  	$msg = __CLASS__.": ".__FUNCTION__;
		if(is_object($this)){
		  	$msg = get_class($this).": ".$msg;
		}
			  
	  	try{
	  		if(!mysql_query($query)){
	    		throw new cException();
	    	}
		  	
	    	if(is_object($this) && get_class($this) == 'cDb'){
		  		$this->lastid = mysql_insert_id();
		  	}
		  	
		  	cLogsDb::addFileLog($query);
		  	if(is_object($this) && is_object($this->logs) && get_class($this) != 'cDb'){
				$this->logs->addLog($query,$msg,$class);
			}else{
				cDb::$logs->addLog($query,$msg,$class);
			}
			
	    	return true;
	  	}catch (cException $e) {	    
	    	$query = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')',$query);
	  		cLogsDb::addFileLog($query);
	    	cDb::$logs->addLog($query,$msg,$class." _error");
	    	return false;
	  	}
	}
	
/**
	 * insert record(s) into one table
	 * @param string $table - name of table to insert data
	 * @param array $aCols - array(	array(col1,string[boolean][optional][default true]),
	 * 								array(col2,string[boolean][optional][default true]),
	 * 								array(col3,string[boolean][optional][default true]))
	 * @param array $aValues - array(val1,val2,val3) or array(array(val1,val2,val3),array(...),...)
	 * @return boolean
	 */
	public function insertMore($table, $aCols, $aValues=null) {
		$result = false;
	  	
		$class = cDb::$logClass;
		$msg = __CLASS__.": ".__FUNCTION__;
		if(is_object($this)){
		  	$msg = get_class($this).": ".$msg;
		}
	  	
		try{
	  		if(!is_array($aCols = self::db_parseColsToDbInsertMore($aCols))) {
	  			throw new cException("Incorrect format of aCols!");
	  		}
	  		$cols = $aCols[0];
	  		if(!is_array($aVals = self::db_parseValsToDbInsertMore($aValues,$aCols[1]))) {
	  			throw new cException("Incorrect format of aVals!");
	  		}
	  		$vals = implode(",",$aVals);
	  			  		
	  		$query = "INSERT INTO $table ($cols) VALUES $vals";
			
			cDb::$logs->addLog($query,$msg,$class);
				  
		  	try{
		  		if(!mysql_query($query)){
		    		throw new cException();
		    	}
			  	
		    	if(is_object($this) && get_class($this) == 'cDb'){
			  		$this->lastid = mysql_insert_id();
			  	}
			  	
			  	cLogsDb::addFileLog($query);
			  	if(is_object($this) && is_object($this->logs) && get_class($this) != 'cDb'){
					$this->logs->addLog($query,$msg,$class);
				}else{
					cDb::$logs->addLog($query,$msg,$class);
				}
				
		    	return true;
		  	}catch (cException $e) {	    
		    	$query = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')',$query);
		  		cLogsDb::addFileLog($query);
		    	cDb::$logs->addLog($query,$msg,$class." _error");
		    	return false;
		  	}
	  	}catch(cException $e) {
	  		$err = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')');
	  		cLogsDb::addFileLog($err);
	  		cDb::$logs->addLog($err,$msg,$class." _error");
		    return false;
	  	}
	}
	
	/**
	 * @param string $table - name of table
	 * @param array $aValues - array(column, value, string(true,false)[optional]) or array(array,array,...)
	 * @param array $aWhere - array(column, operator, value) or array,operator(AND,OR),array 
	 * @return boolean - status of updating
	 */	
	public function update($table, $aValues=null, $aWhere=null) {
		$result = false;
	  
	  	$values = "";
	  	$where = "";
	  
	  	if($aWhere){
	    	$where = "WHERE ".self::db_getWhere($aWhere,true);
	  	}
	  	
		if($aValues){
	    	$colVal = self::db_parseDataToDbUpdate($aValues);
	    	$query = "UPDATE $table SET $colVal $where";	    	
		}else{
			return false;
		}
		
		$class = cDb::$logClass;
		$msg = __CLASS__.": ".__FUNCTION__;
		if(is_object($this)){
			$msg = get_class($this).": ".$msg;
		}
	  
		try{
	  		if(!mysql_query($query)){
		    	throw new cException();
	    	}
	   
	    	cLogsDb::addFileLog($query);
	    	cDb::$logs->addLog($query,$msg,"_dbLog");
	  		if(is_object($this) && is_object($this->logs)){
				$this->logs->addLog($query,$msg,"_dbLog");
			}else{
				cDb::$logs->addLog($query,$msg,$class);
			}
			return true;
			
	  	}catch (cException $e) {
	    	$query = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')',$query);
	  		cLogsDb::addFileLog($query);
	  		cDb::$logs->addLog($query,$msg,$class." _error");
	    	return false;
	  	}
	}
	
	public function createCopyOfTable($tablename, $newtablename) {
		$class = cDb::$logClass;
		$msg = __CLASS__.": ".__FUNCTION__;
		if(is_object($this)){
			$msg = get_class($this).": ".$msg;
		}
		
		try{
			/// table doesn't exist ///
			if(!self::checkExistTable($tablename)) {
				throw new cException("Table to copy doesn't exist!");
			}
			
			/// new table already exists ///
			if(self::checkExistTable($newtablename)) {
				throw new cException("New table to recieve data already exists!");				
			}
		}catch(cException $e){
			$err = $e->getMessage();
			if(is_object($this) && is_object($this->logs)){
				$this->logs->addLog($err,$msg,"_dbLog");
			}else{
				cDb::$logs->addLog($err,$msg,"_dbLog");
			}
			return false;
		}
		
		try{
	  		$query = "CREATE TABLE $newtablename LIKE $tablename";
        	if(!mysql_query($query)){
		    	throw new cException("New table to recieve data wasn't created!");
	    	}
	    	cLogsDb::addFileLog($query);
			if(is_object($this) && is_object($this->logs)){
				$this->logs->addLog($query,$msg,"_dbLog");
			}else{
				cDb::$logs->addLog($query,$msg,$class);
			}
			
	   		$query = "INSERT INTO $tablename SELECT * FROM $newtablename";
			if(!mysql_query($query)){
		    	throw new cException("Data weren't copied to new table!");
	    	}	    	
	   		cLogsDb::addFileLog($query);	    	
	  		if(is_object($this) && is_object($this->logs)){
				$this->logs->addLog($query,$msg,"_dbLog");
			}else{
				cDb::$logs->addLog($query,$msg,$class);
			}
			return true;
			
	  	}catch (cException $e) {
	    	$query = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')',$query);
	  		cLogsDb::addFileLog($query);
	  		cDb::$logs->addLog($query,$msg,$class." _error");
	    	return false;
	  	}
			
	}
	
	/**
	 * truncate table
	 * @param string $tablename - name of table
	 */
	public function truncateTable($tablename) {
		$class = cDb::$logClass;
		$msg = __CLASS__.": ".__FUNCTION__;
		if(is_object($this)){
			$msg = get_class($this).": ".$msg;
		}
		
		try{
	  		$query = "TRUNCATE TABLE $tablename";
        	if(!mysql_query($query)){
		    	throw new cException("Table $tablename couldn't be truncated!");
	    	}
	    	cLogsDb::addFileLog($query);
			if(is_object($this) && is_object($this->logs)){
				$this->logs->addLog($query,$msg,"_dbLog");
			}else{
				cDb::$logs->addLog($query,$msg,$class);
			}
			return true;
			
	  	}catch (cException $e) {
	    	$query = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')',$query);
	  		cLogsDb::addFileLog($query);
	  		cDb::$logs->addLog($query,$msg,$class." _error");
	    	return false;
	  	}
	}
	
	/**
	 * drop table
	 * @param string $tablename - name of table
	 */
	public function dropTable($tablename) {
		$class = cDb::$logClass;
		$msg = __CLASS__.": ".__FUNCTION__;
		if(is_object($this)){
			$msg = get_class($this).": ".$msg;
		}
		
		try{
	  		$query = "DROP TABLE $tablename";
        	if(!mysql_query($query)){
		    	throw new cException("Table $tablename couldn't be droped!");
	    	}
	    	cLogsDb::addFileLog($query);
			if(is_object($this) && is_object($this->logs)){
				$this->logs->addLog($query,$msg,"_dbLog");
			}else{
				cDb::$logs->addLog($query,$msg,$class);
			}
			return true;
			
	  	}catch (cException $e) {
	    	$query = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')',$query);
	  		cLogsDb::addFileLog($query);
	  		cDb::$logs->addLog($query,$msg,$class." _error");
	    	return false;
	  	}
	}
	
	private function db_getTable($aTable)
	{
	  if(is_string($aTable))
	    return $aTable;
	  
	  //array('user',array('id_user','LEFT'),'story')
	  // ODPOVIDA user left join story using(id_user)
	  
	  //array('user',array('id_user','=','i_user','INNER'),'story')  
	  // ODPOVIDA user inner join story on(id_user = i_user)
	  
	  $ret = "";
	  for($i=0; $i < count($aTable); $i++){
	    /// join ///
	    if(is_array($aTable[$i])){
	       if(!isset($aTable[$i+1]))
	        continue;
	        
	      /// join using() ///
	      if(count($aTable[$i]) < 3){
	        /// INNER OUTER LEFT RIGHT ///
	        if(isset($aTable[$i][1]))
	          $ret .= " ".$aTable[$i][1];
	        $ret .= " JOIN ".$aTable[$i+1]." USING(".$aTable[$i][0].")";      
	      /// join on() ///
	      }else{
	        if(isset($aTable[$i][3]))
	          $ret .= " ".$aTable[$i][3];
	        $ret .= " JOIN ".$aTable[$i+1]." ON(".$aTable[$i][0]." ".$aTable[$i][1]." ".$aTable[$i][2].")";
	      }
	      $i++;
	    
	    /// table ///
	    }else{
	      $ret .= "$aTable[$i]";
	    }
	  }
	  
	  return $ret;
	}
	
	/**
	 * 
	 * @param array $aCols
	 * @return string
	 */
	private function db_getCols($aCols)
	{
	  return implode(",",$aCols);
	}
	
	/**
	 * parse an array of where clauses into right form for mysql query(select,update...) 
	 * @param array $aWhere - array(column, operator, value) or array,operator(AND,OR),array
	 * @param boolean $first
	 * @return string - mysql WHERE clause
	 */	
	private function db_getWhere($aWhere, $first=false)
	{
	  $ret = "";
	  
	  if(!is_array($aWhere[0]) && $first){
	    return self::db_getWhere(array($aWhere));
	  }
	  
	  for($i = 0; $i < count($aWhere); $i++){    
	    if(is_array($aWhere[$i])){
	      if (is_array($aWhere[$i][0])){
	        $ret .= "(".self::db_getWhere($aWhere[$i]).")";
	        continue;
	      }else{
	        if(is_numeric($aWhere[$i][2])){
	          $val = $aWhere[$i][1]." ".$aWhere[$i][2]." ";
	        }elseif(is_null($aWhere[$i][2])){
	          if($aWhere[$i][1] == '=')
	            $val = "IS NULL ";
	          else
	            $val = "IS NOT NULL ";
	        }else{
	          $val = $aWhere[$i][1]." '".$aWhere[$i][2]."' ";
	        }
	        $ret .= $aWhere[$i][0]." ".$val;
	      }
	    }else{
	      $ret .= $aWhere[$i]." ";
	    }  
	  }
	  
	  return $ret;
	}
	
	
	private function db_getOrder($aOrder)
	{
	  $ret = "order by ";
	  
	  for($i = 0; $i < count($aOrder); $i++){
	    $ret .= $aOrder[$i].","; 
	  }
	  $ret = preg_replace('/,$/','',$ret);     
	  return $ret;
	}
	
	/**
	 * @param array $aData - array of cols and vals - array(array(col,value,string(def=true)) || array(col,value,string(def=true))
	 * @return array (string($vals),string($cols))
	 */  
	private function db_parseDataToDbInsert($aData)
	{
	  $vals = "";
	  $cols = "";
	  
	  if( is_null($aData) || (!is_array($aData) || !count($aData)) )
	    return array($cols,$vals);
	    
	  if(!is_array($aData[0]))
	    return self::db_parseDataToDbInsert(array($aData));
	  
	  foreach($aData as $k=>$arr)
	  {    
	    $cols .= $arr[0].",";
	    
	    /// value must be string ///
	    if(!isset($arr[2]) || $arr[2] == true)
	      $vals .= "'".$arr[1]."',";
	    else
	      $vals .= $arr[1].",";      
	  }
	  
	  $vals = preg_replace('/,$/','',$vals);
	  $cols = preg_replace('/,$/','',$cols);
	  
	  return array($cols,$vals);
	}
	
	private function db_parseDataToDbUpdate($aData)
	{
	  $retData = "";
	  
	  if( is_null($aData) || (!is_array($aData) || !count($aData)) )
	    return $retData;
	    
	  if(!is_array($aData[0]))
	    return self::db_parseDataToDbUpdate(array($aData));
	  
	  foreach($aData as $k=>$arr)
	  {    
	    $retData .= $arr[0]." = ";
	    
	    /// value must be string ///
	    if(!isset($arr[2]) || $arr[2] == true)
	      $retData .= "'".$arr[1]."',";
	    else
	      $retData .= $arr[1].",";      
	  }
	  
	  $retData = preg_replace('/,$/','',$retData);
	  
	  return $retData;
	}
	
	/**
	 * return array(cols[string],types of cols[array]), where types of cols is array of boolean values for each col - true=string,false=other(without apostrophe)
	 * @param array $aData - array(	array(col1,string[boolean][optional][default true]),
	 * 								array(col2,string[boolean][optional][default true]),
	 * 								array(col3,string[boolean][optional][default true]))
	 * return array/false
	 */
	private function db_parseColsToDbInsertMore($aData) {
	  	if(!is_array($aData)) {
	    	return false;
	  	}
		$aCols = array();
		$aTypes = array();	  
		foreach($aData as $k=>$arr) {    
			$aCols[] = $arr[0];
			if(isset($arr[1]) && $arr[1] === false){
				$aTypes[] = false;
			}else{
				$aTypes[] = true;
			}      
		}
		if(!count($aCols)) {
			return false;
		}
		return array(implode(",",$aCols), $aTypes);
	}
	
	/**
	 * return array of arrays with vals[string]:"(val1,val2,'val3')" according to types of cols(string or others)
	 * @param array $aData - array(val1,val2,val3) or array(array(),array(),...)	
	 * return array/false
	 */
	private function db_parseValsToDbInsertMore($aData,$aTypes, $parsingFinal = true) {
		$retData = array('cols','types');
	  
	  	if(!is_array($aData)) {
	    	return false;
	  	}
		$aVals = array();
			  
		foreach($aData as $k=>$val) {    
			/// more rows to insert ///
			if(is_array($val)){
				$aVals[] = self::db_parseValsToDbInsertMore($val,$aTypes,false);
				continue;
			}
			///**** parsing values for one row ****///
			$oneRow = true;
			/// col is string type or require '' ///			
			if($aTypes[$k]){
				$aVals[] = "'$val'";
			}else{
				$aVals[] = $val;
			}      
		}
		
		/// return of vals for one row from recursive call///
		if(!$parsingFinal && isset($oneRow)){
			return "(".implode(",",$aVals).")";
		}
		/// final return with one row ///
		if($parsingFinal && isset($oneRow)) {
			return array("(".implode(",",$aVals).")");
		}		
		/// final return with more rows///
		return $aVals;
	}
	/**
	 * returns array of errors; if no errors returns empty array
	 * @return array
	 */
	public function getErrors() {
		return $this->aErrors;	
	}
	
	function __destruct() {
		$this->closeConnection();
	}
	
	/**
	 * check if table exists in database and return true if exists, otherwise false
	 * @param string $tablename - name of table
	 * @return boolean
	 */
	public function checkExistTable($tablename) {
		if(is_object($this) && isset($this->dbase)){
			$database = $this->dbase;
		}else{
			$res = mysql_query("SELECT DATABASE()");
        	$database = mysql_result($res, 0);						
		}
		$res = mysql_query("SELECT COUNT(*) AS count
	        FROM information_schema.tables
	        WHERE table_schema = '$database'
	        AND table_name = '$tablename'");
		
		return mysql_result($res, 0) == 1;		
	}
}
?>
