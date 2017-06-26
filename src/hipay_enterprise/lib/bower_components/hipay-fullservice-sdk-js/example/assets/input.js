$(document).ready(function() {
    
    // add class to input
            
    $(".input-group > input").focus(function(e){
        $(this).parent().addClass("input-group-focus");
    }).blur(function(e){
        $(this).parent().removeClass("input-group-focus");
    });

    document.addEventListener('keyup', function(e){
        var input = e.target;
        
        if (!$.nodeName(input, 'input')) return;
        input.checkValidity();
        var element = $(input).parent();
        
        if(input.validity.valid) {
            element.removeClass('invalid');
            element.parent().removeClass('invalid');
        } else {
            element.addClass('invalid');
            element.parent().removeClass('invalid');
        }
    });
    
    
    //limit input max-caracters
    
    (function($) {
        $.fn.extend( {
            limiter: function(limit, elem) {
                $(this).on("keyup focus", function() {
                    setCount(this, elem);
                });
                function setCount(src, elem) {
                    var chars = src.value.length;
                    if (chars > limit) {
                        src.value = src.value.substr(0, limit);
                        chars = limit;
                    }
                    elem.html( limit - chars );
                }
                setCount($(this)[0], elem);
            }
        });
    })(jQuery);
    
    var elem = $("#chars");
    $("#input-card").limiter(16, elem);
    $("#input-month").limiter(2, elem);
    $("#input-year").limiter(4, elem);
    $("#input-cvv").limiter(4, elem);
    
});