function calcDetails(calcData) {
    var addRowToResult = function (type, key, options) {
        var html = '';

        switch (type) {
            case 'sum':
                html = '<div class="row sum">' + key + '</div>';
                break;
            case 'header':
                html = '<div class="row">' + key + '</div>';
                break;
            case 'error':
                html = '<div class="error" data-error="' + options.type + '">' + key + '</div>';
                break;
            case 'notice':
                html = '<div class="notice" data-notice="' + options.type + '">' + key + '</div>';
                break;
            case 'row':
                html =
                    '<div class="row">' +
                    '<div class="key">' + key + '</div>' +
                    '<div class="val">' + options + '</div>' +
                    '</div>';
                break;
            case 'title':
                html = '<div class="title">' + key + '</div>';
            case 'subtitle':
                html = '<div class="subtitle">' + key + '</div>';
        }

        return html;
    };

    var totalResult = {
        html: '',
        days: [],
        hotels: [],
        errorsCount: 0,
        orderErrorsCount: 0,
    };

    var currenciesIcons = {
        rub: {
            name: ''
        },
        eur: {
            name: 'Евро: '
        },
        usd: {
            name: 'Доллары: '
        }
    };

    $.each(calcData.errors, function(key, error) {
        totalResult.errorsCount++;
        totalResult.html += '<div class="error">' + error + '</div>';
    });

    var hotelsData = calcData.hotels;
    var hotelsResult = {
        html: '',
        errorsCount: 0,
        orderErrorsCount: 0
    };

    if(hotelsData) {
        if(parseInt(hotelsData.summary)) {
            $.each(hotelsData.summary, function (currency, summary) {
                hotelsResult.html += addRowToResult('sum', 'Сумма: ' +
                    $.pipe.price(summary.income) +
                    ' (' + $.pipe.price(summary.outgo, currency) + ' + ' + summary.percent + '%)');
            });
        }

        $.each(hotelsData.notices, function (errorCode, notice) {
            hotelsResult.html += addRowToResult('notice', notice, {type: 'hotels'});
        });


        $.each(hotelsData.hotels, function (key, hotelData) {
            hotelsResult.html += addRowToResult('sum', hotelData.name + ': ' + hotelData.desc, {type: 'hotels'});
            $.each(hotelData.rooms, function (key, roomData) {
                hotelsResult.html += addRowToResult('subtitle', roomData.name + ': ' + $.pipe.price(roomData.income, 'rub'));

                if (Object.keys(roomData.errors).length) {
                    $.each(roomData.errors, function (errorCode, error) {
                        hotelsResult.html += addRowToResult('error', error, {type: 'hotels'});
                        hotelsResult.errorsCount++;
                    });
                } else {
                    hotelsResult.html += addRowToResult('row', 'Стомиость: ', roomData.desc);
                }
            });
        });
    }

    totalResult.hotels = hotelsResult;

    $.each(calcData.days, function(dayId, dayData) {
        var dayResult = {
            html: '',
            errorsCount: 0,
            orderErrorsCount: 0
        };

        if(dayData.errors !== undefined && Object.keys(dayData.errors).length) {
            $.each(dayData.errors, function(key, error) {
                dayResult.html += '<div class="error">' + error + '</div>';
                dayResult.errorsCount++;
            });

            totalResult.days[dayId].push(dayResult);

            if(dayResult.errorsCount) return;
        }

        if(dayData.guides !== undefined && dayData.guides.list.length) {
            $.each(dayData.guides.list, function(key, guide) {
                dayResult.html += addRowToResult('title', guide.name, {type: 'guides'});

                if (guide.order_errors && Object.keys(guide.order_errors).length) {
                    $.each(guide.order_errors, function(errorCode, error) {
                        dayResult.html += addRowToResult('error', error, {type: 'guides'});
                        dayResult.orderErrorsCount++;
                    });
                }

                if (Object.keys(guide.errors).length) {
                    $.each(guide.errors, function (errorCode, error) {
                        dayResult.html += addRowToResult('error', error, {type: 'guides'});
                        dayResult.errorsCount++;
                    });
                } else {
                    dayResult.html += addRowToResult('row', 'Стоимость', guide.income + ' (' + guide.desc + ')');
                }
            });
        }

        if (dayData.transports !== undefined && dayData.transports.list.length) {
            $.each(dayData.transports.list, function(key, transport) {
                dayResult.html += addRowToResult('title', transport.name, {type: 'transports'});

                if (transport.order_errors && Object.keys(transport.order_errors).length) {
                    $.each(transport.order_errors, function(errorCode, error) {
                        dayResult.html += addRowToResult('error', error, {type: 'transports'});
                        dayResult.orderErrorsCount++;
                    });
                }

                if (Object.keys(transport.errors).length) {
                    $.each(transport.errors, function (errorCode, error) {
                        dayResult.html += addRowToResult('error', error, {type: 'transports'});
                        dayResult.errorsCount++;
                    });
                } else {
                    dayResult.html += addRowToResult('row', 'Стоимость', transport.income + ' (' + transport.desc + ')');
                }
            });
        }

        if (dayData.museums !== undefined) {
            $.each(dayData.museums.list, function(key, museum) {
                dayResult.html += addRowToResult('title', museum.name, {type: 'museums'});

                if (Object.keys(museum.errors).length) {
                    $.each(museum.errors, function(key, error) {
                        dayResult.html += addRowToResult('error', error, {type: 'museums'});
                        dayResult.errorsCount++;
                    });
                } else {
                    if (museum.income) {
                        dayResult.html += addRowToResult('row', 'Стоимость', museum.income);
                    }

                    if (museum.income) {
                        var msmDesc = '';
                        msmDesc +=  parseInt(museum.adults_count) ? 'Взр: ' + museum.adult_tickets + ' руб. * ' + museum.adults_count + ' чел.' : '';
                        msmDesc +=  parseInt(museum.children_count) ? ' Дет: ' + museum.child_tickets + ' руб. * ' + museum.children_count + ' чел.' : '';

                        dayResult.html += addRowToResult('row', 'Билеты',  museum.tickets + ' (' + msmDesc + ')');
                    }

                    if (museum.guides) {
                        dayResult.html += addRowToResult('row', 'Музейный гид', museum.guides);
                    }

                    if (museum.extra.length) {
                        museum.extra.forEach(function (extra) {
                            dayResult.html += addRowToResult('row', extra.name, extra.desc);
                        });
                    }
                }
            });
        }

        if (dayData.extra !== undefined) {
            $.each(dayData.extra.list, function(key, extra) {
                dayResult.html += addRowToResult('title', 'Доп. расходы', {type: 'extra'});

                if (Object.keys(extra.errors).length) {
                    $.each(extra.errors, function(key, error) {
                        dayResult.html += addRowToResult('error', error, {type: 'extra'});
                        dayResult.errorsCount++;
                    });
                } else {
                    dayResult.html += addRowToResult('row', extra.name,  extra.income);
                }
            });
        }

        if (dayResult.errorsCount) {
            dayResult.html += addRowToResult('header', 'Ошибки в форме');
        } else {
            var daySummary = '';
            $.each(dayData.summary, function (currency, summary) {
                daySummary +=
                    addRowToResult('sum', currenciesIcons[currency]['name'] + ' ' +
                        'Сумма: <b>' + $.pipe.price(summary.income, currency) + '</b> ' +
                        ' (' + $.pipe.price(summary.outgo, currency) + ' + ' + summary.percent +  '%)')+
                    addRowToResult('sum', 'Врослый <b>' + $.pipe.price(summary.adult, currency)
                        + '</b>, детский <b>' + $.pipe.price(summary.child, currency) + '</b>');
            });
            dayResult.html += daySummary;
        }

        totalResult.orderErrorsCount += dayResult.orderErrorsCount;
        totalResult.errorsCount += dayResult.errorsCount;
        totalResult.days[dayId] = dayResult;
    });

    if (!totalResult.errorsCount) {
        var totalSummary = '';
        $.each(calcData.summary, function (currency, summary) {
            totalSummary +=
                addRowToResult('sum', currenciesIcons[currency]['name'] + ' ' +
                    'Сумма: <b>' + $.pipe.price(summary.income, currency) + '</b>' +
                    ' (' + $.pipe.price(summary.outgo, currency) + ' + ' + summary.percent +  '%)'
                ) +
                addRowToResult('sum', 'Врослый <b>' + $.pipe.price(summary.adult, currency)
                    + '</b>, детский <b>' + $.pipe.price(summary.child, currency) + '</b>');
        });
        totalResult.html += totalSummary;
    } else {
        totalResult.html += addRowToResult('error', 'В форме присутствуют ошибки, препятствующие рассчету', {type: 'extra'});
    }

    return totalResult;
}