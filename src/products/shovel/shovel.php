<?php
require __DIR__ . '/../_lib.php';

$shovel = new Product(
	"Shovel",
	"A sturdy shovel for digging and gardening.",
	19.99,
	"./shovel.png"
);

make_product_page($shovel);
