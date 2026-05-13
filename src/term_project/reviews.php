<?php
require_once __DIR__ . '/../secure/auth.php';
require_once __DIR__ . '/companies/wyatt_company.php';
require_once __DIR__ . '/companies/lucas_company.php';
require_once __DIR__ . '/companies/robbie_company.php';
require_once __DIR__ . '/companies/andrew_company.php';
require_once __DIR__ . '/review_lib.php';

auth_start_session();

if (!auth_is_logged_in()) {
	$redirect = '/term_project/reviews.php';

	if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/term_project/reviews.php') === 0) {
		$redirect = $_SERVER['REQUEST_URI'];
	}

	header('Location: /secure/login.php?redirect=' . rawurlencode($redirect));
	exit;
}

$companies = [
	wyatt_company_get_data(),
	lucas_company_get_data(),
	robbie_company_get_data(),
	andrew_company_get_data(),
];

$products = term_project_get_reviewable_products($companies);
$errors = [];
$values = [
	'product_key' => $_POST['product_key'] ?? $_GET['product'] ?? '',
	'rating' => $_POST['rating'] ?? '',
	'review_text' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$values['product_key'] = trim((string) ($_POST['product_key'] ?? ''));
	$values['rating'] = trim((string) ($_POST['rating'] ?? ''));
	$values['review_text'] = trim((string) ($_POST['review_text'] ?? ''));

	if ($values['product_key'] === '' || !isset($products[$values['product_key']])) {
		$errors['product_key'] = 'Choose a product.';
	}

	if (!in_array($values['rating'], ['1', '2', '3', '4', '5'], true)) {
		$errors['rating'] = 'Choose a rating from 1 to 5.';
	}

	if ($values['review_text'] === '') {
		$errors['review_text'] = 'This field is required.';
	}

	$user_id = $_SESSION['user_id'] ?? null;

	if (!is_numeric($user_id)) {
		$errors['form'] = 'Please sign in again before adding a review.';
	}

	if (!$errors) {
		try {
			term_project_insert_product_review(
				(int) $user_id,
				$products[$values['product_key']],
				(int) $values['rating'],
				$values['review_text']
			);

			header('Location: reviews.php?product=' . rawurlencode($values['product_key']) . '&created=1');
			exit;
		} catch (Throwable $e) {
			$errors['form'] = 'Unable to save the review right now.';
		}
	}
} else {
	$values['product_key'] = trim((string) $values['product_key']);
}

if ($values['product_key'] === '' || !isset($products[$values['product_key']])) {
	$values['product_key'] = array_key_first($products) ?: '';
}

function h($value) {
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Product Reviews</title>
	<link rel="stylesheet" href="/term_project/styles.css">
</head>
<body class="tp-page">
	<header class="tp-header">
		<div class="tp-shell tp-header-inner">
			<h1 class="tp-brand">Product Reviews</h1>
			<nav class="tp-nav" aria-label="Review navigation">
				<a href="/term_project/">Marketplace</a>
				<span>Signed in as <?php echo h(auth_current_user_name()); ?></span>
				<a href="/secure/logout.php?redirect=/term_project/">Logout</a>
			</nav>
		</div>
	</header>

	<main class="tp-main">
		<div class="tp-shell">
			<?php if (isset($_GET['created'])): ?>
				<p class="tp-message tp-message-success">Review added successfully.</p>
			<?php endif; ?>

			<?php if ($errors): ?>
				<p class="tp-message tp-message-error"><?php echo h($errors['form'] ?? 'Please fix the errors below.'); ?></p>
			<?php endif; ?>

			<?php if (empty($products)): ?>
				<p class="tp-empty">No products are available to review.</p>
			<?php else: ?>
				<form class="tp-form tp-panel" method="post" action="reviews.php">
					<div class="tp-field">
						<label for="product_key">Product</label>
						<select id="product_key" name="product_key" required>
							<?php foreach ($products as $key => $product): ?>
								<option value="<?php echo h($key); ?>" <?php echo $key === $values['product_key'] ? 'selected' : ''; ?>>
									<?php echo h($product['company_name'] . ' - ' . $product['product_name']); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php if (isset($errors['product_key'])): ?><span class="tp-field-error"><?php echo h($errors['product_key']); ?></span><?php endif; ?>
					</div>

					<div class="tp-field">
						<label for="rating">Rating</label>
						<select id="rating" name="rating" required>
							<option value="">Choose a rating</option>
							<?php for ($rating = 1; $rating <= 5; $rating++): ?>
								<option value="<?php echo $rating; ?>" <?php echo (string) $rating === $values['rating'] ? 'selected' : ''; ?>>
									<?php echo $rating; ?> star<?php echo $rating === 1 ? '' : 's'; ?>
								</option>
							<?php endfor; ?>
						</select>
						<?php if (isset($errors['rating'])): ?><span class="tp-field-error"><?php echo h($errors['rating']); ?></span><?php endif; ?>
					</div>

					<div class="tp-field">
						<label for="review_text">Review</label>
						<textarea id="review_text" name="review_text" required><?php echo h($values['review_text']); ?></textarea>
						<?php if (isset($errors['review_text'])): ?><span class="tp-field-error"><?php echo h($errors['review_text']); ?></span><?php endif; ?>
					</div>

					<div>
						<button class="tp-submit" type="submit">Add Review</button>
					</div>
				</form>

			<?php endif; ?>
		</div>
	</main>
</body>
</html>
