<?php 

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;


class Product extends Model
{

    public static function listAll()
    {

        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

        $sql = NULL;

    }

    public static function checkList($list)
    {
        foreach ($list as &$row) {
            
            $p = new Product;
            $p->setData($row);
            $row = $p->getValues();
        }

        return $list;


    }

    public function saveDB()
    {
        
        $sql = new Sql();

        $results = $sql->select("
            CALL sp_products_save(
                :idproduct,
                :desproduct,
                :vlprice,
                :vlwidth,
                :vlheight,
                :vllength,
                :vlweight,
                :desurl
            )
        ", array(
            ":idproduct" => $this->getidproduct(),
            ":desproduct" => $this->getdesproduct(),
            ":vlprice" => $this->getvlprice(),
            ":vlwidth" => $this->getvlwidth(),
            ":vlheight" => $this->getvlheight(),
            ":vllength" => $this->getvllength(),
            ":vlweight" => $this->getvlweight(),
            ":desurl" => $this->getdesurl()
        ));

        $this->setData($results[0]);

    }

    public function get($idproduct)
    {

        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", array(
            ":idproduct" => $idproduct 
        ));

        if (count($results) === 0)
        {
            throw new \Exception("Ocorreu um erro ao apagar o produto.");
        }
        else
        {
            $this->setData($results[0]);
        }

    }

    public function delete()
    {

        $sql = new Sql();
        $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", array(
            ":idproduct" => $this->getidproduct() 
        ));

    }

    public function checkPhoto()
    {
        if (file_exists($_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR .
        "res" . DIRECTORY_SEPARATOR . 
        "site" . DIRECTORY_SEPARATOR . 
        "img" . DIRECTORY_SEPARATOR . 
        "products" . DIRECTORY_SEPARATOR . 
        $this->getidproduct() . ".jpg"
        ))
        {
            $url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";
        }
        else
        {
            $url = "/res/site/img/product.jpg";
        }

        return $this->setdesphoto($url);
    }

    public function getValues()
    {

        $this->checkPhoto();

        $values = parent::getValues();

        return $values;
    }

    public function setPhoto($file)
    {
        $extension = explode(".", $file["name"]);
        $extension = end($extension);

        switch ($extension)
        {
            case "jpg":
            case "jpeg":
                $image = imagecreatefromjpeg($file["tmp_name"]);
            break;
            case "gif":
                $image = imagecreatefromgif($file["tmp_name"]);
            break;
            case "png":
                $image = imagecreatefrompng($file["tmp_name"]);
            break;
        }

        if (isset($image))
        {
            $dist = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR .
            "res" . DIRECTORY_SEPARATOR . 
            "site" . DIRECTORY_SEPARATOR . 
            "img" . DIRECTORY_SEPARATOR . 
            "products" . DIRECTORY_SEPARATOR . 
            $this->getidproduct() . ".jpg";

            imagejpeg($image, $dist);

            imagedestroy($image);

            $this->checkPhoto();
        }



    }

    public function deletePhoto($idproduct)
    {
        $dist = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR .
        "res" . DIRECTORY_SEPARATOR . 
        "site" . DIRECTORY_SEPARATOR . 
        "img" . DIRECTORY_SEPARATOR . 
        "products" . DIRECTORY_SEPARATOR . 
        $idproduct . ".jpg";

        if (file_exists($dist))
        {
            unlink($dist);    
        }

    }


    public function getFromURL($desurl)
    {
        $sql = new Sql();
        $rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1", array(
            ":desurl" => $desurl
        ));

        $this->setData($rows[0]);
    }

    public function getCategories()
    {
        $sql = new Sql();

        return $sql->select("
            SELECT * FROM tb_categories a
            INNER JOIN tb_productscategories b
            ON a.idcategory = b.idcategory 
            WHERE b.idproduct = :idproduct
        ", array(
            ":idproduct" => $this->getidproduct()
        ));
    }


    public static function getPage($page = 1, $itemsPerPage = 3)
    {
        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

        $results = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS *
            FROM tb_products
            ORDER BY desproduct
            LIMIT $start, $itemsPerPage;
        ");

        $resultsTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        return array(
            "data" => $results,
            "total" => (int)$resultsTotal[0]["nrtotal"],
            "pages" => ceil((int)$resultsTotal[0]["nrtotal"] / $itemsPerPage)
        );


    }

    public static function getPageSearch($search, $page = 1, $itemsPerPage = 3)
    {
        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

        $results = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS *
            FROM tb_products
            WHERE desproduct LIKE :search
            ORDER BY desproduct
            LIMIT $start, $itemsPerPage;
        ", array(
            ":search" => "%" . $search . "%"
        ));

        $resultsTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        return array(
            "data" => $results,
            "total" => (int)$resultsTotal[0]["nrtotal"],
            "pages" => ceil((int)$resultsTotal[0]["nrtotal"] / $itemsPerPage)
        );


    }





}




?>