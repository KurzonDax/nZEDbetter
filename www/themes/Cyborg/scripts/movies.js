/**
 * Created by Randy on 12/30/13.
 */

jQuery(function ($) {

    $('#movietitle').typeahead({
        name:  'movietitle',
        prefetch:  {
            url: WWW_TOP + '/ajax-movies?action=titles',
            ttl: 1800000
        },
        limit: '10'
    });

    $('#moviedirector').typeahead({
        name    : 'moviedirector',
        prefetch: {
            url: WWW_TOP + '/ajax-movies?action=directors',
            ttl: 1800000
        },
        limit   : '10'
    });

    var actorList = [];
    $.getJSON('/ajax-movies?action=actors', function (data) {
        $.each(data, function (key, val) {
            actorList.push({id: key, text: val});
        });
    });

    $('#movieactors').select2({
        data: actorList,
        minimumInputLength: 3,
        placeholder: 'Actors...',
        multiple: true,
        width: '79.5%',
        maximumSelectionSize: 4,
        allowClear: true,
        formatSelectionTooBig: function(maxsize) { return 'You have selected the max number of actors.';},
        initSelection: function (element, callback) {
            var data = [];
            if(typeof actorsInit !== 'undefined') {
                $(actorsInit).each(function () {
                    if(this.id !== '-1'){
                        data.push({id: this.id, text: this.text});
                    }
                });
            }
            callback(data);
        }
    });

    if (typeof actorsInit !== 'undefined') {
        $('#movieactors').select2('data', actorsInit);
    }

    $('#rating').select2({minimumResultsForSearch: -1, allowClear: true});
    $('#genre').select2({maximumSelectionSize: 3});
    $('#year').select2({maximumSelectionSize: 2});
    $('#MPAA').select2({maximumSelectionSize: 3});
    $('#category').select2({maximumSelectionSize: 4});

    $('#btnAdvancedSearch').click(function () {
        var params = '?action=search';
        if ($('#movietitle').val() !== null) {
            params += '&title=' + encodeURIComponent($('#movietitle').val());
        }
        if ($('#MPAA').select2('val').length > 0) {
            params += '&' + $.param({ MPAA: $('#MPAA').select2('val') });
        }
        if ($('#rating').select2('val') > 0) {
            params += '&rating=' + $('#rating').select2('val');
        }
        if ($('#movieactors').select2('val').length > 0) {
            params += '&' + $.param({ actors: $('#movieactors').select2('val') }, false);
        }
        if ($('#moviedirector').val() !== null) {
            params += '&director=' + encodeURIComponent($('#moviedirector').val());
        }
        if ($('#genre').select2('val').length > 0) {
            params += '&' + $.param({ genres: $('#genre').select2('val') }, false);
        }
        if ($('#year').select2('val').length > 0) {
            params += '&' + $.param({ years: $('#year').select2('val') }, false);
        }
        if ($('#category').select2('val').length > 0) {
            params += '&' + $.param({ t: $('#category').select2('val') }, false);
        }

        params += '&ob=' + $('#orderBy').val();

        window.location.href = WWW_TOP + 'movies' + params;
    });

    $('.icon_cart_movie').click(function (e) {
        if ($(this).hasClass('icon_cart_clicked')) {
            return false;
        }
        var guid = $(this).attr('data-guid');
        var title = $(this).attr('data-title');
        $.post(WWW_TOP + 'cart?add=' + guid, function (resp) {
            $(e.target).addClass('icon_cart_clicked').attr('title', 'Added to Cart');

            $.pnotify({
                title: 'ADDED!',
                text : title + ' is now in your Cart! ^_^',
                type : 'success',
                icon : 'icon-info-sign'
            });

        });
        return false;
    });

});