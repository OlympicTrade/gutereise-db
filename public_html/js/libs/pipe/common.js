var fn = {};
$.pipe = fn;

function dd(data) {
    console.log(data);
}
function onLoad(callback) {
    $(function () {
        setTimeout(function () {
            callback();
        }, 2);
    })
}

/* Loading animation */
fn.loadingHtml = function(box) {
    var html =
        '<div class="loading">' +
        '<i class="fas fa-circle-notch fa-spin"></i>' +
        '</div>';

    box.empty().html(html);
};

/* cpp (int) */
fn.int = function(str, def) {
    var int = parseInt(str);
    def = def ? def : 0;
    return isNaN(int) ? def : int;
};

/* Copy to clipboard */
fn.copy = function(el) {
    var message = new Message();
    message.setMessage({icon: 'far fa-copy', text: 'Скопировано'});

    var tmp = $("<input>");

    $("body").append(tmp);

    tmp.val($(el).text()).select();

    document.execCommand("copy");

    tmp.remove();
};

fn.trim = function(str, char, dir) {
    var result;

    if($.inArray(char, ['[', ']', '(', ')', '+', '.']) !== -1) {
        char = '\\' + char;
    }

    switch (dir) {
        case 'left':
            result = str.replace(new RegExp('^' + char + '+', 'g'), '');
            break;
        case 'right':
            result = str.replace(new RegExp('['+ char + ']+$', 'g'), '');
            break;
        default:
            result = str.replace(new RegExp('^[' + char + ']+|[' + char + ']+$', 'g'), '');
            break;
    }
    return result;
};

fn.formElement = function(type, options) {
    var el;
    options = $.extend({
        empty: null,
        value: '',
    }, options);

    switch (type) {
        case 'text':
            options = $.extend({
                class: 'std-input',
            }, options);
            el = $('<input type="text" class="' + options.class + '" value="' + options.value + '">');

            if(options.empty !== null) {
                el.attr('placeholder', options.empty);
            }
            break;

        case 'hidden':
            el = $('<input type="hidden" class="' + options.class + '" value="' + options.value + '">');
            el.val(options.value);
            break;

        case 'select':
            options = $.extend({
                class:   'std-select',
                options: {},
            }, options);

            el = $('<select class="' + options.class + '">');

            if(options.empty !== null) {
                $('<option value="">' + options.empty + '</option>').appendTo(el);
            }

            $.each(options.options, function (key, val) {
                var opt = $('<option value="' + key + '">' + val + '</option>');

                if(key === options.value) {
                    opt.attr('selected', 'selected');
                }

                opt.appendTo(el);
            });

            break;

        case 'time':
            options = $.extend({
                class: 'std-select',
                name:  '',
                min:   '00:00',
                max:   '12:00',
                interval: 15,
            }, options);

            var min = options.min.split(':');
            var minH = parseInt(fn.trim(min[0], '0', 'left'));
            var minM = parseInt(fn.trim(min[1], '0', 'left'));

            minH = isNaN(minH) ? 0 : minH;
            minM = isNaN(minM) ? 0 : minM;

            var max = options.max.split(':');
            var maxH = parseInt(fn.trim(max[0], '0', 'left'));
            var maxM = parseInt(fn.trim(max[1], '0', 'left'));
            maxH = isNaN(maxH) ? 0 : maxH;
            maxM = isNaN(maxM) ? 0 : maxM;

            el = $('<select class="' + options.class + '" data-type="time"></select>');

            if(options.empty !== null) {
                $('<option value="">' + options.empty + '</option>').appendTo(el);
            }

            function pad(str, max) {
                str = str.toString();
                return str.length < max ? pad('0' + str, max) : str;
            }

            for(var h = minH; h <= maxH; h++) {
                var mS = h === minH ? minM : 0;
                var mF = h === maxH ? maxM : 60;

                if(h === minH) m = minM;
                if(h === maxH) m = maxM;

                for(var m = mS; m < mF; m += options.interval) {
                    var time = pad(h, 2) + ':' + pad(m, 2);
                    var val = time + ':00';
                    var opt = $('<option value="' + time + ':00">' + time + '</option>');

                    if(val === options.value) {
                        opt.attr('selected', 'selected');
                    }

                    opt.appendTo(el);
                }
            }

            var time = pad(maxH, 2) + ':' + pad(maxM, 2);
            $('<option value="' + time + ':00">' + time + '</option>').appendTo(el);

            break;
    }
    el.attr('name', options.name);

    return el;
};

/**
 * @param box
 * @returns {FormData}
 */
fn.serializeForm = function(box, files) {
    if(!files) {
        var data = {};
        $('input, textarea, select', box).each(function() {
            var el = $(this);
            var name = el.attr('name');

            if(!name) { return; }

            if(el.attr('type') == 'checkbox') {
                if(el.is(':checked')) {
                    data[name] = el.val();
                }

                return;
            }

            data[name] = el.val();
        });

        return data;
    }

    var data = new FormData();

    $('input[type="file"]', box).each(function() {
        data.append("file", $(this).prop('files')[0]);
    });

    $('input:not([type="file"]), textarea, select', box).each(function() {
        var el = $(this);
        var name = el.attr('name');

        if(!name) { return; }

        if(el.attr('type') === 'checkbox') {
            if(el.is(':checked')) {
                data.append(name, el.val());
            }
            return;
        }

        data.append(name, el.val());
    });

    return data;
};
/*
fn.serializeArray = function(box, files) {
    var data = {};
    $('input, textarea, select', box).each(function() {
        var el = $(this);
        var name = el.attr('name');

        if(!name) { return; }

        if(el.attr('type') == 'checkbox') {
            if(el.is(':checked')) {
                data[name] = el.val();
            }

            return;
        }

        data[name] = el.val();
    });

    return data;
};
*/
fn.serializeString = function(box) {
    var str = '';
    $.each(fn.serializeArray(box), function(key, val) {
        str += key + '=' + val + '&';
    });
    return encodeURI(str);
};

/* Price
 10000 -> 10 000
 */
fn.price = function(price, sign) {
    var priceStr = new String(price);

    priceStr = priceStr.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');

    switch (sign) {
        case 'rub':
            priceStr += ' <i class="fal fa-ruble-sign"></i>';
            break;
        case 'eur':
            priceStr += ' <i class="fal fa-euro-sign"></i>';
            break;
        case 'usd':
            priceStr += ' <i class="fal fa-dollar-sign"></i>';
            break;
        default:
            break;
    }

    return priceStr;
};

fn.scrollTo = function(el, duration, options) {
    options = $.extend({
        offsetTop     : -50,
        easing        : 'swing'
    }, options);

    if(!duration) {
        duration = 400;
    }

    $('html, body').animate({
        scrollTop: el.offset().top + options.offsetTop,
    }, duration, options.easing);
};

/* Url
 var url = new Url();
 url.setPath('/user/');
 url.setParams({id: 5});
 url.setHash({test: 'word'});
 url.redirect();
 */
var Url = function () {
    this.path = '',
        this.get = {},
        this.hash = {},

        this.init = function () {
            var url = {};

            var tmp = location.href.split('?');
            if (tmp[1]) {
                tmp = tmp[1].split('#');
                url.get = tmp[0];
                url.hash = tmp[1];
            }

            url.hash = location.hash.substr(1);
            url.path = '/' + tmp[0].replace(/^http:\/\/[a-zA-Z1-9\-\.]*\//, '');

            this.path = url.path;

            if (url.get) {
                var getParams = url.get.split('&');
                for (i = 0; i < getParams.length; i++) {
                    var tmp = getParams[i].split('=');
                    this.get[tmp[0]] = tmp[1];
                }
            }

            if (url.hash) {
                var hashParams = url.hash.split('&');
                for (i = 0; i < hashParams.length; i++) {
                    var tmp = hashParams[i].split('=');
                    this.hash[tmp[0]] = tmp[1];
                }
            }

            return this;
        };

    this.setPath = function (path) {
        this.path = path;
        return this;
    };

    this.setParams = function (params, value) {
        if (typeof params === "object") {
            this.get = $.extend(this.get, params);
        } else {
            this.get[params] = value;
        }

        return this;
    };

    this.getParams = function (key) {
        if (key) {
            return this.get[key];
        } else {
            return this.get;
        }
    };

    this.clearParams = function () {
        this.get = {};
        return this;
    };

    this.setHash = function (params, value) {
        if (typeof params === "object") {
            this.hash = $.extend(this.hash, params);
        } else {
            this.hash[params] = value;
        }

        location.hash = '#' + this.generateHash();

        return this;
    };

    this.getHash = function (key) {
        if (key) {
            return this.hash[key];
        } else {
            return this.hash;
        }
    };

    this.clearHash = function () {
        this.hash = {};
        return this;
    };

    this.generateParams = function () {
        var getParams = '';
        var first = true;

        for (var param in this.get) {
            if (this.get[param]) {
                getParams += (first ? '' : '&') + param + '=' + this.get[param];
                first = false;
            }
        }

        return getParams;
    };

    this.generateHash = function () {
        var hashParams = '';
        var first = true;

        for (var param in this.hash) {
            if (this.hash[param]) {
                hashParams += (first ? '' : '&') + param + '=' + this.hash[param];
                first = false;
            }
        }

        return hashParams;
    };

    this.getUrl = function () {
        var url = this.path;

        if (getParams = this.generateParams()) {
            url += '?' + getParams;
        }

        if (hashParams = this.generateHash()) {
            url += '#' + hashParams;
        }

        return url;
    };

    this.redirect = function () {
        location.href = this.getUrl();
    };
};

fn.url = function () {
    return new Url();
};

/* Tabs */
var Tabs = function (el, options) {
    this.el = el;

    this.options = $.extend({
        relatedTabs: false,
        historyMode: false,
        afterLoad:   false,
        onInit:      false,
        initTab:     false,
    }, options);

    this.header = this.el.children('.tabs-header');
    this.body = this.el.children('.tabs-body');
    this.addBtn = $('.add-tab', this.header);
    var obj = this;

    this.clear = function () {
        obj.header.children('.tab').each(function () {
            if($(this).hasClass('btn')) return;
            obj.delTab($(this).data('tab'));
        });
    };

    this.delTab = function (tabId) {
        var hTab = obj.header.children('.tab[data-tab="' + tabId + '"]');
        var bTab = obj.body.children('.tab[data-tab="' + tabId + '"]');

        options.delTab(obj, hTab, bTab);
        hTab.remove();
        bTab.remove();
        obj.addBtn.prev().trigger('activate');
    };

    this.getTab = function (tabName) {
        return {
            header: obj.header.children('.tab[data-tab="' + tabName + '"]'),
            body: obj.body.children('.tab[data-tab="' + tabName + '"]'),
        };
    };

    this.getActiveTab = function () {
        return {
            header: obj.header.children('.tab.active'),
            body: obj.body.children('.tab.active'),
        };
    };

    this.getFirstTab = function () {
        return {
            header: obj.header.children('.tab:first-child'),
            body: obj.body.children('.tab:first-child'),
        };
    };

    this.getLastTab = function () {
        return {
            header: obj.header.children('.tab:not(".add-btn"):last-child'),
            body: obj.body.children('.tab:not(".add-btn"):last-child'),
        };
    };

    this.addTab = function (data) {
        var tabId = $('.tab', obj.header).length;
        var tabOpts = options.addTab(obj, tabId, (data === undefined ? {} : data));
        var tabName = 'new-' + tabOpts.id;

        var hTab = $('<div class="tab" data-tab="' + tabName + '" data-tab-id="' + tabId + '">' + tabOpts.header + '</div>');
        var bTab = $('<div class="tab" data-tab="' + tabName + '" data-tab-id="' + tabId + '">' + tabOpts.body + '</div>');

        obj.addBtn.before(hTab);
        obj.body.append(bTab);

        if(options.onLoad) {
            options.onLoad(bTab);
        }

        if(options.initTab) {
            options.initTab(bTab);
        }

        hTab.on('activate click', function() {
            obj.setActive($(this));
        }).trigger('activate');

        hTab.on('dblclick', function() {
            if (confirm('Удалть вкладку')) {
                obj.delTab($(this).data('tab'));
            }
        });

        return obj.getTab(tabName);
    };

    this.init = function () {
        var options = obj.options;

        if(options.initTab) {
            //options.initTab(el);
        }

        obj.addBtn.addClass('btn');

        this.el.children('.tabs-header, .tabs-body').children('.tab').each(function() {
            var tab = $(this);
            if(!tab.data('tab')) {
                tab.attr('data-tab', (parseInt(tab.index() + 1)));
            }
        });

        obj.body.children('.tab').each(function() {
            var tab = $(this);
            if(options.initTab) {
                options.initTab(tab);
            }
        });

        obj.header.children('.tab').on('activate click', function () {
            var tab = $(this);

            if(tab.hasClass('btn')) {
                return;
            }

            if(tab.data('tab') || tab.data('load')) {
                obj.setActive(tab);
                return false;
            }
        });

        var active = obj.header.children('.tab.active');
        if (!active.length) {
            var tabName = fn.url().getHash(this.el.data('name'));
            if (tabName) {
                active = obj.header.children('.tab[data-tab="' + tabName + '"]').addClass('active');
            }
        }
        if (!active.length) {
            active = obj.header.children('.tab:eq(0)').addClass('active');
        }

        obj.setActive(active);

        if(obj.addBtn.length) {
            obj.addBtn.on('click', function() {
                obj.addTab();
            });
        }

        obj.header.children('.tab').on('dblclick', function() {
            if($(this).hasClass('btn')) {
                return;
            }

            if (confirm('Удалть вкладку')) {
                obj.delTab($(this).data('tab'));
            }
        });
    };

    this.setActive = function (tab) {
        //if(tab.hasClass('add-tab')) return;
        if(tab.hasClass('btn')) return;

        var header = this.el.children('.tabs-header');
        var body = this.el.children('.tabs-body');

        var headerTab = header.children('.tab[data-tab="' + tab.attr('data-tab') + '"]');
        var bodyTab = body.children('.tab[data-tab="' + tab.attr('data-tab') + '"]');

        if(!bodyTab.length) {
            bodyTab = $('<div class="tab"></div>');
            bodyTab.attr('data-tab', tab.attr('data-tab'));
            bodyTab.appendTo(body);
        }

        if(headerTab.hasClass('active') && !bodyTab.is(':empty')) {
            bodyTab.addClass('active');
            return;
        }

        headerTab.addClass('active').siblings().removeClass('active');
        bodyTab.addClass('active').siblings().removeClass('active');

        var options = this.options;

        if (headerTab.attr('data-load')) {
            var url = headerTab.attr('data-load');

            if(this.options.historyMode) {
                var disablePushState = this.options.disablePushState;

                $.ajax({
                    url: url,
                    dataType: 'json',
                    success: function(resp) {
                        bodyTab.html(resp.html);
                        if(!disablePushState) {
                            History.replaceState({}, resp.meta.title, url);
                        }

                        if(options.afterLoad) {
                            options.afterLoad();
                        }
                    }
                });
            } else {
                bodyTab.load(url, function () {
                    headerTab.attr('data-load', null);
                });
            }
        }

        if (this.el.attr('data-name')) {
            fn.url().setHash(this.el.attr('data-name'), headerTab.attr('data-tab'));
        }

        if(this.options.relatedTabs) {
            $(this.options.relatedTabs + ' > .tabs-header > .tab[data-tab="' + tab.attr('data-tab') + '"]').trigger('activate');
        }
    };

    this.init();
};

$.fn.tabs = function (options) {
    $(this).each(function () {
        new Tabs($(this), options);
    });
};

$.fn.tabs = function (options) {
    var initDE = function(el) {
        var sl = el.data('aptero-tabs');

        if (sl === undefined || sl === '') {
            sl = new Tabs(el, options);
            el.data('aptero-tabs', sl);
        }

        return sl;
    };

    if($(this).length === 1) {
        return initDE($(this));
    }

    $(this).each(function () {
        initDE($(this));
    });

    return this;
};

/* SmartList */
var SmartList = function(el, options) {
    this.el = el;
    this.changeTimer = null;
    var list = $('.list', this.el);
    var pattern = $('.pattern', this.el);
    var insetId = 0;

    this.options = $.extend({
        replace: null,
    }, options);

    var obj = this;

    this.on = function(event, fn) {
        $(this).on(event, fn);
    };

    this.trigger = function(event, opts) {
        $(this).trigger(event, opts);
    };

    $('input, select, textarea', pattern).each(function () {
        var el = $(this);
        el.attr('name', '');
    });

    var getRow = function () {
        insetId++;
        var row = pattern.clone();
        row.removeClass('pattern');

        $('input, select, textarea', row).each(function () {
            var el = $(this);
            var name = el.data('name').replace(/_ID_/, 'new-' + insetId);

            if(obj.options.replace !== null) {
                name = obj.options.replace(name, insetId);
            }

            el.attr('name', name);
            row.attr('data-name', '');
        });

        return row;
    };

    this.updateSort = function() {
        var sortId = 0;
        $('.row', list).each(function () {
            $(this).find('input.sort').val(sortId);
            sortId++;
        });

        if(sortId === 1) {
            $('.row.first', list).addClass('active');
        } else {
            $('.row.first', list).removeClass('active');
        }
    };

    list.on('click', '.del', function () {
        $(this).closest('.row').remove();
        obj.updateSort();
        obj.trigger('change', [obj]);
    });

    list.on('click', '.delimiter', function () {
        var row = getRow();
        $(this).closest('.row').after(row);
        obj.updateSort();
        obj.trigger('add', [row]);
        obj.trigger('change', [obj]);
    });

    obj.clear = function(vals) {
        $('.row:not(.first)', list).remove();
        insetId = 0;

        return obj;
    };

    obj.addRow = function(vals) {
        var row = getRow();

        if(vals !== undefined) {
            var i = 0;
            $('input, select, textarea', $('.col', row)).each(function () {
                $(this).val(vals[i]);
                i++;
            });
        }

        row.appendTo(list);
        obj.updateSort();
        obj.trigger('add', [row]);
    };

    obj.updateSort();

    list.sortable({
        placeholder: 'sort-placeholder',
        handle: '.sort',
        revert: true,
        update: function (event, ui) {
            obj.updateSort();
        }
    });

    list.on('change keyup', 'input, select, textarea', function () {
        clearTimeout(obj.changeTimer);
        obj.changeTimer = setTimeout(function () {
            obj.trigger('change', [obj]);
        }, 200);
    });

    return obj;
};


$.fn.smartlist = function (options) {
    var initSl = function(el) {
        var sl = el.data('smartlist');

        if (sl === undefined || sl === '') {
            sl = new SmartList(el, options);
            el.data('smartlist', sl);
        }

        return sl;
    };

    if($(this).length === 1) {
        return initSl($(this));
    }

    $(this).each(function () {
        initSl($(this));
    });

    return this;
};
