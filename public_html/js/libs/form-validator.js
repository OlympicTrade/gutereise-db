(function($) {
    var defaults;

    var options;

    function checkErrors(form, requestType, verifiableElement) {
        var result = false;
        var data = form.serializeArray();
        var async = (requestType == 'validate' ? true : false);

        data.push({
            name: 'requestType',
            value: requestType
        });

        $.ajax({
            type: "POST",
            async: async,
            url: form.attr('action'),
            data: data,
            dataType: "json",
            success: function(resp) {
                if(!resp['errors'] || !Object.keys(resp['errors']).length) {
                    form.find('.msgs').remove();
                    form.find('.invalid').removeClass('invalid');
                    result = resp ? resp : true;
                    return;
                }

                form.find('.form-msgs').empty();

                if(resp['errors']['all']) {
                    form.find('.form-msgs').append(renderErrors(resp['errors']['all']));
                }

                form.find('input[type!="hidden"][type!="submit"], select, textarea').each(function() {
                    var input     = $(this);
                    var inputBox  = $(this).parent();
                    var errorsBox = inputBox.find('.msgs');
                    var errors    = resp['errors'][input.attr('name')];

                    if(!errors) {
                        errorsBox.remove();
                        inputBox.removeClass('invalid');
                    }

                    if(verifiableElement) {
                        if(verifiableElement.attr('name') == input.attr('name')) {
                            errorsBox.remove();
                            if(resp['errors'][input.attr('name')]) {
                                inputBox.addClass('invalid');
                                inputBox.append(renderErrors(errors));
                            } else {
                                inputBox.removeClass('invalid');
                            }
                        }

                        return;
                    }

                    errorsBox.remove();

                    if(errors) {
                        console.log(errors);
                        inputBox.addClass('invalid');
                        inputBox.append(renderErrors(errors));
                    } else {
                        inputBox.removeClass('invalid');
                    }
                });
            }
        });

        return result;
    }

    function renderErrors(errors) {
        if(!errors) {
            return '';
        }

        var html = '<div class="msgs">';

        for(key in errors) {
            html += '<div class="msg">' + errors[key] + '</li>';
        }

        html += '</div>';

        return html;
    }

    $.fn.formValidate = function(){
        return checkErrors($(this), 'submit');
    };

    $.fn.formValidator = function(params){
        options = $.extend({autoValidate: true}, defaults, options, params);

        var form = $(this);

        if(options.autoValidate) {
            form.find('input, select, textarea').blur(function()
            {
                checkErrors(form, 'validate', $(this));
            });
        }

        form.submit(function(){
            if(options.before) {
                options.before(form);
            }

            if(result = checkErrors(form, 'submit', null)) {
                if(options.success) {
                    options.success(result, form);
                }
            } else {
                if(options.fail) {
                    options.fail(result, form);
                }
            }

            if(options.after) {
                options.after(result, form);
            }

            return false;
        });

        return this;
    };
})(jQuery);