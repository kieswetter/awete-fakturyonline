// JavaScript Document
/**
* sRequest = 'a=1&b=3&c=e'
* sTargetUrl = url of php script
* sBackFok = name of back function to be called if all goes right 
**/
function useAjax(sRequest, sTargetUrl, sBackFok, sBackFfailed ) {
    sTargetUrl = "/blog/_php/ajax/" + sTargetUrl;
	var http_request = false;
    //var string = document.getElementById('string').value;
    var request = sRequest;

    if (window.XMLHttpRequest) {
        http_request = new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        try {
          http_request = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (eror) {
          http_request = new ActiveXObject("Microsoft.XMLHTTP");
        }
    }
    //alert("ajax: " + sTargetUrl);
    http_request.onreadystatechange = function() { resultAjax(http_request, sBackFok, sBackFfailed); };
    http_request.open('POST', sTargetUrl, true);
    http_request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    http_request.send(request);
}

function resultAjax(http_request, fOk, fFailed) {
    if (http_request.readyState == 4) {
        if (http_request.status == 200) {
            //eval(fOk + "(http_request.responseText);");
            handlerAjaxOk(fOk, http_request.responseText);
        } else {
            handlerAjaxFailed(fFailed, http_request);            
        }
    }
}

function handlerAjaxOk(fOk, resp)
{
  //alert(fOk + "\n" + resp);
  eval(fOk + "('" + resp + "');");
}
function handlerAjaxFailed(fFailed, resp)
{
  eval(fFailed + "();");
}
  
