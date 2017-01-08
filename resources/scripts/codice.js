$('.alert-fixed').on('click', function () {
    $(this).slideUp('slow', function () {
        $(this).remove();
    });
});

$('table.docs-index td[data-toggle="tooltip"]').tooltip();
