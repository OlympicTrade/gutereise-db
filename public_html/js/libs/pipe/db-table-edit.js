let DbShortTables = function (box, options) {
    this.box = box;
    this.changeTimer = null;

    let pattern = $('.pattern', box);
    let list = $('.list', box);
    let name = box.data('prefix') + '';
    let insetId = 0;

    let obj = this;

    this.on = function(event, fn) {
        $(this).on(event, fn);
    };

    this.trigger = function(event, opts) {
        $(this).trigger(event, opts);
    };

    list.sortable({
        placeholder: 'sort-placeholder',
        handle: '.item-sort',
        revert: true,
        update: function (event, ui) {
            obj.updateSort();
            ui.item.find('.sort').trigger('change');
            obj.trigger('update', [item]);
        }
    });

    $('input, select, textarea', pattern).each(function () {
        let el = $(this);
        el.attr('data-name', el.attr('name'));
        el.attr('name', '');
    });

    list.on('change', '[data-type="collection"]', function() {
        let el = $(this);
        let cell = el.closest('.cell');
        $('[data-action="view"]', cell).css('display', (el.val() ? 'block' : 'none'));
    });

    $('[data-type="collection"]', list).trigger('change');

    function updateSelect(module, select, val) {
        $.ajax({
            url: getUrl({module: module, section: section, action: 'get-list-data'}),
            success: function(resp) {
                let html = '<option value="0">Не выбран</option>';

                resp.forEach(function(row) {
                    html += '<option value="' + row.id + '">' + row.label + '</option>';
                });

                if(val === undefined) {
                    val = select.val();
                }

                select.empty().html(html);
                select.val(val);

                if(val) select.closest('.cell').find('[data-action="view"]').css('display', 'block');
            }
        });
    }

    list.on('click', '[data-action="view"]', function() {
        let el = $(this);
        let cell = el.closest('.cell');
        let select = $('select', cell);
        let section = cell.data('section');
        let module = cell.data('module');

        $.fancybox.open({
            src: getUrl({module: module, section: section, action: 'edit', id: $('select', cell).val()}),
            type: 'ajax',
            smallBtn : true,
            opts: {
                ajax: {
                    settings: {
                        data: el.data()
                    }
                },
                afterLoad: function(e, slide) {
                    slide.$slide.on('click', function(e) {
                        if($(e.target).hasClass('fancybox-slide')) {
                            $.fancybox.close()
                        }
                    });
                    initElements(slide.$slide);
                    let options = {
                        save: {
                            callback: function(resp) {
                                updateSelect(module, select);
                            },
                            delete: function() {
                                updateSelect(module, select, 0);
                            }
                        }
                    };

                    initTableEdit($('.table-edit', slide.$slide), options);
                }
            }
        });
    });

    list.on('click', '[data-action="edit"]', function() {
        let el = $(this);
        let cell = el.closest('.cell');
        let select = $('select', cell);
        let section = cell.data('section');
        let module  = cell.data('module');

        $.fancybox.open({
            src: getUrl({module: module, section: section, action: 'edit'}),
            type: 'ajax',
            smallBtn : true,
            opts: {
                ajax: {
                    settings: {
                        data: el.data()
                    }
                },
                afterLoad: function(e, slide) {
                    slide.$slide.on('click', function(e) {
                        if($(e.target).hasClass('fancybox-slide')) {
                            $.fancybox.close()
                        }
                    });
                    initElements(slide.$slide);
                    let options = {
                        save: {
                            callback: function(resp) {
                                updateSelect(module, select, resp.data.id);
                            },
                            delete: function() {
                                updateSelect(module, select, 0);
                            }
                        }
                    };

                    initTableEdit($('.table-edit', slide.$slide), options);
                }
            }
        });
    });

    this.updateSort = function() {
        let sortId = 0;
        $('.item', list).each(function () {
            $(this).find('input.sort').val(sortId);
            sortId++;
        });
    };

    this.delItem = function(item, options) {
        options = $.extend({
            triggers: ['update', 'del'],
        }, options);

        if(item.data('id')) {
            list.append('<input type="hidden" name="' + name + '[del-' + item.data('id') + ']" value="' + item.data('id') + '">');
        }

        $.each(options.triggers, function (key, triggerName) {
            obj.trigger(triggerName, [item]);
        });

        item.remove();
        obj.updateSort();
    };

    this.copyItem = function(item) {
        let newItem = item.clone();

        insetId++;
        let nameReplace = box.data('name') + '[' + 'new-' + insetId + ']';

        newItem.attr('data-id', null);
        $('.row-acts input', newItem).remove();
        $('input, select, textarea', newItem).each(function() {
            let el = $(this);
            el
                .val($('[name="' + el.attr('name') + '"]', item).val())
                .attr('name', el.attr('name').replace(/.+\[\d+\]/, nameReplace))
                .attr('name', el.attr('name').replace(/.+\[new-\d+\]/, nameReplace));
        });

        obj.trigger('update', [item]);
        obj.trigger('copy', [newItem]);

        newItem.appendTo(list);
        obj.updateSort();
    };

    let getRow = function () {
        insetId++;
        let item = pattern.clone();
        item.removeClass('pattern');

        $('input, select, textarea', item).each(function () {
            let el = $(this);
            let name = el.data('name').replace(/_ID_/, 'new-' + insetId);

            /*if(obj.options.replace !== null) {
                name = obj.options.replace(name, insetId);
            }*/

            el.attr('name', name);
            item.attr('data-name', '');
        });

        return item;
    };

    this.addItem = function(vals, options) {
        let item = getRow();

        options = $.extend({
            triggers: ['update', 'add'],
        }, options);

        if(vals !== undefined) {
            let i = 0;
            $('input, select, textarea', $('.cell', item)).each(function () {
                $(this).val(vals[i]);
                i++;
            });
        }

        $.each(options.triggers, function (key, triggerName) {
            obj.trigger(triggerName, [item]);
        });

        item.appendTo(list);

        if(options.after !== undefined) {
            options.after(item);
        }

        obj.updateSort();
    };

    this.clear = function(options) {
        options = $.extend({
            triggers: ['update', 'add'],
        }, options);

        $('.item', list).each(function () {
            obj.delItem($(this), options);
        });

        return obj;
    };

    box.on('click', '.item-del', function() {
        obj.delItem($(this).closest('.item'));
        obj.trigger('change', [obj]);
    });

    box.on('click', '.item-copy', function() {
        obj.copyItem($(this).closest('.item'));
        obj.trigger('change', [obj]);
    });

    $('.item-add', box).on('click', function() {
        obj.addItem();
        obj.trigger('change', [obj]);
    });

    $(box).on('click', '.item-edit', function(){
        $('.item', box).removeClass('edit');
        let item = $(this).closest('.item').addClass('edit');

        $('input, textarea', form).each(function() {
            $(this).val($('[name="' + $(this).data('name') + '"]', item).val());
        });
    });

    list.on('change keyup', 'input, select, textarea', function () {
        clearTimeout(obj.changeTimer);
        obj.changeTimer = setTimeout(function () {
            obj.trigger('change', [obj]);
        }, 200);
    });
};

$.fn.dbShortTables = function (options) {
    let initDE = function(el) {
        let sl = el.data('db-short-table');

        if (sl === undefined || sl === '') {
            sl = new DbShortTables(el, options);
            el.data('db-short-table', sl);
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

let DbTableEdit = function (form, options) {
    let obj = this;
    let sidebar = $.pipe.template.getModuleMenu();
    let module  = form.data('module');
    let section = form.data('section');
    let id = $('[name="id"]', form).val();

    if(!id || id == 0) {
        $('.item-del', sidebar).addClass('hidden');
    }

    this.save = function(options) {
        options = $.extend({
            success: false,
        }, options);

        updateEditors();
        let message = new Message();
        message.setLoading({icon: 'far fa-list-alt'});

        let url = '';
        if(options.btn !== undefined && options.btn.data('url')) {
            url = options.btn.data('url');
        } else if(form.attr('action') !== '') {
            url = form.attr('action');
        } else {
            url = getUrl({module: module, section: section, action: 'edit', id: id});
        }

        let ajaxOpts = {
            url: url,
            method: 'post',
            data: form.serializeArray(),
            success: function(resp) {
                $('.element .error', form).remove();

                if(resp.status == 1) {
                    message.setMessage({icon: 'far fa-list-alt', text: 'Сохранено'});

                    if(options.success) {
                        options.success(resp);
                    }
                } else {
                    $.each(resp.errors, function (name, errors) {
                        let field = $('[name="' + name + '"]', form);

                        if(!field.length || !field.closest('.element').length) { dd('Не найдено поле ' + name) }

                        $.each(errors, function (code, error) {
                            $('<div class="error">' + error + '</div>').appendTo(field.closest('.element'));
                        });
                    });
                }
            }
        };

        if($('[name="file"]', form)) {
            ajaxOpts['processData'] = false;
            ajaxOpts['contentType'] = false;
            ajaxOpts['data'] = $.pipe.serializeForm(form, true);
        } else {
            ajaxOpts['data'] = $.pipe.serializeForm(form);
        }

        $.ajax(ajaxOpts);
    };

    this.del = function(options) {
        options = $.extend({
            success: false
        }, options);

        $.ajax({
            url: getUrl({module: module, section: section, action: 'delete', id: id}),
            type: 'post',
            data: {},
            success: function($resp) {
                if(!parseInt($resp['status'])) {
                    alert('При удалении произошла ошибка');
                } else {
                    location.href = getUrl({module: module, section: section})
                }
            }
        });
    };

    $('.item-save', sidebar).on('click', function(){
        let btn = $(this);
        obj.save({
            btn: btn,
            success: function () {
                if($('[name*="new-"]', form).length) {
                    //location.reload();
                }

                if(btn.closest('.fancybox-container').length) {
                    $.fancybox.close();
                }

                if(btn.data('redirect') === 'reload') {
                    //location.reload();
                }
            }
        });
    });

    $('.item-del', sidebar).on('click', function() {
        if(confirm('Вы уверены, что хотите удалить запись?')) {
            obj.del();
        }

        return false;
    });

    $('.short-form', form).dbShortTables();
};

$.fn.dbTableEdit = function (options) {
    let initDE = function(el) {
        let sl = el.data('db-table-edit');

        if (sl === undefined || sl === '') {
            sl = new DbTableEdit(el, options);
            el.data('db-table-edit', sl);
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