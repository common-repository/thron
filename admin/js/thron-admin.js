jQuery(document).ready(function($) {

    $(document).on("change", '#media-attachment-thron-source-filter', function(event) {
        if (this.value == 'local') {
            $('#media-attachment-thron-folders-filter').prop('disabled', 'disabled');
            $('.attachment-filters-tag').prop('disabled', 'disabled');

        } else {
            $('#media-attachment-thron-folders-filter').prop('disabled', false);
            $('.attachment-filters-tag').prop('disabled', false);

        }
    });

});
