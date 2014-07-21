<?php

/**
 * TemplateGenerator
 * @version 0.0.1
 * @author MinecrafterJPN
 */

echo "\n- [*] - TemplateGenerator - [*] -\n\n";

echo "PluginName: ";
$projectName = rtrim(fgets(STDIN));
@mkdir($projectName);

echo "author: ";
$author = rtrim(fgets(STDIN));

echo "authors(separate with space): ";
$authors = explode(" ", rtrim(fgets(STDIN)));
if (count($authors) === 1 and reset($authors) === "") $authors = null;

echo "version: ";
$version = rtrim(fgets(STDIN));

echo "API version(separate with space): ";
$apiVersions = explode(" ", rtrim(fgets(STDIN)));

echo "When is the plugin loaded?(STARTUP or POSTWORLD): ";
$load = strtoupper(rtrim(fgets(STDIN)));

while ($load !== "STARTUP" and $load !== "POSTWORLD") {
    echo "load must be \"STARTUP\" or \"POSTWORLD\"!\n";
    echo "When is the plugin loaded?(STARTUP or POSTWORLD):";
    $load = strtoupper(rtrim(fgets(STDIN)));
}

echo "description: ";
$description = rtrim(fgets(STDIN));

echo "website: ";
$website = rtrim(fgets(STDIN));

echo "depends(separate with space): ";
$depends = explode(" ", rtrim(fgets(STDIN)));
if (count($depends) === 1 and reset($depends) === "") $depends = null;

echo "softdepends(separate with space): ";
$softdepends = explode(" ", rtrim(fgets(STDIN)));
if (count($softdepends) === 1 and reset($softdepends) === "") $softdepends = null;

echo "\n$projectName has commands?(y/N): ";
$ans = rtrim(fgets(STDIN));

$commands = [];

while ($ans === "y") {
    echo "command name: ";
    $cName = rtrim(fgets(STDIN));
    echo "command description: ";
    $cDescription = rtrim(fgets(STDIN));
    echo "command usage: ";
    $cUsage = rtrim(fgets(STDIN));
    echo "command permission: ";
    $cPermission = rtrim(fgets(STDIN));
    $commands[] = ["name" => $cName, "description" => $cDescription, "usage" => $cUsage, "permission" => $cPermission];
    echo "More commands?(y/N): ";
    $ans = rtrim(fgets(STDIN));
}

echo "\n$projectName has permissions?(y/N): ";
$ans = rtrim(fgets(STDIN));

$permissions = [];

while ($ans === "y") {
    function makePermission()
    {
        echo "permission name: ";
        $pName = rtrim(fgets(STDIN));
        echo "permission default: ";
        $pDefault = rtrim(fgets(STDIN));
        echo "permission description: ";
        $pDescription = rtrim(fgets(STDIN));
        echo "$pName has children permissions?(y/N): ";
        $ans2 = rtrim(fgets(STDIN));

        $pChildren = [];

        while ($ans2 === "y") {
            $pChildren[] = makePermission();
            echo "$pName has more children permissions?(y/N): ";
            $ans2 = rtrim(fgets(STDIN));
        }
        return ["name" => $pName, "default" => $pDefault, "description" => $pDescription, "children" => $pChildren];
    }
    $permissions[] = makePermission();
    echo "More permissions?(y/N): ";
    $ans = rtrim(fgets(STDIN));
}

echo "\nGenerating plugin.yml ...\n";

$ymlPath = $projectName . "/plugin.yml";
touch($ymlPath);

$ymlText = "";
$ymlText .= "name: $projectName\n";
$ymlText .= "main: $author/$projectName\n";
$ymlText .= "author: $author\n";

if (!empty($authors)) {
    $authorsString = "[";
    foreach ($authors as $a) {
        $authorsString .= "$a ";
    }
    $authorsString = rtrim($authorsString);
    $authorsString .= "]";
    $ymlText .= "authors: $authorsString\n";
}

$ymlText .= "version: $version\n";

$apiVersionsString = "[";
foreach ($apiVersions as $apiVersion) {
    $apiVersionsString .= "$apiVersion ";
}
$apiVersionsString = rtrim($apiVersionsString);
$apiVersionsString .= "]";
$ymlText .= "api: $apiVersionsString\n";

$ymlText .= "load: $load\n";
$ymlText .= "description: $description\n";

if (!empty($website)) {
    $ymlText .= "website: $website\n";
}

if (!empty($depends)) {
    $dependsString = "[";
    foreach ($depends as $depend) {
        $dependsString .= "$depend ";
    }
    $dependsString = rtrim($dependsString);
    $dependsString .= "]";
    $ymlText .= "depend: $dependsString\n";
}

if (!empty($softdepends)) {
    $softdependsString = "[";
    foreach ($softdepends as $softdepend) {
        $softdependsString .= "$softdepend ";
    }
    $softdependsString = rtrim($softdependsString);
    $softdependsString .= "]";
    $ymlText .= "softdepend: $softdependsString\n";
}

if (!empty($commands)) {
    $commandsString = "commands:\n";
    $spaceCount = 1;
    foreach ($commands as $command) {
        $commandsString .= str_repeat(" ", $spaceCount) . "${command['name']}:\n";
        $spaceCount++;
        $commandsString .= str_repeat(" ", $spaceCount) . "desciption: ${command['description']}\n";
        $commandsString .= str_repeat(" ", $spaceCount) . "usage: ${command['usage']}\n";
        $commandsString .= str_repeat(" ", $spaceCount) . "permission: ${command['permission']}\n";
    }
    $ymlText .= $commandsString;
}

if (!empty($permissions)) {
    $permissionsString = "permissions:\n";
    $spaceCount = 1;
    foreach ($permissions as $permission) {
        function makePermissionString($spaceCount, $permission)
        {
            $str = "";
            $str .= str_repeat(" ", $spaceCount) . "${permission['name']}:\n";
            $spaceCount++;
            $str .= str_repeat(" ", $spaceCount) . "default: ${permission['default']}\n";
            $str .= str_repeat(" ", $spaceCount) . "description: ${permission['description']}\n";
            if (!empty($permission['children'])) {
                $str .= str_repeat(" ", $spaceCount) . "children:\n";
                $spaceCount++;
                foreach ($permission['children'] as $child) {
                    $str .= makePermissionString($spaceCount, $child);
                }
            }
            return $str;
        }
        $permissionsString .= makePermissionString($spaceCount, $permission);
    }
    $ymlText .= $permissionsString;
}

file_put_contents($ymlPath, $ymlText, LOCK_EX | FILE_APPEND);

echo "Generating src ...\n";
@mkdir($projectName . "/src");
@mkdir($projectName . "/src/$author");

$sourcecodePath = $projectName . "/src/$author/$projectName.php";

touch($sourcecodePath);

$sourcecode = <<< "SRC"
<?php

namespace $author;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class $projectName extends PluginBase
{
    public function onLoad()
	{
	}

	public function onEnable()
	{
    }

	public function onDisable()
	{
	}


SRC;

$sourcecode .= <<< 'SRC'
	public function onCommand(CommandSender $sender, Command $command, $label, array $args)
	{
	}
}
SRC;

file_put_contents($sourcecodePath, $sourcecode, LOCK_EX | FILE_APPEND);
