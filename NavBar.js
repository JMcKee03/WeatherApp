
//js to change mouse color to yellow when hovered over navigation bar

document.addEventListener("DOMContentLoaded", function() {
    const navLinks = document.querySelectorAll("nav a"); // Fixed selector
    
    navLinks.forEach(link => {
        link.addEventListener("mouseover", function() {
            this.style.color = "yellow";
        });
        link.addEventListener("mouseout", function() {
            this.style.color = "white";
        });
    });
});



