const API = {
    url: './',
    accessToken: 'SlimCMSpageAccessToken',
};

const MSG = { SUCCESS: 1, FAILURE: 2, delay: 5000 };
const ENTER = 13, ESCAPE = 27;

function showRegisterForm() {
    $('div#registerForm').fadeIn(function() {
        setTimeout(function () { $('input#email-register').focus(); }, 500);
        $('form#mainRegisterForm input').unbind('keydown').on('keydown', function(event) {
            if (event.keyCode == ENTER) {
                event.preventDefault();
                registerUser();
            }
            if (event.keyCode == ESCAPE) {
                event.preventDefault();
                showPreview();
            }
        });
    });
}

function registerUser() {
    const credentials = {
        'email': $('form#mainRegisterForm input[name=email]').val(),
        'password': $('form#mainRegisterForm input[name=password]').val(),
    };
    $.post(API.url + 'register.php', credentials, function(response, status) {
        const result = JSON.parse(response.substring(response.indexOf('{')));
        if (result.user.access_token) {
            localStorage.setItem(API.accessToken, result.user.access_token);
            localStorage.setItem('userName', result.user.name);
            localStorage.setItem('userEmail', result.user.email);
            setTimeout(function() { showPreview(); }, 2000);
            showMessage(MSG.SUCCESS, result.message);
            $('div#progress').show();
            animateProgress();
        }
        else {
            localStorage.removeItem(API.accessToken);
            localStorage.removeItem('userName');
            localStorage.removeItem('userEmail');
            setTimeout(function() { showRegisterForm(); }, 1000);
            showMessage(MSG.FAILURE, result.message);
        }
    });
    $('div#registerForm').fadeOut(function() {
        $('form#mainRegisterForm input').val(null);
    });
}

function animateProgress() {
    const delta = 50;
    function step(percent) {
        $('div#progress div.progress-bar').css('width', percent + '%').attr('aria-valuenow', percent);
        $('div#progress div.percent').text(percent + '%');
        setTimeout(function() {
            percent += delta;
            if (percent <= 100) {
                step(percent);
            }
        }, 500);
    }
    step(0);
}

function showMessage(type, message) {
    $('div#alerts').show();
    $('div.alert').hide();
    if (type == MSG.SUCCESS) {
        $('div.alert-success').text(message).show();
    }
    if (type == MSG.FAILURE) {
        $('div.alert-danger').text(message).show();
    }
    setTimeout(function() { $('div.alert').hide(); $('div#alerts').hide(); }, MSG.delay);
}

function showPreview() {
    const installUrl = window.location.href;
    const pageUrl = installUrl.replace('/install', '');
    window.location.href = pageUrl;
}

$(document).ready(function() {
    showRegisterForm();
});
