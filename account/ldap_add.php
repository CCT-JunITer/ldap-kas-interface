<?php
header("Content-Type: text/html; charset=utf-8");
include __DIR__ . '/../lib/autoload.php';

$ldap = new Ldap();

$email = $_COOKIE['account'];

if ($ldap->findUser($email)) {
    header('Location: /account');
}

function encodeImage($imgfile)
{
    $imgbinary = fread(fopen($imgfile, "r"), filesize($imgfile));
    return (base64_encode($imgbinary));
    //return $imgbinary;
}

$universities = array(
    "TU" => "Technische Universität Berlin",
    "HU" => "Humboldt-Universität zu Berlin",
    "FU" => "Freie Universität Berlin",
    "XX" => "Sonstige"
);
$degrees = array(
    "Bachelor",
    "Master",
    "Doktor"
);
$fieldOfStudies = array(
    "Informatik",
    "Ingenieurwesen",
    "Naturwissenschaften und Mathematik",
    "Psychologie und Sozialwissenschaften",
    "Wirtschaftswissenschaften",
    "Sonstige"
);

if ($_POST['formSent'] > 0) {

    $user = array();

    $imap = new Imap($email, $_POST['pass']);

    if ($_POST['pass'] == $_POST['passCheck'] && $imap->auth()) {

        $password = "{SHA}" . base64_encode(pack("H*", sha1($_POST['pass'])));

        /*if (isset($_FILES['profilePhoto'])) {

            $tmp_path = sys_get_temp_dir() . $_FILES['profilePhoto']['tmp_name'];
            echo $tmp_path . "\n";
            if (file_exists($tmp_path)) {
                $imgPlain = encodeImage($tmp_path);
            }

        } else {
            $imgPlain = " ";
        }

        echo "<pre>$imgPlain</pre>";
*/

        $user['cn'] = explode('@', $email)[0];
        $user['sn'] = $_POST['lastName'];
        $user['givenName'] = $_POST['firstName'];
        $user['active'] = !isset($_POST['passiv']) ? 1 : 0;
        $user['ressort'] = $_POST['ressort'];
        $user['birthdate'] = $_POST['birthday'];
        $user['entrydate'] = $_POST['entryDate'];
        $user['linkedinurl'] = !empty($_POST['linkedInUrl']) ? $_POST['linkedInUrl'] : " ";
        $user['slackid'] = !empty($_POST['slackId']) ? $_POST['slackId'] : " ";
        $user['university'] = $universities[$_POST['university']];
        $user['unishort'] = $_POST['university'];
        $user['courseofstudies'] = $_POST['courseofstudies'];
        $user['fieldeofstudies'] = $_POST['fieldofstudies']; // so auch im LDAP mit field"e"ofstudies
        $user['degree'] = $_POST['degree'];
        $user['status'] = $_POST['memberstatus'];
        $user['passivesince'] = isset($_POST['passiv']) ? $_POST['passiveSince'] : " ";
        $user['passiveuntil'] = isset($_POST['passiv']) ? $_POST['passiveUntil'] : " ";
        $user['mail'] = $email;
        $user['objectclass'][0] = "top";
        $user['objectclass'][1] = "person";
        $user['objectclass'][2] = "organizationalPerson";
        $user['objectclass'][3] = "inetOrgPerson";
        $user['objectclass'][4] = "cct-ev";
        $user['userPassword'] = $password;
        $user['jpegPhoto'] = $imgPlain;

        $user['exitdate'] = ' ';
        $user['apiKey'] = ' ';
        $user['apiKeyExpire'] = ' ';
        $user['nfcId'] = ' ';
        $user['extrafield1'] = ' ';
        $user['extrafield2'] = ' ';
        $user['extrafield3'] = ' ';
        $user['extrafield4'] = ' ';
        $user['extrafield5'] = ' ';
        $user['extrafield6'] = ' ';
        $user['extrafield7'] = ' ';
        $user['extrafield8'] = ' ';
        $user['extrafield9'] = ' ';
        $user['extrafield10'] = ' ';
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
<div class="alert alert-info">Wenn du dem Vorstand, den Senior Consultants (ehem. Board) oder den PMs angehörst, wähle
    "kein Ressort" aus und melde
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

                <div class="form-group">
                    <select name="university" id="university" class="form-control">
                        <!--<option>--- Universität wählen ---</option>-->
                        <?php
                        foreach ($universities as $key => $value) {
                            ?>
                            <option value="<?= $key ?>"><?= $value ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <select name="fieldofstudies" id="fieldofstudies" class="form-control">
                        <option>--- Studienrichtung wählen ---</option>
                        <?php
                        foreach ($fieldOfStudies as $value) {
                            ?>
                            <option value="<?= $value ?>"><?= $value ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <select name="degree" id="degree" class="form-control">
                        <!--<option>--- Abschluss wählen ---</option>-->
                        <?php
                        foreach ($degrees as $value) {
                            ?>
                            <option value="<?= $value ?>"><?= $value ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="form-label-group">
                    <input type="text" class="form-control" id="courseofstudies" name="courseofstudies" required>
                    <label for="courseofstudies">Studiengang:</label>
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
                <?php

                $status = [
                    "Trainee",
                    "Junior Consultant",
                    "Consultant",
                    "Alumni"
                ];

                foreach ($status as $i => $type) {

                    $typeId = strtolower($type);
                    $typeId = preg_replace('/\s+/', '', $typeId);

                    ?>
                    <div class="form-check">
                        <input id="memberStatus<?= $typeId ?>" class="form-check-input statusRadioField" type="radio"
                               name="memberstatus"
                               value="<?= $type ?>">
                        <label class="form-check-label" for="memberStatus<?= $typeId ?>"><?= $type ?></label>
                    </div>
                    <?php

                }

                ?>
                <hr>
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" id="passive" name="passive">
                    <label class="form-check-label" for="passive">ich bin passiv</label>
                </div>
                <div class="form-label-group" id="passiveSinceWrapper" style="display: none;">
                    <input type="date" class="form-control" id="passiveSince" name="passiveSince">
                    <label for="passiveSince">passiv seit:</label>
                </div>
                <div class="form-label-group" id="passiveUntilWrapper" style="display: none;">
                    <input type="date" class="form-control" id="passiveUntil" name="passiveUntil">
                    <label for="passiveUntil">passiv bis voraussichtlich:</label>
                </div>
            </fieldset>
        </div>
        <div class="col-md-4">
            <fieldset>
                <legend>Ressort wählen</legend>
                <?php

                $ressorts = [
                    "none" => "kein Ressort",
                    "IT" => "IT / JunITer",
                    "PR" => "PR / Marketing",
                    "HR" => "Human Ressources",
                    "IN" => "International Networks",
                    "RM" => "Relations Management",
                    "QM" => "Quality Management",
                ];

                foreach ($ressorts as $key => $ressort) {
                    ?>

                    <div class="form-check">
                        <input id="<?= $key ?>" class="form-check-input" type="radio" name="ressort" value="<?= $key ?>"
                               checked>
                        <label class="form-check-label" for="<?= $key ?>"><?= $ressort ?></label>
                    </div>

                    <?php
                }
                ?>
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
