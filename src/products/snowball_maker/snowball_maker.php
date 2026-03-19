<?
require '../_lib.php';

$snowball_maker = new Product(
	"Snowball Maker",
	"Make perfect snowballs every time with our Snowball Maker! This handy tool allows you to easily create compact and uniform snowballs, making your snowball fights more fun and competitive. Simply fill the mold with snow, press it together, and release to create a perfectly shaped snowball. It's the ultimate accessory for winter fun!",
	5.99,
	"./snowball_maker.png"
);

make_product_page($snowball_maker);
