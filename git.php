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
// $output = shell_exec('git reset --hard origin/main 2>&1');
// echo "<pre>$output</pre>";
// echo "done";
// $output = shell_exec('git clean -f -d 2>&1');
// echo "<pre>$output</pre>";
// echo "done";

// Executar o comando 'git pull'
$output = shell_exec('git pull 2>&1');

// Exibir a saída do comando
echo "<pre>$output</pre>";
echo "done";


// $output = shell_exec('curl \'https://my.microsoftpersonalcontent.com/personal/2c99c02e615859bb/_layouts/15/download.aspx?UniqueId=85089b56-9be9-4acc-bd61-f05ef5738aa3&Translate=false&tempauth=v1e.eyJzaXRlaWQiOiIyYTkzMjA2ZC02YjQwLTQ1MzAtYTA0YS01M2RlNjI3YjkyMzYiLCJhdWQiOiIwMDAwMDAwMy0wMDAwLTBmZjEtY2UwMC0wMDAwMDAwMDAwMDAvbXkubWljcm9zb2Z0cGVyc29uYWxjb250ZW50LmNvbUA5MTg4MDQwZC02YzY3LTRjNWItYjExMi0zNmEzMDRiNjZkYWQiLCJleHAiOiIxNzQ3MjA5NjExIn0.tMHxu9kFki0Y41zNXb0hCEfx3B5LYFOmEAHGtV_LqB8cZru8D49E7Jt1EuK1UI1zI_0F1zs_X-rGYplPjkLd-u0dA-ykJjy8nRbt3-rgb80h750vO2NXiRwKJ-0Q0tRxQesyz7vVaRmGfQWY4r3Z6LIoL6ZsYvzYdyJrjANOs9s-wLXvQCdiE4Ttq4jZF7ka5NYUwdpbzMu6w8QXEPmKmaNtoyFVnDLUR0pdUrhE5jiEr8_l1pVtrGovMe2DA6SUs72zsxPEt6XFFiRXN-YZwFBzbsS4w0FZvraIv0I_FbGAojkrEq2aUatvuhsNx7L8192Llw2smdN0hjYMrlSXwbh7b_MjVmL-Z-AusyYLK0J4UJ-fwaROESDlHho6WJGd4f7MULxt1DOYkNllsa4_FaFfMtvN2VOVnlKM_Mz8W8lJg9XSoMsWHoIotsABgBO05LCmIhcEg3EGIcpVn-j5EJ3hHX9LOw2-G0yeP1fgJdf1wKhiEgmvuqy8U1XzLjSERzGf3iThTa32PgRopOM7kw.Y7YXhiYPfsDmrTuBHXz91D0L-12PLWoR5b4X_lq1ccs&ApiVersion=2.0\' \
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
//   -H \'user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36\' --output /var/www/html/omicbots/omicbots-db/omicbots.sql 2>&1');
// echo "<pre>$output</pre>";
// echo "done";

$output = shell_exec('pwd 2>&1');
echo "<pre>$output</pre>";
echo "done";

$output = shell_exec('ls -la 2>&1');
echo "<pre>$output</pre>";
echo "done";

// $output = shell_exec('mysql --user=omicbotsuser --password=omicbots# omicbostdb < omicbots.sql 2>&1');
// echo "<pre>$output</pre>";
// echo "done";

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
