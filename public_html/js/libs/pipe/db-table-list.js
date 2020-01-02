var DbTableList = function (box, options) {
    var obj = this;
    var module  = globals.module;
    var section = globals.section;

    options = $.extend({
        callbacks: {
            beforeLoad: function (query) {
                return true;
            },
        },
    }, options);

    this.setOptions = function (newOptions) {
        options = $.extend(options, newOptions);
    };

    var initTable = function() {
        var rows = $('.data-row', box);

        $('td', rows).on('click', function() {
            if($(this).hasClass('btns')) return;

            var row = $(this).parent();

            location.href = getUrl({module: module, section: section, action: 'edit', id: row.data('id')});
        });

        $('.btn', rows).on('click', function() {
            var row = $(this).closest('.data-row');
            var id = row.data('id');

            if($(this).hasClass('del')) {
                if(confirm('Вы уверены, что хотите удалить запись?')) {
                    $.ajax({
                        url: getUrl({module: module, section: section, action: 'delete', id: id}),
                        type: 'post',
                        data: {},
                        success: function(resp) {
                            if(!parseInt(resp['status'])) {
                                alert('При удалении произошла ошибка');
                            } else {
                                row.remove();
                            }
                        }
                    });
                }
            }

            if($(this).hasClass('copy')) {
                location.href = getUrl({module: module, section: section, action: 'copy', id: id});
            }
        });
    };

    var initSidebar = function() {
        var sidebar = $.pipe.template.getModuleMenu();
        var searchBox  = $('.search', sidebar);
        var searchList = $('.search-help', sidebar);

        //New table item
        $('.item-add', sidebar).on('click', function(){
            location.href = getUrl({module: sidebar.data('module'), section: sidebar.data('section'), action: 'edit'});
        });

        //Search help queries
        var initQueryRow = function(row) {
            var queryTxt = $('span', row).text();

            $('.del', row).on('click', function (e) {
                e.stopPropagation();
                $.ajax({
                    url: getUrl({module: module, section: section, action: 'search-queries'}),
                    type: 'post',
                    data: {module: module, section: section, query: queryTxt, action: 'del'},
                    success: function(resp) {
                        row.remove();
                    }
                });
            });

            row.on('click', function () {
                $('input', searchBox).val(queryTxt).trigger('keyup');
            });
        };
        $('.row', searchList).each(function () {
            initQueryRow($(this));
        });

        $('.add', searchBox).on('click', function () {
            var query = $('input', searchBox).val();
            if(query === '') {
                alert('Введите текст запроса'); return;
            }

            $.ajax({
                url: getUrl({module: module, section: section, action: 'search-queries'}),
                type: 'post',
                data: {module: module, section: section, query: query, action: 'add'},
                success: function(resp) {
                    if(resp.error) {
                        alert(resp.error);
                    } else {
                        var row = $(
                            '<div class="row">'+
                                '<span>' + $('input', searchBox).val() + '</span>'+
                                '<i class="del"></i>'+
                            '</div>');
                        row.appendTo(searchList);
                        initQueryRow(row);
                    }
                }
            });
        });

        //Search loading
        var searchTimer = null;
        $('input', searchBox).on('keyup', function(){
            var query = $(this).val();

            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                if(!options.callbacks.beforeLoad(query)) return;

                $.ajax({
                    url: getUrl({module: module, section: section, action: 'list'}),
                    type: 'post',
                    data: {query: query},
                    success: function(resp) {
                        box.empty().html(resp.html);
                        initTable();
                    }
                });
            }, 200);
        });
    };

    initTable();
    initSidebar();
};

$.fn.dbTableList = function (options) {
    var initDE = function(el) {
        var sl = el.data('db-table-list');

        if (sl === undefined || sl === '') {
            sl = new DbTableList(el, options);
            el.data('db-table-list', sl);
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