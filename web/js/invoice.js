$(document).ready(function() {
    if ($(".unedited").length) {
        $('.btn-success').attr({
            disabled: true,
            title: "Пожалуйста, заполните журнал посещений за указанные даты"
        })
    }
    else if ($("#lesson-total").text() == "0") {
        $('.btn-success').attr({
            disabled: true,
            title: "Нет данных о посещениях за указанный период"
        })
    }
    else {
        $('.btn-success').attr("disabled", false);
    }
});