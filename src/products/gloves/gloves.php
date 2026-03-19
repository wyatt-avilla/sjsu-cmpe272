<?
require '../_lib.php';

$gloves = new Product(
	"Gloves",
	"Stay warm and stylish with our high-quality gloves! Crafted from premium materials, these gloves provide excellent insulation to keep your hands cozy in cold weather. The sleek design ensures a comfortable fit while allowing for easy movement. Whether you're heading out for a winter walk, commuting to work, or enjoying outdoor activities, our gloves are the perfect accessory to keep your hands protected from the elements. Don't let the cold stop you – grab a pair of our gloves and embrace the winter season in style!",
	25.99,
	"./gloves.png"
);

make_product_page($gloves);
