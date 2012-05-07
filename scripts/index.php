<?php
//index repositories in elasticsearch

namespace phorkie;
set_include_path(
    __DIR__ . '/../src/'
    . PATH_SEPARATOR . get_include_path()
);
spl_autoload_register(
    function ($class) {
        $file = str_replace(array('\\', '_'), '/', $class) . '.php';
        $hdl = @fopen($file, 'r', true);
        if ($hdl !== false) {
            fclose($hdl);
            require $file;
        }
    }
);
require_once __DIR__ . '/../data/config.default.php';
if (file_exists(__DIR__ . '/../data/config.php')) {
    require_once __DIR__ . '/../data/config.php';
}
if ($GLOBALS['phorkie']['cfg']['setupcheck']) {
    SetupCheck::run();
}


$db = new Database();
$idx = $db->getIndexer();

//cleanup
echo "Deleting all index data\n";
$idx->deleteAllRepos();

//create mapping
echo "Index setup\n";
$db->getSetup()->setup();


$rs = new Repositories();
list($repos, $count) = $rs->getList(0, 10000);
$idx = new Indexer_Elasticsearch();
foreach ($repos as $repo) {
    echo 'Indexing ' . $repo->id . "\n";
    $idx->addRepo($repo, filectime($repo->gitDir));
}
?>
