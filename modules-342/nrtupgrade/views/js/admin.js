window.nrt_update = false;

$(document).ready(function(){
    $.xhrPool = [];
    $.xhrPool.abortAll = function() {
        $.each(this, function(jqXHR) {
            if (jqXHR && (jqXHR.readystate !== 4)) {
                jqXHR.abort();
            }
        });
    };

	$("a#upgradeNrtNow").click(function(e) {
        if(window.nrt_update){
            return;
        }

        window.nrt_update = true;

        $("a#upgradeNrtNow").addClass('hidden');
        $("#upgradeNrtLoading").removeClass('hidden');

        e.preventDefault();

        $("#currentlyNrtProcessing").show();

        doNrtAjaxRequest('upgradeNrtNow', {});
	});
});

function nrt_call_function(func) {
    this[func].apply(this, Array.prototype.slice.call(arguments, 1));
}

function addNrtQuickInfo(arrQuickInfo) {
    if (arrQuickInfo) {
        var $quickInfo = $("#quickNrtInfo");

        $quickInfo.show();

        for (var i = 0; i < arrQuickInfo.length; i++) {
            $quickInfo.append(arrQuickInfo[i] + "<div class=\"clear\"></div>");
        }

        $quickInfo.prop({scrollTop: $quickInfo.prop("scrollHeight")}, 1);
    }
}

function addNrtError(arrError) {
    if (typeof arrError !== "undefined" && arrError.length) {

        $("#errorNrtDuringUpgrade").show();

        var $infoError = $("#infoNrtError");

        for (var i = 0; i < arrError.length; i++) {
            $infoError.append(arrError[i] + "<div class=\"clear\"></div>");
        }

        $infoError.prop({scrollTop: $infoError.prop("scrollHeight")}, 1);
    }
}

function cleanNrtInfo() {
    $("#infoNrtStep").html("reset<br/>");
}

function updateNrtInfoStep(msg) {
    if (msg) {
        var $infoStep = $("#infoNrtStep");

        $infoStep.append(msg + "<div class=\"clear\"></div>");
        
        $infoStep.prop({scrollTop: $infoStep.prop("scrollHeight")}, 1);
    }
}

function ucNrtFirst(str) {
    if (str.length > 0) {
        return str[0].toUpperCase() + str.substring(1);
    }
    return str;
}

function handleNrtSuccess(res, action) {
    if (res.next !== "") {
        $("#" + res.next).addClass("nextNrtStep");

        doNrtAjaxRequest(res.next, res.nextParams);
    } else {
        addNrtQuickInfo([opNrtUpgrade.endOfProcess]);
    }
}

function startNrtProcess(type) {
    $("#currentNrtConfigurationBlock, #upgradeNrtButtonBlock").slideUp("fast");

    $("#activityNrtLogBlock").fadeIn("slow");

    $(window).bind("beforeunload", function(e) {
        if (confirm(opNrtUpgrade.updateInProgress)) {
            $.xhrPool.abortAll();
            $(window).unbind("beforeunload");
            return true;
        } else {
            if (type === "upgrade") {
                e.returnValue = false;
                e.cancelBubble = true;
                if (e.stopPropagation) {
                    e.stopPropagation();
                }
                if (e.preventDefault) {
                    e.preventDefault();
                }
            }
        }
    });
}

function afterUpgradeNrtNow(res) {
    startNrtProcess("upgrade");
    $("#upgradeNrtNow")
        .unbind()
        .replaceWith(
        "<span id=\"upgradeNrtNow\">"
        + opNrtUpgrade.upgradingTheme
        + " ...</span>"
    );

    $("#upgradeNrtLoading").addClass('hidden');
}

function afterUpgradeNrtComplete(res) {
    var params = res.nextParams;

    $("#pleaseNrtWait").hide();

    $("#upgradeNrtResultCheck")
    .html("<p>" + opNrtUpgrade.upgradeComplete + "</p>")
    .show();
    $("#infoNrtStep").html("<p class=\"alert alert-success\">" + opNrtUpgrade.upgradeComplete + "</p>");

    var todoList = opNrtUpgrade.todoList;
    var todoBullets = "<ul>";
    for (var i in todoList) {
        todoBullets += "<li>" + todoList[i] + "</li>";
    }

    todoBullets += "</ul>";

    $("#upgradeNrtResultToDoList")
        .html("<strong>" + opNrtUpgrade.todoListTitle + "</strong>")
        .append(todoBullets)
        .show();

    $(window).unbind("beforeunload");
}

function afterErrorNrt(res) {
    var params = res.nextParams;
    if (params.next === "") {
        $(window).unbind("beforeunload");
    }
    $("#pleaseNrtWait").hide();

    addNrtQuickInfo(["unbind :) "]);
}

function doNrtAjaxRequest(action, nextParams) {
    if (opNrtUpgrade._PS_MODE_DEV_ === true) {
        addNrtQuickInfo(["[DEV] ajax request : " + action]);
    }

    $("#pleaseNrtWait").show();

    $.ajax({
        type: "POST",
        url: opNrtUpgrade.ajaxUrl,
        async: true,
        dataType: 'json',
        data: {
            func: action,
            params: nextParams
        },
        beforeSend: function(jqXHR) {
            $.xhrPool.push(jqXHR);
        },
        complete: function(jqXHR) {
            $.xhrPool.pop();
        },
        success: function(res, textStatus, jqXHR) {
            $("#pleaseNrtWait").hide();

            addNrtQuickInfo(res.nextQuickInfo);
            addNrtError(res.nextErrors);
            updateNrtInfoStep(res.next_desc);

            var currentParams = res.nextParams;

            if (res.status === "ok") {
                $("#" + action).addClass("done");

                if (res.stepDone) {
                    $("#" + action).addClass("stepok");
                }

                var funcName = "after" + ucNrtFirst(action);

                if (typeof window[funcName] === "function") {
                    nrt_call_function(funcName, res);
                }

                handleNrtSuccess(res, action);
            } else {
                $("#" + action).addClass("done steperror");
                alert(opNrtUpgrade.errorDetectedDuring + " [" + action + "].");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $("#pleaseWait").hide();
            if (textStatus === "timeout") {
                if (action === "download") {
                    updateNrtInfoStep(opNrtUpgrade.cannotDownloadFile);
                } else {
                    updateNrtInfoStep("[Server Error] Timeout: " + opNrtUpgrade.downloadTimeout);
                }
            }
            else {
                try {
                    res = $.parseJSON(jqXHR.responseText);
                    addNrtQuickInfo(res.nextQuickInfo);
                    addNrtError(res.nextErrors);
                    updateNrtInfoStep(res.next_desc);
                } catch (e) {
                    updateNrtInfoStep("[Ajax / Server Error for action " + action + "] textStatus: \"" + textStatus + " \" errorThrown:\"" + errorThrown + " \" jqXHR: \" " + jqXHR.responseText + "\"");
                }
            }
        }
    });
}
