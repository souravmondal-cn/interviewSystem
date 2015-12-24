tinymce.init({selector: 'textarea'});

$(document).ready(function () {

    $('.table').DataTable();
    $('#userHist').hide();

    var isAdmin = $("#isAdmin").val();
    if (isAdmin === '1') {
        $("#addressDiv").hide();
        $("#locationDiv").hide();
        $("#fileUploadDiv").hide();
        $("#allowAccessDiv").hide();
    }

    if (isAdmin === '0') {
        $("#addressDiv").show();
        $("#locationDiv").show();
        $("#fileUploadDiv").show();
        $("#allowAccessDiv").show();
    }
});

$("#isAdmin").change(function () {
    var isAdmin = $("#isAdmin").val();

    if (isAdmin === '1') {
        $("#addressDiv").hide();
        $("#locationDiv").hide();
        $("#fileUploadDiv").hide();
        $("#allowAccessDiv").hide();
    }

    if (isAdmin === '0') {
        $("#addressDiv").show();
        $("#locationDiv").show();
        $("#fileUploadDiv").show();
        $("#allowAccessDiv").show();
    }
});

$('#userEmail').blur(function () {
    var email = $('#userEmail').val();
    $.ajax({
        url: basepath+'/checkEmail/' + email,
        method: 'post',
        success: function (data, status) {
            if (data == true) {
                $('#userEmailCheck').html(' &nbsp;&nbsp; Email id is already registered! &nbsp;');
                $('#userHist').show();
                $('#viewUserHistory').attr('href', basepath+'/viewHistory/' + email);
                $('#viewUserHistory').attr('class', 'btn btn-default');
                $('#userEmail').focus();
            }
            else {
                $('#userEmailCheck').html('');
                $('#userHist').hide();
            }
        }
    });
});

$('#userEmailId').blur(function () {
    var emailId = $('#userEmailId').val();
    $.ajax({
        url: basepath+'/checkEmail/' + emailId,
        method: 'post',
        success: function (data, status) {
            if (data != '1') {
                $('#checkEmailForExam').html('this email id is not registered!');
                $('#userEmailId').val('');
                $('#userEmailId').focus();
            }
            else {
                $('#checkEmailForExam').html('');
            }
        }
    });
});