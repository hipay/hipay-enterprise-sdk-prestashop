$(document).ready(function() {
    
    // payment logos links
    
    $("#sofort").click(function() {
        window.location="sofort.html";
    });
    
    $("#klarna").click(function() {
        window.location="klarna.html";
    });
    
    $("#visa, #mastercard, #amex, #bancontact, #yandex, #webmoney, #sisal, #p24, #qiwi").click(function() {
        window.location="index.html";
    });
    
    // payment select links
    
    $('select[name="payment-links"]').change(function(){
        
        var paymentLink = $(this).val();
        
        if (paymentLink == "sofort") {
            window.location="sofort.html"; 
        } else {
            window.location="index.html";
        }
        
        if (paymentLink == "klarna") {
            window.location="klarna.html"; 
        }
        
    });
    
});