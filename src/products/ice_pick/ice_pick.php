<?php
require __DIR__ . '/../_lib.php';

$ice_pick = new Product(
	"Ice Pick",
	"You'll love our Ice Pick! This versatile tool is perfect for breaking up ice, whether you're clearing a path or preparing for winter activities. With its sturdy design and sharp point, the Ice Pick makes it easy to chip away at ice and snow. It's an essential accessory for anyone who wants to stay safe and prepared during the winter months.",
	10.99,
	"./ice_pick.png"
);

make_product_page($ice_pick);
