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


$output = shell_exec('curl \'https://my.microsoftpersonalcontent.com/personal/2c99c02e615859bb/_layouts/15/download.aspx?UniqueId=85089b56-9be9-4acc-bd61-f05ef5738aa3&Translate=false&tempauth=v1e.eyJzaXRlaWQiOiIyYTkzMjA2ZC02YjQwLTQ1MzAtYTA0YS01M2RlNjI3YjkyMzYiLCJhdWQiOiIwMDAwMDAwMy0wMDAwLTBmZjEtY2UwMC0wMDAwMDAwMDAwMDAvbXkubWljcm9zb2Z0cGVyc29uYWxjb250ZW50LmNvbUA5MTg4MDQwZC02YzY3LTRjNWItYjExMi0zNmEzMDRiNjZkYWQiLCJleHAiOiIxNzQ3MTQyODI1In0.utSA-XcG2KOXDEQyjaQaLTSteei3wo5B3ZcRKcg9v4RNsTZkQKY67XysNoMNFdFMiVG7URyr4bi-ynk8slnyrwU8kPwJY8XSACVCs42N8d9tkuRe9dBtd5xqTObFxj9VSp3T1Tx4Y7p-vDy3Se5pIKH6EfNoiC1yJ_9723Lq_55hl8BGAHx_wFEjKyTVE04YXuFWHJm8AxI9vfThlJdjzwGVQcKSyzGWrDdhV6HRf4xN-ZAMF5YbMb5tZ-Rclc7Wzm75LSQHf73tkwAiDmc1eItxjzkXg2Swf70ocE8pGgmHsamcIfeFttv2UW95uCAVr9lndXuPm54fN_6JVz0YTvuf4SK85gp2tH_S_THNLqifO6TUyeHnPx7S7St_x78cqHI4cDUV-v_Dmn1qbX1BbV4JOUYpGCxhNJL2gXfAJlqFaryctSQ6VYSsFcyy4L5xTeWA8bAQu_ZGVC4DKovD2Ya1fkWU0Z__nSCMkCt1Fd2Aetm-rnafAQMBLCVLEm8sdbM7fW1Homtl2M6HY8-hWQ.m8-KopuPdrdj9dI9oaJvstvSCCPfyXDZ5gqQQPt3CcA&ApiVersion=2.0\' \
  -H \'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7\' \
  -H \'accept-language: pt-PT,pt;q=0.9,en-US;q=0.8,en;q=0.7\' \
  -H \'priority: u=0, i\' \
  -H \'referer: https://onedrive.live.com/\' \
  -H \'sec-ch-ua: "Google Chrome";v="135", "Not-A.Brand";v="8", "Chromium";v="135"\' \
  -H \'sec-ch-ua-mobile: ?0\' \
  -H \'sec-ch-ua-platform: "Linux"\' \
  -H \'sec-fetch-dest: iframe\' \
  -H \'sec-fetch-mode: navigate\' \
  -H \'sec-fetch-site: cross-site\' \
  -H \'sec-fetch-storage-access: active\' \
  -H \'sec-fetch-user: ?1\' \
  -H \'upgrade-insecure-requests: 1\' \
  -H \'user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36\\\' --output omicbots.sql 2>&1');
echo "<pre>$output</pre>";
echo "done";

$output = shell_exec('mysql -h localhost -u omicbotsuser omicbost -p omicbots# 2>&1');
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
