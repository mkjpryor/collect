<?hh // strict

namespace Mkjp\Collect;


/**
 * Extensional set backed by a TrieMap
 */
final class TrieSet<Tv> implements ExtensionalSet<Tv> {
    use SetTrait<Tv>, IterableTrait<Tv>;
    
    private function __construct(private CollectMap<Tv, int> $items) { }
    
    public function add(Tv $item): ExtensionalSet<Tv> {
        // Try to set the item as a key in the underlying map
        $items = $this->items->set($item, 1);
        // If it changes the map, return a new set
        return $items === $this->items ? $this : new TrieSet($items);
    }
    
    public function contains(Tv $item): bool {
        return $this->items->containsKey($item);
    }
    
    public function getIterator(): \Iterator<Tv> {
        return $this->items->keys()->getIterator();
    }
    
    public function remove(Tv $item): ExtensionalSet<Tv> {
        // Try to remove the key from the underlying map
        $items = $this->items->remove($item);
        // If it changes the map, return a new set
        return $items === $this->items ? $this : new TrieSet($items);
    }
    
    public function size(): int {
        return $this->items->count();
    }
    
    /**
     * Creates a new empty TrieSet
     */
    public static function create<Tv1>(): ExtensionalSet<Tv1> {
        return new TrieSet(TrieMap::create());
    }
    
    /**
     * Creates a new TrieSet containing the unique items from the given traversable
     */
    public static function from<Tv1>(\Traversable<Tv1> $items): ExtensionalSet<Tv1> {
        $set = TrieSet::create();
        foreach( $items as $v ) $set = $set->add($v);
        return $set;
    }
}
