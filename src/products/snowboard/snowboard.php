<?
require '../_lib.php';

$snowboard= new Product(
	"Snowboard",
	"A high-quality snowboard for all your winter adventures.",
	399.99,
	"./snowboard.png"
);

make_product_page($snowboard);
