var Calc = function () {
    this.close = function() {
        $.fancybox.close();
    };

    this.open = function() {
        $.fancybox.open({
            src: '/calc/',
            type: 'ajax',
            smallBtn : true,
            closeClickOutside: false,
            opts: {
                afterLoad: function(e, slide) {
                    initElements(slide.$slide);
                }
            }
        });
    };

};