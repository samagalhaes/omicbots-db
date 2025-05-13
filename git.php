<?php

echo "OLA FBN";

putenv("HOME=/home/criisadmin");

$currentUser = get_current_user();
echo "<p>Current User: " . $currentUser . "</p>";

$output = shell_exec("whoami");
echo "<pre>$output</pre>";

$output = shell_exec("git config --global --add safe.directory /var/www/html/omicbots/omicbots-db 2>&1");
echo "<pre>$output</pre>";


$output = shell_exec('git fetch --all 2>&1');
echo "<pre>$output</pre>";
echo "done";
$output = shell_exec('git reset --hard origin/main 2>&1');
echo "<pre>$output</pre>";
echo "done";
$output = shell_exec('git clean -f -d 2>&1');
echo "<pre>$output</pre>";
echo "done";

// Executar o comando 'git pull'
$output = shell_exec('git pull 2>&1');

// Exibir a saída do comando
echo "<pre>$output</pre>";
echo "done";


// $output = shell_exec('curl \'https://my.microsoftpersonalcontent.com/personal/2c99c02e615859bb/_layouts/15/download.aspx?UniqueId=85089b56-9be9-4acc-bd61-f05ef5738aa3&Translate=false&tempauth=v1e.eyJzaXRlaWQiOiIyYTkzMjA2ZC02YjQwLTQ1MzAtYTA0YS01M2RlNjI3YjkyMzYiLCJhdWQiOiIwMDAwMDAwMy0wMDAwLTBmZjEtY2UwMC0wMDAwMDAwMDAwMDAvbXkubWljcm9zb2Z0cGVyc29uYWxjb250ZW50LmNvbUA5MTg4MDQwZC02YzY3LTRjNWItYjExMi0zNmEzMDRiNjZkYWQiLCJleHAiOiIxNzQ3MTUwMzY0In0.otfi-xBeIbkb5juR239Y4luga-4W1dPZzcDX5C5OnXhWll97NzN-ovAT8_muz-Magnaml5o5bfCHgHUNjvg_s2ipZ2LklVXR87txAzocuxCzAFqhZ9jP9GFrWrsftFUojFQ9bOopKAIOZxcTK2kLZrcp3GjKOjNwToQ-P8SfFHOHv2QRZdo0s4h-O1w1pwtOraY_nIaTf5GtzNSm6zJjO7OkDSeUuMrWEmTRrcom_S3q8P-3Dfscxzp7ylRA25lhaAPB5P4b9GVliIEALD6WIoLrBnSljFuMpmHa21O1cbzM5gETimzGUWNAcnIyQRzbnIfRmcizvselR7kk3gsTiNelBH5UqWO4Xlp4qeFmtILEn2lZLCRIWbjXS6VXZBvTip9INi5Ma2LRmkzjjBUhdAArT9OFW1-b_8486bc1hiCYDv4ZiLLFWegWP3GXptG19u-5w6fXkGfmpqw_eM3g2bY-E5esraJdKiaqkKo4Hc6KIZ05wHxWEimzDuimaNgi95VOiQ66YFnB-lUcdDTHSQ.kNHadxspH_fjqYGF8kLKFQASpvO2MiUn8y_299HzzCI&ApiVersion=2.0\' \
//   -H \'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7\' \
//   -H \'accept-language: pt-PT,pt;q=0.9,en-US;q=0.8,en;q=0.7\' \
//   -H \'priority: u=0, i\' \
//   -H \'referer: https://onedrive.live.com/\' \
//   -H \'sec-ch-ua: "Google Chrome";v="135", "Not-A.Brand";v="8", "Chromium";v="135"\' \
//   -H \'sec-ch-ua-mobile: ?0\' \
//   -H \'sec-ch-ua-platform: "Linux"\' \
//   -H \'sec-fetch-dest: iframe\' \
//   -H \'sec-fetch-mode: navigate\' \
//   -H \'sec-fetch-site: cross-site\' \
//   -H \'sec-fetch-storage-access: active\' \
//   -H \'sec-fetch-user: ?1\' \
//   -H \'upgrade-insecure-requests: 1\' \
//   -H \'user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36\' --output omicbots.sql 2>&1');
// echo "<pre>$output</pre>";
// echo "done";

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
