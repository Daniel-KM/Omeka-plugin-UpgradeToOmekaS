jQuery(document).ready(function () {
    var $ = jQuery;

    // Display the fields for the database.
    var databaseType = $('#fieldset-database input[name=database_type]:checked').val();
    databaseType == 'share' ? shareDatabase() : separateDatabase();

    $('#fieldset-database input[name=database_type]').change(function () {
        this.value == 'share' ? shareDatabase() : separateDatabase();
    });
    function shareDatabase() {
        var inputs = $('#fieldset-database .field');
        $(inputs).each(function() {
            if (!$(this).find('.inputs.radio').length && !$(this).find('#database_prefix').length && !$(this).find('#database_prefix_note-label').length) {
                $(this).hide(300);
            }
        });
    }
    function separateDatabase() {
        var inputs = $('#fieldset-database .field');
        $(inputs).each(function() {
            if (!$(this).find('.inputs.radio').length && !$(this).find('#database_prefix').length) {
                $(this).show(300);
            }
        });
    }

    // Display the mapped elements.
    hideshowMappedElements();
    $('#display-mapped-elements').click(function () {
        hideshowMappedElements();
    });
    function hideshowMappedElements() {
        var button = $('#display-mapped-elements');
        if (button.val() == 'show') {
            $('#fieldset-elements .field').each(function() {
                $(this).show(300);
            });
            button.val('hide');
            button.text('Hide mapped elements');
        } else {
            $($('#fieldset-elements .field')).each(function() {
                if ($(this).find('select').val())
                    $(this).hide(300);
            });
            button.val('show');
            button.text('Show all elements');
        }
    }

    // Display the checkboxes for the confirmation.
    if ($('body.upgrade.form').hasClass('confirm')) {
        $('#fieldset-confirm').show();
    } else {
        $('#fieldset-confirm').hide();
    }
});
