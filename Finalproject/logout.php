<?php
// Start the session
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Display a JavaScript alert for logout confirmation
echo "<script>
    alert('You have successfully logged out.');
    window.location.href = 'index.php';
</script>";
exit();
?>