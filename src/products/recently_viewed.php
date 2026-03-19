<?php
require __DIR__ . '/_lib.php';

$recentProducts = get_recent_products();
?>

<html>
<title>Recently Viewed Products</title>
<h1>Recently Viewed Products</h1>
<p><a href="./products.php">Back to Products</a></p>

<?php if (empty($recentProducts)): ?>
<p>No products viewed yet.</p>
<?php else: ?>
<ul>
<?php foreach ($recentProducts as $product): ?>
  <li>
    <a href="<?php echo htmlspecialchars($product['path'], ENT_QUOTES, 'UTF-8'); ?>">
      <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>
    </a>
  </li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
</html>
