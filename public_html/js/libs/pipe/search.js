var Search = function () {
    var box = $('#search');
    var input = $('input', box);
    var obj = this;

    $('.close', box).on('click', function () {
        obj.close();
    });

    this.close = function() {
        var search = box;

        input.val('');

        setTimeout(function () {
            search.css({
                display: 'none',
            });
        }, 200);

        search.css({
            opacity: 0
        });
    };

    this.open = function() {
        box.css({
            display: 'block',
            opacity: 1
        });

        input.focus();
    };


    $.widget('custom.catcomplete', $.ui.autocomplete, {
        _create: function() {
            this._super();
            var widget = this.widget();

            widget.menu('option', 'items', '.ac-item');
            widget.addClass('search-autocomplete');
        },
        _renderItem: function(ul, item) {
            var li = $('<li></li>');

            switch(item.type) {
                case 'title':
                    li.addClass('ac-title')
                        .text(item.label);
                    break;
                case 'hr':
                    li.addClass('ac-hr')
                        .text(item.label);
                    break;
                case 'show-all':
                    li.addClass('ac-show-all')
                        .append('<span>Показать еще</span>');

                    li.on('click', function() {
                        ul.find('[data-type="' + item.module + '"]').css({display: 'block'});
                        li.remove();
                    });
                    break;
                default:
                    li.addClass('ac-item')
                        .attr('data-type', item.module)
                        .append('<a href="' + item.url + '">' + item.label + '</a>');
            }

            if(item.hide) {
                li.addClass('hide');
            }

            return li.appendTo(ul)
        }
    });

    var pos = {my: 'left top', at: 'left bottom'};

    input.catcomplete({
        position: pos,
        source: function(request, response) {
            $.ajax({
                url: '/search/',
                type: 'get',
                dataType: 'json',
                data: {
                    query: request.term
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        select: function(event, ui) {
            if(ui.item.url) {
                location.href = ui.item.url;
            }
        },
        open: function(event, ui) {

        },
        lookup           : 'res',
        zIndex           : 9999,
        deferRequestBy   : 300,
        params           : {limit: 20},
    });
};