<?php
    class MYSQL_OPERATIONS{
        private $dbcon;

        public function __construct()
        {
            $this->dbcon = $GLOBALS['connection'];
        }

        public function update($table, $data, $where, $echo='')
        {
            $count = count($data);
            $where_count = count($where);

            $sql = "UPDATE `".$table."` SET";

            $start = 0;
            foreach ($data as $k => $v)
            {
                $start = $start + 1;

                if($start == $count)
                {
                    $sql .= " `".$k."` = '".$this->esc($v)."'";
                } else {
                    $sql .= " `".$k."` = '".$this->esc($v)."', ";
                }
            }
            $sql .= " WHERE ";

            if($where_count == 1)
            {
                foreach($where as $m => $n)
                {
                    $sql .= "`".$m."` = '".$this->esc($n)."'";
                }
            }
            else
            {
                $x = 0;
                foreach($where as $m => $n)
                {
                    $x = $x + 1;
                    if($x == $where_count)
                    {
                        $sql .= "`".$m."` = '".$this->esc($n)."'";
                    } else {
                        $sql .= "`".$m."` = '".$this->esc($n)."' and ";
                    }
                }
            }
            
            if($echo!=''){
                echo $sql;
            }
            $update = mysqli_query($this->dbcon,$sql);

            if($update)
                return true;
            else
                return false;
        }

        public function insert($table, $data, $echo='', $getID='')
        {
            $count = count($data);
            $sql = "INSERT INTO `".$table."` (";

            $start = 0;
            foreach ($data as $k => $v)
            {
                $start = $start + 1;

                if($start == $count)
                {
                    $sql .= "`".$k."`";
                } else {
                    $sql .= "`".$k."`, ";
                }
            }

            $sql .= ") value (";

            $number = 0;
            foreach ($data as $k => $v)
            {
                $number = $number + 1;

                if($number == $count)
                {
                    $sql .= "'".$this->esc($v)."'";
                } else {
                    $sql .= "'".$this->esc($v)."', ";
                }
            }
            $sql .= ")";

            $insert = mysqli_query($this->dbcon,$sql);
            if($echo!=''){
                echo $sql;
            }

            if($insert){
                if($getID!=''){
                    return mysqli_insert_id($this->dbcon);
                }else{
                    return true;
                }
            }else
            {
                return false;
            }
                
        }

        public function delete($table, $where)
        {
            $where_count = count($where);
            $sql = "DELETE FROM `".$table."` WHERE ";

            if($where_count == 1)
            {
                foreach($where as $m => $n)
                {
                    $sql .= "`".$m."` = '".$this->esc($n)."'";
                }
            }
            else
            {
                $x = 0;
                foreach($where as $m => $n)
                {
                    $x = $x + 1;
                    if($x == $where_count)
                    {
                        $sql .= "`".$m."` = '".$this->esc($n)."'";
                    } else {
                        $sql .= "`".$m."` = '".$this->esc($n)."' and ";
                    }
                }
            }
            //echo $sql;
            $delete = mysqli_query($this->dbcon,$sql) or die ($sql);

            if($delete)
                return true;
            else
                return false;
        }

        public function query($sql, $echo='')
        {
            if($echo!=''){
                echo $sql;
            }
            $query = mysqli_query($this->dbcon,$sql);

            if($query)
            {
                $result = array();
                while ($record = mysqli_fetch_array($query))
                {
                    $result[] = $record;
                }
                return $result;
            } else {
                return false;
            }

        }

        public function select_query($sql)
        {
            $query = mysqli_query($this->dbcon,$sql) or die ($sql);
            $record = mysqli_fetch_array($query);
            return $record;
        }

        public function select($table, $like, $where = '', $order = '', $limit = '')
        {
            $count = count($like);
            $where_count = count($where);

            $sql = "select ";

            $x = 0;
            foreach($like as $value)
            {
                $x = $x + 1;
                if($x == $count)
                {
                    $sql .= "`".$value."`";
                } else {
                    $sql .= "`".$value."`, ";
                }
            }
            $sql .= " from `".$table."`";

            //where
            if($where != '')
            {
                $sql .= " where ";

                if($where_count == 1)
                {
                    foreach($where as $m => $n)
                    {
                        $sql .= "`".$m."` = '".$this->esc($n)."'";
                    }
                }
                else
                {
                    $x = 0;
                    foreach($where as $m => $n)
                    {
                        $x = $x + 1;
                        if($x == $where_count)
                        {
                            $sql .= "`".$m."` = '".$this->esc($n)."'";
                        } else {
                            $sql .= "`".$m."` = '".$this->esc($n)."' and ";
                        }
                    }
                }
            }
            //where end

            if($order == true)
            {
                $sql .= " ORDER BY ";
                foreach($order as $m => $n)
                {
                    $sql .= "`".$m."` ".$n;
                }
            }
            if($limit == true)
            {
                $sql .= " limit ";
                foreach($limit as $m => $n)
                {
                    $sql .= $this->esc($m).", ".$this->esc($n);
                }
            }
            //echo $sql;
            $query = mysqli_query($this->dbcon,$sql) or die ($sql);

            if($query)
            {
                $result = array();
                while ($record = mysqli_fetch_array($query))
                {
                    $result[] = $record;
                }
                return $result;
            } else {
                return false;
            }

        }

        public function get_data($tableName, $where, $wherevalue, $rowname){
            $sql = mysqli_query($this->dbcon,"SELECT `".$rowname."` FROM $tableName WHERE $where = '$wherevalue'");
            $row = mysqli_fetch_assoc($sql);
            return $row["$rowname"];
        }

        public function getRow($tableName, $where, $wherevalue){
            //echo "SELECT * FROM $tableName WHERE $where = '$wherevalue'<br>";
            $sql = mysqli_query($this->dbcon, "SELECT * FROM $tableName WHERE $where = '$wherevalue'");
            return $row = mysqli_fetch_assoc($sql);

        }

        public function customQuery($tableName,$where){
            $sql = mysqli_query($this->dbcon, "SELECT * FROM $tableName $where");
            //echo "SELECT * FROM $tableName $where<br>";
            return $row = mysqli_fetch_assoc($sql);
        }

        public function selectAll($table, $where=''){
            $result = array();
            $data = $this->esc($table);
            $all = mysqli_query($this->dbcon, "SELECT * FROM `$data` $where");
            //echo 'SELECT * FROM `'.$data.'` '.$where.'<br>';
            while($table= mysqli_fetch_array($all))
            {
                $result[] = $table;
            }
            return $result;
        }          

        public function num_rows($sql)
        {
            $result = mysqli_query($this->dbcon, $sql) or die ($sql);
            $count = mysqli_num_rows($result);
            if($count > 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        public function esc($string)
        {
            return mysqli_real_escape_string($this->dbcon, $string);
        }

        public function get($key)
        {
            $_GET[$key] = htmlspecialchars(stripslashes($_GET[$key]));
            $_GET[$key] = str_ireplace("script", "blocked", $_GET[$key]);
            $_GET[$key] = mysqli_real_escape_string($this->dbcon, $_GET[$key]);
            return $_GET[$key];
        }

        public function post($key)
        {
            $_POST[$key] = (stripslashes($_POST[$key]));
            //$_POST[$key] = str_ireplace("script", "blocked", $_POST[$key]);
            //$_POST[$key] = mysqli_real_escape_string($this->dbcon, $_POST[$key]);
            return $_POST[$key];
        }

        public function remove($var)
        {
            $var = str_ireplace(array('"', "'", '\r\n', '\\', '\"', '£', '&'), array('&#34;', '&#39;', '', '', '', '&pound;', '&amp;'), $var);
            return $var;
        }
		
		public function remove_tags($var)
		{
			return html_entity_decode(strip_tags($var));
		}
		
		public function char($var)
		{
			$var = str_ireplace(array('"', "'", '\r\n', '\\', '\"', '&'), array('&#34;', '&#39;', '', '', '', '&amp;'), $var);
            return $var;
		}

        // execute query
        public function execute_query($sql)
        {
            $result = mysqli_query($this->dbcon, $sql);

            return $result;
        }
    }

    /*mysql_connect("localhost","root","") or die("Sql not connect.");
    mysql_select_db("res") or die("Database could not found.");

    $table = 'comments';
    $data = array(
    'name'        =>    'razib',
    'email'           =>    'razib@razib.com',
    'comments'        =>    '1234522233'
    );
    $where = array(
    'id'       =>    '1'
    );

    //select
    $field = array('restaurantid',  'catid',  'restaurant_name');
    $where = array('restaurantid' => '6', 'status' => '1');
    $order = array('restaurantid' => 'asc');
    $limit = array('0' => '2');
    $select = $db->select('restaurant_info', $field, $where, $order, $limit);

    for ($x = 0; $x < count($select); $x++) {

    echo "<tr>";
    echo "<td>".$select[$x][0]."</td>";
    echo "<td>".$select[$x][1]."</td>";
    echo "<td>".$select[$x][2]."</td>";
    echo "</tr>";
    }
    */

    //$insert = insert($table, $data);
    //$update = update($table, $data, $where);
?>