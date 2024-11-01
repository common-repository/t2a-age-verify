(function( $ ) {
    'use strict';
    $(document).ready(function(){
        $(".age-verification-form").submit(function(e) {
            e.preventDefault();

            $('.error', $(this)).remove();

            var surname = $("#surname");        if(!surname.val()) {
                errorBefore("Please enter a surname.", surname);
            }

            var forename = $("#forename");        if(!forename.val()) {
                errorBefore("Please enter a forename.", forename);
            }

            var postcode = $("#postcode");        if(!postcode.val()) {
                errorBefore("Please enter a postcode.", postcode);
            }

            var addr1 = $("#addr1");        if(!addr1.val()) {
                errorBefore("Please enter an address.", addr1);
            }

            if($('.error', $(this)).length) {
                $('.error', $(this)).first().next('input').focus();
            } else {

                $.ajax({
                    url: 'https://ageverifyuk.com/rest/rest.aspx',
                    dataType: 'json',
                    data: {
                        'method'   : "age_verification",
                        'api_key'  : 'wordpressdemo',
                        'surname' : surname.val(),
                        'forename' : forename.val(),
                        'postcode' : postcode.val(),
                        'addr1' : addr1.val(),
                        'check_under_18' : 'true',
                        'output'   : 'json'
                    },
                    success: function(result){
                        if(result.validation_status == "NOT_FOUND") {
                            $('#results-output').append('<p class="output not_found">'+forename.val()+' '+surname.val()+' was not found at the specificed address')
                        };
                        if(result.validation_status == "FOUND_UNDER_18") {
                            $('#results-output').append('<p class="output found_under_18">'+forename.val()+' '+surname.val()+' was found at the specified address, but we cannot confirm they are over 18')
                        };
                        if(result.validation_status == "FOUND") {
                            $('#results-output').append('<p class="output found">'+forename.val()+' '+surname.val()+' was found at the specified address and is over 18')
                        };
                        $('.age-verification-example').hide();
                        $('.results').show();
                    }
                });
            }
        });

        $('.results-return').on('click', function(e){
            e.preventDefault();
            $('#results-output').empty();
            $('.age-verification-form input').val('');

            $('.age-verification-example').show();
            $('.results').hide();
        });
    });

    function errorBefore(msg, insertBefore) {
        $('<p class="error">' + msg + '</p>').insertBefore(insertBefore);
    }
})( jQuery );