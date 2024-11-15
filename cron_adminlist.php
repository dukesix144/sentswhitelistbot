<?php
header('Content-type: application/json');

// Read the file
$fileUrl = 'https://www.rrgaming.net/squad/remotesquadadmins.txt';
$fh = fopen($fileUrl, 'r');

if (!$fh) {
    die(json_encode(['error' => 'Failed to open file.']));
}

$data = [];
$term = "Group=";

// Process each line
while ($line = fgets($fh)) {
    if (trim($line) !== '' && strpos($line, $term) === false) {
        $lineData = explode('-', $line);
        $data[] = [
            'item' => trim($lineData[0]),
            'value' => trim($lineData[1] ?? ''),
        ];
    }
}

fclose($fh);

// Decode JSON and prepare arrays
$ars = json_decode(json_encode($data), true);
$array1 = array_column($ars, 'item');
$array2 = array_column($ars, 'value');
$array3 = $array4 = $array5 = [];

function getStringBetween($string, $startChar, $endChar)
{
    $pattern = "/".preg_quote($startChar, '/')."(.*?)".preg_quote($endChar, '/')."/";
    preg_match($pattern, $string, $matches);
    return $matches[1] ?? '';
}

function uniqueMultidimArray($array, $key)
{
    $tempArray = [];
    $keyArray = [];

    foreach ($array as $val) {
        if (!in_array($val[$key], $keyArray)) {
            $keyArray[] = $val[$key];
            $tempArray[] = $val;
        }
    }
    return $tempArray;
}


$count = count($array1);
for ($i = 0; $i < $count; $i++) {
    $item = $array1[$i];
    $processedItem = substr($item, strlen('Admin='));
    $discord = strtok($processedItem, '/');
    $title = getStringBetween($array2[$i], "(", ")");
    $name = preg_replace("/\([^)]+\)/", "", $array2[$i]);

    $array5[] = [
        'id' => $i,
        'discord' => $discord,
        'name' => str_replace(' ', '', $name),
        'title' => str_replace(' ', '', $title),
        'steamid' => $title,
    ];
}

// Remove duplicates
$array99 = uniqueMultidimArray($array5, 'steamid');

// Final JSON
$final = json_encode($array99);

// Database connection
$servername = "";
$username = "";
$password = "";
$dbname = "";

$conn = new mysqli($servername, $username, $password, $dbname);
mysqli_set_charset($conn, 'utf8');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Clear and insert new data
$conn->query("DELETE FROM adminlist");

$array = json_decode($final, true);
foreach ($array as $row) {
    $sql = "
        INSERT INTO adminlist (id, name, title, discord, steamid) VALUES (
            '{$row['id']}',
            '{$row['name']}',
            '{$row['title']}',
            '{$row['discord']}',
            '{$row['steamid']}'
        )
    ";
    if (!$conn->query($sql)) {
        error_log("Error: " . $conn->error);
    }
}

$conn->close();
?>

