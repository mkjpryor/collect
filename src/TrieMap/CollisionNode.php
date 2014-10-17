<?hh // strict

namespace Mkjp\Collect\TrieMap;


/**
 * @internal
 * 
 * Class for a node that handles hash collisions
 */
final class CollisionNode<Tk, Tv> implements Node<Tk, Tv> {
    /**
     * Create a new collision node with the given 
     */
    public function __construct(private array<(Tk, Tv)> $items, private int $depth) { }
    
    public function contains(int $hash, Tk $key): bool {
        foreach( $this->items as $item ) {
            if( $item[0] === $key ) return true;
        }
        return false;
    }
    
    public function count(): int {
        return count($this->items);
    }
    
    public function get(int $hash, Tk $key): ?Tv {
        foreach( $this->items as $item ) {
            if( $item[0] === $key ) return $item[1];
        }
        return null;
    }
    
    public function getIterator(): \Iterator<\Pair<Tk, Tv>> {
        foreach( $this->items as $item ) {
            yield Pair { $item[0], $item[1] };
        }
    }
    
    public function remove(int $hash, Tk $key): Node<Tk, Tv> {
        $idx = -1;
        foreach( $this->items as $i => $item ) {
            if( $item[0] === $key ) $idx = $i;
        }
        
        if( $idx < 0 ) return $this;
        
        if( count($this->items) === 1 ) return new EmptyNode($this->depth);
        
        $items = $this->items;
        array_splice($items, $idx, 1);
        return new CollisionNode($items, $this->depth);
    }
    
    public function set(int $hash, Tk $key, Tv $value): Node<Tk, Tv> {
        // Check if we already have the key
        foreach( $this->items as $i => $item ) {
            if( $item[0] === $key ) {
                // If we do already have the key, just change the associated value
                $items = $this->items;
                $items[$i] = tuple($key, $value);
                return new CollisionNode($items, $this->depth);
            }
        }
        // If we don't have the key, just append the value
        $items = $this->items;
        $items[] = tuple($key, $value);
        return new CollisionNode($items, $this->depth);
    }
}
