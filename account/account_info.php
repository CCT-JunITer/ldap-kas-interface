<?php
header("Content-Type: text/html; charset=utf-8");
include __DIR__ . '/../lib/autoload.php';

$ldap = new Ldap();

$email = $_COOKIE['account'];

if ($ldap->findUser($email)) {
    header('Location: /account');
}

function encodeImage($imgfile) {
    $imgbinary = fread(fopen($imgfile, "r"), filesize($imgfile));
    return (base64_encode($imgbinary));
    //return $imgbinary;
}

if ($_POST['formSent'] > 0) {

    $user = array();

    $imap = new Imap($email, $_POST['pass']);

    if ($_POST['pass'] == $_POST['passCheck'] && $imap->auth()) {

        $password = "{SHA}" . base64_encode(pack("H*", sha1($_POST['pass'])));

        if (isset($_FILES['profilePhoto'])) {

            $tmp_path = sys_get_temp_dir() . $_FILES['profilePhoto']['tmp_name'];
            echo $tmp_path . "\n";
            if (file_exists($tmp_path)) {
                $imgPlain = encodeImage($tmp_path);
            }

        } else {
            $imgPlain = " ";
        }

        echo "<pre>$imgPlain</pre>";

        $user['cn'] = explode('@', $email)[0];
        $user['sn'] = $_POST['lastName'];
        $user['givenName'] = $_POST['firstName'];
        $user['active'] = !isset($_POST['firstName']) ? 1 : 0;
        $user['ressort'] = $_POST['ressort'];
        $user['birthdate'] = $_POST['birthday'];
        $user['entrydate'] = $_POST['entryDate'];
        $user['exitdate'] = " "; // todo: delete in ldap
        /*if (!empty($_POST['linkedInUrl'])) {
            $user['linkedin'] = $_POST['linkedInUrl'];
        }*/
        $user['linkedin'] = !empty($_POST['linkedInUrl']) ? $_POST['linkedInUrl'] : " ";
        $user['slackid'] = !empty($_POST['slackId']) ? $_POST['slackId'] : " ";
        $user['university'] = $_POST['university'];
        $user['unishort'] = $_POST['uniShort'];
        $user['courseofstudies'] = $_POST['studiengang'];
        $user['status'] = $_POST['memberstatus'];
        $user['passivesince'] = isset($_POST['passiv']) ? $_POST['passiveSince'] : " ";
        $user['mail'] = $email;
        $user['objectclass'][0] = "top";
        $user['objectclass'][1] = "person";
        $user['objectclass'][2] = "organizationalPerson";
        $user['objectclass'][3] = "inetOrgPerson";
        $user['objectclass'][4] = "cct-ev";
        $user['userPassword'] = $password;
        $user['jpegPhoto'] = $imgPlain;

//        add more structure by sorting by first char of lastName
//        $char = substr($_POST['lastName'], 0, 1);
//        $dn = "cn=" . $user['cn'] . ",ou=" . $char . ",ou=people," . $ldap->getBaseDn();
        $dn = "cn=" . $user['cn'] . ",ou=people," . $ldap->getBaseDn();

        if ($ldap->addUser($user, $dn)) {

            if ($_POST['ressort'] != 'none') {
                $ressortDn = 'cn=' . $_POST['ressort'] . ',ou=Ressorts,ou=Groups,' . $ldap->getBaseDn();
                $ldap->addMemberOf($email, $ressortDn);
            }

            $statusDn = 'cn=' . $_POST['memberstatus'] . ',ou=Status,ou=Groups,' . $ldap->getBaseDn();
            $ldap->addMemberOf($email, $statusDn);

            $ldap->addMemberOf($email, 'cn=users,ou=Groups,' . $ldap->getBaseDn());

            //header('Location: /account/?status=ldap_add_user_success'); // todo: header
        } else {
            ldap_get_option($ldap->getConn(), LDAP_OPT_ERROR_STRING, $diagnosticMsg);
            echo $diagnosticMsg;
        }

    } else {
        $passwordError = ""; // todo: set pwd error
    }


}


?>

<?php include __DIR__ . '/../_head.php'; ?>

<div class="card" style="">
    <div class="card-body">
        <h5 class="card-title">LDAP Status <span class="badge badge-warning">wichtig</span></h5>
        <h6 class="card-subtitle mb-2 text-muted">LDAP Account nicht vorhanden</h6>
        <p class="card-text">
            Dein Account wurde leider noch nicht in unsere zentrale Accountverwaltung übertragen.<br>
            Hier kannst du deinen Account ins LDAP übertragen.
        </p>
    </div>
</div>
<br>
<div class="alert alert-info">Wenn du dem Vorstand, dem Board oder den PMs angehörst, wähle "kein Ressort" aus und melde
    dich bitte beim IT-Support.
</div>
<br>
<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="formSent" value="<?= time() ?>">
    <div class="row">
        <div class="col-md-4">
            <fieldset>
                <legend>Mitgliedsdaten</legend>
                <div class="form-label-group">
                    <input type="text" class="form-control" id="firstName" name="firstName" required>
                    <label for="firstName">Vorname:</label>
                </div>
                <div class="form-label-group">
                    <input type="text" class="form-control" id="lastName" name="lastName" required>
                    <label for="lastName">Nachname:</label>
                </div>
                <div class="form-label-group">
                    <input type="date" class="form-control" id="birthday" name="birthday" required>
                    <label for="birthday">Geburtsdatum</label>
                </div>
                <div class="form-label-group">
                    <input type="date" class="form-control" id="entryDate" name="entryDate" required>
                    <label for="entryDate">Eintrittsdatum</label>
                </div>
            </fieldset>
        </div>
        <div class="col-md-4">
            <fieldset>
                <legend>Universität</legend>

                <div class="form-label-group">
                    <input type="text" class="form-control" id="university" name="university" required>
                    <label for="university">Universität:</label>
                </div>
                <div class="form-label-group">
                    <input type="text" class="form-control" id="uniShort" name="uniShort" required>
                    <label for="uniShort">Uni Kürzel:</label>
                </div>
                <div class="form-label-group">
                    <input type="text" class="form-control" id="studiengang" name="studiengang" required>
                    <label for="studiengang">Studiengang:</label>
                </div>
            </fieldset>
        </div>
        <div class="col-md-4">
            <fieldset>
                <legend>Social & Media</legend>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fab fa-linkedin"></i></span>
                    </div>
                    <input type="text" class="form-control" placeholder="LinkedIn Url" aria-label="LinkedIn Url"
                           id="linkedInUrl" name="linkedInUrl">
                </div>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fab fa-slack"></i></span>
                    </div>
                    <input type="text" class="form-control" placeholder="Slack ID" aria-label="Slack ID" id="slackId"
                           name="slackId" disabled>
                </div>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="profilePhoto" name="profilePhoto">
                    <label class="custom-file-label" for="profilePhoto" data-browse="Photo auswählen">Photo auswählen
                        (nur JPEG)</label>
                </div>
            </fieldset>
        </div>
        <div class="col-md-4">
            <fieldset>
                <legend>Passwort festlegen</legend>
                <div class="form-label-group">
                    <input type="password" class="form-control" id="pass" name="pass" required>
                    <label for="pass">Aktuelles Passwort:</label>
                </div>
                <div class="form-label-group">
                    <input type="password" class="form-control" id="passCheck" name="passCheck" required>
                    <label for="passCheck">Passwort wiederholen:</label>
                </div>
            </fieldset>
        </div>
        <div class="col-md-4">
            <fieldset>
                <legend>Mitgliedstatus wählen</legend>
                <div class="form-check">
                    <input id="memberStatusMember" class="form-check-input" type="radio" name="memberstatus"
                           value="mitglied">
                    <label class="form-check-label" for="memberStatusMember">Mitglied</label>
                </div>
                <div class="form-check">
                    <input id="memberStatusTrainee" class="form-check-input" type="radio" name="memberstatus"
                           value="anwaerter">
                    <label class="form-check-label" for="memberStatusTrainee">Anwärter</label>
                </div>
                <div class="form-check">
                    <input id="memberStatusNewbie" class="form-check-input" type="radio" name="memberstatus"
                           value="interessent" checked>
                    <label class="form-check-label" for="memberStatusNewbie">Interessent</label>
                </div>
                <div class="form-check">
                    <input id="memberStatusAlumni" class="form-check-input" type="radio" name="memberstatus"
                           value="alumni">
                    <label class="form-check-label" for="memberStatusAlumni">Alumni</label>
                </div>
                <hr>
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" id="passive" name="passive">
                    <label class="form-check-label" for="passive">ich bin passiv</label>
                </div>
                <div class="form-label-group" id="passiveSinceWrapper" style="display: none;">
                    <input type="date" class="form-control" id="passiveSince" name="passiveSince">
                    <label for="passiveSince">passiv seit:</label>
                </div>
            </fieldset>
        </div>
        <div class="col-md-4">
            <fieldset>
                <legend>Ressort wählen</legend>
                <div class="form-check">
                    <input id="ressortNONE" class="form-check-input" type="radio" name="ressort" value="none" checked>
                    <label class="form-check-label" for="ressortNONE">kein Ressort</label>
                </div>
                <div class="form-check">
                    <input id="ressortIT" class="form-check-input" type="radio" name="ressort" value="IT">
                    <label class="form-check-label" for="ressortIT">IT / Juniter</label>
                </div>
                <div class="form-check">
                    <input id="ressortPR" class="form-check-input" type="radio" name="ressort" value="PR">
                    <label class="form-check-label" for="ressortPR">PR / Marketing</label>
                </div>
                <div class="form-check">
                    <input id="ressortHR" class="form-check-input" type="radio" name="ressort" value="HR">
                    <label class="form-check-label" for="ressortHR">HR</label>
                </div>
                <div class="form-check">
                    <input id="ressortIN" class="form-check-input" type="radio" name="ressort" value="IN">
                    <label class="form-check-label" for="ressortIN">IN</label>
                </div>
                <div class="form-check">
                    <input id="ressortRM" class="form-check-input" type="radio" name="ressort" value="RM">
                    <label class="form-check-label" for="ressortRM">RM</label>
                </div>
                <div class="form-check">
                    <input id="ressortQM" class="form-check-input" type="radio" name="ressort" value="QM">
                    <label class="form-check-label" for="ressortQM">QM</label>
                </div>
            </fieldset>
        </div>
        <div class="col-md-12 text-center">
            <input type="submit" class="btn btn-success mt-5 d-block text-white" style="margin: auto"
                   value="Account übertragen">
        </div>
    </div>
</form>
<?php include __DIR__ . '/../_tail.php';
die(); ?>
