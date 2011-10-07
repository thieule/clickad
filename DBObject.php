<?php
class DBObject{
    /**
    * Connection to MySQL.
    *
    * @var string
    */
    var $link;

    /**
    * Holds the most recent connection.
    *
    * @var string
    */
    var $recent_link = null;

    /**
    * Holds the contents of the most recent SQL query.
    *
    * @var string
    */
    var $sql = '';

    /**
    * Holds the number of queries executed.
    *
    * @var integer
    */
    var $query_count = 0;

    /**
    * The text of the most recent database error message.
    *
    * @var string
    */
    var $error = '';

    /**
    * The error number of the most recent database error message.
    *
    * @var integer
    */
    var $errno = '';

    /**
    * Do we currently have a lock in place?
    *
    * @var boolean
    */
    var $is_locked = false;

    /**
    * Show errors? If set to true, the error message/sql is displayed.
    *
    * @var boolean
    */
    var $show_errors = false;
    
    function __construct()
	{
		//$this->db_connect(HOSTNAME,USER,PASS,DB);
	}

    /**
    * Constructor. Initializes a database connection and selects our database.
    *
    * @param  string  Database host
    * @param  string  Database username
    * @param  string  Database password
    * @param  string  Database name
    * @return boolean
    */
    function db_connect($db_host, $db_user, $db_pass, $db_name)
    {
        $this->link = @mysql_connect($db_host, $db_user, $db_pass);
        if ($this->link)
        {
            if (@mysql_select_db($db_name, $this->link))
            {
                $this->recent_link =& $this->link;
                return $this->link;
            }
        }
                
        // If we couldn't connect or select the db...
        $this->raise_error("Could not select and/or connect to database: ".$db_name);
    }

    /**
    * Executes a sql query. If optional $only_first is set to true, it will
    * return the first row of the result as an array.
    *
    * @param  string  Query to run
    * @param  bool    Return only the first row, as an array?
    * @return mixed
    */
    function query($sql, $only_first = false)
    {
        $this->recent_link =& $this->link;
        $this->sql =& $sql;
        $result = @mysql_query($sql, $this->link);

        $this->query_count++;

        if ($only_first)
        {
            $return = $this->fetch_array($result);
            $this->free_result($result);
            return $return;
        }
        return $result;
    }
    
    function loadList($i_sql){
		
   		$resource = $this->query($i_sql);
		$result_array = array();
		
   		if($resource){
			
			$num_rec = 0;
			while($row = mysql_fetch_array($resource))
			{
				$result_array[$num_rec] = $row;
				$num_rec++;
			}
			mysql_free_result($resource);

   			if ($num_rec > 0 )
   				return $result_array;
		}
		return false;
   	}
    
	/**
	* This global function loads the first row of a query into an object
	*
	* @access	public
	* @return 	object
	*/
	function loadObject($query='' )
	{
	   $res=$this->query($query);
		if (!$res) {
			return null;
		}
		$ret = null;
		if ($object = mysql_fetch_object( $res)) {
			$ret = $object;
		}
		mysql_free_result( $res);
		return $ret;
	}

	/**
	* Load a list of database objects
	*
	* If <var>key</var> is not empty then the returned array is indexed by the value
	* the database key.  Returns <var>null</var> if the query fails.
	*
	* @access	public
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	*/
	function loadObjectList( $query='' )
	{
	    $res=$this->query($query);
		if (!$res) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_object( $res )) {
			if (@$key) {
				@$array[$row->$key] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysql_free_result( $res );
		return $array;
	}
    static function QuoteSmart($i_value) {
   		// Stripslashes
    	if (get_magic_quotes_gpc()) 
            $i_value = stripslashes($i_value);
        
		// Quote if not integer
    	if (!is_numeric($i_value)) 
	        $i_value =  "'".htmlspecialchars($i_value)."'" ;
	    return $i_value;

   	}
    
    /**
	 * This method loads the first field of the first row returned by the query.
	 *
	 * @access	public
	 * @return The value returned in the query or null if the query failed.
	 */
	function loadResult($query='')
	{
		if (!($cur = $this->query($query))) {
			return null;
		}
		$ret = null;
		if ($row = mysql_fetch_row( $cur )) {
			$ret = $row[0];
		}
		mysql_free_result( $cur );
		return $ret;
	}
    
    	/**
	 * Load an array of single field results into an array
	 *
	 * @access	public
	 */
	function loadResultArray($query='',$numinarray = 0)
	{
		if (!($cur = $this->query($query))) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_row( $cur )) {
			$array[] = $row[$numinarray];
		}
		mysql_free_result( $cur );
		return $array;
	}

	/**
	* Fetch a result row as an associative array
	*
	* @access	public
	* @return array
	*/
	function loadAssoc($query='')
	{
		if (!($cur = $this->query($query))) {
			return null;
		}
		$ret = null;
		if ($array = mysql_fetch_assoc( $cur )) {
			$ret = $array;
		}
		mysql_free_result( $cur );
		return $ret;
	}

	/**
	* Load a assoc list of database rows
	*
	* @access	public
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	*/
	function loadAssocList($query='', $key='' )
	{
		if (!($cur = $this->query($query))) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_assoc( $cur )) {
			if ($key) {
				$array[$row[$key]] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysql_free_result( $cur );
		return $array;
	}


    /**
    * Fetches a row from a query result and returns the values from that row as an array.
    *
    * @param  string  The query result we are dealing with.
    * @return array
    */
    function fetch_array($result)
    {
        return @mysql_fetch_assoc($result);
    }

    /**
    * Returns the number of rows in a result set.
    *
    * @param  string  The query result we are dealing with.
    * @return integer
    */
    function num_rows($result)
    {
        return @mysql_num_rows($result);
    }

    /**
    * Retuns the number of rows affected by the most recent query
    *
    * @return integer
    */
    function affected_rows()
    {
        return @mysql_affected_rows($this->recent_link);
    }

    /**
    * Returns the number of queries executed.
    *
    * @param  none
    * @return integer
    */
    function num_queries()
    {
        return $this->query_count;
    }

    /**
    * Lock database tables
    *
    * @param   array  Array of table => lock type
    * @return  void
    */
    function lock($tables)
    {
        if (is_array($tables) AND count($tables))
        {
            $sql = '';

            foreach ($tables AS $name => $type)
            {
                $sql .= (!empty($sql) ? ', ' : '') . "$name $type";
            }

            $this->query("LOCK TABLES $sql");
            $this->is_locked = true;
        }
    }

    /**
    * Unlock tables
    */
    function unlock()
    {
        if ($this->is_locked)
        {
            $this->query("UNLOCK TABLES");
            $this->is_locked = false;
        }
    }

    /**
    * Returns the ID of the most recently inserted item in an auto_increment field
    *
    * @return  integer
    */
    function insert_id()
    {
        return @mysql_insert_id($this->link);
    }

    /**
    * Escapes a value to make it safe for using in queries.
    *
    * @param  string  Value to be escaped
    * @param  bool    Do we need to escape this string for a LIKE statement?
    * @return string
    */
    function prepare($value, $do_like = false)
    {
        $value = stripslashes($value);

        if ($do_like)
        {
            $value = str_replace(array('%', '_'), array('\%', '\_'), $value);
        }

        if (function_exists('mysql_real_escape_string'))
        {
            return mysql_real_escape_string($value, $this->link);
        }
        else
        {
            return mysql_escape_string($value);
        }
    }

    /**
    * Frees memory associated with a query result.
    *
    * @param  string   The query result we are dealing with.
    * @return boolean
    */
    function free_result($result)
    {
        return @mysql_free_result($result);
    }

    /**
    * Turns database error reporting on
    */
    function show_errors()
    {
        $this->show_errors = true;
    }

    /**
    * Turns database error reporting off
    */
    function hide_errors()
    {
        $this->show_errors = false;
    }

    /**
    * Closes our connection to MySQL.
    *
    * @param  none
    * @return boolean
    */
    function close()
    {
        $this->sql = '';
        return @mysql_close($this->link);
    }

    /**
    * Returns the MySQL error message.
    *
    * @param  none
    * @return string
    */
    function error()
    {
        $this->error = (is_null($this->recent_link)) ? '' : mysql_error($this->recent_link);
        return $this->error;
    }

    /**
    * Returns the MySQL error number.
    *
    * @param  none
    * @return string
    */
    function errno()
    {
        $this->errno = (is_null($this->recent_link)) ? 0 : mysql_errno($this->recent_link);
        return $this->errno;
    }

    /**
    * Gets the url/path of where we are when a MySQL error occurs.
    *
    * @access private
    * @param  none
    * @return string
    */
    function _get_error_path()
    {
        if ($_SERVER['REQUEST_URI'])
        {
            $errorpath = $_SERVER['REQUEST_URI'];
        }
        else
        {
            if ($_SERVER['PATH_INFO'])
            {
                $errorpath = $_SERVER['PATH_INFO'];
            }
            else
            {
                $errorpath = $_SERVER['PHP_SELF'];
            }

            if ($_SERVER['QUERY_STRING'])
            {
                $errorpath .= '?' . $_SERVER['QUERY_STRING'];
            }
        }

        if (($pos = strpos($errorpath, '?')) !== false)
        {
            $errorpath = urldecode(substr($errorpath, 0, $pos)) . substr($errorpath, $pos);
        }
        else
        {
            $errorpath = urldecode($errorpath);
        }
        return $_SERVER['HTTP_HOST'] . $errorpath;
    }

    /**
    * If there is a database error, the script will be stopped and an error message displayed.
    *
    * @param  string  The error message. If empty, one will be built with $this->sql.
    * @return string
    */
    function raise_error($error_message = '')
    {
        if ($this->recent_link)
        {
            $this->error = $this->error($this->recent_link);
            $this->errno = $this->errno($this->recent_link);
        }

        if ($error_message == '')
        {
            $this->sql = "Error in SQL query:\n\n" . rtrim($this->sql) . ';';
            $error_message =& $this->sql;
        }
        else
        {
            $error_message = $error_message . ($this->sql != '' ? "\n\nSQL:" . rtrim($this->sql) . ';' : '');
        }

        $message = "<textarea rows=\"10\" cols=\"80\">MySQL Error:\n\n\n$error_message\n\nError: {$this->error}\nError #: {$this->errno}\nFilename: " . $this->_get_error_path() . "\n</textarea>";

        if (!$this->show_errors)
        {
            $message = "<!--\n\n$message\n\n-->";
        }
       die("There seems to have been a slight problem with our database, please try again later.<br /><br />\n$message");
    }
    
    function getTableFields( $tables, $typeonly = true )
	{
		settype($tables, 'array'); //force to array
		$result = array();

		foreach ($tables as $tblval)
		{
			$fields = $this->loadObjectList('SHOW FIELDS FROM ' . $tblval);

			if($typeonly)
			{
				foreach ($fields as $field) {
					$result[$tblval][$field->Field] = preg_replace("/[(0-9)]/",'', $field->Type );
				}
			}
			else
			{
				foreach ($fields as $field) {
					$result[$tblval][$field->Field] = $field;
				}
			}
		}

		return $result;
	}
    
    /**
	 * Inserts a row into a table based on an objects properties
	 *
	 * @access	public
	 * @param	string	The name of the table
	 * @param	object	An object whose properties match table fields
	 * @param	string	The name of the primary key. If provided the object property is updated.
	 */
	function insertObject( $table, &$object, $keyName = NULL )
	{
		$fmtsql = 'INSERT INTO '.$this->nameQuote($table).' ( %s ) VALUES ( %s ) ';
		$fields = array();
		foreach (get_object_vars( $object ) as $k => $v) {
			if (is_array($v) or is_object($v) or $v === NULL) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}
			$fields[] = $this->nameQuote( $k );
			$values[] = $this->isQuoted( $k ) ? $this->Quote( $v ) : (int) $v;
		}
		$result=$this->query( sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) ) );
		if (!$result) {
			return false;
		}
		$id = $this->insertid();
		if ($keyName && $id) {
			$object->$keyName = $id;
		}
		return true;
	}
    
     /**
	 * Update a row into a table based on an objects properties
	 *
	 * @access	public
	 * @param	string	The name of the table
	 * @param	object	An object whose properties match table fields
	 * @param	string	The name of the primary key. If provided the object property is updated.
	 */
  function updateObject( $table, &$object, $keyName, $updateNulls=true )
	{
		$fmtsql = 'UPDATE '.$this->nameQuote($table).' SET %s WHERE %s';
		$tmp = array();
		foreach (get_object_vars( $object ) as $k => $v)
		{
			if( is_array($v) or is_object($v) or $k[0] == '_' ) { // internal or NA field
				continue;
			}
			if( $k == $keyName ) { // PK not to be updated
				$where = $keyName . '=' . $this->Quote( $v );
				continue;
			}
			if ($v === null)
			{
				if ($updateNulls) {
					$val = 'NULL';
				} else {
					continue;
				}
			} else {
				$val = $this->isQuoted( $k ) ? $this->Quote( $v ) : (int) $v;
			}
			$tmp[] = $this->nameQuote( $k ) . '=' . $val;
		}
		$result=$this->query( sprintf( $fmtsql, implode( ",", $tmp ) , $where ) );
		return $result;
	}
    
      /**
	 * Get list fields in table
	 *
	 * @access	public
	 * @param	string	The list fields of the table
	 * @param	object	An object whose properties match table fields
	 * @param	string	The name of the primary key. If provided the object property is updated.
	 */
     
    function getFields($tablename)
    {
        $rows=$this->loadObjectList('SHOW COLUMNS FROM '.$tablename);
        foreach((array)$rows as $row)
        {
            if($row->Key!='PRI')
                $result[]=$row->Field;
        }
        return $result;
    }
    
     /**
	 * Get key in table
	 *
	 * @access	public
	 * @param	string	The list fields of the table
	 * @param	object	An object whose properties match table fields
	 * @param	string	The name of the primary key. If provided the object property is updated.
	 */
    function getKey($tablename)
    {
        $rows=$this->loadObjectList('SHOW COLUMNS FROM '.$tablename);
        foreach((array)$rows as $row)
        {
            if($row->Key=='PRI')
            {
                return $row->Field; break;
            }
        }
        return null;
    }

}

?>