<?php

include __DIR__ . '/lib/autoload.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$email = $_POST['email'];
$password = $_POST['password'];

$imap = new Imap($email, $password);

if ($imap->auth()) {
    $_SESSION['account'] = $email;
    $_SESSION['status'] = 'logged_in';
    header('Location: /account');
    setcookie('account', $email);
}

echo $email;

?>
<?php include __DIR__ . '/_head.php'; ?>
    <form class="form-signin" method="post" action="./index.php">
        <div class="text-center mb-4">
            <img class="mb-4 ml-5" src="https://cct-ev.de/wp-content/themes/cct_5/images/logo_black.png" alt="">
            <p>Melde dich mit deinen CCT e.V. Zugangsdaten an.</p>

            <?php

            if (!empty($email)) {
                echo ($imap->auth()) ? '<div class="alert alert-success">Login erfolgreich</div>' : '<div class="alert alert-danger">Kein Login möglich. Korrekte Daten?</div>';
            }

            ?>

        </div>

        <div class="form-label-group">
            <input type="email" id="inputEmail" name="email" class="form-control" placeholder="Email address" required
                   autofocus value="<?= $_POST['email'] ?>">
            <label for="inputEmail">Email address</label>
        </div>

        <div class="form-label-group">
            <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password"
                   required>
            <label for="inputPassword">Password</label>
        </div>

        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    </form>

<?php

include __DIR__ . '/_tail.php';
?>