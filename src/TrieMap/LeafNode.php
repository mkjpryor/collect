<?hh // strict

namespace Mkjp\Collect\TrieMap;


/**
 * @internal
 * 
 * Class for a leaf node
 */
final class LeafNode<Tk, Tv> implements Node<Tk, Tv> {
    /**
     * Create a new leaf node with the given hash, key and value
     */
    public function __construct(private int $hash,
                                private Tk $key, private Tv $value, private int $depth) { }
    
    public function contains(int $hash, Tk $key): bool {
        return $this->key === $key;
    }
    
    public function count(): int {
        return 1;
    }
    
    public function get(int $hash, Tk $key): ?Tv {
        return $this->key === $key ? $this->value : null;
    }
    
    public function getIterator(): \Iterator<\Pair<Tk, Tv>> {
        yield Pair { $this->key, $this->value };
    }
    
    public function remove(int $hash, Tk $key): Node<Tk, Tv> {
        return $this->key === $key ? new EmptyNode($this->depth) : $this;
    }
    
    public function set(int $hash, Tk $key, Tv $value): Node<Tk, Tv> {
        // If the hashes differ, we need to create a new index node
        if( $hash !== $this->hash ) {
            // If we have reached the maximum depth, we have a collision, so we
            // return a collision node
            if( $this->depth >= 6 ) {
                return new CollisionNode(
                    [tuple($this->key, $this->value), tuple($key, $value)], $this->depth
                );
            }
            
            return IndexNode::fromItems(tuple($this->hash, $this->key, $this->value),
                                        tuple($hash, $key, $value), $this->depth);
        }
        
        // If we have a hash collision, return a new collision node
        if( $key !== $this->key ) {
            return new CollisionNode(
                [tuple($this->key, $this->value), tuple($key, $value)], $this->depth
            );
        }

        if( $value === $this->value ) return $this;

        // If the keys are the same, just return a new leaf node with the new value        
        return new LeafNode($this->hash, $this->key, $value, $this->depth);
    }
}
