<?php 

use Hcode\PageAdmin;
use Hcode\Model\User;

//Listar todos os utilizadores


$app->get("/admin/users/:iduser/password", function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-password", array(
		"user" => $user->getValues(),
		"msgError" => User::getError(),
		"msgSuccess" => User::getSuccess()
	));


});

$app->post("/admin/users/:iduser/password", function($iduser){

	User::verifyLogin();

	if (!isset($_POST["despassword"]) || $_POST["despassword"] === "")
	{
		User::setError("Preencha a nova senha");
		header("Location: /admin/users/$iduser/password");
		exit;
	}

	if (!isset($_POST["despassword-confirm"]) || $_POST["despassword-confirm"] === "")
	{
		User::setError("Preencha a confirmação da nova senha");
		header("Location: /admin/users/$iduser/password");
		exit;
	}

	if ($_POST["despassword"] !== $_POST["despassword-confirm"])
	{
		User::setError("Confirme corretamente as senhas.");
		header("Location: /admin/users/$iduser/password");
		exit;
	}

	$user = new User();

	$user->get((int)$iduser);

	$user->setPassword(User::getPasswordHash($_POST["despassword"]));

	User::setSuccess("Senha alterada corretamente.");
		header("Location: /admin/users/$iduser/password");
		exit;


});


$app->get("/admin/users", function(){

	User::verifyLogin();


	$search = (isset($_GET["search"])) ? $_GET["search"] : "";
	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

	if ($search != "")
	{
		$pagination = User::getPageSearch($search, $page);
	}
	else
	{
		$pagination = User::getPage($page);
	}

	$pages = array();

	for ($x = 0; $x < $pagination["pages"]; $x++)
	{
		array_push($pages, array(
			"href" => "/admin/users?" . http_build_query(array(
				"page" => $x+1,
				"search" => $search
			)),
			"text" => $x+1
		));
	}

	$page = new PageAdmin();
	$page->setTpl("users", array(
		"users" => $pagination["data"],
		"search" => $search,
		"pages" => $pages
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