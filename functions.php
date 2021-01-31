<?php

use Hcode\Model\Cart;
use Hcode\Model\User;

function formatPrice($vlprice)
{
    if ($vlprice != NULL)
    {
        return number_format((float)$vlprice, 2, ",", ".");
    }
}

function formatDate($date)
{
    if ($date != NULL)
    {
        return date("d/m/Y", strtotime($date));
    }
}

function checkLogin($inadmin = true)
{

	return User::checkLogin($inadmin);

}

function getUserName()
{

	$user = User::getFromSession();

	return $user->getdesperson();

}

function getCartNrQtd()
{

    $cart = Cart::getFromSession();
    $totals = $cart->getProductsTotals();

    return $totals["nrqtd"];
}

function getCartVlSubTotal()
{

    $cart = Cart::getFromSession();
    $totals = $cart->getProductsTotals();

    return formatPrice($totals["vlprice"]);
}

?>