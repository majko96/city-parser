const path = "api/search";
$(document).ready(function () {
    $("#autocomplete-input").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: path,
                dataType: "json",
                data: {
                    query: request.term
                },
                delay: 250,
                success: function (data) {
                    response(data.map(item => ({ label: item.name, value: item.id })));
                }
            });
        },
        minLength: 1,
        select: function (event, ui) {
            window.location.href = '/city/' + ui.item.value;
        }
    });
});
