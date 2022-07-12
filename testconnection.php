<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$context = context_system::instance();

require_capability('moodle/site:config', $context);
$PAGE->set_context($context);
$PAGE->set_url('/local/quercus_tasks/testconnection.php');

echo $OUTPUT->header();

$settings = get_config('local_quercus_tasks');
if (!$settings->connectionhost || !$settings->connectionpassword || !$settings->connectiondatabase) {
    throw new moodle_exception('connectionsettingserror', 'local_quercus_tasks');
}

if (!function_exists('oci_connect')) {
    throw new moodle_exception('ocierror', 'local_quercus_tasks');
}

// Connects to the XE service (i.e. database) on the "localhost" machine
$conn = oci_connect($settings->connectiondatabase, $settings->connectionpassword, $settings->connectionhost);

if (!$conn) {
	$e = oci_error();
    throw new moodle_exception('connectiondatabaserror', 'local_quercus_tasks', '', null, $e['message']);
}

$stid = oci_parse($conn, 'select * from STAFF_ENROLMENTS OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY');
oci_execute($stid);

echo "<table border='1'>\n";

$columnsCount = oci_num_fields($stid);
echo "<tr>";
for ($i = 1; $i <= $columnsCount ; $i++) {
    $colname = oci_field_name($stid, $i);
    echo "  <th>".htmlspecialchars($colname,ENT_QUOTES|ENT_SUBSTITUTE)."</th>";
}
echo "</tr>";

while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
	echo "<tr>\n";
	foreach ($row as $item) {
		echo " <td>" . ($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;") . "</td>\n";
	}
	echo "</tr>\n";
}
echo "</table>\n";

echo $OUTPUT->footer();
