<?php 

use Hcode\Model\User;
use Hcode\PageAdmin;
use Hcode\Model\Category;

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



?>