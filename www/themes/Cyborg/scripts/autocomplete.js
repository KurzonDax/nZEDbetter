/**
 * Created with JetBrains PhpStorm.
 * User: Randy
 * Date: 9/21/13
 * Time: 10:34 AM
 *
 */

$(function () {
    'use strict';

    // Load countries then initialize plugin:
    $.ajax({
        url: '/pages/ajax_get_book_genres.php',
        dataType: 'json'
    }).done(function (source) {



            // Initialize ajax autocomplete:
            $('#autocomplete').autocomplete({
                serviceUrl: '/pages/ajax_get_book_genres.php',
                onSelect: function (suggestion) {
                    alert('You selected: ' + suggestion.value + ', ' + suggestion.data);
                }
            });



    });

});