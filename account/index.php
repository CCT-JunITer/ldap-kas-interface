<?php
header("Content-Type: text/html; charset=utf-8");
include __DIR__ . '/../lib/autoload.php';


$api = new Api();
$ldap = new Ldap();

$email = $_COOKIE['account'];

if ($_POST['func'] == 'chgPwd') {
    $newPwd = $_POST['newPwd'];
    $newPwdCheck = $_POST['newPwdCheck'];

    if ($newPwd == $newPwdCheck) {
        $api->changePwd($email, $newPwd);
    }

    $msg = "Password erfolgreich geändert. Die Änderung kann bis zu 10 Minuten dauern.";
}


?>

<?php include __DIR__ . '/../_head.php'; ?>

<?php if ($ldap->findUser($email) == false) { ?>
    <div class="card" style="">
        <div class="card-body">
            <h5 class="card-title">LDAP Status <span class="badge badge-warning">wichtig</span></h5>
            <h6 class="card-subtitle mb-2 text-muted">LDAP Account nicht vorhanden</h6>
            <p class="card-text">
                Dein Account wurde leider noch nicht in unsere zentrale Accountverwaltung übertragen.<br>
                Klicke unten um deinen Mail Account zu übertragen. Falls du nach deinem Passwort gefragt wirst, gib
                dieses
                bitte zur Bestätigung an.
            </p>
            <a href="ldap_add.php" class="btn btn-primary mr-3">LDAP Account anlegen</a>
            <a href="#" class="card-link">Support kontaktieren</a>
        </div>
    </div>
    <br>
<?php } else {
    if ($_GET['status'] == 'ldap_add_user_success') { ?>
        <div class="alert alert-success mt-4">
            Dein Nutzer wurde erfolgreich im System angelegt.
        </div>
    <?php } else { ?>
        <div class="card" style="">
            <div class="card-body">
                <h5 class="card-title">LDAP Status</h5>
                <h6 class="card-subtitle mb-2 text-muted">Dein LDAP Account existiert bereits</h6>
                <p class="card-text">
                </p>
            </div>
        </div>
    <?php }
} ?>
<div class="row">

     <!--    <div class="col-md-3">-->
<!--        <form action="--><?//= $_SERVER['PHP_SELF'] ?><!--" method="post">-->
<!--            <input type="hidden" name="func" value="chgPwd">-->
<!--            <fieldset>-->
<!--                <legend>Passwort ändern</legend>-->
<!--                <div class="form-label-group">-->
<!--                    <input type="text" class="form-control" id="newPwd" name="newPwd">-->
<!--                    <label for="newPwd">Neues Passwort:</label>-->
<!--                </div>-->
<!--                <div class="form-label-group">-->
<!--                    <input type="text" class="form-control" id="newPwdCheck" name="newPwdCheck">-->
<!--                    <label for="newPwdCheck">Passwort wiederholen:</label>-->
<!--                </div>-->
<!--                <input type="submit" class="btn btn-primary btn-block" value="Passwort ändern">-->
<!--            </fieldset>-->
<!--        </form>-->
<!--    </div>-->
</div>
<?php include __DIR__ . '/../_tail.php';
die(); ?>
