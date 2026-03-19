<?php
require __DIR__ . '/../_lib.php';

$snow_shoes = new Product(
	"Snow Shoes",
	"Experience the great outdoors even in deep snow with our Snow Shoes! Designed to distribute your weight evenly, these snow shoes allow you to walk on top of the snow without sinking. Whether you're hiking, snowshoeing, or just exploring winter landscapes, our snow shoes provide comfort and stability. Made with durable materials and adjustable straps, they are perfect for all skill levels. Don't let the snow hold you back – get your pair of snow shoes today and enjoy winter like never before!",
	199.99,
	"./snow_shoes.png"
);

make_product_page($snow_shoes);
