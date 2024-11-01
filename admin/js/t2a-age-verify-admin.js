(function( $ ) {
    'use strict';
    $(document).ready(function() {

        $('.data-product').on("change", function(){
            $('#'+$(this).val()).trigger('click');
        });

        var userTable = $('#t2aUsersTable').DataTable();
        $('#t2aProductsTable').DataTable();
        $('#t2aGuestTable').DataTable();
        $('#t2aAttemptsTable').DataTable();


        $('#product_verify').change(function(){
            if(this.checked) {
                $('#full_site').prop('checked', false);
            }
            $('.product-list').slideToggle();
        });

        $('#full_site').change(function(){
            if(this.checked) {
                $('#product_verify').prop('checked', false);
                $('.product-list').slideUp();
            }
        });

        $('#vonly').change(function(){
            if(this.checked) {
                userTable.search('Age Verified').draw();
                $('#nvonly').prop('checked', false);
            }else {
                userTable.search('').draw();
            }
        });

        $('#nvonly').change(function(){
            if(this.checked) {
                userTable.search('Not Verified').draw();
                $('#vonly').prop('checked', false);
            }else {
                userTable.search('').draw();
            }
        });
    });
})( jQuery );