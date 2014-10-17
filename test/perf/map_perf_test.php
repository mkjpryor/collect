<?hh

/*******************************************************************************
 * This file contains performance tests for maps
 *******************************************************************************/
 
require_once(__DIR__ . '/../../vendor/autoload.php');
 
 
use Mkjp\Collect\CollectMap;
use Mkjp\Collect\HashMap;
use Mkjp\Collect\TrieMap;


function run_test<Tk>(int $n, (function(int): Tk) $keyGen): void {
    $name = [];
    $avgContains = [];
    $avgGet = [];
    $avgSet = [];
    $avgRemove = [];
    
    
    $name[] = 'Native Map';
    $map = Map {};
    $t1 = microtime(true);
    for( $i = 1; $i <= $n; $i++ ) $map->set($keyGen($i), $i);
    $t2 = microtime(true);
    for( $i = 1; $i <= $n; $i++ ) $c = $map->containsKey($keyGen($i));
    $t3 = microtime(true);
    for( $i = 1; $i <= $n; $i++ ) $g = $map->get($keyGen($i));
    $t4 = microtime(true);
    for( $i = 1; $i <= $n; $i++ ) $map->removeKey($keyGen($i));
    $t5 = microtime(true);
    $avgContains[] = ($t3 - $t2) / $n;
    $avgGet[] = ($t4 - $t3) / $n;
    $avgSet[] = ($t2 - $t1) / $n;
    $avgRemove[] = ($t5 - $t4) / $n;
    
    
    /*$name[] = 'Native ImmMap';
    $map = ImmMap {};
    $t1 = microtime(true);
    for( $i = 1; $i <= $n; $i++ ) $map = $map->toMap()->set($keyGen($i), $i)->toImmMap();
    $t2 = microtime(true);
    for( $i = 1; $i <= $n; $i++ ) $c = $map->containsKey($keyGen($i));
    $t3 = microtime(true);
    for( $i = 1; $i <= $n; $i++ ) $g = $map->get($keyGen($i));
    $t4 = microtime(true);
    for( $i = 1; $i <= ($n-1); $i++ ) $map = $map->toMap()->removeKey($keyGen($i))->toImmMap();
    $t5 = microtime(true);
    $avgContains[] = ($t3 - $t2) / $n;
    $avgGet[] = ($t4 - $t3) / $n;
    $avgSet[] = ($t2 - $t1) / $n;
    $avgRemove[] = ($t5 - $t4) / $n;
    
    
    $name[] = 'Collect naive HashMap';
    $map = HashMap::create();
    $t1 = microtime(true);
    for( $i = 1; $i <= $n; $i++ ) $map = $map->set($keyGen($i), $i);
    $t2 = microtime(true);
    for( $i = 1; $i <= $n; $i++ ) $c = $map->containsKey($keyGen($i));
    $t3 = microtime(true);
    for( $i = 1; $i <= $n; $i++ ) $g = $map->get($keyGen($i));
    $t4 = microtime(true);
    for( $i = 1; $i <= ($n-1); $i++ ) $map = $map->remove($keyGen($i));
    $t5 = microtime(true);
    $avgContains[] = ($t3 - $t2) / $n;
    $avgGet[] = ($t4 - $t3) / $n;
    $avgSet[] = ($t2 - $t1) / $n;
    $avgRemove[] = ($t5 - $t4) / $n;
    */
    
    $name[] = 'Collect TrieMap';
    $map = TrieMap::create();
    $t1 = microtime(true);
    for( $i = 1; $i <= $n; $i++ ) $map = $map->set($keyGen($i), $i);
    $t2 = microtime(true);
    for( $i = 1; $i <= $n; $i++ ) $c = $map->containsKey($keyGen($i));
    $t3 = microtime(true);
    for( $i = 1; $i <= $n; $i++ ) $g = $map->get($keyGen($i));
    $t4 = microtime(true);
    for( $i = 1; $i <= ($n-1); $i++ ) $map = $map->remove($keyGen($i));
    $t5 = microtime(true);
    $avgContains[] = ($t3 - $t2) / $n;
    $avgGet[] = ($t4 - $t3) / $n;
    $avgSet[] = ($t2 - $t1) / $n;
    $avgRemove[] = ($t5 - $t4) / $n;
    
    
    // Normalise the results to the smallest for each category
    $avgContains = array_map($x ==> $x / min($avgContains), $avgContains);
    $avgGet = array_map($x ==> $x / min($avgGet), $avgGet);
    $avgSet = array_map($x ==> $x / min($avgSet), $avgSet);
    $avgRemove = array_map($x ==> $x / min($avgRemove), $avgRemove);
    
    // Build and output the results table
    $table = new Console_Table();
    $table->setHeaders(["\$n = $n", "contains", "get", "set", "remove"]);
    $table->addCol($name, 0);
    $table->addCol($avgContains, 1);
    $table->addCol($avgGet, 2);
    $table->addCol($avgSet, 3);
    $table->addCol($avgRemove, 4);
    
    echo $table->getTable();
}


run_test(1000, $i ==> "K$i");
run_test(10000, $i ==> "K$i");
run_test(100000, $i ==> "K$i");
run_test(1000000, $i ==> "K$i");
