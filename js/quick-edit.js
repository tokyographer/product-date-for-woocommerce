jQuery(document).ready(function ($) {
    // Populate Quick Edit fields
    $('a.editinline').on('click', function () {
        const postId = $(this).closest('tr').attr('id').replace('post-', '');
        const retreatStartDate = $(`#retreat_start_date_${postId}`).text();

        // Populate the Retreat Start Date field (date type)
        $('input[name="_retreat_start_date"]').val(retreatStartDate || '');
    });
});