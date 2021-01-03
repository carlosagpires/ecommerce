<?php 

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;

class Category extends Model
{
    
    public static function listAll()
    {

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");

    }

    public function saveDB()
    {
        $sql = new Sql();
        /*
        pidcategory INT,
        pdescategory VARCHAR(64)
        */

        $results = $sql->select("
            CALL sp_categories_save(:idcategory, :descategory)
            ",
            array(
                ":idcategory" => $this->getidcategory(),
                ":descategory" => $this->getdescategory()
            )
        );

        //var_dump($results);

        $this->setData($results[0]);

    }

    public function get($idcategory)
    {
        
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
            ":idcategory" => $idcategory 
        ));

        if (count($results) === 0)
        {
            throw new \Exception("Ocorreu um erro ao apagar a Categoria");
        }
        else
        {
            $this->setData($results[0]);
        }

        

    }

    public function delete()
    {

        $sql = new Sql();
        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
            ":idcategory" => $this->getidcategory() 
        ));

    }


}


?>