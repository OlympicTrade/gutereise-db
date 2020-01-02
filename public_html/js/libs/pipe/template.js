let Template = function () {
    let obj = this;
    let options = $.parseJSON($.cookie('template'));
    let mainNav    = null;
    let moduleNav = null;

    if(!options) {
        options = {
            nav: {
                main:   'wire',
                module: 'wire',
            }
        }
    }

    let updateTemplate = function () {
        let navWidth = mainNav.outerWidth() + moduleNav.outerWidth();
        $('#container').css({marginLeft: navWidth});
    };

    let save = function (options) {
        $.cookie('template', JSON.stringify(options), {expires: 365, path: "/"});
    };

    let sizeSwitcher = function(menu, el) {
        $('.size-switcher', el).on('click', function () {
            if( options.nav[menu] === 'wire') {
                el.addClass('slim').removeClass('wire');
                options.nav[menu] = 'slim';
            } else {
                el.addClass('wire').removeClass('slim');
                options.nav[menu] = 'wire';
            }
            save(options);
            setTimeout(function () {
                updateTemplate();
            }, 200);
        });
    };

    let initMainNav = function() {
        $('.arr', mainNav).on('click', function (e) {
            e.stopPropagation();
            $(this).closest('li')
                .toggleClass('open')
                .siblings()
                .removeClass('open');

            return false;
        });
    };
    
    let initModuleNav = function() {

    };

    this.getModuleMenu = function() {
        return moduleNav;
    };

    this.getMainMenu = function() {
        return mainNav;
    };

    this.init = function() {
        mainNav = $('#nav .main');
        moduleNav = $('#nav .module');
        updateTemplate();
        sizeSwitcher('main', mainNav);
        sizeSwitcher('submenu', moduleNav);
        initMainNav();
        initModuleNav();

        $('.menu', mainNav).css({height: mainNav.height() - $('.widget.search', mainNav).outerHeight()});
    };
};

$.pipe.template = new Template();