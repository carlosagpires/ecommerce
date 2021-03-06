<?php 

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;

class Address extends Model 
{


    const SESSION_ERROR = "AddressError";

    public static function getCEP($nrCEP)
    {
        $nrCEP = str_replace("-", "", $nrCEP);

        //https://viacep.com.br/ws/01001000/json/

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://viacep.com.br/ws/$nrCEP/json/");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $data = json_decode(curl_exec($ch), true);

        curl_close($ch);

        return $data;

    }

    public function loadFromCEP($nrCEP)
    {
        $data = Address::getCEP($nrCEP);

        if (isset($data["logradouro"]) && $data["logradouro"] != NULL)
        {
            $this->setdesaddress($data["logradouro"]);
            $this->setdescomplement($data["complemento"]);
            $this->setdesdistrict($data["bairro"]);
            $this->setdescity($data["localidade"]);
            $this->setdesstate($data["uf"]);
            $this->setdescountry("Brasil");
            $this->setdeszipcode($nrCEP);
        }


    }

    public function save()
    {

        $sql = new Sql();

        $params = array(
            ":idaddress" => $this->getidaddress(),
            ":idperson" => $this->getidperson(),
            ":desaddress" => $this->getdesaddress(),
            ":desnumber" => $this->getdesnumber(),
            ":descomplement" => $this->getdescomplement(),
            ":descity" => $this->getdescity(),
            ":desstate" => $this->getdesstate(),
            ":descountry" => $this->getdescountry(),
            ":deszipcode" => $this->getdeszipcode(),
            ":desdistrict" => $this->getdesdistrict()
        );

        //var_dump($params);
        //exit; 

        $results = $sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :desnumber, :descomplement, :descity, :desstate,
            :descountry, :deszipcode, :desdistrict)", $params);

            if (count($results) > 0)
            {
                $this->setData($results[0]);
            }


    }

    public static function setMsgError($msg)
	{

        $_SESSION[Address::SESSION_ERROR] = $msg;

	}

	public static function getMsgError()
	{

        $msg = "";

        if (isset($_SESSION[Address::SESSION_ERROR]))
        {
            $msg = $_SESSION[Address::SESSION_ERROR];
            Address::clearMsgError();
        }

        return $msg;

	}

	public static function clearMsgError()
	{
        unset($_SESSION[Address::SESSION_ERROR]);
	}






}

?>