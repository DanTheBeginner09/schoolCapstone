const logoutLink = document.getElementById("logoutLink"); // Make sure this ID is assigned to your logout link
    const logoutModal = document.getElementById("logoutModal");
    const confirmLogout = document.getElementById("confirmLogout");
    const cancelLogout = document.getElementById("cancelLogout");

    // Show modal on logout link click
    logoutLink.addEventListener("click", function(event) {
        event.preventDefault(); // Prevent the default link behavior
        logoutModal.style.display = "block"; // Show the modal
    });

    // Handle logout confirmation
    confirmLogout.addEventListener("click", function() {
        // Redirect to login page (or logout script)
        // Destroy the session
        
        window.location.href = 'logout.php'; // Adjust as necessary
    });

    // Close modal on cancel
    cancelLogout.addEventListener("click", function() {
        logoutModal.style.display = "none"; // Hide the modal
    });

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        if (event.target === logoutModal) {
            logoutModal.style.display = "none";
        }
    };



//     const toggleCheckbox = document.getElementById("toggle-Sidebar");
// const studentDashboard = document.querySelector(".studentdashboard");
// const paymentDashboard = document.querySelector(".paymentdashboard");

// Function to adjust dashboard styles based on sidebar state
function adjustDashboard() {
    if (window.innerWidth > 768) { // Check if the screen width is greater than 768px
        if (toggleCheckbox.checked) {
            studentDashboard.style.marginLeft = "250px"; // Adjust margin when sidebar is open
            studentDashboard.style.width = "calc(100% - 250px)"; // Adjust width when sidebar is open
       
            paymentDashboard.style.marginLeft = "250px"; 
            paymentDashboard .style.marginLeft = "250px"; 
        } else {
            studentDashboard.style.marginLeft = "0"; // Remove margin when sidebar is hidden
            studentDashboard.style.width = "100%"; // Full width when sidebar is hidden

            paymentDashboard.style.marginLeft = "0";
            paymentDashboard.style.width = "100%";


        }
    } else {
        // Reset styles for mobile devices
        studentDashboard.style.marginLeft = "0"; // Ensure no margin on mobile
        studentDashboard.style.width = "100%"; // Full width on mobile

        paymentDashboard.style.marginLeft = "0";
        paymentDashboard.style.width = "100%";
    }
}

// Add event listener for the toggle checkbox
toggleCheckbox.addEventListener("change", adjustDashboard);

// Optional: Adjust on resize
window.addEventListener("resize", adjustDashboard);


    // Additional logout modal functionality as previously defined

