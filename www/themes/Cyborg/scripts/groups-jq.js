/**
 * Created with JetBrains PhpStorm.
 * User: Randy
 * Date: 9/28/13
 * Time: 3:53 PM
 *
 */

<!-- **** Global Variables **** -->


// pNotify stack position
var stack_bottomright = {"dir1": "up", "dir2": "left", "firstpos1": 25, "firstpos2": 25};
var order_by = 'name_ASC';

jQuery(function($){

    $(window).load(function() {
        $("#footer").pinFooter('absolute');
    });

    $(window).resize(function() {
        $("#footer").pinFooter('absolute');
    });

    $( document ).ready(function() {
        addLinkHandlers();
        setDates();
        getGroupStats();

    });

    $('.selectpicker').each(function() {
        $(this).selectpicker().selectpicker('setStyle', 'btn-tertiary');
    });



    <!-- **** Popover Functions **** -->

    $(".table-help-icon-modal").each( function(index) {
        $(this).popover({
            placement: 'top',
            html    : 'true',
            title   : '<span class="text-info"><strong>'+$(this).attr("data-title")+'</strong></span>'+
                '<button id="popovercloseid-'+$(this).attr('id')+'" name="popClose" type="button" class="close">&times;</button>',
            content : 'test',
            trigger : 'manual',
            container: '#modalAddGroups'
        });
        $(this).click(function(e){
            $(this).popover('toggle');
        });
    });

    $(".help-icon-modal").each( function(index) {
        $(this).popover({
            placement: 'top',
            html    : 'true',
            title   : '<span class="text-info"><strong>'+$(this).attr("data-title")+'</strong></span>'+
                '<button id="popovercloseid-'+$(this).attr('id')+'" name="popClose" type="button" class="close">&times;</button>',
            content : 'test',
            trigger : 'manual',
            container: '#modalAdvancedSearch'
        });
        $(this).click(function(e){
            $(this).popover('toggle');
        });
    });

    $("#modalAddGroups").on('click', function(e) {
        if(e.target.id.replace(/-(.+)$/,'')=="popovercloseid")
        {
            e.stopImmediatePropagation();
            $("#"+e.target.id.replace('popovercloseid-','')).popover('toggle');
        }
    });
    $(document).click(function(e) {
        if(e.target.id.replace(/-(.+)$/,'')=="popovercloseid")
        {
            $("#"+e.target.id.replace('popovercloseid-','')).popover('toggle');
        }
    });

    <!-- **** Checkbox Functionality **** -->

    $("#group-invertSelection").click(function() {
        $.each($(".group_check"), function() {
            $(this).prop('checked', ($(this).prop('checked')!=true))
        });
        setButtons();
    });

    <!-- **** Button Functions **** -->

    var groupID;
    var groupIDs;

    $("#group-add").click( function() {
        $("#frmAddGroup")[0].reset();
        $("#frmBulkAddGroups")[0].reset();
        $("#frmBulkAddGroups, #frmAddGroup").find(".alert-danger").remove();
        $("#frmBulkAddGroups, #frmAddGroup").find(".alert-warning").remove();
        $("#frmBulkAddGroups, #frmAddGroup").find(".error-danger, .error-warning").removeClass('error-danger').removeClass('error-warning')
        $("#bulkListList, #groupActiveNo, #groupBackfillNo, #bulkActiveNo, #bulkBackfillNo").prop('checked', true)
            .trigger('change').parent().addClass('active').siblings('label').removeClass('active');
        $("#modalAddGroups").css('margin-left',function(){ return (-($(this).width()/2)).toString()+"px"})
            .modal("show");
    });

    $("#searchGroupName").keypress( function(e) {
         if(e.which == 13) {
             e.preventDefault();
             $("#btnGroupSearch").trigger('click');
         }
     });

    $("#btnGroupSearch").click(function (event) {
        $('#page').css('cursor','wait');
        var submitData = {};
        submitData['action'] = 'search';
        submitData['name'] = "LIKE '%"+$("#searchGroupName").val().trim()+"%'";
        submitData['offset'] = 0;
        submitData['ob'] = $("#order_by").val();
        submitData['rand'] = Math.random();
        // Reset the advanced search form because any criteria there no longer applies
        $("#frmAdvancedSearch")[0].reset();
        $("input[id|='chkAdvancedSearch']").each(function() {
            $(this).prop('checked', false);
            $(this).parent().parent().parent().find('[name=advSearchControl]').prop('disabled', true);
        });
        $("#advancedSearchActiveEither, #advancedSearchBackfillEither").prop('checked', true)
            .trigger('change').parent().addClass('active').siblings('label').removeClass('active');
        $("#order_by").val(submitData['ob']);  // Reset the order by field
        ajaxSubmitSearchRequest(submitData, true);
    });

    $("#btnAdvancedSearch").click( function() {
        $("#modalAdvancedSearch").css('margin-left',function(){ return (-($(this).width()/2)).toString()+"px"})
            .modal("show");
    });

    $("input[type='radio']").on('change', function() {
       if($(this).prop('checked')==true){
           $(this).parent().parent().find("i").remove();
           $(this).parent().prepend("<i class='icon-ok'></i>");
       }
    });

    <!-- **** General Helper Functions **** -->

    function getCheckedIDs(returnArray) {
        returnArray = typeof returnArray !== 'undefined' ? returnArray : false;
        var arrGroups = [];
        $.each($(".group_check:checked"), function () {
            arrGroups.push($(this).parent().parent().attr("id").replace("grouprow-",""));
        });
        if(returnArray==true)
            return arrGroups;
        else
            return  $.param({id : arrGroups});
    }

    function disableCheckedItems() {
        $.each($(".group_check:checked"), function () {
            $(this).attr("disabled", "disabled").parent().parent().find("a").addClass("disabled").attr("disabled", "disabled").prop("disabled", true);
            $(this).parent().parent().find("div, td").removeClass("edit_files edit_size edit_backfill edit_name edit_desc pointer").off('dblclick');
            $(this).parent().parent().find("td").css("background-color", "#BBB");
        });
    }

    <!-- **** Restrict Number Entry to Just Numbers -->

    // We only want integers in the number entry fields
    $('input[type=number], .edit_files, .edit_backfill, .edit_size').keypress( function(e) {

        var a = [];
        var k = e.which;
        a.push(0);  // Mozilla and their backwards ways sends a 0 for the tab key instead of 9
        a.push(8);  // Backspace key (required for Firefox)
        a.push(9);  // Tab Key
        a.push(13); // Enter key (Required for Chrome and Firefox)
        for (i = 48; i < 58; i++)
            a.push(i);
        if (!($.inArray(k,a)>=0))
            e.preventDefault();
    });

    <!-- Selected Group Dropdown Functions -->

    $("a[id|='groupMulti']").on('click', function() {
        if ($(".group_check:checked").length == 0) {
            alert("No groups selected to process!");
            return;
        }
        var action = $(this).attr('id').replace('groupMulti-','');
        var rand_no = Math.random();
        groupIDs = getCheckedIDs();
        $.ajax({
            url       : WWW_TOP + '/admin/ajax-group-ops.php',
            data      : 'action='+action+'&rand=' + rand_no + '&' + groupIDs,
            dataType  : "html",
            type      : "POST",
            success   : function(data)
            {
                /*if(data.indexOf('backfill')>0)
                    setBackfillLinks();
                else
                    setActiveLinks();*/
                if(action=='allActive' || action == 'allInactive' || action == 'toggleActive')
                    setAllActiveLinks(action);
                else if(action=='allBackfillActive' || action=='allBackfillInactive' || action == 'toggleBackfill')
                    setAllBackfillLinks(action);
                displayNotification(data);
            },
            error   : function(xhr,err,e)
            {
                setButtons();
                $(document).scrollTop(0);
                $("#group_list").prepend('<div id="dangerGroupsMultiError" class="alert-danger alert-pagetop">'+
                    '<b><i class="icon-warning-sign"></i> ERROR!</b> The following error occurred while attempting to to process your request:<br /> ' +
                    err + '<br />You should <a href="'+document.URL+'">refresh</a> the page and attempt the operation again.</div>');
            }
        });
    });

    function getGroupList() {
        var groupList = '';
        if($(".group_check:checked").length == 0)
            displayNotification("No groups selected to delete.", "Unable to Delete Groups", 'danger', 'icon-warning-sign');
        $.each($(".group_check:checked"), function() {
            groupList = groupList + $(this).parent().siblings("td[id|='name']").attr('id').replace('name-','').replace(/_/g,'.') + "<br />";
        });
        return groupList;
    }

    $("#group-Delete").click(function() {
        $("#modalDeleteGroupsList").html(getGroupList());
        $("#formDeleteGroups").find("input[type='checkbox']").prop('checked', false);
        $("#modalDeleteGroups").css('margin-left',function(){ return (-($(this).width()/2)).toString()+"px"})
            .modal("show");
    });

    $("#group-Reset").click(function() {
        $("#modalResetGroupsList").html(getGroupList());
        $("#chkDeleteCollections").prop('checked', false);
        $("#modalResetGroups").css('margin-left',function(){ return (-($(this).width()/2)).toString()+"px"})
            .modal("show");
    });

    $("#group-Purge").click(function() {
        $("#modalPurgeGroupsList").html(getGroupList());
        $("#modalPurgeGroups").css('margin-left',function(){ return (-($(this).width()/2)).toString()+"px"})
            .modal("show");
    });

    $("#btnConfirmDeleteGroups").click(function(){
        if ($(".group_check:checked").length == 0) {
            alert("No groups selected to delete!");
            return;
        }
        var action = 'deleteGroups';
        if ($("#chkFormDeleteCollections").prop('checked')==true) {
            action = action + "&deleteCollections=1"
        }
        if ($("#chkFormDeleteReleases").prop('checked')==true) {
            action = action + "&deleteReleases=1"
        }
        if(action.length>12){ // Only show alert if we're deleting collections or releases
            $("#group_list").prepend('<div id="warningGroupsDeleteStart" class="alert-warning alert-pagetop">'+
                '<b><i class="icon-warning-sign"></i> Warning!</b> Group(s) are in the process of being deleted. Please <strong>DO NOT</strong> ' +
                'refresh or leave this page until the process is complete.  This process may take some time to finish.</div>');
        }
        var rand_no = Math.random();
        groupIDs = getCheckedIDs();
        $.ajax({
            url       : WWW_TOP + '/admin/ajax-group-ops.php',
            data      : 'action='+action+'&rand=' + rand_no + '&' + groupIDs,
            dataType  : "html",
            type      : "POST",
            success   : function(data)
            {
                $("#group_list").find("#warningGroupsDeleteStart").remove();
                $.each($(".group_check:checked"), function() {
                    $(this).parent().parent().remove();
                });
                setButtons();
                displayNotification(data);
            },
            error   : function(xhr,err,e)
            {
                setButtons();
                $(document).scrollTop(0);
                $("#group_list").prepend('<div id="dangerGroupsDeleteError" class="alert-danger alert-pagetop">'+
                    '<b><i class="icon-warning-sign"></i> ERROR!</b> The following error occurred while attempting to delete the groups:<br /> ' +
                    err + '<br />You should <a href="'+document.URL+'">refresh</a> the page and attempt the operation again.</div>');
            }
        });

    });

    $("#btnConfirmResetGroups").click(function(){
        if ($(".group_check:checked").length == 0) {
            alert("No groups selected to reset!");
            return;
        }
        disableCheckedItems();
        $(document).scrollTop(0);
        $("#group_list").prepend('<div id="warningGroupsResetStart" class="alert-warning alert-pagetop">'+
            '<b><i class="icon-warning-sign"></i> Warning!</b> Group(s) are in the process of being reset. Please <strong>DO NOT</strong> ' +
            'refresh or leave this page until the process is complete.</div>');
        var action = 'resetGroups';
        if($("#chkDeleteCollections").prop('checked')){
            action = action + '&deleteCollections=1';
            $("#warningGroupsResetStart").text().append(" This process may take some time to finish.");
        }
        var rand_no = Math.random();
        groupIDs = getCheckedIDs();
        $.ajax({
            url       : WWW_TOP + '/admin/ajax-group-ops.php',
            data      : 'action='+action+'&rand=' + rand_no + '&' + groupIDs,
            dataType  : "html",
            type      : "POST",
            success   : function(data)
            {
                /*$.each($(".group_check:checked"), function () {
                    $(this).prop('checked', false);
                });*/
                $("#group_list").find("#warningGroupsResetStart").remove();
                setButtons();
                $(document).scrollTop(0);
                $("#group_list").prepend('<div id="successGroupsResetEnd" class="alert-success alert-pagetop">'+
                    '<b><i class="icon-ok-sign"></i> Alert!</b> Group(s) have been reset. It is strongly recommended that you <a href="'+document.URL+'">refresh</a> the page ' +
                    'to reflect the changes.</div>');
                displayNotification(data);
            },
            error   : function(xhr,err,e)
            {
                $("#group_list").find("#warningGroupsResetStart").remove();
                setButtons();
                $(document).scrollTop(0);
                $("#group_list").prepend('<div id="dangerGroupsResetError" class="alert-danger alert-pagetop">'+
                    '<b><i class="icon-warning-sign"></i> ERROR!</b> The following error occurred while attempting to reset the groups:<br /> ' +
                    err + '<br />You should <a href="'+document.URL+'">refresh</a> the page and attempt the operation again.</div>');
            }
        });
    });

    $("#btnConfirmPurgeGroups").click(function(){
        if ($(".group_check:checked").length == 0) {
            alert("No groups selected to purge!");
            return;
        }
        $("#group_list").prepend('<div id="warningGroupsPurgeStart" class="alert-warning alert-pagetop">'+
            '<b><i class="icon-warning-sign"></i> Warning!</b> Group(s) are in the process of being purged. Please <strong>DO NOT</strong> ' +
            'refresh or leave this page until the process is complete. This process may take some time to finish.</div>');
        disableCheckedItems();
        var action = 'purgeGroups';
        var rand_no = Math.random();
        groupIDs = getCheckedIDs();
        $.ajax({
            url       : WWW_TOP + '/admin/ajax-group-ops.php',
            data      : 'action='+action+'&rand=' + rand_no + '&' + groupIDs,
            dataType  : "html",
            type      : "POST",
            success   : function(data)
            {
                /*$.each($(".group_check:checked"), function () {
                    $(this).prop('checked', false);
                });*/
                $("#group_list").find("#warningGroupsPurgeStart").remove();
                setButtons();
                $(document).scrollTop(0);
                $("#group_list").prepend('<div id="successGroupsPurgeEnd" class="alert-success alert-pagetop">' +
                    '<b><i class="icon-ok-sign"></i> Alert!</b> Group(s) have been purged. It is strongly recommended that you <a href="'+document.URL+'">refresh</a> the page ' +
                    'to reflect the changes.</div>');
                displayNotification(data);
            },
            error   : function(xhr,err,e)
            {
                $("#group_list").find("#warningGroupsPurgeStart").remove();
                setButtons();
                $(document).scrollTop(0);
                $("#group_list").prepend('<div id="dangerGroupsPurgeError" class="alert-danger alert-pagetop">'+
                    '<b><i class="icon-warning-sign"></i> ERROR!</b> The following error occurred while attempting to purge the groups:<br /> ' +
                    err + '<br />You should <a href="'+document.URL+'">refresh</a> the page and attempt the operation again.</div>');
            }
        });

    });

    <!-- **** Add Groups Dialog Functions **** -->

    $("#btnAddGroupsSave").click( function() {
        var submitData = '';
        if($("#frmAddGroup").parent().css("display") != "none") {
            if(blankGroupName($("#groupName")))
                return;
            if(badGroupName($("#groupName")))
                return;
            submitData = "action=addgroup&rand="+Math.random().toString()+"&"+getAddFormValues(false);
            $( "#modalAddGroups" ).modal('hide');
        } else if($("#frmBulkAddGroups").parent().css("display") != "none") {
            /* Do something here to bulk add new groups */
            if(!validateBulkList($("#bulkList"), true))
                return;
            // Check list type and validate
            submitData = "action=bulkadd&rand="+Math.random().toString()+"&"+getAddFormValues(true);
            $( "#modalAddGroups" ).modal('hide');
        } else {
            /* Something went wrong.  Inform user and close dialog */
            $("#modalAddGroups").modal('hide');
            $("#group_list").prepend('<div id="dangerAddGroupsError" class="alert-danger alert-pagetop">'+
                '<b><i class="icon-warning-sign"></i> ERROR!</b> Something went drastically wrong attempting to add the group(s) ' +
                'you requested.  Please <a href="'+document.URL+'">refresh</a> the page and attempt the operation again.</div>');
            return;
        }
        $("#modalAddGroupsSuccess").find("tbody").find("tr").each(function() {$(this).remove()});
        $("#addGroupsWaiting").removeClass("hidden");
        $("#addGroupsFinished").addClass("hidden");
        $("#btnAddGroupsOk").addClass("hidden");
        $("#btnAddGroupsRefresh").prop('href', document.url);
        $("#modalAddGroupsSuccessFooter").addClass("hidden");
        $("p[id|='pNotAdded']").each(function() {$(this).remove()});
        $("#modalAddGroupsSuccess").css('margin-left',function(){ return (-($(this).width()/2)).toString()+"px"}).modal('show');
        $.ajax({
            url       : WWW_TOP + '/admin/ajax-group-ops.php',
            data      : submitData,
            dataType  : "html",
            type      : "POST",
            success   : function(data)
            {
                var returnData = jQuery.parseJSON(data);
                var groupsNotAdded = [];
                $.each(returnData, function(key, value) {
                    if(value.status.match(/#!GROUP EXISTS/) != null) {
                        groupsNotAdded.push(value.name);
                        return true;
                    }
                    $("#modalAddGroupsTable").append('<tr><td>'+value.id+'</td><td>'+value.name+'</td><td>'+value.status+'</td></tr>')
                });
                if(groupsNotAdded.length > 0){
                    var notAddedMsg = '';
                    $("#addGroupsFinished").append('<p id="pNotAdded-1">The following groups were not added because they already exist in the database:</p>');
                    $.each(groupsNotAdded, function(i, value) {
                        notAddedMsg = notAddedMsg + value + "<br />"
                    });
                    $("#addGroupsFinished").append("<p id='pNotAdded-2' class='uncolumned-list'>"+notAddedMsg+"</p> ")
                }
                $("#addGroupsWaiting").addClass("hidden");
                $("#modalAddGroupsSuccessFooter").removeClass("hidden");
                $("#addGroupsFinished").removeClass("hidden");
                $("#btnAddGroupsOk").removeClass("hidden");
            },
            error   : function(xhr,err,e)
            {
                $(document).scrollTop(0);
                $("#group_list").prepend('<div id="dangerAddGroupsError" class="alert-danger alert-pagetop">'+
                    '<b><i class="icon-warning-sign"></i> ERROR!</b> The following error occurred while attempting to add the groups:<br /> ' +
                    err + '<br />You should <a href="'+document.URL+'">refresh</a> the page and attempt the operation again.</div>');
            }
        });
    });

    function getAddFormValues(bulk) {
        var formData = {};
        var bulkListParm='';
        if(bulk == true) {
            bulk='bulk';
            bulkListParm = $.param({groupName : parseBulkList($("#bulkList").val())}) + "&";
            formData.regexList = $("input[name=bulkListType]:checked").val();
            formData.updateExisting = $("#bulkUpdateExistingGroups").prop('checked');
        } else {
            bulk='';
            formData.groupName = $("#groupName").val();
        }
        formData.description = $("#"+bulk+"description").val();
        formData.backfillTarget = $("#"+bulk+"backfillTarget").val();
        formData.minFiles = $("#"+bulk+"minFiles").val();
        formData.minSize = $("#"+bulk+"minSizeValue").val();
        formData.active = $("input[name="+bulk+"groupActive]:checked").val();
        formData.backfill = $("input[name="+bulk+"groupBackfill]:checked").val();
        return bulkListParm + $.param(formData);
    }

    <!-- **** Form Validation Event Handlers **** -->

    var warningBackfill;
    var warningBulkBackfill;

    // Select text on click
    $(function(){
        $("input[type='Text']").on("click",function(){
            if (typeof this.selectionStart == "number")
                this.select();
        });
    });
    // Select text on click
    $(function(){
        $("input[type='Number']").on("click",function(){
            if (typeof this.selectionStart == "number")
                this.select();
        })
            .blur(function() {
                if($(this).val() == '')
                    $(this).val(0);
            })
    });

    $("#groupMinSize").blur(function() {
        $("#minSizeValue").val(function() {
            return $("#groupMinSize").val() * $("#minSizeUnitValue").val();
        });
    });

    $("#minSizeUnitValue").change(function() {
        $("#minSizeValue").val(function() {
            return $("#groupMinSize").val() * $("#minSizeUnitValue").val();
        });
    });

    $("#bulkminSize").blur(function() {
        $("#bulkminSizeValue").val(function() {
            return $("#bulkMinSize").val() * $("#bulkminSizeUnitValue").val();
        });
    });

    $("#bulkminSizeUnitValue").change(function() {
        $("#bulkminSizeValue").val(function() {
            return $("#bulkminSize").val() * $("#bulkminSizeUnitValue").val();
        });
    });

    $("#backfillTarget").blur (function() {
        if($(this).val()>1900){
            if($("#warningBackfillTarget").length == 0) {
                $("#frmAddGroup").prepend('<div id="warningBackfillTarget" class="alert-warning alert-dismissable">'+
                    '<b>Warning!</b> The maximum allowable number of backfill days is 1900.<a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>'+
                    '</div>');
                warningBackfill = setTimeout(function () {$("#warningBackfillTarget").remove();}, 10000);
            } else {
                clearTimeout(warningBackfill);
                warningBackfill = setTimeout(function () {$("#warningBackfillTarget").remove();}, 10000);
            }
            $(this).val(1900)
        }
    });

    $("#bulkbackfillTarget").blur (function() {
        if($(this).val()>1900){
            if($("#warningBackfillTarget").length == 0) {
                $("#frmBulkAddGroups").prepend('<div id="warningBackfillTarget" class="alert-warning alert-dismissable">'+
                    '<b>Warning!</b> The maximum allowable number of backfill days is 1900.<a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>'+
                    '</div>');
                warningBulkBackfill = setTimeout(function () {$("#warningBackfillTarget").remove();}, 10000);
            } else {
                clearTimeout(warningBulkBackfill);
                warningBackfill = setTimeout(function () {$("#warningBackfillTarget").remove();}, 10000);
            }
            $(this).val(1900)
        }
    });

    $("#bulkList").blur(function() {
        validateBulkList($(this), false);
    });

    // Add Group Name field  tabAddGroups
    
    $("input[name='groupName']").on('blur', function() {

        if(blankGroupName($(this)))
            return;
        badGroupName($(this));
    });

    <!-- **** Add Groups Form Validation Functions **** -->

    var dangerBlankName;
    var warningBadName;
    var dangerInvalidList;
    var dangerBlankListAlert;
    var dangerInvalidChars;

    function blankGroupName(objName) {
        if(objName.val().trim()=="") {
            objName.addClass('error-danger');
            if(objName.hasClass("error-warning"))
                objName.removeClass("error-warning");
            if($("#dangerAddNewGroupName").length == 0) {
                $("#frmAddGroup").prepend('<div id="dangerAddNewGroupName" class="alert-danger alert-dismissable">'+
                    '<b><b><i class="icon-warning-sign"></i> Warning!</b> The group name field cannot be blank.<a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>'+
                    '</div>');
                dangerBlankName = setTimeout(function () {$("#dangerAddNewGroupName").remove();}, 10000);
            } else {
                clearTimeout(dangerBlankName);
                dangerBlankName = setTimeout(function () {$("#dangerAddNewGroupName").remove();}, 10000);
            }
            return true;
        } else if(objName.val().trim() != "" && objName.hasClass("error-danger")) {
            objName.removeClass("error-danger");
            if($("#dangerAddNewGroupName").length > 0) {
                clearTimeout(dangerBlankName);
                $("#dangerAddNewGroupName").remove();
            }
        }
        return false;
    }

    function badGroupName(objName) {
        if(!(objName.val().trim().match(/.+\..+\..+/))) {
            objName.addClass('error-warning');
            if($("#infoAddNewGroupName").length == 0) {
                $("#frmAddGroup").prepend('<div id="warningAddNewGroupName" class="alert-warning alert-dismissable">'+
                    '<b><b><i class="icon-warning-sign"></i> Warning!</b> The group name you entered does not appear to be a valid newsgroup.<a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>'+
                    '</div>');
                warningBadName = setTimeout(function () {$("#warningAddNewGroupName").remove();}, 10000);
            } else {
                clearTimeout(warningBadName);
                warningBadName = setTimeout(function () {$("#warningAddNewGroupName").remove();}, 10000);
            }
            return true;
        } else if(objName.val().trim().match(/.+\..+\..+/) && objName.hasClass("error-warning")) {
            objName.removeClass("error-warning");
            if($("#warningAddNewGroupName").length > 0) {
                clearTimeout(warningBadName);
                $("#warningAddNewGroupName").remove();
            }
        }
        return false;
    }

    function validateBulkList(objName, chkBblank) {
        if(chkBblank && blankBulkList(objName))
            return false;
        if(objName.val().trim() != "" && objName.hasClass("error-danger")){
            objName.removeClass("error-danger");
            if($("#dangerBlankList").length > 0) {
                clearTimeout(dangerBlankListAlert);
                $("#dangerBlankList").remove();
            }
        }
        if($("input[name=bulkListType]:checked").val()==0){ //List type (i.e. not regex)
            if(!(invalidBulkList(objName))){
                $("#bulkgroupName").val(stripCommas(objName.val().trim()));
                return true;
            }
        } else if($("input[name=bulkListType]:checked").val()==1 && !(blankBulkList(objName))) {
            $("#bulkgroupName").val(stripPipes(objName.val().trim()));
            return true;
        }
        return false;
    }

    function blankBulkList(objName) {
        if(objName.val().trim()=="") {
            objName.addClass('error-danger');
            if($("#dangerBlankList").length == 0) {
                $("#frmBulkAddGroups").prepend('<div id="dangerBlankList" class="alert-danger alert-dismissable">'+
                    '<b><i class="icon-warning-sign"></i> Warning!</b> The group list field cannot be blank.<a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>'+
                    '</div>');
                dangerBlankListAlert = setTimeout(function () {$("#dangerBlankList").remove();}, 15000);
            } else {
                clearTimeout(dangerBlankListAlert);
                dangerBlankListAlert = setTimeout(function () {$("#dangerBlankList").remove();}, 15000);
            }
            return true;
        } else if(objName.val().trim() != "" && objName.hasClass("error-danger")) {
            objName.removeClass("error-danger");
            if($("#dangerBlankList").length > 0) {
                clearTimeout(dangerBlankListAlert);
                $("#dangerBlankList").remove();
            }
        }
        return false;
    }

    function invalidBulkList(objName) {
        var invalidChars = /[^A-Za-z0-9\.\n,\-]/g
        if($("input[name=bulkListType]:checked").val() == 0){ //List type (i.e. not regex)
            if($("#bulkList").val().match(/\n/) != null && $("#bulkList").val().match(/,/) != null) {
                objName.addClass('error-danger');
                if($("#dangerInvalidList").length == 0) {
                    $("#frmBulkAddGroups").prepend('<div id="dangerInvalidList" class="alert-danger alert-dismissable">'+
                        '<b><i class="icon-warning-sign"></i> Warning!</b> The group list you entered appears to be invalid.  It must be either: ' +
                        '<ul><li>A comma separated list with no new-line characters, or</li><li>It must be one group per line</li></ul><a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>'+
                        '</div>');
                    dangerInvalidList = setTimeout(function () {$("#dangerInvalidList").remove();}, 15000);
                } else {
                    clearTimeout(dangerInvalidList);
                    dangerInvalidList = setTimeout(function () {$("#dangerInvalidList").remove();}, 15000);
                }
                return true;
            } else if (invalidChars.test(objName.val())) {
                objName.addClass('error-danger');
                if($("#dangerInvalidChars").length == 0) {
                    $("#frmBulkAddGroups").prepend('<div id="dangerInvalidChars" class="alert-danger alert-dismissable">'+
                        '<b><i class="icon-warning-sign"></i> Warning!</b> You have invalid characters in the newsgroup names listed.<br />Valid ' +
                        'characters are A-Z, a-z, 0-9, periods, and hyphens.<a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>'+
                        '</div>');
                    dangerInvalidChars = setTimeout(function () {$("#dangerInvalidList").remove();}, 15000);
                    if($("#dangerInvalidList").length > 0) {
                        $("#dangerInvalidList").remove();
                        clearTimeout(dangerInvalidList);
                    }
                } else {
                    clearTimeout(dangerInvalidList);
                    dangerInvalidChars = setTimeout(function () {$("#dangerInvalidList").remove();}, 15000);
                }
                return true;
            } else {
                $("#dangerInvalidList").remove();
                $("#dangerInvalidChars").remove();
                return false;
            }
        }
        return false;
    }

    function stripPipes(textValue) {
        if($("input[name=bulkListType]:checked").val()== 1){ //Regex type (i.e. not a List)
            if(textValue.substr(0,1)=="|") {
                textValue = textValue.substr(1);
            }
            if(textValue.substr(-1)=="|") {
                textValue = textValue.substr(0, textValue.length-1);
            }
        }
        return textValue;
    }

    function stripCommas(textValue) {
        if($("input[name=bulkListType]:checked").val()== 0){ //List type (i.e. not a regex)
            if(textValue.substr(0,1)==",") {
                textValue = textValue.substr(1);
            }
            if(textValue.substr(-1)==",") {
                textValue = textValue.substr(0, textValue.length-1);
            }
        }
        return textValue;
    }

    function parseBulkList(list) {
        list = list.trim();
        if($("input[name=bulkListType]:checked").val()== 0){ //List type (i.e. not a regex)
            if(list.match(/,/) != null) {
                return list.split(",");
            } else {
                return list.split(/\n/mg);
            }
        } else {
            return {0:list};
        }
    }

    <!-- **** Advanced Search Functions **** -->

    // $("input[id|='datAdvancedSearch']").prop('disabled', true);

    $("button[id|='calAdvancedSearch']").click(function() {
        $(this).parent().parent().find('input[type="text"]').datepick({
            dateFormat: 'yyyy-mm-dd',
            showOnFocus: false,
            alignment: 'bottomRight',
            yearRange: 'c+0:c-6',
            minDate: -1930,
            maxDate: +0,
            onClose: function(dates) {
                $(this).datepick('destroy');
            }
        }).datepick('show');
    });

    $("input[id|='chkAdvancedSearch']").click(function() {
        $(this).parent().parent().parent().find('[name=advSearchControl]').prop('disabled', !($(this).prop('checked')==true));
        $(this).parent().parent().parent().find('select[id|=selAdvancedSearch]').selectpicker('refresh');
        if($(this).prop('checked'))
            $(this).parent().parent().parent().find("input[name=advSearchControl], button[id|=calAdvancedSearch], #selAdvancedSearch-minSizeUnitValue").prop('disabled',
                ($(this).parent().parent().parent().find("select[id|=selAdvancedSearch]").val()=='IS NULL'));
        $("#btnResetAdvancedSearch").prop('disabled',
            (!($("input[id|='chkAdvancedSearch']:checked").length)) && ($("input[name=advancedSearchBackfill]:checked").val()==-1) && ($("input[name=advancedSearchActive]:checked").val()==-1));
    });

    $("input[id|='datAdvancedSearch']").blur(function () {
        if($(this).val().substr(0,1)== '-')
            $(this).val(parseRelativeDate($(this).val()));
    });

    $("#specialAdvancedSearch-minSize").blur(function() {
       $("#numAdvancedSearch-minSize").val($(this).val()*$("#selAdvancedSearch-minSizeUnitValue").val());
    });

    $("#selAdvancedSearch-minSizeUnitValue").change(function () {
        $("#numAdvancedSearch-minSize").val($(this).val()*$("#specialAdvancedSearch-minSize").val());
    });

    $("input[name=advancedSearchBackfill], input[name=advancedSearchActive]").change(function() {
        if($(this).val() != -1)
            $("#btnResetAdvancedSearch").prop('disabled', false);
        else
            $("#btnResetAdvancedSearch").prop('disabled',
                (!($("input[id|='chkAdvancedSearch']:checked").length)) && ($("input[name=advancedSearchBackfill]:checked").val()==-1) && ($("input[name=advancedSearchActive]:checked").val()==-1));
    });

    $("select[id|=selAdvancedSearch]").change(function () {
        $(this).parent().parent().find("input[name=advSearchControl], button[id|=calAdvancedSearch], #selAdvancedSearch-minSizeUnitValue").prop('disabled', ($(this).val()=='IS NULL'));
        $("#selAdvancedSearch-minSizeUnitValue").selectpicker('refresh');

    });

    $("#btnAdvancedSearchOk").click(function() {
        $("#modalAdvancedSearch").modal("hide");
        var submitData = getSearchFieldData(0);
        ajaxSubmitSearchRequest(submitData, true);
    });


    $("#btnShowAllGroups").click(function () {
        var submitData = {};
        submitData['action'] = 'search';
        submitData['offset'] = 0;
        submitData['name'] = "LIKE '%%'";
        submitData['ob'] = $("#order_by").val();
        submitData['rand'] = Math.random();
        $("#frmAdvancedSearch")[0].reset();
        $("input[id|='chkAdvancedSearch']").each(function() {
            $(this).prop('checked', false);
            $(this).parent().parent().parent().find('[name=advSearchControl]').prop('disabled', true);
        });
        $("#advancedSearchActiveEither, #advancedSearchBackfillEither").prop('checked', true)
            .trigger('change').parent().addClass('active').siblings('label').removeClass('active');
        $('.selectpicker').each(function() {
            $(this).selectpicker('refresh')
        });
        $(this).addClass('disabled');
        ajaxSubmitSearchRequest(submitData);
    });

    $("#btnResetAdvancedSearch").click(function() {
        var orderBy = $("#order_by").val();
        $("#frmAdvancedSearch")[0].reset();
        $("input[id|='chkAdvancedSearch']").each(function() {
            $(this).prop('checked', false);
            $(this).parent().parent().parent().find('[name=advSearchControl]').prop('disabled', true);
        });
        $("#advancedSearchActiveEither, #advancedSearchBackfillEither").prop('checked', true)
            .trigger('change').parent().addClass('active').siblings('label').removeClass('active');
        $('.selectpicker').each(function() {
            $(this).selectpicker('refresh');
        });
        $(this).prop('disabled', true);
        $("order_by").val(orderBy);
    });

    function ajaxSubmitSearchRequest(submitData, enableShowAllButton) {
        if(arguments.length>1 && enableShowAllButton === true)
            $("#btnShowAllGroups").removeClass('disabled');
        $('#page').css('cursor','wait');
        $.post('/admin/ajax-group-search-results.php', submitData)
            .done(function (data) {
                $("#group_list").replaceWith(data);
                $('#page').css('cursor','default');
                addLinkHandlers();
                setButtons();
                setDates();
            })
            .fail(function(xhr,err,e)
            {
                console.log(xhr);
                $('#page').css('cursor','default');
                $(document).scrollTop(0);
                $("#group_list").prepend('<div id="dangerSearchRequestError" class="alert-danger alert-pagetop">'+
                    '<b><i class="icon-warning-sign"></i> ERROR!</b> The following error occurred while submitting your request:<br /> ' +
                    err + '<br />You should <a href=>refresh</a> the page and attempt the operation again.</div>');

            });
    }

    function getSearchFieldData(offset) {
        var fieldData = {};
        var fieldName = '';
        var fieldOp = '';
        fieldData['action'] = 'search';
        fieldData['offset'] = typeof offset !== 'undefined' ? offset : 0;
        fieldData['ob'] = $("#order_by").val();
        fieldData['rand'] = Math.random();
        $("input[id|=txtAdvancedSearch]").each(function() {
           if($("#"+$(this).attr('id').replace(/txt/,'chk')).prop('checked') == true) {
               fieldName = $(this).attr('id').replace('txtAdvancedSearch-','').toLowerCase();
               fieldOp = $("#"+$(this).attr('id').replace('txt','sel')).val();
               switch (fieldOp){
                   case 'EQUALS':
                       fieldData[fieldName] = "= '" + $.trim($(this).val()) + "'";
                       break;
                   case 'LIKE':
                       fieldData[fieldName] = "LIKE '%" + $.trim($(this).val()) + "%'";
                       break;
                   case 'NOT LIKE':
                       fieldData[fieldName] = "NOT LIKE '%" + $.trim($(this).val()) + "%'";
                       break;
                   case 'IS NULL':
                       fieldData[fieldName] = "IS NULL";
               }
           }
        });
        $("input[id|=datAdvancedSearch]").each(function() {
            if($("#"+$(this).attr('id').replace('dat','chk')).prop('checked') == true) {
                fieldName = $(this).attr('id').replace('datAdvancedSearch-','').toLowerCase();
                fieldOp = $("#"+$(this).attr('id').replace('dat','sel')).val();
                switch (fieldOp){
                    case 'EQUALS':
                        fieldData[fieldName] = "LIKE '%" + $.trim($(this).val()) + "%'";
                        break;
                    case 'NOT':
                        fieldData[fieldName] = "NOT LIKE '%" + $.trim($(this).val()) + "%'";
                        break;
                    case 'IS NULL':
                        fieldData[fieldName] = "IS NULL";
                        break;
                    default:
                        fieldData[fieldName] = fieldOp + " '" + $.trim($(this).val()) + "'";
                }
            }
        });
        if($("[name=advancedSearchActive]:checked").val() != -1) {
            fieldData['active'] = '= ' + $("[name=advancedSearchActive]:checked").val();
        }
        if($("[name=advancedSearchBackfill]:checked").val() != -1) {
            fieldData['backfill'] = '= ' +  $("[name=advancedSearchBackfill]:checked").val();
        }
        $("input[id|=numAdvancedSearch]").each(function() {
            if($("#"+$(this).attr('id').replace('num','chk')).prop('checked') == true) {
                fieldName = $(this).attr('id').replace('numAdvancedSearch-','').toLowerCase();
                fieldOp = $("#"+$(this).attr('id').replace('num','sel')).val();
                switch (fieldOp){
                    case 'EQUALS':
                        fieldData[fieldName] = "= '" + $.trim($(this).val()) + "'";
                        break;
                    case 'NOT':
                        fieldData[fieldName] = "!= '" + $.trim($(this).val()) + "'";
                        break;
                    case 'IS NULL':
                        fieldData[fieldName] = "IS NULL";
                        break;
                    default:
                        fieldData[fieldName] = fieldOp + " '" + $.trim($(this).val()) + "'";
                }
            }
        });
        return  $.param(fieldData);
    }

    /* *******************************************************************
            IMPORTANT  The following function must be called on document
            load, and every time the results table is rebuilt.  The purpose
            of the function is to rebuild all of the event handlers that
            are associated with the table (popovers, inline edits, activate
            buttons, etc.
        ***************************************************************** */

    function addLinkHandlers() {

        <!-- **** Inline Edit Functions **** -->

        $(".edit_desc").editable("ajax-group-ops.php", {
            indicator   : '<img src="'+www_top+'/../themes/'+user_style+'/images/indicator.gif">',
            type        : "textarea",
            submit      : "<i class='icon-ok btn-primary btn-mini inline-btn'></i>",
            cancel      : "<i class='icon-remove btn-deactivate btn-mini inline-btn'></i>",
            style       : "width: 200px; display: inline-table; line-height: 29px; font-size: 12px; margin-bottom: 0; margin-top: 5px;",
            name        : "desc",
            id          : "id",
            method      : "POST",
            event       : "dblclick",
            placeholder : "<div style='color:#CFCFCF'>Double click to add description</div>",
            tooltip     : "Double click to edit description",
            rows        : "0",
            cols        : "0",
            submitdata  : {action : "edit"}
        });

        $(".edit_name").editable("ajax-group-ops.php", {
            indicator   : '<img src="'+www_top+'/../themes/'+user_style+'/images/indicator.gif">',
            type        : "text",
            submit      : "<i class='icon-ok btn-primary btn-mini inline-btn'></i>",
            cancel      : "<i class='icon-remove btn-deactivate btn-mini inline-btn'></i>",
            style       : "width: 200px; display: inline-table; line-height: 29px; font-size: 12px;",
            name        : "name",
            id          : "id",
            method      : "POST",
            event       : "dblclick",
            placeholder : "<div style='color:#CFCFCF'>Double click to add group name</div>",
            tooltip     : "Double click to edit group name",
            data        : function (e) { return (e.replace(/a\.b/gi, "alt.binaries")) },
            callback    : function(value, settings, revert, submitdata) {

                if(value=="#!GROUP EXISTS" && submitdata.name.replace(/alt\.binaries/gi, "a.b") != revert) {
                    // Group already exists, so we're not going to add it again.
                    this.innerText=revert;
                    $.pnotify({
                        title: 'Group Change Failed',
                        text: 'The group name you attempted to use ('+submitdata.name+') is already in the database.',
                        type: 'error',
                        opacity: .9,
                        history: false,
                        icon: 'icon-warning-sign',
                        addclass: "stack-bottomright",
                        stack: stack_bottomright

                    });
                }
                else if (value=="#!GROUP EXISTS" && submitdata.name.replace(/alt\.binaries/gi, "a.b") == revert)
                {
                    // Group name didn't change, so just revert back to original
                    this.innerText=revert;
                } else {
                    // Group name changed, and it's not the same as original
                    $.pnotify({
                        title: 'Group Change Successful',
                        text: 'The group name ('+value+') was successfully updated in the database.',
                        type: 'success',
                        history: false,
                        opacity: .9,
                        icon: 'icon-ok-circle',
                        addclass: "stack-bottomright",
                        stack: stack_bottomright
                    });
                }
            },
            submitdata  : {action : "edit"}
        });

        $(".edit_files").editable("ajax-group-ops.php", {
            indicator   : '<img src="'+www_top+'/../themes/'+user_style+'/images/indicator.gif">',
            type        : "text",
            submit      : "<i class='icon-ok btn-primary btn-mini inline-btn'></i>",
            cancel      : "<i class='icon-remove btn-deactivate btn-mini inline-btn'></i>",
            style       : "width: 60px; display: inline-table; line-height: 29px; font-size: 12px; margin-bottom: 0; margin-top: 5px;",
            name        : "files",
            id          : "id",
            method      : "POST",
            event       : "dblclick",
            tooltip     : "Double click to edit minimum files",
            select      : true,
            submitdata  : {action : "edit"}
        });

        $(".edit_size").editable("ajax-group-ops.php", {
            indicator   : '<img src="'+www_top+'/../themes/'+user_style+'/images/indicator.gif">',
            type        : "text",
            submit      : "<i class='icon-ok btn-primary btn-mini inline-btn'></i>",
            cancel      : "<i class='icon-remove btn-deactivate btn-mini inline-btn'></i>",
            style       : "width: 60px; display: inline-table; line-height: 29px; font-size: 12px; margin-bottom: 0; margin-top: 5px;",
            name        : "size",
            id          : "id",
            method      : "POST",
            event       : "dblclick",
            tooltip     : "Double click to edit minimum size",
            select      : true,
            data        : function (e) { return (e.replace(/,| MB/g, '') * 1048576); },
            submitdata  : {action : "edit"}
        });

        $(".edit_backfill").editable("ajax-group-ops.php", {
            indicator   : '<img src="'+www_top+'/../themes/'+user_style+'/images/indicator.gif">',
            type        : "text",
            submit      : "<i class='icon-ok btn-primary btn-mini inline-btn'></i>",
            cancel      : "<i class='icon-remove btn-deactivate btn-mini inline-btn'></i>",
            style       : "width: 60px; display: inline-table; line-height: 29px; font-size: 12px; margin-bottom: 0; margin-top: 5px;",
            name        : "backfill",
            id          : "id",
            method      : "POST",
            event       : "dblclick",
            select      : true,
            tooltip     : "Double click to edit target backfill days",
            submitdata  : {action : "edit"}
        });

        <!-- **** Activate/Deactivate Button Handlers **** -->

        $("a[id|='btnActivate']").on('click', function() {
            ajax_group_status($(this).attr('id').replace('btnActivate-',''), 1);
        });

        $("a[id|='btnDeactivate']").on('click', function() {
            ajax_group_status($(this).attr('id').replace('btnDeactivate-',''), 0);
        });

        $("a[id|='btnBackfillDeactivate']").on('click', function() {
            ajax_backfill_status($(this).attr('id').replace('btnBackfillDeactivate-',''), 0);
        });

        $("a[id|='btnBackfillActivate']").on('click', function() {
            ajax_backfill_status($(this).attr('id').replace('btnBackfillActivate-',''), 1);
        });

        <!-- **** Restrict Number Entry to Just Numbers -->

        $('.edit_files, .edit_backfill, .edit_size').keypress( function(e) {

            var a = [];
            var k = e.which;
            a.push(0);  // Mozilla and their backwards ways sends a 0 for the tab key instead of 9
            a.push(8);  // Backspace key (required for Firefox)
            a.push(9);  // Tab Key
            a.push(13); // Enter key (Required for Chrome and Firefox)
            for (i = 48; i < 58; i++)
                a.push(i);
            if (!($.inArray(k,a)>=0))
                e.preventDefault();
        });

        <!-- **** Set checkboxes **** -->

        $(".group_check").change(function() {
            setButtons()
        });

        $("#chkSelectAll").change(function () {
            if ($("#chkSelectAll").prop('checked')== true) {
                $.each($(".group_check"), function () { $(this).prop('checked', true);})
                setButtons()
            } else {
                $.each($(".group_check"), function () { $(this).prop('checked', false);})
                setButtons()
            }
        });

        <!-- **** Popover Boxes from Help Icons **** -->

        $(".table-help-icon").each( function(index) {
            $(this).popover({
                placement: 'top',
                html    : 'true',
                title   : '<span class="text-info"><strong>'+$(this).attr("data-title")+'</strong></span>'+
                    '<button id="popovercloseid-'+$(this).attr('id')+'" name="popClose" type="button" class="close">&times;</button>',
                content : 'test',
                trigger : 'manual'
            });
            $(this).click(function(e){
                $(this).popover('toggle');
            });
        });

        <!-- Grey out and disable elements for groups that are resetting or purging -->

        $.each($("tr[id|='disabled']"), function () {
            $(this).find(".group_check").attr("disabled", "disabled");
            $(this).find("a").addClass("disabled").attr("disabled", "disabled").prop("disabled", true);
            $(this).find("div, td").removeClass("edit_files edit_size edit_backfill edit_name edit_desc pointer").off('dblclick');
            $(this).find("td").css("background-color", "#BBB");
        });
        if($("tr[id|='disabled']").length > 0) {
            $("#group_list").prepend('<div id="dangerGroupsProcessing" class="alert-danger alert-pagetop">'+
                '<b><i class="icon-warning-sign"></i> Alert!</b> Group(s) are currently processing either a reset or purge request.  It is recommended that you <a href="'+document.URL+'">refresh</a> the page periodically ' +
                'to reflect the changes.  Until the process is completed, you will be unable to manage these groups.</div>');
        }

        $(".sort-icons").click(function () {
            $("#order_by").val($(this).attr('id'));
            ajaxSubmitSearchRequest(getSearchFieldData($("#current_offset").val()));
        });

        $("#"+$("#order_by").val()).addClass('sort-active');

        $("a[name|=pager]").click(function() {
            $("#current_offset").val($(this).attr('data-offset'));
            ajaxSubmitSearchRequest(getSearchFieldData($(this).attr('data-offset')));
        });

        $("select[name=pagerselect]").change(function () {
            $("#current_offset").val($("option:selected", this).attr('data-offset'));
            ajaxSubmitSearchRequest(getSearchFieldData($("option:selected", this).attr('data-offset')));
        });


    }

    <!-- **** HELPER FUNCTIONS **** -->
    /**
     * ajax_group_status()
     *
     * @param id        group id
     * @param what    0 = deactive, 1 = activate
     */
    function ajax_group_status(id, what)
    {
        // no caching of results
        var rand_no = Math.random();
        if (what != undefined)
        {
            $.ajax({
                url       : WWW_TOP + '/admin/ajax-group-ops.php?rand=' + rand_no,
                data      : { id: id, group_status: what, action: 'edit' },
                dataType  : "html",
                type      : "POST",
                success   : function(data)
                {
                    setActiveLinks($("#chk-"+id));
                    displayNotification(data);
                    getGroupStats();
                },
                error: function(xhr,err,e) { alert( "Error in ajax_group_status: " + err ); }
            });
        }
        else
        {
            alert('Weird.. what group id are looking for?');
        }
    };

    /**
     * ajax_backfill_status()
     *
     * @param id        group id
     * @param what    0 = deactive, 1 = activate
     */
    function ajax_backfill_status(id, what)
    {
        // no caching of results
        var rand_no = Math.random();
        if (what != undefined)
        {
            $.ajax({
                url       : WWW_TOP + '/admin/ajax-group-ops.php?rand=' + rand_no,
                data      : { id: id, backfill_status: what, action: 'edit' },
                dataType  : "html",
                type      : "POST",
                success   : function(data)
                {
                    setBackfillLinks($("#chk-"+id));
                    displayNotification(data);
                    getGroupStats();
                },
                error: function(xhr,err,e) { alert( "Error in ajax_backfill_status: " + err ); }
            });
        }
        else
        {
            alert('Weird.. what group id are looking for?');
        }
    };

    function setActiveLinks(obj) {
        var objArr = [];
        (typeof obj !== 'undefined') ? objArr.push(obj) : objArr = $(".group_check:checked");
        $.each(objArr, function () {
            groupID = $(this).attr("id").replace("chk-","");
            if($('#group-list-table').find("#btnActivate-"+groupID).length>0){
                $('td#group-' + groupID).html('<a id="btnDeactivate-'+ groupID +'" class="noredtext btn btn-deactivate btn-xs">Deactivate</a>');
                $('a#btnDeactivate-'+groupID).on('click', function() {
                    ajax_group_status(groupID, 0);});
            } else if($('#group-list-table').find("#btnDeactivate-"+groupID).length>0){
                $('td#group-' + groupID).html('<a id="btnActivate-'+ groupID +'" class="noredtext btn btn-activate btn-xs">Activate</a>');
                $('a#btnActivate-'+groupID).on('click', function() {
                    ajax_group_status(groupID, 1);});
            }
        });
        getGroupStats();
    };

    function setBackfillLinks(obj) {
        var objArr = [];
        (typeof obj !== 'undefined') ? objArr.push(obj) : objArr = $(".group_check:checked");
        $.each(objArr, function () {
            groupID = $(this).attr("id").replace("chk-","");
            if($('#group-list-table').find("#btnBackfillActivate-"+groupID).length>0){
                $('td#backfill-' + groupID).html('<a id="btnBackfillDeactivate-'+ groupID +'" class="noredtext btn btn-deactivate btn-xs">Deactivate</a>');
                $('a#btnBackfillDeactivate-'+groupID).on('click', function() {
                    ajax_backfill_status(groupID, 0);});
            } else if($('#group-list-table').find("#btnBackfillDeactivate-"+groupID).length>0){
                $('td#backfill-' + groupID).html('<a id="btnBackfillActivate-'+ groupID +'" class="noredtext btn btn-activate btn-xs">Activate</a>');
                $('a#btnBackfillActivate-'+groupID).on('click', function() {
                    ajax_backfill_status(groupID, 1);});
            }
        });
        getGroupStats();
    };

    function setAllActiveLinks(action, obj) {
        var objArr = [];
        (typeof obj !== 'undefined') ? objArr.push(obj) : objArr = $(".group_check:checked");
        $.each(objArr, function () {
            groupID = $(this).attr("id").replace("chk-","");
            if(action == 'allActive'){
                if($('#group-list-table').find("#btnActivate-"+groupID).length>0){
                    $('td#group-' + groupID).html('<a id="btnDeactivate-'+ groupID +'" class="noredtext btn btn-deactivate btn-xs">Deactivate</a>');
                    $('a#btnDeactivate-'+groupID).on('click', function() {
                        ajax_group_status(groupID, 0);});
                }
            } else if(action =='allInactive') {
                if($('#group-list-table').find("#btnDeactivate-"+groupID).length>0){
                    $('td#group-' + groupID).html('<a id="btnActivate-'+ groupID +'" class="noredtext btn btn-activate btn-xs">Activate</a>');
                    $('a#btnActivate-'+groupID).on('click', function() {
                        ajax_group_status(groupID, 1);});
                }
            } else if(action == 'toggleActive') {
                if($('#group-list-table').find("#btnDeactivate-"+groupID).length>0){
                    $('td#group-' + groupID).html('<a id="btnActivate-'+ groupID +'" class="noredtext btn btn-activate btn-xs">Activate</a>');
                    $('a#btnActivate-'+groupID).on('click', function() {
                        ajax_group_status(groupID, 1);});
                } else {
                    $('td#group-' + groupID).html('<a id="btnDeactivate-'+ groupID +'" class="noredtext btn btn-deactivate btn-xs">Deactivate</a>');
                    $('a#btnDeactivate-'+groupID).on('click', function() {
                        ajax_group_status(groupID, 0);});
                }
            }
        });
        getGroupStats();
    };

    function setAllBackfillLinks(action, obj) {
        var objArr = [];
        (typeof obj !== 'undefined') ? objArr.push(obj) : objArr = $(".group_check:checked");
        $.each(objArr, function () {
            groupID = $(this).attr("id").replace("chk-","");
            if(action == 'allBackfillActive'){
                if($('#group-list-table').find("#btnBackfillActivate-"+groupID).length>0){
                    $('td#backfill-' + groupID).html('<a id="btnBackfillDeactivate-'+ groupID +'" class="noredtext btn btn-deactivate btn-xs">Deactivate</a>');
                    $('a#btnBackfillDeactivate-'+groupID).on('click', function() {
                        ajax_backfill_status(groupID, 0);});
                }
            } else if(action =='allBackfillInactive') {
                if($('#group-list-table').find("#btnBackfillDeactivate-"+groupID).length>0){
                    $('td#backfill-' + groupID).html('<a id="btnBackfillActivate-'+ groupID +'" class="noredtext btn btn-activate btn-xs">Activate</a>');
                    $('a#btnBackfillActivate-'+groupID).on('click', function() {
                        ajax_backfill_status(groupID, 1);});
                }
            } else if(action == 'toggleBackfill') {
                if($('#group-list-table').find("#btnBackfillDeactivate-"+groupID).length>0){
                    $('td#backfill-' + groupID).html('<a id="btnBackfillActivate-'+ groupID +'" class="noredtext btn btn-activate btn-xs">Activate</a>');
                    $('a#btnBackfillActivate-'+groupID).on('click', function() {
                        ajax_backfill_status(groupID, 1);});
                } else {
                    $('td#backfill-' + groupID).html('<a id="btnBackfillDeactivate-'+ groupID +'" class="noredtext btn btn-deactivate btn-xs">Deactivate</a>');
                    $('a#btnBackfillDeactivate-'+groupID).on('click', function() {
                        ajax_backfill_status(groupID, 0);});
                }
            }
        });
        getGroupStats();
    };
    function displayNotification(text, title, type, icon ) {
        title = typeof title !== 'undefined' ? title : 'Group Change Successful';
        type = typeof type !== 'undefined' ? type : 'success';
        icon = typeof icon !== 'undefined' ? icon : 'icon-ok-circle';
        $.pnotify({
            title: title,
            text: text,
            type: type,
            history: false,
            opacity: .9,
            icon: icon,
            addclass: "stack-bottomright",
            stack: stack_bottomright,
            animation: 'show',
            delay: 12000
        });
    };

    function setButtons() {
        var chkedGroups = $(".group_check:checked").length;
        if(chkedGroups == 0) {
            $("#btnMultiOps").html($("#btnMultiOps").html().replace('Groups...', 'Group...')); $("#btnMultiOps").attr("disabled", "disabled").prop('disabled',true);
            $("#chkSelectAll").prop('checked', false);
        } else if(chkedGroups == 1) {
            $("#btnMultiOps").html($("#btnMultiOps").html().replace('Groups...', 'Group...')); $("#btnMultiOps").removeAttr("disabled").prop('disabled',false);
        } else {
            $("#btnMultiOps").html($("#btnMultiOps").html().replace( 'Group...', 'Groups...')); $("#btnMultiOps").removeAttr("disabled").prop('disabled',false);
        }
    };

    function timeAgo(dateString, showAgo) {
        var second = 1000,
            minute = second * 60,
            hour = minute * 60,
            day = hour * 24,
            week = day * 7,
            halfyear = day * 183,
            month = day * 30,
            year = day * 365;

        dateString = dateString.replace(/\s/,'T'); //Had to do this because IE is fucking retarded
        var rightNow = new Date();
        var then = new Date(dateString);
        var offset = new Date().getTimezoneOffset() * minute;  //For simplicity, we're going to assume server is same timezone as local browser

    /* if ($.browser.msie) {
        // IE can't parse these crazy Ruby dates
        then = Date.parse(dateString.replace(/( \+)/, ' UTC$1'));
    } */
        var ago = (typeof showAgo != 'undefined' && showAgo == true) ? ' ago' : ''; 
        var diff = rightNow - then - offset;
        // console.log("rightnow = "+rightNow+"  then = "+then+" offset = "+offset);

        if (isNaN(diff) || diff < 0)
            return "n/a"; // return blank string if unknown
        if (diff < second * 2)
            return "Moments ago";
        if (diff < minute)
            return Math.floor(diff / second) + " seconds" + ago;
        if (diff < minute * 2)
            return "1 minute"+ ago;
        if (diff < hour)
            return Math.floor(diff / minute) + " minutes" + ago;
        if (diff < hour * 2)
            return (diff / hour).toFixed(1)+" hours" + ago;
        if (diff < day)
            return  (diff / hour).toFixed(1) + " hours" + ago;
        if (diff < month)
            return (diff / day).toFixed(1) + " days" + ago;
        if (diff < halfyear)
            return (diff / week).toFixed(1) + " weeks" + ago;
        if (diff < year)
            return (diff / month).toFixed(1) + " months" + ago;
        else
            return (diff / year).toFixed(1) + " years" + ago;
    };

    function setDates() {
        $("td[id|=dateTime]").each(function () {
            $(this).html(timeAgo($(this).attr('data-date'), false))
        })
    };

    function parseRelativeDate(relativeDate) {
        var today = new Date().getTime();
        var timeSpan = relativeDate.match(/[DdWwMmYy]/);
        var offset = Number(relativeDate.match(/\d+/));
        // for simplicity, we're not going to worry about DST, or differences in days in a month, or leap years
        var day = 86400000,
            week = day * 7,
            month = day * 30,
            year = day * 365;
        var delta = 0;
        switch (timeSpan.toString().toLowerCase().trim()) {
            case 'd':
                delta = offset * day;
                break;
            case 'w':
                delta = offset * week;
                break;
            case 'm':
                delta = offset * month;
                break;
            case 'y':
                delta = offset * year;
                break;
            default:
                delta = 0;
        }
        var resultDate = new Date(today-delta);

        return resultDate.getFullYear()+'-'
            +(resultDate.getMonth()+1).toString().replace(/^\d$/,'0'+(resultDate.getMonth()+1))+'-'
            +(resultDate.getDate()).toString().replace(/^\d$/,'0'+resultDate.getDate());

    };

    function getGroupStats()
    {
        $.post('ajax-group-ops.php','action=getGroupStats')
            .done(function(data) {
                var results = $.parseJSON(data);
                $("#totalGroups").html(results['totalGroups']);
                $("#activeGroups").html(results['activeGroups']);
                $("#inactiveGroups").html(results['inactiveGroups']);
                $("#backfillGroups").html(results['backfillGroups']);
                $("#inactiveBackfillGroups").html(results['inactiveBackfillGroups']);
                $("#notUpdated").html(results['notUpdated']);
            })
    }

});

(function($) {
    // plugin definition
    $.fn.pinFooter = function(options) {
        // Get the height of the footer and window + window width
        var wH = $(window).height();
        var wW = getWindowWidth();
        var fH = $(this).outerHeight(true);
        var bH = $("body").outerHeight(true);
        var mB = parseInt($("body").css("margin-bottom"));

        if (options == 'relative') {
            if (bH > getWindowHeight()) {
                $(this).css("position","absolute");
                $(this).css("width",wW + "px");
                $(this).css("top",bH - fH + "px");
                $("body").css("overflow-x","hidden");
            } else {
                $(this).css("position","fixed");
                $(this).css("width",wW + "px");
                $(this).css("top",wH - fH + "px");
            }
        } else { // Pinned option
            // Set CSS attributes for positioning footer
            $(this).css("position","fixed");
            $(this).css("width",wW + "px");
            $(this).css("top",wH - fH + "px");
            $("body").css("height",(bH + mB) + "px");
        }
    };

    // private function for debugging
    function debug($obj) {
        if (window.console && window.console.log) {
            window.console.log('Window Width: ' + $(window).width());
            window.console.log('Window Height: ' + $(window).height());
        }
    };

    // Dependable function to get Window Height
    function getWindowHeight() {
        var windowHeight = 0;
        if (typeof(window.innerHeight) == 'number') {
            windowHeight = window.innerHeight;
        }
        else {
            if (document.documentElement && document.documentElement.clientHeight) {
                windowHeight = document.documentElement.clientHeight;
            }
            else {
                if (document.body && document.body.clientHeight) {
                    windowHeight = document.body.clientHeight;
                }
            }
        }
        return windowHeight;
    };

    // Dependable function to get Window Width
    function getWindowWidth() {
        var windowWidth = 0;
        if (typeof(window.innerWidth) == 'number') {
            windowWidth = window.innerWidth;
        }
        else {
            if (document.documentElement && document.documentElement.clientWidth) {
                windowWidth = document.documentElement.clientWidth;
            }
            else {
                if (document.body && document.body.clientWidth) {
                    windowWidth = document.body.clientWidth;
                }
            }
        }
        return windowWidth;
    };
})(jQuery);