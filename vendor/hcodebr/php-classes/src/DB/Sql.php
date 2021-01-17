<?php 

namespace Hcode\DB;

class Sql {

	const HOSTNAME = "127.0.0.1";
	const USERNAME = "php7";
	const PASSWORD = "php7root$";
	const DBNAME = "db_ecommerce";

	private $conn;

	public function __construct()
	{

		$this->conn = new \PDO(
			"mysql:dbname=".Sql::DBNAME.";host=".Sql::HOSTNAME, 
			Sql::USERNAME,
			Sql::PASSWORD
		);

	}

	private function setParams($statement, $parameters = array())
	{

		foreach ($parameters as $key => $value) {
			
			$this->bindParam($statement, $key, $value);

		}

	}

	private function bindParam($statement, $key, $value)
	{

		$statement->bindParam($key, $value);

	}

	public function query($rawQuery, $params = array())
	{

		try {

			$stmt = $this->conn->prepare($rawQuery);
			$this->setParams($stmt, $params);
			$stmt->execute();

		} catch (\Throwable $th) {
			throw new \Exception("Ocorreu um erro no acesso à base de dados: $th");
			exit;
		}

	}

	public function select($rawQuery, $params = array()):array
	{

		try {
			$stmt = $this->conn->prepare($rawQuery);
			$this->setParams($stmt, $params);
			$stmt->execute();
	
			return $stmt->fetchAll(\PDO::FETCH_ASSOC);

		} catch (\Throwable $th) {
			throw new \Exception("Ocorreu um erro no acesso à base de dados: $th");
			exit;
		}

	}

}

 ?>