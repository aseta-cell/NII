<?php
// Old journal address: journal.php?journal=<name>
// Sends visitors to the journal's own page, or to the archive filtered by it.

require_once "config.php";

$journal = trim($_GET["journal"] ?? "");
$page = array_search($journal, $settings["journals"], true);

if ($page !== false) {
    redirect($page);
}

redirect($journal === "" ? "journals.html" : "archive.php?journal=" . urlencode($journal));
