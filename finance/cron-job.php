<?   

$postdata = json_encode(array("job" => array("enabled" => true)));

$result = file_get_contents("https://api.cron-job.org/jobs/3717393", false, stream_context_create(array(
    "http" => array(
        "method" => "PATCH",
        "header" => "Content-Type: application/json" . "\r\n"
        ."Content-Length: " . strlen($postdata) . "\r\n"
        ."Authorization: Bearer RtWJSAE3zWRkwYzX70uo4lfPzfJSjLr5r7udu8Poh2c=" . "\r\n",
        "content" => $postdata,
    ),
    )));

echo $result;

?>