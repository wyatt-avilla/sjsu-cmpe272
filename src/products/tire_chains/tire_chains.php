<?php
require __DIR__ . '/../_lib.php';

$tire_chains = new Product(
	"Tire Chains",
	"Ensure your safety on snowy and icy roads with our durable tire chains! These chains provide enhanced traction and grip, allowing you to navigate through challenging winter conditions with confidence. Made from high-quality materials, they are designed to withstand harsh weather and provide reliable performance. Easy to install and remove, our tire chains are a must-have for any driver during the winter season. Don't let the snow slow you down – equip your vehicle with our tire chains today!",
	299.99,
	"./tire_chains.png"
);

make_product_page($tire_chains);
