(function( $ ) {
    'use strict';
    $(document).ready(function() {
        $('body').on('click', '#processOcr', function(e){
            e.preventDefault();
            var transaction_id = $(this).data('transaction');
            var key            = $(this).data('key');
            var status         = "";
            window.open("https://ageverifyuk.com/upload/ocr/"+transaction_id);
            $('#avukMsg').html('<p>Waiting for confirmation of document upload</p>');
            checkOcrComplete(transaction_id, key, status);
        });
    });

    function checkOcrComplete(transaction_id, key, status){
        if(status !== ""){
            if(status === "VALIDATED") {
                $('#avukMsg').html('<p>Thank you for uploading your documents. Please attempt to checkout again to complete your transaction.</p>');
                var hiddenTransaction = "<input type=\"hidden\" value=\""+transaction_id+"\" name=\"transaction_id\" />";
                $(hiddenTransaction).insertAfter('#billing_email');
            } else {
                $('#avukMsg').html('<p>Unfortunately we were not able to validate you as being over 18.</p>');
            }
        }
        else{
            $.ajax({
                "url":"https://ageverifyuk.com/rest/rest.aspx",
                "data": {
                    "api_key":key,
                    "output":"json",
                    "method":"ocr_transaction",
                    "cmd":"get",
                    "transaction_id":transaction_id
                }
            }).done(function(data){
                status = data.ocr_status;
                setTimeout(function(){
                    checkOcrComplete(transaction_id, key, status);
                }, 250);
            });
        }
    }

})( jQuery );