<?hh // strict

namespace Mkjp\Collect\TrieMap;


/**
 * @internal
 * 
 * Class for an empty node
 */
final class EmptyNode<Tk, Tv> implements Node<Tk, Tv> {
    public function __construct(private int $depth) { }
    
    public function contains(int $hash, Tk $key): bool {
        return false;
    }
    
    public function count(): int {
        return 0;
    }
    
    public function get(int $hash, Tk $key): ?Tv {
        return null;
    }
    
    public function getIterator(): \Iterator<\Pair<Tk, Tv>> {
        return new \EmptyIterator();
    }
    
    public function remove(int $hash, Tk $key): Node<Tk, Tv> {
        return $this;
    }
    
    public function set(int $hash, Tk $key, Tv $value): Node<Tk, Tv> {
        return new LeafNode($hash, $key, $value, $this->depth);
    }
}
