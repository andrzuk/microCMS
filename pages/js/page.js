const API = {
    url: '../api/',
};

const page = {
    getReady: function(callback) {
        $.get(API.url + 'get_ready.php', function(response, status) {
            const ready = JSON.parse(response.substring(response.indexOf('{')));
            callback(ready.result);
        });
    },
    getMenu: function() {
        $.get(API.url + 'get_menu.php', function(response, status) {
            const menu = JSON.parse(response.substring(response.indexOf('{')));
            $.each(menu.data, function(idx, item) {
                const $link = '<li class="nav-item"><a class="nav-link js-scroll-trigger" href="/#section-'+item.id+'">'+item.caption+'</a></li>';
                $('nav#mainNav div#navbarResponsive ul.navbar-nav').append($link);
            });
            pageScrolling();
        });
    },
    getContent: function(index) {
        $.get(API.url + 'get_page.php?id=' + index, function(response, status) {
            const content = JSON.parse(response.substring(response.indexOf('{')));
            const $section = '<section class="page-section bg-light" id="'+content.data.page_index+'">'+content.data.content+'</section>';
            $('body#page-top div#mainContent').html($section);
            setTimeout(function() { location.href = '#page-' + index; }, 100);
        });
    },
    getPart: function(name) {
        $.get(API.url + 'get_part.php?name=' + name, function(response, status) {
            const $part = JSON.parse(response.substring(response.indexOf('{')));
            if (name == 'title') {
                $('head title').text($part.data.content);
            }
            if (name == 'logo') {
                $('span#brand-logo').html($part.data.content);
            }
            if (name == 'description') {
                $('head meta[name=description]').attr('content', $part.data.content);
            }
            if (name == 'author') {
                $('head meta[name=author]').attr('content', $part.data.content);
            }
            if (name == 'header') {
                $('header#mainHeader').append($part.data.content);
            }
            if (name == 'footer') {
                $('footer#mainFooter').append($part.data.content);
            }
            if (name == 'style') {
                $('style#customStyle').html($part.data.content);
            }
            if (name == 'script') {
                $('script#customScript').html($part.data.content);
            }
        });
    },
    loadContent: function(index = null) {
		if (index) {
			const id = parseInt(index.replace('page-', ''));
			page.getContent(id);
		}
		else {
			const pageUrl = window.location.href;
			if (pageUrl.indexOf('/#page-') !== -1) {
				const id = pageUrl.substring(pageUrl.indexOf('/#page-') + 7);
				page.getContent(id);
			}
		}
    },
};

$(document).ready(function() {
	page.getPart('title');
	page.getPart('logo');
	page.getPart('description');
	page.getPart('author');
	page.getPart('header');        
	page.getMenu();
	page.getPart('footer');        
	page.getPart('style');
	page.getPart('script');
	page.loadContent();
});
