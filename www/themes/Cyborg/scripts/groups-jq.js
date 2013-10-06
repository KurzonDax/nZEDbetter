/**
 * Created with JetBrains PhpStorm.
 * User: Randy
 * Date: 9/28/13
 * Time: 3:53 PM
 *
 */

/* Grey out and disable elements for groups that are resetting or purging */
$( document ).ready(function() {
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
});
jQuery(function($){

    <!-- **** Global Variables **** -->

    // pNotify stack position
    var stack_bottomright = {"dir1": "up", "dir2": "left", "firstpos1": 25, "firstpos2": 25};

    <!-- **** Popover Functions **** -->

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

    $("#chkSelectAll").change(function () {
        if ($("#chkSelectAll").prop('checked')== true) {
            $.each($(".group_check"), function () { $(this).prop('checked', true);})
            setButtons()
        } else {
            $.each($(".group_check"), function () { $(this).prop('checked', false);})
            setButtons()
        }
    });

    $(".group_check").change(function() {
            setButtons()
    });

    $("#group-invertSelection").click(function() {
        $.each($(".group_check"), function() {
            $(this).prop('checked', ($(this).prop('checked')!=true))
        });
    });

    <!-- **** Button Functions **** -->

    var groupID;
    var groupIDs;

    $("#group-delete").click(function() {
        if ($(".group_check:checked").length == 0) {
            alert("No groups selected to delete!");
        } else {
            var ids= getCheckedIDs();
            createNotification("growlDeletedGroup", { groupName: ids }, { expires:10000 });
            $.each($(".group_check:checked"), function() {$(this).parent().parent().remove()});
            setButtons()
            /* Need to write rest of this function, and create php script */
        }

    });

    $("#group-add").click( function() {
        $("#frmAddGroup")[0].reset();
        $("#frmBulkAddGroups")[0].reset();
        // $("#backfill_target_slider").slider('value', 0);
        $("#backfill_target").val(0);
        $("#modalAddGroups").css('margin-left',function(){ return (-($(this).width()/2)).toString()+"px"})
            .modal("show");

    });

    $("#btnAddGroupsSave").click( function() {
        $( "#modalAddGroups" ).modal('hide');
        if($("#frmAddGroup").parent().css("display") != "none") {
            /* Do something here to add a single new group */

        } else if($("#frmBulkAddGroups").parent().css("display") != "none") {
            /* Do something here to bulk add new groups */

        } else {
            /* Something went wrong.  Inform user and close dialog */

        }
    });

    $("input[type='radio']").on('change', function() {

       if($(this).prop('checked')==true){
           $(this).parent().parent().find("i").remove();
           $(this).parent().prepend("<i class='icon-ok'></i>");
       }

    });
    <!-- **** Form Validation Event Handlers **** -->

    var warningBackfill;

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

    $("#backfill_target").blur (function() {
        if($(this).val()>1900){
            if($("#warningBackfillTarget").length == 0) {
                $("#frmAddGroup").append('<div id="warningBackfillTarget" class="alert-warning alert-dismissable">'+
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

    $("#Bulkbackfill_target").blur (function() {
        if($(this).val()>1900){
            if($("#warningBackfillTarget").length == 0) {
                $("#frmBulkAddGroups").append('<div id="warningBackfillTarget" class="alert-warning alert-dismissable">'+
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

    // Add Group Name field  tabAddGroups
    var alertName;
    var warningName;

    $("input[name='groupName']").on('blur', function() {

        if(blankGroupName($(this)))
            return;
        checkField = badGroupName($(this));
    });
    <!-- **** Form Validation Functions **** -->
    
    function blankGroupName(objName) {
        if(objName.val().trim()=="") {
            objName.addClass('error-danger');
            if(objName.hasClass("error-warning"))
                objName.removeClass("error-warning");
            if($("#dangerAddNewGroupName").length == 0) {
                $("#frmAddGroup").append('<div id="dangerAddNewGroupName" class="alert-danger alert-dismissable">'+
                    '<b>Warning!</b> The group name field cannot be blank.<a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>'+
                    '</div>');
                alertName = setTimeout(function () {$("#dangerAddNewGroupName").remove();}, 10000);
            } else {
                clearTimeout(alertName);
                alertName = setTimeout(function () {$("#dangerAddNewGroupName").remove();}, 10000);
            }
            return true;
        } else if(objName.val().trim() != "" && objName.hasClass("error-danger")) {
            objName.removeClass("error-danger");
            if($("#dangerAddNewGroupName").length > 0) {
                clearTimeout(alertName);
                $("#dangerAddNewGroupName").remove();
            }
        }
        return false;
    }
    
    function badGroupName(objName) {
        if(!(objName.val().trim().match(/.+\..+\..+/))) {
            objName.addClass('error-warning');
            if($("#infoAddNewGroupName").length == 0) {
                $("#frmAddGroup").append('<div id="warningAddNewGroupName" class="alert-warning alert-dismissable">'+
                    '<b>Warning!</b> The group name you entered does not appear to be a valid newsgroup.<a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>'+
                    '</div>');
                warningName = setTimeout(function () {$("#warningAddNewGroupName").remove();}, 10000);
            } else {
                clearTimeout(warningName);
                warningName = setTimeout(function () {$("#warningAddNewGroupName").remove();}, 10000);
            }
            return true;
        } else if(objName.val().trim().match(/.+\..+\..+/) && objName.hasClass("error-warning")) {
            objName.removeClass("error-warning");
            if($("#warningAddNewGroupName").length > 0) {
                clearTimeout(warningName);
                $("#warningAddNewGroupName").remove();
            }
        }
        return false;
    }
    

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

    function setButtons() {
        var chkedGroups = $(".group_check:checked").length;
        if(chkedGroups == 0) {
            $("#btnMultiOps").html($("#btnMultiOps").html().replace('Groups...', 'Group...')); $("#btnMultiOps").attr("disabled", "disabled").prop('disabled',true);
        } else if(chkedGroups == 1) {
            $("#btnMultiOps").html($("#btnMultiOps").html().replace('Groups...', 'Group...')); $("#btnMultiOps").removeAttr("disabled").prop('disabled',false);
        } else {
            $("#btnMultiOps").html($("#btnMultiOps").html().replace( 'Group...', 'Groups...')); $("#btnMultiOps").removeAttr("disabled").prop('disabled',false);
        }
    }

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
    }

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
    }

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
    }
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
                },
                error: function(xhr,err,e) { alert( "Error in ajax_group_status: " + err ); }
            });
        }
        else
        {
            alert('Weird.. what group id are looking for?');
        }
    }

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
                },
                error: function(xhr,err,e) { alert( "Error in ajax_backfill_status: " + err ); }
            });
        }
        else
        {
            alert('Weird.. what group id are looking for?');
        }
    }

    <!-- **** Restrict Number Entry to Just Numbers -->

    // We only want integers in the number entry fields
    $('input[type=number], .edit_files, .edit_backfill, .edit_size').keypress( function(e) {
        // I knew the following was too simple to be true.  Didn't work on Firefox
        /* if (!(String.fromCharCode(e.keyCode).match(/[0-9]/))) {
            return false; */

        var a = [];
        var k = e.which;
        console.log(k);
        a.push(0);  // Mozilla and their backwards ways sends a 0 for the tab key instead of 9
        a.push(8);  // Backspace key (required for Firefox)
        a.push(9);  // Tab Key
        a.push(13); // Enter key (Required for Chrome and Firefox)
        for (i = 48; i < 58; i++)
            a.push(i);

        if (!($.inArray(k,a)>=0))
            e.preventDefault();

    });

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
                if(data.indexOf('backfill')>0)
                    setBackfillLinks();
                else
                    setActiveLinks();
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
                '<b><i class="icon-warning-sign"></i> Warning!</b> Group(s) are in the process of being reset. Please <strong>DO NOT</strong> ' +
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
                $("#group_list").prepend('<div id="dangerGroupsPurgetError" class="alert-danger alert-pagetop">'+
                    '<b><i class="icon-warning-sign"></i> ERROR!</b> The following error occurred while attempting to purge the groups:<br /> ' +
                    err + '<br />You should <a href="'+document.URL+'">refresh</a> the page and attempt the operation again.</div>');
            }
        });

    });
});

