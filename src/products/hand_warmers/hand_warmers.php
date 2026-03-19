<?
require '../_lib.php';

$hand_warmers = new Product(
	"Hand Warmers",
	"Keep your hands warm and cozy with our Hand Warmers! These compact and portable heat packs are perfect for chilly days. Simply activate them by shaking or squeezing, and they will provide hours of soothing warmth. Whether you're outdoor adventuring, attending sporting events, or just need a little extra warmth during the winter months, our Hand Warmers are the ideal solution to keep your hands comfortable and toasty.",
	2.99,
	"./hand_warmers.png"
);

make_product_page($hand_warmers);
