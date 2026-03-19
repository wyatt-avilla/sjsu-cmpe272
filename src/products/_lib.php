<?php
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

function make_product_page($product) {
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
