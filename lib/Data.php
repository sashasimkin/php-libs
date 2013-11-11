<?php
/**
 * Db thin wrapper
 *
 * @author Sasha Simkin <sashasimkin@gmail.com>
 */


/**
 * Class QueryResult
 *
 * @property $data array Query result
 * @property $count integer Count retrieved/updated/added/removed records
 * @property $id integer If it is Insert query returns id
 */
class QueryResult{
    public function __construct($data){
        foreach($data as $k => $v) {
            $this->{'__' . $k} = $v;
        }
    }
    public function __get($name) {
        return $this->{'__' . $name};
    }

    public function values_list($field) {
        $res = array();
        foreach($this->__data as $row) {
            if(isset($row[$field])) {
                $res[] = $row[$field];
            } else break;
        }

        return $res;
    }
}


/**
 * Main class for handle db connection
 */
class Data{

	/**
	 * Internal db identifier
	 *
	 * @var object
	 */
	private $db;

	/**
	 * How fetch data when SELECT data from Db
	 *
	 * @var int
	 */
	private $fetchMode=PDO::FETCH_ASSOC;

	/**
	 * Queries count for object.
	 * Just for stats
	 *
	 * @var int
	 */
	private $qCount=0;

	/**
	 * Construct class, connect to Db and save identifier into $this->db
	 *
	 * @param $config array Connection settings
	 */
	public function __construct($config) {

		try {
			$this->db=new PDO('mysql:host=' . $config['hostname'] . ';dbname=' . $config['db'], $config['user'], $config['password']);

			$this->db->query('set names "utf8"');

			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {

			$this->error('connect', array('message'=>$e->getMessage()));
		}
	}

	/**
	 * Querying database
	 *
	 * @param $query
	 *
	 * @return QueryResult Object with query result and some additional fields
	 */
	public function query($query) {
        $args = func_get_args();
        array_shift($args); //first element is not an argument but the query itself, should removed

		$query=trim($query);
		$type=trim(strtoupper(substr($query, 0, strpos($query, ' '))));

		try {
			$q = $this->db->prepare($query);
            $q->execute($args);

			$data['status']=true;

			switch($type) {
				case 'SELECT':
					$data['data']=$q->fetchAll($this->fetchMode);
					$data['count']=count($data['data']);
					break;

				case 'INSERT':
					$data['count']=$q->rowCount();
					$data['id']=$this->db->lastInsertId();
					break;

				case 'UPDATE':
					$data['count']=$q->rowCount();
					break;

				case 'DELETE':
					$data['count']=$q->rowCount();
					break;

				case 'TRUNCATE':
					throw new PDOException('Incorrect query type.');
					break;
				default: break;
			}

			$this->qCount++;

			return new QueryResult($data);
		} catch(PDOException $e) {
			$this->error($type, array('message'=>$e->getMessage(), 'query'=>$query));

			$data['status']=false;

			return (object) $data;
		}
	}

	/**
	 * Set pdo attributes directly to connection
	 *
	 * @param $attr Attribute name
	 * @param $value Attribute value
	 *
	 * @return bool
	 */
	public function setAttribute($attr, $value) {
		return $this->db->setAttribute($attr, $value);
	}

	/**
	 * Set fetch mode
	 *
	 * @param $mode
	 *
	 * @return mixed
	 */
	public function setFetchMode($mode) {
		return $this->fetchMode=$mode;
	}

	/**
	 * Escape string
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function quote($value) {
		return $this->db->quote($value);
	}

	/**
	 * Show errors
	 *
	 * @param string $operation
	 * @param array $message
	 *
	 * @throws Exception
	 */
	private function error($operation, $message) {
		throw new Exception('DataBase Error: ' . $operation . '<br /> Message: ' . $message['message'] . '<br /> Query: ' . $message['query'] . '<hr />');
	}

	/**
	 * Get queries count for current object
	 *
	 * @return int
	 */
	public function qCount() {
		return $this->qCount;
	}
}
