<?php
require __DIR__ . '/../_lib.php';

$sled = new Product(
	"Sled",
	"Experience the thrill of gliding down snowy hills with our classic sled! Made from durable materials, this sled is designed for both fun and safety. Its sleek design allows for smooth rides, while the sturdy construction ensures it can withstand the winter elements. Whether you're racing down a hill or just enjoying a leisurely ride, our sled is the perfect companion for your winter adventures. Get ready to create unforgettable memories with family and friends on this fantastic sled!",
	19.99,
	"./sled.png"
);

make_product_page($sled);
