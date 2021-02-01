<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include dirname(__FILE__) . '/../db/connection.php';

$token = NULL;
$success = FALSE;
$message = NULL;

$login = 'admin';
$email = $_POST['email'];
$password = $_POST['password'];
$role = 1;
$active = 1;

if (!empty($email) && !empty($password)) {
	
	$password = sha1($password);
	
	$db_connection = connect();

	$queries = array(
		'DROP TABLE IF EXISTS `images`;',
		'DROP TABLE IF EXISTS `parts`;',
		'DROP TABLE IF EXISTS `sections`;',
		'DROP TABLE IF EXISTS `menu`;',
		'DROP TABLE IF EXISTS `users`;',
		'DROP TABLE IF EXISTS `roles`;',
	);

	foreach ($queries as $query) {
		$statement = $db_connection->prepare($query);
		$statement->execute();
	}

	$queries = array(
		"
			CREATE TABLE `images` (
			  `id` int(10) UNSIGNED NOT NULL,
			  `filename` varchar(100) CHARACTER SET utf8 NOT NULL,
			  `type` varchar(20) CHARACTER SET utf8 NOT NULL,
			  `size` int(10) UNSIGNED NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		",
		"
			CREATE TABLE `menu` (
			  `id` int(10) UNSIGNED NOT NULL,
			  `caption` varchar(100) CHARACTER SET utf8 NOT NULL,
			  `sequence` int(10) UNSIGNED NOT NULL,
			  `active` tinyint(4) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		",
		"
			CREATE TABLE `parts` (
			  `id` int(10) UNSIGNED NOT NULL,
			  `name` varchar(100) CHARACTER SET utf8 NOT NULL,
			  `content` longtext CHARACTER SET utf8 NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		",
		"
			CREATE TABLE `roles` (
			  `id` int(10) UNSIGNED NOT NULL,
			  `name` varchar(16) NOT NULL,
			  `mask_a` tinyint(1) NOT NULL,
			  `mask_o` tinyint(1) NOT NULL,
			  `mask_u` tinyint(1) NOT NULL,
			  `mask_g` tinyint(1) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		",
		"
			CREATE TABLE `sections` (
			  `id` int(10) UNSIGNED NOT NULL,
			  `menu_id` int(10) UNSIGNED NOT NULL,
			  `content` longtext CHARACTER SET utf8 NOT NULL,
			  `sequence` int(10) UNSIGNED NOT NULL,
			  `active` tinyint(4) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		",
		"
			CREATE TABLE `users` (
			  `id` int(11) UNSIGNED NOT NULL,
			  `login` varchar(100) NOT NULL,
			  `password` varchar(100) NOT NULL,
			  `email` varchar(128) NOT NULL,
			  `role` int(10) UNSIGNED NOT NULL,
			  `active` tinyint(1) NOT NULL,
			  `registered` datetime NOT NULL,
			  `logged_in` datetime NOT NULL,
			  `logged_out` datetime NOT NULL,
			  `token` varchar(255) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		",
	);

	foreach ($queries as $query) {
		$statement = $db_connection->prepare($query);
		$statement->execute();
	}

	$queries = array(
		"
			ALTER TABLE `images`
			  ADD PRIMARY KEY (`id`),
			  ADD UNIQUE KEY `filename` (`filename`);
		",
		"
			ALTER TABLE `menu`
			  ADD PRIMARY KEY (`id`);
		",
		"
			ALTER TABLE `parts`
			  ADD PRIMARY KEY (`id`),
			  ADD UNIQUE KEY `name` (`name`);
		",
		"
			ALTER TABLE `roles`
			  ADD PRIMARY KEY (`id`);
		",
		"
			ALTER TABLE `sections`
			  ADD PRIMARY KEY (`id`),
			  ADD KEY `fk_section_menu` (`menu_id`);
		",
		"
			ALTER TABLE `users`
			  ADD PRIMARY KEY (`id`),
			  ADD UNIQUE KEY `login` (`login`),
			  ADD UNIQUE KEY `email` (`email`),
			  ADD KEY `fk_users_roles` (`role`);
		",
	);

	foreach ($queries as $query) {
		$statement = $db_connection->prepare($query);
		$statement->execute();
	}

	$queries = array(
		"
			ALTER TABLE `images`
			  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
		",
		"
			ALTER TABLE `menu`
			  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
		",
		"
			ALTER TABLE `parts`
			  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
		",
		"
			ALTER TABLE `roles`
			  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
		",
		"
			ALTER TABLE `sections`
			  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
		",
		"
			ALTER TABLE `users`
			  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
		",
	);

	foreach ($queries as $query) {
		$statement = $db_connection->prepare($query);
		$statement->execute();
	}

	$queries = array(
		"
			ALTER TABLE `sections`
			  ADD CONSTRAINT `fk_section_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`);
		",
		"
			ALTER TABLE `users`
			  ADD CONSTRAINT `fk_users_roles` FOREIGN KEY (`role`) REFERENCES `roles` (`id`);
		",
	);

	foreach ($queries as $query) {
		$statement = $db_connection->prepare($query);
		$statement->execute();
	}

	$queries = array(
		"
			INSERT INTO `menu` (`id`, `caption`, `sequence`, `active`) VALUES
			(1, '(empty)', 0, 0),
			(2, 'Services', 1, 1),
			(3, 'Portfolio', 2, 0),
			(4, 'About', 3, 1),
			(5, 'Team', 4, 1),
			(6, 'Contact', 5, 1);
		",
		"
			INSERT INTO `parts` (`id`, `name`, `content`) VALUES
			(1, 'header', '<div class=\"container\">\r\n<div class=\"masthead-subheading\">Welcome To Our Studio!</div>\r\n<div class=\"masthead-heading text-uppercase\">It\'s Nice To Meet You</div>\r\n<a class=\"btn btn-primary btn-xl text-uppercase js-scroll-trigger\" href=\"/admin\">Admin Panel</a>\r\n</div>\r\n'),
			(2, 'footer', '<div class=\"container\">\r\n<div class=\"row align-items-center\">\r\n<div class=\"col-lg-4 text-lg-left\">Copyright © MicroCMS 2021</div>\r\n<div class=\"col-lg-4 my-3 my-lg-0\">\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-twitter\"></i></a>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-facebook-f\"></i></a>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-linkedin-in\"></i></a>\r\n</div>\r\n<div class=\"col-lg-4 text-lg-right\">\r\n<a class=\"mr-3\" href=\"#!\">Privacy Policy</a>\r\n<a href=\"#!\">Terms of Use</a>\r\n</div>\r\n</div>\r\n</div>\r\n'),
			(3, 'title', 'Super Slim CMS'),
			(4, 'logo', '<span style=\"color: #c00; font-size: 1.25em;\">micro</span><span style=\"color: #0c0; font-size: 1.75em; font-weight: bold;\">CMS</span>'),
			(5, 'description', 'Native CMS - super slim and fast Micro CMS based on free templates and jQuery scrips. Admin Panel included.'),
			(6, 'author', 'Andrzej Żukowski'),
			(7, 'script', 'setTimeout(function() {\n$(\'h3\').css({ color: \'#090\', \'text-decoration\': \'underline\' });\n}, 200);'),
			(8, 'style', 'body {\nmargin: inherit;\npadding: inherit;\nbackground: inherit;\n}');
		",
		"
			INSERT INTO `roles` (`id`, `name`, `mask_a`, `mask_o`, `mask_u`, `mask_g`) VALUES
			(1, 'admin', 1, 0, 0, 0),
			(2, 'operator', 0, 1, 0, 0),
			(3, 'user', 0, 0, 1, 0),
			(4, 'guest', 0, 0, 0, 1);
		",
		"
			INSERT INTO `sections` (`id`, `menu_id`, `content`, `sequence`, `active`) VALUES
			(1, 2, '<div class=\"container\">\n<div class=\"text-center\">\n<h2 class=\"section-heading text-uppercase\">Services</h2>\n<h3 class=\"section-subheading text-muted\">Lorem ipsum dolor sit amet consectetur.</h3>\n</div>\n<div class=\"row text-center\">\n<div class=\"col-md-4\">\n<span class=\"fa-stack fa-4x\">\n<i class=\"fas fa-circle fa-stack-2x text-primary\"></i>\n<i class=\"fas fa-shopping-cart fa-stack-1x fa-inverse\"></i>\n</span>\n<h4 class=\"my-3\">E-Commerce</h4>\n<p class=\"text-muted\">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Minima maxime quam architecto quo inventore harum ex magni, dicta impedit.</p>\n</div>\n<div class=\"col-md-4\">\n<span class=\"fa-stack fa-4x\">\n<i class=\"fas fa-circle fa-stack-2x text-primary\"></i>\n<i class=\"fas fa-laptop fa-stack-1x fa-inverse\"></i>\n</span>\n<h4 class=\"my-3\">Responsive Design</h4>\n<p class=\"text-muted\">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Minima maxime quam architecto quo inventore harum ex magni, dicta impedit.</p>\n</div>\n<div class=\"col-md-4\">\n<span class=\"fa-stack fa-4x\">\n<i class=\"fas fa-circle fa-stack-2x text-primary\"></i>\n<i class=\"fas fa-lock fa-stack-1x fa-inverse\"></i>\n</span>\n<h4 class=\"my-3\">Web Security</h4>\n<p class=\"text-muted\">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Minima maxime quam architecto quo inventore harum ex magni, dicta impedit.</p>\n</div>\n</div>\n</div>\n', 1, 1),
			(2, 3, '<div class=\"container\">\n<div class=\"text-center\">\n<h2 class=\"section-heading text-uppercase\">Portfolio</h2>\n<h3 class=\"section-subheading text-muted\">Lorem ipsum dolor sit amet consectetur.</h3>\n</div>\n<div class=\"row\">\n<div class=\"col-lg-4 col-sm-6 mb-4\">\n<div class=\"portfolio-item\">\n<a class=\"portfolio-link\" data-toggle=\"modal\" href=\"#portfolioModal1\">\n<div class=\"portfolio-hover\">\n<div class=\"portfolio-hover-content\"><i class=\"fas fa-plus fa-3x\"></i></div>\n</div>\n<img class=\"img-fluid\" src=\"assets/img/portfolio/01-thumbnail.jpg\" alt=\"\" />\n</a>\n<div class=\"portfolio-caption\">\n<div class=\"portfolio-caption-heading\">Threads</div>\n<div class=\"portfolio-caption-subheading text-muted\">Illustration</div>\n</div>\n</div>\n</div>\n<div class=\"col-lg-4 col-sm-6 mb-4\">\n<div class=\"portfolio-item\">\n<a class=\"portfolio-link\" data-toggle=\"modal\" href=\"#portfolioModal2\">\n<div class=\"portfolio-hover\">\n<div class=\"portfolio-hover-content\"><i class=\"fas fa-plus fa-3x\"></i></div>\n</div>\n<img class=\"img-fluid\" src=\"assets/img/portfolio/02-thumbnail.jpg\" alt=\"\" />\n</a>\n<div class=\"portfolio-caption\">\n<div class=\"portfolio-caption-heading\">Explore</div>\n<div class=\"portfolio-caption-subheading text-muted\">Graphic Design</div>\n</div>\n</div>\n</div>\n<div class=\"col-lg-4 col-sm-6 mb-4\">\n<div class=\"portfolio-item\">\n<a class=\"portfolio-link\" data-toggle=\"modal\" href=\"#portfolioModal3\">\n<div class=\"portfolio-hover\">\n<div class=\"portfolio-hover-content\"><i class=\"fas fa-plus fa-3x\"></i></div>\n</div>\n<img class=\"img-fluid\" src=\"assets/img/portfolio/03-thumbnail.jpg\" alt=\"\" />\n</a>\n<div class=\"portfolio-caption\">\n<div class=\"portfolio-caption-heading\">Finish</div>\n<div class=\"portfolio-caption-subheading text-muted\">Identity</div>\n</div>\n</div>\n</div>\n<div class=\"col-lg-4 col-sm-6 mb-4 mb-lg-0\">\n<div class=\"portfolio-item\">\n<a class=\"portfolio-link\" data-toggle=\"modal\" href=\"#portfolioModal4\">\n<div class=\"portfolio-hover\">\n<div class=\"portfolio-hover-content\"><i class=\"fas fa-plus fa-3x\"></i></div>\n</div>\n<img class=\"img-fluid\" src=\"assets/img/portfolio/04-thumbnail.jpg\" alt=\"\" />\n</a>\n<div class=\"portfolio-caption\">\n<div class=\"portfolio-caption-heading\">Lines</div>\n<div class=\"portfolio-caption-subheading text-muted\">Branding</div>\n</div>\n</div>\n</div>\n<div class=\"col-lg-4 col-sm-6 mb-4 mb-sm-0\">\n<div class=\"portfolio-item\">\n<a class=\"portfolio-link\" data-toggle=\"modal\" href=\"#portfolioModal5\">\n<div class=\"portfolio-hover\">\n<div class=\"portfolio-hover-content\"><i class=\"fas fa-plus fa-3x\"></i></div>\n</div>\n<img class=\"img-fluid\" src=\"assets/img/portfolio/05-thumbnail.jpg\" alt=\"\" />\n</a>\n<div class=\"portfolio-caption\">\n<div class=\"portfolio-caption-heading\">Southwest</div>\n<div class=\"portfolio-caption-subheading text-muted\">Website Design</div>\n</div>\n</div>\n</div>\n<div class=\"col-lg-4 col-sm-6\">\n<div class=\"portfolio-item\">\n<a class=\"portfolio-link\" data-toggle=\"modal\" href=\"#portfolioModal6\">\n<div class=\"portfolio-hover\">\n<div class=\"portfolio-hover-content\"><i class=\"fas fa-plus fa-3x\"></i></div>\n</div>\n<img class=\"img-fluid\" src=\"assets/img/portfolio/06-thumbnail.jpg\" alt=\"\" />\n</a>\n<div class=\"portfolio-caption\">\n<div class=\"portfolio-caption-heading\">Window</div>\n<div class=\"portfolio-caption-subheading text-muted\">Photography</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n', 2, 1),
			(3, 4, '<div class=\"container\">\r\n<div class=\"text-center\">\r\n<h2 class=\"section-heading text-uppercase\">About</h2>\r\n<h3 class=\"section-subheading text-muted\">Lorem ipsum dolor sit amet consectetur.</h3>\r\n</div>\r\n<ul class=\"timeline\">\r\n<li>\r\n<div class=\"timeline-image\"><img class=\"rounded-circle img-fluid\" src=\"assets/img/about/1.jpg\" alt=\"\" /></div>\r\n<div class=\"timeline-panel\">\r\n<div class=\"timeline-heading\">\r\n<h4>2009-2011</h4>\r\n<h4 class=\"subheading\">Our Humble Beginnings</h4>\r\n</div>\r\n<div class=\"timeline-body\"><p class=\"text-muted\">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sunt ut voluptatum eius sapiente, totam reiciendis temporibus qui quibusdam, recusandae sit vero unde, sed, incidunt et ea quo dolore laudantium consectetur!</p></div>\r\n</div>\r\n</li>\r\n<li class=\"timeline-inverted\">\r\n<div class=\"timeline-image\"><img class=\"rounded-circle img-fluid\" src=\"assets/img/about/2.jpg\" alt=\"\" /></div>\r\n<div class=\"timeline-panel\">\r\n<div class=\"timeline-heading\">\r\n<h4>March 2011</h4>\r\n<h4 class=\"subheading\">An Agency is Born</h4>\r\n</div>\r\n<div class=\"timeline-body\"><p class=\"text-muted\">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sunt ut voluptatum eius sapiente, totam reiciendis temporibus qui quibusdam, recusandae sit vero unde, sed, incidunt et ea quo dolore laudantium consectetur!</p></div>\r\n</div>\r\n</li>\r\n<li>\r\n<div class=\"timeline-image\"><img class=\"rounded-circle img-fluid\" src=\"assets/img/about/3.jpg\" alt=\"\" /></div>\r\n<div class=\"timeline-panel\">\r\n<div class=\"timeline-heading\">\r\n<h4>December 2012</h4>\r\n<h4 class=\"subheading\">Transition to Full Service</h4>\r\n</div>\r\n<div class=\"timeline-body\"><p class=\"text-muted\">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sunt ut voluptatum eius sapiente, totam reiciendis temporibus qui quibusdam, recusandae sit vero unde, sed, incidunt et ea quo dolore laudantium consectetur!</p></div>\r\n</div>\r\n</li>\r\n<li class=\"timeline-inverted\">\r\n<div class=\"timeline-image\"><img class=\"rounded-circle img-fluid\" src=\"assets/img/about/4.jpg\" alt=\"\" /></div>\r\n<div class=\"timeline-panel\">\r\n<div class=\"timeline-heading\">\r\n<h4>July 2014</h4>\r\n<h4 class=\"subheading\">Phase Two Expansion</h4>\r\n</div>\r\n<div class=\"timeline-body\"><p class=\"text-muted\">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sunt ut voluptatum eius sapiente, totam reiciendis temporibus qui quibusdam, recusandae sit vero unde, sed, incidunt et ea quo dolore laudantium consectetur!</p></div>\r\n</div>\r\n</li>\r\n<li class=\"timeline-inverted\">\r\n<div class=\"timeline-image\">\r\n<h4>\r\nBe Part\r\n<br />\r\nOf Our\r\n<br />\r\nStory!\r\n</h4>\r\n</div>\r\n</li>\r\n</ul>\r\n</div>\r\n', 3, 1),
			(4, 5, '<div class=\"container\">\r\n<div class=\"text-center\">\r\n<h2 class=\"section-heading text-uppercase\">Our Amazing Team</h2>\r\n<h3 class=\"section-subheading text-muted\">Lorem ipsum dolor sit amet consectetur.</h3>\r\n</div>\r\n<div class=\"row\">\r\n<div class=\"col-lg-4\">\r\n<div class=\"team-member\">\r\n<img class=\"mx-auto rounded-circle\" src=\"assets/img/team/1.jpg\" alt=\"\" />\r\n<h4>Kay Garland</h4>\r\n<p class=\"text-muted\">Lead Designer</p>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-twitter\"></i></a>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-facebook-f\"></i></a>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-linkedin-in\"></i></a>\r\n</div>\r\n</div>\r\n<div class=\"col-lg-4\">\r\n<div class=\"team-member\">\r\n<img class=\"mx-auto rounded-circle\" src=\"assets/img/team/2.jpg\" alt=\"\" />\r\n<h4>Larry Parker</h4>\r\n<p class=\"text-muted\">Lead Marketer</p>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-twitter\"></i></a>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-facebook-f\"></i></a>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-linkedin-in\"></i></a>\r\n</div>\r\n</div>\r\n<div class=\"col-lg-4\">\r\n<div class=\"team-member\">\r\n<img class=\"mx-auto rounded-circle\" src=\"assets/img/team/3.jpg\" alt=\"\" />\r\n<h4>Diana Petersen</h4>\r\n<p class=\"text-muted\">Lead Developer</p>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-twitter\"></i></a>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-facebook-f\"></i></a>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-linkedin-in\"></i></a>\r\n</div>\r\n</div>\r\n</div>\r\n<div class=\"row\">\r\n<div class=\"col-lg-8 mx-auto text-center\"><p class=\"large text-muted\">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut eaque, laboriosam veritatis, quos non quis ad perspiciatis, totam corporis ea, alias ut unde.</p></div>\r\n</div>\r\n</div>\r\n', 4, 1),
			(5, 6, '<div class=\"container\">\n<div class=\"text-center\">\n<h2 class=\"section-heading text-uppercase\">Contact</h2>\n<h3 class=\"section-subheading text-muted\">Lorem ipsum dolor sit amet consectetur.</h3>\n</div>\n<form id=\"contactForm\" name=\"sentMessage\" novalidate=\"novalidate\">\n<div class=\"row align-items-stretch mb-5\">\n<div class=\"col-md-6\">\n<div class=\"form-group\">\n<input class=\"form-control\" id=\"name\" type=\"text\" placeholder=\"Your Name *\" required=\"required\" data-validation-required-message=\"Please enter your name.\" />\n<p class=\"help-block text-danger\"></p>\n</div>\n<div class=\"form-group\">\n<input class=\"form-control\" id=\"email\" type=\"email\" placeholder=\"Your Email *\" required=\"required\" data-validation-required-message=\"Please enter your email address.\" />\n<p class=\"help-block text-danger\"></p>\n</div>\n<div class=\"form-group mb-md-0\">\n<input class=\"form-control\" id=\"phone\" type=\"tel\" placeholder=\"Your Phone *\" required=\"required\" data-validation-required-message=\"Please enter your phone number.\" />\n<p class=\"help-block text-danger\"></p>\n</div>\n</div>\n<div class=\"col-md-6\">\n<div class=\"form-group form-group-textarea mb-md-0\">\n<textarea class=\"form-control\" id=\"message\" rows=\"6\" placeholder=\"Your Message *\" required=\"required\" data-validation-required-message=\"Please enter a message.\"></textarea>\n<p class=\"help-block text-danger\"></p>\n</div>\n</div>\n</div>\n<div class=\"text-center\">\n<div id=\"success\"></div>\n<button class=\"btn btn-primary btn-xl text-uppercase\" id=\"sendMessageButton\" type=\"submit\">Send Message</button>\n</div>\n</form>\n</div>\n', 5, 1),
			(6, 1, '<div class=\"py-1\">\n<div class=\"container\">\n<div class=\"row\">\n<div class=\"col-md-3 col-sm-6 my-3\">\n<a href=\"#!\"><img class=\"img-fluid d-block mx-auto\" src=\"assets/img/logos/envato.jpg\" alt=\"\" /></a>\n</div>\n<div class=\"col-md-3 col-sm-6 my-3\">\n<a href=\"#!\"><img class=\"img-fluid d-block mx-auto\" src=\"assets/img/logos/designmodo.jpg\" alt=\"\" /></a>\n</div>\n<div class=\"col-md-3 col-sm-6 my-3\">\n<a href=\"#!\"><img class=\"img-fluid d-block mx-auto\" src=\"assets/img/logos/themeforest.jpg\" alt=\"\" /></a>\n</div>\n<div class=\"col-md-3 col-sm-6 my-3\">\n<a href=\"#!\"><img class=\"img-fluid d-block mx-auto\" src=\"assets/img/logos/creative-market.jpg\" alt=\"\" /></a>\n</div>\n</div>\n</div>\n</div>\n', 7, 0);
		",
	);

	foreach ($queries as $query) {
		$statement = $db_connection->prepare($query);
		$statement->execute();
	}

	$token = hash('sha256', uniqid());

	$query = 'INSERT INTO users (login, email, password, role, logged_in, active, token)' .
	'         VALUES (:login, :email, :password, :role, NOW(), :active, :token)';

	$statement = $db_connection->prepare($query);
	$statement->bindParam(':login', $login, PDO::PARAM_STR);
	$statement->bindParam(':email', $email, PDO::PARAM_STR);
	$statement->bindParam(':password', $password, PDO::PARAM_STR);
	$statement->bindParam(':role', $role, PDO::PARAM_INT);
	$statement->bindParam(':active', $active, PDO::PARAM_INT);
	$statement->bindParam(':token', $token, PDO::PARAM_STR);

	$statement->execute();
	
	if ($statement->rowCount()) {

		unlink (dirname(__FILE__) . '/index.html');
		unlink (dirname(__FILE__) . '/page.js');
		unlink (dirname(__FILE__) . '/styles.css');
		unlink (dirname(__FILE__) . '/register.php');

		$success = true;
		$message = 'Serwis został poprawnie zainstalowany.';
	} 
	else {
		$message = 'Serwis nie został zainstalowany.';
	}	
}
else {
	$message = "Nie podano wszystkich wymaganych danych.";
}

echo json_encode (
	array (
		'user' => array (
			'name' => $login, 
			'email' => $email, 
			'access_token' => $token, 
		), 
		'success' => $success, 
		'message' => $message,
	)
);

?>
