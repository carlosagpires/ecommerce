<?php 

session_start();

require_once("vendor/autoload.php");

use Slim\Slim;
use Hcode\Page;
use Hcode\PageAdmin;
use Hcode\Model\User;
use Hcode\Model\Category;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Page();
	$page->setTpl("index");

});

$app->get('/admin', function() {

	User::verifyLogin();
    
	$page = new PageAdmin();
	$page->setTpl("index");

});

$app->get('/admin/login', function(){

		$page = new PageAdmin([
			"header" => false,
			"footer" => false
		]);
		$page->setTpl("login");
});

$app->post('/admin/login', function(){

	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;

});

$app->get('/admin/logout', function(){

		User::logout();
		header("Location: /admin/login");
		exit;
});


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

//Esqueci a senha
$app->get("/admin/forgot", function() {

	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);
	$page->setTpl("forgot");
	exit;

});

//envio do email do esqueci a senha
$app->post("/admin/forgot", function(){


	$user = User::getForgot($_POST["email"]);


	header("Location: /admin/forgot/sent");
	exit;

});

$app->get("/admin/forgot/sent", function(){
	
	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);
	$page->setTpl("forgot-sent");
	exit;
});

$app->get("/admin/forgot/reset", function(){
	
	$user = User::validForgotDecrypt($_GET["code"]);
	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);
	$page->setTpl("forgot-reset", array(
		"name" => $user["desperson"],
		"code" => $_GET["code"]
	));
	exit;
});

$app->post("/admin/forgot/reset", function(){
	
	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);
	
	$user = new User();

	$user->get((int)$forgot["iduser"]);


	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [

		"cost"=>12

	]);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);
	$page->setTpl("forgot-reset-success");
	exit;
});

$app->get("/admin/categories", function(){

	User::verifyLogin();

	$categories = Category::listAll();

	$page = new PageAdmin();
	$page->setTpl("categories", [
		"categories" => $categories
	]);
	exit;
});

$app->get("/admin/categories/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();
	$page->setTpl("categories-create");
	exit;
});

$app->post("/admin/categories/create", function(){

	User::verifyLogin();

	$category = new Category();
	$category->setData($_POST);
	$category->saveDB();

	header("Location: /admin/categories");

	exit;

});

$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);
	$category->delete();

	header("Location: /admin/categories");

	exit;

});

$app->get("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$page = new PageAdmin();
	$page->setTpl("categories-update", array(
		"category" => $category->getValues()
	));
	exit;

});

$app->post("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);
	$category->setData($_POST);
	$category->saveDB();

	header("Location: /admin/categories");

	exit;

});

$app->run();

 ?>