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



$output = shell_exec('phpenmod password 2>&1');
echo "<pre>$output</pre>";
echo "done";

$output = shell_exec('systemctl restart apache2 2>&1');
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
?>
