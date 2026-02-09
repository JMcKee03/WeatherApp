<?php
session_start();
session_destroy();
echo "<script>
    localStorage.removeItem('loggedIn');
    window.location.href = 'login.html';
</script>";
exit();
?>
