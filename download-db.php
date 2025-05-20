<?php

echo "OLA FBN";

putenv("HOME=/home/criisadmin");

$currentUser = get_current_user();
echo "<p>Current User: " . $currentUser . "</p>";

$output = shell_exec("whoami");
echo "<pre>$output</pre>";

$output = shell_exec("git config --global --add safe.directory /var/www/html/omicbots/omicbots-db 2>&1");
echo "<pre>$output</pre>";

$output = shell_exec('curl \'https://inesctecpt-my.sharepoint.com/personal/renan_tosin_office365_inesctec_pt/_layouts/15/download.aspx?SourceUrl=%2Fpersonal%2Frenan%5Ftosin%5Foffice365%5Finesctec%5Fpt%2FDocuments%2FFicheiros%20de%20Conversa%20do%20Microsoft%20Teams%2Fomicbots%5F2025%5F05%5F20%2Esql\' \
  -H \'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7\' \
  -H \'accept-language: pt-PT,pt;q=0.9,en-US;q=0.8,en;q=0.7\' \
  -b \'MSFPC=GUID=de0adfcea7f94580999904c4b93c4a54&HASH=de0a&LV=202502&V=4&LU=1739440069970; ExcelWacDataCenter=PNL1; ExcelWacDataCenterSetTime=2025-05-09T07:35:19.803Z; FeatureOverrides_experiments=[]; PowerPointWacDataCenter=GEU9; WacDataCenter=GEU9; msal.cache.encryption=%7B%22id%22%3A%220196c994-f9b8-792d-b1f2-cef5a1bed283%22%2C%22key%22%3A%22uhAJH4uUP1pyFC4S3Ie8cYp3T-Qm6zNFuX9QcIJzWwE%22%7D; WSS_FullScreenMode=false; PowerPointWacDataCenterSetTime=2025-05-13T12:20:15.074Z; WacDataCenterSetTime=2025-05-13T12:20:15.074Z; MicrosoftApplicationsTelemetryDeviceId=11302cc2-690b-4ae0-85d4-6d4a3ce1c50f; FedAuth=77u/PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48U1A+VjE0LDBoLmZ8bWVtYmVyc2hpcHx1cm4lM2FzcG8lM2F0ZW5hbnRhbm9uIzExOWU1ZDM3LTQzYTgtNGY4Ny1iNTJiLWVlZDI4ODk5OWJmZiwwIy5mfG1lbWJlcnNoaXB8dXJuJTNhc3BvJTNhdGVuYW50YW5vbiMxMTllNWQzNy00M2E4LTRmODctYjUyYi1lZWQyODg5OTliZmYsMTMzOTIyMjk2OTgwMDAwMDAwLDAsMTMzOTIzMzQ4MjkyNzk4MDQ0LDAuMC4wLjAsMjU4LDExOWU1ZDM3LTQzYTgtNGY4Ny1iNTJiLWVlZDI4ODk5OWJmZiwsLDI5OTZhMjEwLTA3MzgtNGQ4Mi04ZDIxLTNhYTZkOTVlMmZjZCxjNDFhYTBhMS01MGEwLWMwMDAtZGJlMy1kMzNlODMzYmY1ZDIsaGZYaTRtY2dUa3F0WTFENitGT0ZPZywwLDAsMCwsLCwyNjUwNDY3NzQzOTk5OTk5OTk5LDAsLCwsLCwsMCwsMTg5MDQxLGQ3V19lbmZqWjMyVl9SdXI5aVJ3T1A1RThjOCw3bnhiczlWbEp0dlRXYUo4aFVRRkxCNndRSXMsdmFMalB4b3p5RXRqS0RuTU9mcmFYTVJSc216c3lFV09nQUZDbURpSW1GYjk5eGR1QlhQMUg2UnFwTFdKUUVndjNsaXFqbGFtMjNQTkRnS2lqRENNUDBidmlxcUgyVGs2QnVGRFAzeHdNdTVWajhRNm5ObkJpWEZMVlRqR3Btc1V5VkdtOGtyM1N3bVZSMkRRVjNPSmgwbkZhcVF2WG9nRTloRVB2Sm1zWUhjVzFOVVUzZFN1bFk2M1pyQ1lCdDZrVHVpSjFTb011dkpCZWpaMzgrZnJCUWtnREhnaVJINTN5elphbm5LQTBnQStib0JtaUl5OWdIc0xyK0dZZTlxN2F0OWhZbVNzOWJtVndIeGZrRExIYW5GZGRwUm9yMy9GSGpLTHNjM2UzT0k5VjF5V3BGcmh0ZDVXU0d0dVd1R2NhTGNZMk5xQjJLMWNLekJTOEE1b1VRPT08L1NQPg==; ai_session=XGgn3lVFuDDqvUD8lwgGFz|1747778425503|1747778665122; SPA_RT=\' \
  -H \'priority: u=0, i\' \
  -H \'referer: https://inesctecpt-my.sharepoint.com/personal/renan_tosin_office365_inesctec_pt/_layouts/15/onedrive.aspx?id=%2Fpersonal%2Frenan%5Ftosin%5Foffice365%5Finesctec%5Fpt%2FDocuments%2FFicheiros%20de%20Conversa%20do%20Microsoft%20Teams%2Fomicbots%5F2025%5F05%5F20%2Esql&parent=%2Fpersonal%2Frenan%5Ftosin%5Foffice365%5Finesctec%5Fpt%2FDocuments%2FFicheiros%20de%20Conversa%20do%20Microsoft%20Teams&ga=1\' \
  -H \'sec-ch-ua: "Chromium";v="136", "Google Chrome";v="136", "Not.A/Brand";v="99"\' \
  -H \'sec-ch-ua-mobile: ?0\' \
  -H \'sec-ch-ua-platform: "Linux"\' \
  -H \'sec-fetch-dest: iframe\' \
  -H \'sec-fetch-mode: navigate\' \
  -H \'sec-fetch-site: same-origin\' \
  -H \'sec-fetch-user: ?1\' \
  -H \'service-worker-navigation-preload: {"supportsFeatures":[1855,61313]}\' \
  -H \'upgrade-insecure-requests: 1\' \
  -H \'user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36\' --output omicbots.sql 2>&1');
echo "<pre>$output</pre>";
echo "done";

$output = shell_exec('pwd 2>&1');
echo "<pre>$output</pre>";
echo "done";

$output = shell_exec('ls -la 2>&1');
echo "<pre>$output</pre>";
echo "done";

$output = shell_exec('mysql --user=omicbotsuser --password=omicbots# omicbostdb < omicbots.sql 2>&1');
echo "<pre>$output</pre>";
echo "done";

// $output = shell_exec('ls -la /var/log/apache2/error.log 2>&1');
// // Exibir a saída do comando
// echo "<pre>$output</pre>";
// echo "done";

// $output = shell_exec('tail /var/log/apache2/error.log 2>&1');
// // Exibir a saída do comando
// echo "<pre>$output</pre>";
// echo "done";

$output = shell_exec('composer update 2>&1');
// Exibir a saída do comando
echo "<pre>$output</pre>";
echo "done";

$output = shell_exec('composer install 2>&1');
// Exibir a saída do comando
echo "<pre>$output</pre>";
echo "done";

// Finalizar o conteúdo PHP
$fileContent .= "\n?>";

// Escrever o conteúdo em um novo arquivo PHP
if (file_put_contents($newFileName, $fileContent)) {
    echo "O arquivo $newFileName foi gerado com sucesso!";
    //    echo "<br><a href='$newFileName'>Clique aqui para visualizar o arquivo gerado</a>";
} else {
    echo "Erro ao criar o arquivo $newFileName.";
}
