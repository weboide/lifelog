jQuery( document ).ready(function( $ ) {
    if($('#inputtz').length) {
        $('#inputtz').val(Intl.DateTimeFormat().resolvedOptions().timeZone);
    }
});