/**
 * Created with JetBrains PhpStorm.
 * User: Randy
 * Date: 10/16/13
 * Time: 5:42 AM
 *
 */

var validateStep = {};
var nextStepAjax = {};
var submitStepAjax = {};
var databaseSuccess = false;

var debugMode = false;

$(function ()
{
    var objWizardOptions = {
        headerTag: "h3",
        bodyTag: "section",
        transitionEffect: "none",
        stepsOrientation: "vertical",
        onStepChanging: function (event, currentIndex, newIndex) { return (currentIndex==0) ? validateStep[0]() : true; },
        onStepChanged: function (event, currentIndex, priorIndex) { return nextStepAjax[currentIndex](); },
        onSubmitForm: function (event, currentIndex) {submitStepAjax[currentIndex](); },
        onFinished: function (event, currentIndex) {window.location.replace('../admin/index.php'); return true;},
        stepsForSubmit: "3, 4, 5, 6"
    };
    if(debugMode)
        objWizardOptions['enableAllSteps'] = true;
    window.wizard$ = $("#installWizard").steps(objWizardOptions);
    wizard$.steps('validateCurrentStep');
});


<!-- **** Next Step Ajax Calls **** -->

    nextStepAjax[0] = function () {
        return true;
    };

    nextStepAjax[1] = function () {
        $("#step1").html("<h2><i class='icon-spinner icon-spin'></i> Checking system, please wait...</h2>");
        ajaxSubmitRequest("step1");
    };

    nextStepAjax[2] = function () {
        if(databaseSuccess) {
            $("#successstep2").remove();
            if($("#dangerDatabaseSuccessstep2").length==0) {
                $("#step2").prepend(formatAlert({
                    type:   'danger',
                    ID:     'DatabaseSuccess',
                    title:  'Database Already Created',
                    text:   'We have already completed configuring the database, so the wizard will not attempt to create it again.  If you need to start' +
                            'over, please refresh the page and restart the wizard from the beginning.',
                    icon:   'icon-warning-sign',
                    step:   'step2'
                }));
            }
            wizard$.steps('hideButton', 'submit').steps('showButton', 'next').steps('enableButton', 'next');
        } else
            wizard$.steps('hideButton', 'next');
        return true;
    };

    nextStepAjax[3] = nextStepAjax[4] = nextStepAjax[5] = nextStepAjax[6] =function () {
        return true;
    };


<!-- **** Submit Ajax Step Functions **** -->

submitStepAjax[2] = function() {
    $('#dangerErrorstep2').remove();
    var blankFields = checkBlankFields($("#step2"));
    if(blankFields){
        if($("#dangerBlankFieldsstep2").length > 0)
            return false;
        else {
            $("#step2").append(formatAlert({
                type:   'danger',
                ID:     'BlankFields',
                title:  'WARNING!',
                text:   'You have left some fields blank.  All fields above are required to properly configure the database.',
                icon:   'icon-warning-sign',
                step:   'step2'
            }));
            return false;
        }
    }

    $("#dangerBlankFieldsstep2").remove();
    var objSubmitData = {};
    $("#step2").find('input[type=text]').each (function() {
        objSubmitData[$(this).attr('id')] = $(this).val();
    });
    objSubmitData['dbLocation'] = $('input[name=dbLocation]:checked').val();
    if(debugMode)
        objSubmitData['debug'] = 'true';
    ajaxSubmitRequest('step2', $.param(objSubmitData), true, true);
    $("#step2").append(formatAlert({
        type:   'warning',
        ID:     'AjaxSubmit',
        title:  'Creating Database',
        text:   'Database setup in progress.  Please wait...',
        icon:   'icon-warning-sign',
        step:   'step2'
    }));

    return true;
};

submitStepAjax[3] = function() {
    $('#dangerErrorstep3').fadeOut(400).remove();
    var blankFields1 = checkBlankFields($("#divPrimaryNNTP"));
    var blankfields2 = ($("#chkUseAlternateNNTP").prop('checked')==true) ? checkBlankFields($("#divAlternateNNTP")) : false;
    if(blankFields1 || blankfields2){
        if($("#dangerBlankFieldsstep3").length > 0)
            return false;
        else {
            $("#step3").append(formatAlert({
                    type:   'danger',
                    ID:     'BlankFields',
                    title:  'WARNING!',
                    text:   'You have left some fields blank.  All fields above are required to properly configure your NNTP service provider(s).',
                    icon:   'icon-warning-sign',
                    step:   'step3',
                    hidden: ''}
            ));
            return false;
        }
    }
    $("#dangerBlankFieldsstep3").remove();
    var objSubmitData = {};
    $("#step3").find('input[type=text]').each (function() {
        objSubmitData[$(this).attr('id')] = $(this).val();
    });
    objSubmitData['ssl'] = ($("#ssl").prop('checked') == true) ? '1' : '0';
    objSubmitData['ssla'] = ($("#ssla").prop('checked') == true) ? '1' : '0';
    objSubmitData['useAltNNTP'] = ($("#chkUseAlternateNNTP").prop('checked') == true) ? 'true' : 'false';
    if(debugMode)
        objSubmitData['debug'] = 'true';
    ajaxSubmitRequest('step3', $.param(objSubmitData), true, true);
    $("#step3").append(formatAlert({
        type:   'warning',
        ID:     'AjaxSubmit',
        title:  'Saving Configuration',
        text:   'Testing the NNTP settings and saving your input into the www/config.php file. Please wait.',
        icon:   'icon-warning-sign',
        step:   'step3'
    }));

    return true;
};

submitStepAjax[4] = function() {
    $('#dangerErrorstep4').remove();
    var blankFields = checkBlankFields($("#step4"));
    if(blankFields){
        if($("#dangerBlankFieldsstep4").length > 0)
            return false;
        else {
            $("#step4").append(formatAlert({
                type:   'danger',
                ID:     'BlankFields',
                title:  'WARNING!',
                text:   'You have left some fields blank.  All fields above are required to create an administrative user.',
                icon:   'icon-warning-sign',
                step:   'step4'
            }));
            return false;
        }
    }
    var objSubmitData = {};
    objSubmitData['action'] = 'step4';
    $('#step4').find("input[type=text], input[type=password]").each(function() {
        objSubmitData[$(this).attr('id')] = $(this).val();
    });
    ajaxSubmitRequest('step4', $.param(objSubmitData), true, true);
    $("#step4").append(formatAlert({
        type:   'warning',
        ID:     'AjaxSubmit',
        title:  'Adding Admin User',
        text:   'Attempting to add the user you specified to the database.  Please wait.',
        icon:   'icon-warning-sign',
        step:   'step4'
    }));
    return true;
};

submitStepAjax[5] = function() {
    if($("#nzbpath").val() == '') {
        if($("#dangerBlankFieldsstep5").length > 0)
            return false;
        else {
            $("#step4").append(formatAlert({
                type:   'danger',
                ID:     'BlankFields',
                title:  'WARNING!',
                text:   'You have left some fields blank.  All fields above are required to create an administrative user.',
                icon:   'icon-warning-sign',
                step:   'step5'
            }));
            return false;
        }
    }
    var objSubmitData = {};
    objSubmitData['action'] = 'step5';
    $('#step5').find("input[type=text]").each(function() {
        objSubmitData[$(this).attr('id')] = $(this).val();
    });
    ajaxSubmitRequest('step5', $.param(objSubmitData), true, true);
    return true;
};

<!-- **** Validation Functions **** -->

    validateStep[0] = function () {
        if(errorStatus){
            $("#step0").append(formatAlert({
                type:   'danger',
                ID:     'ValidationError',
                title:  'ERROR!!',
                text:   errorHTML,
                icon:   'icon-warning-sign',
                step:   'step0'
            }));
            window.wizard$.steps('disableButton', 'next');

        } else
            window.wizard$.steps('enableButton', 'next');

        return !errorStatus;
    };

<!-- **** Helper Functions **** -->

/**
 *
 * @param step {string} indicates the wizard step calling the function, i.e. 'step1'
 * @param submitData {string} parameterized string of data to be submitted via ajax
 * @param showSuccess {boolean} true indicates that the success alert should be shown
 * @param toggleSubmit {boolean} true to flip the submit button into a 'next' button in the wizard
 */
function ajaxSubmitRequest(step, submitData, showSuccess, toggleSubmit) {
    submitData = (typeof submitData == 'undefined') ? 'action='+step : 'action='+step+'&' +submitData;
    showSuccess = (typeof showSuccess == 'undefined') ? true : showSuccess;
    toggleSubmit = (typeof toggleSubmit == 'undefined') ? false : toggleSubmit;
    $('#page').css('cursor','wait');
    if(toggleSubmit)
        wizard$.steps('disableButton', 'submit');
    $.post('ajax-steps.php', submitData)
        .done(function (data) {
            $('#page').css('cursor','default');
            try
            {
                var results = $.parseJSON(data);
                if(results[0] != '')
                    $("#"+step).replaceWith(results[0]);

                if(results[1] == false && showSuccess){
                    if($("#warningAjaxSubmit"+step).length > 0)
                        $("#warningAjaxSubmit"+step).fadeOut(500).remove();
                    if($('#success'+step).length == 0){
                        $("#"+step).append(formatAlert({
                            type:   'success',
                            ID:     '',
                            title:  'SUCCESS!',
                            text:   results[2],
                            icon:   'icon-ok',
                            step:   step,
                            hidden: 'hidden'
                        }));
                        $("#success"+step).fadeIn(500);
                    }
                    if(toggleSubmit)
                        wizard$.steps('hideButton','submit').steps('enableButton', 'next').steps('showButton', 'next');
                    if(step == 'step2')
                        databaseSuccess = true;
                    $("#"+step).removeClass('hidden');
                    return true;
                } else if(results[1] == true) {
                    if($("#warningAjaxSubmit"+step).length > 0)
                        $("#warningAjaxSubmit"+step).fadeOut(500).remove();
                    $('#dangerError'+step).remove();
                    $("#"+step).append(formatAlert({
                        type:   'danger',
                        ID:     'Error',
                        title:  'ERROR!',
                        text:   results[2],
                        icon:   'icon-warning-sign',
                        step:   step,
                        hidden: 'hidden'
                    }));
                    $('#dangerError'+step).fadeIn(500);
                    if(toggleSubmit)
                        wizard$.steps('enableButton', 'submit');
                    else
                        wizard$.steps('disableButton', 'next').steps('hideButton', 'next');
                    wizard$.steps('setStepToError');
                    $("#"+step).removeClass('hidden');
                    return false;
                }
                $("#"+step).removeClass('hidden');
                return !results[1];
            }  // End of 'try' section
            catch (err)
            {

                wizard$.steps('setStepToError');
                $("#"+step).replaceWith('<h2>FATAL ERROR OCCURRED</h2><p style="background-color: #FFA9A9; padding: 15px; border-radius: 10px">Unfortunately, something went very wrong with the' +
                    ' Installation Wizard.  We received an error message from the server.  Here are your options:</p>' +
                    '<ul style="margin-left: 50px;"><li>Refresh this page to restart the Wizard and try again.</li><li>Check the <a href="http://nzedbetter.org" target="_blank">nZEDbetter Wiki</a> for an answer.</li>' +
                    '<li>Create a new issue on the <a href="https://github.com/KurzonDax/nZEDbetter">nZEDbetter Github</a> site.</li></ul>' +
                    '<br />If you choose to report the issue on GitHub, please include <strong>all</strong> of the information below:<br />'+data);

            }
        })
        .fail(function(xhr,err,e)
        {
            console.log(xhr);
            $('#page').css('cursor','default');
            $(document).scrollTop(0);
            wizard$.steps('setStepToError');
            $("#"+step).append(formatAlert({
                type:   'danger',
                ID:     'AjaxError',
                title:  'ERROR!',
                text:   'The following error occurred while submitting your request:<br /> ' +
                    err + '<br />You should refresh the page and start the install wizard again.',
                icon:   'icon-warning-sign',
                step:   step,
                hidden: ''
            }));
            $("#"+step).removeClass('hidden');
            wizard$.steps('disableButton', 'submit').steps('disableButton', 'next');
            return false;

        });
}
/**
 *
 * @param obj$ {jQuery} DOM container with fields to be checked
 * @returns {boolean} true indicates one or more fields are blank/empty
 */
function checkBlankFields(obj$) {
    var blankFields = false;
    obj$.find(':input:enabled').each(function() {
        if($.trim($(this).val())=='' && $(this).prop('disabled') != true) {
            blankFields = true;
            var speed = 100;
            function anim (field$){
                field$.animate({'background-color':  "#FFFFFF"}, speed, 'swing').animate({'background-color': "#FFA9A9"}, speed, 'swing');
            };
            var loop = setInterval(anim($(this)), 300);
            anim($(this));
            $(this).addClass('invalid');
        } else {$(this).css('background-color','#FFF');}
    });

    return blankFields;
}

/**
 *
 * @param options {object}
 *          type:   'danger',
            ID:     'generic',
            title:  'WARNING!',
            text:   'This is the default warning text',
            icon:   'icon-warning-sign',
            step:   '',
            hidden: ''
 * @returns {string} HTML string that can be prepended/appended to a container
 */
function formatAlert(options) {
    var alertOptions = {
        type:   'danger',
        ID:     'generic',
        title:  'WARNING!',
        text:   'This is the default warning text',
        icon:   'icon-warning-sign',
        step:   '',
        hidden: ''  // this needs to be hidden: 'hidden' if the alert should not be immediately visible
    };
    $.extend(alertOptions, options);
    return '<div id="'+alertOptions.type+alertOptions.ID+alertOptions.step+'" class="alert-'+alertOptions.type+' clearfix" '+alertOptions.hidden+'>'+
        '<i class="'+alertOptions.icon+' icon-3x icon-alert-big pull-left"></i><div class="pull-left" style="width: 85%;"><b>'+alertOptions.title+'</b><br />' +
        alertOptions.text+'</div></div>';
}

<!-- **** Event Handlers **** -->

jQuery(function($){
    $("input[name=dbLocation]").click(function() {
        $("#sql_socket").prop('disabled', !($(this).attr('id')=='dbLocal')).toggleClass('invalid',($(this).attr('id')=='dbLocal' && $.trim($("#sql_socket").val()) == '') );
        $("#sql_port").prop('disabled', !($(this).attr('id')=='dbRemote')).toggleClass('invalid',($(this).attr('id')=='dbRemote' && $.trim($("#sql_port").val()) == '') );
        if($(this).attr('id')=='dbLocal'){
            $("#host").data('originalValue',$("#host").val());
            $("#host").val('localhost')
        } else {
            $("#host").val($("#host").data('originalValue'))
        }
    });

    $('input[type=text]').filter("input[name!=tmpfs]") .blur(function() {
        $(this).css('background-color','');
        $(this).toggleClass('invalid', $.trim($(this).val()) == '');
    });

    $("#chkUseAlternateNNTP").click(function() {
        $("#divAlternateNNTP").slideToggle(250);   //toggleClass('hidden', !($(this).prop('checked')));
    });

    $("#adminPass").passStrengthify({security:0, rawEntropy:true}).blur(function() {
        $(this).css('background-color','');
        $(this).toggleClass('invalid', $.trim($(this).val()) == '');
    });

    $("#adminPassConfirm").blur(function() {
        if($(this).val() != $("#adminPass").val()) {
            if($("#dangerPassNoMatchstep4").length == 0) {
                $("#step4").append(formatAlert({
                        type:   'danger',
                        ID:     'PassNoMatch',
                        title:  'WARNING!',
                        text:   'The passwords you entered do not match.  Please correct this.',
                        icon:   'icon-warning-sign',
                        step:   'step4',
                        hidden: ''  // this needs to be hidden: 'hidden' if the alert should not be immediately visible
                }));
            }
        } else {
            $("#dangerPassNoMatchstep4").remove();
        }
        $(this).css('background-color','');
        $(this).toggleClass('invalid', $.trim($(this).val()) == '');
    });

});
