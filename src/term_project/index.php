<?php
require_once __DIR__ . '/../secure/auth.php';
require_once __DIR__ . '/companies/wyatt_company.php';
require_once __DIR__ . '/companies/lucas_company.php';
require_once __DIR__ . '/companies/robbie_company.php';
require_once __DIR__ . '/companies/andrew_company.php';
require_once __DIR__ . '/review_lib.php';

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

function term_project_company_slug($company_name) {
	$slug = strtolower(trim((string) $company_name));
	$slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
	$slug = trim($slug, '-');

	return $slug === '' ? 'company' : $slug;
}

function term_project_short_description($value, $limit = 150) {
	$description = trim(preg_replace('/\s+/', ' ', (string) $value));

	if ($description === '' || strlen($description) <= $limit) {
		return $description;
	}

	return rtrim(substr($description, 0, $limit - 3)) . '...';
}

function term_project_format_price($value) {
	if (!is_numeric($value)) {
		return '';
	}

	return '$' . number_format((float) $value, 2);
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

function get_marketplace_product_cards($companies, $selected_company_slug, $average_ratings) {
	$products = [];

	foreach ($companies as $company) {
		$company_name = trim((string) ($company['company_name'] ?? 'Unknown Company'));
		$company_slug = (string) ($company['company_slug'] ?? term_project_company_slug($company_name));

		if ($selected_company_slug !== '' && $selected_company_slug !== $company_slug) {
			continue;
		}

		if (empty($company['products']) || !is_array($company['products'])) {
			continue;
		}

		foreach ($company['products'] as $product) {
			if (!is_array($product)) {
				continue;
			}

			$title = trim((string) ($product['title'] ?? ''));
			$product_link = trim((string) ($product['product_link'] ?? ''));

			if ($title === '' || $product_link === '') {
				continue;
			}

			$track_url = '/term_project/track.php?' . http_build_query([
				'company' => $company_name,
				'product' => $title,
				'link' => $product_link,
			]);

			$products[] = [
				'title' => $title,
				'company_name' => $company_name,
				'company_slug' => $company_slug,
				'description' => term_project_short_description($product['description'] ?? ''),
				'price' => term_project_format_price($product['price'] ?? null),
				'image_link' => trim((string) ($product['image_link'] ?? '')),
				'track_url' => $track_url,
				'average_rating' => term_project_get_average_rating_for_product($company_name, $product_link, $average_ratings),
			];
		}
	}

	usort($products, function ($left, $right) {
		$company = strcasecmp($left['company_name'], $right['company_name']);

		if ($company !== 0) {
			return $company;
		}

		return strcasecmp($left['title'], $right['title']);
	});

	return $products;
}

function get_selected_company_slug($company_filters) {
	$requested_company = $_GET['company'] ?? '';
	$selected_company = is_array($requested_company) ? '' : trim((string) $requested_company);

	if ($selected_company === '' || !isset($company_filters[$selected_company])) {
		return '';
	}

	return $selected_company;
}

$company_filters = [];

foreach ($companies as $index => $company) {
	$company_name = trim((string) ($company['company_name'] ?? 'Unknown Company'));
	$company_slug = term_project_company_slug($company_name);

	if (isset($company_filters[$company_slug])) {
		$company_slug .= '-' . ($index + 1);
	}

	$companies[$index]['company_slug'] = $company_slug;
	$company_filters[$company_slug] = $company_name;
}

$overall_top_products = get_overall_top_products($companies);
$average_ratings = term_project_get_product_average_ratings();
$selected_company_slug = get_selected_company_slug($company_filters);
$marketplace_products = get_marketplace_product_cards($companies, $selected_company_slug, $average_ratings);
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Term Project Products</title>
	<link rel="stylesheet" href="/term_project/styles.css">
</head>
<body class="tp-page">
	<header class="tp-header">
		<div class="tp-shell tp-header-inner">
			<h1 class="tp-brand">Cross Domain Enterprise Online Market Place</h1>

			<nav class="tp-nav" aria-label="Marketplace navigation">
				<?php if (auth_is_logged_in()): ?>
					<span>Signed in as <?php echo escape_html(auth_current_user_name()); ?></span>
					<a href="/term_project/tracking.php">Tracking Stats</a>
					<a href="/term_project/reviews.php">Add Review</a>
					<a href="/secure/logout.php?redirect=/term_project/">Logout</a>
				<?php else: ?>
					<a href="/term_project/tracking.php">Tracking Stats</a>
					<a href="/secure/login.php?redirect=/term_project/">Login</a>
					<a href="/term_project/create_account.php?redirect=/term_project/">Create Account</a>
					<a href="/term_project/reviews.php">Add Review</a>
				<?php endif; ?>
			</nav>
		</div>
	</header>

	<main class="tp-main">
		<div class="tp-shell">
			<section class="tp-section">
				<h2 class="tp-section-title">Overall Top 5 Most Visited Products</h2>

				<?php if (empty($overall_top_products)): ?>
					<p class="tp-empty">No product visit data available.</p>
				<?php else: ?>
					<ol class="tp-rank-list">
						<?php foreach ($overall_top_products as $product): ?>
							<?php $average_rating = term_project_get_average_rating_for_product($product['company_name'] ?? '', $product['product_link'] ?? '', $average_ratings); ?>
							<li class="tp-rank-item">
								<div>
									<a class="tp-rank-title" href="<?php echo escape_html($product['product_link'] ?? '#'); ?>">
										<?php echo escape_html($product['title'] ?? 'Untitled Product'); ?>
									</a>
									<div class="tp-meta">
										<span><?php echo escape_html($product['company_name'] ?? 'Unknown Company'); ?></span>
										<span><?php echo escape_html(format_visit_count($product['visit_count'] ?? 0)); ?></span>
										<span><?php echo term_project_format_average_rating($average_rating); ?></span>
									</div>
								</div>
							</li>
						<?php endforeach; ?>
					</ol>
				<?php endif; ?>
			</section>

			<section class="tp-section" aria-labelledby="marketplace-products-title">
				<h2 id="marketplace-products-title" class="tp-section-title">Products</h2>

				<nav class="tp-filter-bar" aria-label="Filter products by company">
					<a class="tp-filter-link <?php echo $selected_company_slug === '' ? 'is-active' : ''; ?>" href="/term_project/">All</a>
					<?php foreach ($company_filters as $company_slug => $company_name): ?>
						<a class="tp-filter-link <?php echo $selected_company_slug === $company_slug ? 'is-active' : ''; ?>" href="/term_project/?company=<?php echo escape_html($company_slug); ?>">
							<?php echo escape_html($company_name); ?>
						</a>
					<?php endforeach; ?>
				</nav>

				<?php if (empty($marketplace_products)): ?>
					<p class="tp-empty">No products are available for this company.</p>
				<?php else: ?>
					<div class="tp-product-grid">
						<?php foreach ($marketplace_products as $product): ?>
							<article class="tp-card">
								<div class="tp-card-media">
									<?php if ($product['image_link'] !== ''): ?>
										<img src="<?php echo escape_html($product['image_link']); ?>" alt="<?php echo escape_html($product['title']); ?>">
									<?php else: ?>
										<span class="tp-muted">No image</span>
									<?php endif; ?>
								</div>

								<div class="tp-card-body">
									<span class="tp-company"><?php echo escape_html($product['company_name']); ?></span>
									<h3 class="tp-card-title"><?php echo escape_html($product['title']); ?></h3>

									<?php if ($product['price'] !== ''): ?>
										<div class="tp-price"><?php echo escape_html($product['price']); ?></div>
									<?php endif; ?>

									<?php if ($product['description'] !== ''): ?>
										<p class="tp-card-description"><?php echo escape_html($product['description']); ?></p>
									<?php endif; ?>

									<div class="tp-meta">
										<span><?php echo term_project_format_average_rating($product['average_rating']); ?></span>
									</div>

									<div class="tp-card-actions">
										<a class="tp-button tp-button-primary" href="<?php echo escape_html($product['track_url']); ?>">View product</a>
									</div>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>
		</div>
	</main>
</body>
</html>
