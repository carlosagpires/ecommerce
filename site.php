<?php

use Hcode\Model\Address;
use Hcode\Model\Cart;
use Hcode\Page;
use Hcode\Model\Category;
use Hcode\Model\Product;
use Hcode\Model\User;
use Slim\Slim;

if (!isset($app))
{
    $app = new Slim();
}

$app->get('/', function() {

	$products = Product::listAll();
    
	$page = new Page();
	$page->setTpl("index", array(
		"products" => Product::checkList($products)
	));

});

$app->get("/categories/:idcategory", function($idcategory){

	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;
	
	
	$category = new Category();
	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = array();

	for ($i = 1; $i <= $pagination["pages"] ; $i++) { 
		array_push($pages, array(
			"link" => "/categories/" . $category->getidcategory() . "?page=" . $i,
			"page" => $i
		));
	}

	$page = new Page();
	$page->setTpl("category", array(
		"category" => $category->getValues(),
		"products" => $pagination["data"],
		"pages" => $pages
	));
	exit;

});

$app->get("/products/:desurl", function($desurl){

	$product = new Product();
	$product->getFromURL($desurl);

	$page = new Page();
	$page->setTpl("product-detail", array(
		"product" => $product->getValues(),
		"categories" => $product->getCategories()
	));

});

$app->get("/cart", function(){

	$cart = Cart::getFromSession();
	
	$page = new Page();
	$page->setTpl("cart", array(
		"cart" => $cart->getValues(),
		"products" => $cart->getProducts(),
		"error" => Cart::getMsgError()
	));
	
});

$app->get("/cart/:idproduct/add", function($idproduct){

	$product = new Product();
	$product->get((int)$idproduct);
	$cart = Cart::getFromSession();

	$qtd = (isset($_GET["qtd"])) ? (int)$_GET["qtd"] :1;

	for ($i=0; $i < $qtd; $i++) { 
		$cart->addProduct($product);
	}

	header("Location: /cart");
	exit;
});

$app->get("/cart/:idproduct/minus", function($idproduct){

	$product = new Product();
	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();
	$cart->removeProduct($product);

	header("Location: /cart");
	exit;
});

$app->get("/cart/:idproduct/remove", function($idproduct){

	$product = new Product();
	$product->get((int)$idproduct);
	$cart = Cart::getFromSession();
	$cart->removeProduct($product, true);

	header("Location: /cart");
	exit;
});

$app->post("/cart/freight", function(){

	$cart = Cart::getFromSession();
	$cart->setFreight($_POST["zipcode"]);

	header("Location: /cart");
	exit;
});

$app->get("/checkout", function(){
	
	User::verifyLogin(false);

	$cart = Cart::getFromSession();
	$address = new Address();

	$page = new Page();
	$page->setTpl("checkout", array(
		"cart" => $cart->getValues(),
		"address" => $address->getValues()
	));

});

$app->get("/login", function(){

	$page = new Page();
	$page->setTpl("login", array(
		"error" => User::getError(),
		"errorRegister" => User::getErrorRegister(),
		"registerValues" => (isset($_SESSION["registerValues"])) ? $_SESSION["registerValues"] : array(
			"name" => "",
			"email" => "",
			"phone" => ""
		)
	));

});

$app->post("/login", function(){

	try {
		User::login($_POST["login"], $_POST["password"]);
	} catch (Exception $e) {
		
		User::setError($e->getMessage());
	}


	header("Location: /checkout");
	exit;


});

$app->get("/logout", function(){

	User::logout();

	header("Location: /login");
	exit;
});

$app->post("/register", function(){

	$_SESSION["registerValues"] = $_POST;

	if (!isset($_POST["name"]) || $_POST["name"] == "")
	{
		User::setErrorRegister("Preencha o seu nome.");
		header("Location: /login");
		exit;
	}

	if (!isset($_POST["email"]) || $_POST["email"] == "")
	{
		User::setErrorRegister("Preencha o seu email.");
		header("Location: /login");
		exit;
	}

	if (!isset($_POST["password"]) || $_POST["password"] == "")
	{
		User::setErrorRegister("Preencha a sua palavra passe.");
		header("Location: /login");
		exit;
	}

	if (User::checkLoginExist($_POST["email"]) === true)
	{
		User::setErrorRegister("Este endereço de e-mail já está a ser utilizado por outro utilizador.");
		header("Location: /login");
		exit;
	}

	$user = new User();
	$user->setData(array(
		"inadmin" => 0,
		"deslogin" => $_POST["email"],
		"desperson" => $_POST["name"],
		"desemail" => $_POST["email"],
		"despassword" => $_POST["password"],
		"nrphone" => $_POST["phone"]
	));

	$user->saveDB();

	User::login($_POST["email"], $_POST["password"]);

	header("Location: /checkout");
	exit;


});


//Esqueci a senha
$app->get("/forgot", function() {

	$page = new Page();
	$page->setTpl("forgot");
	

});

//envio do email do esqueci a senha
$app->post("/forgot", function(){


	$user = User::getForgot($_POST["email"], false);


	header("Location: /forgot/sent");
	exit;

});

$app->get("/forgot/sent", function(){
	
	$page = new Page();
	$page->setTpl("forgot-sent");
	
});

$app->get("/forgot/reset", function(){
	
	$user = User::validForgotDecrypt($_GET["code"]);
	$page = new Page();

	$page->setTpl("forgot-reset", array(
		"name" => $user["desperson"],
		"code" => $_GET["code"]
	));
	
});

$app->post("/forgot/reset", function(){
	
	$forgot = User::validForgotDecrypt($_POST["code"]);	

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = User::getPasswordHash($_POST["password"]);

	$user->setPassword($password);

	$page = new Page();

	$page->setTpl("forgot-reset-success");
	
});

$app->get("/profile", function(){

	User::verifyLogin(false);
	$user = User::getFromSession();

	$page = new Page();
	$page->setTpl("profile", array(
		"user" => $user->getValues(),
		"profileMsg" => User::getSuccess(),
		"profileError" => User::getError()
	));

});

$app->post("/profile", function(){

	$desperson = $_POST["desperson"];
	$desemail = $_POST["desemail"];

	User::verifyLogin(false);
	
	if (!isset($desperson) || $desperson === "")
	{
		User::setError("Preencha o seu nome.");
		header("Location: /profile");
		exit;
	}

	if (!isset($desemail) || $desemail === "")
	{
		User::setError("Preencha o seu e-mail.");
		header("Location: /profile");
		exit;
	}

	$user = User::getFromSession();

	if ($desemail !== $user->getdesemail())
	{
		if (User::checkLoginExist($desemail) === true)
		{
			User::setError("Este endereço de e-mail já está a ser utilizado.");
			header("Location: /profile");
			exit;
		}
	}

	$_POST["inadmin"] = $user->getinadmin();
	$_POST["despassword"] = $user->getdespassword();
	$_POST["deslogin"] = $desemail;
	
	$user->setData($_POST);
	$user->updateDB();

	User::setSuccess("Dados alterados com sucesso.");

	$_SESSION[User::SESSION] = $user->getValues();

	header("Location: /profile");
	exit;

});

?>