onLoad(function () {
    setTimeout(function () {
        $.pipe.template.init();

        initAutocompeteEl();
        initDatepicker();
        initTabs();
        initTableEdit($('.table-edit'));
        initTableList($('.table-list'));
        initPopups($('body'));
        initElements($('#container'));
        initTop();
        initHotKeys();

        $('fieldset').each(function () {
            let fs = $(this);
            if(!fs.children('legend').length) {
                fs.addClass('no-label');
            }
        });
    }, 2);
});

function initHotKeys() {
    let lastKey = {code: null, time: null};

    $(document).on('keyup', function (e) {
        let code = e.keyCode || e.which;

        if(code === 27) { //Escape
            $(document).trigger('keyEscape');
            return;
        }

        if(lastKey.code !== code || (Date.now() - lastKey.time > 500)) {
            lastKey.code = code;
            lastKey.time = Date.now();
            return;
        }

        lastKey.code = null;
        lastKey.time = null;

        switch (code) {
            case 16:
                $(document).trigger('shiftDbl');
                break;
            case 17:
                $(document).trigger('ctrlDbl');
                break;
            default:
        }
    });

    //Calc
    let calc = new Calc();

    $(document).on('ctrlDbl', function() {
        calc.open();
    });

    $('.calc-open').on('click', function() {
        calc.open();
        return false;
    });

    $(document).on('keyEscape', function() {
        calc.close();

        $.pipe.template.getModuleMenu().find('.widget.search [name="query"]').val('').trigger('keyup');
    });

    //Search
    let search = new Search();

    $(document).on('shiftDbl', function() {
        search.open();
    });

    $(document).on('keyEscape', function() {
        search.close();
    });

    $('.search input', $.pipe.template.getMainMenu()).on('focus', function () {
        search.open()
    });
}

function getUrl(opts) {
    let url = '/' + opts.module;

    if(opts.module !== opts.section && opts.section !== '' && opts.section !== undefined) {
        url += '-' + opts.section;
    }

    url += '/';

    if ('action' in opts) {
        url += opts.action + '/';
    }

    if ('id' in opts) {
        url += opts.id + '/';
    }

    return url;
}

let editors = [];

function initTextEditor(box) {
    $('textarea.editor', box).each(function(){
        let textarea = $(this);

        if(CKEDITOR.instances[textarea.attr('name')] !== undefined) {
            return;
        }

        let editor = CKEDITOR.replace(this, {
            height: 208,
            language: 'ru',
            toolbar: [
                { name: 'document', items: ['Source']},
                { name: 'styles', items: ['Format']},
                { name: 'basicstyles', groups: ['basicstyles', 'cleanup'], items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']},
                { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi'], items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']},
                { name: 'links', items: ['Link', 'Unlink']},
                { name: 'tools', items: ['Maximize']},
                { name: 'others', items: ['-']}
            ],
        });

        editor.textarea = textarea;
        editors.push(editor);
    });

    CKEDITOR.editorConfig = function(config) {
        config.extraPlugins = 'jqueryspellchecker';
    };
}

let saveTimer = null;
function saveTable(options) {
    let msg = $('#save-msg');
    if(!msg.length) {
        msg = $('<div id="save-msg">Сохраняеться...</div>');
        msg.appendTo($('body'));
    }

    msg.text('Сохраняеться...').removeClass('complete').addClass('process');

    clearTimeout(saveTimer);
    saveTimer = setTimeout(function() {
        $.ajax({
            url: options.url,
            method: 'post',
            data: options.form.serializeArray(),
            success: options.success
        });

        msg.text('Сохранено').removeClass('process').addClass('complete');
    }, 300);
}

function updateEditors() {
    $.each(editors, function(value, i) {
        let editor = editors[value];
        if (editor) {
            editor.textarea.val(editor.getData());
        }
    });
}

function initTop() {
    let top = $('#top');
    $('.mb-menu', top).on('click', function () {
        top.toggleClass('open');
    });
}

function initElements(box) {
    $('.smart-list', box).smartlist();

    let asd = function () {
        box.on('click', '.btn-prefix', function() {
            let btn = $(this);
            let input = btn.closest('.element').find('input[type="text"], textarea');
            let text = input.val();
            let prefix = btn.data('text');

            btn.siblings('.btn-prefix').each(function () {
                text = text.replace($(this).data('text'), '');
            });

            text = prefix + text.replace(prefix, '');

            input.val(text).trigger('keyup');
        });
    }();

    $('.std-input:not([type="file"]), .std-textarea, .std-select', '.element', box).each(function () {
        let el = $(this);
        let element = $(this).closest('.element');

        if(el.attr('placeholder') !== undefined && el.attr('placeholder') !== '') {
            element.addClass('not-empty');
            return;
        }

        if(el.hasClass('std-select')) {
            element.addClass('not-empty');
            return;
        }

        el.on('focus', function () {
            $(this).closest('.element').addClass('focus');
        });

        el.on('focusout', function () {
            $(this).closest('.element').removeClass('focus');
        });

        el.on('keyup check', function () {
            if($(this).val()) {
                element.addClass('not-empty');
            } else {
                element.removeClass('not-empty');
            }
        });

        el.trigger('keyup');
    });

    $('.element.std-file', box).each(function () {
        let deflabel = $('label', $(this)).text();

        $('input', $(this)).change(function() {
            //let label = $(this).closest('label').find('');
            let text = $(this).prev();

            if ($(this).val() != '') {
                text.text('Новый файл: ' + $(this)[0].files[0].name);
            } else {
                text.text(deflabel);
            }
        });
    });

    $('.element-checkbox', box).each(function () {
        let box = $(this);

        $('label input', box).on('change', function () {
            let el = $(this);
            let lbl = el.parent();

            if($(this).is(':checked')) {
                lbl.addClass('checked');
            } else {
                lbl.removeClass('checked');
            }
        });
    });

    initTextEditor(box);
}

function initPopups(box) {
    box.on('click', '.popup', function() {
        let el = $(this);

        $.fancybox.open({
            src: el.attr('href'),
            type: 'ajax',
            smallBtn : true,
            closeClickOutside: false,
            opts: {
                ajax: {
                    settings: {
                        data: el.data()
                    }
                },
                afterLoad: function(e, slide) {
                    slide.$slide.on('click', function(e) {
                        if($(e.target).hasClass('fancybox-slide')) {
                            $.fancybox.close();
                        }
                    });
                    initElements(slide.$slide);
                }
            }
        });

        return false;
    });
}

function addHit(model, id) {
    $.ajax({
        url: '/' + model + '/add-hit/',
        method: 'post',
        data: {id: id},
    });
}

function initAutocompeteEl() {
    $('.autocomplete-el').each(function() {
        let box = $(this);

        let label = $('.el-label', box);
        let value = $('.el-value', box);

        label.on('input', function() {
            if(!label.val()) {
                value.val('');
                value.trigger('change');
            }
        });

        label.autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '/clients/autocomplete/',
                    type: "get",
                    dataType: "json",
                    data: {
                        query: request.term
                    },
                    success: function( data ) {
                        response(data);
                    }
                });
            },
            select: function (event, ui) {
                label.val(ui.item.label);
                value.val(ui.item.value);
                value.trigger('change');
                return false;
            }
        });
    });
}
/*
function initEdit() {
    $('body').on('click', '.del-block', function() {
        $(this).closest('.block').remove();
    });
}
*/
function initTableList(form, options) {
    let calendar = $('.calendar-widget ');

    if(!calendar.length) {
        form.dbTableList(options);
        return
    }

    let container = $('#container');
    let odbtl = $('.orders-dbtll');

    container.addClass('calendar');

    $('.table-list', odbtl).dbTableList({
        callbacks: {
            beforeLoad: function (query) {
                if(query === '') {
                    odbtl.css({display: 'none'});
                    calendar.css({display: 'block'});
                    container.addClass('calendar');
                } else {
                    odbtl.css({display: 'block'});
                    calendar.css({display: 'none'});
                    container.removeClass('calendar');
                }
                return true;
            },
        }
    });

}

function initTableEdit(form, options) {
    form.dbTableEdit(options);
}

function initDatepicker() {
    $.datepicker.regional['ru'] = {
        clearText: 'Очистить',
        clearStatus: '',
        closeText: 'Закрыть',
        closeStatus: '',
        prevText: '',
        prevStatus: '',
        nextText: '',
        nextStatus: '',
        currentText: 'Сегодня',
        currentStatus: '',
        monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь', 'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
        monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн', 'Июл','Авг','Сен','Окт','Ноя','Дек'],
        monthStatus: '',
        yearStatus: '',
        weekHeader: 'Не',
        weekStatus: '',
        dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
        dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        dayStatus: 'DD',
        dateStatus: 'D, M d',
        dateFormat: 'dd.mm.yy',
        firstDay: 1,
        initStatus: '',
        isRTL: false,
    };

    $.datepicker.setDefaults($.datepicker.regional['ru']);

    $(document).on('focus', '.datepicker', function(){
        let el = $(this);
        el.datepicker({
            onSelect: function(date) {
                el.val(date).trigger('change').trigger('keyup');
            }
        });
        el.prop('readonly', true);
    });

    $(document).on('focus', '.datepicker-dm', function(){
        let el = $(this);
        let options = $.extend($.datepicker.regional['ru'], {
            dateFormat: 'dd.mm',
            beforeShow: function(s,d) {
                $('.ui-datepicker').addClass('no-year');
            }
        });

        el.datepicker(options);
        el.prop('readonly', true);
    });
}


function initTabs() {
    $('.tabs').tabs();
}