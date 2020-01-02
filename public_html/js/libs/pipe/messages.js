function Message()
{
    this.msg = null;
    this.container = null;
    this.type = null;
    this.initFlag = false;

    this.init = function () {
        if(this.initFlag) {
            this.msg.removeClass('del');
            return this.msg;
        }

        if(!$('.message-box').length) {
            this.container = $('<div/>').addClass('message-box').appendTo($('body'));
        } else {
            this.container = $('.message-box');
        }

        this.msg = $('<div/>').addClass('message').appendTo(this.container);



        this.initFlag = true;
        return this.msg;
    };

    this.setLoading = function (opts) {
        var msg = this.init();
        msg.removeClass('del');
        this.setType('loading');

        opts = $.extend({
            icon: 'fal fa-spinner fa-spin',
            text: 'Выполнено',
        }, opts);

        msg.empty().append('<i class="' + opts.icon + '"></i> ' + opts.text);
        msg.off('click');

        setTimeout(function() {
            msg.css({opacity: 1, top: 0});
        }, 1);
    };

    this.setMessage = function(opts) {
        var msg = this.init();
        msg.removeClass('del');

        opts = $.extend({
            type: 'success',
            icon: 'fal fa-check',
            text: 'Выполнено',
            fade: true
        }, opts);

        this.setType(opts.type);
        msg.empty().append('<i class="' + opts.icon + '"></i> ' + opts.text);

        var msgObj = this;
        msg.off('click').on('click', function(){
            msgObj.delMessage();
        });

        msg.animate({opacity: 1, top: 0}, 600);

        setTimeout(function () {
            msg.fadeOut(400);
        }, 6000);
    };

    this.delMessage = function() {
        this.init();
        this.msg.addClass('del');
    };

    this.setType = function(type) {
        if(!this.msg || type == this.type) {
            return;
        }

        var newType = type ? type : 'default';
        var oldType = this.type;
        var msg = this.msg;

        if(!oldType) {
            msg.addClass(newType);
        } else {
            msg.addClass(newType, 300, function() {
                msg.removeClass(oldType);
            });
        }

        this.type = newType;
    };
}
