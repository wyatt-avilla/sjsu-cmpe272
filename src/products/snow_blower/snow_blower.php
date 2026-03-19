<?
require '../_lib.php';

$snow_blower = new Product(
	"Snow Blower",
	"Keep your driveway clear and safe with our powerful Snow Blower! This heavy-duty machine is designed to quickly and efficiently remove snow from your property, making winter maintenance a breeze. With its robust engine and wide clearing path, the Snow Blower can handle even the heaviest snowfall, ensuring you can get back to your daily routine without delay. Say goodbye to shoveling and hello to effortless snow removal with our reliable Snow Blower!",
	899.99,
	"./snow_blower.png"
);

make_product_page($snow_blower);
