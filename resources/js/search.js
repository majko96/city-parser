$(document).ready(function () {
    const path = "api/search";
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
                    response(data.map(item => ({
                        label: item.name,
                        value: item.id,
                        imagePath: item.imagePath,
                        address: item.address
                    })));
                }
            });
        },
        minLength: 1,
        select: function (event, ui) {
            window.location.href = '/city/' + ui.item.value;
        },
    }).data('ui-autocomplete')._renderItem = function(ul, item){
        var address = item.address ? item.address.match(/[^,]*,\s*(.*)/)[1].trim() : ''; // Check if address is empty
        var imgHtml = item.imagePath ? "<img src='storage/" + item.imagePath + "'></img>" : ''; // Check if imagePath is available
        return $("<li class='ui-autocomplete-row'>" + imgHtml + item.label + "<small>" + address + "</small></li>")
          .data("item.autocomplete", item)
          .appendTo(ul);
    };
});
