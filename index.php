<?php

// I use Google Voice to automatically initiate the Callback, but if you want, you can call your DID manually to do the same.
// The google-voice-click2call.py script used when setting this to TRUE can be found here: https://github.com/gboudreau/google-voice-click2call
$use_google_voice = TRUE;
$google_voice_number = "2245551212"; // Comment-out, or simply ignore, if you change $use_google_voice to FALSE.

### End of configuration ###

$EOL = "<br/>\n";

chdir(dirname(__FILE__));

if (isset($_POST['click2call'])) {
    $config = (object) $_POST;
    echo "Calling " . $_POST['callback_number'] . "... Please wait until your phone rings.$EOL";
    flush();
} else if (isset($_GET['id'])) {
    $guid = $_GET['id'];

    if (!file_exists("config/$guid.json")) {
        die("Unknown ID.");
    }

    $config = json_decode(file_get_contents("config/$guid.json"));
} else {
    ?>
    <html>
    <head>
        <title>voip.ms Click2Call</title>
    </head>
    <body>
    <a href="https://github.com/gboudreau/voipms-click2call" target="_blank"><img style="position: absolute; top: 0; right: 0; border: 0;" src="img/forkme_left_red_aa0000.png" alt="Fork me on GitHub"></a>
    <h1>voip.ms Click2Call Script</h1>
    <?php

    if (isset($_POST['callback_number'])) {
        $guid = gen_uuid();
        $_POST['did_number'] = preg_replace('/[^0-9]/', '', $_POST['did_number']);
        $_POST['callback_number'] = preg_replace('/[^0-9]/', '', $_POST['callback_number']);
        file_put_contents("config/$guid.json", json_encode($_POST));
        chmod("config/$guid.json", 0400);

        $url = str_replace('index.php', '', $_SERVER['SCRIPT_URI']) . "?id=$guid";
        ?>
        Use this URL to trigger a callback using the following configuration:<br/>
        <a target="_blank" href="<?php echo $url ?>">voip.ms Click2Call</a><br/>
        Right-click or drag-and-drop the above link to bookmark it for later use.<br/>
        <br/>
        Configuration attached to your unique URL:<br/>
        <table>
            <tr>
                <td>voip.ms username</td>
                <td><strong><?php echo htmlentities($_POST['voip_username']) ?></strong></td>
            </tr>
            <tr>
                <td>voip.ms API password</td>
                <td><strong><?php echo htmlentities($_POST['voip_api_password']) ?></strong></td>
            </tr>
            <tr>
                <td>voip.ms DID</td>
                <td><strong><?php echo htmlentities($_POST['did_number']) ?></strong></td>
            </tr>
            <tr>
                <td>Number to call</td>
                <td><strong><?php echo htmlentities($_POST['callback_number']) ?></strong></td>
            </tr>
        </table>
        </body>
        </html>
        <?php
        exit();
    }
    ?>
    <div>
        Using this script, you can initiate a callback to any phone number. This will give you a dial tone to dial out using your voip.ms account.<br/>
        Great to call long distance from your cell phone, or when you're at a relative's place, and want to call long distance without incurring any fees to them (it probably costs them nothing to receive calls, right?)
    </div>

    <h3>Installation Instructions</h3>
    <ol>
        <li>In your voip.ms account, <a target="voipms" href="https://www.voip.ms/m/api.php">enable the API, and create a new API password</a>. You will also need to add <strong><?php echo $_SERVER['SERVER_ADDR'] ?></strong> to the <strong>Enable IP Addresses</strong> field. The <a target="voipms" href="https://www.voip.ms/m/apidocs.php">API documentation</a> has detailed instructions on how to do this.</li>
        <li><a target="voipms" href="https://www.voip.ms/m/callback.php">Create a callback</a>. In <strong>Number to call</strong>, enter the number you want to get a dial tone on (your cell phone, a relative number, etc.) This number needs to include the country code, i.e. 15145551234.</li>
    </ol>

    <h3>How to use</h3>
    <ul>
        <li>
            Fill the form below, and hit <em>Save For Later</em>. You will receive your own unique URL; you'll want to bookmark it.<br/>
            When you want to initiate a callback, simply load your unique URL in any browser, and wait for your number to ring.
        </li>
        <li>You can also do a one-time Click2Call using the <em>Click2Call</em> button.</li>
    </ul>

    <form method="post" action="">
        <table>
            <tr><td>voip.ms username</td><td><input type="text" name="voip_username" placeholder="you@email.com" value="<?php echo isset($_REQUEST['voip_username']) ? $_REQUEST['voip_username'] : ''?>" /></td></tr>
            <tr><td>voip.ms API password</td><td><input type="text" name="voip_api_password" value="<?php echo isset($_REQUEST['voip_api_password']) ? $_REQUEST['voip_api_password'] : ''?>" /></td></tr>
            <tr><td>voip.ms DID</td><td><input type="text" name="did_number" placeholder="5145551212" value="<?php echo isset($_REQUEST['did_number']) ? $_REQUEST['did_number'] : ''?>" /></td><td class="help">Eg. 5145551212 (no country code). The DID to call to initiate the callback. This needs to be a voip.ms DID number.</td></tr>
            <tr><td>Number to call</td><td><input type="text" name="callback_number" placeholder="15145551212" /></td><td class="help">Eg. 15145551212 (include the country code). The number that will be called. Answering that call will give you a dial tone. Hooray!</td></tr>
        </table>
        <input type="submit" value="Save For Later" />
        or <input type="submit" name="click2call" value="Click2Call" />
    </form>

    <h3>How It Works</h3>
    <ul>
        <li>If you use <em>Save For Later</em>, your information is saved in a JSON file on the server, named with the GUID that is uniquely generated when saving. When you later call the page and provide that GUID, the script will load that JSON file, and execute a Click2Call.</li>
        <li>During a Click2Call, the voip.ms API is used to: 1) confirm that the specified DID exists; 2) confirm that the specified <em>Number to call</em> is configured as a <a target="voipms" href="https://www.voip.ms/m/callback.php">Callback</a>: this makes the system more secure, as it can only be used to callback numbers you have already configured in your account; 3) Create a <a target="voipms" href="https://www.voip.ms/m/callerid_filtering.php">CallerID Filter</a>, that will be used to trigger the Callback.</li>
        <li>After the above checks are made using the voip.ms API, the CallerID Filter will be modified (if needed) to route to the proper Callback. Then, Google Voice is used to call your DID: this will trigger the CallerID filter (which is configured for CallerID = the Google Voice number). The CallerID Filter is routed to the Callback for the specified number, so that phone will ring. Answering that call will give you a voip.ms dial tone. Dial any number, and your voip.ms account will be charged for the call.</li>
    </ul>
    </body>
    </html>
    <?php
    exit();
}

// If we use Google Voice, we need a CallerID Filter with the Google Voice number.
if (isset($use_google_voice) && $use_google_voice) {
    $filter_callerid = $google_voice_number;
} else {
    // If we don't use Google Voice, the CallerID Filter we need is the callback number.
    $filter_callerid = $config->callback_number;
}

echo "Connecting to voip.ms API... ";
$result = voipms_api_call('getDIDsInfo');
if (!isset($result->dids)) {
    die("[ERROR] couldn't connect to your account using the API. Error: " . $result->status . $EOL);
}
echo "[OK]$EOL";

echo "Looking for DID in acount... ";
$dids = $result->dids;
foreach ($dids as $did) {
    if ($did->did == $config->did_number) {
        $regular_routing = $did->routing;
    }
}
if (!isset($regular_routing)) {
    die("[ERROR] DID $config->did_number not found in account.$EOL");
}
echo "[OK]$EOL";

echo "Looging for Callback for $config->callback_number in account... ";
$callbacks = voipms_api_call('getCallbacks')->callbacks;
foreach ($callbacks as $callback) {
    if ($callback->number == $config->callback_number) {
        $callback_to_use = (int) $callback->callback;
        break;
    }
}
if (!isset($callback_to_use)) {
    die("[ERROR] Callback to number $config->callback_number not found in account.$EOL");
}
echo "[OK]$EOL";

echo "Looging for CallerID Filter for $filter_callerid in account... ";
$filter_to_edit = get_filter_to_edit();

if ($filter_to_edit === FALSE) {
    echo " [NOT FOUND]$EOL";
    echo "Creating CallerID Filter... ";
    $result = voipms_api_call('setCallerIDFiltering', array(
        'callerid' => $filter_callerid,
        'did' => $config->did_number,
        'routing' => "cb:$callback_to_use"
    ));
    if ($result->status != 'success') {
        die("[ERROR] $result->status$EOL");
    }
    $filter_to_edit = get_filter_to_edit();
    if ($filter_to_edit === FALSE) {
        die("[ERROR] Couldn't create new CallerID filter for $filter_callerid. See above for possible errors.$EOL");
    }
}
echo "[OK]$EOL";

change_filtering($filter_to_edit, "cb:$callback_to_use");

if (isset($use_google_voice) && $use_google_voice) {
    $cmd = "python google-voice-click2call.py " . escapeshellarg("t:$config->did_number");
    passthru($cmd);
} else {
    echo "You now need to call $config->did_number from $filter_callerid to trigger a callback. The call will not connect, that's normal. Just wait 10-15 seconds, and you'll receive a phone call from voip.ms. Answer that call to get a dial tone.$EOL";
}

function get_filter_to_edit() {
    global $filter_callerid;
    $filter_to_edit = FALSE;
    $filters = voipms_api_call('getCallerIDFiltering')->filtering;
    foreach ($filters as $filter) {
        if ($filter->callerid == $filter_callerid) {
            $filter_to_edit = $filter;
            break;
        }
    }
    return $filter_to_edit;
}

function change_filtering(&$filter_to_edit, $filter_routing_to_use) {
    global $config, $EOL;
    if ($filter_to_edit->routing != $filter_routing_to_use) {
        echo "Changing CallerID Filter routing to Callback $config->callback_number ... ";
        $result = voipms_api_call('setCallerIDFiltering', array(
            'filter' => $filter_to_edit->filtering,
            'callerid' => $filter_to_edit->callerid,
            'did' => $filter_to_edit->did,
            'routing' => $filter_routing_to_use
        ));
        $filter_to_edit->routing = $filter_routing_to_use;
        if ($result->status == 'success') {
            echo "[OK]$EOL";
        } else {
            die("[ERROR] $result->status$EOL");
        }
    }
}

function voipms_api_call($method, $params=array()) {
    global $config;
    $params['api_username'] = $config->voip_username;
    $params['api_password'] = $config->voip_api_password;
    $params['method'] = $method;
    
    $_params = array();
    foreach ($params as $k => $v) {
        $_params[] = urlencode($k) . '=' . urlencode($v);
    }
    $params = implode('&', $_params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_URL, "https://voip.ms/api/v1/rest.php?$params");
    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result);
}

function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}
?>
