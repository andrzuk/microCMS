const API = {
    url: '../api/',
    accessToken: 'SlimCMSpageAccessToken',
};

const MSG = { SUCCESS: 1, FAILURE: 2, delay: 5000 };

const page = {
    getReady: function(callback) {
        $.get(API.url + 'get_ready.php', function(response, status) {
            const ready = JSON.parse(response.substring(response.indexOf('{')));
            callback(ready.result);
        });
    },
    showInstall: function() {
        $('div#install').show();
    },
    checkAuthorization: function() {
        const token = localStorage.getItem(API.accessToken);
        if (token) {
            $.ajax({
                url: API.url + 'check_auth.php',
                headers: { 'X-Auth-Token': token },
                type: 'GET',
                success: function(response) { 
                    showAdminPanel();
                },
                error: function(response) {
                    showLoginForm();
                }
            });
        }
        else {
            showLoginForm();
        }
    },
    getPart: function(name) {
        $.get(API.url + 'get_part.php?name=' + name, function(response, status) {
            const $part = JSON.parse(response.substring(response.indexOf('{')));
            if (name == 'title') {
                $('head title').text($part.data.content);
            }
            if (name == 'description') {
                $('head meta[name=description]').attr('content', $part.data.content);
            }
            if (name == 'author') {
                $('head meta[name=author]').attr('content', $part.data.content);
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

function showLoginForm() {
    $('li.logged-out').show();
    $('li.logged-in').hide();
    $('div.itemContent').hide();
    $('div#loginForm').fadeIn(function() {
        setTimeout(function () { $('input#email').focus(); }, 500);
        $('form#mainLoginForm input').on('keydown', function(event) {
            const ENTER = 13, ESCAPE = 27;
            if (event.keyCode == ENTER) {
                event.preventDefault();
                loginUser();
            }
            if (event.keyCode == ESCAPE) {
                event.preventDefault();
                showPreview();
            }
        });
    });
}

function loginUser() {
    const credentials = {
        'email': $('form#mainLoginForm input[name=email]').val(),
        'password': $('form#mainLoginForm input[name=password]').val(),
    };
    $.post(API.url + 'login.php', credentials, function(response, status) {
        const result = JSON.parse(response.substring(response.indexOf('{')));
        if (result.user.access_token) {
            localStorage.setItem(API.accessToken, result.user.access_token);
            localStorage.setItem('userName', result.user.name);
            localStorage.setItem('userEmail', result.user.email);
            localStorage.setItem('loggedIn', result.user.logged_in);
            localStorage.setItem('loggedOut', result.user.logged_out);    
            showAdminPanel();
            showMessage(MSG.SUCCESS, result.message);
        }
        else {
            localStorage.removeItem(API.accessToken);
            localStorage.removeItem('userName');
            localStorage.removeItem('userEmail');
            localStorage.removeItem('loggedIn');
            localStorage.removeItem('loggedOut');    
            showLoginForm();
            showMessage(MSG.FAILURE, result.message);
        }
    });
    $('div#loginForm').fadeOut(function() {
        $('form#mainLoginForm input').val(null);
    });
}

function logoutUser() {
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'logout.php',
            headers: { 'X-Auth-Token': token },
            type: 'GET',
            success: function(response) { 
                localStorage.removeItem(API.accessToken);
                localStorage.removeItem('userName');
                localStorage.removeItem('userEmail');
                localStorage.removeItem('loggedIn');
                localStorage.removeItem('loggedOut');    
                const result = JSON.parse(response.substring(response.indexOf('{')));
                showMessage(result.success ? MSG.SUCCESS : MSG.FAILURE, result.message);
                showLoginForm();
            },
        });
    }
}

function showPreview() {
    const adminUrl = window.location.href;
    const pageUrl = adminUrl.replace('/admin', '');
    window.location.href = pageUrl;
}

function showAdminPanel() {
    $('li.logged-out').hide();
    $('li.logged-in').show();
    $('div.itemContent').hide();
    $('div#usersList, div#partsList, div#menusList, div#sectionsList, div#imagesList').fadeIn();
    loadUsersList();
    loadPartsList();
    loadMenusList();
    loadSectionsList();
    loadImagesList();
    adminScrolling();
}

function loadUsersList() {
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/get_users.php',
            headers: { 'X-Auth-Token': token },
            type: 'GET',
            success: function(response) { 
                $('div#usersList table tbody').html(null);
                $('form#userForm select#edit-role').val(null);
                const data = JSON.parse(response.substring(response.indexOf('{')));
                $.each(data.result, function(idx, item) {
                    const status = parseInt(item.active) ? 'normal' : 'locked';
                    const $row = $('<tr class="'+status+'"><th scope="row">'+item.id+'</th><td>'+item.login+'</td><td>'+item.email+'</td><td>'+item.role+'</td><td>'+item.logged_in+'</td><td class="action"><button class="btn btn-sm btn-warning" onclick="fillUser('+item.id+')">Edytuj</button><button class="btn btn-sm btn-info" onclick="changePassword('+item.id+')">Hasło</button><button class="btn btn-sm btn-danger" onclick="removeConfirm(\'users\', '+item.id+')">Usuń</button></td></tr>');
                    $('div#usersList table tbody').append($row);
                });
            },
        });
    }
}

function addUser() {
    $('form#userForm input#user-id').val(0);
    $('form#userForm input#edit-login').val(null);
    $('form#userForm input#edit-email').val(null);
    $('form#userForm select#edit-role').val(4);
    $('form#userForm input#edit-enabled-on').prop('checked', true); 
    $('form#userForm input#edit-enabled-off').prop('checked', null);
    $('form#userForm input#edit-login').focus();
}

function fillUser(id) {
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/get_user.php?id=' + id,
            headers: { 'X-Auth-Token': token },
            type: 'GET',
            success: function(response) { 
                const data = JSON.parse(response.substring(response.indexOf('{')));
                $('form#userForm input#user-id').val(data.result.id);
                $('form#userForm input#edit-login').val(data.result.login);
                $('form#userForm input#edit-email').val(data.result.email);
                $('form#userForm select#edit-role').val(data.result.role);
                $('form#userForm input#edit-enabled-on').prop('checked', parseInt(data.result.active) ? true : null); 
                $('form#userForm input#edit-enabled-off').prop('checked', parseInt(data.result.active) ? null : true);
                $('form#userForm input#edit-login').focus();
            },
        });
    }
}

function saveUser() {
    const user = {
        id: $('form#userForm input#user-id').val(),
        login: $('form#userForm input#edit-login').val(),
        email: $('form#userForm input#edit-email').val(),
        role: $('form#userForm select#edit-role').val(),
        active: $('form#userForm input#edit-enabled-on').is(':checked') ? 1 : 0,
    };
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + (user.id == 0 ? 'admin/add_user.php' : 'admin/update_user.php'),
            headers: { 'X-Auth-Token': token },
            data: user,
            type: 'POST',
            success: function(response) { 
                const data = JSON.parse(response.substring(response.indexOf('{')));
                if (data.success) {
                    $('form#userForm input, form#userForm select').val(null);
                    loadUsersList();
                    showMessage(MSG.SUCCESS, data.message);
                }
                else {
                    showMessage(MSG.FAILURE, data.message);
                }
            },
        });
    }
}

function changePassword(id) {
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/get_user.php?id=' + id,
            headers: { 'X-Auth-Token': token },
            type: 'GET',
            success: function(response) { 
                const data = JSON.parse(response.substring(response.indexOf('{')));
                $('form#passwordForm input#password-id').val(data.result.id);
                $('form#passwordForm input#edit-password').val(null).focus();
            },
        });
    }
}

function savePassword() {
    const password = {
        id: $('form#passwordForm input#password-id').val(),
        password: $('form#passwordForm input#edit-password').val(),
    };
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/update_password.php',
            headers: { 'X-Auth-Token': token },
            data: password,
            type: 'POST',
            success: function(response) { 
                const data = JSON.parse(response.substring(response.indexOf('{')));
                if (data.success) {
                    $('form#passwordForm input').val(null);
                    showMessage(MSG.SUCCESS, data.message);
                }
                else {
                    showMessage(MSG.FAILURE, data.message);
                }
            },
        });
    }
}

function removeUser(id) {
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/remove_user.php?id=' + id,
            headers: { 'X-Auth-Token': token },
            type: 'GET',
            success: function(response) { 
                const data = JSON.parse(response.substring(response.indexOf('{')));
                if (data.success) {
                    $('form#userForm input, form#userForm select').val(null);
                    loadUsersList();
                    showMessage(MSG.SUCCESS, data.message);
                }
                else {
                    showMessage(MSG.FAILURE, data.message);
                }
            },
        });
    }
}

function loadPartsList() {
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/get_parts.php',
            headers: { 'X-Auth-Token': token },
            type: 'GET',
            success: function(response) { 
                $('div#partsList table tbody').html(null);
                const data = JSON.parse(response.substring(response.indexOf('{')));
                $.each(data.result, function(idx, item) {
                    const $row = $('<tr><th scope="row">'+item.id+'</th><td>'+item.name+'</td><td>'+item.content.replace('<', '').replace('>', '').substring(0, 32)+'...'+'</td><td class="action"><button class="btn btn-sm btn-warning" onclick="fillPart('+item.id+')">Edytuj</button></td></tr>');
                    $('div#partsList table tbody').append($row);
                });
            },
        });
    }
}

function fillPart(id) {
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/get_part.php?id=' + id,
            headers: { 'X-Auth-Token': token },
            type: 'GET',
            success: function(response) { 
                const data = JSON.parse(response.substring(response.indexOf('{')));
                $('form#partForm input#part-id').val(data.result.id);
                $('form#partForm input#edit-name').val(data.result.name);
                $('form#partForm textarea#edit-content').val(data.result.content).focus();
            },
        });
    }
}

function savePart() {
    const part = {
        id: $('form#partForm input#part-id').val(),
        name: $('form#partForm input#edit-name').val(),
        content: $('form#partForm textarea#edit-content').val(),
    };
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/update_part.php',
            headers: { 'X-Auth-Token': token },
            data: part,
            type: 'POST',
            success: function(response) { 
                const data = JSON.parse(response.substring(response.indexOf('{')));
                if (data.success) {
                    $('form#partForm input, form#partForm textarea').val(null);
                    loadPartsList();
                    showMessage(MSG.SUCCESS, data.message);
                }
                else {
                    showMessage(MSG.FAILURE, data.message);
                }
            },
        });
    }
}

function loadMenusList() {
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/get_menus.php',
            headers: { 'X-Auth-Token': token },
            type: 'GET',
            success: function(response) { 
                $('div#menusList table tbody').html(null);
                const data = JSON.parse(response.substring(response.indexOf('{')));
                $.each(data.result, function(idx, item) {
                    const status = parseInt(item.active) ? 'normal' : 'locked';
                    const $row = $('<tr class="'+status+'"><th scope="row">'+item.id+'</th><td>'+item.caption+'</td><td>'+item.sequence+'</td><td class="action"><button class="btn btn-sm btn-warning" onclick="fillMenu('+item.id+')">Edytuj</button><button class="btn btn-sm btn-danger" onclick="removeConfirm(\'menus\', '+item.id+')">Usuń</button></td></tr>');
                    $('div#menusList table tbody').append($row);
                });
            },
        });
    }
}

function addMenu() {
    $('form#menuForm input#menu-id').val(0);
    $('form#menuForm input#edit-caption').val(null);
    $('form#menuForm input#edit-sequence').val(null);
    $('form#menuForm input#edit-active-on').prop('checked', true); 
    $('form#menuForm input#edit-active-off').prop('checked', null);
    $('form#menuForm input#edit-caption').focus();
}

function fillMenu(id) {
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/get_menu.php?id=' + id,
            headers: { 'X-Auth-Token': token },
            type: 'GET',
            success: function(response) { 
                const data = JSON.parse(response.substring(response.indexOf('{')));
                $('form#menuForm input#menu-id').val(data.result.id);
                $('form#menuForm input#edit-caption').val(data.result.caption);
                $('form#menuForm input#edit-sequence').val(data.result.sequence);
                $('form#menuForm input#edit-active-on').prop('checked', parseInt(data.result.active) ? true : null); 
                $('form#menuForm input#edit-active-off').prop('checked', parseInt(data.result.active) ? null : true);
                $('form#menuForm input#edit-caption').focus();
            },
        });
    }
}

function saveMenu() {
    const menu = {
        id: $('form#menuForm input#menu-id').val(),
        caption: $('form#menuForm input#edit-caption').val(),
        sequence: $('form#menuForm input#edit-sequence').val(),
        active: $('form#menuForm input#edit-active-on').is(':checked') ? 1 : 0,
    };
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + (menu.id == 0 ? 'admin/add_menu.php' : 'admin/update_menu.php'),
            headers: { 'X-Auth-Token': token },
            data: menu,
            type: 'POST',
            success: function(response) { 
                const data = JSON.parse(response.substring(response.indexOf('{')));
                if (data.success) {
                    $('form#menuForm input').val(null),
                    loadMenusList();
                    showMessage(MSG.SUCCESS, data.message);
                }
                else {
                    showMessage(MSG.FAILURE, data.message);
                }
            },
        });
    }
}

function removeMenu(id) {
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/remove_menu.php?id=' + id,
            headers: { 'X-Auth-Token': token },
            type: 'GET',
            success: function(response) { 
                const data = JSON.parse(response.substring(response.indexOf('{')));
                if (data.success) {
                    loadMenusList();
                    showMessage(MSG.SUCCESS, data.message);
                }
                else {
                    showMessage(MSG.FAILURE, data.message);
                }
            },
        });
    }
}

function loadSectionsList() {
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/get_sections.php',
            headers: { 'X-Auth-Token': token },
            type: 'GET',
            success: function(response) { 
                $('div#sectionsList table tbody').html(null);
                const data = JSON.parse(response.substring(response.indexOf('{')));
                $.each(data.result, function(idx, item) {
                    const status = parseInt(item.active) ? 'normal' : 'locked';
                    const $row = $('<tr class="'+status+'"><th scope="row">'+item.id+'</th><td>'+item.caption+'</td><td>'+item.sequence+'</td><td class="action"><button class="btn btn-sm btn-warning" onclick="fillSection('+item.id+')">Edytuj</button><button class="btn btn-sm btn-danger" onclick="removeConfirm(\'sections\', '+item.id+')">Usuń</button></td></tr>');
                    $('div#sectionsList table tbody').append($row);
                });
            },
        });
    }
}

function addSection() {
    $('form#sectionForm select#link-id').html(null);
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/get_menus.php',
            headers: { 'X-Auth-Token': token },
            type: 'GET',
            success: function(response) { 
                const data = JSON.parse(response.substring(response.indexOf('{')));
                $.each(data.result, function(idx, item) {
                    const $option = $('<option value="'+item.id+'">'+item.caption+'</option>');
                    $('form#sectionForm select#link-id').append($option);
                });
                $('form#sectionForm input#section-id').val(0);
                $('form#sectionForm select#link-id').val(0);
                $('form#sectionForm textarea#edit-contents').val(null);
                $('form#sectionForm input#edit-order').val(0);
                $('form#sectionForm input#edit-visible-on').prop('checked', true); 
                $('form#sectionForm input#edit-visible-off').prop('checked', null);
                $('form#sectionForm select#link-id').focus();            
            },
        });
    }
}

function fillSection(id) {
    $('form#sectionForm select#link-id').html(null);
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/get_menus.php',
            headers: { 'X-Auth-Token': token },
            type: 'GET',
            success: function(response) { 
                const data = JSON.parse(response.substring(response.indexOf('{')));
                $.each(data.result, function(idx, item) {
                    const $option = $('<option value="'+item.id+'">'+item.caption+'</option>');
                    $('form#sectionForm select#link-id').append($option);
                });
                $.ajax({
                    url: API.url + 'admin/get_section.php?id=' + id,
                    headers: { 'X-Auth-Token': token },
                    type: 'GET',
                    success: function(response) { 
                        const data = JSON.parse(response.substring(response.indexOf('{')));
                        $('form#sectionForm input#section-id').val(data.result.id);
                        $('form#sectionForm select#link-id').val(data.result.menu_id);
                        $('form#sectionForm textarea#edit-contents').val(data.result.content);
                        $('form#sectionForm input#edit-order').val(data.result.sequence);
                        $('form#sectionForm input#edit-visible-on').prop('checked', parseInt(data.result.active) ? true : null); 
                        $('form#sectionForm input#edit-visible-off').prop('checked', parseInt(data.result.active) ? null : true);
                        $('form#sectionForm select#link-id').focus();
                    },
                });        
            },
        });
    }
}

function saveSection() {
    const section = {
        id: $('form#sectionForm input#section-id').val(),
        menu_id: $('form#sectionForm select#link-id').val(),
        content: $('form#sectionForm textarea#edit-contents').val(),
        sequence: $('form#sectionForm input#edit-order').val(),
        active: $('form#sectionForm input#edit-visible-on').is(':checked') ? 1 : 0,
    };
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + (section.id == 0 ? 'admin/add_section.php' : 'admin/update_section.php'),
            headers: { 'X-Auth-Token': token },
            data: section,
            type: 'POST',
            success: function(response) { 
                const data = JSON.parse(response.substring(response.indexOf('{')));
                if (data.success) {
                    $('form#sectionForm input, form#sectionForm textarea, form#sectionForm select').val(null);
                    loadSectionsList();
                    showMessage(MSG.SUCCESS, data.message);
                }
                else {
                    showMessage(MSG.FAILURE, data.message);
                }
            },
        });
    }
}

function removeSection(id) {
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/remove_section.php?id=' + id,
            headers: { 'X-Auth-Token': token },
            type: 'GET',
            success: function(response) { 
                const data = JSON.parse(response.substring(response.indexOf('{')));
                if (data.success) {
                    $('form#sectionForm input, form#sectionForm textarea, form#sectionForm select').val(null);
                    loadSectionsList();
                    showMessage(MSG.SUCCESS, data.message);
                }
                else {
                    showMessage(MSG.FAILURE, data.message);
                }
            },
        });
    }
}

function loadImagesList() {
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/get_images.php',
            headers: { 'X-Auth-Token': token },
            type: 'GET',
            success: function(response) { 
                $('div#imagesList table tbody').html(null);
                const data = JSON.parse(response.substring(response.indexOf('{')));
                $.each(data.result, function(idx, item) {
                    const $row = $('<tr><th scope="row">'+item.id+'</th><td>'+item.filename.substring(0, 20)+'</td><td>'+item.type.replace('image/', '')+'</td><td>'+parseInt(item.size / 1024)+'KB</td><td class="action"><button class="btn btn-sm btn-warning" onclick="showImage('+item.id+')">Pokaż</button><button class="btn btn-sm btn-danger" onclick="removeConfirm(\'images\', '+item.id+')">Usuń</button></td></tr>');
                    $('div#imagesList table tbody').append($row);
                });
            },
        });
    }
}

function uploadImage() {
    var file_data = $('#fileToUpload').prop('files')[0];
    var form_data = new FormData();
    form_data.append('file', file_data);
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/upload_image.php',
            headers: { 'X-Auth-Token': token },
            dataType: 'text', 
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'POST',
            success: function (response) {
                const data = JSON.parse(response.substring(response.indexOf('{')));
                if (data.success) {
                    loadImagesList();
                    showImage(data.image.id);
                    showMessage(MSG.SUCCESS, data.message);
                }
                else {
                    showMessage(MSG.FAILURE, data.message);
                }
            },
        });
    }
}

function showImage(id) {
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/get_image.php?id=' + id,
            headers: { 'X-Auth-Token': token },
            type: 'GET',
            success: function(response) { 
                const data = JSON.parse(response.substring(response.indexOf('{')));
                $('form#imageForm span#imageId').text(data.result.id);
                $('form#imageForm span#imageName').text(data.result.filename);
                $('form#imageForm span#previewImage img').attr('src', '../upload/' + data.result.filename);
            },
        });
    }
}

function removeImage(id) {
    const token = localStorage.getItem(API.accessToken);
    if (token) {
        $.ajax({
            url: API.url + 'admin/remove_image.php?id=' + id,
            headers: { 'X-Auth-Token': token },
            type: 'GET',
            success: function(response) { 
                const data = JSON.parse(response.substring(response.indexOf('{')));
                if (data.success) {
                    loadImagesList();
                    $('form#imageForm span#imageId').text(null);
                    $('form#imageForm span#imageName').text(null);
                    $('form#imageForm span#previewImage img').attr('src', null);
                    showMessage(MSG.SUCCESS, data.message);
                }
                else {
                    showMessage(MSG.FAILURE, data.message);
                }
            },
        });
    }
}

function removeConfirm(table, id) {
    $('div#removeConfirm').modal();
    $('div#removeConfirm button#confirm').unbind('click').on('click', function() {
        if (table == 'images') {
            removeImage(id);
        }
        if (table == 'users') {
            removeUser(id);
        }
        if (table == 'menus') {
            removeMenu(id);
        }
        if (table == 'sections') {
            removeSection(id);
        }
    });
}

function showMessage(type, message) {
    $('div#messages').show();
    $('div.alert').hide();
    if (type == MSG.SUCCESS) {
        $('div.alert-success').text(message).show();
    }
    if (type == MSG.FAILURE) {
        $('div.alert-danger').text(message).show();
    }
    setTimeout(function() { $('div.alert').hide(); $('div#messages').hide(); }, MSG.delay);
}

$(document).ready(function() {
    page.getReady(function(ready) {
        if (ready) {
            page.getPart('title');
            page.getPart('description');
            page.getPart('author');
            page.getPart('style');
            page.getPart('script');
            page.checkAuthorization();
        }
        else {
            page.showInstall();
        }
    });
});
