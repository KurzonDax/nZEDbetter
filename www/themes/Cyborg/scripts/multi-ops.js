/**
 * Created by Randy on 1/1/14.
 */

var stack_bottomright = {"dir1": "up", "dir2": "left", "firstpos1": 25, "firstpos2": 25};

jQuery( function($) {

    $('#btnMultiNzbDownload').click(function () {
        var ids = '';
        $('table.data input[type="checkbox"]:checked').each(function () {
            if ($(this).prop('checked') === true) {
                ids += $(this).data('guid') + ',';
            }
        });
        ids = ids.substring(0, ids.length - 1);
        if (ids) {
            window.location = WWW_TOP + 'getnzb?zip=1&id=' + encodeURIComponent(ids);
        }
    });

    $('#btnMultiCartAdd').click(function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var guids = [];
        var counter = 0;
        var names = '';
        $('table.data input[type="checkbox"]:checked').each(function () {
            var guid = $(this).data('guid');
            if (guid && $('#cart-'+guid).hasClass('icon_cart_clicked') === false) {
                $('#cart-' + guid).addClass('icon_cart_clicked').attr('title', 'Already Added to Cart');
                guids.push(guid);
                names += $(this).data('searchname') + '<br />';
                counter ++;
            }
        });
        $.post(WWW_TOP + 'cart', { 'add': guids }).success(function() {
            displayNotification('The following ' + counter + ' release(s) have been added to your Cart: <br /><br />' + names, 'Releases Added to Cart');
            $('table.data input[type="checkbox"]:checked').prop('checked', false);
        });

    });

    $('input.nzb_multi_operations_sab').click(function () {
        $("table.data INPUT[type='checkbox']:checked").each(function (i, row) {
            var $sabIcon = $(row).parent().parent().children('td.icons').children('.icon_sab');
            var guid = $(row).val();
            if (guid && !$sabIcon.hasClass('icon_sab_clicked')) {
                var nzburl = SERVERROOT + "sendtosab/" + guid;
                $.post(nzburl, function (resp) {
                    $sabIcon.addClass('icon_sab_clicked').attr('title', 'Added to Queue');
                    $.pnotify({
                        title: 'ADDED TO SAB!',
                        text : 'Its now in the queue!! ^_^',
                        type : 'info',
                        icon : 'fa-icon-info-sign'
                    });
                });
            }
            $(this).attr('checked', false);
        });
    });

    function displayNotification(text, title, type, icon) {
        title = typeof title !== 'undefined' ? title : 'Operation Successful';
        type = typeof type !== 'undefined' ? type : 'success';
        icon = typeof icon !== 'undefined' ? icon : 'icon-ok-circle';
        $.pnotify({
            title    : title,
            text     : text,
            type     : type,
            history  : false,
            opacity  : .9,
            icon     : icon,
            addclass : "stack-bottomright",
            stack    : stack_bottomright,
            animation: 'show',
            delay    : 12000,
            width    : '400px'
        });
    }

});