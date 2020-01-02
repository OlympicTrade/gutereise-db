var Calendar = function (box, options) {
    this.box      = box;
    var controls = $('.controls', this.box);
    var calendar = $('.calendar', this.box);
    var cDate = calendar.data('date');
    var cPage = calendar.children('.page');

    this.options = $.extend({
        initPage:     false,
        initControls: false,
        loadPageData: false,
        url:  'url to load calendar page',
    }, options);

    var obj = this;

    var yearCtrl  = $('.year', controls);
    var monthCtrl = $('.month', controls);

    var startX = 0;
    var dragActiv = false;

    var slidingTimer;
    var initPage = function (page) {
        if(obj.options.initPage) {
            obj.options.initPage(page);
        }

        var setDefault = function() {
            page.removeClass('drag');
            dragActiv = false;
            page.off('mousemove, mouseup');
        };

        page.on('mousedown', function (e) {
            e.stopPropagation();
            startX = e.pageX;
            dragActiv = true;
            page.css({left: 0});
            page.addClass('drag');

            clearTimeout(slidingTimer);
            slidingTimer = setTimeout(function() {
                page.css({left: 0});
                setDefault();
            }, 2000);

            page.on('mousemove', function (e) {
                if(dragActiv) {
                    page.css({left: -(startX - e.pageX)});
                }
            });

            $(document).on('mouseup', function (e) {
                if(!dragActiv) {
                    return;
                }

                var diff = startX - e.pageX;

                if(Math.abs(diff) / ($(window).width() / 2) > 0.6) {
                    if(diff < 0) {
                        cPage.addClass('left');
                        $('.prev', controls).trigger('click');
                    } else {
                        cPage.addClass('right');
                        $('.next', controls).trigger('click');
                    }
                } else {
                    page.css({left: 0});
                }

                setDefault();
            });
        });
    };

    var loadTimer;
    var pageInitTimer;
    this.loadPage = function(page, date, shift) {
        clearTimeout(loadTimer);
        loadTimer = setTimeout(function() {
            if(!date)  date = cDate;
            if(!shift) shift = 'none';

            var nPage = $('<div class="page new"><div class="loading"><i class="fa fa-spin fa-spinner"></i></div></div>');

            nPage.prependTo(calendar);

            setTimeout(function() {
                nPage.removeClass('new');

                clearTimeout(pageInitTimer);
                pageInitTimer = setTimeout(function() {
                    cPage.remove();
                    cPage = nPage;
                }, 500);
            }, 1);

            var loadData = {
                date:  date,
                shift: shift
            };

            if(obj.options.loadPageData) {
                obj.options.loadPageData(loadData);
            }

            $.ajax({
                url:    obj.options.url,
                method: 'post',
                data: loadData,
                success: function (resp) {
                    nPage.html(resp.html);
                    initPage(nPage);
                    calendar.css({minHeight: nPage.outerHeight()});

                    yearCtrl.val(resp.year);
                    monthCtrl.val(resp.month);
                    cDate = resp.date;
                }
            });
        }, 200);
    };

    this.initControls = function() {
        $('.year, .month', controls).on('change', function () {
            cPage.addClass('fade');
            var date = yearCtrl.val() + '-' + monthCtrl.val() + '-01';
            obj.loadPage(calendar, date);
        });

        $('.prev', controls).on('click', function () {
            cPage.addClass('right');
            obj.loadPage(calendar, null, 'prev');
        });

        $('.next', controls).on('click', function () {
            cPage.addClass('next');
            obj.loadPage(calendar, null, 'next');
        });

        if(obj.options.initControls) {
            obj.options.initControls(controls);
        }
    };

    this.initControls(calendar);
    this.loadPage(calendar);
};

$.fn.calendar = function (options) {
    var init = function(el) {
        var sl = el.data('calendar');

        if (sl === undefined || sl === '') {
            sl = new Calendar(el, options);
            el.data('calendar', sl);
        }

        return sl;
    };

    if($(this).length === 1) {
        return init($(this));
    }

    $(this).each(function () {
        init($(this));
    });

    return this;
};