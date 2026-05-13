<?php
require_once __DIR__ . '/../secure/auth.php';
require_once __DIR__ . '/companies/wyatt_company.php';
require_once __DIR__ . '/companies/lucas_company.php';
require_once __DIR__ . '/companies/robbie_company.php';
require_once __DIR__ . '/companies/andrew_company.php';

auth_start_session();

$companies = [
	wyatt_company_get_data(),
	lucas_company_get_data(),
	robbie_company_get_data(),
	andrew_company_get_data(),
];

function escape_html($value) {
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function format_visit_count($value) {
	$count = (int) $value;
	$label = $count === 1 ? 'visit' : 'visits';

	return $count . ' ' . $label;
}

function get_overall_top_products($companies, $limit = 5) {
	$products = [];

	foreach ($companies as $company) {
		if (empty($company['top_products']) || !is_array($company['top_products'])) {
			continue;
		}

		$company_name = trim((string) ($company['company_name'] ?? 'Unknown Company'));

		foreach ($company['top_products'] as $product) {
			if (!is_array($product)) {
				continue;
			}

			$title = trim((string) ($product['title'] ?? ''));
			$product_link = trim((string) ($product['product_link'] ?? ''));

			if ($title === '' || $product_link === '') {
				continue;
			}

			$product['title'] = $title;
			$product['product_link'] = $product_link;
			$product['visit_count'] = is_numeric($product['visit_count'] ?? null) ? (int) $product['visit_count'] : 0;
			$product['company_name'] = $company_name;
			$products[] = $product;
		}
	}

	usort($products, function ($left, $right) {
		$visits = ($right['visit_count'] ?? 0) <=> ($left['visit_count'] ?? 0);

		if ($visits !== 0) {
			return $visits;
		}

		return strcasecmp($left['title'] ?? '', $right['title'] ?? '');
	});

	return array_slice($products, 0, $limit);
}

$overall_top_products = get_overall_top_products($companies);
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Term Project Products</title>
</head>
<body>
	<header>
		<?php if (auth_is_logged_in()): ?>
			<span>Signed in as <?php echo escape_html(auth_current_user_name()); ?></span>
			<a href="/secure/logout.php?redirect=/term_project/">Logout</a>
		<?php else: ?>
			<a href="/secure/login.php?redirect=/term_project/">Login</a>
			<a href="/term_project/create_account.php?redirect=/term_project/">Create Account</a>
		<?php endif; ?>
		<h1>Cross Domain Enterprise Online Market Place</h1>
	</header>

	<main>
		<section>
			<h2>Overall Top 5 Most Visited Products</h2>

			<?php if (empty($overall_top_products)): ?>
				<p>No product visit data available.</p>
			<?php else: ?>
				<ol>
					<?php foreach ($overall_top_products as $product): ?>
						<li>
							<a href="<?php echo escape_html($product['product_link'] ?? '#'); ?>">
								<?php echo escape_html($product['title'] ?? 'Untitled Product'); ?>
							</a>
							<span><?php echo escape_html($product['company_name'] ?? 'Unknown Company'); ?></span>
							<span><?php echo escape_html(format_visit_count($product['visit_count'] ?? 0)); ?></span>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
		</section>

		<?php foreach ($companies as $company): ?>
			<section>
				<h2><?php echo escape_html($company['company_name'] ?? 'Unknown Company'); ?></h2>

				<?php if (empty($company['products'])): ?>
					<p>No products available.</p>
				<?php else: ?>
					<ul>
						<?php foreach ($company['products'] as $product): ?>
							<li>
								<a href="<?php echo escape_html($product['product_link'] ?? '#'); ?>">
									<?php echo escape_html($product['title'] ?? 'Untitled Product'); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if (array_key_exists('top_products', $company)): ?>
					<section>
						<h3>Top 5 Most Visited Products</h3>

						<?php if (empty($company['top_products'])): ?>
							<p>No product visit data available.</p>
						<?php else: ?>
							<ol>
								<?php foreach ($company['top_products'] as $product): ?>
									<li>
										<a href="<?php echo escape_html($product['product_link'] ?? '#'); ?>">
											<?php echo escape_html($product['title'] ?? 'Untitled Product'); ?>
										</a>
										<span><?php echo escape_html(format_visit_count($product['visit_count'] ?? 0)); ?></span>
									</li>
								<?php endforeach; ?>
							</ol>
						<?php endif; ?>
					</section>
				<?php endif; ?>
			</section>
		<?php endforeach; ?>
	</main>
</body>
</html>
