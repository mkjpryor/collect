<?hh // strict

namespace Mkjp\Collect;

use Mkjp\Option\Option;

use Mkjp\Collect\TrieMap\Node;
use Mkjp\Collect\TrieMap\EmptyNode;


/**
 * Immutable implementation of the map interface using a hash-array-mapped-trie
 */
final class TrieMap<Tk, Tv> implements CollectMap<Tk, Tv> {
    use MapTrait<Tk, Tv>;
    
    /**
     * Creates a new TrieMap with the given root node
     */
    private function __construct(private Node<Tk, Tv> $root) {}
    
    private static function hashCode(Tk $key): int {
        // Get a string hash for the value
        if( is_object($key) ) {
            // For objects, use spl_object_hash
            $strHash = spl_object_hash($key);
        }
        else {
            // Now we know we have a non-int primitive type
    
            // For arrays, first hash the contents
            if( is_array($key) )
                $key = array_map($x ==> TrieMap::hashCode($x), $key);
    
            // Just cast the object to a string
            // We don't worry too much about collisions here, since they should be
            // few in number, and we can deal with them if they happen
            $strHash = (string)$key;
        }
    
        // Convert the string hash to a 32-bit integer
        return crc32($strHash) & 0xFFFFFFFF;
    }

    public function containsKey(Tk $key): bool {
        return $this->root->contains(self::hashCode($key), $key);
    }
    
    public function count(): int {
        return $this->root->count();
    }
    
    public function get(Tk $key): Option<Tv> {
        return Option::from($this->root->get(self::hashCode($key), $key));
    }
    
    public function getIterator(): KeyedIterator<Tk, Tv> {
        // Create a KeyedIterator from an iterator over our key-value pairs
        return new KeyValuePairIterator($this->root->getIterator());
    }

    public function remove(Tk $key): CollectMap<Tk, Tv> {
        $node = $this->root->remove(self::hashCode($key), $key);
        // If the returned node is different to the current root, we need to return
        // a new map, otherwise we just return $this
        return $node === $this->root ? $this : new TrieMap($node);
    }
    
    public function set(Tk $key, Tv $value): CollectMap<Tk, Tv> {
        $node = $this->root->set(self::hashCode($key), $key, $value);
        // If the returned node is different to the current root, we need to return
        // a new map, otherwise we just return $this
        return $node === $this->root ? $this : new TrieMap($node);
    }
    
    /**
     * Create a new empty TrieMap
     */
    public static function create<Tk1, Tv1>(): CollectMap<Tk1, Tv1> {
        return new TrieMap(new EmptyNode(0));
    }
    
    /**
     * Create a new TrieMap containing the given items
     */
    public static function from<Tk1, Tv1>(KeyedTraversable<Tk1, Tv1> $items): CollectMap<Tk1, Tv1> {
        $map = TrieMap::create();
        foreach( $items as $k => $v ) $map = $map->set($k, $v);
        return $map;
    }
}
