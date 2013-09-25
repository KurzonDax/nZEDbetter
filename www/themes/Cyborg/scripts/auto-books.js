/**
 * Created with JetBrains PhpStorm.
 * User: Randy
 * Date: 9/22/13
 * Time: 5:45 AM
 *
 * autocomplete functionality for the books category
 */

jQuery(function ($) {

    $.ajax({
        url: WWW_TOP + '/ajax_get_book_genres?type=genres',
        dataType: 'json'
    }).done(function (source) {

            var genresArray = $.map(source, function (value) { return { value: value}; });
            $('#autocomplete-genres').autocomplete({
                lookup: genresArray

            });
        });
});

jQuery(function ($) {

    $.ajax({
        url: WWW_TOP + '/ajax_get_book_genres?type=authors',
        dataType: 'json'
    }).done(function (source) {

            var authorsArray = $.map(source, function (value) { return { value: value}; });
            $('#autocomplete-authors').autocomplete({
                lookup: authorsArray,
                minChars: 3

            });

        });

});

jQuery(function ($) {

    $.ajax({
        url: WWW_TOP + '/ajax_get_book_genres?type=publishers',
        dataType: 'json'
    }).done(function (source) {

            var publishersArray = $.map(source, function (value) { return { value: value}; });
            $('#autocomplete-publishers').autocomplete({
                lookup: publishersArray,
                minChars: 3

            });

        });

});
$("#autocomplete-authors,#title,#autocomplete-genres, #autocomplete-publishers")
    .focusout( function() {
        if ($(this).val() == ""){ $(this).removeClass("form-control-validated");
        } else { $(this).addClass("form-control-validated")}});

$("#minRating")
    .change( function() {
        if ($(this).val() == "-1"){ $(this).removeClass("form-select-validated");
        } else { $(this).addClass("form-select-validated")}});

$( document ).ready(function() {
    if ($("#autocomplete-authors").val() == "") {
        $("#autocomplete-authors").removeClass("form-control-validated");
    } else {
        $("#autocomplete-authors").addClass("form-control-validated")
    }
    if ($("#title").val() == "") {
        $("#title").removeClass("form-control-validated");
    } else {
        $("#title").addClass("form-control-validated")
    }
    if ($("#autocomplete-genres").val() == "") {
        $("#autocomplete-genres").removeClass("form-control-validated");
    } else {
        $("#autocomplete-genres").addClass("form-control-validated")
    }
    if ($("#autocomplete-publishers").val() == "") {
        $("#autocomplete-publishers").removeClass("form-control-validated");
    } else {
        $("#autocomplete-publishers").addClass("form-control-validated")
    }
    if ($("#minRating").val() == "-1") {
        $("#minRating").removeClass("form-select-validated");
    } else {
        $("#minRating").addClass("form-select-validated")
    }
});


