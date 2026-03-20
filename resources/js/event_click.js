let versionClicks = 0;

// Find the card by its class name
const versionCard = document.querySelector('.card_general');

versionCard.addEventListener('click', function() {
    versionClicks++;

    if (versionClicks === 7) {
        // Trigger your popup here!
        alert("Developer Mode Activated!"); 
        
        // Alternatively, show your custom errorModal from earlier:
        // showErrorPopup("Secret Menu Unlocked: V2.0.0 Beta Debugger");

        // Reset the counter so they can trigger it again
        versionClicks = 0;
    }

    if (versionClicks > 3 && versionClicks < 7) {
        console.log("Almost there... " + (7 - versionClicks) + " more clicks.");
    }
    // Optional: Reset counter if they don't click again within 2 seconds
    // This makes it a "rapid click" secret rather than just 7 clicks total
    clearTimeout(window.clickTimer);
    window.clickTimer = setTimeout(() => {
        versionClicks = 0;
    }, 2000);
});