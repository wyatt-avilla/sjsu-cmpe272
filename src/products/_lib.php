<?php
define('RECENT_PRODUCTS_COOKIE', 'recent_products');
define('RECENT_PRODUCTS_LIMIT', 5);

class Product {
	public $name;
	public $description;
	public $price;
	public $image_url;

	public function __construct($name, $description, $price, $image_url) {
		$this->name = $name;
		$this->description = $description;
		$this->price = $price;
		$this->image_url = $image_url;
	}
}

function get_recent_products() {
	if (!isset($_COOKIE[RECENT_PRODUCTS_COOKIE])) {
		return [];
	}

	$recentProducts = json_decode($_COOKIE[RECENT_PRODUCTS_COOKIE], true);

	if (!is_array($recentProducts)) {
		return [];
	}

	$validProducts = [];

	foreach ($recentProducts as $product) {
		if (!is_array($product) || !isset($product['name'], $product['path'])) {
			continue;
		}

		$validProducts[] = [
			'name' => $product['name'],
			'path' => $product['path'],
		];
	}

	return array_slice($validProducts, 0, RECENT_PRODUCTS_LIMIT);
}

function get_current_product_path() {
	if (!isset($_SERVER['SCRIPT_FILENAME'])) {
		return null;
	}

	$productsDirectory = realpath(__DIR__);
	$currentScript = realpath($_SERVER['SCRIPT_FILENAME']);

	if ($productsDirectory === false || $currentScript === false) {
		return null;
	}

	$prefix = $productsDirectory . DIRECTORY_SEPARATOR;

	if (strpos($currentScript, $prefix) !== 0) {
		return null;
	}

	return str_replace(DIRECTORY_SEPARATOR, '/', substr($currentScript, strlen($prefix)));
}

function track_recent_product($product) {
	$productPath = get_current_product_path();

	if ($productPath === null) {
		return;
	}

	$recentProducts = get_recent_products();
	$updatedProducts = [
		[
			'name' => $product->name,
			'path' => $productPath,
		],
	];

	foreach ($recentProducts as $recentProduct) {
		if ($recentProduct['path'] === $productPath) {
			continue;
		}

		$updatedProducts[] = $recentProduct;
	}

	$updatedProducts = array_slice($updatedProducts, 0, RECENT_PRODUCTS_LIMIT);
	$cookieValue = json_encode($updatedProducts);

	if ($cookieValue === false) {
		return;
	}

	setcookie(RECENT_PRODUCTS_COOKIE, $cookieValue, time() + (86400 * 30), '/');
	$_COOKIE[RECENT_PRODUCTS_COOKIE] = $cookieValue;
}

function make_product_page($product) {
	track_recent_product($product);
	?>
	<div class="product">
		<h2><?php echo $product->name; ?></h2>
		<img src="<?php echo $product->image_url; ?>" alt="<?php echo $product->name; ?>">
		<p><?php echo $product->description; ?></p>
		<p>Price: $<?php echo number_format($product->price, 2); ?></p>
	</div>
	<?php
}
?>
