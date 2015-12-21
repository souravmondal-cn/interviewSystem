tinymce.init({selector: 'textarea'});

$(document).ready(function () {

    $('.table-striped').DataTable();
    $('#userHist').hide();

    var isAdmin = $("#isAdmin").val();
    if (isAdmin === '1') {
        $("#addressDiv").hide();
        $("#locationDiv").hide();
        $("#fileUploadDiv").hide();
    }

    if (isAdmin === '0') {
        $("#addressDiv").show();
        $("#locationDiv").show();
        $("#fileUploadDiv").show();
    }
});

$("#isAdmin").change(function () {
    var isAdmin = $("#isAdmin").val();

    if (isAdmin === '1') {
        $("#addressDiv").hide();
        $("#locationDiv").hide();
        $("#fileUploadDiv").hide();
    }

    if (isAdmin === '0') {
        $("#addressDiv").show();
        $("#locationDiv").show();
        $("#fileUploadDiv").show();
    }
});

$('#userEmail').blur(function () {
    var email = $('#userEmail').val();
    $.ajax({
        url: '/checkEmail/' + email,
        method: 'post',
        success: function (data, status) {
            if (data == true) {
                $('#userEmailCheck').html(' &nbsp;&nbsp; Email id is already registered! &nbsp;');
                $('#userHist').show();
                $('#viewUserHistory').attr('href', '/viewHistory/' + email);
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
        url: '/checkEmail/' + emailId,
        method: 'post',
        success: function (data, status) {
            if (data === '0') {
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