const API = {
    url: 'api/',
};

const page = {
    getReady: function(callback) {
        $.get(API.url + 'get_ready.php', function(response, status) {
            const ready = JSON.parse(response.substring(response.indexOf('{')));
            callback(ready.result);
        });
    },
    showInstall: function () {
        $('div#install').show();
    },
    getMenu: function() {
        $.get(API.url + 'get_menu.php', function(response, status) {
            const menu = JSON.parse(response.substring(response.indexOf('{')));
            $.each(menu.data, function(idx, item) {
                const $link = '<li class="nav-item"><a class="nav-link js-scroll-trigger" href="#menu-'+item.id+'">'+item.caption+'</a></li>';
                $('nav#mainNav div#navbarResponsive ul.navbar-nav').append($link);
            });            
            pageScrolling();
        });
    },
    getSections: function() {
        $.get(API.url + 'get_sections.php', function(response, status) {
            const sections = JSON.parse(response.substring(response.indexOf('{')));
            $.each(sections.data, function(idx, item) {
                const $section = '<section class="page-section bg-light" id="menu-'+item.menu_id+'">'+item.content+'</section>';
                $('body#page-top div#mainContent').append($section);
            });
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
};

$(document).ready(function() {
    page.getReady(function(ready) {
        if (ready) {
            page.getPart('title');
            page.getPart('logo');
            page.getPart('description');
            page.getPart('author');
            page.getPart('header');        
            page.getMenu();
            page.getSections();
            page.getPart('footer');        
            page.getPart('style');
            page.getPart('script');
        }
        else {
            page.showInstall();
        }
    });
});
