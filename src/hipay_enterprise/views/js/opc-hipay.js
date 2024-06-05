// Create an instance of EventTarget
var eventTarget = new EventTarget();

// Define the custom event
var opcUpdateCardEvent = new CustomEvent('opc_update_card', {
    detail: {
        message: 'Card updated successfully',
        timestamp: new Date()
    }
});

// Function to trigger the custom event
function triggerOpcUpdateCard() {
    eventTarget.dispatchEvent(opcUpdateCardEvent);
}
// Dispatch the event

loadScript(prestashop.urls.base_url + 'modules/hipay_enterprise/views/js/hosted-fields.js', function() {
     triggerOpcUpdateCard();
});
function findByOpcContainer(selector) {
    var container = document.querySelector('#onepagecheckoutps div#onepagecheckoutps_step_three');

    // Use querySelectorAll to find all elements matching the selector within the container
    var elements = container.querySelectorAll(selector);
    var foundElements = [];

    // Iterate through the found elements and add them to the foundElements array
    elements.forEach(function(element) {
        foundElements.push(element);
    });

    return foundElements;
}

function loadScript(url, callback) {
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = url;

    // If the script is successfully loaded, call the callback function
    script.onload = function() {
        if (typeof callback === 'function') {
            callback();
        }
    };

    document.head.appendChild(script);
}
