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
		'DROP TABLE IF EXISTS `messages`;',
		'DROP TABLE IF EXISTS `parts`;',
		'DROP TABLE IF EXISTS `sections`;',
		'DROP TABLE IF EXISTS `menu`;',
		'DROP TABLE IF EXISTS `pages`;',
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
			CREATE TABLE `messages` (
			  `id` int(10) UNSIGNED NOT NULL,
			  `name` varchar(100) CHARACTER SET utf8 NOT NULL,
			  `email` varchar(100) CHARACTER SET utf8 NOT NULL,
			  `phone` varchar(100) CHARACTER SET utf8 NOT NULL,
			  `content` longtext CHARACTER SET utf8 NOT NULL,
			  `sent` datetime NOT NULL
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
			CREATE TABLE `pages` (
			  `id` int(10) UNSIGNED NOT NULL,
			  `page_index` varchar(100) CHARACTER SET utf8 NOT NULL,
			  `title` varchar(255) CHARACTER SET utf8 NOT NULL,
			  `content` longtext CHARACTER SET utf8 NOT NULL,
			  `modified` datetime NOT NULL
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
			ALTER TABLE `messages`
			  ADD PRIMARY KEY (`id`);
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
			ALTER TABLE `pages`
			  ADD PRIMARY KEY (`id`);
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
			ALTER TABLE `messages`
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
			ALTER TABLE `pages`
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
			(3, 'Portfolio', 2, 1),
			(4, 'About', 3, 1),
			(5, 'Team', 4, 1),
			(6, 'Contact', 5, 1);
		",
		"
			INSERT INTO `parts` (`id`, `name`, `content`) VALUES
			(1, 'header', '<div class=\"container\">\r\n<div class=\"masthead-subheading\">Welcome To Our Studio!</div>\r\n<div class=\"masthead-heading text-uppercase\">It\'s Nice To Meet You</div>\r\n<a class=\"btn btn-primary btn-xl text-uppercase js-scroll-trigger\" href=\"/admin\">Admin Panel</a>\r\n</div>\r\n'),
			(2, 'footer', '<div class=\"container\">\r\n<div class=\"row align-items-center\">\r\n<div class=\"col-lg-4 text-lg-left\">Copyright © MicroCMS 2021</div>\r\n<div class=\"col-lg-4 my-3 my-lg-0\">\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"https://twitter.com/andy_zukowski\"><i class=\"fab fa-twitter\"></i></a>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"https://github.com/andrzuk\"><i class=\"fab fa-github\"></i></a>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"https://www.facebook.com/MySiteInWeb\"><i class=\"fab fa-facebook-f\"></i></a>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"https://pl.linkedin.com/in/andrzejzukowski\"><i class=\"fab fa-linkedin-in\"></i></a>\r\n</div>\r\n<div class=\"col-lg-4 text-lg-right\">\r\n<a class=\"mr-3\" href=\"#page-1\" onclick=\"page.loadContent(\'page-1\')\">Privacy Policy</a>\r\n<a href=\"#page-2\" onclick=\"page.loadContent(\'page-2\')\">Terms of Use</a>\r\n</div>\r\n</div>\r\n</div>\r\n'),
			(3, 'title', 'Super Slim and Simple CMS'),
			(4, 'logo', '<span style=\"color: #c00; font-size: 1.25em;\">micro</span><span style=\"color: #0c0; font-size: 1.75em; font-weight: bold;\">CMS</span>'),
			(5, 'description', 'Native CMS - super slim and fast Micro CMS based on free RWD templates and jQuery scrips on frontend and PHP / MySQL on backend. Admin Panel included.'),
			(6, 'author', 'Andrzej Żukowski'),
			(7, 'script', 'setTimeout(function() {\n$(\'h3\').css({ color: \'#090\', \'text-decoration\': \'underline\' });\n}, 200);'),
			(8, 'style', 'body {\nmargin: inherit;\npadding: inherit;\nbackground: inherit;\n}\nfooter {\nbackground-color: darkgrey;\ncolor: white;\n}');
		",
		"
			INSERT INTO `pages` (`id`, `page_index`, `title`, `content`, `modified`) VALUES
			(1, 'page-1', 'Polityka prywatności', '<div class=\"container mt-5\">\n<h2 class=\"section-heading text-uppercase\">Polityka prywatności</h2>\n<div class=\"row\">\n<div class=\"col-md-12\">\n<h3 class=\"section-subheading text-muted\">Polityka prywatności opisuje zasady przetwarzania przez nas informacji na Twój temat, w tym danych osobowych oraz ciasteczek, czyli tzw. cookies.</h3>\n<div id=\"polityka\">\n<h2 class=\"point\"><b>1. Informacje ogólne</b></h2>\n<ol>\n<li>Niniejsza polityka dotyczy Serwisu www, funkcjonującego pod adresem url: <b>http://micro-cms.pl</b></li>\n<li>Operatorem serwisu oraz Administratorem danych osobowych jest: <b>Andrzej Żukowski</b></li>	\n<li>Adres kontaktowy poczty elektronicznej operatora: <b>andrzuk@tlen.pl</b></li>\n<li>Operator jest Administratorem Twoich danych osobowych w odniesieniu do danych podanych dobrowolnie w Serwisie.</li>\n<li>Serwis wykorzystuje dane osobowe w następujących celach:\n<ol>\n<li>Prowadzenie systemu komentarzy</li>\n<li>Prezentacja profil użytkownika innym użytkownikom</li>\n<li>Obsługa zapytań przez formularz</li>\n<li>Prezentacja oferty lub informacji</li>\n</ol>\n</li>\n<li>Serwis realizuje funkcje pozyskiwania informacji o użytkownikach i ich zachowaniu w następujący sposób:\n<ol>\n<li>Poprzez dobrowolnie wprowadzone w formularzach dane, które zostają wprowadzone do systemów Operatora.</li>\n<li>Poprzez zapisywanie w urządzeniach końcowych plików cookie (tzw. „ciasteczka”).</li>\n</ol>\n</li>\n</ol>\n<h2 class=\"point\"><b>2. Wybrane metody ochrony danych stosowane przez Operatora</b></h2>\n<ol>\n<li>Dane osobowe przechowywane w bazie danych są zaszyfrowane w taki sposób, że jedynie posiadający Operator klucz może je odczytać. Dzięki temu dane są chronione na wypadek wykradzenia bazy danych z serwera.</li>\n<li>Hasła użytkowników są przechowywane w postaci hashowanej. Funkcja hashująca działa jednokierunkowo - nie jest możliwe odwrócenie jej działania, co stanowi obecnie współczesny standard w zakresie przechowywania haseł użytkowników.</li>\n<li>W celu ochrony danych Operator regularnie wykonuje kopie bezpieczeństwa.</li>\n</ol>\n<h2 class=\"point\"><b>3. Hosting</b></h2>\n<ol>\n<li>Serwis jest hostowany (technicznie utrzymywany) na serwera operatora: futurehost.pl</li>\n<li>Dane rejestrowe firmy hostingowej: H88 S.A. z siedzibą w Poznaniu, Franklina Roosevelta 22, 60-829 Poznań, wpisana do Krajowego Rejestru Sądowego przez Sąd Rejonowy Poznań – Nowe Miasto i Wilda w Poznaniu, Wydział VIII Gospodarczy Krajowego Rejestru Sądowego pod nr KRS 0000612359, REGON 364261632, NIP 7822622168, kapitał zakładowy 210.000,00 zł w pełni wpłacony.</li>\n<li>Pod adresem <a href=\"https://futurehost.pl\">https://futurehost.pl</a> możesz dowiedzieć się więcej o hostingu i sprawdzić politykę prywatności firmy hostingowej.</li>\n<li>Firma hostingowa:\n<ol>\n<li>stosuje środki ochrony przed utratą danych (np. macierze dyskowe, regularne kopie bezpieczeństwa),</li>\n<li>stosuje adekwatne środki ochrony miejsc przetwarzania na wypadek pożaru (np. specjalne systemy gaśnicze),</li>\n<li>stosuje adekwatne środki ochrony systemów przetwarzania na wypadek nagłej awarii zasilania (np. podwójne tory zasilania, agregaty, systemy podtrzymania napięcia UPS),</li>\n<li>stosuje środki fizycznej ochrony dostępu do miejsc przetwarzania danych (np. kontrola dostępu, monitoring),</li>\n<li>stosuje środki zapewnienia odpowiednich warunków środowiskowych dla serwerów jako elementów systemu przetwarzania danych (np. kontrola warunków środowiskowych, specjalistyczne systemy klimatyzacji),</li>\n<li>stosuje rozwiązania organizacyjne dla zapewnienia możliwie wysokiego stopnia ochrony i poufności (szkolenia, wewnętrzne regulaminy, polityki haseł itp.),</li>\n<li>powołała Inspektora Ochrony Danych.</li>\n</ol>\n</li>\n<li>Firma hostingowa w celu zapewnienia niezawodności technicznej prowadzi logi na poziomie serwera. Zapisowi mogą podlegać:\n<ol>\n<li>zasoby określone identyfikatorem URL (adresy żądanych zasobów – stron, plików),</li>\n<li>czas nadejścia zapytania,</li>\n<li>czas wysłania odpowiedzi,</li>\n<li>nazwę stacji klienta – identyfikacja realizowana przez protokół HTTP,</li>\n<li>informacje o błędach jakie nastąpiły przy realizacji transakcji HTTP,</li>\n<li>adres URL strony poprzednio odwiedzanej przez użytkownika (referer link) – w przypadku gdy przejście do Serwisu nastąpiło przez odnośnik,</li>\n<li>informacje o przeglądarce użytkownika,</li>\n<li>informacje o adresie IP,</li>\n<li>informacje diagnostyczne związane z procesem samodzielnego zamawiania usług poprzez rejestratory na stronie,</li>\n<li>informacje związane z obsługą poczty elektronicznej kierowanej do Operatora oraz wysyłanej przez Operatora.</li>\n</ol>\n</li>\n</ol>\n<h2 class=\"point\"><b>4. Twoje prawa i dodatkowe informacje o sposobie wykorzystania danych</b></h2>\n<ol>\n<li>W niektórych sytuacjach Administrator ma prawo przekazywać Twoje dane osobowe innym odbiorcom, jeśli będzie to niezbędne do wykonania zawartej z Tobą umowy lub do zrealizowania obowiązków ciążących na Administratorze. Dotyczy to takich grup odbiorców:\n<ol>\n<li>osoby upoważnione przez nas, pracownicy i współprcownicy, którzy muszą mieć dostęp do danych osobowych w celu wykonywania swoich obowiązków,</li>\n<li>firma hostingowa,</li>\n<li>firmy obsługująca mailingi,</li>\n<li>firmy obsługująca komunikaty SMS,</li>\n<li>firmy, z którymi Administrator współpracuje w zakresie marketingu własnego,</li>\n<li>kurierzy,</li>\n<li>ubezpieczyciele,</li>\n<li>kancelarie prawne i windykatorzy,</li>\n<li>banki,</li>\n<li>operatorzy płatności,</li>\n<li>organy publiczne.</li>\n</ol>\n</li>\n<li>Twoje dane osobowe przetwarzane przez Administratora nie dłużej, niż jest to konieczne do wykonania związanych z nimi czynności określonych osobnymi przepisami (np. o prowadzeniu rachunkowości). W odniesieniu do danych marketingowych dane nie będą przetwarzane dłużej niż przez 3 lata.</li>\n<li>Przysługuje Ci prawo żądania od Administratora:\n<ol>\n<li>dostępu do danych osobowych Ciebie dotyczących,</li>\n<li>ich sprostowania,</li>\n<li>usunięcia,</li>\n<li>ograniczenia przetwarzania,</li>\n<li>oraz przenoszenia danych.</li>\n</ol>\n</li>\n<li>Przysługuje Ci prawo do złożenia sprzeciwu w zakresie przetwarzania wskazanego w pkt 3.3 c) wobec przetwarzania danych osobowych w celu wykonania prawnie uzasadnionych interesów realizowanych przez Administratora, w tym profilowania, przy czym prawo sprzeciwu nie będzie mogło być wykonane w przypadku istnienia ważnych prawnie uzasadnionych podstaw do przetwarzania, nadrzędnych wobec Ciebie interesów, praw i wolności, w szczególności ustalenia, dochodzenia lub obrony roszczeń.</li>\n<li>Na działania Administratora przysługuje skarga do Prezesa Urzędu Ochrony Danych Osobowych, ul. Stawki 2, 00-193 Warszawa.</li>\n<li>Podanie danych osobowych jest dobrowolne, lecz niezbędne do obsługi Serwisu.</li>\n<li>W stosunku do Ciebie mogą być podejmowane czynności polegające na zautomatyzowanym podejmowaniu decyzji, w tym profilowaniu w celu świadczenia usług w ramach zawartej umowy oraz w celu prowadzenia przez Administratora marketingu bezpośredniego.</li>\n<li>Dane osobowe nie są przekazywane od krajów trzecich w rozumieniu przepisów o ochronie danych osobowych. Oznacza to, że nie przesyłamy ich poza teren Unii Europejskiej.</li>\n</ol>\n<h2 class=\"point\"><b>5. Informacje w formularzach</b></h2>\n<ol>\n<li>Serwis zbiera informacje podane dobrowolnie przez użytkownika, w tym dane osobowe, o ile zostaną one podane.</li>\n<li>Serwis może zapisać informacje o parametrach połączenia (oznaczenie czasu, adres IP).</li>\n<li>Serwis, w niektórych wypadkach, może zapisać informację ułatwiającą powiązanie danych w formularzu z adresem e-mail użytkownika wypełniającego formularz. W takim wypadku adres e-mail użytkownika pojawia się wewnątrz adresu url strony zawierającej formularz.</li>\n<li>Dane podane w formularzu są przetwarzane w celu wynikającym z funkcji konkretnego formularza, np. w celu dokonania procesu obsługi zgłoszenia serwisowego lub kontaktu handlowego, rejestracji usług itp. Każdorazowo kontekst i opis formularza w czytelny sposób informuje, do czego on służy.</li>\n</ol>\n<h2 class=\"point\"><b>6. Logi Administratora</b></h2>\n<ol>\n<li>Informacje o zachowaniu użytkowników w serwisie podlegają logowaniu. Dane te są wykorzystywane w celu administrowania serwisem.</li>\n<li>W skład tych informacji wchodzą: data i czas zdarzenia, rodzaj zdarzenia (otwarcie podstrony, logowanie, rejestracja, wysłanie wiadomości, komentarza lub uwagi, wyszukiwanie), adres IP użytkownika, informacje o przeglądarce użytkownika (tzw. agent).</li>\n</ol>\n<h2 class=\"point\"><b>7. Istotne techniki marketingowe</b></h2>\n<ol>\n<li>Operator stosuje analizę statystyczną ruchu na stronie, poprzez Google Analytics (Google Inc. z siedzibą w USA). Operator nie przekazuje do operatora tej usługi danych osobowych, a jedynie zanonimizowane informacje. Usługa bazuje na wykorzystaniu ciasteczek w urządzeniu końcowym użytkownika. W zakresie informacji o preferencjach użytkownika gromadzonych przez sieć reklamową Google użytkownik może przeglądać i edytować informacje wynikające z plików cookies przy pomocy narzędzia: https://www.google.com/ads/preferences/</li>\n<li>Operator stosuje rozwiązanie badające zachowanie użytkowników poprzez tworzenie map ciepła oraz nagrywanie zachowania na stronie. Te informacje są anonimizowane zanim zostaną przesłane do operatora usługi tak, że nie wie on jakiej osoby fizycznej one dotyczą. W szczególności nagrywaniu nie podlegają wpisywane hasła oraz inne dane osobowe.</li>\n</ol>\n<h2 class=\"point\"><b>8. Informacja o plikach cookies</b></h2>\n<ol>\n<li>Serwis korzysta z plików cookies.</li>\n<li>Pliki cookies (tzw. „ciasteczka”) stanowią dane informatyczne, w szczególności pliki tekstowe, które przechowywane są w urządzeniu końcowym Użytkownika Serwisu i przeznaczone są do korzystania ze stron internetowych Serwisu. Cookies zazwyczaj zawierają nazwę strony internetowej, z której pochodzą, czas przechowywania ich na urządzeniu końcowym oraz unikalny numer.</li>\n<li>Podmiotem zamieszczającym na urządzeniu końcowym Użytkownika Serwisu pliki cookies oraz uzyskującym do nich dostęp jest operator Serwisu.</li>\n<li>Pliki cookies wykorzystywane są w następujących celach:\n<ol>\n<li>utrzymanie sesji użytkownika Serwisu (po zalogowaniu), dzięki której użytkownik nie musi na każdej podstronie Serwisu ponownie wpisywać loginu i hasła;</li>\n<li>realizacji celów określonych powyżej w części \"Istotne techniki marketingowe\";</li>\n</ol>\n</li>\n<li>W ramach Serwisu stosowane są dwa zasadnicze rodzaje plików cookies: „sesyjne” (session cookies) oraz „stałe” (persistent cookies). Cookies „sesyjne” są plikami tymczasowymi, które przechowywane są w urządzeniu końcowym Użytkownika do czasu wylogowania, opuszczenia strony internetowej lub wyłączenia oprogramowania (przeglądarki internetowej). „Stałe” pliki cookies przechowywane są w urządzeniu końcowym Użytkownika przez czas określony w parametrach plików cookies lub do czasu ich usunięcia przez Użytkownika.</li>\n<li>Oprogramowanie do przeglądania stron internetowych (przeglądarka internetowa) zazwyczaj domyślnie dopuszcza przechowywanie plików cookies w urządzeniu końcowym Użytkownika. Użytkownicy Serwisu mogą dokonać zmiany ustawień w tym zakresie. Przeglądarka internetowa umożliwia usunięcie plików cookies. Możliwe jest także automatyczne blokowanie plików cookies Szczegółowe informacje na ten temat zawiera pomoc lub dokumentacja przeglądarki internetowej.</li>\n<li>Ograniczenia stosowania plików cookies mogą wpłynąć na niektóre funkcjonalności dostępne na stronach internetowych Serwisu.</li>\n<li>Pliki cookies zamieszczane w urządzeniu końcowym Użytkownika Serwisu wykorzystywane mogą być również przez współpracujące z operatorem Serwisu podmioty, w szczególności dotyczy to firm: Google (Google Inc. z siedzibą w USA), Facebook (Facebook Inc. z siedzibą w USA), Twitter (Twitter Inc. z siedzibą w USA).</li>\n</ol>\n<h2 class=\"point\"><b>9. Zarządzanie plikami cookies – jak w praktyce wyrażać i cofać zgodę?</b></h2>\n<ol>\n<li>Jeśli użytkownik nie chce otrzymywać plików cookies, może zmienić ustawienia przeglądarki. Zastrzegamy, że wyłączenie obsługi plików cookies niezbędnych dla procesów uwierzytelniania, bezpieczeństwa, utrzymania preferencji użytkownika może utrudnić, a w skrajnych przypadkach może uniemożliwić korzystanie ze stron www</li>\n<li>W celu zarządzania ustawienia cookies wybierz z listy poniżej przeglądarkę internetową, której używasz i postępuj zgodnie z instrukcjami:\n<ol>\n<li><a href=\"https://support.microsoft.com/pl-pl/help/10607/microsoft-edge-view-delete-browser-history\">Edge</a></li>\n<li><a href=\"https://support.microsoft.com/pl-pl/help/278835/how-to-delete-cookie-files-in-internet-explorer\">Internet Explorer</a></li>\n<li><a href=\"http://support.google.com/chrome/bin/answer.py?hl=pl&answer=95647\">Chrome</a></li>\n<li><a href=\"http://support.apple.com/kb/PH5042\">Safari</a></li>\n<li><a href=\"http://support.mozilla.org/pl/kb/W%C5%82%C4%85czanie%20i%20wy%C5%82%C4%85czanie%20obs%C5%82ugi%20ciasteczek\">Firefox</a></li>\n<li><a href=\"http://help.opera.com/Windows/12.10/pl/cookies.html\">Opera</a></li>\n</ol>\nUrządzenia mobilne:\n<ol>\n<li><a href=\"http://support.google.com/chrome/bin/answer.py?hl=pl&answer=95647\">Android</a></li>\n<li><a href=\"http://support.apple.com/kb/HT1677?viewlocale=pl_PL\">Safari (iOS)</a></li>\n<li><a href=\"http://www.windowsphone.com/pl-pl/how-to/wp7/web/changing-privacy-and-other-browser-settings\">Windows Phone</a></li>\n</ol>\n</li>\n</ol>\n</div>\n</div>\n</div>\n</div>\n', NOW()),
			(2, 'page-2', 'Regulamin', '<div class=\"container mt-5\">\n<h2 class=\"section-heading text-uppercase\">Regulamin</h2>\n<div class=\"row\">\n<div class=\"col-md-12\">\n<h3 class=\"section-subheading text-muted\">Regulamin opisuje zasady korzystania z niniejszej Strony.</h3>\n<div id=\"regulamin\">\n<div>\n<h2 class=\"point\">POSTANOWIENIA OGÓLNE</h2>\n<ol>\n<li>Strona <b>http://micro-cms.pl</b> działa na zasadach określonych w niniejszym Regulaminie.</li>\n<li>Regulamin określa rodzaje i zakres usług świadczonych drogą elektroniczną przez Stronę <b>http://micro-cms.pl</b>, zasady świadczenia tych usług, warunki zawierania i rozwiązywania umów o świadczenie usług drogą elektroniczną, a także tryb postępowania reklamacyjnego.</li>	\n<li>Każdy Usługobiorca z chwilą podjęcia czynności zmierzających do korzystania z Usług Elektronicznych Strony <b>http://micro-cms.pl</b>, zobowiązany jest do przestrzegania postanowień niniejszego Regulaminu.</li>\n<li>W sprawach nieuregulowanych w niniejszym Regulaminie mają zastosowanie przepisy:\n<ol>\n<li>Ustawy o świadczeniu usług drogą elektroniczną z dnia 18 lipca 2002 r. (Dz. U. Nr 144, poz. 1204 ze zm.), </li>\n<li>Ustawy o prawach konsumenta z dnia 30 maja 2014 r. (Dz. U. 2014 poz. 827),</li>\n<li>Ustawy Kodeks cywilny z dnia 23 kwietnia 1964 r. (Dz. U. nr 16, poz. 93 ze zm.)  oraz inne właściwe przepisy prawa polskiego.</li>\n</ol>\n</li>\n</ol>\n<h2 class=\"point\">DEFINICJE</h2>\n<ol>\n<li>FORMULARZ KONTAKTOWY – formularz dostępny na Stronie <b>http://micro-cms.pl</b> umożliwiający Usługobiorcy bezpośredni kontakt z Usługodawcą.</li>\n<li>REGULAMIN - niniejszy regulamin Strony.</li>\n<li>USŁUGODAWCA – <b>Andrzej Żukowski</b>  nie wykonujący działalności gospodarczej, adres poczty elektronicznej: <b>andrzuk@tlen.pl</b>.</li>\n<li>USŁUGOBIORCA – osoba fizyczna, osoba prawna albo jednostka organizacyjna nieposiadająca osobowości prawnej, której ustawa przyznaje zdolność prawną korzystająca z Usługi Elektronicznej.</li>\n<li>USŁUGA ELEKTRONICZNA – usługa świadczona drogą elektroniczną przez Usługodawcę na rzecz Usługobiorcy za pośrednictwem Strony.</li>\n</ol>\n<h2 class=\"point\">RODZAJ I ZAKRES USŁUG ELEKTRONICZNYCH</h2>\n<ol>\n<li>Usługodawca umożliwia za pośrednictwem Strony korzystanie z Usług Elektronicznych takich jak:\n<ol>\n<li>zapoznanie się z prezentowaną treścią oraz ofertą Projektu Aplikacji Internetowej typu CMS,</li>\n<li>dostęp do kodu źródłowego Projektu Aplikacji Internetowej w serwisie GitHub poprzez zamieszczony w stopce Strony link,</li>\n<li>korzystanie z Formularza Kontaktowego,</li>\n</ol>\n</li>\n<li>Świadczenie Usług Elektronicznych na rzecz Usługobiorców odbywa się na warunkach określonych w Regulaminie.</li>\n</ol>\n<h2 class=\"point\">WARUNKI ŚWIADCZENIA I ZAWIERANIA UMÓW O ŚWIADCZENIE USŁUG ELEKTRONICZNYCH</h2>\n<ol>\n<li>Świadczenie Usług Elektronicznych określonych w rozdziale III pkt. 1 Regulaminu przez Usługodawcę jest nieodpłatne.</li>\n<li>Okres na jaki umowa zostaje zawarta:\n<ol>\n<li>umowa o świadczenie Usługi Elektronicznej polegającej na umożliwieniu wysłania wiadomości za pośrednictwem Formularza Kontaktowego zawierana jest na czas oznaczony i ulega rozwiązaniu z chwilą wysłania wiadomości albo zaprzestania jej wysyłania przez Usługobiorcę.</li>\n<li>umowa o świadczenie Usługi Elektronicznej polegającej na pobraniu kodu źródłowego aplikacji - zarówno metodą <b>\"git clone\"</b>, jak i <b>\"download ZIP\"</b> - zawierana jest na czas oznaczony i ulega rozwiązaniu z chwilą pobrania kodu źródłowego albo zaprzestania jego pobierania przez Usługobiorcę.</li>\n</ol>\n</li>\n<li>Wymagania techniczne niezbędne do współpracy z systemem teleinformatycznym, którym posługuje się Usługodawca:\n<ol>\n<li>komputer z dostępem do Internetu,</li>\n<li>dostęp do poczty elektronicznej,</li>\n<li>przeglądarka internetowa,</li>\n<li>włączenie w przeglądarce internetowej Cookies oraz Javascript.</li>\n</ol>\n</li>\n<li>Usługobiorca zobowiązany jest do korzystania ze Strony w sposób zgodny z prawem i dobrymi obyczajami mając na uwadze poszanowanie dóbr osobistych i praw własności intelektualnej osób trzecich.</li>\n<li>Usługobiorca zobowiązany jest do wprowadzania danych zgodnych ze stanem faktycznym.</li>\n<li>Usługobiorcę obowiązuje zakaz dostarczania treści o charakterze bezprawnym.</li>\n</ol>\n<h2 class=\"point\">TRYB POSTĘPOWANIA REKLAMACYJNEGO</h2>\n<ol>\n<li>Reklamacje związane ze świadczeniem Usług Elektronicznych przez Usługodawcę:\n<ol>\n<li>Reklamacje związane ze świadczeniem Usług Elektronicznych za pośrednictwem Strony Usługobiorca może składać za pośrednictwem poczty elektronicznej na adres: <b>andrzuk@tlen.pl</b>.</li>\n<li>W powyższej wiadomości e-mail należy podać jak najwięcej informacji i okoliczności dotyczących przedmiotu reklamacji, w szczególności rodzaj i datę wystąpienia nieprawidłowości oraz dane kontaktowe. Podane informacje znacznie ułatwią i przyspieszą rozpatrzenie reklamacji przez Usługodawcę.</li>\n<li>Rozpatrzenie reklamacji przez Usługodawcę następuje niezwłocznie, nie później niż w terminie 14 dni.</li>\n<li>Odpowiedź Usługodawcy w sprawie reklamacji jest wysyłana na adres e-mail Usługobiorcy podany w zgłoszeniu reklamacyjnym lub w inny podany przez Usługobiorcę sposób.</li>\n</ol>\n</li>\n<li>Świadczenie Usług Elektronicznych na rzecz Usługobiorców odbywa się na warunkach określonych w Regulaminie.</li>\n</ol>\n<h2 class=\"point\">WŁASNOŚĆ INTELEKTUALNA</h2>\n<ol>\n<li>Wszystkie treści zamieszczone na stronie internetowej pod adresem <b>http://micro-cms.pl</b> korzystają z ochrony prawno-autorskiej i są własnością <b>http://micro-cms.pl</b>.</li>\n<li>Usługobiorca ponosi pełną odpowiedzialność za szkodę wyrządzoną Usługodawcy, będącą następstwem użycia jakiejkolwiek zawartości Strony <b>http://micro-cms.pl</b>, bez zgody Usługodawcy.</li>\n<li>Jakiekolwiek wykorzystanie przez kogokolwiek, bez wyraźnej pisemnej zgody Usługodawcy, któregokolwiek z elementów składających się na treść oraz zawartość Strony <b>http://micro-cms.pl</b> stanowi naruszenie prawa autorskiego przysługującego Usługodawcy i skutkuje odpowiedzialnością cywilnoprawną oraz karną.</li>\n</ol>\n<h2 class=\"point\">POSTANOWIENIA KOŃCOWE</h2>\n<ol>\n<li>Umowy zawierane za pośrednictwem Strony zawierane są zgodnie z prawem polskim.</li>\n<li>W przypadku niezgodności jakiejkolwiek części Regulaminu z obowiązującym prawem, w miejsce zakwestionowanego przepisu Regulaminu zastosowanie mają właściwe przepisy prawa polskiego.</li>\n</ol>\n</div>\n</div>\n</div>\n</div>\n</div>\n', NOW());
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
			(2, 3, '<div class=\"container\">\n<div class=\"text-center\">\n<h2 class=\"section-heading text-uppercase\">Portfolio</h2>\n<h3 class=\"section-subheading text-muted\">Lorem ipsum dolor sit amet consectetur.</h3>\n</div>\n<div class=\"row\">\n<div class=\"col-lg-4 col-sm-6 mb-4\">\n<div class=\"portfolio-item\">\n<a class=\"portfolio-link\" data-toggle=\"modal\" href=\"#portfolioModal1\">\n<div class=\"portfolio-hover\">\n<div class=\"portfolio-hover-content\"><i class=\"fas fa-plus fa-3x\"></i></div>\n</div>\n<img class=\"img-fluid\" src=\"assets/img/portfolio/01-thumbnail.jpg\" alt=\"\" />\n</a>\n<div class=\"portfolio-caption\">\n<div class=\"portfolio-caption-heading\">Threads</div>\n<div class=\"portfolio-caption-subheading text-muted\">Illustration</div>\n</div>\n</div>\n</div>\n<div class=\"col-lg-4 col-sm-6 mb-4\">\n<div class=\"portfolio-item\">\n<a class=\"portfolio-link\" data-toggle=\"modal\" href=\"#portfolioModal2\">\n<div class=\"portfolio-hover\">\n<div class=\"portfolio-hover-content\"><i class=\"fas fa-plus fa-3x\"></i></div>\n</div>\n<img class=\"img-fluid\" src=\"assets/img/portfolio/02-thumbnail.jpg\" alt=\"\" />\n</a>\n<div class=\"portfolio-caption\">\n<div class=\"portfolio-caption-heading\">Explore</div>\n<div class=\"portfolio-caption-subheading text-muted\">Graphic Design</div>\n</div>\n</div>\n</div>\n<div class=\"col-lg-4 col-sm-6 mb-4\">\n<div class=\"portfolio-item\">\n<a class=\"portfolio-link\" data-toggle=\"modal\" href=\"#portfolioModal3\">\n<div class=\"portfolio-hover\">\n<div class=\"portfolio-hover-content\"><i class=\"fas fa-plus fa-3x\"></i></div>\n</div>\n<img class=\"img-fluid\" src=\"assets/img/portfolio/03-thumbnail.jpg\" alt=\"\" />\n</a>\n<div class=\"portfolio-caption\">\n<div class=\"portfolio-caption-heading\">Finish</div>\n<div class=\"portfolio-caption-subheading text-muted\">Identity</div>\n</div>\n</div>\n</div>\n<div class=\"col-lg-4 col-sm-6 mb-4 mb-lg-0\">\n<div class=\"portfolio-item\">\n<a class=\"portfolio-link\" data-toggle=\"modal\" href=\"#portfolioModal4\">\n<div class=\"portfolio-hover\">\n<div class=\"portfolio-hover-content\"><i class=\"fas fa-plus fa-3x\"></i></div>\n</div>\n<img class=\"img-fluid\" src=\"assets/img/portfolio/04-thumbnail.jpg\" alt=\"\" />\n</a>\n<div class=\"portfolio-caption\">\n<div class=\"portfolio-caption-heading\">Lines</div>\n<div class=\"portfolio-caption-subheading text-muted\">Branding</div>\n</div>\n</div>\n</div>\n<div class=\"col-lg-4 col-sm-6 mb-4 mb-sm-0\">\n<div class=\"portfolio-item\">\n<a class=\"portfolio-link\" data-toggle=\"modal\" href=\"#portfolioModal5\">\n<div class=\"portfolio-hover\">\n<div class=\"portfolio-hover-content\"><i class=\"fas fa-plus fa-3x\"></i></div>\n</div>\n<img class=\"img-fluid\" src=\"assets/img/portfolio/05-thumbnail.jpg\" alt=\"\" />\n</a>\n<div class=\"portfolio-caption\">\n<div class=\"portfolio-caption-heading\">Southwest</div>\n<div class=\"portfolio-caption-subheading text-muted\">Website Design</div>\n</div>\n</div>\n</div>\n<div class=\"col-lg-4 col-sm-6\">\n<div class=\"portfolio-item\">\n<a class=\"portfolio-link\" data-toggle=\"modal\" href=\"#portfolioModal6\">\n<div class=\"portfolio-hover\">\n<div class=\"portfolio-hover-content\"><i class=\"fas fa-plus fa-3x\"></i></div>\n</div>\n<img class=\"img-fluid\" src=\"assets/img/portfolio/06-thumbnail.jpg\" alt=\"\" />\n</a>\n<div class=\"portfolio-caption\">\n<div class=\"portfolio-caption-heading\">Window</div>\n<div class=\"portfolio-caption-subheading text-muted\">Photography</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n<!-- Portfolio Modals-->\n<!-- Modal 1-->\n<div class=\"portfolio-modal modal fade\" id=\"portfolioModal1\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">\n<div class=\"modal-dialog\">\n<div class=\"modal-content\">\n<div class=\"close-modal\" data-dismiss=\"modal\"><img src=\"assets/img/close-icon.svg\" alt=\"Close modal\" /></div>\n<div class=\"container\">\n<div class=\"row justify-content-center\">\n<div class=\"col-lg-8\">\n<div class=\"modal-body\">\n<!-- Project Details Go Here-->\n<h2 class=\"text-uppercase\">Project Name</h2>\n<p class=\"item-intro text-muted\">Lorem ipsum dolor sit amet consectetur.</p>\n<img class=\"img-fluid d-block mx-auto\" src=\"assets/img/portfolio/01-full.jpg\" alt=\"\" />\n<p>Use this area to describe your project. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Est blanditiis dolorem culpa incidunt minus dignissimos deserunt repellat aperiam quasi sunt officia expedita beatae cupiditate, maiores repudiandae, nostrum, reiciendis facere nemo!</p>\n<ul class=\"list-inline\">\n<li>Date: January 2020</li>\n<li>Client: Threads</li>\n<li>Category: Illustration</li>\n</ul>\n<button class=\"btn btn-primary\" data-dismiss=\"modal\" type=\"button\">\n<i class=\"fas fa-times mr-1\"></i>\nClose Project\n</button>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n<!-- Modal 2-->\n<div class=\"portfolio-modal modal fade\" id=\"portfolioModal2\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">\n<div class=\"modal-dialog\">\n<div class=\"modal-content\">\n<div class=\"close-modal\" data-dismiss=\"modal\"><img src=\"assets/img/close-icon.svg\" alt=\"Close modal\" /></div>\n<div class=\"container\">\n<div class=\"row justify-content-center\">\n<div class=\"col-lg-8\">\n<div class=\"modal-body\">\n<!-- Project Details Go Here-->\n<h2 class=\"text-uppercase\">Project Name</h2>\n<p class=\"item-intro text-muted\">Lorem ipsum dolor sit amet consectetur.</p>\n<img class=\"img-fluid d-block mx-auto\" src=\"assets/img/portfolio/02-full.jpg\" alt=\"\" />\n<p>Use this area to describe your project. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Est blanditiis dolorem culpa incidunt minus dignissimos deserunt repellat aperiam quasi sunt officia expedita beatae cupiditate, maiores repudiandae, nostrum, reiciendis facere nemo!</p>\n<ul class=\"list-inline\">\n<li>Date: January 2020</li>\n<li>Client: Explore</li>\n<li>Category: Graphic Design</li>\n</ul>\n<button class=\"btn btn-primary\" data-dismiss=\"modal\" type=\"button\">\n<i class=\"fas fa-times mr-1\"></i>\nClose Project\n</button>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n<!-- Modal 3-->\n<div class=\"portfolio-modal modal fade\" id=\"portfolioModal3\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">\n<div class=\"modal-dialog\">\n<div class=\"modal-content\">\n<div class=\"close-modal\" data-dismiss=\"modal\"><img src=\"assets/img/close-icon.svg\" alt=\"Close modal\" /></div>\n<div class=\"container\">\n<div class=\"row justify-content-center\">\n<div class=\"col-lg-8\">\n<div class=\"modal-body\">\n<!-- Project Details Go Here-->\n<h2 class=\"text-uppercase\">Project Name</h2>\n<p class=\"item-intro text-muted\">Lorem ipsum dolor sit amet consectetur.</p>\n<img class=\"img-fluid d-block mx-auto\" src=\"assets/img/portfolio/03-full.jpg\" alt=\"\" />\n<p>Use this area to describe your project. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Est blanditiis dolorem culpa incidunt minus dignissimos deserunt repellat aperiam quasi sunt officia expedita beatae cupiditate, maiores repudiandae, nostrum, reiciendis facere nemo!</p>\n<ul class=\"list-inline\">\n<li>Date: January 2020</li>\n<li>Client: Finish</li>\n<li>Category: Identity</li>\n</ul>\n<button class=\"btn btn-primary\" data-dismiss=\"modal\" type=\"button\">\n<i class=\"fas fa-times mr-1\"></i>\nClose Project\n</button>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n<!-- Modal 4-->\n<div class=\"portfolio-modal modal fade\" id=\"portfolioModal4\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">\n<div class=\"modal-dialog\">\n<div class=\"modal-content\">\n<div class=\"close-modal\" data-dismiss=\"modal\"><img src=\"assets/img/close-icon.svg\" alt=\"Close modal\" /></div>\n<div class=\"container\">\n<div class=\"row justify-content-center\">\n<div class=\"col-lg-8\">\n<div class=\"modal-body\">\n<!-- Project Details Go Here-->\n<h2 class=\"text-uppercase\">Project Name</h2>\n<p class=\"item-intro text-muted\">Lorem ipsum dolor sit amet consectetur.</p>\n<img class=\"img-fluid d-block mx-auto\" src=\"assets/img/portfolio/04-full.jpg\" alt=\"\" />\n<p>Use this area to describe your project. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Est blanditiis dolorem culpa incidunt minus dignissimos deserunt repellat aperiam quasi sunt officia expedita beatae cupiditate, maiores repudiandae, nostrum, reiciendis facere nemo!</p>\n<ul class=\"list-inline\">\n<li>Date: January 2020</li>\n<li>Client: Lines</li>\n<li>Category: Branding</li>\n</ul>\n<button class=\"btn btn-primary\" data-dismiss=\"modal\" type=\"button\">\n<i class=\"fas fa-times mr-1\"></i>\nClose Project\n</button>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n<!-- Modal 5-->\n<div class=\"portfolio-modal modal fade\" id=\"portfolioModal5\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">\n<div class=\"modal-dialog\">\n<div class=\"modal-content\">\n<div class=\"close-modal\" data-dismiss=\"modal\"><img src=\"assets/img/close-icon.svg\" alt=\"Close modal\" /></div>\n<div class=\"container\">\n<div class=\"row justify-content-center\">\n<div class=\"col-lg-8\">\n<div class=\"modal-body\">\n<!-- Project Details Go Here-->\n<h2 class=\"text-uppercase\">Project Name</h2>\n<p class=\"item-intro text-muted\">Lorem ipsum dolor sit amet consectetur.</p>\n<img class=\"img-fluid d-block mx-auto\" src=\"assets/img/portfolio/05-full.jpg\" alt=\"\" />\n<p>Use this area to describe your project. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Est blanditiis dolorem culpa incidunt minus dignissimos deserunt repellat aperiam quasi sunt officia expedita beatae cupiditate, maiores repudiandae, nostrum, reiciendis facere nemo!</p>\n<ul class=\"list-inline\">\n<li>Date: January 2020</li>\n<li>Client: Southwest</li>\n<li>Category: Website Design</li>\n</ul>\n<button class=\"btn btn-primary\" data-dismiss=\"modal\" type=\"button\">\n<i class=\"fas fa-times mr-1\"></i>\nClose Project\n</button>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n<!-- Modal 6-->\n<div class=\"portfolio-modal modal fade\" id=\"portfolioModal6\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">\n<div class=\"modal-dialog\">\n<div class=\"modal-content\">\n<div class=\"close-modal\" data-dismiss=\"modal\"><img src=\"assets/img/close-icon.svg\" alt=\"Close modal\" /></div>\n<div class=\"container\">\n<div class=\"row justify-content-center\">\n<div class=\"col-lg-8\">\n<div class=\"modal-body\">\n<!-- Project Details Go Here-->\n<h2 class=\"text-uppercase\">Project Name</h2>\n<p class=\"item-intro text-muted\">Lorem ipsum dolor sit amet consectetur.</p>\n<img class=\"img-fluid d-block mx-auto\" src=\"assets/img/portfolio/06-full.jpg\" alt=\"\" />\n<p>Use this area to describe your project. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Est blanditiis dolorem culpa incidunt minus dignissimos deserunt repellat aperiam quasi sunt officia expedita beatae cupiditate, maiores repudiandae, nostrum, reiciendis facere nemo!</p>\n<ul class=\"list-inline\">\n<li>Date: January 2020</li>\n<li>Client: Window</li>\n<li>Category: Photography</li>\n</ul>\n<button class=\"btn btn-primary\" data-dismiss=\"modal\" type=\"button\">\n<i class=\"fas fa-times mr-1\"></i>\nClose Project\n</button>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n</div>\n', 2, 1),
			(3, 4, '<div class=\"container\">\r\n<div class=\"text-center\">\r\n<h2 class=\"section-heading text-uppercase\">About</h2>\r\n<h3 class=\"section-subheading text-muted\">Lorem ipsum dolor sit amet consectetur.</h3>\r\n</div>\r\n<ul class=\"timeline\">\r\n<li>\r\n<div class=\"timeline-image\"><img class=\"rounded-circle img-fluid\" src=\"assets/img/about/1.jpg\" alt=\"\" /></div>\r\n<div class=\"timeline-panel\">\r\n<div class=\"timeline-heading\">\r\n<h4>2009-2011</h4>\r\n<h4 class=\"subheading\">Our Humble Beginnings</h4>\r\n</div>\r\n<div class=\"timeline-body\"><p class=\"text-muted\">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sunt ut voluptatum eius sapiente, totam reiciendis temporibus qui quibusdam, recusandae sit vero unde, sed, incidunt et ea quo dolore laudantium consectetur!</p></div>\r\n</div>\r\n</li>\r\n<li class=\"timeline-inverted\">\r\n<div class=\"timeline-image\"><img class=\"rounded-circle img-fluid\" src=\"assets/img/about/2.jpg\" alt=\"\" /></div>\r\n<div class=\"timeline-panel\">\r\n<div class=\"timeline-heading\">\r\n<h4>March 2011</h4>\r\n<h4 class=\"subheading\">An Agency is Born</h4>\r\n</div>\r\n<div class=\"timeline-body\"><p class=\"text-muted\">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sunt ut voluptatum eius sapiente, totam reiciendis temporibus qui quibusdam, recusandae sit vero unde, sed, incidunt et ea quo dolore laudantium consectetur!</p></div>\r\n</div>\r\n</li>\r\n<li>\r\n<div class=\"timeline-image\"><img class=\"rounded-circle img-fluid\" src=\"assets/img/about/3.jpg\" alt=\"\" /></div>\r\n<div class=\"timeline-panel\">\r\n<div class=\"timeline-heading\">\r\n<h4>December 2012</h4>\r\n<h4 class=\"subheading\">Transition to Full Service</h4>\r\n</div>\r\n<div class=\"timeline-body\"><p class=\"text-muted\">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sunt ut voluptatum eius sapiente, totam reiciendis temporibus qui quibusdam, recusandae sit vero unde, sed, incidunt et ea quo dolore laudantium consectetur!</p></div>\r\n</div>\r\n</li>\r\n<li class=\"timeline-inverted\">\r\n<div class=\"timeline-image\"><img class=\"rounded-circle img-fluid\" src=\"assets/img/about/4.jpg\" alt=\"\" /></div>\r\n<div class=\"timeline-panel\">\r\n<div class=\"timeline-heading\">\r\n<h4>July 2014</h4>\r\n<h4 class=\"subheading\">Phase Two Expansion</h4>\r\n</div>\r\n<div class=\"timeline-body\"><p class=\"text-muted\">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sunt ut voluptatum eius sapiente, totam reiciendis temporibus qui quibusdam, recusandae sit vero unde, sed, incidunt et ea quo dolore laudantium consectetur!</p></div>\r\n</div>\r\n</li>\r\n<li class=\"timeline-inverted\">\r\n<div class=\"timeline-image\">\r\n<h4>\r\nBe Part\r\n<br />\r\nOf Our\r\n<br />\r\nStory!\r\n</h4>\r\n</div>\r\n</li>\r\n</ul>\r\n</div>\r\n', 3, 1),
			(4, 5, '<div class=\"container\">\r\n<div class=\"text-center\">\r\n<h2 class=\"section-heading text-uppercase\">Our Amazing Team</h2>\r\n<h3 class=\"section-subheading text-muted\">Lorem ipsum dolor sit amet consectetur.</h3>\r\n</div>\r\n<div class=\"row\">\r\n<div class=\"col-lg-4\">\r\n<div class=\"team-member\">\r\n<img class=\"mx-auto rounded-circle\" src=\"assets/img/team/1.jpg\" alt=\"\" />\r\n<h4>Kay Garland</h4>\r\n<p class=\"text-muted\">Lead Designer</p>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-twitter\"></i></a>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-facebook-f\"></i></a>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-linkedin-in\"></i></a>\r\n</div>\r\n</div>\r\n<div class=\"col-lg-4\">\r\n<div class=\"team-member\">\r\n<img class=\"mx-auto rounded-circle\" src=\"assets/img/team/2.jpg\" alt=\"\" />\r\n<h4>Larry Parker</h4>\r\n<p class=\"text-muted\">Lead Marketer</p>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-twitter\"></i></a>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-facebook-f\"></i></a>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-linkedin-in\"></i></a>\r\n</div>\r\n</div>\r\n<div class=\"col-lg-4\">\r\n<div class=\"team-member\">\r\n<img class=\"mx-auto rounded-circle\" src=\"assets/img/team/3.jpg\" alt=\"\" />\r\n<h4>Diana Petersen</h4>\r\n<p class=\"text-muted\">Lead Developer</p>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-twitter\"></i></a>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-facebook-f\"></i></a>\r\n<a class=\"btn btn-dark btn-social mx-2\" href=\"#!\"><i class=\"fab fa-linkedin-in\"></i></a>\r\n</div>\r\n</div>\r\n</div>\r\n<div class=\"row\">\r\n<div class=\"col-lg-8 mx-auto text-center\"><p class=\"large text-muted\">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut eaque, laboriosam veritatis, quos non quis ad perspiciatis, totam corporis ea, alias ut unde.</p></div>\r\n</div>\r\n</div>\r\n', 4, 1),
			(5, 6, '<div class=\"container\">\n<div class=\"text-center\">\n<h2 class=\"section-heading text-uppercase\">Contact</h2>\n<h3 class=\"section-subheading text-muted\">Oto nasza lokalizacja:</h3>\n<div class=\"mb-5\">\n<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4868.189906184837!2d16.90535455942152!3d52.40495529413141!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x9eeed0bd55c0f364!2sMi%C4%99dzynarodowe+Targi+Pozna%C5%84skie!5e0!3m2!1spl!2spl!4v1494942598996\" width=\"100%\" height=\"450\" frameborder=\"0\" style=\"border: #aaa 1px solid;\"></iframe>\n</div>\n<h3 class=\"section-subheading text-muted\">Napisz do nas, korzystając z poniższego formularza kontaktowego:</h3>\n</div>\n<form id=\"contactForm\" name=\"sentMessage\" novalidate=\"novalidate\">\n<div class=\"row align-items-stretch mb-5\">\n<div class=\"col-md-6\">\n<div class=\"form-group\">\n<input class=\"form-control\" id=\"name\" type=\"text\" placeholder=\"Your Name *\" required=\"required\" data-validation-required-message=\"Please enter your name.\" />\n<p class=\"help-block text-danger\"></p>\n</div>\n<div class=\"form-group\">\n<input class=\"form-control\" id=\"email\" type=\"email\" placeholder=\"Your Email *\" required=\"required\" data-validation-required-message=\"Please enter your email address.\" />\n<p class=\"help-block text-danger\"></p>\n</div>\n<div class=\"form-group mb-md-0\">\n<input class=\"form-control\" id=\"phone\" type=\"tel\" placeholder=\"Your Phone *\" required=\"required\" data-validation-required-message=\"Please enter your phone number.\" />\n<p class=\"help-block text-danger\"></p>\n</div>\n</div>\n<div class=\"col-md-6\">\n<div class=\"form-group form-group-textarea mb-md-0\">\n<textarea class=\"form-control\" id=\"message\" rows=\"6\" placeholder=\"Your Message *\" required=\"required\" data-validation-required-message=\"Please enter a message.\"></textarea>\n<p class=\"help-block text-danger\"></p>\n</div>\n</div>\n</div>\n<div class=\"text-center\">\n<div id=\"success\"></div>\n<button class=\"btn btn-primary btn-xl text-uppercase\" id=\"sendMessageButton\" type=\"button\" onclick=\"sendMessage($(\'form#contactForm\'))\">Send Message</button>\n</div>\n</form>\n</div>\n', 5, 1),
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
