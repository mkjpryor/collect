<?hh // strict

namespace Mkjp\Collect\TrieMap;


/**
 * @internal
 * 
 * Interface for nodes in a TrieMap
 */
interface Node<Tk, Tv> extends \IteratorAggregate<\Pair<Tk, Tv>> {
    public function contains(int $hash, Tk $key): bool;
    
    public function count(): int;
    
    public function get(int $hash, Tk $key): ?Tv;
    
    public function remove(int $hash, Tk $key): Node<Tk, Tv>;
    
    public function set(int $hash, Tk $key, Tv $value): Node<Tk, Tv>;
}
