<?php
function extractProductName($productPage) {
    $contents = file_get_contents($productPage);

    if ($contents === false) {
        return null;
    }

    if (preg_match('/new\s+Product\s*\(\s*([\'"])(.*?)\1/s', $contents, $matches)) {
        return $matches[2];
    }

    return null;
}

$productPages = glob(__DIR__ . '/*/*.php');
$products = [];

foreach ($productPages as $productPage) {
    $relativePath = str_replace(__DIR__ . '/', '', $productPage);
    $productName = extractProductName($productPage);

    if ($productName === null) {
        continue;
    }

    $products[] = [
        'name' => $productName,
        'path' => $relativePath,
    ];
}

usort($products, function ($left, $right) {
    return strcmp($left['name'], $right['name']);
});
?>

<html>
<title>Products</title>
<h1>Products</h1>
<ul>
<?php foreach ($products as $product): ?>
  <li>
    <a href="<?php echo htmlspecialchars($product['path'], ENT_QUOTES, 'UTF-8'); ?>">
      <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>
    </a>
  </li>
<?php endforeach; ?>
</ul>
</html>
