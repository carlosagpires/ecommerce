<?php 

use Hcode\PageAdmin;
use Hcode\Model\User;

//Listar todos os utilizadores



$app->get("/admin/users", function(){

	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();
	$page->setTpl("users", array(
		"users"=>$users
	));

});

//Ir para o écran de criação de um novo utilizador

$app->get("/admin/users/create", function(){

	User::verifyLogin();
	
	$page = new PageAdmin();
	$page->setTpl("users-create");

});

//Apagar um utilizador

$app->get("/admin/users/:iduser/delete", function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);
	$user->deleteDB();

	header("Location: /admin/users");

	exit;

});

//Ver os detalhes de um utilizador

$app->get("/admin/users/:iduser", function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	
	$page = new PageAdmin();
	$page->setTpl("users-update", array(
		"user" => $user->getValues()
	));

});


//Guardar na base de dados um novo utilizador

$app->post("/admin/users/create", function(){

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	if (is_int($_POST["nrphone"]) === false)
	{
		$_POST["nrphone"] = 0;

	}

	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [

		"cost"=>12

	]);

	$user->setData($_POST);
	$user->saveDB();

	header("Location: /admin/users");
	exit;

});

//Guardar na base de dados as alterações feitas a um utilizador

$app->post("/admin/users/:iduser", function($iduser){

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	if (is_int($_POST["nrphone"]) === false)
	{
		$_POST["nrphone"] = 0;

	}

	$user->get((int)$iduser);
	$user->setData($_POST);
	$user->updateDB();

	header("Location: /admin/users");

	exit;

});


?>